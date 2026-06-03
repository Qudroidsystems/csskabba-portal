<!doctype html>
<html lang="en" data-layout="vertical" data-sidebar="dark" data-sidebar-size="lg" data-preloader="disable" data-theme="default" data-topbar="light" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <title>{{ $pagetitle }} | Vite-ESchool 2.0</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="school management software" name="description">
    <meta content="" name="author">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Dynamic Favicon from School Logo -->
    @php
        $activeSchool = App\Models\SchoolInformation::getActiveSchool();
        $faviconUrl = $activeSchool ? $activeSchool->getLogoWithFallbackAttribute() : asset('theme/layouts/assets/images/favicon.ico');
    @endphp
    <link rel="icon" type="image/png" href="{{ $faviconUrl }}">
    <link rel="shortcut icon" type="image/png" href="{{ $faviconUrl }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link id="fontsLink" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/bold/style.css">
    <link href="{{ asset('theme/layouts/assets/fonts/materialdesignicons-webfont.woff2') }}?v=6.5.95" rel="stylesheet" type="font/woff2">

    <!-- Layout CSS — load BEFORE layout.js -->
    <link href="{{ asset('theme/layouts/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('theme/layouts/assets/css/icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('theme/layouts/assets/css/app.min.css') }}" rel="stylesheet">
    <link href="{{ asset('theme/layouts/assets/css/custom.min.css') }}" rel="stylesheet">

    <!-- layout.js must run AFTER the CSS above -->
    <script src="{{ asset('theme/layouts/assets/js/layout.js') }}"></script>

    <!-- NProgress -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.css"/>
    <script src="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.min.js"></script>

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- jQuery (needed by Select2 and some page scripts) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        /* =====================================================
           NPROGRESS
           ===================================================== */
        #nprogress .bar  { background: #4f8ef7 !important; height: 3px !important; box-shadow: 0 0 8px rgba(79,142,247,.6) !important; }
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
           SPINNER
           ===================================================== */
        .spin { animation: spin 1s linear infinite; }
        @keyframes spin { 0%{transform:rotate(0deg)} 100%{transform:rotate(360deg)} }

        /* =====================================================
           MISC
           ===================================================== */
        .form-check-input:checked { background-color: #405189; border-color: #405189; }
        .swal2-toast { font-size: 14px !important; }
        .swal2-container.swal2-top-end { top: 70px !important; }
        .table tbody tr { transition: background-color .15s ease; }
        .table tbody tr:hover { background-color: rgba(67,97,238,.05); }
        .modal.fade  .modal-dialog { transform: translate(0,-50px); transition: transform .3s ease-out; }
        .modal.show  .modal-dialog { transform: translate(0,0); }

        /* =====================================================
           SIDEBAR LAYOUT — flex column so footer pins to bottom
           ===================================================== */
        .app-menu {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: 250px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }
        #scrollbar {
            flex: 1;
            overflow-y: auto;
            scrollbar-width: thin;
            padding-bottom: 8px;
        }
        #scrollbar::-webkit-scrollbar { width: 4px; }
        #scrollbar::-webkit-scrollbar-track  { background: rgba(255,255,255,.05); border-radius: 4px; }
        #scrollbar::-webkit-scrollbar-thumb  { background: rgba(255,255,255,.2);  border-radius: 4px; }
        #scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,.3); }
        .bg-light::-webkit-scrollbar { width: 6px; }
        .bg-light::-webkit-scrollbar-track  { background: #f1f1f1; border-radius: 10px; }
        .bg-light::-webkit-scrollbar-thumb  { background: #888; border-radius: 10px; }
        .bg-light::-webkit-scrollbar-thumb:hover { background: #555; }
        .navbar-menu .container-fluid { padding: 0; }
        #navbar-nav { padding-bottom: 8px; }

        /* =====================================================
           SIDEBAR LOGOUT FOOTER
           ===================================================== */
        .sidebar-footer {
            flex-shrink: 0;
            border-top: 1px solid rgba(255,255,255,.1);
            padding: 20px 16px 24px;
            margin-top: auto;
            background: inherit;
        }
        .sidebar-footer-user {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
        }
        .sidebar-footer-user img {
            width: 36px; height: 36px;
            border-radius: 50%; object-fit: cover;
            border: 2px solid rgba(255,255,255,.18);
            flex-shrink: 0;
        }
        .sidebar-footer-avatar-initials {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: #405189;
            color: #fff;
            font-size: 13px; font-weight: 600;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            border: 2px solid rgba(255,255,255,.18);
        }
        .sidebar-footer-user-info { min-width: 0; flex: 1; }
        .sidebar-footer-user-name {
            font-size: 13px; font-weight: 600; color: #fff;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .sidebar-footer-user-role {
            font-size: 11px; color: rgba(255,255,255,.45);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .sidebar-logout-btn {
            display: flex; align-items: center; gap: 9px;
            width: 100%; padding: 9px 14px;
            border-radius: 8px;
            background: rgba(239,68,68,.12);
            border: 1px solid rgba(239,68,68,.22);
            color: #f87171; font-size: 13px; font-weight: 500;
            cursor: pointer; text-decoration: none;
            transition: background .2s, border-color .2s, color .2s;
        }
        .sidebar-logout-btn:hover {
            background: rgba(239,68,68,.24);
            border-color: rgba(239,68,68,.45);
            color: #fca5a5;
        }
        .sidebar-logout-btn i { font-size: 17px; flex-shrink: 0; }

        /* =====================================================
           SIDEBAR NAV ACTIVE STATES
           ===================================================== */
        #navbar-nav .menu-dropdown { overflow: hidden; }
        #navbar-nav .nav-link.menu-link .ri-arrow-down-s-line { transition: transform .25s ease; display: inline-block; }
        #navbar-nav .nav-link.menu-link[aria-expanded="true"] .ri-arrow-down-s-line { transform: rotate(180deg); }

        #navbar-nav .nav-link.menu-link.nav-active-parent {
            color: #fff !important;
            background: rgba(79,142,247,.18) !important;
            border-left: 3px solid #4f8ef7;
            padding-left: calc(1.3rem - 3px);
        }
        #navbar-nav .nav-link.menu-link.nav-active-parent i { color: #4f8ef7 !important; }

        #navbar-nav .nav-sm .nav-link.nav-active-child { color: #7eb8fb !important; font-weight: 500; }
        #navbar-nav .nav-sm .nav-link.nav-active-child::before {
            content: ''; display: inline-block;
            width: 5px; height: 5px; border-radius: 50%;
            background: #4f8ef7; margin-right: 8px;
            box-shadow: 0 0 0 3px rgba(79,142,247,.25);
            vertical-align: middle; flex-shrink: 0;
            animation: dotPop .25s ease;
        }
        @keyframes dotPop { from{transform:scale(0);opacity:0} to{transform:scale(1);opacity:1} }

        /* =====================================================
           SIDEBAR HOVER TRANSITIONS + RIPPLE
           ===================================================== */
        #navbar-nav .nav-link { position: relative; overflow: hidden; transition: color .18s, background-color .18s, padding-left .18s; }
        .nav-ripple {
            position: absolute; border-radius: 50%;
            background: rgba(255,255,255,.18);
            transform: scale(0); animation: ripple-anim .55s linear;
            pointer-events: none; z-index: 0;
        }
        @keyframes ripple-anim { to{transform:scale(5);opacity:0} }

        /* =====================================================
           BACK TO TOP
           ===================================================== */
        #back-to-top { opacity:0; visibility:hidden; transform:translateY(12px); transition:opacity .3s,transform .3s,visibility .3s; }
        #back-to-top.show { opacity:1; visibility:visible; transform:translateY(0); }
        #back-to-top:hover { transform:translateY(-3px) !important; }

        /* =====================================================
           PAGE FADE-IN
           ===================================================== */
        .page-content { animation: pageFadeIn .35s ease; }
        @keyframes pageFadeIn { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:translateY(0)} }

        /* =====================================================
           TOPBAR
           ===================================================== */
        #page-topbar .header-item { transition: color .2s ease, background-color .2s ease; }
        .header-profile-user-enhanced { transition: transform .25s ease, box-shadow .25s ease; }
        .header-profile-user-enhanced:hover { transform: scale(1.07); box-shadow: 0 0 0 3px rgba(79,142,247,.35) !important; }

        /* TOPBAR USER DROPDOWN */
        .topbar-user .dropdown-menu {
            min-width: 220px;
            z-index: 9999 !important;
            border-radius: 12px;
            overflow: hidden;
            margin-top: 8px !important;
            box-shadow: 0 8px 32px rgba(0,0,0,.18);
        }

        /* =====================================================
           MOBILE SIDEBAR — ANIMATED
           ===================================================== */
        .vertical-overlay {
            position: fixed;
            inset: 0;
            z-index: 999;
            background: rgba(0,0,0,0);
            display: none;
            transition: background 0.3s ease;
        }
        body.vertical-sidebar-enable .vertical-overlay {
            display: block;
            background: rgba(0,0,0,.65);
            backdrop-filter: blur(2px);
            animation: overlayFadeIn 0.3s ease;
        }
        @keyframes overlayFadeIn {
            from { background: rgba(0,0,0,0); backdrop-filter: blur(0); }
            to { background: rgba(0,0,0,.65); backdrop-filter: blur(2px); }
        }

        /* Mobile: sidebar slides in/out with smooth animation */
        @media (max-width: 1024.98px) {
            .app-menu {
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: none;
            }
            body.vertical-sidebar-enable .app-menu {
                transform: translateX(0);
                box-shadow: 4px 0 24px rgba(0,0,0,.35);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
        }

        /* Desktop: normal behavior */
        @media (min-width: 1025px) {
            body.vertical-sidebar-enable .app-menu {
                transform: none;
            }
        }

        /* =====================================================
           SEARCH BUTTON - VISIBLE ON MOBILE
           ===================================================== */
        .mobile-search-btn {
            display: none;
        }

        @media (max-width: 768px) {
            .desktop-search-btn {
                display: none !important;
            }
            .mobile-search-btn {
                display: inline-flex !important;
            }
        }

        /* =====================================================
           SPOTLIGHT SEARCH STYLES
           ===================================================== */
        .typing-dot {
            display: inline-block;
            animation: typingDot 1.4s infinite ease-in-out;
        }
        .typing-dot:nth-child(2) { animation-delay: .2s; }
        .typing-dot:nth-child(3) { animation-delay: .4s; }

        @keyframes typingDot {
            0%, 60%, 100% { transform: translateY(0); opacity: .5; }
            30% { transform: translateY(-4px); opacity: 1; }
        }

        @keyframes loadingSpin {
            0% { transform: rotate(0); }
            100% { transform: rotate(360deg); }
        }

        .spotlight-result-item {
            animation: resultBounceIn 0.35s cubic-bezier(0.34, 1.3, 0.64, 1) forwards;
            opacity: 0;
        }

        @keyframes resultBounceIn {
            0% { opacity: 0; transform: translateX(-20px) scale(0.95); }
            60% { opacity: 0.8; transform: translateX(4px) scale(1.02); }
            100% { opacity: 1; transform: translateX(0) scale(1); }
        }

        .search-tooltip {
            position: absolute;
            bottom: -35px;
            left: 0;
            background: rgba(0,0,0,.85);
            color: #fff;
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 6px;
            white-space: nowrap;
            opacity: 0;
            transition: opacity .2s;
            pointer-events: none;
            z-index: 100;
            backdrop-filter: blur(4px);
        }

        /* Finance module cards */
        .finance-stat-card { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); border-radius: 12px; padding: 20px; color: white; transition: transform .3s,box-shadow .3s; }
        .finance-stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(102,126,234,.35); }
        .payment-progress { height: 8px; border-radius: 4px; background: #e2e8f0; }
        .payment-progress-bar { height: 100%; border-radius: 4px; transition: width .4s ease; }
        .scholarship-card { border-left: 4px solid #10b981; transition: transform .2s,box-shadow .2s; }
        .scholarship-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.1); }
        .payroll-table th { background: #1e293b; color: white; }
        .card { transition: box-shadow .25s, transform .25s; }
        .card:hover { box-shadow: 0 6px 20px rgba(0,0,0,.08); }
        .btn  { transition: transform .15s, box-shadow .15s; }
        .btn:active { transform: scale(.97); }
        #navbar-nav > li { animation: navItemFadeIn .4s ease both; }
        #navbar-nav > li:nth-child(1)  { animation-delay:.02s }
        #navbar-nav > li:nth-child(2)  { animation-delay:.04s }
        #navbar-nav > li:nth-child(3)  { animation-delay:.06s }
        #navbar-nav > li:nth-child(4)  { animation-delay:.08s }
        #navbar-nav > li:nth-child(5)  { animation-delay:.10s }
        #navbar-nav > li:nth-child(6)  { animation-delay:.12s }
        #navbar-nav > li:nth-child(7)  { animation-delay:.14s }
        #navbar-nav > li:nth-child(8)  { animation-delay:.16s }
        #navbar-nav > li:nth-child(9)  { animation-delay:.18s }
        #navbar-nav > li:nth-child(10) { animation-delay:.20s }
        #navbar-nav > li:nth-child(11) { animation-delay:.22s }
        #navbar-nav > li:nth-child(12) { animation-delay:.24s }
        #navbar-nav > li:nth-child(n+13) { animation-delay:.26s }
        @keyframes navItemFadeIn { from{opacity:0;transform:translateX(-8px)} to{opacity:1;transform:translateX(0)} }
        .dropdown-menu { animation: dropdownFadeIn .25s cubic-bezier(.4,0,.2,1); transform-origin: top right; }
        @keyframes dropdownFadeIn { from{opacity:0;transform:translateY(-10px) scale(.97)} to{opacity:1;transform:translateY(0) scale(1)} }
        @media print { .no-print{display:none!important} body{padding:0;margin:0} }
        @keyframes spotlightOverlayFadeIn  { from{background:rgba(0,0,0,.2);backdrop-filter:blur(0)} to{background:rgba(0,0,0,.65);backdrop-filter:blur(8px)} }
        @keyframes spotlightOverlayFadeOut { from{background:rgba(0,0,0,.65);backdrop-filter:blur(8px)} to{background:rgba(0,0,0,.2);backdrop-filter:blur(0)} }
        @keyframes spotlightModalBounceIn  { 0%{opacity:0;transform:translateY(-40px) scale(.9)} 40%{opacity:.8;transform:translateY(8px) scale(1.02)} 70%{opacity:.95;transform:translateY(-3px) scale(.99)} 100%{opacity:1;transform:translateY(0) scale(1)} }
        @keyframes spotlightModalFadeOut   { 0%{opacity:1;transform:translateY(0) scale(1)} 100%{opacity:0;transform:translateY(-20px) scale(.95)} }
    </style>

    <!-- Route-specific CSS includes -->
    @if (Route::is('dashboard'))              @include('layouts.pages-assets.css.users-list-css') @endif
    @if (Route::is('users.*'))                @include('layouts.pages-assets.css.users-list-css') @endif
    @if (Route::is('student-id-cards.*'))     @include('layouts.pages-assets.css.users-list-css') @endif
    @if (Route::is('student.payments.*'))     @include('layouts.pages-assets.css.users-list-css') @endif
    @if (Route::is('profile.*'))              @include('layouts.pages-assets.css.users-list-css') @endif
    @if (Route::is('roles.*'))                @include('layouts.pages-assets.css.roles-list-css') @endif
    @if (Route::is('permissions.*'))          @include('layouts.pages-assets.css.permission-list-css') @endif
    @if (Route::is('session.*'))              @include('layouts.pages-assets.css.session-list-css') @endif
    @if (Route::is('school-information.*'))   @include('layouts.pages-assets.css.schoolinformation-list-css') @endif
    @if (Route::is('admin.school-info.*'))    @include('layouts.pages-assets.css.schoolinformation-list-css') @endif
    @if (Route::is('term.*'))                 @include('layouts.pages-assets.css.term-list-css') @endif
    @if (Route::is('schoolhouse.*'))          @include('layouts.pages-assets.css.schoolhouse-list-css') @endif
    @if (Route::is('schoolarm.*'))            @include('layouts.pages-assets.css.arm-list-css') @endif
    @if (Route::is('classcategories.*'))      @include('layouts.pages-assets.css.classcategory-list-css') @endif
    @if (Route::is('schoolclass.*'))          @include('layouts.pages-assets.css.schoolclass-list-css') @endif
    @if (Route::is('classteacher.*'))         @include('layouts.pages-assets.css.classteacher-list-css') @endif
    @if (Route::is('subject.*'))              @include('layouts.pages-assets.css.subject-list-css') @endif
    @if (Route::is('subjects.*'))             @include('layouts.pages-assets.css.subject-list-css') @endif
    @if (Route::is('subjectteacher.*'))       @include('layouts.pages-assets.css.subjectteacher-list-css') @endif
    @if (Route::is('subjectclass.*'))         @include('layouts.pages-assets.css.subjectclass-list-css') @endif
    @if (Route::is('schoolbill.*'))           @include('layouts.pages-assets.css.schoolbill-list-css') @endif
    @if (Route::is('schoolbilltermsession.*'))@include('layouts.pages-assets.css.schoolbilltermsession-list-css') @endif
    @if (Route::is('student.*'))              @include('layouts.pages-assets.css.student-list-css') @endif
    @if (Route::is('studentbatchindex'))      @include('layouts.pages-assets.css.student-list-css') @endif
    @if (Route::is('myclass.*'))              @include('layouts.pages-assets.css.myclass-list-css') @endif
    @if (Route::is('mysubject.*'))            @include('layouts.pages-assets.css.mysubject-list-css') @endif
    @if (Route::is('viewstudent'))            @include('layouts.pages-assets.css.viewstudent-list-css') @endif
    @if (Route::is('studentreports.*'))       @include('layouts.pages-assets.css.studentreport-list-css') @endif
    @if (Route::is('studentmockreports.*'))   @include('layouts.pages-assets.css.studentreport-list-css') @endif
    @if (Route::is('broadsheet*'))            @include('layouts.pages-assets.css.broadsheet-list-css') @endif
    @if (Route::is('subjectoperation.*'))     @include('layouts.pages-assets.css.subjectoperation-list-css') @endif
    @if (Route::is('subjects.subjectinfo'))   @include('layouts.pages-assets.css.subjectinfo-list-css') @endif
    @if (Route::is('myresultroom.*'))         @include('layouts.pages-assets.css.myresultroom-list-css') @endif
    @if (Route::is('subjectscoresheet'))      @include('layouts.pages-assets.css.subjectscoresheet-list-css') @endif
    @if (Route::is('subassessment.*'))        @include('layouts.pages-assets.css.subjectscoresheet-list-css') @endif
    @if (Route::is('assessment.*'))           @include('layouts.pages-assets.css.subjectscoresheet-list-css') @endif
    @if (Route::is('assessments'))            @include('layouts.pages-assets.css.subjectscoresheet-list-css') @endif
    @if (Route::is('subjectscoresheet-mock.*'))@include('layouts.pages-assets.css.subjectscoresheet-mock-list-css') @endif
    @if (Route::is('studentresults*'))        @include('layouts.pages-assets.css.studentresults-list-css') @endif
    @if (Route::is('schoolpayment*'))         @include('layouts.pages-assets.css.schoolpayment-list-css') @endif
    @if (Route::is('analysis*'))              @include('layouts.pages-assets.css.analysis-list-css') @endif
    @if (Route::is('exams*'))                 @include('layouts.pages-assets.css.exams-list-css') @endif
    @if (Route::is('questions*'))             @include('layouts.pages-assets.css.questions-list-css') @endif
    @if (Route::is('cbt*'))                   @include('layouts.pages-assets.css.cbt-list-css') @endif
    @if (Route::is('classbroadsheet.*'))      @include('layouts.pages-assets.css.classbroadsheet-list-css') @endif
    @if (Route::is('principalscomment.*'))    @include('layouts.pages-assets.css.principalscomment-list-css') @endif
    @if (Route::is('myprincipalscomment.*'))  @include('layouts.pages-assets.css.myprincipalscomment-list-css') @endif
    @if (Route::is('compulsorysubjectclass.*'))@include('layouts.pages-assets.css.compulsorysubjectclass-list-css') @endif
    @if (Route::is('subjectvetting.*'))       @include('layouts.pages-assets.css.subjectvettings-list-css') @endif
    @if (Route::is('mocksubjectvetting.*'))   @include('layouts.pages-assets.css.mocksubjectvettings-list-css') @endif
    @if (Route::is('mysubjectvettings.*'))    @include('layouts.pages-assets.css.mysubjectvettings-list-css') @endif
    @if (Route::is('mymocksubjectvettings.*'))@include('layouts.pages-assets.css.mymocksubjectvettings-list-css') @endif
    @if (Route::is('timetable.*'))            @include('layouts.pages-assets.css.timetable-list-css') @endif
    @if (Route::is('rooms.*'))                @include('layouts.pages-assets.css.rooms-list-css') @endif
    @if (Route::is('promotions.*'))           @include('layouts.pages-assets.css.promotions-list-css') @endif
    @if (Route::is('attendance.*'))           @include('layouts.pages-assets.css.attendance-list-css') @endif
    @if (Route::is('transcript.*'))           @include('layouts.pages-assets.css.attendance-list-css') @endif
    @if (Route::is('admin.score-entry.*'))    @include('layouts.pages-assets.css.adminscoreentry-list-css') @endif
    @if (Route::is('admin.scholarship.*') || Route::is('admin.discount.*') || Route::is('sibling.*') ||
        Route::is('payment.*') || Route::is('reports.financial.*') || Route::is('reports.analysis.*') ||
        Route::is('payroll.*') || Route::is('staff.payments.*'))
        @include('layouts.pages-assets.css.finance-list-css')
    @endif
</head>

<body>
<div id="layout-wrapper">

    <!-- ========== SIDEBAR ========== -->
    <div class="app-menu navbar-menu">
        <!-- LOGO -->
        <div class="navbar-brand-box">
            @php
                use App\Models\SchoolInformation;
                $schoolInfo = SchoolInformation::getActiveSchool();
                $schoolName = $schoolInfo?->school_name ?? config('app.name', 'School System');
                $defaultLogo      = asset('theme/layouts/assets/images/logo-dark.png');
                $defaultLogoLight = asset('theme/layouts/assets/images/logo-light.png');
            @endphp

            <a href="{{ url('/') }}" class="logo logo-dark">
                <span class="logo-sm">
                    <img src="{{ $schoolInfo?->getLogoUrlAttribute() ?? $defaultLogo }}" alt="{{ $schoolName }}"
                         style="height:80px;width:auto;border-radius:10px;object-fit:contain;padding:3px;background:rgb(39,38,38);">
                </span>
                <span class="logo-lg">
                    <img src="{{ $schoolInfo?->getLogoUrlAttribute() ?? $defaultLogo }}" alt="{{ $schoolName }}"
                         style="height:80px;width:auto;border-radius:12px;object-fit:contain;padding:2px;background:rgb(37,36,36);">
                </span>
            </a>
            <a href="{{ url('/') }}" class="logo logo-light">
                <span class="logo-sm">
                    <img src="{{ $schoolInfo?->getLogoUrlAttribute() ?? $defaultLogoLight }}" alt="{{ $schoolName }}"
                         style="height:45px;width:auto;border-radius:10px;object-fit:contain;padding:3px;background:rgb(40,39,39);">
                </span>
                <span class="logo-lg">
                    <img src="{{ $schoolInfo?->getLogoUrlAttribute() ?? $defaultLogoLight }}" alt="{{ $schoolName }}"
                         style="height:80px;width:auto;border-radius:12px;object-fit:contain;padding:2px;background:rgb(37,36,36);">
                </span>
            </a>

            <button type="button" class="btn btn-sm p-0 fs-3xl header-item float-end btn-vertical-sm-hover" id="vertical-hover">
                <i class="ri-record-circle-line"></i>
            </button>
        </div>

        <!-- NAV (scrollable) -->
        <div id="scrollbar">
            <div class="container-fluid">
                <div id="two-column-menu"></div>
                <ul class="navbar-nav" id="navbar-nav">
                    <li class="menu-title"><span data-key="t-menu">Menu</span></li>
                    <li class="nav-item">
                        <a class="nav-link menu-link collapsed" href="#sidebarDashboards" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarDashboards">
                            <i class="ph-gauge"></i> <span data-key="t-dashboards">Dashboards</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarDashboards">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="{{ route('dashboard') }}" class="nav-link">Administration Analytics</a></li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- SIDEBAR LOGOUT FOOTER -->
        @auth
        <div class="sidebar-footer">
            @php
                $sidebarUser = Auth::user();
                $sidebarIsStudent = $sidebarUser->hasRole('student');
                $sidebarSrc = null;
                if ($sidebarIsStudent) {
                    $stu = \App\Models\Student::find($sidebarUser->student_id);
                    if ($stu?->picture) {
                        $bn = basename($stu->picture);
                        if (\Illuminate\Support\Facades\Storage::disk('public')->exists('student_avatars/' . $bn))
                            $sidebarSrc = asset('storage/student_avatars/' . $bn);
                    }
                } else {
                    if ($sidebarUser->avatar) {
                        $bn = basename($sidebarUser->avatar);
                        if (\Illuminate\Support\Facades\Storage::disk('public')->exists('staff_avatars/' . $bn))
                            $sidebarSrc = asset('storage/staff_avatars/' . $bn);
                    }
                }
                $sidebarInitials = collect(explode(' ', $sidebarUser->name))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->implode('');
            @endphp

            <div class="sidebar-footer-user">
                @if($sidebarSrc)
                    <img src="{{ $sidebarSrc }}" alt="{{ $sidebarUser->name }}">
                @else
                    <span class="sidebar-footer-avatar-initials">{{ $sidebarInitials }}</span>
                @endif
                <div class="sidebar-footer-user-info">
                    <div class="sidebar-footer-user-name">{{ $sidebarUser->name }}</div>
                    <div class="sidebar-footer-user-role">{{ $sidebarUser->roles->first()->name ?? 'User' }}</div>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}" id="sidebar-logout-form">
                @csrf
                <button type="submit" class="sidebar-logout-btn">
                    <i class="mdi mdi-logout"></i>
                    <span>Sign Out</span>
                </button>
            </form>
        </div>
        @endauth

        <div class="sidebar-background"></div>
    </div>

    <div class="vertical-overlay" id="vertical-overlay"></div>

    <!-- ========== TOPBAR ========== -->
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

                    <!-- Desktop Search Button -->
                    <div class="d-none d-md-inline-flex align-items-center position-relative desktop-search-btn">
                        <button type="button" id="spotlight-trigger-desktop" style="display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:10px;padding:7px 14px;cursor:pointer;transition:all .2s;min-width:220px;">
                            <i class="mdi mdi-magnify" style="font-size:16px;opacity:.6;"></i>
                            <span style="font-size:13px;opacity:.55;flex:1;text-align:left;">Search everything…</span>
                            <div style="display:flex;gap:4px;"><kbd style="font-size:10px;padding:2px 6px;border-radius:4px;background:rgba(255,255,255,.12);">⌘</kbd><kbd style="font-size:10px;padding:2px 6px;border-radius:4px;background:rgba(255,255,255,.12);">K</kbd></div>
                        </button>
                        <div class="search-tooltip">Press <kbd>⌘K</kbd> or <kbd>Ctrl+K</kbd> to search</div>
                    </div>

                    <!-- Mobile Search Button (Icon only) -->
                    <button type="button" id="spotlight-trigger-mobile" class="btn btn-icon btn-topbar btn-ghost-dark rounded-circle mobile-search-btn" style="width:38px;height:38px;margin-left:8px;">
                        <i class="mdi mdi-magnify fs-3xl"></i>
                    </button>
                </div>

                <div class="d-flex align-items-center gap-1">
                    <div class="position-relative" id="theme-toggle-wrapper">
                        <button type="button" id="theme-toggle-btn" class="btn btn-icon btn-topbar btn-ghost-dark rounded-circle" style="width:38px;height:38px;"><i id="theme-icon" class="bi bi-sun align-middle fs-3xl"></i></button>
                        <div id="theme-dropdown" style="display:none;position:absolute;top:calc(100% + 8px);right:0;min-width:170px;background:var(--vz-dropdown-bg,#fff);border:1px solid var(--vz-border-color,#e9ebec);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.12);z-index:9999;overflow:hidden;padding:6px;">
                            <a href="javascript:void(0)" class="theme-mode-item d-flex align-items-center gap-2 px-3 py-2 rounded-2 text-decoration-none" data-mode="light"><i class="bi bi-sun"></i> Light</a>
                            <a href="javascript:void(0)" class="theme-mode-item d-flex align-items-center gap-2 px-3 py-2 rounded-2 text-decoration-none" data-mode="dark"><i class="bi bi-moon"></i> Dark</a>
                            <a href="javascript:void(0)" class="theme-mode-item d-flex align-items-center gap-2 px-3 py-2 rounded-2 text-decoration-none" data-mode="auto"><i class="bi bi-moon-stars"></i> Auto</a>
                        </div>
                    </div>

                    <!-- USER DROPDOWN -->
                    @php
                        use App\Models\User as UserModel;
                        use App\Models\Student;
                        use Illuminate\Support\Facades\Storage;
                        use Illuminate\Support\Facades\Auth;

                        $userdata  = Auth::user();
                        $isStudent = $userdata->hasRole('student');
                        $fullName  = $userdata->name ?? 'User';
                        $initials  = collect(explode(' ', $fullName))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->implode('');
                        $srcPath   = null;

                        if ($isStudent) {
                            $student        = Student::where('id', $userdata->student_id)->first();
                            $studentPicture = $student?->picture;
                            if ($studentPicture) {
                                $basename = basename($studentPicture);
                                if (Storage::disk('public')->exists('student_avatars/' . $basename))
                                    $srcPath = asset('storage/student_avatars/' . $basename);
                            }
                        } else {
                            if ($userdata->avatar) {
                                $basename = basename($userdata->avatar);
                                if (Storage::disk('public')->exists('staff_avatars/' . $basename))
                                    $srcPath = asset('storage/staff_avatars/' . $basename);
                            }
                        }

                        $userRoles = $userdata->roles->pluck('name');
                    @endphp

                    <div class="dropdown position-relative ms-sm-3 header-item topbar-user" id="user-dropdown-wrapper">
                        <button type="button" id="user-menu-btn" class="btn shadow-none p-0" style="background:transparent;border:none;">
                            <span class="d-flex align-items-center gap-2">
                                <span style="display:inline-block;width:42px;height:42px;flex-shrink:0;position:relative;">
                                    @if($srcPath)
                                        <img id="topbar-avatar-img" src="{{ $srcPath }}" alt="{{ $fullName }}" style="width:42px;height:42px;border-radius:10px;object-fit:cover;" onerror="this.style.display='none';document.getElementById('topbar-avatar-fallback').style.display='flex';">
                                        <span id="topbar-avatar-fallback" style="display:none;width:42px;height:42px;border-radius:10px;background:#405189;color:#fff;align-items:center;justify-content:center;">{{ $initials }}</span>
                                    @else
                                        <span style="display:flex;width:42px;height:42px;border-radius:10px;background:#405189;color:#fff;align-items:center;justify-content:center;">{{ $initials }}</span>
                                    @endif
                                </span>
                                <span class="d-none d-xl-flex flex-column align-items-start ms-1"><span class="fw-medium" style="font-size:13px;">{{ $userdata->name }}</span></span>
                            </span>
                        </button>

                        <div id="user-dropdown" class="dropdown-menu dropdown-menu-end" style="display:none;position:absolute;top:calc(100% + 8px);right:0;min-width:220px;background:var(--vz-dropdown-bg,#fff);border-radius:12px;z-index:9999;">
                            <div class="dropdown-header"><h6 class="mb-0">Welcome back!</h6><small class="text-muted">{{ $userdata->name }}</small></div>
                            <div class="dropdown-divider"></div>
                            <div class="px-3 py-2">
                                <div class="small text-muted mb-2 text-uppercase" style="font-size:10px;">Your Roles</div>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($userRoles as $roleName)
                                        <span style="display:inline-block;font-size:10px;font-weight:600;padding:3px 10px;border-radius:20px;background:#eef2ff;color:#405189;">{{ $roleName }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            @if(!$isStudent)<a class="dropdown-item" href="{{ route('users.overview', $userdata->id) }}"><i class="mdi mdi-account-circle me-2"></i>My Profile</a>@endif
                            <a class="dropdown-item" href="{{ route('profile.settings', ['id' => $userdata->id]) }}"><i class="mdi mdi-cog me-2"></i>Account Settings</a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}" id="topbar-logout-form">@csrf<a class="dropdown-item text-danger" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('topbar-logout-form').submit();"><i class="mdi mdi-logout me-2"></i>Logout</a></form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- SPOTLIGHT SEARCH MODAL -->
    <div id="spotlight-overlay" style="display:none;position:fixed;inset:0;z-index:10000;align-items:flex-start;justify-content:center;padding-top:6vh;background:rgba(0,0,0,0);backdrop-filter:blur(0);">
        <div id="spotlight-box" style="width:100%;max-width:860px;margin:0 24px;background:rgba(24,26,32,.96);border:1px solid rgba(255,255,255,.1);border-radius:28px;box-shadow:0 32px 80px rgba(0,0,0,.6);overflow:hidden;transform:translateY(-40px) scale(0.9);opacity:0;transition:all 0.35s cubic-bezier(0.34, 1.3, 0.64, 1);">
            <div style="display:flex;align-items:center;gap:16px;padding:20px 24px;border-bottom:1px solid rgba(255,255,255,.08);">
                <i class="mdi mdi-magnify" style="font-size:26px;color:#4f8ef7;flex-shrink:0;"></i>
                <input id="spotlight-input" type="text" placeholder="Search for pages, students, staff, classes…" autocomplete="off" style="flex:1;background:transparent;border:none;outline:none;font-size:18px;color:#fff;caret-color:#4f8ef7;padding:8px 0;">
                <div style="display:flex;gap:8px;">
                    <button id="spotlight-clear-history" style="background:rgba(255,255,255,.08);border:none;border-radius:10px;padding:6px 12px;color:rgba(255,255,255,.6);font-size:12px;font-weight:500;cursor:pointer;">Clear History</button>
                    <kbd id="spotlight-esc" style="font-size:12px;padding:4px 10px;border-radius:8px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);color:rgba(255,255,255,.6);cursor:pointer;">ESC</kbd>
                </div>
            </div>
            <div id="spotlight-results" style="max-height:520px;overflow-y:auto;padding:12px 0;">
                <div id="spotlight-history-section" style="display:none;">
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 24px 8px;">
                        <span style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.4);">Recent Searches</span>
                        <button id="spotlight-clear-history-btn" style="background:transparent;border:none;color:rgba(255,255,255,.4);font-size:12px;cursor:pointer;">Clear All</button>
                    </div>
                    <div id="spotlight-history-list"></div>
                    <div style="height:1px;background:rgba(255,255,255,.06);margin:12px 20px;"></div>
                </div>
                <div id="spotlight-empty" style="padding:48px 24px;text-align:center;color:rgba(255,255,255,.35);">
                    <i class="mdi mdi-lightning-bolt" style="font-size:48px;display:block;margin-bottom:16px;opacity:.4;"></i>
                    <span style="font-size:15px;">Start typing to search…</span>
                </div>
                <ul id="spotlight-list" style="list-style:none;margin:0;padding:0;display:none;"></ul>
                <div id="spotlight-loading" style="display:none;padding:48px;text-align:center;">
                    <div style="display:inline-block;width:32px;height:32px;border:2px solid rgba(255,255,255,.15);border-top-color:#4f8ef7;border-radius:50%;animation:loadingSpin .7s linear infinite;"></div>
                    <div style="margin-top:16px;font-size:13px;color:rgba(255,255,255,.45);">Searching<span class="typing-dot">.</span><span class="typing-dot">.</span><span class="typing-dot">.</span></div>
                </div>
            </div>
            <div style="padding:14px 24px;border-top:1px solid rgba(255,255,255,.07);display:flex;gap:24px;font-size:12px;color:rgba(255,255,255,.35);flex-wrap:wrap;">
                <span><kbd style="background:rgba(255,255,255,.1);border-radius:5px;padding:2px 6px;">⌘K</kbd> / <kbd style="background:rgba(255,255,255,.1);border-radius:5px;padding:2px 6px;">Ctrl+K</kbd> open</span>
                <span><kbd style="background:rgba(255,255,255,.1);border-radius:5px;padding:2px 6px;">↑↓</kbd> navigate</span>
                <span><kbd style="background:rgba(255,255,255,.1);border-radius:5px;padding:2px 6px;">↵</kbd> open</span>
                <span><kbd style="background:rgba(255,255,255,.1);border-radius:5px;padding:2px 6px;">ESC</kbd> close</span>
            </div>
        </div>
    </div>

    @yield('content')

    <footer class="footer">
        <div class="container-fluid">
            <div class="row"><div class="col-sm-6"><script>document.write(new Date().getFullYear())</script> © {{ $school->school_name ?? 'Vite-ESchool' }}</div><div class="col-sm-6"><div class="text-sm-end d-none d-sm-block">Created by Qudroid Systems</div></div></div>
        </div>
    </footer>
