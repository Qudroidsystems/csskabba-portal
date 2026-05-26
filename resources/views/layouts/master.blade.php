<!doctype html>
<html lang="en" data-layout="vertical" data-sidebar="dark" data-sidebar-size="lg" data-preloader="disable" data-theme="default" data-topbar="light" data-bs-theme="light">

<head>

    <meta charset="utf-8">
    <title>{{ $pagetitle }} | Vite-ESchool 2.0</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="school management software" name="description">
    <meta content="" name="author">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('theme/layouts/assets/images/favicon.ico')}}">

    <!-- Fonts css load -->
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link id="fontsLink" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/bold/style.css">
    <link href="{{ asset('theme/layouts/assets/fonts/materialdesignicons-webfont.woff2') }}?v=6.5.95" rel="stylesheet" type="font/woff2">

    <!-- NProgress (page transition bar) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.css"/>
    <script src="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.min.js"></script>

    <style>
        /* =====================================================
           NPROGRESS CUSTOM STYLE
           ===================================================== */
        #nprogress .bar {
            background: #4f8ef7 !important;
            height: 3px !important;
            box-shadow: 0 0 8px rgba(79, 142, 247, 0.6) !important;
        }
        #nprogress .peg  { box-shadow: none !important; }
        #nprogress .spinner { display: none !important; }

        /* =====================================================
           PAGINATION
           ===================================================== */
        .pagination-wrap .page-item { margin: 0 5px; }
        .pagination-wrap .page-link { padding: 5px 10px; }
        .pagination-wrap .active .page-link { background-color: #007bff; color: white; }
        .pagination-wrap .disabled .page-link { pointer-events: none; opacity: 0.5; }

        /* =====================================================
           LOADING SPINNER
           ===================================================== */
        .spin { animation: spin 1s linear infinite; }
        @keyframes spin {
            0%   { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* =====================================================
           CHECKBOX
           ===================================================== */
        .form-check-input:checked { background-color: #405189; border-color: #405189; }

        /* =====================================================
           SWEETALERT CUSTOMIZATION
           ===================================================== */
        .swal2-toast { font-size: 14px !important; }
        .swal2-container.swal2-top-end { top: 70px !important; }

        /* =====================================================
           TABLE ROW HOVER
           ===================================================== */
        .table tbody tr {
            transition: background-color 0.15s ease;
        }
        .table tbody tr:hover { background-color: rgba(67, 97, 238, 0.05); }

        /* =====================================================
           MODAL ANIMATIONS
           ===================================================== */
        .modal.fade .modal-dialog {
            transform: translate(0, -50px);
            transition: transform 0.3s ease-out;
        }
        .modal.show .modal-dialog { transform: translate(0, 0); }

        /* =====================================================
           SIDEBAR SCROLLBAR
           ===================================================== */
        #scrollbar { overflow-y: auto; }
        #scrollbar::-webkit-scrollbar { width: 4px; }
        #scrollbar::-webkit-scrollbar-track { background: transparent; }
        #scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.12); border-radius: 4px; }
        #scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.28); }
        .bg-light::-webkit-scrollbar { width: 6px; }
        .bg-light::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        .bg-light::-webkit-scrollbar-thumb { background: #888; border-radius: 10px; }
        .bg-light::-webkit-scrollbar-thumb:hover { background: #555; }

        /* =====================================================
           SIDEBAR: SMOOTH ACCORDION
           ===================================================== */
        #navbar-nav .menu-dropdown {
            overflow: hidden;
        }

        /* Chevron rotation */
        #navbar-nav .nav-link.menu-link .ri-arrow-down-s-line {
            transition: transform 0.25s ease;
            display: inline-block;
        }
        #navbar-nav .nav-link.menu-link[aria-expanded="true"] .ri-arrow-down-s-line {
            transform: rotate(180deg);
        }

        /* =====================================================
           SIDEBAR: ACTIVE PARENT ITEM
           ===================================================== */
        #navbar-nav .nav-link.menu-link.nav-active-parent {
            color: #fff !important;
            background: rgba(79, 142, 247, 0.18) !important;
            border-left: 3px solid #4f8ef7;
            padding-left: calc(1.3rem - 3px);
        }
        #navbar-nav .nav-link.menu-link.nav-active-parent i {
            color: #4f8ef7 !important;
        }

        /* =====================================================
           SIDEBAR: ACTIVE CHILD LINK
           ===================================================== */
        #navbar-nav .nav-sm .nav-link.nav-active-child {
            color: #7eb8fb !important;
            font-weight: 500;
        }
        #navbar-nav .nav-sm .nav-link.nav-active-child::before {
            content: '';
            display: inline-block;
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: #4f8ef7;
            margin-right: 8px;
            box-shadow: 0 0 0 3px rgba(79, 142, 247, 0.25);
            vertical-align: middle;
            flex-shrink: 0;
            animation: dotPop 0.25s ease;
        }
        @keyframes dotPop {
            from { transform: scale(0); opacity: 0; }
            to   { transform: scale(1); opacity: 1; }
        }

        /* =====================================================
           SIDEBAR: HOVER TRANSITIONS
           ===================================================== */
        #navbar-nav .nav-link {
            transition: color 0.18s ease, background-color 0.18s ease, padding-left 0.18s ease;
        }

        /* =====================================================
           RIPPLE EFFECT ON NAV LINKS
           ===================================================== */
        #navbar-nav .nav-link {
            position: relative;
            overflow: hidden;
        }
        .nav-ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.18);
            transform: scale(0);
            animation: ripple-anim 0.55s linear;
            pointer-events: none;
            z-index: 0;
        }
        @keyframes ripple-anim {
            to { transform: scale(5); opacity: 0; }
        }

        /* =====================================================
           BACK TO TOP BUTTON — ANIMATED
           ===================================================== */
        #back-to-top {
            opacity: 0;
            visibility: hidden;
            transform: translateY(12px);
            transition: opacity 0.3s ease, transform 0.3s ease, visibility 0.3s ease;
        }
        #back-to-top.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        #back-to-top:hover {
            transform: translateY(-3px) !important;
        }

        /* =====================================================
           PAGE CONTENT FADE-IN
           ===================================================== */
        .page-content {
            animation: pageFadeIn 0.35s ease;
        }
        @keyframes pageFadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* =====================================================
           TOPBAR BUTTON HOVER EFFECTS
           ===================================================== */
        #page-topbar .header-item {
            transition: color 0.2s ease, background-color 0.2s ease;
        }

        /* =====================================================
           PROFILE AVATAR HOVER
           ===================================================== */
        .header-profile-user-enhanced {
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .header-profile-user-enhanced:hover {
            transform: scale(1.07);
            box-shadow: 0 0 0 3px rgba(79, 142, 247, 0.35) !important;
        }

        /* =====================================================
           FINANCE MODULE CUSTOM CSS
           ===================================================== */
        .finance-stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 20px;
            color: white;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .finance-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.35);
        }

        .payment-method-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            line-height: 24px;
            font-family: 'Helvetica Neue', 'Helvetica', Arial, sans-serif;
            color: #555;
        }

        .payment-progress { height: 8px; border-radius: 4px; background: #e2e8f0; }
        .payment-progress-bar { height: 100%; border-radius: 4px; transition: width 0.4s ease; }

        .scholarship-card {
            border-left: 4px solid #10b981;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .scholarship-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .payroll-table th { background: #1e293b; color: white; }

        /* =====================================================
           CARD HOVER LIFT (GENERAL)
           ===================================================== */
        .card {
            transition: box-shadow 0.25s ease, transform 0.25s ease;
        }
        .card:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        }

        /* =====================================================
           BUTTON MICRO-INTERACTION
           ===================================================== */
        .btn {
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .btn:active {
            transform: scale(0.97);
        }

        /* =====================================================
           MENU TITLE FADE-IN (stagger)
           ===================================================== */
        #navbar-nav > li {
            animation: navItemFadeIn 0.4s ease both;
        }
        #navbar-nav > li:nth-child(1)  { animation-delay: 0.02s; }
        #navbar-nav > li:nth-child(2)  { animation-delay: 0.04s; }
        #navbar-nav > li:nth-child(3)  { animation-delay: 0.06s; }
        #navbar-nav > li:nth-child(4)  { animation-delay: 0.08s; }
        #navbar-nav > li:nth-child(5)  { animation-delay: 0.10s; }
        #navbar-nav > li:nth-child(6)  { animation-delay: 0.12s; }
        #navbar-nav > li:nth-child(7)  { animation-delay: 0.14s; }
        #navbar-nav > li:nth-child(8)  { animation-delay: 0.16s; }
        #navbar-nav > li:nth-child(9)  { animation-delay: 0.18s; }
        #navbar-nav > li:nth-child(10) { animation-delay: 0.20s; }
        #navbar-nav > li:nth-child(11) { animation-delay: 0.22s; }
        #navbar-nav > li:nth-child(12) { animation-delay: 0.24s; }
        #navbar-nav > li:nth-child(n+13) { animation-delay: 0.26s; }
        @keyframes navItemFadeIn {
            from { opacity: 0; transform: translateX(-8px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        /* =====================================================
           DROPDOWN MENU ANIMATION
           ===================================================== */
        .dropdown-menu {
            animation: dropdownFadeIn 0.2s ease;
        }
        @keyframes dropdownFadeIn {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* =====================================================
           TOOLTIP-STYLE TITLE ON COLLAPSED SIDEBAR
           ===================================================== */
        [data-sidebar-size="sm"] #navbar-nav .nav-link span[data-key],
        [data-sidebar-size="sm"] #navbar-nav .menu-title span {
            display: none;
        }

        /* =====================================================
           PRINT STYLES
           ===================================================== */
        @media print {
            .no-print { display: none !important; }
            .invoice-box { box-shadow: none; border: none; padding: 0; }
            body { padding: 0; margin: 0; }
        }

        /* =====================================================
           SPOTLIGHT SEARCH ANIMATION
           ===================================================== */
        @keyframes spotlightSpin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Route-specific CSS includes -->
    @if (Route::is('dashboard')) @include('layouts.pages-assets.css.users-list-css') @endif
    @if (Route::is('users.*')) @include('layouts.pages-assets.css.users-list-css') @endif
    @if (Route::is('student-id-cards.*')) @include('layouts.pages-assets.css.users-list-css') @endif
    @if (Route::is('student.payments.*')) @include('layouts.pages-assets.css.users-list-css') @endif
    @if (Route::is('profile.*')) @include('layouts.pages-assets.css.users-list-css') @endif
    @if (Route::is('roles.*')) @include('layouts.pages-assets.css.roles-list-css') @endif
    @if (Route::is('permissions.*')) @include('layouts.pages-assets.css.permission-list-css') @endif
    @if (Route::is('session.*')) @include('layouts.pages-assets.css.session-list-css') @endif
    @if (Route::is('school-information.*')) @include('layouts.pages-assets.css.schoolinformation-list-css') @endif
    @if (Route::is('admin.school-info.*')) @include('layouts.pages-assets.css.schoolinformation-list-css') @endif
    @if (Route::is('term.*')) @include('layouts.pages-assets.css.term-list-css') @endif
    @if (Route::is('schoolhouse.*')) @include('layouts.pages-assets.css.schoolhouse-list-css') @endif
    @if (Route::is('schoolarm.*')) @include('layouts.pages-assets.css.arm-list-css') @endif
    @if (Route::is('classcategories.*')) @include('layouts.pages-assets.css.classcategory-list-css') @endif
    @if (Route::is('schoolclass.*')) @include('layouts.pages-assets.css.schoolclass-list-css') @endif
    @if (Route::is('classteacher.*')) @include('layouts.pages-assets.css.classteacher-list-css') @endif
    @if (Route::is('subject.*')) @include('layouts.pages-assets.css.subject-list-css') @endif
    @if (Route::is('subjects.*')) @include('layouts.pages-assets.css.subject-list-css') @endif
    @if (Route::is('subjectteacher.*')) @include('layouts.pages-assets.css.subjectteacher-list-css') @endif
    @if (Route::is('subjectclass.*')) @include('layouts.pages-assets.css.subjectclass-list-css') @endif
    @if (Route::is('schoolbill.*')) @include('layouts.pages-assets.css.schoolbill-list-css') @endif
    @if (Route::is('schoolbilltermsession.*')) @include('layouts.pages-assets.css.schoolbilltermsession-list-css') @endif
    @if (Route::is('student.*')) @include('layouts.pages-assets.css.student-list-css') @endif
    @if (Route::is('studentbatchindex')) @include('layouts.pages-assets.css.student-list-css') @endif
    @if (Route::is('myclass.*')) @include('layouts.pages-assets.css.myclass-list-css') @endif
    @if (Route::is('mysubject.*')) @include('layouts.pages-assets.css.mysubject-list-css') @endif
    @if (Route::is('viewstudent')) @include('layouts.pages-assets.css.viewstudent-list-css') @endif
    @if (Route::is('studentreports.*')) @include('layouts.pages-assets.css.studentreport-list-css') @endif
    @if (Route::is('studentmockreports.*')) @include('layouts.pages-assets.css.studentreport-list-css') @endif
    @if (Route::is('broadsheet*')) @include('layouts.pages-assets.css.broadsheet-list-css') @endif
    @if (Route::is('subjectoperation.*')) @include('layouts.pages-assets.css.subjectoperation-list-css') @endif
    @if (Route::is('subjects.subjectinfo')) @include('layouts.pages-assets.css.subjectinfo-list-css') @endif
    @if (Route::is('myresultroom.*')) @include('layouts.pages-assets.css.myresultroom-list-css') @endif
    @if (Route::is('subjectscoresheet')) @include('layouts.pages-assets.css.subjectscoresheet-list-css') @endif
    @if (Route::is('subassessment.*')) @include('layouts.pages-assets.css.subjectscoresheet-list-css') @endif
    @if (Route::is('assessment.*')) @include('layouts.pages-assets.css.subjectscoresheet-list-css') @endif
    @if (Route::is('assessments')) @include('layouts.pages-assets.css.subjectscoresheet-list-css') @endif
    @if (Route::is('subjectscoresheet-mock.*')) @include('layouts.pages-assets.css.subjectscoresheet-mock-list-css') @endif
    @if (Route::is('studentresults*')) @include('layouts.pages-assets.css.studentresults-list-css') @endif
    @if (Route::is('schoolbill*')) @include('layouts.pages-assets.css.schoolbill-list-css') @endif
    @if (Route::is('schoolpayment*')) @include('layouts.pages-assets.css.schoolpayment-list-css') @endif
    @if (Route::is('analysis*')) @include('layouts.pages-assets.css.analysis-list-css') @endif
    @if (Route::is('exams*')) @include('layouts.pages-assets.css.exams-list-css') @endif
    @if (Route::is('questions*')) @include('layouts.pages-assets.css.questions-list-css') @endif
    @if (Route::is('cbt*')) @include('layouts.pages-assets.css.cbt-list-css') @endif
    @if (Route::is('classbroadsheet.*')) @include('layouts.pages-assets.css.classbroadsheet-list-css') @endif
    @if (Route::is('principalscomment.*')) @include('layouts.pages-assets.css.principalscomment-list-css') @endif
    @if (Route::is('myprincipalscomment.*')) @include('layouts.pages-assets.css.myprincipalscomment-list-css') @endif
    @if (Route::is('compulsorysubjectclass.*')) @include('layouts.pages-assets.css.compulsorysubjectclass-list-css') @endif
    @if (Route::is('subjectvetting.*')) @include('layouts.pages-assets.css.subjectvettings-list-css') @endif
    @if (Route::is('mocksubjectvetting.*')) @include('layouts.pages-assets.css.mocksubjectvettings-list-css') @endif
    @if (Route::is('mysubjectvettings.*')) @include('layouts.pages-assets.css.mysubjectvettings-list-css') @endif
    @if (Route::is('mymocksubjectvettings.*')) @include('layouts.pages-assets.css.mymocksubjectvettings-list-css') @endif
    @if (Route::is('timetable.*')) @include('layouts.pages-assets.css.timetable-list-css') @endif
    @if (Route::is('rooms.*')) @include('layouts.pages-assets.css.rooms-list-css') @endif
    @if (Route::is('promotions.*')) @include('layouts.pages-assets.css.promotions-list-css') @endif
    @if (Route::is('attendance.*')) @include('layouts.pages-assets.css.attendance-list-css') @endif
    @if (Route::is('transcript.*')) @include('layouts.pages-assets.css.attendance-list-css') @endif
    @if (Route::is('admin.score-entry.*')) @include('layouts.pages-assets.css.adminscoreentry-list-css') @endif

    {{-- Finance Module CSS --}}
    @if (Route::is('admin.scholarship.*') || Route::is('admin.discount.*') || Route::is('sibling.*') ||
        Route::is('payment.*') || Route::is('reports.financial.*') || Route::is('reports.analysis.*') ||
        Route::is('payroll.*') || Route::is('staff.payments.*'))
        @include('layouts.pages-assets.css.finance-list-css')
    @endif
</head>

<body>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <!-- ========== App Menu ========== -->
        <div class="app-menu navbar-menu">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                @php
                    use App\Models\SchoolInformation;
                    $schoolInfo = SchoolInformation::getActiveSchool();
                    $schoolName = $schoolInfo?->school_name ?? config('app.name', 'School System');
                    $defaultLogo = asset('theme/layouts/assets/images/logo-dark.png');
                    $defaultLogoLight = asset('theme/layouts/assets/images/logo-light.png');
                @endphp

                <a href="{{ url('/') }}" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="{{ $schoolInfo?->getLogoUrlAttribute() ?? $defaultLogo }}"
                            alt="{{ $schoolName }}"
                            style="height: 80px; width: auto; border-radius: 10px; object-fit: contain; padding: 3px; background: rgb(39, 38, 38);">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ $schoolInfo?->getLogoUrlAttribute() ?? $defaultLogo }}"
                            alt="{{ $schoolName }}"
                            style="height: 80px; width: auto; border-radius: 12px; object-fit: contain; padding: 2px; background: rgb(37, 36, 36);">
                    </span>
                </a>

                <a href="{{ url('/') }}" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="{{ $schoolInfo?->getLogoUrlAttribute() ?? $defaultLogoLight }}"
                            alt="{{ $schoolName }}"
                            style="height: 45px; width: auto; border-radius: 10px; object-fit: contain; padding: 3px; background: rgb(40, 39, 39);">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ $schoolInfo?->getLogoUrlAttribute() ?? $defaultLogoLight }}"
                            alt="{{ $schoolName }}"
                            style="height: 80px; width: auto; border-radius: 12px; object-fit: contain; padding: 2px; background: rgb(37, 36, 36);">
                    </span>
                </a>

                <button type="button" class="btn btn-sm p-0 fs-3xl header-item float-end btn-vertical-sm-hover" id="vertical-hover">
                    <i class="ri-record-circle-line"></i>
                </button>
            </div>

            <div id="scrollbar">
                <div class="container-fluid">
                    <div id="two-column-menu"></div>
                    <ul class="navbar-nav" id="navbar-nav">

                        <li class="menu-title"><span data-key="t-menu">Menu</span></li>

                        {{-- Dashboard --}}
                        <li class="nav-item">
                            <a class="nav-link menu-link collapsed" href="#sidebarDashboards" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarDashboards">
                                <i class="ph-gauge"></i> <span data-key="t-dashboards">Dashboards</span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarDashboards">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('dashboard') }}" class="nav-link" data-key="t-analytics">Administration Analytics</a>
                                    </li>
                                    @can('finance dashboard')
                                    <li class="nav-item">
                                        <a href="dashboard-crm.html" class="nav-link" data-key="t-crm">Finance Analytics</a>
                                    </li>
                                    @endcan
                                    @can('academics dashboard')
                                    <li class="nav-item">
                                        <a href="index.html" class="nav-link" data-key="t-ecommerce">Academics Analytics</a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>

                        {{-- USERS & PRIVILEGES --}}
                        @if(auth()->user()->can('View user') || auth()->user()->can('View role') || auth()->user()->can('View user-account'))
                            <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-pages">USERS & PRIVILEDGES</span></li>
                        @endif

                        @can('View user')
                            <li class="nav-item">
                                <a class="nav-link menu-link collapsed" href="#sidebarusers" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarusers">
                                    <i class="ph-user-circle"></i> <span data-key="t-authentication">User Managements</span>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarusers">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="{{ route('users.index') }}" class="nav-link" role="button" data-key="t-signin">Users</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        @endcan

                        {{-- My Account --}}
                        <li class="nav-item">
                            <a class="nav-link menu-link collapsed" href="#sidebaraccount" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebaraccount">
                                <i class="ph-address-book"></i> <span data-key="t-pages">My Account</span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebaraccount">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('users.overview', ['id' => Auth::id()]) }}" class="nav-link">
                                            <i class="ri-profile-line me-2"></i> My Profile
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('profile.settings', ['id' => Auth::id()]) }}" class="nav-link">
                                            <i class="ri-settings-3-line me-2"></i> Account Settings
                                        </a>
                                    </li>
                                    @if(Auth::user()->isStaff())
                                    <li class="nav-item">
                                        <div class="nav-link text-muted small px-0">
                                            <i class="ri-briefcase-line me-2"></i> STAFF SECTION
                                        </div>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('profile.settings', ['id' => Auth::id()]) }}#employmentInfo" class="nav-link ps-4">
                                            <i class="ri-building-line me-2"></i> Employment Details
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('profile.settings', ['id' => Auth::id()]) }}#qualifications" class="nav-link ps-4">
                                            <i class="ri-graduation-cap-line me-2"></i> Academic Qualifications
                                        </a>
                                    </li>
                                    @endif
                                    @if(Auth::user()->isStudent())
                                    <li class="nav-item">
                                        <div class="nav-link text-muted small px-0">
                                            <i class="ri-user-star-line me-2"></i> STUDENT SECTION
                                        </div>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('profile.settings', ['id' => Auth::id()]) }}#studentInfo" class="nav-link ps-4">
                                            <i class="ri-user-line me-2"></i> Student Details
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('profile.settings', ['id' => Auth::id()]) }}#parentInfo" class="nav-link ps-4">
                                            <i class="ri-parent-line me-2"></i> Parent Information
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('profile.settings', ['id' => Auth::id()]) }}#academicInfo" class="nav-link ps-4">
                                            <i class="ri-book-open-line me-2"></i> Academic Details
                                        </a>
                                    </li>
                                    @endif
                                    <li class="nav-item">
                                        <div class="nav-link text-muted small px-0">
                                            <i class="ri-shield-keyhole-line me-2"></i> SECURITY
                                        </div>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('profile.settings', ['id' => Auth::id()]) }}#security" class="nav-link ps-4">
                                            <i class="ri-lock-password-line me-2"></i> Change Password
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('profile.settings', ['id' => Auth::id()]) }}#security" class="nav-link ps-4">
                                            <i class="ri-mail-settings-line me-2"></i> Change Email
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        @can('View role')
                            <li class="nav-item">
                                <a class="nav-link menu-link collapsed" href="#sidebarroles" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarroles">
                                    <i class="ph-address-book"></i> <span data-key="t-pages">Roles And Permissions</span>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarroles">
                                    <ul class="nav nav-sm flex-column">
                                        @can('View role')
                                            <li class="nav-item"><a href="{{ route('roles.index') }}" class="nav-link" data-key="t-starter">Roles</a></li>
                                        @endcan
                                        @can('View permission')
                                            <li class="nav-item"><a href="{{ route('permissions.index') }}" class="nav-link" data-key="t-profile">Permissions</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            </li>
                        @endcan

                        {{-- STUDENT & PARENTS --}}
                        @if(auth()->user()->can('View student') || auth()->user()->can('Create student-bulk-upload') || auth()->user()->can('View parent') || auth()->user()->can('View id card'))
                            <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-apps">STUDENT & PARENTS</span></li>
                        @endif

                        @if(auth()->user()->can('View student') || auth()->user()->can('Create student-bulk-upload')|| auth()->user()->can('Create student-bulk-upload'))
                            <li class="nav-item">
                                <a href="#sidebarStudentmanagement" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarStudentmanagement">
                                    <i class="ph-storefront"></i> <span data-key="t-ecommerce">Student Management</span>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarStudentmanagement">
                                    <ul class="nav nav-sm flex-column">
                                        @can('View student')
                                            <li class="nav-item"><a href="{{ route('student.index') }}" class="nav-link" data-key="t-products">All Students</a></li>
                                        @endcan
                                        @can('Create student-bulk-upload')
                                            <li class="nav-item"><a href="{{ route('studentbatchindex') }}" class="nav-link" data-key="t-products-grid">Batch Student Registration</a></li>
                                        @endcan
                                        @can('View id card')
                                            <li class="nav-item">
                                                <a href="{{ route('student-id-cards.index') }}" class="nav-link">
                                                    <i class="ri-id-card-line me-1"></i> ID Card Generator
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                            </li>
                        @endif


                        @if(auth()->user()->can('View student assessments') || auth()->user()->can('View student payments'))
                            <li class="menu-title"><i class="ph-graduation-cap"></i> <span data-key="t-apps">STUDENT PORTAL</span></li>
                        @endif
                        @can('View student assessments')
                            <li class="nav-item">
                                <a href="#sidebarAssessments" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarExams">
                                    <i class="ph-graduation-cap"></i> <span data-key="t-ecommerce">Assessments</span>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarAssessments">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="{{ route('assessments') }}" class="nav-link" data-key="t-products">My Assessments</a>
                                        </li>

                                    </ul>
                                </div>
                            </li>
                        @endcan
                        {{-- @can('View student payments') --}}
                            <li class="nav-item">
                                <a href="#sidebarPayment" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarExams">
                                    <i class="ph-graduation-cap"></i> <span data-key="t-ecommerce">Payments</span>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarPayment">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="{{ route('student.payments') }}" class="nav-link" data-key="t-products">My Payments</a>
                                        </li>

                                    </ul>
                                </div>
                            </li>
                        {{-- @endcan --}}



                        @can('View parent')
                            <li class="nav-item">
                                <a href="#sidebarParent" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarParent">
                                    <i class="ph-storefront"></i> <span data-key="t-ecommerce">Parent Management</span>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarParent">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item"><a href="{{ route('parent.index') }}" class="nav-link" data-key="t-products">All Parents</a></li>
                                    </ul>
                                </div>
                            </li>
                        @endcan

                        {{-- SUBJECT REGISTRATION --}}
                        @if(auth()->user()->can('View my-class') || auth()->user()->can('View my-subject'))
                            <li class="menu-title"><i class="ph-folder-open"></i> <span data-key="t-apps">SUBJECT REGISTRATION</span></li>
                            <li class="nav-item">
                                <a href="#sidebarsubjectoperaton" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarsubjectoperaton">
                                    <i class="ph-folder-open"></i> <span data-key="t-ecommerce">Subject Registration</span>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarsubjectoperaton">
                                    <ul class="nav nav-sm flex-column">
                                        @can('View my-class')
                                            <li class="nav-item"><a href="{{ route('subjectoperation.index') }}" class="nav-link" data-key="t-products">Student Subject Registration</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            </li>
                        @endif

                         @if(auth()->user()->can('View exam') || auth()->user()->can('View cbt-exam'))
                            <li class="menu-title"><i class="ph-graduation-cap"></i> <span data-key="t-apps">EXAMS AND CBT </span></li>
                         @endif
                        @can('View exam')
                        <li class="nav-item">
                            <a href="#sidebarExams" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarExams">
                                <i class="ph-graduation-cap"></i> <span data-key="t-ecommerce">Exams Managment</span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarExams">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('exams.index') }}" class="nav-link" data-key="t-products">All Examinations</a>
                                    </li>
                                    @can('View question')
                                    <li class="nav-item">
                                        <a href="{{ route('questions.all') }}" class="nav-link" data-key="t-questions">Questions Management</a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                        @endcan


                        @can('View cbt-exam')
                        <li class="nav-item">
                            <a href="#sidebarCBT" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarCBT">
                                <i class="ph-graduation-cap"></i> <span data-key="t-ecommerce">CBT Managment</span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarCBT">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('cbt.index') }}" class="nav-link" data-key="t-products">CBT Exercise</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        @endcan

                        {{-- TIMETABLE MANAGEMENT --}}
                        @if(auth()->user()->can('View timetable') || auth()->user()->can('View my timetable'))
                            <li class="nav-item">
                                <a href="#sidebartimetable" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebartimetable">
                                    <i class="ph-calendar"></i> <span data-key="t-timetable">Timetable Management</span>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebartimetable">
                                    <ul class="nav nav-sm flex-column">
                                        @can('View timetable')
                                            <li class="nav-item"><a href="{{ route('timetable.index') }}" class="nav-link" data-key="t-admin-timetable"><i class="ph-gear"></i> Admin Timetable</a></li>
                                        @endcan
                                        @can('View my timetable')
                                            <li class="nav-item"><a href="{{ route('timetable.teacher') }}" class="nav-link" data-key="t-my-timetable"><i class="ph-user"></i> My Timetable</a></li>
                                        @endcan
                                        @can('View rooms')
                                            <li class="nav-item"><a href="{{ route('rooms.index') }}" class="nav-link" data-key="t-rooms"><i class="ph-door"></i> Room Management</a></li>
                                        @endcan
                                        @can('View timetable reports')
                                            <li class="nav-item"><a href="{{ route('timetable.reports.index') }}" class="nav-link" data-key="t-reports"><i class="ph-chart-bar"></i> Timetable Reports</a></li>
                                        @endcan
                                        @can('View exam timetable')
                                            <li class="nav-item"><a href="{{ route('exam-timetable.index') }}" class="nav-link" data-key="t-exam"><i class="ph-exam"></i> Exam Timetable</a></li>
                                        @endcan
                                        @can('View holidays')
                                            <li class="nav-item"><a href="{{ route('holidays.index') }}" class="nav-link" data-key="t-holidays"><i class="ph-calendar-blank"></i> Holidays</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            </li>
                        @endif

                        {{-- CLASSES & RECORDS --}}
                        @if(auth()->user()->can('View my-class') || auth()->user()->can('View my-subject') || auth()->user()->can('View my-subject-vettings') || auth()->user()->can('View my-mock-subject-vettings') || auth()->user()->can('View myresult-room') || auth()->user()->can('View student-report') || auth()->user()->can('View student-mock-report') || auth()->user()->can('View my-principals-comment'))
                            <li class="menu-title"><i class="ph-folder-open"></i> <span data-key="t-apps">CLASSES & RECORDS</span></li>
                        @endif

                        @if(auth()->user()->can('View my-class') || auth()->user()->can('View my-subject') || auth()->user()->can('View my-subject-vettings') || auth()->user()->can('View my-mock-subject-vettings')|| auth()->user()->can('View my-principals-comment'))
                            <li class="nav-item">
                                <a href="#sidebarClasses" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarClasses">
                                    <i class="ph-folder-open"></i> <span data-key="t-ecommerce">Classes & Subjects</span>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarClasses">
                                    <ul class="nav nav-sm flex-column">
                                        @can('View my-class')
                                            <li class="nav-item"><a href="{{ route('myclass.index') }}" class="nav-link" data-key="t-products">My Class</a></li>
                                        @endcan
                                        @can('View my-subject')
                                            <li class="nav-item"><a href="{{ route('mysubject.index') }}" class="nav-link" data-key="t-products">My Subject</a></li>
                                        @endcan
                                        @can('View my-subject-vettings')
                                            <li class="nav-item"><a href="{{ route('mysubjectvettings.index') }}" class="nav-link" data-key="t-products">Subjects to Vet</a></li>
                                        @endcan
                                        @can('View my-mock-subject-vettings')
                                            <li class="nav-item"><a href="{{ route('mymocksubjectvettings.index') }}" class="nav-link" data-key="t-products">Mock Subjects to Vet</a></li>
                                        @endcan
                                        @can('View my-principals-comment')
                                            <li class="nav-item"><a href="{{ route('myprincipalscomment.index') }}" class="nav-link" data-key="t-products">Principal's Comment</a></li>
                                        @endcan

                                    </ul>
                                </div>
                            </li>
                        @endif

                        {{-- ATTENDANCE --}}
                        @if(auth()->user()->can('View attendance-register') || auth()->user()->can('View attendance-class-summary') || auth()->user()->can('View attendance-student-report'))
                            <li class="nav-item">
                                <a href="#sidebarAttendance" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarAttendance">
                                    <i class="ph-calendar-check"></i> <span data-key="t-attendance">Attendance</span>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarAttendance">
                                    <ul class="nav nav-sm flex-column">
                                        @can('View attendance-register')
                                            <li class="nav-item"><a href="{{ route('attendance.my-classes') }}" class="nav-link">Mark Attendance</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            </li>
                        @endif

                        {{-- RECORDS AND RESULTS --}}
                        @if(auth()->user()->can('View myresult-room') || auth()->user()->can('View student-report') || auth()->user()->can('View student-mock-report')||auth()->user()->can('View admin-score-entry'))
                            <li class="nav-item">
                                <a href="#sidebarRecords" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarRecords">
                                    <i class="ph-folder-open"></i> <span data-key="t-ecommerce">Records and Results</span>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarRecords">
                                    <ul class="nav nav-sm flex-column">
                                        @can('View myresult-room')
                                            <li class="nav-item"><a href="{{ route('myresultroom.index') }}" class="nav-link" data-key="t-products">Terminal Records</a></li>
                                        @endcan
                                        @can('View student-report')
                                            <li class="nav-item"><a href="{{ route('studentreports.index') }}" class="nav-link" data-key="t-products">Terminal Result Reports</a></li>
                                            <li class="nav-item"><a href="{{ route('broadsheet.index') }}" class="nav-link" data-key="t-products">Terminal Result Broadsheet</a></li>
                                        @endcan
                                        @can('View student-mock-report')
                                            <li class="nav-item"><a href="{{ route('studentmockreports.index') }}" class="nav-link" data-key="t-products">Mock Result Reports</a></li>
                                        @endcan
                                         {{-- Option 1: Add under CLASSES & RECORDS section --}}
                                        @can('View admin-score-entry')
                                            <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-apps">ADMIN TOOLS</span></li>
                                            <li class="nav-item">
                                                <a href="{{ route('admin.score-entry.index') }}" class="nav-link">
                                                    <i class="ri-admin-line"></i> <span data-key="t-admin-score-entry">Admin Score Entry</span>
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                            </li>
                        @endif

                        {{-- TRANSCRIPTS --}}
                        @if(auth()->user()->can('View student-transcript') || auth()->user()->can('Preview student-transcript') || auth()->user()->can('Download student-transcript'))
                            <li class="menu-title"><i class="ph-folder-open"></i> <span data-key="t-apps">TRANSCRIPTS</span></li>
                            <li class="nav-item">
                                <a href="#sidebarTranscript" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarTranscript">
                                    <i class="ri-file-text-line"></i> <span data-key="t-transcript">Transcript</span>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarTranscript">
                                    <ul class="nav nav-sm flex-column">
                                        @can('View student-transcript')
                                            <li class="nav-item"><a href="{{ route('transcript.index') }}" class="nav-link">Generate Transcript</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            </li>
                        @endif

                        {{-- PROMOTION MANAGEMENT --}}
                        @if(auth()->user()->can('View myresult-room') || auth()->user()->can('View student-report') || auth()->user()->can('View student-mock-report'))
                            <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-apps">PROMOTION MANAGEMENT</span></li>
                            <li class="nav-item">
                                <a href="#sidebarPromotions" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarPromotions">
                                    <i class="ph-folder-open"></i> <span data-key="t-ecommerce">Promotion Management</span>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarPromotions">
                                    <ul class="nav nav-sm flex-column">
                                        @can('View myresult-room')
                                            <li class="nav-item"><a href="{{ route('promotions.index') }}" class="nav-link" data-key="t-products">Student Promotion</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            </li>
                        @endif

                        {{-- ============================================================
                             BURSARY & FINANCE
                             ============================================================ --}}
                        @if(auth()->user()->can('View school-payment') || auth()->user()->can('View analysis') ||
                            auth()->user()->can('View scholarship') || auth()->user()->can('View discount') ||
                            auth()->user()->can('View sibling groups') || auth()->user()->can('View financial reports') ||
                            auth()->user()->can('View payroll') || auth()->user()->can('View staff payments'))
                            <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-apps">BURSARY & FINANCE</span></li>
                        @endif

                        @can('View school-payment')
                        <li class="nav-item">
                            <a href="#sidebarStudentpayments" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarStudentpayments">
                                <i class="ph-storefront"></i> <span data-key="t-ecommerce">Student Payments</span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarStudentpayments">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('schoolpayment.index') }}" class="nav-link" data-key="t-products">Student Bill</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('payment.index') }}" class="nav-link" data-key="t-payments">
                                            <i class="ri-wallet-line"></i> Payment Portal
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        @endcan

                        @can('View analysis')
                        <li class="nav-item">
                            <a href="#sidebarAnalysis" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarAnalysis">
                                <i class="ph-storefront"></i> <span data-key="t-ecommerce">Payment Analysis</span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarAnalysis">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('analysis.index') }}" class="nav-link" data-key="t-products">School Payment Analysis</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        @endcan

                        @can('View scholarship')
                        <li class="nav-item">
                            <a href="#sidebarScholarship" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarScholarship">
                                <i class="ph-graduation-cap"></i> <span data-key="t-scholarship">Scholarship Management</span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarScholarship">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('admin.scholarship.index') }}" class="nav-link" data-key="t-all-scholarships">
                                            <i class="ri-list-check"></i> All Scholarships
                                        </a>
                                    </li>
                                    @can('Create scholarship')
                                    <li class="nav-item">
                                        <a href="{{ route('admin.scholarship.create') }}" class="nav-link" data-key="t-create-scholarship">
                                            <i class="ri-add-line"></i> Create Scholarship
                                        </a>
                                    </li>
                                    @endcan
                                    <li class="nav-item">
                                        <a href="{{ route('admin.scholarship.assignments') }}" class="nav-link" data-key="t-scholarship-assignments">
                                            <i class="ri-user-star-line"></i> Scholarship Assignments
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.scholarship.applications') }}" class="nav-link" data-key="t-scholarship-applications">
                                            <i class="ri-file-list-line"></i> Applications
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        @endcan

                        @can('View discount')
                        <li class="nav-item">
                            <a href="#sidebarDiscount" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarDiscount">
                                <i class="ph-tag"></i> <span data-key="t-discount">Discount Management</span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarDiscount">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('admin.discount.index') }}" class="nav-link" data-key="t-all-discounts">
                                            <i class="ri-list-check"></i> All Discounts
                                        </a>
                                    </li>
                                    @can('Create discount')
                                    <li class="nav-item">
                                        <a href="{{ route('admin.discount.create') }}" class="nav-link" data-key="t-create-discount">
                                            <i class="ri-add-line"></i> Create Discount
                                        </a>
                                    </li>
                                    @endcan
                                    <li class="nav-item">
                                        <a href="{{ route('admin.discount.assignments') }}" class="nav-link" data-key="t-discount-assignments">
                                            <i class="ri-user-settings-line"></i> Discount Assignments
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        @endcan

                        @can('View sibling groups')
                        <li class="nav-item">
                            <a href="#sidebarSibling" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarSibling">
                                <i class="ph-users"></i> <span data-key="t-sibling-groups">Sibling Groups</span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarSibling">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                       <a href="{{ route('sibling.index') }}" class="nav-link" data-key="t-all-groups">
                                            <i class="ri-group-line"></i> All Family Groups
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('sibling.create') }}" class="nav-link" data-key="t-create-group">
                                            <i class="ri-add-line"></i> Create Family Group
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        @endcan

                        @can('Manage payment gateways')
                        <li class="nav-item">
                            <a href="{{ route('admin.payment-gateways.index') }}" class="nav-link">
                                <i class="ph-credit-card"></i> <span data-key="t-payment-gateways">Payment Gateways</span>
                            </a>
                        </li>
                        @endcan


                        @can('View financial reports')
                            <li class="nav-item">
                                <a href="#sidebarAccounting" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarAccounting">
                                    <i class="ph-chart-line"></i> <span data-key="t-accounting">Accounting & Reports</span>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarAccounting">
                                    <ul class="nav nav-sm flex-column">
                                        {{-- Financial Reports --}}
                                        <li class="nav-item">
                                            <a href="{{ route('reports.financial.balance-sheet') }}" class="nav-link" data-key="t-balance-sheet">
                                                <i class="ri-file-copy-line"></i> Balance Sheet
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.financial.income-statement') }}" class="nav-link" data-key="t-income-statement">
                                                <i class="ri-bar-chart-line"></i> Income Statement
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.financial.trial-balance') }}" class="nav-link" data-key="t-trial-balance">
                                                <i class="ri-calculator-line"></i> Trial Balance
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.financial.cash-flow') }}" class="nav-link" data-key="t-cash-flow">
                                                <i class="ri-wallet-line"></i> Cash Flow
                                            </a>
                                        </li>

                                        <li class="dropdown-divider"></li>

                                        {{-- Financial Analysis Reports --}}
                                        <li class="nav-item">
                                            <a href="{{ route('reports.financial.debtors') }}" class="nav-link" data-key="t-debtors-list">
                                                <i class="ri-user-follow-line"></i> Student Debtors List
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.financial.collection-summary') }}" class="nav-link" data-key="t-collection-summary">
                                                <i class="ri-bar-chart-grouped-line"></i> Collection Summary
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.financial.scholarship-impact') }}" class="nav-link" data-key="t-scholarship-impact">
                                                <i class="ri-graduation-cap-line"></i> Scholarship Impact
                                            </a>
                                        </li>

                                        <li class="dropdown-divider"></li>

                                        {{-- School Analysis Reports --}}
                                        <li class="nav-item">
                                            <a href="{{ route('reports.analysis.index') }}" class="nav-link" data-key="t-class-analysis">
                                                <i class="ri-school-line"></i> Class Analysis
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.analysis.school-wide') }}" class="nav-link" data-key="t-school-wide-analysis">
                                                <i class="ri-building-line"></i> School-Wide Analysis
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.analysis.scholarship-impact') }}" class="nav-link" data-key="t-scholarship-impact-analysis">
                                                <i class="ri-gift-line"></i> Scholarship Impact Analysis
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        @endcan

                        @can('View payroll')
                        <li class="nav-item">
                            <a href="#sidebarPayroll" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarPayroll">
                                <i class="ph-money"></i> <span data-key="t-payroll">Payroll Management</span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarPayroll">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('payroll.periods') }}" class="nav-link" data-key="t-payroll-periods">
                                            <i class="ri-calendar-line"></i> Payroll Periods
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('payroll.summary') }}" class="nav-link" data-key="t-payroll-summary">
                                            <i class="ri-bar-chart-line"></i> Payroll Summary
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('payroll.statutory') }}" class="nav-link" data-key="t-statutory-report">
                                            <i class="ri-tax-line"></i> Statutory Report
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                                  <a href="{{ route('payroll.salary-structures') }}" class="nav-link" data-key="t-salary-structures">
                                            <i class="ri-bank-card-line"></i> Salary Structures
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        @endcan

                        @can('View staff payments')
                        <li class="nav-item">
                            <a href="#sidebarStaffPayments" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarStaffPayments">
                                <i class="ph-wallet"></i> <span data-key="t-staff-payments">Staff Payments</span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarStaffPayments">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('staff.payments.index') }}" class="nav-link" data-key="t-all-payments">
                                            <i class="ri-list-check"></i> All Payments
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('staff.payments.dashboard') }}" class="nav-link" data-key="t-my-payments">
                                            <i class="ri-briefcase-line"></i> My Payments
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        @endcan

                        {{-- SCHOOL BASIC SETTINGS --}}
                        @if(auth()->user()->can('View schoolinformation') || auth()->user()->can('View session') || auth()->user()->can('View term') || auth()->user()->can('View schoolhouse') || auth()->user()->can('View school-arm') || auth()->user()->can('View class-category') || auth()->user()->can('View school-class') || auth()->user()->can('View class-teacher') || auth()->user()->can('View subjects') || auth()->user()->can('View subject-teacher') || auth()->user()->can('View subject-class') || auth()->user()->can('View compulsory-subject') || auth()->user()->can('View principals-comment') || auth()->user()->can('View school-bills') || auth()->user()->can('View school-bill-for-term-session'))
                            <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-components">SCHOOL BASIC SETTINGS</span></li>
                        @endif

                        @can('View schoolinformation')
                            <li class="nav-item">
                                <a href="#sidebarSchoolInfo" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarSchoolInfo">
                                    <i class="ph-file-text"></i> <span data-key="t-invoices">School Information</span>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarSchoolInfo">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item"><a href="{{ route('school-information.index') }}" class="nav-link" data-key="t-list-view">School Information</a></li>
                                    </ul>
                                </div>
                            </li>
                        @endcan

                        @if(auth()->user()->can('View session') || auth()->user()->can('View term') || auth()->user()->can('View schoolhouse'))
                            <li class="nav-item">
                                <a href="#sidebarSession" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarSession">
                                    <i class="ph-file-text"></i> <span data-key="t-invoices">Session Term & House</span>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarSession">
                                    <ul class="nav nav-sm flex-column">
                                        @can('View session')
                                            <li class="nav-item"><a href="{{ route('session.index') }}" class="nav-link" data-key="t-list-view">School Session</a></li>
                                        @endcan
                                        @can('View term')
                                            <li class="nav-item"><a href="{{ route('term.index') }}" class="nav-link" data-key="t-overview">School Term</a></li>
                                        @endcan
                                        @can('View schoolhouse')
                                            <li class="nav-item"><a href="{{ route('schoolhouse.index') }}" class="nav-link" data-key="t-create-invoice">School House</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            </li>
                        @endif

                        @if(auth()->user()->can('View school-arm') || auth()->user()->can('View class-category') || auth()->user()->can('View school-class') || auth()->user()->can('View class-teacher'))
                            <li class="nav-item">
                                <a href="#sidebarClassessettings" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarClassessettings">
                                    <i class="ph-file-text"></i> <span data-key="t-invoices">Classes</span>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarClassessettings">
                                    <ul class="nav nav-sm flex-column">
                                        @can('View school-arm')
                                            <li class="nav-item"><a href="{{ route('schoolarm.index') }}" class="nav-link" data-key="t-list-view">Class Arm</a></li>
                                        @endcan
                                        @can('View class-category')
                                            <li class="nav-item"><a href="{{ route('classcategories.index') }}" class="nav-link" data-key="t-overview">Class Category</a></li>
                                        @endcan
                                        @can('View school-class')
                                            <li class="nav-item"><a href="{{ route('schoolclass.index') }}" class="nav-link" data-key="t-create-invoice">Class Name</a></li>
                                        @endcan
                                        @can('View class-teacher')
                                            <li class="nav-item"><a href="{{ route('classteacher.index') }}" class="nav-link" data-key="t-create-invoice">Class Teacher</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            </li>
                        @endif

                        @if(auth()->user()->can('View subjects') || auth()->user()->can('View subject-teacher') || auth()->user()->can('View subject-class') || auth()->user()->can('View compulsory-subject'))
                            <li class="nav-item">
                                <a href="#sidebarSub" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarSub">
                                    <i class="ph-file-text"></i> <span data-key="t-invoices">Subject</span>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarSub">
                                    <ul class="nav nav-sm flex-column">
                                        @can('View subjects')
                                            <li class="nav-item"><a href="{{ route('subject.index') }}" class="nav-link" data-key="t-list-view">Subject</a></li>
                                        @endcan
                                        @can('View subject-teacher')
                                            <li class="nav-item"><a href="{{ route('subjectteacher.index') }}" class="nav-link" data-key="t-overview">Assign Subject Teacher</a></li>
                                        @endcan
                                        @can('View subject-class')
                                            <li class="nav-item"><a href="{{ route('subjectclass.index') }}" class="nav-link" data-key="t-create-invoice">Assign Class Subject</a></li>
                                        @endcan
                                        @can('View compulsory-subject')
                                            <li class="nav-item"><a href="{{ route('compulsorysubjectclass.index') }}" class="nav-link" data-key="t-create-invoice">Assign Compulsory Subject to classes</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            </li>
                        @endif

                        {{-- ATTENDANCE ADMIN --}}
                        @if(auth()->user()->can('View attendance-settings') || auth()->user()->can('View attendance-holidays') || auth()->user()->can('View attendance-school-report'))
                            <li class="nav-item">
                                <a href="#sidebarAttendanceAdmin" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarAttendanceAdmin">
                                    <i class="ph-calendar-check"></i> <span data-key="t-attendance-admin">Attendance Admin</span>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarAttendanceAdmin">
                                    <ul class="nav nav-sm flex-column">
                                        @can('View attendance-settings')
                                            <li class="nav-item"><a href="{{ route('attendance.settings') }}" class="nav-link">Term Settings</a></li>
                                        @endcan
                                        @can('View attendance-holidays')
                                            <li class="nav-item"><a href="{{ route('attendance.holidays') }}" class="nav-link">Holidays & Breaks</a></li>
                                        @endcan
                                        @can('View attendance-school-report')
                                            <li class="nav-item"><a href="{{ route('attendance.school-report') }}" class="nav-link">School Report</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            </li>
                        @endif

                        @can('View principals-comment')
                            <li class="nav-item">
                                <a href="#sidebarPrincipal" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarPrincipal">
                                    <i class="ph-file-text"></i> <span data-key="t-invoices">Principal's Comments</span>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarPrincipal">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item"><a href="{{ route('principalscomment.index') }}" class="nav-link" data-key="t-list-view">Assign Staff</a></li>
                                    </ul>
                                </div>
                            </li>
                        @endcan

                        @can('View subjects')
                            <li class="nav-item">
                                <a href="#sidebarSubjectvetting" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarSubjectvetting">
                                    <i class="ph-file-text"></i> <span data-key="t-invoices">Terminal Subject Vettings</span>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarSubjectvetting">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item"><a href="{{ route('subjectvetting.index') }}" class="nav-link" data-key="t-list-view">Assign Subjects to Staff</a></li>
                                    </ul>
                                </div>
                            </li>
                        @endcan

                        @can('View subjects')
                            <li class="nav-item">
                                <a href="#mocksidebarSubjectvetting" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="mocksidebarSubjectvetting">
                                    <i class="ph-file-text"></i> <span data-key="t-invoices">Mock Subject Vettings</span>
                                </a>
                                <div class="collapse menu-dropdown" id="mocksidebarSubjectvetting">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item"><a href="{{ route('mocksubjectvetting.index') }}" class="nav-link" data-key="t-list-view">Assign Subjects to Staff</a></li>
                                    </ul>
                                </div>
                            </li>
                        @endcan

                        @if(auth()->user()->can('View school-bills') || auth()->user()->can('View school-bill-for-term-session'))
                            <li class="nav-item">
                                <a href="#sidebarBills" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarBills">
                                    <i class="ph-file-text"></i> <span data-key="t-invoices">School Bills</span>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarBills">
                                    <ul class="nav nav-sm flex-column">
                                        @can('View school-bills')
                                            <li class="nav-item"><a href="{{ route('schoolbill.index') }}" class="nav-link" data-key="t-list-view">Bills</a></li>
                                        @endcan
                                        @can('View school-bill-for-term-session')
                                            <li class="nav-item"><a href="{{ route('schoolbilltermsession.index') }}" class="nav-link" data-key="t-overview">Apply Bills</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            </li>
                        @endif

                    </ul>
                </div>
            </div>
        </div>
        <div class="sidebar-background"></div>

        <!-- Left Sidebar End -->
        <div class="vertical-overlay"></div>

        <!-- ========== Header ========== -->
        <header id="page-topbar">
            <div class="layout-width">
                <div class="navbar-header">
                    <div class="d-flex">
                        <div class="navbar-brand-box horizontal-logo">
                            <a href="index.html" class="logo logo-dark">
                                <span class="logo-sm"><img src="{{ asset('theme/layouts/assets/images/logo-sm.png')}}" alt="" height="22"></span>
                                <span class="logo-lg"><img src="{{ asset('theme/layouts/assets/images/logo-dark.png')}}" alt="" height="22"></span>
                            </a>
                            <a href="index.html" class="logo logo-light">
                                <span class="logo-sm"><img src="{{ asset('theme/layouts/assets/images/logo-sm.png')}}" alt="" height="22"></span>
                                <span class="logo-lg"><img src="{{ asset('theme/layouts/assets/images/logo-light.png')}}" alt="" height="22"></span>
                            </a>
                        </div>

                        <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger shadow-none" id="topnav-hamburger-icon">
                            <span class="hamburger-icon"><span></span><span></span><span></span></span>
                        </button>

                        {{-- SPOTLIGHT SEARCH TRIGGER BUTTON (replaces old search form) --}}
                        <div class="d-none d-md-inline-flex align-items-center">
                            <button type="button"
                                    id="spotlight-trigger"
                                    style="display:flex; align-items:center; gap:8px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); border-radius:10px; padding:7px 14px; cursor:pointer; transition:background 0.2s ease; min-width:220px;"
                                    onmouseover="this.style.background='rgba(255,255,255,0.13)'"
                                    onmouseout="this.style.background='rgba(255,255,255,0.08)'">
                                <i class="mdi mdi-magnify" style="font-size:16px; opacity:0.6;"></i>
                                <span style="font-size:13px; opacity:0.55; flex:1; text-align:left;">Search pages, students…</span>
                                <kbd style="font-size:10px; padding:1px 5px; border-radius:4px; background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.2); opacity:0.6;">⌘K</kbd>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        {{-- TOPBAR USER DROPDOWN BUTTON (FIXED AVATAR + ROLES) --}}
                        <div class="dropdown ms-sm-3 header-item topbar-user">
                            <button type="button"
                                    class="btn shadow-none p-0"
                                    id="page-header-user-dropdown"
                                    data-bs-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                    style="background: transparent; border: none; line-height: 1;">
                                <span class="d-flex align-items-center gap-2">

                                    @php
                                        use App\Models\User as UserModel;
                                        use App\Models\Student;
                                        use Illuminate\Support\Facades\Storage;
                                        use Illuminate\Support\Facades\Auth;

                                        $userdata   = Auth::user();
                                        $isStudent  = $userdata->hasRole('student');
                                        $fullName   = $userdata->name ?? 'User';
                                        $initials   = collect(explode(' ', $fullName))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->implode('');
                                        $fallback   = asset('assets/images/users/avatar-1.jpg');
                                        $srcPath    = null;

                                        if ($isStudent) {
                                            $student        = Student::where('id', $userdata->student_id)->first();
                                            $studentPicture = $student?->picture;
                                            if ($studentPicture) {
                                                $basename = basename($studentPicture);
                                                if (Storage::disk('public')->exists('student_avatars/' . $basename)) {
                                                    $srcPath = asset('storage/student_avatars/' . $basename);
                                                }
                                            }
                                        } else {
                                            if ($userdata->avatar) {
                                                $basename = basename($userdata->avatar);
                                                if (Storage::disk('public')->exists('staff_avatars/' . $basename)) {
                                                    $srcPath = asset('storage/staff_avatars/' . $basename);
                                                }
                                            }
                                        }

                                        $userRoles     = $userdata->roles->pluck('name');
                                        $roleBadgeMap  = [
                                            'admin'       => '#405189',
                                            'teacher'     => '#0a9396',
                                            'student'     => '#e76f51',
                                            'bursar'      => '#2a9d8f',
                                            'principal'   => '#6a0572',
                                            'parent'      => '#e9c46a',
                                            'staff'       => '#457b9d',
                                        ];
                                    @endphp

                                    {{-- AVATAR — fixed size, no cropping --}}
                                    <span style="display:inline-block; width:42px; height:42px; flex-shrink:0; position:relative;">
                                        @if($srcPath)
                                            <img
                                                id="topbar-avatar-img"
                                                src="{{ $srcPath }}"
                                                alt="{{ $fullName }}"
                                                class="header-profile-user-enhanced"
                                                style="width:42px; height:42px; border-radius:10px; object-fit:cover; object-position:center top; display:block; border:2px solid rgba(255,255,255,0.25);"
                                                data-bs-toggle="modal"
                                                data-bs-target="#imageViewModal"
                                                data-image="{{ $srcPath }}"
                                                onerror="this.style.display='none'; document.getElementById('topbar-avatar-fallback').style.display='flex';"
                                            >
                                            <span
                                                id="topbar-avatar-fallback"
                                                style="display:none; width:42px; height:42px; border-radius:10px; background:#405189; color:#fff; font-size:14px; font-weight:600; align-items:center; justify-content:center; position:absolute; top:0; left:0;">
                                                {{ $initials }}
                                            </span>
                                        @else
                                            <span
                                                id="topbar-avatar-fallback"
                                                style="display:flex; width:42px; height:42px; border-radius:10px; background:#405189; color:#fff; font-size:14px; font-weight:600; align-items:center; justify-content:center;">
                                                {{ $initials }}
                                            </span>
                                        @endif
                                    </span>

                                    {{-- Name + Role Badges (stacked, no shift) --}}
                                    <span class="d-none d-xl-flex flex-column align-items-start ms-1" style="line-height:1.2; gap:3px;">
                                        <span class="fw-medium" style="font-size:13px; color:inherit; white-space:nowrap; max-width:140px; overflow:hidden; text-overflow:ellipsis;">
                                            {{ $userdata->name }}
                                        </span>
                                        <span class="d-flex flex-wrap gap-1" style="max-width:160px;">
                                            @foreach($userRoles->take(3) as $roleName)
                                                @php
                                                    $lc    = strtolower($roleName);
                                                    $color = $roleBadgeMap[$lc] ?? '#6c757d';
                                                @endphp
                                                <span style="display:inline-block; font-size:9px; font-weight:600; text-transform:uppercase; letter-spacing:0.04em; padding:1px 6px; border-radius:20px; background:{{ $color }}22; color:{{ $color }}; border:1px solid {{ $color }}44; white-space:nowrap; line-height:1.6;">
                                                    {{ $roleName }}
                                                </span>
                                            @endforeach
                                            @if($userRoles->count() > 3)
                                                <span style="font-size:9px; color:#6c757d;">+{{ $userRoles->count()-3 }}</span>
                                            @endif
                                        </span>
                                    </span>

                                </span>
                            </button>

                            {{-- Dropdown menu with role badges --}}
                            <div class="dropdown-menu dropdown-menu-end">
                                <h6 class="dropdown-header">Welcome {{ $userdata->name }}!</h6>

                                <div class="px-3 pb-2 d-flex flex-wrap gap-1">
                                    @foreach($userRoles as $roleName)
                                        @php
                                            $lc    = strtolower($roleName);
                                            $color = $roleBadgeMap[$lc] ?? '#6c757d';
                                        @endphp
                                        <span style="display:inline-block; font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:0.04em; padding:2px 8px; border-radius:20px; background:{{ $color }}18; color:{{ $color }}; border:1px solid {{ $color }}33;">
                                            {{ $roleName }}
                                        </span>
                                    @endforeach
                                </div>

                                <div class="dropdown-divider"></div>
                                @if (!$isStudent)
                                    <a class="dropdown-item" href="{{ route('users.overview', $userdata->id) }}">
                                        <i class="mdi mdi-account-circle text-muted fs-lg align-middle me-1"></i>
                                        <span class="align-middle">Profile</span>
                                    </a>
                                @endif
                                <a class="dropdown-item" href="{{ route('profile.settings', ['id' => $userdata->id]) }}">
                                    <i class="mdi mdi-cog text-muted fs-lg align-middle me-1"></i>
                                    <span class="align-middle">Settings</span>
                                </a>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline" data-no-progress>
                                    @csrf
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); this.closest('form').submit();">
                                        <i class="mdi mdi-logout text-muted fs-lg align-middle me-1"></i>
                                        <span class="align-middle" data-key="t-logout">Logout</span>
                                    </a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Image View Modal -->
        <div class="modal fade" id="imageViewModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header"><h5 class="modal-title">Profile Photo</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body text-center p-4"><img id="enlargedImage" src="" alt="Profile" class="img-fluid rounded-3" style="max-height:400px;"></div>
                </div>
            </div>
        </div>

        {{-- ============================================================
             SPOTLIGHT SEARCH MODAL (macOS style)
             ============================================================ --}}
        <div id="spotlight-overlay"
             style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.45); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); align-items:flex-start; justify-content:center; padding-top:10vh;">

            <div id="spotlight-box"
                 style="width:100%; max-width:620px; margin:0 16px; background:rgba(30,32,40,0.92); border:1px solid rgba(255,255,255,0.12); border-radius:16px; box-shadow:0 32px 80px rgba(0,0,0,0.6); overflow:hidden; transform:scale(0.95); opacity:0; transition:transform 0.2s cubic-bezier(0.34,1.56,0.64,1), opacity 0.18s ease;">

                {{-- Search Input --}}
                <div style="display:flex; align-items:center; gap:12px; padding:16px 20px; border-bottom:1px solid rgba(255,255,255,0.08);">
                    <i class="mdi mdi-magnify" style="font-size:22px; color:rgba(255,255,255,0.5); flex-shrink:0;"></i>
                    <input
                        id="spotlight-input"
                        type="text"
                        placeholder="Search for pages, students, staff, classes…"
                        autocomplete="off"
                        style="flex:1; background:transparent; border:none; outline:none; font-size:16px; color:#fff; caret-color:#4f8ef7;"
                    >
                    <kbd id="spotlight-esc"
                         style="font-size:11px; padding:2px 7px; border-radius:5px; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.18); color:rgba(255,255,255,0.5); cursor:pointer; flex-shrink:0;">
                        ESC
                    </kbd>
                </div>

                {{-- Results Container --}}
                <div id="spotlight-results" style="max-height:420px; overflow-y:auto; padding:8px 0;">

                    {{-- Empty/Initial State --}}
                    <div id="spotlight-empty" style="padding:32px 20px; text-align:center; color:rgba(255,255,255,0.3);">
                        <i class="mdi mdi-lightning-bolt" style="font-size:32px; display:block; margin-bottom:8px; opacity:0.4;"></i>
                        <span style="font-size:13px;">Start typing to search…</span>
                    </div>

                    {{-- Results List --}}
                    <ul id="spotlight-list" style="list-style:none; margin:0; padding:0; display:none;"></ul>

                    {{-- Loading Spinner --}}
                    <div id="spotlight-loading" style="display:none; padding:20px; text-align:center;">
                        <div style="display:inline-block; width:20px; height:20px; border:2px solid rgba(255,255,255,0.15); border-top-color:#4f8ef7; border-radius:50%; animation:spin 0.7s linear infinite;"></div>
                    </div>
                </div>

                {{-- Footer Keyboard Hints --}}
                <div style="padding:10px 20px; border-top:1px solid rgba(255,255,255,0.07); display:flex; gap:16px; font-size:11px; color:rgba(255,255,255,0.3);">
                    <span><kbd style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.15); border-radius:3px; padding:0 4px;">↑↓</kbd> navigate</span>
                    <span><kbd style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.15); border-radius:3px; padding:0 4px;">↵</kbd> open</span>
                    <span><kbd style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.15); border-radius:3px; padding:0 4px;">ESC</kbd> close</span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        @yield('content')

        <!-- Footer -->
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <script>document.write(new Date().getFullYear())</script>
                        @php $school = App\Models\SchoolInformation::where('is_active', true)->first(); @endphp
                        {{ $school->school_name ?? 'Vite-ESchool' }}
                    </div>
                    <div class="col-sm-6">
                        <div class="text-sm-end d-none d-sm-block">Created by Qudroid Systems</div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Back to Top -->
    <button class="btn btn-dark btn-icon" id="back-to-top" title="Back to top">
        <i class="bi bi-caret-up fs-3xl"></i>
    </button>

    <!-- Preloader -->
    <div id="preloader"><div id="status"><div class="spinner-border text-primary avatar-sm" role="status"><span class="visually-hidden">Loading...</span></div></div></div>

    <!-- Customizer -->
    <div class="customizer-setting d-none d-md-block">
        <div class="btn btn-info p-2 text-uppercase rounded-end-0 shadow-lg" data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas" aria-controls="theme-settings-offcanvas">
            <i class="bi bi-gear mb-1"></i> Customizer
        </div>
    </div>

    <!-- Theme Settings Offcanvas -->
    <div class="offcanvas offcanvas-end border-0" tabindex="-1" id="theme-settings-offcanvas">
        <div class="d-flex align-items-center bg-primary bg-gradient p-3 offcanvas-header">
            <div class="me-2"><h5 class="mb-1 text-white">Steex Builder</h5><p class="text-white text-opacity-75 mb-0">Choose your themes & layouts etc.</p></div>
            <button type="button" class="btn-close btn-close-white ms-auto" id="customizerclose-btn" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            <div data-simplebar class="h-100">
                <div class="p-4">
                    <h6 class="fs-md mb-1">Layout</h6>
                    <p class="text-muted fs-sm">Choose your layout</p>
                    <div class="row">
                        <div class="col-4">
                            <div class="form-check card-radio">
                                <input id="customizer-layout01" name="data-layout" type="radio" value="vertical" class="form-check-input">
                                <label class="form-check-label p-0 avatar-md w-100" for="customizer-layout01">
                                    <span class="d-flex gap-1 h-100">
                                        <span class="flex-shrink-0"><span class="bg-light d-flex h-100 flex-column gap-1 p-1"><span class="d-block p-1 px-2 bg-primary-subtle rounded mb-2"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span></span></span>
                                        <span class="flex-grow-1"><span class="d-flex h-100 flex-column"><span class="bg-light d-block p-1"></span><span class="bg-light d-block p-1 mt-auto"></span></span></span>
                                    </span>
                                </label>
                            </div>
                            <h5 class="fs-sm text-center fw-medium mt-2">Vertical</h5>
                        </div>
                        <div class="col-4">
                            <div class="form-check card-radio">
                                <input id="customizer-layout02" name="data-layout" type="radio" value="horizontal" class="form-check-input">
                                <label class="form-check-label p-0 avatar-md w-100" for="customizer-layout02">
                                    <span class="d-flex h-100 flex-column gap-1">
                                        <span class="bg-light d-flex p-1 gap-1 align-items-center"><span class="d-block p-1 bg-primary-subtle rounded me-1"></span><span class="d-block p-1 pb-0 px-2 bg-primary-subtle ms-auto"></span><span class="d-block p-1 pb-0 px-2 bg-primary-subtle"></span></span>
                                        <span class="bg-light d-block p-1"></span><span class="bg-light d-block p-1 mt-auto"></span>
                                    </span>
                                </label>
                            </div>
                            <h5 class="fs-sm text-center fw-medium mt-2">Horizontal</h5>
                        </div>
                        <div class="col-4">
                            <div class="form-check card-radio">
                                <input id="customizer-layout03" name="data-layout" type="radio" value="twocolumn" class="form-check-input">
                                <label class="form-check-label p-0 avatar-md w-100" for="customizer-layout03">
                                    <span class="d-flex gap-1 h-100">
                                        <span class="flex-shrink-0"><span class="bg-light d-flex h-100 flex-column gap-1"><span class="d-block p-1 bg-primary-subtle mb-2"></span><span class="d-block p-1 pb-0 bg-primary-subtle"></span><span class="d-block p-1 pb-0 bg-primary-subtle"></span><span class="d-block p-1 pb-0 bg-primary-subtle"></span></span></span>
                                        <span class="flex-shrink-0"><span class="bg-light d-flex h-100 flex-column gap-1 p-1"><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span></span></span>
                                        <span class="flex-grow-1"><span class="d-flex h-100 flex-column"><span class="bg-light d-block p-1"></span><span class="bg-light d-block p-1 mt-auto"></span></span></span>
                                    </span>
                                </label>
                            </div>
                            <h5 class="fs-sm text-center fw-medium mt-2">Two Column</h5>
                        </div>
                    </div>

                    <h6 class="mt-4 fs-md mb-1">Theme</h6>
                    <p class="text-muted fs-sm">Choose your suitable Theme.</p>
                    <div class="row">
                        <div class="col-6"><div class="form-check card-radio"><input id="customizer-theme01" name="data-theme" type="radio" value="default" class="form-check-input"><label class="form-check-label p-0" for="customizer-theme01"><img src="{{ asset('theme/layouts/assets/images/custom-theme/light-mode.png')}}" alt="" class="img-fluid"></label></div><h5 class="fs-sm text-center fw-medium mt-2">Default</h5></div>
                        <div class="col-6"><div class="form-check card-radio"><input id="customizer-theme02" name="data-theme" type="radio" value="material" class="form-check-input"><label class="form-check-label p-0" for="customizer-theme02"><img src="{{ asset('theme/layouts/assets/images/custom-theme/material.png')}}" alt="" class="img-fluid"></label></div><h5 class="fs-sm text-center fw-medium mt-2">Material</h5></div>
                        <div class="col-6"><div class="form-check card-radio"><input id="customizer-theme03" name="data-theme" type="radio" value="creative" class="form-check-input"><label class="form-check-label p-0" for="customizer-theme03"><img src="{{ asset('theme/layouts/assets/images/custom-theme/creative.png')}}" alt="" class="img-fluid"></label></div><h5 class="fs-sm text-center fw-medium mt-2">Creative</h5></div>
                        <div class="col-6"><div class="form-check card-radio"><input id="customizer-theme04" name="data-theme" type="radio" value="minimal" class="form-check-input"><label class="form-check-label p-0" for="customizer-theme04"><img src="{{ asset('theme/layouts/assets/images/custom-theme/minimal.png')}}" alt="" class="img-fluid"></label></div><h5 class="fs-sm text-center fw-medium mt-2">Minimal</h5></div>
                        <div class="col-6"><div class="form-check card-radio"><input id="customizer-theme05" name="data-theme" type="radio" value="modern" class="form-check-input"><label class="form-check-label p-0" for="customizer-theme05"><img src="{{ asset('theme/layouts/assets/images/custom-theme/modern.png')}}" alt="" class="img-fluid"></label></div><h5 class="fs-sm text-center fw-medium mt-2">Modern</h5></div>
                        <div class="col-6"><div class="form-check card-radio"><input id="customizer-theme06" name="data-theme" type="radio" value="interaction" class="form-check-input"><label class="form-check-label p-0" for="customizer-theme06"><img src="{{ asset('theme/layouts/assets/images/custom-theme/interaction.png')}}" alt="" class="img-fluid"></label></div><h5 class="fs-sm text-center fw-medium mt-2">Interaction</h5></div>
                    </div>

                    <h6 class="mt-4 fs-md mb-1">Color Scheme</h6>
                    <p class="text-muted fs-sm">Choose Light or Dark Scheme.</p>
                    <div class="colorscheme-cardradio">
                        <div class="row g-3">
                            <div class="col-6"><div class="form-check card-radio"><input class="form-check-input" type="radio" name="data-bs-theme" id="layout-mode-light" value="light"><label class="form-check-label p-0 bg-transparent" for="layout-mode-light"><img src="{{ asset('theme/layouts/assets/images/custom-theme/light-mode.png')}}" alt="" class="img-fluid"></label></div><h5 class="fs-sm text-center fw-medium mt-2">Light</h5></div>
                            <div class="col-6"><div class="form-check card-radio dark"><input class="form-check-input" type="radio" name="data-bs-theme" id="layout-mode-dark" value="dark"><label class="form-check-label p-0 bg-transparent" for="layout-mode-dark"><img src="{{ asset('theme/layouts/assets/images/custom-theme/dark-mode.png')}}" alt="" class="img-fluid"></label></div><h5 class="fs-sm text-center fw-medium mt-2">Dark</h5></div>
                        </div>
                    </div>

                    <div id="layout-width">
                        <h6 class="mt-4 fs-md mb-1">Layout Width</h6>
                        <p class="text-muted fs-sm">Choose Fluid or Boxed layout.</p>
                        <div class="row">
                            <div class="col-4"><div class="form-check card-radio"><input class="form-check-input" type="radio" name="data-layout-width" id="layout-width-fluid" value="fluid"><label class="form-check-label p-0 avatar-md w-100" for="layout-width-fluid"><span class="d-flex gap-1 h-100"><span class="flex-shrink-0"><span class="bg-light d-flex h-100 flex-column gap-1 p-1"><span class="d-block p-1 px-2 bg-primary-subtle rounded mb-2"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span></span></span><span class="flex-grow-1"><span class="d-flex h-100 flex-column"><span class="bg-light d-block p-1"></span><span class="bg-light d-block p-1 mt-auto"></span></span></span></span></label></div><h5 class="fs-sm text-center fw-medium mt-2">Fluid</h5></div>
                            <div class="col-4"><div class="form-check card-radio"><input class="form-check-input" type="radio" name="data-layout-width" id="layout-width-boxed" value="boxed"><label class="form-check-label p-0 avatar-md w-100" for="layout-width-boxed"><span class="d-flex gap-1 h-100 border-start border-end"><span class="flex-shrink-0"><span class="bg-light d-flex h-100 flex-column gap-1 p-1"><span class="d-block p-1 px-2 bg-primary-subtle rounded mb-2"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span></span></span><span class="flex-grow-1"><span class="d-flex h-100 flex-column"><span class="bg-light d-block p-1"></span><span class="bg-light d-block p-1 mt-auto"></span></span></span></span></label></div><h5 class="fs-sm text-center fw-medium mt-2">Boxed</h5></div>
                        </div>
                    </div>

                    <div id="layout-position">
                        <h6 class="mt-4 fs-md mb-1">Layout Position</h6>
                        <p class="text-muted fs-sm">Choose Fixed or Scrollable Layout Position.</p>
                        <div class="btn-group radio" role="group">
                            <input type="radio" class="btn-check" name="data-layout-position" id="layout-position-fixed" value="fixed"><label class="btn btn-light w-sm" for="layout-position-fixed">Fixed</label>
                            <input type="radio" class="btn-check" name="data-layout-position" id="layout-position-scrollable" value="scrollable"><label class="btn btn-light w-sm ms-0" for="layout-position-scrollable">Scrollable</label>
                        </div>
                    </div>

                    <h6 class="mt-4 fs-md mb-1">Topbar Color</h6>
                    <p class="text-muted fs-sm">Choose Light or Dark Topbar Color.</p>
                    <div class="row">
                        <div class="col-4"><div class="form-check card-radio"><input class="form-check-input" type="radio" name="data-topbar" id="topbar-color-light" value="light"><label class="form-check-label p-0 avatar-md w-100" for="topbar-color-light"><span class="d-flex gap-1 h-100"><span class="flex-shrink-0"><span class="bg-light d-flex h-100 flex-column gap-1 p-1"><span class="d-block p-1 px-2 bg-primary-subtle rounded mb-2"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span></span></span><span class="flex-grow-1"><span class="d-flex h-100 flex-column"><span class="bg-light d-block p-1"></span><span class="bg-light d-block p-1 mt-auto"></span></span></span></span></label></div><h5 class="fs-sm text-center fw-medium mt-2">Light</h5></div>
                        <div class="col-4"><div class="form-check card-radio"><input class="form-check-input" type="radio" name="data-topbar" id="topbar-color-dark" value="dark"><label class="form-check-label p-0 avatar-md w-100" for="topbar-color-dark"><span class="d-flex gap-1 h-100"><span class="flex-shrink-0"><span class="bg-light d-flex h-100 flex-column gap-1 p-1"><span class="d-block p-1 px-2 bg-primary-subtle rounded mb-2"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span></span></span><span class="flex-grow-1"><span class="d-flex h-100 flex-column"><span class="bg-primary d-block p-1"></span><span class="bg-light d-block p-1 mt-auto"></span></span></span></span></label></div><h5 class="fs-sm text-center fw-medium mt-2">Dark</h5></div>
                    </div>

                    <div id="sidebar-size">
                        <h6 class="mt-4 fs-md mb-1">Sidebar Size</h6>
                        <p class="text-muted fs-sm">Choose a size of Sidebar.</p>
                        <div class="row">
                            <div class="col-4"><div class="form-check sidebar-setting card-radio"><input class="form-check-input" type="radio" name="data-sidebar-size" id="sidebar-size-default" value="lg"><label class="form-check-label p-0 avatar-md w-100" for="sidebar-size-default"><span class="d-flex gap-1 h-100"><span class="flex-shrink-0"><span class="bg-light d-flex h-100 flex-column gap-1 p-1"><span class="d-block p-1 px-2 bg-primary-subtle rounded mb-2"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span></span></span><span class="flex-grow-1"><span class="d-flex h-100 flex-column"><span class="bg-light d-block p-1"></span><span class="bg-light d-block p-1 mt-auto"></span></span></span></span></label></div><h5 class="fs-sm text-center fw-medium mt-2">Default</h5></div>
                            <div class="col-4"><div class="form-check sidebar-setting card-radio"><input class="form-check-input" type="radio" name="data-sidebar-size" id="sidebar-size-compact" value="md"><label class="form-check-label p-0 avatar-md w-100" for="sidebar-size-compact"><span class="d-flex gap-1 h-100"><span class="flex-shrink-0"><span class="bg-light d-flex h-100 flex-column gap-1 p-1"><span class="d-block p-1 bg-primary-subtle rounded mb-2"></span><span class="d-block p-1 pb-0 bg-primary-subtle"></span><span class="d-block p-1 pb-0 bg-primary-subtle"></span><span class="d-block p-1 pb-0 bg-primary-subtle"></span></span></span><span class="flex-grow-1"><span class="d-flex h-100 flex-column"><span class="bg-light d-block p-1"></span><span class="bg-light d-block p-1 mt-auto"></span></span></span></span></label></div><h5 class="fs-sm text-center fw-medium mt-2">Compact</h5></div>
                            <div class="col-4">
                                <div class="form-check sidebar-setting card-radio">
                                    <input class="form-check-input" type="radio" name="data-sidebar-size" id="sidebar-size-small" value="sm">
                                    <label class="form-check-label p-0 avatar-md w-100" for="sidebar-size-small">
                                        <span class="d-flex gap-1 h-100">
                                            <span class="flex-shrink-0"><span class="bg-light d-flex h-100 flex-column gap-1"><span class="d-block p-1 bg-primary-subtle mb-2"></span><span class="d-block p-1 pb-0 bg-primary-subtle"></span><span class="d-block p-1 pb-0 bg-primary-subtle"></span><span class="d-block p-1 pb-0 bg-primary-subtle"></span></span></span>
                                            <span class="flex-grow-1"><span class="d-flex h-100 flex-column"><span class="bg-light d-block p-1"></span><span class="bg-light d-block p-1 mt-auto"></span></span></span>
                                        </span>
                                    </label>
                                </div>
                                <h5 class="fs-sm text-center fw-medium mt-2">Small (Icon View)</h5>
                            </div>
                            <div class="col-4">
                                <div class="form-check sidebar-setting card-radio">
                                    <input class="form-check-input" type="radio" name="data-sidebar-size" id="sidebar-size-small-hover" value="sm-hover">
                                    <label class="form-check-label p-0 avatar-md w-100" for="sidebar-size-small-hover">
                                        <span class="d-flex gap-1 h-100">
                                            <span class="flex-shrink-0"><span class="bg-light d-flex h-100 flex-column gap-1"><span class="d-block p-1 bg-primary-subtle mb-2"></span><span class="d-block p-1 pb-0 bg-primary-subtle"></span><span class="d-block p-1 pb-0 bg-primary-subtle"></span><span class="d-block p-1 pb-0 bg-primary-subtle"></span></span></span>
                                            <span class="flex-grow-1"><span class="d-flex h-100 flex-column"><span class="bg-light d-block p-1"></span><span class="bg-light d-block p-1 mt-auto"></span></span></span>
                                        </span>
                                    </label>
                                </div>
                                <h5 class="fs-sm text-center fw-medium mt-2">Small Hover View</h5>
                            </div>
                        </div>
                    </div>

                    <div id="sidebar-view">
                        <h6 class="mt-4 fs-md mb-1">Sidebar View</h6>
                        <p class="text-muted fs-sm">Choose Default or Detached Sidebar view.</p>
                        <div class="row">
                            <div class="col-4">
                                <div class="form-check sidebar-setting card-radio">
                                    <input class="form-check-input" type="radio" name="data-layout-style" id="sidebar-view-default" value="default">
                                    <label class="form-check-label p-0 avatar-md w-100" for="sidebar-view-default">
                                        <span class="d-flex gap-1 h-100">
                                            <span class="flex-shrink-0"><span class="bg-light d-flex h-100 flex-column gap-1 p-1"><span class="d-block p-1 px-2 bg-primary-subtle rounded mb-2"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span></span></span>
                                            <span class="flex-grow-1"><span class="d-flex h-100 flex-column"><span class="bg-light d-block p-1"></span><span class="bg-light d-block p-1 mt-auto"></span></span></span>
                                        </span>
                                    </label>
                                </div>
                                <h5 class="fs-sm text-center fw-medium mt-2">Default</h5>
                            </div>
                            <div class="col-4">
                                <div class="form-check sidebar-setting card-radio">
                                    <input class="form-check-input" type="radio" name="data-layout-style" id="sidebar-view-detached" value="detached">
                                    <label class="form-check-label p-0 avatar-md w-100" for="sidebar-view-detached">
                                        <span class="d-flex h-100 flex-column">
                                            <span class="bg-light d-flex p-1 gap-1 align-items-center px-2"><span class="d-block p-1 bg-primary-subtle rounded me-1"></span><span class="d-block p-1 pb-0 px-2 bg-primary-subtle ms-auto"></span><span class="d-block p-1 pb-0 px-2 bg-primary-subtle"></span></span>
                                            <span class="d-flex gap-1 h-100 p-1 px-2"><span class="flex-shrink-0"><span class="bg-light d-flex h-100 flex-column gap-1 p-1"><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span></span></span></span>
                                            <span class="bg-light d-block p-1 mt-auto px-2"></span>
                                        </span>
                                    </label>
                                </div>
                                <h5 class="fs-sm text-center fw-medium mt-2">Detached</h5>
                            </div>
                        </div>
                    </div>

                    <div id="sidebar-color">
                        <h6 class="mt-4 fs-md mb-1">Sidebar Color</h6>
                        <p class="text-muted fs-sm">Choose a color of Sidebar.</p>
                        <div class="row">
                            <div class="col-4">
                                <div class="form-check sidebar-setting card-radio" data-bs-toggle="collapse" data-bs-target="#collapseBgGradient.show">
                                    <input class="form-check-input" type="radio" name="data-sidebar" id="sidebar-color-light" value="light">
                                    <label class="form-check-label p-0 avatar-md w-100" for="sidebar-color-light">
                                        <span class="d-flex gap-1 h-100"><span class="flex-shrink-0"><span class="bg-white border-end d-flex h-100 flex-column gap-1 p-1"><span class="d-block p-1 px-2 bg-primary-subtle rounded mb-2"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span></span></span><span class="flex-grow-1"><span class="d-flex h-100 flex-column"><span class="bg-light d-block p-1"></span><span class="bg-light d-block p-1 mt-auto"></span></span></span></span>
                                    </label>
                                </div>
                                <h5 class="fs-sm text-center fw-medium mt-2">Light</h5>
                            </div>
                            <div class="col-4">
                                <div class="form-check sidebar-setting card-radio" data-bs-toggle="collapse" data-bs-target="#collapseBgGradient.show">
                                    <input class="form-check-input" type="radio" name="data-sidebar" id="sidebar-color-dark" value="dark">
                                    <label class="form-check-label p-0 avatar-md w-100" for="sidebar-color-dark">
                                        <span class="d-flex gap-1 h-100"><span class="flex-shrink-0"><span class="bg-primary d-flex h-100 flex-column gap-1 p-1"><span class="d-block p-1 px-2 bg-soft-light rounded mb-2"></span><span class="d-block p-1 px-2 pb-0 bg-soft-light"></span><span class="d-block p-1 px-2 pb-0 bg-soft-light"></span><span class="d-block p-1 px-2 pb-0 bg-soft-light"></span></span></span><span class="flex-grow-1"><span class="d-flex h-100 flex-column"><span class="bg-light d-block p-1"></span><span class="bg-light d-block p-1 mt-auto"></span></span></span></span>
                                    </label>
                                </div>
                                <h5 class="fs-sm text-center fw-medium mt-2">Dark</h5>
                            </div>
                            <div class="col-4">
                                <button class="btn btn-link avatar-md w-100 p-0 overflow-hidden border collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBgGradient" aria-expanded="false" aria-controls="collapseBgGradient">
                                    <span class="d-flex gap-1 h-100"><span class="flex-shrink-0"><span class="bg-vertical-gradient d-flex h-100 flex-column gap-1 p-1"><span class="d-block p-1 px-2 bg-soft-light rounded mb-2"></span><span class="d-block p-1 px-2 pb-0 bg-soft-light"></span><span class="d-block p-1 px-2 pb-0 bg-soft-light"></span><span class="d-block p-1 px-2 pb-0 bg-soft-light"></span></span></span><span class="flex-grow-1"><span class="d-flex h-100 flex-column"><span class="bg-light d-block p-1"></span><span class="bg-light d-block p-1 mt-auto"></span></span></span></span>
                                </button>
                                <h5 class="fs-sm text-center fw-medium mt-2">Gradient</h5>
                            </div>
                        </div>
                        <div class="collapse" id="collapseBgGradient">
                            <div class="d-flex gap-2 flex-wrap img-switch p-2 px-3 bg-light rounded">
                                <div class="form-check sidebar-setting card-radio"><input class="form-check-input" type="radio" name="data-sidebar" id="sidebar-color-gradient" value="gradient"><label class="form-check-label p-0 avatar-xs rounded-circle" for="sidebar-color-gradient"><span class="avatar-title rounded-circle bg-vertical-gradient"></span></label></div>
                                <div class="form-check sidebar-setting card-radio"><input class="form-check-input" type="radio" name="data-sidebar" id="sidebar-color-gradient-2" value="gradient-2"><label class="form-check-label p-0 avatar-xs rounded-circle" for="sidebar-color-gradient-2"><span class="avatar-title rounded-circle bg-vertical-gradient-2"></span></label></div>
                                <div class="form-check sidebar-setting card-radio"><input class="form-check-input" type="radio" name="data-sidebar" id="sidebar-color-gradient-3" value="gradient-3"><label class="form-check-label p-0 avatar-xs rounded-circle" for="sidebar-color-gradient-3"><span class="avatar-title rounded-circle bg-vertical-gradient-3"></span></label></div>
                                <div class="form-check sidebar-setting card-radio"><input class="form-check-input" type="radio" name="data-sidebar" id="sidebar-color-gradient-4" value="gradient-4"><label class="form-check-label p-0 avatar-xs rounded-circle" for="sidebar-color-gradient-4"><span class="avatar-title rounded-circle bg-vertical-gradient-4"></span></label></div>
                            </div>
                        </div>
                    </div>

                    <div id="sidebar-img">
                        <h6 class="mt-4 fw-semibold fs-base">Sidebar Images</h6>
                        <p class="text-muted fs-sm">Choose a image of Sidebar.</p>
                        <div class="d-flex gap-2 flex-wrap img-switch">
                            <div class="form-check sidebar-setting card-radio"><input class="form-check-input" type="radio" name="data-sidebar-image" id="sidebarimg-none" value="none"><label class="form-check-label p-0 avatar-sm h-auto" for="sidebarimg-none"><span class="avatar-md w-auto bg-light d-flex align-items-center justify-content-center"><i class="ri-close-fill fs-3xl"></i></span></label></div>
                            <div class="form-check sidebar-setting card-radio"><input class="form-check-input" type="radio" name="data-sidebar-image" id="sidebarimg-01" value="img-1"><label class="form-check-label p-0 avatar-sm h-auto" for="sidebarimg-01"><img src="{{ asset('theme/layouts/assets/images/sidebar/img-sm-1.jpg')}}" alt="" class="avatar-md w-auto object-cover"></label></div>
                            <div class="form-check sidebar-setting card-radio"><input class="form-check-input" type="radio" name="data-sidebar-image" id="sidebarimg-02" value="img-2"><label class="form-check-label p-0 avatar-sm h-auto" for="sidebarimg-02"><img src="{{ asset('theme/layouts/assets/images/sidebar/img-sm-2.jpg')}}" alt="" class="avatar-md w-auto object-cover"></label></div>
                            <div class="form-check sidebar-setting card-radio"><input class="form-check-input" type="radio" name="data-sidebar-image" id="sidebarimg-03" value="img-3"><label class="form-check-label p-0 avatar-sm h-auto" for="sidebarimg-03"><img src="{{ asset('theme/layouts/assets/images/sidebar/img-sm-3.jpg')}}" alt="" class="avatar-md w-auto object-cover"></label></div>
                            <div class="form-check sidebar-setting card-radio"><input class="form-check-input" type="radio" name="data-sidebar-image" id="sidebarimg-04" value="img-4"><label class="form-check-label p-0 avatar-sm h-auto" for="sidebarimg-04"><img src="{{ asset('theme/layouts/assets/images/sidebar/img-sm-4.jpg')}}" alt="" class="avatar-md w-auto object-cover"></label></div>
                        </div>
                    </div>

                    <div id="preloader-menu">
                        <h6 class="mt-4 fw-semibold fs-base">Preloader</h6>
                        <p class="text-muted fs-sm">Choose a preloader.</p>
                        <div class="row">
                            <div class="col-4">
                                <div class="form-check sidebar-setting card-radio">
                                    <input class="form-check-input" type="radio" name="data-preloader" id="preloader-view-custom" value="enable">
                                    <label class="form-check-label p-0 avatar-md w-100" for="preloader-view-custom">
                                        <span class="d-flex gap-1 h-100"><span class="flex-shrink-0"><span class="bg-light d-flex h-100 flex-column gap-1 p-1"><span class="d-block p-1 px-2 bg-primary-subtle rounded mb-2"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span></span></span><span class="flex-grow-1"><span class="d-flex h-100 flex-column"><span class="bg-light d-block p-1"></span><span class="bg-light d-block p-1 mt-auto"></span></span></span></span>
                                        <span class="d-flex align-items-center justify-content-center"><span class="spinner-border text-primary avatar-xxs m-auto" role="status"><span class="visually-hidden">Loading...</span></span></span>
                                    </label>
                                </div>
                                <h5 class="fs-sm text-center fw-medium mt-2">Enable</h5>
                            </div>
                            <div class="col-4">
                                <div class="form-check sidebar-setting card-radio">
                                    <input class="form-check-input" type="radio" name="data-preloader" id="preloader-view-none" value="disable">
                                    <label class="form-check-label p-0 avatar-md w-100" for="preloader-view-none">
                                        <span class="d-flex gap-1 h-100"><span class="flex-shrink-0"><span class="bg-light d-flex h-100 flex-column gap-1 p-1"><span class="d-block p-1 px-2 bg-primary-subtle rounded mb-2"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span></span></span><span class="flex-grow-1"><span class="d-flex h-100 flex-column"><span class="bg-light d-block p-1"></span><span class="bg-light d-block p-1 mt-auto"></span></span></span></span>
                                    </label>
                                </div>
                                <h5 class="fs-sm text-center fw-medium mt-2">Disable</h5>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="offcanvas-footer border-top p-3 text-center">
            <div class="row">
                <div class="col-6">
                    <button type="button" class="btn btn-light w-100" id="reset-layout">Reset</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ====================================================
         Route-specific JS includes
         ==================================================== -->
    @if (Route::is('dashboard')) @include('layouts.pages-assets.js.dashboard-list-js') @endif
    @if (Route::is('users.*')) @include('layouts.pages-assets.js.users-list-js') @endif
    @if (Route::is('student-id-cards.*')) @include('layouts.pages-assets.js.idcard-list-js') @endif
    @if (Route::is('student.payments.*')) @include('layouts.pages-assets.js.studentpayment-list-js') @endif
    @if (Route::is('profile.*')) @include('layouts.pages-assets.js.users-list-js') @endif
    @if (Route::is('roles.*')) @include('layouts.pages-assets.js.role-list-js') @endif
    @if (Route::is('permissions.*')) @include('layouts.pages-assets.js.permissions-list-js') @endif
    @if (Route::is('session.*')) @include('layouts.pages-assets.js.session-list-js') @endif
    @if (Route::is('term.*')) @include('layouts.pages-assets.js.term-list-js') @endif
    @if (Route::is('school-information.*')) @include('layouts.pages-assets.js.schoolinformation-list-js') @endif
    @if (Route::is('admin.school-info.*')) @include('layouts.pages-assets.js.schoolinformation-list-js') @endif
    @if (Route::is('schoolhouse.*')) @include('layouts.pages-assets.js.schoolhouse-list-js') @endif
    @if (Route::is('schoolarm.*')) @include('layouts.pages-assets.js.arm-list-js') @endif
    @if (Route::is('classcategories.*')) @include('layouts.pages-assets.js.classcategory-list-js') @endif
    @if (Route::is('schoolclass.*')) @include('layouts.pages-assets.js.schoolclass-list-js') @endif
    @if (Route::is('classteacher.*')) @include('layouts.pages-assets.js.classteacher-list-js') @endif
    @if (Route::is('subject.*')) @include('layouts.pages-assets.js.subject-list-js') @endif
    @if (Route::is('subjects.*')) @include('layouts.pages-assets.js.subject-list-js') @endif
    @if (Route::is('subjectteacher.*')) @include('layouts.pages-assets.js.subjectteacher-list-js') @endif
    @if (Route::is('subjectclass.*')) @include('layouts.pages-assets.js.subjectclass-list-js') @endif
    @if (Route::is('schoolbill.*')) @include('layouts.pages-assets.js.schoolbill-list-js') @endif
    @if (Route::is('schoolbilltermsession.*')) @include('layouts.pages-assets.js.schoolbilltermsession-list-js') @endif
    @if (Route::is('student.*')) @include('layouts.pages-assets.js.student-list-js') @endif
    @if (Route::is('studentbatchindex')) @include('layouts.pages-assets.js.studentbatch-list-js') @endif
    @if (Route::is('myclass.*')) @include('layouts.pages-assets.js.myclass-list-js') @endif
    @if (Route::is('mysubject.*')) @include('layouts.pages-assets.js.mysubject-list-js') @endif
    @if (Route::is('viewstudent')) @include('layouts.pages-assets.js.viewstudent-list-js') @endif
    @if (Route::is('studentreports.*')) @include('layouts.pages-assets.js.studentreport-list-js') @endif
    @if (Route::is('broadsheet.*')) @include('layouts.pages-assets.js.studentreport-list-js') @endif
    @if (Route::is('studentmockreports.*')) @include('layouts.pages-assets.js.studentmockreport-list-js') @endif
    @if (Route::is('subjectoperation.*')) @include('layouts.pages-assets.js.subjectoperation-list-js') @endif
    @if (Route::is('subjects.subjectinfo')) @include('layouts.pages-assets.js.subjectinfo-list-js') @endif
    @if (Route::is('myresultroom.*')) @include('layouts.pages-assets.js.myresultroom-list-js') @endif
    @if (Route::is('assessment.*')) @include('layouts.pages-assets.js.subjectscoresheet-list-js') @endif
    @if (Route::is('assessments')) @include('layouts.pages-assets.js.studentassessment-list-js') @endif
    @if (Route::is('subjectscoresheet')) @include('layouts.pages-assets.js.subjectscoresheet-list-js') @endif
    @if (Route::is('subjectscoresheet-mock.*')) @include('layouts.pages-assets.js.subjectscoresheet-mock-list-js') @endif
    @if (Route::is('studentresults*')) @include('layouts.pages-assets.js.studentresults-list-js') @endif
    @if (Route::is('schoolbill*')) @include('layouts.pages-assets.js.schoolbill-list-js') @endif
    @if (Route::is('schoolpayment*')) @include('layouts.pages-assets.js.schoolpayment-list-js') @endif
    @if (Route::is('analysis*')) @include('layouts.pages-assets.js.analysis-list-js') @endif
    @if (Route::is('exams*')) @include('layouts.pages-assets.js.exams-list-js') @endif
    @if (Route::is('questions*')) @include('layouts.pages-assets.js.questions-list-js') @endif
    @if (Route::is('cbt*')) @include('layouts.pages-assets.js.cbt-list-js') @endif
    @if (Route::is('classbroadsheet.*')) @include('layouts.pages-assets.js.classbroadsheet-list-js') @endif
    @if (Route::is('principalscomment.*')) @include('layouts.pages-assets.js.principalscomment-list-js') @endif
    @if (Route::is('myprincipalscomment.*')) @include('layouts.pages-assets.js.myprincipalscomment-list-js') @endif
    @if (Route::is('compulsorysubjectclass.*')) @include('layouts.pages-assets.js.compulsorysubjectclass-list-js') @endif
    @if (Route::is('subjectvetting.*')) @include('layouts.pages-assets.js.subjectvetting-list-js') @endif
    @if (Route::is('mocksubjectvetting.*')) @include('layouts.pages-assets.js.mocksubjectvetting-list-js') @endif
    @if (Route::is('mysubjectvettings.*')) @include('layouts.pages-assets.js.mysubjectvettings-list-js') @endif
    @if (Route::is('mymocksubjectvettings.*')) @include('layouts.pages-assets.js.timetable-list-js') @endif
    @if (Route::is('timetable.*')) @include('layouts.pages-assets.js.timetable-list-js') @endif
    @if (Route::is('rooms.*')) @include('layouts.pages-assets.js.rooms-list-js') @endif
    @if (Route::is('promotions.*')) @include('layouts.pages-assets.js.promotions-list-js') @endif
    @if (Route::is('attendance.*')) @include('layouts.pages-assets.js.attendance-list-js') @endif
    @if (Route::is('transcript.*')) @include('layouts.pages-assets.js.attendance-list-js') @endif
    @if (Route::is('admin.score-entry.*')) @include('layouts.pages-assets.js.adminscoreentry-list-js') @endif

    {{-- Finance Module JS --}}
    @if (Route::is('admin.scholarship.*') || Route::is('admin.discount.*') || Route::is('sibling.*') ||
        Route::is('payment.*') || Route::is('reports.financial.*') || Route::is('reports.analysis.*') ||
        Route::is('payroll.*') || Route::is('staff.payments.*'))
        @include('layouts.pages-assets.js.scholarship-list-js')
    @endif

    <!-- ====================================================
         MASTER ENHANCEMENT SCRIPTS
         ==================================================== -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {

        // =====================================================
        // 1. NPROGRESS — page transition bar
        // =====================================================
        if (typeof NProgress !== 'undefined') {
            NProgress.configure({ showSpinner: false, speed: 400, minimum: 0.1 });

            // Start bar on any real navigation link
            document.querySelectorAll('a[href]').forEach(function (a) {
                var href = a.getAttribute('href');
                if (
                    href &&
                    !href.startsWith('#') &&
                    !href.startsWith('javascript') &&
                    !href.startsWith('mailto') &&
                    !href.startsWith('tel') &&
                    !a.hasAttribute('data-bs-toggle') &&
                    !a.hasAttribute('data-bs-dismiss') &&
                    a.getAttribute('target') !== '_blank'
                ) {
                    a.addEventListener('click', function () {
                        NProgress.start();
                    });
                }
            });

            // Done when page fully loads / restores from bfcache
            window.addEventListener('pageshow', function () { NProgress.done(); });
            window.addEventListener('load', function () { NProgress.done(); });
        }

        // =====================================================
        // 2. ACTIVE LINK DETECTION — auto-open parent & scroll
        // =====================================================
        (function () {
            var currentPath = window.location.pathname;

            // Walk every child nav-link inside the sidebar
            var childLinks = document.querySelectorAll('#navbar-nav .nav-sm a.nav-link');

            childLinks.forEach(function (link) {
                try {
                    var linkPath = new URL(link.href, window.location.origin).pathname;
                    // Match exact path OR sub-path (for nested routes)
                    var isActive = linkPath === currentPath ||
                                   (linkPath.length > 1 && currentPath.startsWith(linkPath));

                    if (!isActive) return;

                    // Mark child as active
                    link.classList.add('nav-active-child');

                    // Walk up to the parent collapse div
                    var parentCollapse = link.closest('.collapse');
                    if (parentCollapse) {
                        // Bootstrap: add 'show' to open the collapse
                        parentCollapse.classList.add('show');

                        // Find the toggle button / anchor that controls this collapse
                        var collapseId = parentCollapse.getAttribute('id');
                        var parentToggle = document.querySelector(
                            '[data-bs-target="#' + collapseId + '"], [href="#' + collapseId + '"]'
                        );

                        if (parentToggle) {
                            parentToggle.setAttribute('aria-expanded', 'true');
                            parentToggle.classList.remove('collapsed');
                            parentToggle.classList.add('nav-active-parent');
                        }
                    }

                    // Smooth scroll the active link into the sidebar's visible area
                    setTimeout(function () {
                        link.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }, 350);

                } catch (e) {
                    // Silently ignore malformed hrefs
                }
            });
        })();

        // =====================================================
        // 3. RIPPLE EFFECT on nav-link clicks
        // =====================================================
        document.querySelectorAll('#navbar-nav .nav-link').forEach(function (link) {
            link.addEventListener('click', function (e) {
                // Don't add ripple to dropdown toggles (they open/close, no nav)
                if (link.hasAttribute('data-bs-toggle')) return;

                var ripple = document.createElement('span');
                ripple.classList.add('nav-ripple');

                var rect     = link.getBoundingClientRect();
                var size     = Math.max(rect.width, rect.height);
                var x        = e.clientX - rect.left - size / 2;
                var y        = e.clientY - rect.top  - size / 2;

                ripple.style.cssText =
                    'width:'  + size + 'px;' +
                    'height:' + size + 'px;' +
                    'left:'   + x    + 'px;' +
                    'top:'    + y    + 'px;';

                link.appendChild(ripple);

                setTimeout(function () {
                    if (ripple.parentNode) ripple.parentNode.removeChild(ripple);
                }, 650);
            });
        });

        // =====================================================
        // 4. BACK-TO-TOP button — show after 300px scroll
        // =====================================================
        var backToTop = document.getElementById('back-to-top');
        if (backToTop) {
            window.addEventListener('scroll', function () {
                if (window.scrollY > 300) {
                    backToTop.classList.add('show');
                } else {
                    backToTop.classList.remove('show');
                }
            }, { passive: true });

            backToTop.addEventListener('click', function () {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

        // =====================================================
        // 5. IMAGE MODAL — profile photo enlargement
        // =====================================================
        var imageModal = document.getElementById('imageViewModal');
        if (imageModal) {
            imageModal.addEventListener('show.bs.modal', function (event) {
                var button     = event.relatedTarget;
                var imageSrc   = button ? button.getAttribute('data-image') : null;
                var enlargedImg = document.getElementById('enlargedImage');
                if (enlargedImg && imageSrc) {
                    enlargedImg.src = imageSrc;
                }
            });
        }

        // =====================================================
        // 6. RESET LAYOUT button
        // =====================================================
        var resetBtn = document.getElementById('reset-layout');
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                localStorage.clear();
                location.reload();
            });
        }

        // =====================================================
        // 7. FORM SUBMISSION — start NProgress on any form submit
        // =====================================================
        document.querySelectorAll('form').forEach(function (form) {
            if (form.getAttribute('action') && !form.dataset.noProgress) {
                form.addEventListener('submit', function () {
                    if (typeof NProgress !== 'undefined') {
                        NProgress.start();
                    }
                });
            }
        });

        // =====================================================
        // 8. ACTIVE STATE on direct (non-collapse) nav links
        // =====================================================
        (function () {
            var currentPath = window.location.pathname;
            var directLinks = document.querySelectorAll('#navbar-nav > li > a.nav-link:not(.menu-link)');
            directLinks.forEach(function (link) {
                try {
                    var linkPath = new URL(link.href, window.location.origin).pathname;
                    if (linkPath === currentPath) {
                        link.classList.add('nav-active-parent');
                    }
                } catch (e) {}
            });
        })();

    }); // end DOMContentLoaded
    </script>

    <!-- ====================================================
         SPOTLIGHT SEARCH JAVASCRIPT
         ==================================================== -->
    <script>
    (function () {
        // ============================================================
        // STATIC PAGE INDEX (all menu links)
        // ============================================================
        var STATIC_PAGES = [
            { title: 'Administration Dashboard',    url: '{{ route("dashboard") }}',                              icon: 'mdi-gauge',          category: 'Dashboards' },
            { title: 'User Management',             url: '{{ route("users.index") }}',                            icon: 'mdi-account-group',  category: 'Users & Privileges' },
            { title: 'Roles & Permissions',         url: '{{ route("roles.index") }}',                            icon: 'mdi-shield-account', category: 'Users & Privileges' },
            { title: 'All Students',                url: '{{ route("student.index") }}',                          icon: 'mdi-school',         category: 'Students' },
            { title: 'Batch Student Registration',  url: '{{ route("studentbatchindex") }}',                      icon: 'mdi-account-multiple-plus', category: 'Students' },
            { title: 'ID Card Generator',           url: '{{ route("student-id-cards.index") }}',                 icon: 'mdi-card-account-details', category: 'Students' },
            { title: 'My Profile',                  url: '{{ route("users.overview", ["id" => Auth::id()]) }}',   icon: 'mdi-account-circle', category: 'My Account' },
            { title: 'Account Settings',            url: '{{ route("profile.settings", ["id" => Auth::id()]) }}', icon: 'mdi-cog',            category: 'My Account' },
            { title: 'School Information',          url: '{{ route("school-information.index") }}',               icon: 'mdi-domain',         category: 'School Settings' },
            { title: 'School Session',              url: '{{ route("session.index") }}',                          icon: 'mdi-calendar-range', category: 'School Settings' },
            { title: 'School Term',                 url: '{{ route("term.index") }}',                             icon: 'mdi-calendar',       category: 'School Settings' },
            { title: 'School House',                url: '{{ route("schoolhouse.index") }}',                      icon: 'mdi-home-group',     category: 'School Settings' },
            { title: 'Class Arm',                   url: '{{ route("schoolarm.index") }}',                        icon: 'mdi-table-chair',    category: 'Classes' },
            { title: 'Class Category',              url: '{{ route("classcategories.index") }}',                  icon: 'mdi-format-list-bulleted', category: 'Classes' },
            { title: 'Class Name',                  url: '{{ route("schoolclass.index") }}',                      icon: 'mdi-google-classroom', category: 'Classes' },
            { title: 'Class Teacher',               url: '{{ route("classteacher.index") }}',                     icon: 'mdi-human-male-board', category: 'Classes' },
            { title: 'Subject',                     url: '{{ route("subject.index") }}',                          icon: 'mdi-book-open-variant', category: 'Subjects' },
            { title: 'Assign Subject Teacher',      url: '{{ route("subjectteacher.index") }}',                   icon: 'mdi-account-tie',    category: 'Subjects' },
            { title: 'Assign Class Subject',        url: '{{ route("subjectclass.index") }}',                     icon: 'mdi-book-plus',      category: 'Subjects' },
            { title: 'Student Subject Registration', url: '{{ route("subjectoperation.index") }}',                icon: 'mdi-clipboard-list', category: 'Subject Registration' },
            { title: 'My Class',                    url: '{{ route("myclass.index") }}',                          icon: 'mdi-google-classroom', category: 'Classes & Records' },
            { title: 'My Subject',                  url: '{{ route("mysubject.index") }}',                        icon: 'mdi-book-open',      category: 'Classes & Records' },
            { title: 'Terminal Records',            url: '{{ route("myresultroom.index") }}',                     icon: 'mdi-file-chart',     category: 'Records & Results' },
            { title: 'Terminal Result Reports',     url: '{{ route("studentreports.index") }}',                   icon: 'mdi-file-document',  category: 'Records & Results' },
            { title: 'Terminal Result Broadsheet',  url: '{{ route("broadsheet.index") }}',                       icon: 'mdi-table-large',    category: 'Records & Results' },
            { title: 'Mock Result Reports',         url: '{{ route("studentmockreports.index") }}',               icon: 'mdi-file-document-edit', category: 'Records & Results' },
            { title: 'Student Promotions',          url: '{{ route("promotions.index") }}',                       icon: 'mdi-arrow-up-circle', category: 'Promotions' },
            { title: 'Student Bill',                url: '{{ route("schoolpayment.index") }}',                    icon: 'mdi-receipt',        category: 'Finance' },
            { title: 'Payment Portal',              url: '{{ route("payment.index") }}',                          icon: 'mdi-wallet',         category: 'Finance' },
            { title: 'All Scholarships',            url: '{{ route("admin.scholarship.index") }}',                icon: 'mdi-medal',          category: 'Finance' },
            { title: 'All Discounts',               url: '{{ route("admin.discount.index") }}',                   icon: 'mdi-tag-multiple',   category: 'Finance' },
            { title: 'Sibling Family Groups',       url: '{{ route("sibling.index") }}',                          icon: 'mdi-account-multiple', category: 'Finance' },
            { title: 'Payroll Periods',             url: '{{ route("payroll.periods") }}',                        icon: 'mdi-calendar-clock', category: 'Payroll' },
            { title: 'Payroll Summary',             url: '{{ route("payroll.summary") }}',                        icon: 'mdi-cash-multiple',  category: 'Payroll' },
            { title: 'All Examinations',            url: '{{ route("exams.index") }}',                            icon: 'mdi-clipboard-text', category: 'Exams & CBT' },
            { title: 'Questions Management',        url: '{{ route("questions.all") }}',                          icon: 'mdi-help-circle',    category: 'Exams & CBT' },
            { title: 'CBT Exercise',                url: '{{ route("cbt.index") }}',                              icon: 'mdi-monitor',        category: 'Exams & CBT' },
            { title: 'Timetable',                   url: '{{ route("timetable.index") }}',                        icon: 'mdi-table-clock',    category: 'Timetable' },
            { title: 'Mark Attendance',             url: '{{ route("attendance.my-classes") }}',                  icon: 'mdi-clipboard-check', category: 'Attendance' },
            { title: 'Principal\'s Comment',        url: '{{ route("principalscomment.index") }}',                icon: 'mdi-comment-text',   category: 'Records' },
            { title: 'Balance Sheet',               url: '{{ route("reports.financial.balance-sheet") }}',        icon: 'mdi-scale-balance',  category: 'Accounting' },
            { title: 'Income Statement',            url: '{{ route("reports.financial.income-statement") }}',     icon: 'mdi-chart-line',     category: 'Accounting' },
            { title: 'Cash Flow',                   url: '{{ route("reports.financial.cash-flow") }}',            icon: 'mdi-cash-refund',    category: 'Accounting' },
            { title: 'Student Debtors List',        url: '{{ route("reports.financial.debtors") }}',              icon: 'mdi-account-alert',  category: 'Accounting' },
            { title: 'Transcript',                  url: '{{ route("transcript.index") }}',                       icon: 'mdi-file-account',   category: 'Transcripts' },
        ];

        // Category colors for visual grouping
        var CAT_COLORS = {
            'Dashboards':           '#4f8ef7',
            'Users & Privileges':   '#405189',
            'Students':             '#e76f51',
            'My Account':           '#2a9d8f',
            'School Settings':      '#6a0572',
            'Classes':              '#0a9396',
            'Subjects':             '#e9c46a',
            'Subject Registration': '#e9c46a',
            'Classes & Records':    '#0a9396',
            'Records & Results':    '#457b9d',
            'Promotions':           '#2a9d8f',
            'Finance':              '#10b981',
            'Payroll':              '#e76f51',
            'Exams & CBT':          '#f4a261',
            'Timetable':            '#4f8ef7',
            'Attendance':           '#e9c46a',
            'Records':              '#6a0572',
            'Accounting':           '#10b981',
            'Transcripts':          '#457b9d',
        };

        // DOM Elements
        var overlay      = document.getElementById('spotlight-overlay');
        var box          = document.getElementById('spotlight-box');
        var input        = document.getElementById('spotlight-input');
        var emptyState   = document.getElementById('spotlight-empty');
        var loadingEl    = document.getElementById('spotlight-loading');
        var list         = document.getElementById('spotlight-list');
        var trigger      = document.getElementById('spotlight-trigger');
        var escBtn       = document.getElementById('spotlight-esc');

        var debounceTimer  = null;
        var activeIndex    = -1;
        var currentResults = [];

        // Open Spotlight
        function openSpotlight() {
            overlay.style.display = 'flex';
            requestAnimationFrame(function () {
                box.style.transform = 'scale(1)';
                box.style.opacity   = '1';
            });
            setTimeout(function () { input.focus(); }, 50);
        }

        // Close Spotlight
        function closeSpotlight() {
            box.style.transform = 'scale(0.95)';
            box.style.opacity   = '0';
            setTimeout(function () {
                overlay.style.display = 'none';
                input.value = '';
                showEmpty();
            }, 180);
        }

        // Event Listeners
        if (trigger) trigger.addEventListener('click', openSpotlight);
        if (escBtn)  escBtn.addEventListener('click',  closeSpotlight);

        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) closeSpotlight();
        });

        // Keyboard Shortcuts (Cmd/Ctrl + K)
        document.addEventListener('keydown', function (e) {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                overlay.style.display === 'flex' ? closeSpotlight() : openSpotlight();
            }
            if (e.key === 'Escape' && overlay.style.display === 'flex') {
                closeSpotlight();
            }
        });

        // Arrow/Enter Navigation
        input.addEventListener('keydown', function (e) {
            var items = list.querySelectorAll('li[data-idx]');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, items.length - 1);
                highlightItem(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                highlightItem(items);
            } else if (e.key === 'Enter') {
                if (activeIndex >= 0 && currentResults[activeIndex]) {
                    window.location.href = currentResults[activeIndex].url;
                }
            }
        });

        function highlightItem(items) {
            items.forEach(function (li, i) {
                var isActive = i === activeIndex;
                li.style.background = isActive ? 'rgba(79,142,247,0.18)' : '';
                var titleSpan = li.querySelector('.result-title');
                if (titleSpan) {
                    titleSpan.style.color = isActive ? '#4f8ef7' : '#fff';
                }
                if (isActive) li.scrollIntoView({ block: 'nearest' });
            });
        }

        // UI Helpers
        function showEmpty() {
            emptyState.style.display  = 'block';
            loadingEl.style.display   = 'none';
            list.style.display        = 'none';
            list.innerHTML            = '';
            currentResults            = [];
            activeIndex               = -1;
        }

        function showLoading() {
            emptyState.style.display = 'none';
            loadingEl.style.display  = 'block';
            list.style.display       = 'none';
        }

        // Render Results with Grouping
        function renderResults(results) {
            loadingEl.style.display  = 'none';
            emptyState.style.display = 'none';
            list.innerHTML           = '';
            activeIndex              = -1;
            currentResults           = results;

            if (!results.length) {
                emptyState.innerHTML = '<i class="mdi mdi-magnify-close" style="font-size:32px; display:block; margin-bottom:8px; opacity:0.4;"></i><span style="font-size:13px;">No results found</span>';
                emptyState.style.display = 'block';
                list.style.display    = 'none';
                return;
            }

            emptyState.innerHTML = '<i class="mdi mdi-lightning-bolt" style="font-size:32px; display:block; margin-bottom:8px; opacity:0.4;"></i><span style="font-size:13px;">Start typing to search…</span>';

            // Group by category
            var grouped = {};
            results.forEach(function (r) {
                if (!grouped[r.category]) grouped[r.category] = [];
                grouped[r.category].push(r);
            });

            var idx = 0;
            Object.keys(grouped).forEach(function (cat) {
                // Category Header
                var header = document.createElement('li');
                header.style.cssText = 'padding:6px 20px 3px; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:rgba(255,255,255,0.3);';
                header.textContent   = cat;
                list.appendChild(header);

                grouped[cat].forEach(function (r) {
                    var li = document.createElement('li');
                    li.setAttribute('data-idx', idx);
                    li.style.cssText = 'display:flex; align-items:center; gap:12px; padding:9px 20px; cursor:pointer; transition:background 0.1s ease; border-radius:6px; margin:0 8px;';

                    var accentColor = CAT_COLORS[r.category] || '#4f8ef7';

                    var iconWrap = document.createElement('span');
                    iconWrap.style.cssText = 'width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; background:' + accentColor + '22;';

                    var icon = document.createElement('i');
                    icon.className   = (r.icon || 'mdi-chevron-right') + ' mdi';
                    icon.style.cssText = 'font-size:16px; color:' + accentColor + ';';
                    iconWrap.appendChild(icon);

                    var textWrap = document.createElement('span');
                    textWrap.style.cssText = 'flex:1; min-width:0;';

                    var title = document.createElement('span');
                    title.className    = 'result-title';
                    title.style.cssText = 'display:block; font-size:14px; font-weight:500; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;';
                    title.textContent  = r.title;

                    var sub = document.createElement('span');
                    sub.style.cssText  = 'display:block; font-size:11px; color:rgba(255,255,255,0.35);';
                    sub.textContent    = r.subtitle || r.category;

                    textWrap.appendChild(title);
                    textWrap.appendChild(sub);

                    var arrow = document.createElement('i');
                    arrow.className    = 'mdi mdi-arrow-right';
                    arrow.style.cssText = 'font-size:14px; color:rgba(255,255,255,0.2); flex-shrink:0;';

                    li.appendChild(iconWrap);
                    li.appendChild(textWrap);
                    li.appendChild(arrow);

                    (function (item, itemIdx) {
                        li.addEventListener('mouseenter', function () {
                            li.style.background = 'rgba(79,142,247,0.12)';
                            activeIndex = itemIdx;
                        });
                        li.addEventListener('mouseleave', function () {
                            li.style.background = activeIndex === itemIdx ? 'rgba(79,142,247,0.18)' : '';
                        });
                        li.addEventListener('click', function () {
                            window.location.href = item.url;
                        });
                    })(r, idx);

                    list.appendChild(li);
                    idx++;
                });
            });

            list.style.display = 'block';
        }

        // Search Static Pages
        function searchStatic(query) {
            var q = query.toLowerCase().trim();
            return STATIC_PAGES.filter(function (p) {
                return p.title.toLowerCase().includes(q) ||
                       p.category.toLowerCase().includes(q);
            }).slice(0, 12);
        }

        // Dynamic Search (AJAX)
        function searchDynamic(query) {
            return fetch('{{ url("/api/search") }}?q=' + encodeURIComponent(query) + '&_token={{ csrf_token() }}', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(function (r) { return r.ok ? r.json() : { results: [] }; })
            .then(function (data) { return data.results || []; })
            .catch(function () { return []; });
        }

        // Input Handler with Debounce
        input.addEventListener('input', function () {
            var query = input.value.trim();

            if (!query) { showEmpty(); return; }

            // Show static results instantly
            var staticResults = searchStatic(query);
            renderResults(staticResults);

            // Debounce AJAX for dynamic results
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                if (query.length < 2) return;
                searchDynamic(query).then(function (dynamicResults) {
                    if (input.value.trim() !== query) return;
                    var merged = staticResults.concat(dynamicResults);
                    // Deduplicate by URL
                    var seen  = {};
                    var deduped = merged.filter(function (r) {
                        if (seen[r.url]) return false;
                        seen[r.url] = true;
                        return true;
                    });
                    renderResults(deduped);
                });
            }, 280);
        });

    })();
    </script>

</body>

</html>
