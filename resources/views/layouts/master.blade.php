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
           SPOTLIGHT SEARCH ENHANCED ANIMATIONS
           ===================================================== */
        @keyframes spotlightOverlayFadeIn {
            from { background: rgba(0, 0, 0, 0.2); backdrop-filter: blur(0px); }
            to { background: rgba(0, 0, 0, 0.65); backdrop-filter: blur(8px); }
        }
        @keyframes spotlightOverlayFadeOut {
            from { background: rgba(0, 0, 0, 0.65); backdrop-filter: blur(8px); }
            to { background: rgba(0, 0, 0, 0.2); backdrop-filter: blur(0px); }
        }
        @keyframes spotlightModalBounceIn {
            0% { opacity: 0; transform: translateY(-40px) scale(0.9); }
            40% { opacity: 0.8; transform: translateY(8px) scale(1.02); }
            70% { opacity: 0.95; transform: translateY(-3px) scale(0.99); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes spotlightModalFadeOut {
            0% { opacity: 1; transform: translateY(0) scale(1); }
            100% { opacity: 0; transform: translateY(-20px) scale(0.95); }
        }
        @keyframes resultBounceIn {
            0% { opacity: 0; transform: translateX(-20px) scale(0.95); }
            60% { opacity: 0.8; transform: translateX(4px) scale(1.02); }
            100% { opacity: 1; transform: translateX(0) scale(1); }
        }
        @keyframes resultGlowPulse {
            0% { box-shadow: 0 0 0 0 rgba(79, 142, 247, 0.4); }
            70% { box-shadow: 0 0 0 6px rgba(79, 142, 247, 0); }
            100% { box-shadow: 0 0 0 0 rgba(79, 142, 247, 0); }
        }
        @keyframes loadingSpin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes historySlideIn {
            from { opacity: 0; transform: translateX(-15px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes typingDot {
            0%, 60%, 100% { transform: translateY(0); opacity: 0.5; }
            30% { transform: translateY(-4px); opacity: 1; }
        }

        .spotlight-result-item {
            animation: resultBounceIn 0.35s cubic-bezier(0.34, 1.3, 0.64, 1) forwards;
            opacity: 0;
        }
        .spotlight-result-item:nth-child(1) { animation-delay: 0.00s; }
        .spotlight-result-item:nth-child(2) { animation-delay: 0.03s; }
        .spotlight-result-item:nth-child(3) { animation-delay: 0.06s; }
        .spotlight-result-item.top-match {
            animation: resultBounceIn 0.4s cubic-bezier(0.34, 1.3, 0.64, 1) forwards, resultGlowPulse 0.6s ease 0.3s;
            border-left: 3px solid #4f8ef7;
            background: linear-gradient(90deg, rgba(79, 142, 247, 0.08) 0%, transparent 100%);
        }
        .spotlight-history-item {
            animation: historySlideIn 0.25s ease forwards;
            opacity: 0;
            animation-fill-mode: forwards;
        }
        .spotlight-history-item:nth-child(1) { animation-delay: 0.00s; }
        .spotlight-history-item:nth-child(2) { animation-delay: 0.04s; }
        .spotlight-history-item:nth-child(3) { animation-delay: 0.08s; }
        .typing-dot {
            display: inline-block;
            animation: typingDot 1.4s infinite ease-in-out;
        }
        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }
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

                        {{-- SPOTLIGHT SEARCH TRIGGER BUTTON --}}
                        <div class="d-none d-md-inline-flex align-items-center" style="position:relative;">
                            <button type="button"
                                    id="spotlight-trigger"
                                    style="display:flex; align-items:center; gap:8px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); border-radius:10px; padding:7px 14px; cursor:pointer; transition:all 0.2s ease; min-width:220px;">
                                <i class="mdi mdi-magnify" style="font-size:16px; opacity:0.6;"></i>
                                <span style="font-size:13px; opacity:0.55; flex:1; text-align:left;">Search everything…</span>
                                <div style="display:flex; gap:4px;">
                                    <kbd style="font-size:10px; padding:2px 6px; border-radius:4px; background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.2); opacity:0.7;">⌘</kbd>
                                    <kbd style="font-size:10px; padding:2px 6px; border-radius:4px; background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.2); opacity:0.7;">K</kbd>
                                </div>
                            </button>
                            <div class="search-tooltip"
                                 style="position:absolute; bottom:-35px; left:0; background:rgba(0,0,0,0.85); color:#fff; font-size:11px; padding:4px 10px; border-radius:6px; white-space:nowrap; opacity:0; transition:opacity 0.2s; pointer-events:none; z-index:100; backdrop-filter:blur(4px);">
                                Press <kbd style="background:rgba(255,255,255,0.2); padding:2px 5px; border-radius:4px; margin:0 2px;">⌘K</kbd> or
                                <kbd style="background:rgba(255,255,255,0.2); padding:2px 5px; border-radius:4px; margin:0 2px;">Ctrl+K</kbd> to search
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        {{-- =====================================================
                             FIXED TOPBAR USER DROPDOWN BUTTON
                             Works on all pages including dashboard
                             ===================================================== --}}
                        <div class="dropdown ms-sm-3 header-item topbar-user">
                            <button type="button"
                                    class="btn shadow-none p-0 dropdown-toggle"
                                    id="page-header-user-dropdown"
                                    data-bs-toggle="dropdown"
                                    data-bs-auto-close="outside"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                    style="background: transparent; border: none; line-height: 1; box-shadow: none !important;">
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
             ENHANCED SPOTLIGHT SEARCH MODAL - LARGER VERSION
             ============================================================ --}}
        <div id="spotlight-overlay"
             style="display:none; position:fixed; inset:0; z-index:9999; align-items:flex-start; justify-content:center; padding-top:6vh;">

            <div id="spotlight-box"
                 style="width:100%; max-width:860px; margin:0 24px; background:rgba(24, 26, 32, 0.96); border:1px solid rgba(255,255,255,0.1); border-radius:28px; box-shadow:0 32px 80px rgba(0,0,0,0.6); overflow:hidden;">

                {{-- Search Input Row --}}
                <div style="display:flex; align-items:center; gap:16px; padding:20px 24px; border-bottom:1px solid rgba(255,255,255,0.08);">
                    <i class="mdi mdi-magnify" style="font-size:26px; color:#4f8ef7; flex-shrink:0;"></i>
                    <input
                        id="spotlight-input"
                        type="text"
                        placeholder="Search for pages, students, staff, classes…"
                        autocomplete="off"
                        style="flex:1; background:transparent; border:none; outline:none; font-size:18px; color:#fff; caret-color:#4f8ef7; padding:8px 0;"
                    >
                    <div style="display:flex; gap:8px;">
                        <button id="spotlight-clear-history" style="background:rgba(255,255,255,0.08); border:none; border-radius:10px; padding:6px 12px; color:rgba(255,255,255,0.6); font-size:12px; font-weight:500; cursor:pointer; transition:all 0.2s ease; display:none;" onmouseover="this.style.background='rgba(255,255,255,0.15)'" onmouseout="this.style.background='rgba(255,255,255,0.08)'">
                            Clear History
                        </button>
                        <kbd id="spotlight-esc"
                             style="font-size:12px; padding:4px 10px; border-radius:8px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:rgba(255,255,255,0.6); cursor:pointer; flex-shrink:0; transition:all 0.2s ease; font-weight:500;">
                            ESC
                        </kbd>
                    </div>
                </div>

                {{-- Results Container --}}
                <div id="spotlight-results" style="max-height:520px; overflow-y:auto; padding:12px 0;">

                    {{-- Search History Section --}}
                    <div id="spotlight-history-section" style="display:none;">
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 24px 8px;">
                            <span style="font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:rgba(255,255,255,0.4);">Recent Searches</span>
                            <button id="spotlight-clear-history-btn" style="background:transparent; border:none; color:rgba(255,255,255,0.4); font-size:12px; cursor:pointer; transition:color 0.2s ease; font-weight:500;" onmouseover="this.style.color='rgba(255,255,255,0.7)'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">Clear All</button>
                        </div>
                        <div id="spotlight-history-list" style="list-style:none; margin:0; padding:0;"></div>
                        <div style="height:1px; background:rgba(255,255,255,0.06); margin:12px 20px;"></div>
                    </div>

                    {{-- Suggestions / Empty State --}}
                    <div id="spotlight-empty" style="padding:48px 24px; text-align:center; color:rgba(255,255,255,0.35);">
                        <i class="mdi mdi-lightning-bolt" style="font-size:48px; display:block; margin-bottom:16px; opacity:0.4;"></i>
                        <span style="font-size:15px;">Start typing to search…</span>
                        <div style="margin-top:16px; font-size:12px; opacity:0.4;">Popular: Students, Classes, Payments, Exams</div>
                    </div>

                    {{-- Results List --}}
                    <ul id="spotlight-list" style="list-style:none; margin:0; padding:0; display:none;"></ul>

                    {{-- Loading Spinner --}}
                    <div id="spotlight-loading" style="display:none; padding:48px; text-align:center;">
                        <div style="display:inline-block; width:32px; height:32px; border:2px solid rgba(255,255,255,0.15); border-top-color:#4f8ef7; border-radius:50%; animation:loadingSpin 0.7s linear infinite;"></div>
                        <div style="margin-top:16px; font-size:13px; color:rgba(255,255,255,0.45);">Searching<span class="typing-dot">.</span><span class="typing-dot">.</span><span class="typing-dot">.</span></div>
                    </div>
                </div>

                {{-- Footer Keyboard Hints --}}
                <div style="padding:14px 24px; border-top:1px solid rgba(255,255,255,0.07); display:flex; gap:24px; font-size:12px; color:rgba(255,255,255,0.35); flex-wrap:wrap;">
                    <span><kbd style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.15); border-radius:5px; padding:2px 6px; font-size:11px;">⌘K</kbd> or <kbd style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.15); border-radius:5px; padding:2px 6px; font-size:11px;">Ctrl+K</kbd> open</span>
                    <span><kbd style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.15); border-radius:5px; padding:2px 6px; font-size:11px;">↑↓</kbd> navigate</span>
                    <span><kbd style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.15); border-radius:5px; padding:2px 6px; font-size:11px;">↵</kbd> open</span>
                    <span><kbd style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.15); border-radius:5px; padding:2px 6px; font-size:11px;">ESC</kbd> close</span>
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
                    <!-- Customizer content - keep as is -->
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
         MASTER ENHANCEMENT SCRIPTS (with keyboard shortcuts)
         ==================================================== -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {

        // =====================================================
        // 1. NPROGRESS — page transition bar
        // =====================================================
        if (typeof NProgress !== 'undefined') {
            NProgress.configure({ showSpinner: false, speed: 400, minimum: 0.1 });
            document.querySelectorAll('a[href]').forEach(function (a) {
                var href = a.getAttribute('href');
                if (href && !href.startsWith('#') && !href.startsWith('javascript') && !href.startsWith('mailto') && !href.startsWith('tel') && !a.hasAttribute('data-bs-toggle') && !a.hasAttribute('data-bs-dismiss') && a.getAttribute('target') !== '_blank') {
                    a.addEventListener('click', function () { NProgress.start(); });
                }
            });
            window.addEventListener('pageshow', function () { NProgress.done(); });
            window.addEventListener('load', function () { NProgress.done(); });
        }

        // =====================================================
        // 2. ACTIVE LINK DETECTION
        // =====================================================
        (function () {
            var currentPath = window.location.pathname;
            var childLinks = document.querySelectorAll('#navbar-nav .nav-sm a.nav-link');
            childLinks.forEach(function (link) {
                try {
                    var linkPath = new URL(link.href, window.location.origin).pathname;
                    var isActive = linkPath === currentPath || (linkPath.length > 1 && currentPath.startsWith(linkPath));
                    if (!isActive) return;
                    link.classList.add('nav-active-child');
                    var parentCollapse = link.closest('.collapse');
                    if (parentCollapse) {
                        parentCollapse.classList.add('show');
                        var collapseId = parentCollapse.getAttribute('id');
                        var parentToggle = document.querySelector('[data-bs-target="#' + collapseId + '"], [href="#' + collapseId + '"]');
                        if (parentToggle) {
                            parentToggle.setAttribute('aria-expanded', 'true');
                            parentToggle.classList.remove('collapsed');
                            parentToggle.classList.add('nav-active-parent');
                        }
                    }
                    setTimeout(function () { link.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }, 350);
                } catch (e) {}
            });
        })();

        // =====================================================
        // 3. RIPPLE EFFECT
        // =====================================================
        document.querySelectorAll('#navbar-nav .nav-link').forEach(function (link) {
            link.addEventListener('click', function (e) {
                if (link.hasAttribute('data-bs-toggle')) return;
                var ripple = document.createElement('span');
                ripple.classList.add('nav-ripple');
                var rect = link.getBoundingClientRect();
                var size = Math.max(rect.width, rect.height);
                var x = e.clientX - rect.left - size / 2;
                var y = e.clientY - rect.top - size / 2;
                ripple.style.cssText = 'width:' + size + 'px;height:' + size + 'px;left:' + x + 'px;top:' + y + 'px;';
                link.appendChild(ripple);
                setTimeout(function () { if (ripple.parentNode) ripple.parentNode.removeChild(ripple); }, 650);
            });
        });

        // =====================================================
        // 4. BACK-TO-TOP
        // =====================================================
        var backToTop = document.getElementById('back-to-top');
        if (backToTop) {
            window.addEventListener('scroll', function () { backToTop.classList.toggle('show', window.scrollY > 300); }, { passive: true });
            backToTop.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });
        }

        // =====================================================
        // 5. IMAGE MODAL
        // =====================================================
        var imageModal = document.getElementById('imageViewModal');
        if (imageModal) {
            imageModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var imageSrc = button ? button.getAttribute('data-image') : null;
                var enlargedImg = document.getElementById('enlargedImage');
                if (enlargedImg && imageSrc) enlargedImg.src = imageSrc;
            });
        }

        // =====================================================
        // 6. RESET LAYOUT
        // =====================================================
        var resetBtn = document.getElementById('reset-layout');
        if (resetBtn) {
            resetBtn.addEventListener('click', function () { localStorage.clear(); location.reload(); });
        }

        // =====================================================
        // 7. FORM SUBMISSION NPROGRESS
        // =====================================================
        document.querySelectorAll('form').forEach(function (form) {
            if (form.getAttribute('action') && !form.dataset.noProgress) {
                form.addEventListener('submit', function () { if (typeof NProgress !== 'undefined') NProgress.start(); });
            }
        });

        // =====================================================
        // 8. ACTIVE STATE ON DIRECT NAV LINKS
        // =====================================================
        (function () {
            var currentPath = window.location.pathname;
            var directLinks = document.querySelectorAll('#navbar-nav > li > a.nav-link:not(.menu-link)');
            directLinks.forEach(function (link) {
                try {
                    var linkPath = new URL(link.href, window.location.origin).pathname;
                    if (linkPath === currentPath) link.classList.add('nav-active-parent');
                } catch (e) {}
            });
        })();

        // =====================================================
        // 9. TOOLTIP HOVER
        // =====================================================
        var triggerBtn = document.getElementById('spotlight-trigger');
        var tooltip = document.querySelector('.search-tooltip');
        if (triggerBtn && tooltip) {
            triggerBtn.addEventListener('mouseenter', function () { tooltip.style.opacity = '1'; });
            triggerBtn.addEventListener('mouseleave', function () { tooltip.style.opacity = '0'; });
        }

        // =====================================================
        // 10. FORCE RE-INITIALIZE USER DROPDOWN (Fix for dashboard)
        // =====================================================
        var userDropdownBtn = document.getElementById('page-header-user-dropdown');
        if (userDropdownBtn) {
            // Ensure Bootstrap dropdown is properly initialized
            if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
                try {
                    var existingDropdown = bootstrap.Dropdown.getInstance(userDropdownBtn);
                    if (existingDropdown) {
                        existingDropdown.dispose();
                    }
                    new bootstrap.Dropdown(userDropdownBtn);
                } catch(e) {
                    console.log('Dropdown init error:', e);
                }
            }

            // Fallback: if dropdown still doesn't work, add manual click handler
            userDropdownBtn.addEventListener('click', function(e) {
                var menu = this.nextElementSibling;
                if (menu && !menu.classList.contains('show')) {
                    e.preventDefault();
                    e.stopPropagation();
                    menu.classList.add('show');
                    this.setAttribute('aria-expanded', 'true');
                } else if (menu && menu.classList.contains('show')) {
                    menu.classList.remove('show');
                    this.setAttribute('aria-expanded', 'false');
                }
            });
        }

    });
    </script>

    <!-- ====================================================
         SPOTLIGHT SEARCH JAVASCRIPT (Animated + History)
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
            { title: 'Class Arm',                   url: '{{ route("schoolarm.index") }}',                        icon: 'mdi-table-chair',    category: 'School Settings' },
            { title: 'Class Category',              url: '{{ route("classcategories.index") }}',                  icon: 'mdi-format-list-bulleted', category: 'School Settings' },
            { title: 'Class Name',                  url: '{{ route("schoolclass.index") }}',                      icon: 'mdi-google-classroom', category: 'School Settings' },
            { title: 'Class Teacher',               url: '{{ route("classteacher.index") }}',                     icon: 'mdi-human-male-board', category: 'School Settings' },
            { title: 'Subjects',                    url: '{{ route("subject.index") }}',                          icon: 'mdi-book-open-variant', category: 'Subjects' },
            { title: 'Assign Subject Teacher',      url: '{{ route("subjectteacher.index") }}',                   icon: 'mdi-account-tie',    category: 'Subjects' },
            { title: 'Assign Class Subject',        url: '{{ route("subjectclass.index") }}',                     icon: 'mdi-book-plus',      category: 'Subjects' },
            { title: 'Student Subject Registration', url: '{{ route("subjectoperation.index") }}',                icon: 'mdi-clipboard-list', category: 'Subject Registration' },
            { title: 'My Class',                    url: '{{ route("myclass.index") }}',                          icon: 'mdi-google-classroom', category: 'Classes & Records' },
            { title: 'My Subject',                  url: '{{ route("mysubject.index") }}',                        icon: 'mdi-book-open',      category: 'Classes & Records' },
            { title: 'Subjects to Vet',             url: '{{ route("mysubjectvettings.index") }}',                icon: 'mdi-check-decagram',  category: 'Classes & Records' },
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
            { title: 'Payment Gateways',            url: '{{ route("admin.payment-gateways.index") }}',           icon: 'mdi-credit-card',    category: 'Finance' },
            { title: 'Payroll Periods',             url: '{{ route("payroll.periods") }}',                        icon: 'mdi-calendar-clock', category: 'Payroll' },
            { title: 'Payroll Summary',             url: '{{ route("payroll.summary") }}',                        icon: 'mdi-cash-multiple',  category: 'Payroll' },
            { title: 'Salary Structures',           url: '{{ route("payroll.salary-structures") }}',              icon: 'mdi-bank',           category: 'Payroll' },
            { title: 'All Examinations',            url: '{{ route("exams.index") }}',                            icon: 'mdi-clipboard-text', category: 'Exams & CBT' },
            { title: 'Questions Management',        url: '{{ route("questions.all") }}',                          icon: 'mdi-help-circle',    category: 'Exams & CBT' },
            { title: 'CBT Exercise',                url: '{{ route("cbt.index") }}',                              icon: 'mdi-monitor',        category: 'Exams & CBT' },
            { title: 'Admin Timetable',             url: '{{ route("timetable.index") }}',                        icon: 'mdi-table-clock',    category: 'Timetable' },
            { title: 'My Timetable',                url: '{{ route("timetable.teacher") }}',                      icon: 'mdi-calendar-clock', category: 'Timetable' },
            { title: 'Room Management',             url: '{{ route("rooms.index") }}',                            icon: 'mdi-door',           category: 'Timetable' },
            { title: 'Exam Timetable',              url: '{{ route("exam-timetable.index") }}',                   icon: 'mdi-calendar-check', category: 'Timetable' },
            { title: 'Holidays',                    url: '{{ route("holidays.index") }}',                         icon: 'mdi-calendar-blank', category: 'Timetable' },
            { title: 'Mark Attendance',             url: '{{ route("attendance.my-classes") }}',                  icon: 'mdi-clipboard-check', category: 'Attendance' },
            { title: 'Attendance Settings',         url: '{{ route("attendance.settings") }}',                    icon: 'mdi-cog',            category: 'Attendance' },
            { title: 'Attendance School Report',    url: '{{ route("attendance.school-report") }}',               icon: 'mdi-chart-line',     category: 'Attendance' },
            { title: 'Principal\'s Comment',        url: '{{ route("principalscomment.index") }}',                icon: 'mdi-comment-text',   category: 'Records' },
            { title: 'Balance Sheet',               url: '{{ route("reports.financial.balance-sheet") }}',        icon: 'mdi-scale-balance',  category: 'Accounting' },
            { title: 'Income Statement',            url: '{{ route("reports.financial.income-statement") }}',     icon: 'mdi-chart-line',     category: 'Accounting' },
            { title: 'Cash Flow',                   url: '{{ route("reports.financial.cash-flow") }}',            icon: 'mdi-cash-refund',    category: 'Accounting' },
            { title: 'Student Debtors List',        url: '{{ route("reports.financial.debtors") }}',              icon: 'mdi-account-alert',  category: 'Accounting' },
            { title: 'Class Analysis',              url: '{{ route("reports.analysis.index") }}',                 icon: 'mdi-school',         category: 'Accounting' },
            { title: 'Generate Transcript',         url: '{{ route("transcript.index") }}',                       icon: 'mdi-file-account',   category: 'Transcripts' },
            { title: 'Admin Score Entry',           url: '{{ route("admin.score-entry.index") }}',                icon: 'mdi-clipboard-edit', category: 'Admin Tools' },
            { title: 'Score Entry Lock Management', url: '{{ route("admin.score-entry.lock-management") }}',      icon: 'mdi-lock',           category: 'Admin Tools' },
            { title: 'Student Result Manager',      url: '{{ route("admin.score-entry.student-result-manager") }}', icon: 'mdi-chart-line',   category: 'Admin Tools' },
        ];

        // Category colors
        var CAT_COLORS = {
            'Dashboards': '#4f8ef7', 'Users & Privileges': '#405189', 'Students': '#e76f51', 'My Account': '#2a9d8f',
            'School Settings': '#6a0572', 'Subjects': '#e9c46a', 'Subject Registration': '#e9c46a', 'Classes & Records': '#0a9396',
            'Records & Results': '#457b9d', 'Promotions': '#2a9d8f', 'Finance': '#10b981', 'Payroll': '#e76f51',
            'Exams & CBT': '#f4a261', 'Timetable': '#4f8ef7', 'Attendance': '#e9c46a', 'Records': '#6a0572',
            'Accounting': '#10b981', 'Transcripts': '#457b9d', 'Admin Tools': '#ef4444',
        };

        // Search History Management
        var SEARCH_HISTORY_KEY = 'spotlight_search_history';
        var MAX_HISTORY_ITEMS = 10;

        function getSearchHistory() {
            var history = localStorage.getItem(SEARCH_HISTORY_KEY);
            if (!history) return [];
            try { return JSON.parse(history); } catch(e) { return []; }
        }

        function saveSearchHistory(history) {
            localStorage.setItem(SEARCH_HISTORY_KEY, JSON.stringify(history.slice(0, MAX_HISTORY_ITEMS)));
        }

        function addToSearchHistory(query, result) {
            if (!query || query.trim().length < 2) return;
            var history = getSearchHistory();
            var newItem = { query: query, url: result.url, title: result.title, icon: result.icon, category: result.category, timestamp: Date.now() };
            var existingIndex = history.findIndex(function(item) { return item.query === query && item.url === result.url; });
            if (existingIndex !== -1) history.splice(existingIndex, 1);
            history.unshift(newItem);
            if (history.length > MAX_HISTORY_ITEMS) history.pop();
            saveSearchHistory(history);
            renderSearchHistory();
        }

        function removeFromHistory(index) {
            var history = getSearchHistory();
            history.splice(index, 1);
            saveSearchHistory(history);
            renderSearchHistory();
            var input = document.getElementById('spotlight-input');
            if (input && !input.value.trim()) renderSearchHistory();
        }

        function clearAllHistory() {
            localStorage.removeItem(SEARCH_HISTORY_KEY);
            renderSearchHistory();
            showEmptyState();
        }

        function renderSearchHistory() {
            var history = getSearchHistory();
            var historySection = document.getElementById('spotlight-history-section');
            var historyList = document.getElementById('spotlight-history-list');
            var clearHistoryMain = document.getElementById('spotlight-clear-history');
            var spotlightInput = document.getElementById('spotlight-input');
            var currentQuery = spotlightInput ? spotlightInput.value.trim() : '';

            if (history.length > 0 && !currentQuery) {
                historySection.style.display = 'block';
                historyList.innerHTML = '';
                if (clearHistoryMain) clearHistoryMain.style.display = 'block';

                history.forEach(function(item, idx) {
                    var div = document.createElement('div');
                    div.className = 'spotlight-history-item';
                    div.style.cssText = 'display:flex; align-items:center; gap:14px; padding:10px 24px; cursor:pointer; transition:background 0.15s ease; border-radius:10px; margin:0 16px;';
                    div.setAttribute('data-history-index', idx);

                    var accentColor = CAT_COLORS[item.category] || '#4f8ef7';
                    var iconWrap = document.createElement('span');
                    iconWrap.style.cssText = 'width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; background:' + accentColor + '22;';
                    var icon = document.createElement('i');
                    icon.className = (item.icon || 'mdi-history') + ' mdi';
                    icon.style.cssText = 'font-size:16px; color:' + accentColor + ';';
                    iconWrap.appendChild(icon);

                    var textWrap = document.createElement('span');
                    textWrap.style.cssText = 'flex:1; min-width:0;';
                    var title = document.createElement('span');
                    title.style.cssText = 'display:block; font-size:14px; font-weight:500; color:rgba(255,255,255,0.9); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;';
                    title.textContent = item.title;
                    var querySpan = document.createElement('span');
                    querySpan.style.cssText = 'display:block; font-size:11px; color:rgba(255,255,255,0.4); margin-top:2px;';
                    querySpan.textContent = item.query;
                    textWrap.appendChild(title);
                    textWrap.appendChild(querySpan);

                    var removeBtn = document.createElement('button');
                    removeBtn.innerHTML = '✕';
                    removeBtn.style.cssText = 'background:transparent; border:none; color:rgba(255,255,255,0.35); cursor:pointer; font-size:13px; padding:6px 10px; border-radius:6px; transition:all 0.2s ease;';
                    removeBtn.onmouseover = function() { this.style.color = '#ef4444'; this.style.background = 'rgba(239,68,68,0.15)'; };
                    removeBtn.onmouseout = function() { this.style.color = 'rgba(255,255,255,0.35)'; this.style.background = 'transparent'; };
                    removeBtn.onclick = function(e) { e.stopPropagation(); removeFromHistory(idx); };

                    div.appendChild(iconWrap);
                    div.appendChild(textWrap);
                    div.appendChild(removeBtn);
                    div.onclick = function() { if (spotlightInput) { spotlightInput.value = item.query; performSearch(item.query); } };
                    historyList.appendChild(div);
                });
            } else {
                historySection.style.display = 'none';
                if (clearHistoryMain) clearHistoryMain.style.display = 'none';
            }
        }

        // Spotlight Modal Animation
        var overlay = document.getElementById('spotlight-overlay');
        var box = document.getElementById('spotlight-box');
        var input = document.getElementById('spotlight-input');
        var emptyState = document.getElementById('spotlight-empty');
        var loadingEl = document.getElementById('spotlight-loading');
        var list = document.getElementById('spotlight-list');
        var trigger = document.getElementById('spotlight-trigger');
        var escBtn = document.getElementById('spotlight-esc');
        var clearHistoryBtn = document.getElementById('spotlight-clear-history-btn');
        var clearHistoryMain = document.getElementById('spotlight-clear-history');

        var debounceTimer = null;
        var activeIndex = -1;
        var currentResults = [];

        function openSpotlight() {
            if (!overlay) return;
            overlay.style.display = 'flex';
            overlay.style.animation = 'spotlightOverlayFadeIn 0.25s ease forwards';
            if (box) {
                box.style.animation = 'spotlightModalBounceIn 0.35s cubic-bezier(0.34, 1.3, 0.64, 1) forwards';
                box.style.transform = 'none';
            }
            setTimeout(function() { if (input) input.focus(); }, 100);
            renderSearchHistory();
            if (clearHistoryMain) clearHistoryMain.style.display = getSearchHistory().length > 0 ? 'block' : 'none';
        }

        function closeSpotlight() {
            if (box) box.style.animation = 'spotlightModalFadeOut 0.2s ease forwards';
            if (overlay) overlay.style.animation = 'spotlightOverlayFadeOut 0.2s ease forwards';
            setTimeout(function() {
                if (overlay) overlay.style.display = 'none';
                if (input) input.value = '';
                showEmptyState();
            }, 200);
        }

        function showEmptyState() {
            if (emptyState) emptyState.style.display = 'block';
            if (loadingEl) loadingEl.style.display = 'none';
            if (list) { list.style.display = 'none'; list.innerHTML = ''; }
            if (clearHistoryMain) clearHistoryMain.style.display = getSearchHistory().length > 0 ? 'block' : 'none';
            renderSearchHistory();
            currentResults = [];
            activeIndex = -1;
        }

        function showLoading() {
            if (emptyState) emptyState.style.display = 'none';
            if (loadingEl) loadingEl.style.display = 'block';
            if (list) list.style.display = 'none';
            var historySection = document.getElementById('spotlight-history-section');
            if (historySection) historySection.style.display = 'none';
        }

        function performSearch(query) {
            if (!query || query.trim().length === 0) { showEmptyState(); return; }
            showLoading();
            var staticResults = searchStatic(query);
            renderResults(staticResults);
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() {
                if (query.length < 2) return;
                searchDynamic(query).then(function(dynamicResults) {
                    if (input.value.trim() !== query) return;
                    var merged = staticResults.concat(dynamicResults);
                    var seen = {};
                    var deduped = merged.filter(function(r) { if (seen[r.url]) return false; seen[r.url] = true; return true; });
                    renderResults(deduped);
                });
            }, 280);
        }

        function renderResults(results) {
            if (loadingEl) loadingEl.style.display = 'none';
            if (emptyState) emptyState.style.display = 'none';
            if (list) { list.innerHTML = ''; list.style.display = 'block'; }
            activeIndex = -1;
            currentResults = results;
            var historySection = document.getElementById('spotlight-history-section');
            if (historySection) historySection.style.display = 'none';

            if (!results.length) {
                if (emptyState) {
                    emptyState.innerHTML = '<i class="mdi mdi-magnify-close" style="font-size:42px; display:block; margin-bottom:16px; opacity:0.4;"></i><span style="font-size:15px;">No results found for "' + (input ? input.value : '') + '"</span>';
                    emptyState.style.display = 'block';
                }
                if (list) list.style.display = 'none';
                return;
            }

            var grouped = {};
            results.forEach(function(r) { if (!grouped[r.category]) grouped[r.category] = []; grouped[r.category].push(r); });

            var idx = 0;
            Object.keys(grouped).forEach(function(cat) {
                var header = document.createElement('li');
                header.className = 'spotlight-category-header';
                header.style.cssText = 'padding:12px 24px 6px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:rgba(255,255,255,0.35);';
                header.textContent = cat;
                list.appendChild(header);

                grouped[cat].forEach(function(r, groupIdx) {
                    var li = document.createElement('li');
                    var isTopMatch = idx === 0 && groupIdx === 0;
                    li.className = 'spotlight-result-item' + (isTopMatch ? ' top-match' : '');
                    li.setAttribute('data-idx', idx);
                    li.style.cssText = 'display:flex; align-items:center; gap:14px; padding:12px 24px; cursor:pointer; transition:all 0.2s ease; border-radius:10px; margin:4px 12px;';

                    var accentColor = CAT_COLORS[r.category] || '#4f8ef7';
                    var iconWrap = document.createElement('span');
                    iconWrap.style.cssText = 'width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; background:' + accentColor + '22;';
                    var icon = document.createElement('i');
                    icon.className = (r.icon || 'mdi-chevron-right') + ' mdi';
                    icon.style.cssText = 'font-size:18px; color:' + accentColor + ';';
                    iconWrap.appendChild(icon);

                    var textWrap = document.createElement('span');
                    textWrap.style.cssText = 'flex:1; min-width:0;';
                    var title = document.createElement('span');
                    title.className = 'result-title';
                    title.style.cssText = 'display:block; font-size:15px; font-weight:500; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;';
                    title.textContent = r.title;
                    var sub = document.createElement('span');
                    sub.style.cssText = 'display:block; font-size:12px; color:rgba(255,255,255,0.4); margin-top:2px;';
                    sub.textContent = r.subtitle || r.category;
                    textWrap.appendChild(title);
                    textWrap.appendChild(sub);

                    var arrow = document.createElement('i');
                    arrow.className = 'mdi mdi-arrow-right';
                    arrow.style.cssText = 'font-size:16px; color:rgba(255,255,255,0.25); flex-shrink:0; transition:transform 0.2s ease;';

                    li.appendChild(iconWrap);
                    li.appendChild(textWrap);
                    li.appendChild(arrow);

                    li.addEventListener('mouseenter', function() { this.style.background = 'rgba(79,142,247,0.12)'; arrow.style.transform = 'translateX(6px)'; activeIndex = idx; });
                    li.addEventListener('mouseleave', function() { this.style.background = activeIndex === idx ? 'rgba(79,142,247,0.18)' : ''; arrow.style.transform = 'translateX(0)'; });
                    li.addEventListener('click', function() { addToSearchHistory(input ? input.value : '', r); window.location.href = r.url; });

                    list.appendChild(li);
                    idx++;
                });
            });
        }

        function searchStatic(query) {
            var q = query.toLowerCase().trim();
            return STATIC_PAGES.filter(function(p) { return p.title.toLowerCase().includes(q) || p.category.toLowerCase().includes(q); }).slice(0, 15);
        }

        function searchDynamic(query) {
            return fetch('{{ url("/api/search") }}?q=' + encodeURIComponent(query) + '&_token={{ csrf_token() }}', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(function(r) { return r.ok ? r.json() : { results: [] }; }).then(function(data) { return data.results || []; }).catch(function() { return []; });
        }

        function highlightItem(items) {
            items.forEach(function(li, i) {
                var isActive = i === activeIndex;
                li.style.background = isActive ? 'rgba(79,142,247,0.18)' : '';
                var titleSpan = li.querySelector('.result-title');
                if (titleSpan) titleSpan.style.color = isActive ? '#4f8ef7' : '#fff';
                var arrow = li.querySelector('.mdi-arrow-right');
                if (arrow && isActive) arrow.style.transform = 'translateX(6px)';
                else if (arrow) arrow.style.transform = 'translateX(0)';
                if (isActive) li.scrollIntoView({ block: 'nearest' });
            });
        }

        // Event Listeners
        if (trigger) trigger.addEventListener('click', openSpotlight);
        if (escBtn) escBtn.addEventListener('click', closeSpotlight);
        if (clearHistoryBtn) clearHistoryBtn.addEventListener('click', clearAllHistory);
        if (clearHistoryMain) clearHistoryMain.addEventListener('click', clearAllHistory);
        if (overlay) overlay.addEventListener('click', function(e) { if (e.target === overlay) closeSpotlight(); });

        document.addEventListener('keydown', function(e) {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') { e.preventDefault(); overlay && overlay.style.display === 'flex' ? closeSpotlight() : openSpotlight(); }
            if (e.key === 'Escape' && overlay && overlay.style.display === 'flex') closeSpotlight();
        });

        if (input) {
            input.addEventListener('keydown', function(e) {
                var items = list.querySelectorAll('li[data-idx]');
                if (e.key === 'ArrowDown') { e.preventDefault(); activeIndex = Math.min(activeIndex + 1, items.length - 1); highlightItem(items); }
                else if (e.key === 'ArrowUp') { e.preventDefault(); activeIndex = Math.max(activeIndex - 1, 0); highlightItem(items); }
                else if (e.key === 'Enter' && activeIndex >= 0 && currentResults[activeIndex]) { addToSearchHistory(input.value, currentResults[activeIndex]); window.location.href = currentResults[activeIndex].url; }
            });
            input.addEventListener('input', function() {
                var query = this.value.trim();
                if (!query) { showEmptyState(); renderSearchHistory(); if (clearHistoryMain) clearHistoryMain.style.display = getSearchHistory().length > 0 ? 'block' : 'none'; return; }
                if (clearHistoryMain) clearHistoryMain.style.display = 'none';
                performSearch(query);
            });
        }

        renderSearchHistory();
    })();
    </script>

</body>

</html>