</div>

<button class="btn btn-dark btn-icon" id="back-to-top"><i class="bi bi-caret-up fs-3xl"></i></button>
<div id="preloader"><div id="status"><div class="spinner-border text-primary avatar-sm" role="status"><span class="visually-hidden">Loading...</span></div></div></div>

<script src="{{ asset('theme/layouts/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/js/app.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/js/plugins.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
(function() {
    'use strict';

    // =====================================================
    // SIDEBAR TOGGLE (Mobile & Desktop with Animation)
    // =====================================================
    const ham = document.getElementById('topnav-hamburger-icon');
    const overlay = document.getElementById('vertical-overlay');
    const body = document.body;

    function closeSidebar() {
        body.classList.remove('vertical-sidebar-enable');
    }
    function openSidebar() {
        body.classList.add('vertical-sidebar-enable');
    }
    function toggleSidebar(e) {
        if (e) { e.preventDefault(); e.stopPropagation(); }
        body.classList.contains('vertical-sidebar-enable') ? closeSidebar() : openSidebar();
    }

    if (ham) {
        const newHam = ham.cloneNode(true);
        ham.parentNode.replaceChild(newHam, ham);
        const freshHam = document.getElementById('topnav-hamburger-icon');
        if (freshHam) freshHam.addEventListener('click', toggleSidebar);
    }
    if (overlay) overlay.addEventListener('click', closeSidebar);
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && body.classList.contains('vertical-sidebar-enable')) closeSidebar();
    });

    // =====================================================
    // SPOTLIGHT SEARCH - Full Functionality
    // =====================================================
    const STATIC_PAGES = [
        {title:'Administration Dashboard', url:'{{ route("dashboard") }}', icon:'mdi-gauge', category:'Dashboards'},
        {title:'User Management', url:'{{ route("users.index") }}', icon:'mdi-account-group', category:'Users & Privileges'},
        {title:'Roles & Permissions', url:'{{ route("roles.index") }}', icon:'mdi-shield-account', category:'Users & Privileges'},
        {title:'All Students', url:'{{ route("student.index") }}', icon:'mdi-school', category:'Students'},
        {title:'My Profile', url:'{{ route("users.overview", ["id" => Auth::id()]) }}', icon:'mdi-account-circle', category:'My Account'},
        {title:'Account Settings', url:'{{ route("profile.settings", ["id" => Auth::id()]) }}', icon:'mdi-cog', category:'My Account'},
        {title:'School Information', url:'{{ route("school-information.index") }}', icon:'mdi-domain', category:'School Settings'},
        {title:'School Session', url:'{{ route("session.index") }}', icon:'mdi-calendar-range', category:'School Settings'},
        {title:'School Term', url:'{{ route("term.index") }}', icon:'mdi-calendar', category:'School Settings'},
        {title:'Subjects', url:'{{ route("subject.index") }}', icon:'mdi-book-open-variant', category:'Subjects'},
        {title:'My Class', url:'{{ route("myclass.index") }}', icon:'mdi-google-classroom', category:'Classes & Records'},
        {title:'Terminal Records', url:'{{ route("myresultroom.index") }}', icon:'mdi-file-chart', category:'Records & Results'},
        {title:'Student Bill', url:'{{ route("schoolpayment.index") }}', icon:'mdi-receipt', category:'Finance'},
        {title:'Payment Portal', url:'{{ route("payment.index") }}', icon:'mdi-wallet', category:'Finance'},
        {title:'All Examinations', url:'{{ route("exams.index") }}', icon:'mdi-clipboard-text', category:'Exams & CBT'},
        {title:'CBT Exercise', url:'{{ route("cbt.index") }}', icon:'mdi-monitor', category:'Exams & CBT'},
        {title:'Admin Timetable', url:'{{ route("timetable.index") }}', icon:'mdi-table-clock', category:'Timetable'},
        {title:'Mark Attendance', url:'{{ route("attendance.my-classes") }}', icon:'mdi-clipboard-check', category:'Attendance'},
        {title:'Balance Sheet', url:'{{ route("reports.financial.balance-sheet") }}', icon:'mdi-scale-balance', category:'Accounting'},
        {title:'Generate Transcript', url:'{{ route("transcript.index") }}', icon:'mdi-file-account', category:'Transcripts'},
    ];

    const CAT_COLORS = {
        'Dashboards':'#4f8ef7','Users & Privileges':'#405189','Students':'#e76f51','My Account':'#2a9d8f',
        'School Settings':'#6a0572','Subjects':'#e9c46a','Classes & Records':'#0a9396','Records & Results':'#457b9d',
        'Finance':'#10b981','Exams & CBT':'#f4a261','Timetable':'#4f8ef7','Attendance':'#e9c46a','Accounting':'#10b981','Transcripts':'#457b9d'
    };

    const HISTORY_KEY = 'spotlight_search_history';
    function getHistory() { try { return JSON.parse(localStorage.getItem(HISTORY_KEY) || '[]'); } catch(e) { return []; } }
    function saveHistory(h) { localStorage.setItem(HISTORY_KEY, JSON.stringify(h.slice(0,10))); }

    const overlaySpot = document.getElementById('spotlight-overlay');
    const spotlightBox = document.getElementById('spotlight-box');
    const input = document.getElementById('spotlight-input');
    const emptyEl = document.getElementById('spotlight-empty');
    const loadEl = document.getElementById('spotlight-loading');
    const list = document.getElementById('spotlight-list');
    const triggerDesktop = document.getElementById('spotlight-trigger-desktop');
    const triggerMobile = document.getElementById('spotlight-trigger-mobile');
    const escBtn = document.getElementById('spotlight-esc');
    const histSec = document.getElementById('spotlight-history-section');
    const histList = document.getElementById('spotlight-history-list');
    const clearBtn = document.getElementById('spotlight-clear-history-btn');
    const clearMain = document.getElementById('spotlight-clear-history');

    let timer = null, activeIndex = -1, currentResults = [];

    function openSpotlight() {
        if (!overlaySpot) return;
        overlaySpot.style.display = 'flex';
        overlaySpot.style.background = 'rgba(0,0,0,.65)';
        overlaySpot.style.backdropFilter = 'blur(8px)';
        if (spotlightBox) {
            spotlightBox.style.transform = 'translateY(0) scale(1)';
            spotlightBox.style.opacity = '1';
        }
        if (input) setTimeout(() => input.focus(), 100);
        renderHistory();
        if (clearMain) clearMain.style.display = getHistory().length > 0 ? 'block' : 'none';
    }

    function closeSpotlight() {
        if (spotlightBox) {
            spotlightBox.style.transform = 'translateY(-40px) scale(0.9)';
            spotlightBox.style.opacity = '0';
        }
        if (overlaySpot) {
            overlaySpot.style.background = 'rgba(0,0,0,0)';
            overlaySpot.style.backdropFilter = 'blur(0)';
            setTimeout(() => { overlaySpot.style.display = 'none'; }, 200);
        }
        if (input) input.value = '';
        showEmpty();
    }

    function showEmpty() {
        if (emptyEl) emptyEl.style.display = 'block';
        if (loadEl) loadEl.style.display = 'none';
        if (list) { list.style.display = 'none'; list.innerHTML = ''; }
        renderHistory();
        currentResults = [];
        activeIndex = -1;
    }

    function showLoading() {
        if (emptyEl) emptyEl.style.display = 'none';
        if (loadEl) loadEl.style.display = 'block';
        if (list) list.style.display = 'none';
        if (histSec) histSec.style.display = 'none';
    }

    function renderHistory() {
        const h = getHistory();
        if (h.length > 0 && (!input || !input.value.trim())) {
            if (histSec) histSec.style.display = 'block';
            if (histList) histList.innerHTML = '';
            if (clearMain) clearMain.style.display = 'block';
            h.forEach((item, idx) => {
                const div = document.createElement('div');
                div.className = 'spotlight-history-item';
                div.style.cssText = 'display:flex;align-items:center;gap:14px;padding:10px 24px;cursor:pointer;transition:background .15s;border-radius:10px;margin:0 16px;';
                const c = CAT_COLORS[item.category] || '#4f8ef7';
                div.innerHTML = `<span style="width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:${c}22;"><i class="${item.icon || 'mdi-history'} mdi" style="font-size:16px;color:${c};"></i></span>
                    <span style="flex:1;min-width:0;"><span style="display:block;font-size:14px;font-weight:500;color:rgba(255,255,255,.9);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${item.title}</span><span style="display:block;font-size:11px;color:rgba(255,255,255,.4);margin-top:2px;">${item.query}</span></span>
                    <button class="hist-remove" style="background:transparent;border:none;color:rgba(255,255,255,.35);cursor:pointer;font-size:13px;padding:6px 10px;border-radius:6px;">✕</button>`;
                div.querySelector('.hist-remove').addEventListener('click', (e) => {
                    e.stopPropagation();
                    const his = getHistory();
                    his.splice(idx, 1);
                    saveHistory(his);
                    renderHistory();
                    if (!input || !input.value.trim()) showEmpty();
                });
                div.addEventListener('click', () => { if (input) { input.value = item.query; performSearch(item.query); } });
                if (histList) histList.appendChild(div);
            });
        } else {
            if (histSec) histSec.style.display = 'none';
            if (clearMain) clearMain.style.display = 'none';
        }
    }

    function performSearch(query) {
        if (!query || !query.trim()) { showEmpty(); return; }
        showLoading();
        const results = STATIC_PAGES.filter(p => p.title.toLowerCase().includes(query.toLowerCase()) || p.category.toLowerCase().includes(query.toLowerCase())).slice(0,15);
        renderResults(results);
    }

    function renderResults(results) {
        if (loadEl) loadEl.style.display = 'none';
        if (emptyEl) emptyEl.style.display = 'none';
        if (list) { list.innerHTML = ''; list.style.display = 'block'; }
        if (histSec) histSec.style.display = 'none';
        activeIndex = -1;
        currentResults = results;

        if (!results.length) {
            if (emptyEl) { emptyEl.innerHTML = '<i class="mdi mdi-magnify-close" style="font-size:42px;display:block;margin-bottom:16px;opacity:.4;"></i><span style="font-size:15px;">No results for "' + (input ? input.value : '') + '"</span>'; emptyEl.style.display = 'block'; }
            if (list) list.style.display = 'none';
            return;
        }

        const grouped = {};
        results.forEach(r => { if (!grouped[r.category]) grouped[r.category] = []; grouped[r.category].push(r); });

        let idx = 0;
        Object.keys(grouped).forEach(cat => {
            const header = document.createElement('li');
            header.style.cssText = 'padding:12px 24px 6px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.35);';
            header.textContent = cat;
            list.appendChild(header);

            grouped[cat].forEach((r, gi) => {
                const li = document.createElement('li');
                const isTop = (idx === 0 && gi === 0);
                li.className = 'spotlight-result-item' + (isTop ? ' top-match' : '');
                li.setAttribute('data-idx', idx);
                li.style.cssText = 'display:flex;align-items:center;gap:14px;padding:12px 24px;cursor:pointer;transition:all .2s;border-radius:10px;margin:4px 12px;';
                const c = CAT_COLORS[r.category] || '#4f8ef7';
                li.innerHTML = `<span style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:${c}22;"><i class="${r.icon || 'mdi-chevron-right'} mdi" style="font-size:18px;color:${c};"></i></span>
                    <span style="flex:1;min-width:0;"><span class="result-title" style="display:block;font-size:15px;font-weight:500;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${r.title}</span><span style="display:block;font-size:12px;color:rgba(255,255,255,.4);margin-top:2px;">${r.subtitle || r.category}</span></span>
                    <i class="mdi mdi-arrow-right" style="font-size:16px;color:rgba(255,255,255,.25);flex-shrink:0;transition:transform .2s;"></i>`;
                li.addEventListener('mouseenter', () => { li.style.background = 'rgba(79,142,247,.12)'; activeIndex = idx; });
                li.addEventListener('mouseleave', () => { li.style.background = activeIndex === idx ? 'rgba(79,142,247,.18)' : ''; });
                li.addEventListener('click', () => { window.location.href = r.url; });
                list.appendChild(li);
                idx++;
            });
        });
    }

    if (triggerDesktop) triggerDesktop.addEventListener('click', openSpotlight);
    if (triggerMobile) triggerMobile.addEventListener('click', openSpotlight);
    if (escBtn) escBtn.addEventListener('click', closeSpotlight);
    if (clearBtn) clearBtn.addEventListener('click', () => { localStorage.removeItem(HISTORY_KEY); renderHistory(); showEmpty(); });
    if (clearMain) clearMain.addEventListener('click', () => { localStorage.removeItem(HISTORY_KEY); renderHistory(); showEmpty(); });
    if (overlaySpot) overlaySpot.addEventListener('click', (e) => { if (e.target === overlaySpot) closeSpotlight(); });

    document.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            overlaySpot && overlaySpot.style.display === 'flex' ? closeSpotlight() : openSpotlight();
        }
        if (e.key === 'Escape' && overlaySpot && overlaySpot.style.display === 'flex') closeSpotlight();
    });

    if (input) {
        input.addEventListener('input', () => {
            const q = input.value.trim();
            if (!q) { showEmpty(); return; }
            performSearch(q);
        });
    }

    // =====================================================
    // MANUAL DROPDOWNS & THEME
    // =====================================================
    function makeDropdown(btnId, panelId) {
        const btn = document.getElementById(btnId), panel = document.getElementById(panelId);
        if (!btn || !panel) return;
        const open = () => { panel.style.display = 'block'; btn.setAttribute('aria-expanded', 'true'); };
        const close = () => { panel.style.display = 'none'; btn.setAttribute('aria-expanded', 'false'); };
        btn.addEventListener('click', (e) => { e.stopPropagation(); panel.style.display === 'none' ? open() : close(); });
        document.addEventListener('click', (e) => { if (!btn.contains(e.target) && !panel.contains(e.target)) close(); });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
    }
    makeDropdown('theme-toggle-btn', 'theme-dropdown');
    makeDropdown('user-menu-btn', 'user-dropdown');

    function initTheme() {
        const html = document.documentElement;
        const iconEl = document.getElementById('theme-icon');
        const applyMode = (mode) => {
            const scheme = mode === 'auto' ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') : mode;
            html.setAttribute('data-bs-theme', scheme);
            html.setAttribute('data-topbar', scheme === 'dark' ? 'dark' : 'light');
            if (iconEl) iconEl.className = mode === 'light' ? 'bi bi-sun align-middle fs-3xl' : (mode === 'dark' ? 'bi bi-moon align-middle fs-3xl' : 'bi bi-moon-stars align-middle fs-3xl');
            localStorage.setItem('app-theme', mode);
        };
        applyMode(localStorage.getItem('app-theme') || 'light');
        document.querySelectorAll('.theme-mode-item').forEach(a => a.addEventListener('click', (e) => { e.preventDefault(); applyMode(a.getAttribute('data-mode')); }));
    }
    initTheme();

    // Back to top
    const backBtn = document.getElementById('back-to-top');
    if (backBtn) {
        window.addEventListener('scroll', () => backBtn.classList.toggle('show', window.scrollY > 300));
        backBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    }

    // Active sidebar highlight
    const curPath = window.location.pathname;
    document.querySelectorAll('#navbar-nav .nav-sm a.nav-link').forEach(link => {
        try {
            if (new URL(link.href, window.location.origin).pathname === curPath) {
                link.classList.add('nav-active-child');
                const col = link.closest('.collapse');
                if (col) {
                    col.classList.add('show');
                    const tog = document.querySelector('[data-bs-target="#' + col.id + '"]');
                    if (tog) {
                        tog.setAttribute('aria-expanded', 'true');
                        tog.classList.remove('collapsed');
                        tog.classList.add('nav-active-parent');
                    }
                }
            }
        } catch(e) {}
    });
})();
</script>

<!-- Route-specific JS includes -->
@if (Route::is('dashboard')) @include('layouts.pages-assets.js.dashboard-list-js') @endif
</body>
</html>
