<!doctype html>
<html lang="en" data-layout="vertical" data-sidebar="dark" data-sidebar-size="lg" data-preloader="disable" data-theme="default" data-topbar="light" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <title>{{ $pagetitle }} | Vite-ESchool 2.0</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="school management software" name="description">
    <meta content="" name="author">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $activeSchool = App\Models\SchoolInformation::getActiveSchool();
        $faviconUrl = $activeSchool ? $activeSchool->getLogoWithFallbackAttribute() : asset('theme/layouts/assets/images/favicon.ico');
    @endphp
    <link rel="icon" type="image/png" href="{{ $faviconUrl }}">
    <link rel="shortcut icon" type="image/png" href="{{ $faviconUrl }}">

    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link id="fontsLink" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/bold/style.css">
    <link href="{{ asset('theme/layouts/assets/fonts/materialdesignicons-webfont.woff2') }}?v=6.5.95" rel="stylesheet" type="font/woff2">

    <link href="{{ asset('theme/layouts/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('theme/layouts/assets/css/icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('theme/layouts/assets/css/app.min.css') }}" rel="stylesheet">
    <link href="{{ asset('theme/layouts/assets/css/custom.min.css') }}" rel="stylesheet">
    <script src="{{ asset('theme/layouts/assets/js/layout.js') }}"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.css"/>
    <script src="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        /* =====================================================
           CSS CUSTOM PROPERTIES & KEYFRAMES
           ===================================================== */
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 70px;
            --topbar-height: 60px;
            --transition-smooth: 0.32s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-bounce: 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
            --transition-snap: 0.18s cubic-bezier(0.4, 0, 0.2, 1);
            --accent: #4f8ef7;
            --danger-soft: rgba(239,68,68,.12);
            --danger-border: rgba(239,68,68,.22);
        }

        /* =====================================================
           PAGE ENTRANCE ANIMATIONS
           ===================================================== */
        @keyframes pageFadeSlideIn {
            0%  { opacity: 0; transform: translateY(10px); }
            100%{ opacity: 1; transform: translateY(0); }
        }
        @keyframes sidebarSlideRight {
            0%  { opacity: 0; transform: translateX(-18px); }
            100%{ opacity: 1; transform: translateX(0); }
        }
        @keyframes topbarDropDown {
            0%  { opacity: 0; transform: translateY(-14px); }
            100%{ opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeScaleIn {
            0%  { opacity: 0; transform: scale(0.88); }
            100%{ opacity: 1; transform: scale(1); }
        }
        @keyframes dotPop {
            0%  { transform: scale(0); opacity: 0; }
            60% { transform: scale(1.3); opacity: 1; }
            100%{ transform: scale(1);   opacity: 1; }
        }
        @keyframes navItemFadeIn {
            0%  { opacity: 0; transform: translateX(-10px); }
            100%{ opacity: 1; transform: translateX(0); }
        }
        @keyframes rippleAnim {
            to { transform: scale(5); opacity: 0; }
        }
        @keyframes spin {
            0%   { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes loadingSpin {
            0%   { transform: rotate(0); }
            100% { transform: rotate(360deg); }
        }
        @keyframes dropdownSlideIn {
            0%  { opacity: 0; transform: translateY(-10px) scale(0.96); }
            100%{ opacity: 1; transform: translateY(0)   scale(1); }
        }
        @keyframes dropdownSlideOut {
            0%  { opacity: 1; transform: translateY(0)   scale(1); }
            100%{ opacity: 0; transform: translateY(-8px) scale(0.95); }
        }
        @keyframes spotlightIn {
            0%  { opacity: 0; transform: translateY(-40px) scale(0.88); }
            55% { opacity: 0.9; transform: translateY(6px) scale(1.015); }
            100%{ opacity: 1; transform: translateY(0)    scale(1); }
        }
        @keyframes spotlightOut {
            0%  { opacity: 1; transform: translateY(0) scale(1); }
            100%{ opacity: 0; transform: translateY(-22px) scale(0.94); }
        }
        @keyframes overlayIn {
            0%  { background: rgba(0,0,0,0); backdrop-filter: blur(0); }
            100%{ background: rgba(0,0,0,.65); backdrop-filter: blur(8px); }
        }
        @keyframes overlayOut {
            0%  { background: rgba(0,0,0,.65); backdrop-filter: blur(8px); }
            100%{ background: rgba(0,0,0,0);   backdrop-filter: blur(0); }
        }
        @keyframes resultBounce {
            0%  { opacity: 0; transform: translateX(-16px) scale(0.95); }
            65% { opacity: 0.85; transform: translateX(3px) scale(1.01); }
            100%{ opacity: 1; transform: translateX(0) scale(1); }
        }
        @keyframes typingDot {
            0%,60%,100%{ transform: translateY(0);   opacity: .45; }
            30%         { transform: translateY(-5px); opacity: 1;   }
        }
        @keyframes shimmer {
            0%  { background-position: -200% 0; }
            100%{ background-position:  200% 0; }
        }
        @keyframes footerSlideUp {
            0%  { opacity: 0; transform: translateY(20px); }
            100%{ opacity: 1; transform: translateY(0); }
        }
        @keyframes pulseGlow {
            0%,100%{ box-shadow: 0 0 0 0 rgba(79,142,247,.35); }
            50%     { box-shadow: 0 0 0 6px rgba(79,142,247,0); }
        }
        @keyframes logoEntrance {
            0%  { opacity: 0; transform: scale(0.7) rotate(-8deg); }
            65% { opacity: 1; transform: scale(1.08) rotate(2deg); }
            100%{ opacity: 1; transform: scale(1) rotate(0deg); }
        }

        /* =====================================================
           NPROGRESS
           ===================================================== */
        #nprogress .bar    { background: var(--accent) !important; height: 3px !important; box-shadow: 0 0 10px rgba(79,142,247,.7) !important; }
        #nprogress .peg    { box-shadow: none !important; }
        #nprogress .spinner{ display: none !important; }

        /* =====================================================
           BASE UI
           ===================================================== */
        .form-check-input:checked { background-color: #405189; border-color: #405189; }
        .swal2-toast { font-size: 14px !important; }
        .swal2-container.swal2-top-end { top: 70px !important; }
        .table tbody tr { transition: background-color .15s ease; }
        .table tbody tr:hover { background-color: rgba(67,97,238,.05); }
        .modal.fade .modal-dialog { transform: translate(0,-50px); transition: transform .3s ease-out; }
        .modal.show .modal-dialog { transform: translate(0,0); }
        .spin { animation: spin 1s linear infinite; }
        .card { transition: box-shadow var(--transition-smooth), transform var(--transition-smooth); }
        .card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.1); transform: translateY(-1px); }
        .btn  { transition: transform var(--transition-snap), box-shadow var(--transition-snap), background var(--transition-snap); }
        .btn:active { transform: scale(0.96); }
        .pagination-wrap .page-item { margin: 0 3px; }
        .pagination-wrap .page-link { padding: 5px 10px; transition: all var(--transition-snap); }
        .pagination-wrap .active .page-link { background-color: var(--accent); color: #fff; }
        .pagination-wrap .disabled .page-link { pointer-events: none; opacity: .5; }

        /* =====================================================
           SIDEBAR STRUCTURE
           ===================================================== */
        .app-menu {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: var(--sidebar-width);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: width var(--transition-smooth), transform var(--transition-smooth), box-shadow var(--transition-smooth);
        }
        .navbar-brand-box {
            flex-shrink: 0;
            animation: logoEntrance 0.6s cubic-bezier(0.34,1.56,0.64,1) both;
        }
        #scrollbar {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
        }
        #scrollbar::-webkit-scrollbar       { width: 3px; }
        #scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,.04); border-radius: 4px; }
        #scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.18); border-radius: 4px; }
        #scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,.32); }
        .navbar-menu .container-fluid { padding: 0; }
        #navbar-nav { padding-bottom: 4px; }

        /* =====================================================
           SIDEBAR NAV ITEMS — stagger animation
           ===================================================== */
        #navbar-nav > li { animation: navItemFadeIn 0.38s ease both; }
        #navbar-nav > li:nth-child(1)  { animation-delay: .03s }
        #navbar-nav > li:nth-child(2)  { animation-delay: .06s }
        #navbar-nav > li:nth-child(3)  { animation-delay: .09s }
        #navbar-nav > li:nth-child(4)  { animation-delay: .12s }
        #navbar-nav > li:nth-child(5)  { animation-delay: .15s }
        #navbar-nav > li:nth-child(6)  { animation-delay: .18s }
        #navbar-nav > li:nth-child(7)  { animation-delay: .21s }
        #navbar-nav > li:nth-child(8)  { animation-delay: .24s }
        #navbar-nav > li:nth-child(9)  { animation-delay: .27s }
        #navbar-nav > li:nth-child(10) { animation-delay: .30s }
        #navbar-nav > li:nth-child(11) { animation-delay: .33s }
        #navbar-nav > li:nth-child(12) { animation-delay: .36s }
        #navbar-nav > li:nth-child(n+13){ animation-delay: .38s }

        /* =====================================================
           SIDEBAR ACTIVE STATES
           ===================================================== */
        #navbar-nav .menu-dropdown { overflow: hidden; }
        #navbar-nav .nav-link.menu-link .ri-arrow-down-s-line {
            transition: transform .28s cubic-bezier(0.4,0,0.2,1);
            display: inline-block;
        }
        #navbar-nav .nav-link.menu-link[aria-expanded="true"] .ri-arrow-down-s-line { transform: rotate(180deg); }

        #navbar-nav .nav-link.menu-link.nav-active-parent {
            color: #fff !important;
            background: rgba(79,142,247,.2) !important;
            border-left: 3px solid var(--accent);
            padding-left: calc(1.3rem - 3px);
        }
        #navbar-nav .nav-link.menu-link.nav-active-parent i { color: var(--accent) !important; }

        #navbar-nav .nav-sm .nav-link.nav-active-child { color: #7eb8fb !important; font-weight: 500; }
        #navbar-nav .nav-sm .nav-link.nav-active-child::before {
            content: ''; display: inline-block;
            width: 5px; height: 5px; border-radius: 50%;
            background: var(--accent); margin-right: 8px;
            box-shadow: 0 0 0 3px rgba(79,142,247,.28);
            vertical-align: middle; flex-shrink: 0;
            animation: dotPop .3s cubic-bezier(0.34,1.56,0.64,1);
        }

        /* =====================================================
           RIPPLE
           ===================================================== */
        #navbar-nav .nav-link {
            position: relative; overflow: hidden;
            transition: color .18s, background-color .2s, padding-left .2s;
        }
        .nav-ripple {
            position: absolute; border-radius: 50%;
            background: rgba(255,255,255,.16);
            transform: scale(0);
            animation: rippleAnim .6s linear;
            pointer-events: none; z-index: 0;
        }

        /* =====================================================
           SIDEBAR FOOTER — compact, pinned
           ===================================================== */
        .sidebar-footer {
            flex-shrink: 0;
            border-top: 1px solid rgba(255,255,255,.08);
            padding: 10px 12px 12px;
            background: inherit;
            animation: footerSlideUp 0.5s 0.2s cubic-bezier(0.34,1.3,0.64,1) both;
        }
        .sidebar-footer-user {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }
        .sidebar-footer-user img,
        .sidebar-footer-avatar-initials {
            width: 30px; height: 30px;
            border-radius: 8px;
            object-fit: cover;
            flex-shrink: 0;
            border: 1.5px solid rgba(255,255,255,.18);
            transition: transform var(--transition-bounce), box-shadow var(--transition-smooth);
        }
        .sidebar-footer-avatar-initials {
            background: #405189; color: #fff;
            font-size: 11px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
        }
        .sidebar-footer-user img:hover,
        .sidebar-footer-avatar-initials:hover {
            transform: scale(1.12);
            box-shadow: 0 0 0 3px rgba(79,142,247,.4);
        }
        .sidebar-footer-user-info { min-width: 0; flex: 1; line-height: 1.25; }
        .sidebar-footer-user-name {
            font-size: 12px; font-weight: 600; color: #fff;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .sidebar-footer-user-role {
            font-size: 10px; color: rgba(255,255,255,.42);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .sidebar-logout-btn {
            display: flex; align-items: center; justify-content: center; gap: 7px;
            width: 100%; padding: 7px 10px;
            border-radius: 7px;
            background: var(--danger-soft);
            border: 1px solid var(--danger-border);
            color: #f87171; font-size: 12px; font-weight: 500;
            cursor: pointer;
            transition: background .22s, border-color .22s, color .22s, transform var(--transition-snap);
            position: relative; overflow: hidden;
        }
        .sidebar-logout-btn::after {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.06), transparent);
            transform: translateX(-100%);
            transition: transform .5s ease;
        }
        .sidebar-logout-btn:hover { background: rgba(239,68,68,.22); border-color: rgba(239,68,68,.42); color: #fca5a5; transform: translateY(-1px); }
        .sidebar-logout-btn:hover::after { transform: translateX(100%); }
        .sidebar-logout-btn:active { transform: scale(0.97); }
        .sidebar-logout-btn i { font-size: 15px; flex-shrink: 0; }

        /* =====================================================
           TOPBAR ANIMATIONS
           ===================================================== */
        #page-topbar {
            animation: topbarDropDown 0.4s 0.05s cubic-bezier(0.34,1.3,0.64,1) both;
        }
        #page-topbar .header-item { transition: color .2s, background-color .2s; }
        .header-profile-user-enhanced {
            transition: transform var(--transition-bounce), box-shadow var(--transition-smooth);
        }
        .header-profile-user-enhanced:hover {
            transform: scale(1.08);
            box-shadow: 0 0 0 3px rgba(79,142,247,.38) !important;
        }

        /* =====================================================
           PAGE CONTENT
           ===================================================== */
        .page-content { animation: pageFadeSlideIn 0.38s ease both; }

        /* =====================================================
           DROPDOWN ANIMATIONS
           ===================================================== */
        .dropdown-menu {
            animation: dropdownSlideIn 0.22s cubic-bezier(0.4,0,0.2,1) both;
            transform-origin: top right;
        }

        /* =====================================================
           MOBILE SIDEBAR SLIDE
           ===================================================== */
        .vertical-overlay {
            position: fixed; inset: 0; z-index: 999;
            background: rgba(0,0,0,0);
            display: none;
            transition: background 0.35s ease, backdrop-filter 0.35s ease;
        }

        @media (max-width: 1024.98px) {
            .app-menu {
                transform: translateX(-100%);
                box-shadow: none;
                width: 280px;
            }
            /* Logo ALWAYS visible in mobile sidebar */
            .app-menu .navbar-brand-box { display: flex !important; }

            body.vertical-sidebar-enable .app-menu {
                transform: translateX(0) !important;
                box-shadow: 8px 0 40px rgba(0,0,0,.5);
            }
            .vertical-overlay {
                display: block !important;
                opacity: 0; pointer-events: none;
                backdrop-filter: blur(0);
            }
            body.vertical-sidebar-enable .vertical-overlay {
                opacity: 1 !important; pointer-events: auto;
                background: rgba(0,0,0,.6);
                backdrop-filter: blur(3px);
            }
        }

        @media (min-width: 1025px) {
            .main-content { margin-left: var(--sidebar-width); transition: margin-left var(--transition-smooth); }
            #page-topbar { left: var(--sidebar-width); transition: left var(--transition-smooth); }
            body.sidebar-collapsed .app-menu { width: var(--sidebar-collapsed-width) !important; }
            body.sidebar-collapsed .main-content { margin-left: var(--sidebar-collapsed-width); }
            body.sidebar-collapsed #page-topbar { left: var(--sidebar-collapsed-width); }
            body.sidebar-collapsed .app-menu .navbar-brand-box .logo-lg { display: none !important; }
            body.sidebar-collapsed .app-menu .navbar-brand-box .logo-sm { display: block !important; }
            body.sidebar-collapsed #navbar-nav .menu-title { display: none; }
            body.sidebar-collapsed #navbar-nav .nav-link span:not(.badge) { display: none; }
            body.sidebar-collapsed .sidebar-footer-user-info,
            body.sidebar-collapsed .sidebar-logout-btn span { display: none; }
            body.sidebar-collapsed .sidebar-logout-btn { padding: 7px; }
            body.sidebar-collapsed .sidebar-footer-user { justify-content: center; }
            body.sidebar-collapsed .app-menu .nav-link { justify-content: center; }
            .vertical-overlay { display: none !important; }
        }

        /* =====================================================
           BACK TO TOP
           ===================================================== */
        #back-to-top {
            opacity: 0; visibility: hidden; transform: translateY(14px);
            transition: opacity .3s, transform .3s, visibility .3s;
        }
        #back-to-top.show { opacity: 1; visibility: visible; transform: translateY(0); animation: pulseGlow 2s ease 0.3s; }
        #back-to-top:hover { transform: translateY(-4px) !important; }

        /* =====================================================
           SPOTLIGHT SEARCH
           ===================================================== */
        @keyframes spotlightOverlayFadeIn  { from{background:rgba(0,0,0,.2);backdrop-filter:blur(0)}  to{background:rgba(0,0,0,.65);backdrop-filter:blur(8px)} }
        @keyframes spotlightOverlayFadeOut { from{background:rgba(0,0,0,.65);backdrop-filter:blur(8px)} to{background:rgba(0,0,0,.2);backdrop-filter:blur(0)} }
        @keyframes spotlightModalBounceIn  { 0%{opacity:0;transform:translateY(-40px) scale(.9)} 55%{opacity:.9;transform:translateY(6px) scale(1.01)} 100%{opacity:1;transform:translateY(0) scale(1)} }
        @keyframes spotlightModalFadeOut   { 0%{opacity:1;transform:translateY(0) scale(1)} 100%{opacity:0;transform:translateY(-20px) scale(.95)} }

        .spotlight-result-item {
            animation: resultBounce 0.32s cubic-bezier(0.34,1.3,0.64,1) forwards;
            opacity: 0;
        }
        .spotlight-result-item:nth-child(1){ animation-delay:.00s }
        .spotlight-result-item:nth-child(2){ animation-delay:.04s }
        .spotlight-result-item:nth-child(3){ animation-delay:.08s }
        .spotlight-result-item:nth-child(4){ animation-delay:.12s }

        .typing-dot { display:inline-block; animation:typingDot 1.4s infinite ease-in-out; }
        .typing-dot:nth-child(2){ animation-delay:.2s }
        .typing-dot:nth-child(3){ animation-delay:.4s }

        .search-tooltip {
            position: absolute; bottom: -35px; left: 0;
            background: rgba(0,0,0,.85); color: #fff; font-size: 11px;
            padding: 4px 10px; border-radius: 6px; white-space: nowrap;
            opacity: 0; transition: opacity .2s; pointer-events: none;
            z-index: 100; backdrop-filter: blur(4px);
        }

        /* =====================================================
           FINANCE CARDS
           ===================================================== */
        .finance-stat-card { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); border-radius: 12px; padding: 20px; color: white; transition: transform .3s,box-shadow .3s; }
        .finance-stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(102,126,234,.35); }
        .payment-progress { height: 8px; border-radius: 4px; background: #e2e8f0; }
        .payment-progress-bar { height: 100%; border-radius: 4px; transition: width .4s ease; }
        .scholarship-card { border-left: 4px solid #10b981; transition: transform .2s,box-shadow .2s; }
        .scholarship-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.1); }
        .payroll-table th { background: #1e293b; color: white; }

        /* =====================================================
           PRINT
           ===================================================== */
        @media print { .no-print{display:none!important} body{padding:0;margin:0} }

        /* =====================================================
           RESPONSIVE SEARCH
           ===================================================== */
        @media (max-width: 767.98px) { .desktop-search-only{ display:none !important; } }
        @media (min-width: 768px)    { .mobile-search-only { display:none !important; } }
    </style>

    <!-- Route-specific CSS -->
    @if (Route::is('dashboard'))               @include('layouts.pages-assets.css.users-list-css') @endif
    @if (Route::is('users.*'))                 @include('layouts.pages-assets.css.users-list-css') @endif
    @if (Route::is('student-id-cards.*'))      @include('layouts.pages-assets.css.users-list-css') @endif
    @if (Route::is('student.payments.*'))      @include('layouts.pages-assets.css.users-list-css') @endif
    @if (Route::is('profile.*'))               @include('layouts.pages-assets.css.users-list-css') @endif
    @if (Route::is('roles.*'))                 @include('layouts.pages-assets.css.roles-list-css') @endif
    @if (Route::is('permissions.*'))           @include('layouts.pages-assets.css.permission-list-css') @endif
    @if (Route::is('session.*'))               @include('layouts.pages-assets.css.session-list-css') @endif
    @if (Route::is('school-information.*'))    @include('layouts.pages-assets.css.schoolinformation-list-css') @endif
    @if (Route::is('admin.school-info.*'))     @include('layouts.pages-assets.css.schoolinformation-list-css') @endif
    @if (Route::is('term.*'))                  @include('layouts.pages-assets.css.term-list-css') @endif
    @if (Route::is('schoolhouse.*'))           @include('layouts.pages-assets.css.schoolhouse-list-css') @endif
    @if (Route::is('schoolarm.*'))             @include('layouts.pages-assets.css.arm-list-css') @endif
    @if (Route::is('classcategories.*'))       @include('layouts.pages-assets.css.classcategory-list-css') @endif
    @if (Route::is('schoolclass.*'))           @include('layouts.pages-assets.css.schoolclass-list-css') @endif
    @if (Route::is('classteacher.*'))          @include('layouts.pages-assets.css.classteacher-list-css') @endif
    @if (Route::is('subject.*'))               @include('layouts.pages-assets.css.subject-list-css') @endif
    @if (Route::is('subjects.*'))              @include('layouts.pages-assets.css.subject-list-css') @endif
    @if (Route::is('subjectteacher.*'))        @include('layouts.pages-assets.css.subjectteacher-list-css') @endif
    @if (Route::is('subjectclass.*'))          @include('layouts.pages-assets.css.subjectclass-list-css') @endif
    @if (Route::is('schoolbill.*'))            @include('layouts.pages-assets.css.schoolbill-list-css') @endif
    @if (Route::is('schoolbilltermsession.*')) @include('layouts.pages-assets.css.schoolbilltermsession-list-css') @endif
    @if (Route::is('student.*'))               @include('layouts.pages-assets.css.student-list-css') @endif
    @if (Route::is('studentbatchindex'))       @include('layouts.pages-assets.css.student-list-css') @endif
    @if (Route::is('myclass.*'))               @include('layouts.pages-assets.css.myclass-list-css') @endif
    @if (Route::is('mysubject.*'))             @include('layouts.pages-assets.css.mysubject-list-css') @endif
    @if (Route::is('viewstudent'))             @include('layouts.pages-assets.css.viewstudent-list-css') @endif
    @if (Route::is('studentreports.*'))        @include('layouts.pages-assets.css.studentreport-list-css') @endif
    @if (Route::is('studentmockreports.*'))    @include('layouts.pages-assets.css.studentreport-list-css') @endif
    @if (Route::is('broadsheet*'))             @include('layouts.pages-assets.css.broadsheet-list-css') @endif
    @if (Route::is('subjectoperation.*'))      @include('layouts.pages-assets.css.subjectoperation-list-css') @endif
    @if (Route::is('subjects.subjectinfo'))    @include('layouts.pages-assets.css.subjectinfo-list-css') @endif
    @if (Route::is('myresultroom.*'))          @include('layouts.pages-assets.css.myresultroom-list-css') @endif
    @if (Route::is('subjectscoresheet'))       @include('layouts.pages-assets.css.subjectscoresheet-list-css') @endif
    @if (Route::is('subassessment.*'))         @include('layouts.pages-assets.css.subjectscoresheet-list-css') @endif
    @if (Route::is('assessment.*'))            @include('layouts.pages-assets.css.subjectscoresheet-list-css') @endif
    @if (Route::is('assessments'))             @include('layouts.pages-assets.css.subjectscoresheet-list-css') @endif
    @if (Route::is('subjectscoresheet-mock.*'))@include('layouts.pages-assets.css.subjectscoresheet-mock-list-css') @endif
    @if (Route::is('studentresults*'))         @include('layouts.pages-assets.css.studentresults-list-css') @endif
    @if (Route::is('schoolpayment*'))          @include('layouts.pages-assets.css.schoolpayment-list-css') @endif
    @if (Route::is('analysis*'))               @include('layouts.pages-assets.css.analysis-list-css') @endif
    @if (Route::is('exams*'))                  @include('layouts.pages-assets.css.exams-list-css') @endif
    @if (Route::is('questions*'))              @include('layouts.pages-assets.css.questions-list-css') @endif
    @if (Route::is('cbt*'))                    @include('layouts.pages-assets.css.cbt-list-css') @endif
    @if (Route::is('classbroadsheet.*'))       @include('layouts.pages-assets.css.classbroadsheet-list-css') @endif
    @if (Route::is('principalscomment.*'))     @include('layouts.pages-assets.css.principalscomment-list-css') @endif
    @if (Route::is('myprincipalscomment.*'))   @include('layouts.pages-assets.css.myprincipalscomment-list-css') @endif
    @if (Route::is('compulsorysubjectclass.*'))@include('layouts.pages-assets.css.compulsorysubjectclass-list-css') @endif
    @if (Route::is('subjectvetting.*'))        @include('layouts.pages-assets.css.subjectvettings-list-css') @endif
    @if (Route::is('mocksubjectvetting.*'))    @include('layouts.pages-assets.css.mocksubjectvettings-list-css') @endif
    @if (Route::is('mysubjectvettings.*'))     @include('layouts.pages-assets.css.mysubjectvettings-list-css') @endif
    @if (Route::is('mymocksubjectvettings.*')) @include('layouts.pages-assets.css.mymocksubjectvettings-list-css') @endif
    @if (Route::is('timetable.*'))             @include('layouts.pages-assets.css.timetable-list-css') @endif
    @if (Route::is('rooms.*'))                 @include('layouts.pages-assets.css.rooms-list-css') @endif
    @if (Route::is('promotions.*'))            @include('layouts.pages-assets.css.promotions-list-css') @endif
    @if (Route::is('attendance.*'))            @include('layouts.pages-assets.css.attendance-list-css') @endif
    @if (Route::is('transcript.*'))            @include('layouts.pages-assets.css.attendance-list-css') @endif
    @if (Route::is('admin.score-entry.*'))     @include('layouts.pages-assets.css.adminscoreentry-list-css') @endif
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

        <!-- LOGO — always shown including mobile -->
        <div class="navbar-brand-box">
            @php
                use App\Models\SchoolInformation;
                $schoolInfo  = SchoolInformation::getActiveSchool();
                $schoolName  = $schoolInfo?->school_name ?? config('app.name','School System');
                $defaultLogo = asset('theme/layouts/assets/images/logo-dark.png');
                $defaultLogoLight = asset('theme/layouts/assets/images/logo-light.png');
            @endphp
            <a href="{{ url('/') }}" class="logo logo-dark">
                <span class="logo-sm"><img src="{{ $schoolInfo?->getLogoUrlAttribute() ?? $defaultLogo }}" alt="{{ $schoolName }}" style="height:38px;width:auto;border-radius:8px;object-fit:contain;padding:2px;background:rgb(39,38,38);"></span>
                <span class="logo-lg"><img src="{{ $schoolInfo?->getLogoUrlAttribute() ?? $defaultLogo }}" alt="{{ $schoolName }}" style="height:62px;width:auto;border-radius:10px;object-fit:contain;padding:2px;background:rgb(37,36,36);"></span>
            </a>
            <a href="{{ url('/') }}" class="logo logo-light">
                <span class="logo-sm"><img src="{{ $schoolInfo?->getLogoUrlAttribute() ?? $defaultLogoLight }}" alt="{{ $schoolName }}" style="height:38px;width:auto;border-radius:8px;object-fit:contain;padding:2px;background:rgb(40,39,39);"></span>
                <span class="logo-lg"><img src="{{ $schoolInfo?->getLogoUrlAttribute() ?? $defaultLogoLight }}" alt="{{ $schoolName }}" style="height:62px;width:auto;border-radius:10px;object-fit:contain;padding:2px;background:rgb(37,36,36);"></span>
            </a>
            <button type="button" class="btn btn-sm p-0 fs-3xl header-item float-end btn-vertical-sm-hover" id="vertical-hover">
                <i class="ri-record-circle-line"></i>
            </button>
        </div>

        <!-- NAV -->
        <div id="scrollbar">
            <div class="container-fluid">
                <div id="two-column-menu"></div>
                <ul class="navbar-nav" id="navbar-nav">

                    <li class="menu-title"><span data-key="t-menu">Menu</span></li>

                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a class="nav-link menu-link collapsed" href="#sidebarDashboards" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarDashboards">
                            <i class="ph-gauge"></i> <span data-key="t-dashboards">Dashboards</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarDashboards">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="{{ route('dashboard') }}" class="nav-link">Administration Analytics</a></li>
                                @can('finance dashboard')<li class="nav-item"><a href="dashboard-crm.html" class="nav-link">Finance Analytics</a></li>@endcan
                                @can('academics dashboard')<li class="nav-item"><a href="index.html" class="nav-link">Academics Analytics</a></li>@endcan
                            </ul>
                        </div>
                    </li>

                    <!-- USERS & PRIVILEGES -->
                    @if(auth()->user()->can('View user') || auth()->user()->can('View role') || auth()->user()->can('View user-account'))
                        <li class="menu-title"><i class="ri-more-fill"></i> <span>USERS & PRIVILEDGES</span></li>
                    @endif

                    @can('View user')
                    <li class="nav-item">
                        <a class="nav-link menu-link collapsed" href="#sidebarusers" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarusers">
                            <i class="ph-user-circle"></i> <span>User Managements</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarusers">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="{{ route('users.index') }}" class="nav-link">Users</a></li>
                            </ul>
                        </div>
                    </li>
                    @endcan

                    <!-- My Account -->
                    <li class="nav-item">
                        <a class="nav-link menu-link collapsed" href="#sidebaraccount" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebaraccount">
                            <i class="ph-address-book"></i> <span>My Account</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebaraccount">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="{{ route('users.overview', ['id' => Auth::id()]) }}" class="nav-link"><i class="ri-profile-line me-2"></i> My Profile</a></li>
                                <li class="nav-item"><a href="{{ route('profile.settings', ['id' => Auth::id()]) }}" class="nav-link"><i class="ri-settings-3-line me-2"></i> Account Settings</a></li>
                                @if(Auth::user()->isStaff())
                                    <li class="nav-item"><a href="{{ route('profile.settings', ['id' => Auth::id()]) }}#employmentInfo" class="nav-link ps-4"><i class="ri-building-line me-2"></i> Employment Details</a></li>
                                    <li class="nav-item"><a href="{{ route('profile.settings', ['id' => Auth::id()]) }}#qualifications" class="nav-link ps-4"><i class="ri-graduation-cap-line me-2"></i> Academic Qualifications</a></li>
                                @endif
                                @if(Auth::user()->isStudent())
                                    <li class="nav-item"><a href="{{ route('profile.settings', ['id' => Auth::id()]) }}#studentInfo" class="nav-link ps-4"><i class="ri-user-line me-2"></i> Student Details</a></li>
                                    <li class="nav-item"><a href="{{ route('profile.settings', ['id' => Auth::id()]) }}#parentInfo" class="nav-link ps-4"><i class="ri-parent-line me-2"></i> Parent Information</a></li>
                                @endif
                                <li class="nav-item"><a href="{{ route('profile.settings', ['id' => Auth::id()]) }}#security" class="nav-link ps-4"><i class="ri-lock-password-line me-2"></i> Change Password</a></li>
                            </ul>
                        </div>
                    </li>

                    @can('View role')
                    <li class="nav-item">
                        <a class="nav-link menu-link collapsed" href="#sidebarroles" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarroles">
                            <i class="ph-address-book"></i> <span>Roles And Permissions</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarroles">
                            <ul class="nav nav-sm flex-column">
                                @can('View role')<li class="nav-item"><a href="{{ route('roles.index') }}" class="nav-link">Roles</a></li>@endcan
                                @can('View permission')<li class="nav-item"><a href="{{ route('permissions.index') }}" class="nav-link">Permissions</a></li>@endcan
                            </ul>
                        </div>
                    </li>
                    @endcan

                    <!-- STUDENT & PARENTS -->
                    @if(auth()->user()->can('View student') || auth()->user()->can('Create student-bulk-upload') || auth()->user()->can('View parent') || auth()->user()->can('View id card'))
                        <li class="menu-title"><i class="ri-more-fill"></i> <span>STUDENT & PARENTS</span></li>
                    @endif

                    @if(auth()->user()->can('View student') || auth()->user()->can('Create student-bulk-upload'))
                    <li class="nav-item">
                        <a href="#sidebarStudentmanagement" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarStudentmanagement">
                            <i class="ph-storefront"></i> <span>Student Management</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarStudentmanagement">
                            <ul class="nav nav-sm flex-column">
                                @can('View student')<li class="nav-item"><a href="{{ route('student.index') }}" class="nav-link">All Students</a></li>@endcan
                                @can('Create student-bulk-upload')<li class="nav-item"><a href="{{ route('studentbatchindex') }}" class="nav-link">Batch Student Registration</a></li>@endcan
                                @can('View id card')<li class="nav-item"><a href="{{ route('student-id-cards.index') }}" class="nav-link">ID Card Generator</a></li>@endcan
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if(auth()->user()->can('View student assessments') || auth()->user()->can('View student payments'))
                        <li class="menu-title"><i class="ph-graduation-cap"></i> <span>STUDENT PORTAL</span></li>
                    @endif
                    @can('View student assessments')
                    <li class="nav-item">
                        <a href="#sidebarAssessments" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarAssessments">
                            <i class="ph-graduation-cap"></i> <span>Assessments</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarAssessments">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="{{ route('assessments') }}" class="nav-link">My Assessments</a></li>
                            </ul>
                        </div>
                    </li>
                    @endcan

                    <li class="nav-item">
                        <a href="#sidebarStudentPaymentPortal" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarStudentPaymentPortal">
                            <i class="ph-graduation-cap"></i> <span>Payments</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarStudentPaymentPortal">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="{{ route('student.payments') }}" class="nav-link">My Payments</a></li>
                            </ul>
                        </div>
                    </li>

                    @can('View parent')
                    <li class="nav-item">
                        <a href="#sidebarParent" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarParent">
                            <i class="ph-storefront"></i> <span>Parent Management</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarParent">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="{{ route('parent.index') }}" class="nav-link">All Parents</a></li>
                            </ul>
                        </div>
                    </li>
                    @endcan

                    <!-- SUBJECT REGISTRATION -->
                    @if(auth()->user()->can('View my-class') || auth()->user()->can('View my-subject'))
                        <li class="menu-title"><i class="ph-folder-open"></i> <span>SUBJECT REGISTRATION</span></li>
                        <li class="nav-item">
                            <a href="#sidebarsubjectoperaton" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarsubjectoperaton">
                                <i class="ph-folder-open"></i> <span>Subject Registration</span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarsubjectoperaton">
                                <ul class="nav nav-sm flex-column">
                                    @can('View my-class')<li class="nav-item"><a href="{{ route('subjectoperation.index') }}" class="nav-link">Student Subject Registration</a></li>@endcan
                                </ul>
                            </div>
                        </li>
                    @endif

                    <!-- EXAMS AND CBT -->
                    @if(auth()->user()->can('View exam') || auth()->user()->can('View cbt-exam'))
                        <li class="menu-title"><i class="ph-graduation-cap"></i> <span>EXAMS AND CBT</span></li>
                    @endif
                    @can('View exam')
                    <li class="nav-item">
                        <a href="#sidebarExams" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarExams">
                            <i class="ph-graduation-cap"></i> <span>Exams Management</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarExams">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="{{ route('exams.index') }}" class="nav-link">All Examinations</a></li>
                                @can('View question')<li class="nav-item"><a href="{{ route('questions.all') }}" class="nav-link">Questions Management</a></li>@endcan
                            </ul>
                        </div>
                    </li>
                    @endcan
                    @can('View cbt-exam')
                    <li class="nav-item">
                        <a href="#sidebarCBT" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarCBT">
                            <i class="ph-graduation-cap"></i> <span>CBT Management</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarCBT">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="{{ route('cbt.index') }}" class="nav-link">CBT Exercise</a></li>
                            </ul>
                        </div>
                    </li>
                    @endcan

                    <!-- TIMETABLE -->
                    @if(auth()->user()->can('View timetable') || auth()->user()->can('View my timetable'))
                    <li class="nav-item">
                        <a href="#sidebartimetable" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebartimetable">
                            <i class="ph-calendar"></i> <span>Timetable Management</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebartimetable">
                            <ul class="nav nav-sm flex-column">
                                @can('View timetable')<li class="nav-item"><a href="{{ route('timetable.index') }}" class="nav-link">Admin Timetable</a></li>@endcan
                                @can('View my timetable')<li class="nav-item"><a href="{{ route('timetable.teacher') }}" class="nav-link">My Timetable</a></li>@endcan
                                @can('View rooms')<li class="nav-item"><a href="{{ route('rooms.index') }}" class="nav-link">Room Management</a></li>@endcan
                                @can('View exam timetable')<li class="nav-item"><a href="{{ route('exam-timetable.index') }}" class="nav-link">Exam Timetable</a></li>@endcan
                                @can('View holidays')<li class="nav-item"><a href="{{ route('holidays.index') }}" class="nav-link">Holidays</a></li>@endcan
                            </ul>
                        </div>
                    </li>
                    @endif

                    <!-- CLASSES & RECORDS -->
                    @if(auth()->user()->can('View my-class') || auth()->user()->can('View my-subject') || auth()->user()->can('View my-subject-vettings') || auth()->user()->can('View my-mock-subject-vettings') || auth()->user()->can('View myresult-room') || auth()->user()->can('View student-report') || auth()->user()->can('View student-mock-report') || auth()->user()->can('View my-principals-comment'))
                        <li class="menu-title"><i class="ph-folder-open"></i> <span>CLASSES & RECORDS</span></li>
                    @endif

                    @if(auth()->user()->can('View my-class') || auth()->user()->can('View my-subject') || auth()->user()->can('View my-subject-vettings') || auth()->user()->can('View my-mock-subject-vettings') || auth()->user()->can('View my-principals-comment'))
                    <li class="nav-item">
                        <a href="#sidebarClasses" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarClasses">
                            <i class="ph-folder-open"></i> <span>Classes & Subjects</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarClasses">
                            <ul class="nav nav-sm flex-column">
                                @can('View my-class')<li class="nav-item"><a href="{{ route('myclass.index') }}" class="nav-link">My Class</a></li>@endcan
                                @can('View my-subject')<li class="nav-item"><a href="{{ route('mysubject.index') }}" class="nav-link">My Subject</a></li>@endcan
                                @can('View my-subject-vettings')<li class="nav-item"><a href="{{ route('mysubjectvettings.index') }}" class="nav-link">Subjects to Vet</a></li>@endcan
                                @can('View my-mock-subject-vettings')<li class="nav-item"><a href="{{ route('mymocksubjectvettings.index') }}" class="nav-link">Mock Subjects to Vet</a></li>@endcan
                                @can('View my-principals-comment')<li class="nav-item"><a href="{{ route('myprincipalscomment.index') }}" class="nav-link">Principal's Comment</a></li>@endcan
                            </ul>
                        </div>
                    </li>
                    @endif

                    <!-- ATTENDANCE -->
                    @if(auth()->user()->can('View attendance-register') || auth()->user()->can('View attendance-class-summary') || auth()->user()->can('View attendance-student-report'))
                    <li class="nav-item">
                        <a href="#sidebarAttendance" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarAttendance">
                            <i class="ph-calendar-check"></i> <span>Attendance</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarAttendance">
                            <ul class="nav nav-sm flex-column">
                                @can('View attendance-register')<li class="nav-item"><a href="{{ route('attendance.my-classes') }}" class="nav-link">Mark Attendance</a></li>@endcan
                            </ul>
                        </div>
                    </li>
                    @endif

                    <!-- RECORDS AND RESULTS -->
                    @if(auth()->user()->can('View myresult-room') || auth()->user()->can('View student-report') || auth()->user()->can('View student-mock-report') || auth()->user()->can('View admin-score-entry'))
                    <li class="nav-item">
                        <a href="#sidebarRecords" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarRecords">
                            <i class="ph-folder-open"></i> <span>Records and Results</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarRecords">
                            <ul class="nav nav-sm flex-column">
                                @can('View myresult-room')<li class="nav-item"><a href="{{ route('myresultroom.index') }}" class="nav-link">Terminal Records</a></li>@endcan
                                @can('View student-report')
                                    <li class="nav-item"><a href="{{ route('studentreports.index') }}" class="nav-link">Terminal Result Reports</a></li>
                                    <li class="nav-item"><a href="{{ route('broadsheet.index') }}" class="nav-link">Terminal Result Broadsheet</a></li>
                                @endcan
                                @can('View student-mock-report')<li class="nav-item"><a href="{{ route('studentmockreports.index') }}" class="nav-link">Mock Result Reports</a></li>@endcan
                                @can('View admin-score-entry')<li class="nav-item"><a href="{{ route('admin.score-entry.index') }}" class="nav-link">Admin Score Entry</a></li>@endcan
                            </ul>
                        </div>
                    </li>
                    @endif

                    <!-- TRANSCRIPTS -->
                    @if(auth()->user()->can('View student-transcript') || auth()->user()->can('Preview student-transcript') || auth()->user()->can('Download student-transcript'))
                        <li class="menu-title"><i class="ph-folder-open"></i> <span>TRANSCRIPTS</span></li>
                        <li class="nav-item">
                            <a href="#sidebarTranscript" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarTranscript">
                                <i class="ri-file-text-line"></i> <span>Transcript</span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarTranscript">
                                <ul class="nav nav-sm flex-column">
                                    @can('View student-transcript')<li class="nav-item"><a href="{{ route('transcript.index') }}" class="nav-link">Generate Transcript</a></li>@endcan
                                </ul>
                            </div>
                        </li>
                    @endif

                    <!-- PROMOTION -->
                    @if(auth()->user()->can('View myresult-room') || auth()->user()->can('View student-report') || auth()->user()->can('View student-mock-report'))
                        <li class="menu-title"><i class="ri-more-fill"></i> <span>PROMOTION MANAGEMENT</span></li>
                        <li class="nav-item">
                            <a href="#sidebarPromotions" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarPromotions">
                                <i class="ph-folder-open"></i> <span>Promotion Management</span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarPromotions">
                                <ul class="nav nav-sm flex-column">
                                    @can('View myresult-room')<li class="nav-item"><a href="{{ route('promotions.index') }}" class="nav-link">Student Promotion</a></li>@endcan
                                </ul>
                            </div>
                        </li>
                    @endif

                    <!-- BURSARY & FINANCE -->
                    @if(auth()->user()->can('View school-payment') || auth()->user()->can('View analysis') || auth()->user()->can('View scholarship') || auth()->user()->can('View discount') || auth()->user()->can('View sibling groups') || auth()->user()->can('View financial reports') || auth()->user()->can('View payroll') || auth()->user()->can('View staff payments'))
                        <li class="menu-title"><i class="ri-more-fill"></i> <span>BURSARY & FINANCE</span></li>
                    @endif

                    @can('View school-payment')
                    <li class="nav-item">
                        <a href="#sidebarStudentpayments" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarStudentpayments">
                            <i class="ph-storefront"></i> <span>Student Payments</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarStudentpayments">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="{{ route('schoolpayment.index') }}" class="nav-link">Student Bill</a></li>
                                <li class="nav-item"><a href="{{ route('payment.index') }}" class="nav-link">Payment Portal</a></li>
                            </ul>
                        </div>
                    </li>
                    @endcan

                    @can('View analysis')
                    <li class="nav-item">
                        <a href="#sidebarAnalysis" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarAnalysis">
                            <i class="ph-storefront"></i> <span>Payment Analysis</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarAnalysis">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="{{ route('analysis.index') }}" class="nav-link">School Payment Analysis</a></li>
                            </ul>
                        </div>
                    </li>
                    @endcan

                    @can('View scholarship')
                    <li class="nav-item">
                        <a href="#sidebarScholarship" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-controls="sidebarScholarship">
                            <i class="ph-graduation-cap"></i> <span>Scholarship Management</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarScholarship">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="{{ route('admin.scholarship.index') }}" class="nav-link">All Scholarships</a></li>
                                @can('Create scholarship')<li class="nav-item"><a href="{{ route('admin.scholarship.create') }}" class="nav-link">Create Scholarship</a></li>@endcan
                                <li class="nav-item"><a href="{{ route('admin.scholarship.assignments') }}" class="nav-link">Scholarship Assignments</a></li>
                                <li class="nav-item"><a href="{{ route('admin.scholarship.applications') }}" class="nav-link">Applications</a></li>
                            </ul>
                        </div>
                    </li>
                    @endcan

                    @can('View discount')
                    <li class="nav-item">
                        <a href="#sidebarDiscount" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarDiscount">
                            <i class="ph-tag"></i> <span>Discount Management</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarDiscount">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="{{ route('admin.discount.index') }}" class="nav-link">All Discounts</a></li>
                                @can('Create discount')<li class="nav-item"><a href="{{ route('admin.discount.create') }}" class="nav-link">Create Discount</a></li>@endcan
                                <li class="nav-item"><a href="{{ route('admin.discount.assignments') }}" class="nav-link">Discount Assignments</a></li>
                            </ul>
                        </div>
                    </li>
                    @endcan

                    @can('View sibling groups')
                    <li class="nav-item">
                        <a href="#sidebarSibling" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarSibling">
                            <i class="ph-users"></i> <span>Sibling Groups</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarSibling">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="{{ route('sibling.index') }}" class="nav-link">All Family Groups</a></li>
                                <li class="nav-item"><a href="{{ route('sibling.create') }}" class="nav-link">Create Family Group</a></li>
                            </ul>
                        </div>
                    </li>
                    @endcan

                    @can('Manage payment gateways')
                    <li class="nav-item">
                        <a href="{{ route('admin.payment-gateways.index') }}" class="nav-link">
                            <i class="ph-credit-card"></i> <span>Payment Gateways</span>
                        </a>
                    </li>
                    @endcan

                    @can('View financial reports')
                    <li class="nav-item">
                        <a href="#sidebarAccounting" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarAccounting">
                            <i class="ph-chart-line"></i> <span>Accounting & Reports</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarAccounting">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="{{ route('reports.financial.balance-sheet') }}" class="nav-link">Balance Sheet</a></li>
                                <li class="nav-item"><a href="{{ route('reports.financial.income-statement') }}" class="nav-link">Income Statement</a></li>
                                <li class="nav-item"><a href="{{ route('reports.financial.trial-balance') }}" class="nav-link">Trial Balance</a></li>
                                <li class="nav-item"><a href="{{ route('reports.financial.cash-flow') }}" class="nav-link">Cash Flow</a></li>
                                <li class="nav-item"><a href="{{ route('reports.financial.debtors') }}" class="nav-link">Student Debtors List</a></li>
                                <li class="nav-item"><a href="{{ route('reports.financial.collection-summary') }}" class="nav-link">Collection Summary</a></li>
                                <li class="nav-item"><a href="{{ route('reports.analysis.index') }}" class="nav-link">Class Analysis</a></li>
                                <li class="nav-item"><a href="{{ route('reports.analysis.school-wide') }}" class="nav-link">School-Wide Analysis</a></li>
                            </ul>
                        </div>
                    </li>
                    @endcan

                    @can('View payroll')
                    <li class="nav-item">
                        <a href="#sidebarPayroll" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarPayroll">
                            <i class="ph-money"></i> <span>Payroll Management</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarPayroll">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="{{ route('payroll.periods') }}" class="nav-link">Payroll Periods</a></li>
                                <li class="nav-item"><a href="{{ route('payroll.summary') }}" class="nav-link">Payroll Summary</a></li>
                                <li class="nav-item"><a href="{{ route('payroll.statutory') }}" class="nav-link">Statutory Report</a></li>
                                <li class="nav-item"><a href="{{ route('payroll.salary-structures') }}" class="nav-link">Salary Structures</a></li>
                            </ul>
                        </div>
                    </li>
                    @endcan

                    @can('View staff payments')
                    <li class="nav-item">
                        <a href="#sidebarStaffPayments" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarStaffPayments">
                            <i class="ph-wallet"></i> <span>Staff Payments</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarStaffPayments">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="{{ route('staff.payments.index') }}" class="nav-link">All Payments</a></li>
                                <li class="nav-item"><a href="{{ route('staff.payments.dashboard') }}" class="nav-link">My Payments</a></li>
                            </ul>
                        </div>
                    </li>
                    @endcan

                    <!-- SCHOOL BASIC SETTINGS -->
                    @if(auth()->user()->can('View schoolinformation') || auth()->user()->can('View session') || auth()->user()->can('View term') || auth()->user()->can('View schoolhouse') || auth()->user()->can('View school-arm') || auth()->user()->can('View class-category') || auth()->user()->can('View school-class') || auth()->user()->can('View class-teacher') || auth()->user()->can('View subjects') || auth()->user()->can('View subject-teacher') || auth()->user()->can('View subject-class') || auth()->user()->can('View compulsory-subject') || auth()->user()->can('View principals-comment') || auth()->user()->can('View school-bills') || auth()->user()->can('View school-bill-for-term-session'))
                        <li class="menu-title"><i class="ri-more-fill"></i> <span>SCHOOL BASIC SETTINGS</span></li>
                    @endif

                    @can('View schoolinformation')
                    <li class="nav-item">
                        <a href="#sidebarSchoolInfo" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarSchoolInfo">
                            <i class="ph-file-text"></i> <span>School Information</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarSchoolInfo">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="{{ route('school-information.index') }}" class="nav-link">School Information</a></li>
                            </ul>
                        </div>
                    </li>
                    @endcan

                    @if(auth()->user()->can('View session') || auth()->user()->can('View term') || auth()->user()->can('View schoolhouse'))
                    <li class="nav-item">
                        <a href="#sidebarSession" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarSession">
                            <i class="ph-file-text"></i> <span>Session Term & House</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarSession">
                            <ul class="nav nav-sm flex-column">
                                @can('View session')<li class="nav-item"><a href="{{ route('session.index') }}" class="nav-link">School Session</a></li>@endcan
                                @can('View term')<li class="nav-item"><a href="{{ route('term.index') }}" class="nav-link">School Term</a></li>@endcan
                                @can('View schoolhouse')<li class="nav-item"><a href="{{ route('schoolhouse.index') }}" class="nav-link">School House</a></li>@endcan
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if(auth()->user()->can('View school-arm') || auth()->user()->can('View class-category') || auth()->user()->can('View school-class') || auth()->user()->can('View class-teacher'))
                    <li class="nav-item">
                        <a href="#sidebarClassessettings" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarClassessettings">
                            <i class="ph-file-text"></i> <span>Classes</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarClassessettings">
                            <ul class="nav nav-sm flex-column">
                                @can('View school-arm')<li class="nav-item"><a href="{{ route('schoolarm.index') }}" class="nav-link">Class Arm</a></li>@endcan
                                @can('View class-category')<li class="nav-item"><a href="{{ route('classcategories.index') }}" class="nav-link">Class Category</a></li>@endcan
                                @can('View school-class')<li class="nav-item"><a href="{{ route('schoolclass.index') }}" class="nav-link">Class Name</a></li>@endcan
                                @can('View class-teacher')<li class="nav-item"><a href="{{ route('classteacher.index') }}" class="nav-link">Class Teacher</a></li>@endcan
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if(auth()->user()->can('View subjects') || auth()->user()->can('View subject-teacher') || auth()->user()->can('View subject-class') || auth()->user()->can('View compulsory-subject'))
                    <li class="nav-item">
                        <a href="#sidebarSub" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarSub">
                            <i class="ph-file-text"></i> <span>Subject</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarSub">
                            <ul class="nav nav-sm flex-column">
                                @can('View subjects')<li class="nav-item"><a href="{{ route('subject.index') }}" class="nav-link">Subject</a></li>@endcan
                                @can('View subject-teacher')<li class="nav-item"><a href="{{ route('subjectteacher.index') }}" class="nav-link">Assign Subject Teacher</a></li>@endcan
                                @can('View subject-class')<li class="nav-item"><a href="{{ route('subjectclass.index') }}" class="nav-link">Assign Class Subject</a></li>@endcan
                                @can('View compulsory-subject')<li class="nav-item"><a href="{{ route('compulsorysubjectclass.index') }}" class="nav-link">Assign Compulsory Subject to classes</a></li>@endcan
                            </ul>
                        </div>
                    </li>
                    @endif

                    @can('View principals-comment')
                    <li class="nav-item">
                        <a href="#sidebarPrincipal" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarPrincipal">
                            <i class="ph-file-text"></i> <span>Principal's Comments</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarPrincipal">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="{{ route('principalscomment.index') }}" class="nav-link">Assign Staff</a></li>
                            </ul>
                        </div>
                    </li>
                    @endcan

                    @can('View subjects')
                    <li class="nav-item">
                        <a href="#sidebarSubjectvetting" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarSubjectvetting">
                            <i class="ph-file-text"></i> <span>Terminal Subject Vettings</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarSubjectvetting">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="{{ route('subjectvetting.index') }}" class="nav-link">Assign Subjects to Staff</a></li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a href="#mocksidebarSubjectvetting" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="mocksidebarSubjectvetting">
                            <i class="ph-file-text"></i> <span>Mock Subject Vettings</span>
                        </a>
                        <div class="collapse menu-dropdown" id="mocksidebarSubjectvetting">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="{{ route('mocksubjectvetting.index') }}" class="nav-link">Assign Subjects to Staff</a></li>
                            </ul>
                        </div>
                    </li>
                    @endcan

                    @if(auth()->user()->can('View school-bills') || auth()->user()->can('View school-bill-for-term-session'))
                    <li class="nav-item">
                        <a href="#sidebarBills" class="nav-link menu-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarBills">
                            <i class="ph-file-text"></i> <span>School Bills</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarBills">
                            <ul class="nav nav-sm flex-column">
                                @can('View school-bills')<li class="nav-item"><a href="{{ route('schoolbill.index') }}" class="nav-link">Bills</a></li>@endcan
                                @can('View school-bill-for-term-session')<li class="nav-item"><a href="{{ route('schoolbilltermsession.index') }}" class="nav-link">Apply Bills</a></li>@endcan
                            </ul>
                        </div>
                    </li>
                    @endif

                </ul>
            </div>
        </div><!-- /scrollbar -->

        <!-- ===== SIDEBAR FOOTER — compact ===== -->
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
                        if (\Illuminate\Support\Facades\Storage::disk('public')->exists('student_avatars/'.$bn))
                            $sidebarSrc = asset('storage/student_avatars/'.$bn);
                    }
                } else {
                    if ($sidebarUser->avatar) {
                        $bn = basename($sidebarUser->avatar);
                        if (\Illuminate\Support\Facades\Storage::disk('public')->exists('staff_avatars/'.$bn))
                            $sidebarSrc = asset('storage/staff_avatars/'.$bn);
                    }
                }
                $sidebarInitials = collect(explode(' ', $sidebarUser->name))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->implode('');
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
    </div><!-- /app-menu -->

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

                    <!-- Desktop Search -->
                    <div class="d-none d-md-flex align-items-center desktop-search-only" style="position:relative;">
                        <button type="button" id="spotlight-trigger"
                                style="display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:10px;padding:7px 14px;cursor:pointer;transition:all .22s;min-width:220px;">
                            <i class="mdi mdi-magnify" style="font-size:16px;opacity:.6;"></i>
                            <span style="font-size:13px;opacity:.55;flex:1;text-align:left;">Search everything…</span>
                            <div style="display:flex;gap:4px;">
                                <kbd style="font-size:10px;padding:2px 6px;border-radius:4px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);">⌘</kbd>
                                <kbd style="font-size:10px;padding:2px 6px;border-radius:4px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);">K</kbd>
                            </div>
                        </button>
                        <div class="search-tooltip">Press <kbd style="background:rgba(255,255,255,.2);padding:2px 5px;border-radius:4px;margin:0 2px;">⌘K</kbd> or <kbd style="background:rgba(255,255,255,.2);padding:2px 5px;border-radius:4px;margin:0 2px;">Ctrl+K</kbd> to search</div>
                    </div>

                    <!-- Mobile Search -->
                    <div class="d-flex d-md-none align-items-center ms-2 mobile-search-only">
                        <button type="button" id="spotlight-trigger-mobile"
                                style="display:flex;align-items:center;gap:6px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:8px;padding:6px 10px;cursor:pointer;transition:all .2s;">
                            <i class="mdi mdi-magnify" style="font-size:18px;opacity:.7;color:#fff;"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-1">

                    <!-- Theme Toggle -->
                    <div class="position-relative" id="theme-toggle-wrapper">
                        <button type="button" id="theme-toggle-btn"
                                class="btn btn-icon btn-topbar btn-ghost-dark rounded-circle"
                                style="width:38px;height:38px;transition:transform var(--transition-bounce);"
                                onmouseover="this.style.transform='rotate(20deg) scale(1.1)'"
                                onmouseout="this.style.transform=''"
                                title="Switch theme">
                            <i id="theme-icon" class="bi bi-sun align-middle fs-3xl"></i>
                        </button>
                        <div id="theme-dropdown"
                             style="display:none;position:absolute;top:calc(100% + 8px);right:0;min-width:165px;
                                    background:var(--vz-dropdown-bg,#fff);border:1px solid var(--vz-border-color,#e9ebec);
                                    border-radius:10px;box-shadow:0 8px 28px rgba(0,0,0,.14);z-index:9999;overflow:hidden;padding:5px;">
                            <a href="javascript:void(0)" class="theme-mode-item d-flex align-items-center gap-2 px-3 py-2 rounded-2 text-decoration-none" data-mode="light" style="font-size:13px;color:inherit;transition:background .15s;">
                                <i class="bi bi-sun"></i> <span>Light</span>
                            </a>
                            <a href="javascript:void(0)" class="theme-mode-item d-flex align-items-center gap-2 px-3 py-2 rounded-2 text-decoration-none" data-mode="dark" style="font-size:13px;color:inherit;transition:background .15s;">
                                <i class="bi bi-moon"></i> <span>Dark</span>
                            </a>
                            <a href="javascript:void(0)" class="theme-mode-item d-flex align-items-center gap-2 px-3 py-2 rounded-2 text-decoration-none" data-mode="auto" style="font-size:13px;color:inherit;transition:background .15s;">
                                <i class="bi bi-moon-stars"></i> <span>Auto</span>
                            </a>
                        </div>
                    </div>

                    <!-- User Dropdown -->
                    @php
                        use App\Models\User as UserModel;
                        use App\Models\Student;
                        use Illuminate\Support\Facades\Storage;
                        use Illuminate\Support\Facades\Auth;
                        $userdata  = Auth::user();
                        $isStudent = $userdata->hasRole('student');
                        $fullName  = $userdata->name ?? 'User';
                        $initials  = collect(explode(' ',$fullName))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->implode('');
                        $srcPath   = null;
                        if ($isStudent) {
                            $student = Student::where('id', $userdata->student_id)->first();
                            if ($student?->picture) {
                                $bn = basename($student->picture);
                                if (Storage::disk('public')->exists('student_avatars/'.$bn))
                                    $srcPath = asset('storage/student_avatars/'.$bn);
                            }
                        } else {
                            if ($userdata->avatar) {
                                $bn = basename($userdata->avatar);
                                if (Storage::disk('public')->exists('staff_avatars/'.$bn))
                                    $srcPath = asset('storage/staff_avatars/'.$bn);
                            }
                        }
                        $userRoles = $userdata->roles->pluck('name');
                    @endphp

                    <div class="dropdown position-relative ms-sm-3 header-item topbar-user" id="user-dropdown-wrapper">
                        <button type="button" id="user-menu-btn" class="btn shadow-none p-0"
                                style="background:transparent;border:none;transition:transform var(--transition-bounce);"
                                onmouseover="this.style.transform='scale(1.04)'"
                                onmouseout="this.style.transform=''">
                            <span class="d-flex align-items-center gap-2">
                                <span style="display:inline-block;width:40px;height:40px;flex-shrink:0;position:relative;">
                                    @if($srcPath)
                                        <img id="topbar-avatar-img" src="{{ $srcPath }}" alt="{{ $fullName }}"
                                             style="width:40px;height:40px;border-radius:10px;object-fit:cover;object-position:center top;display:block;border:2px solid rgba(255,255,255,.25);transition:box-shadow .2s;"
                                             onerror="this.style.display='none';document.getElementById('topbar-avatar-fallback').style.display='flex';">
                                        <span id="topbar-avatar-fallback"
                                              style="display:none;width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#405189,#4f8ef7);color:#fff;font-size:13px;font-weight:700;align-items:center;justify-content:center;position:absolute;top:0;left:0;">
                                            {{ $initials }}
                                        </span>
                                    @else
                                        <span style="display:flex;width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#405189,#4f8ef7);color:#fff;font-size:13px;font-weight:700;align-items:center;justify-content:center;">
                                            {{ $initials }}
                                        </span>
                                    @endif
                                </span>
                                <span class="d-none d-xl-flex flex-column align-items-start ms-1" style="line-height:1.2;">
                                    <span class="fw-medium" style="font-size:13px;color:inherit;white-space:nowrap;max-width:130px;overflow:hidden;text-overflow:ellipsis;">{{ $userdata->name }}</span>
                                </span>
                            </span>
                        </button>

                        <div id="user-dropdown" class="dropdown-menu dropdown-menu-end"
                             style="display:none;position:absolute;top:calc(100% + 10px);right:0;min-width:220px;
                                    background:var(--vz-dropdown-bg,#fff);border:1px solid var(--vz-border-color,#e9ebec);
                                    border-radius:14px;box-shadow:0 12px 40px rgba(0,0,0,.16);z-index:9999;overflow:hidden;">
                            <!-- Gradient header -->
                            <div style="background:linear-gradient(135deg,#405189 0%,#4f8ef7 100%);padding:14px 16px;display:flex;align-items:center;gap:10px;">
                                @if($srcPath)
                                    <img src="{{ $srcPath }}" alt="{{ $fullName }}"
                                         style="width:36px;height:36px;border-radius:8px;object-fit:cover;border:2px solid rgba(255,255,255,.35);flex-shrink:0;">
                                @else
                                    <span style="display:flex;width:36px;height:36px;border-radius:8px;background:rgba(255,255,255,.2);color:#fff;font-size:13px;font-weight:700;align-items:center;justify-content:center;flex-shrink:0;">{{ $initials }}</span>
                                @endif
                                <div style="min-width:0;">
                                    <div style="font-size:13px;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:140px;">{{ $userdata->name }}</div>
                                    <div style="font-size:11px;color:rgba(255,255,255,.7);">
                                        @foreach($userRoles->take(2) as $r)
                                            <span style="background:rgba(255,255,255,.18);padding:1px 6px;border-radius:10px;margin-right:3px;font-size:10px;">{{ $r }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div style="padding:6px;">
                                @if(!$isStudent)
                                <a class="dropdown-item d-flex align-items-center gap-2 rounded-2" href="{{ route('users.overview', $userdata->id) }}"
                                   style="font-size:13px;padding:9px 12px;transition:background .15s;">
                                    <i class="mdi mdi-account-circle-outline text-muted"></i> My Profile
                                </a>
                                @endif
                                <a class="dropdown-item d-flex align-items-center gap-2 rounded-2" href="{{ route('profile.settings', ['id'=>$userdata->id]) }}"
                                   style="font-size:13px;padding:9px 12px;transition:background .15s;">
                                    <i class="mdi mdi-cog-outline text-muted"></i> Account Settings
                                </a>
                                <div style="height:1px;background:var(--vz-border-color,#e9ebec);margin:4px 0;"></div>
                                <form method="POST" action="{{ route('logout') }}" id="topbar-logout-form">@csrf
                                    <a class="dropdown-item d-flex align-items-center gap-2 rounded-2 text-danger" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();document.getElementById('topbar-logout-form').submit();"
                                       style="font-size:13px;padding:9px 12px;transition:background .15s;">
                                        <i class="mdi mdi-logout"></i> Logout
                                    </a>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </header>

    <!-- Image Modal -->
    <div class="modal fade" id="imageViewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header"><h5 class="modal-title">Profile Photo</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body text-center p-4"><img id="enlargedImage" src="" alt="Profile" class="img-fluid rounded-3" style="max-height:400px;"></div>
            </div>
        </div>
    </div>

    <!-- ========== SPOTLIGHT ========== -->
    <div id="spotlight-overlay"
         style="display:none;position:fixed;inset:0;z-index:10000;align-items:flex-start;justify-content:center;padding-top:6vh;">
        <div id="spotlight-box"
             style="width:100%;max-width:840px;margin:0 20px;background:rgba(18,20,26,.97);border:1px solid rgba(255,255,255,.1);border-radius:24px;box-shadow:0 40px 100px rgba(0,0,0,.7);overflow:hidden;">
            <div style="display:flex;align-items:center;gap:14px;padding:18px 22px;border-bottom:1px solid rgba(255,255,255,.07);">
                <i class="mdi mdi-magnify" style="font-size:24px;color:#4f8ef7;flex-shrink:0;"></i>
                <input id="spotlight-input" type="text" placeholder="Search pages, students, staff, classes…" autocomplete="off"
                       style="flex:1;background:transparent;border:none;outline:none;font-size:17px;color:#fff;caret-color:#4f8ef7;padding:6px 0;">
                <div style="display:flex;gap:8px;">
                    <button id="spotlight-clear-history" style="display:none;background:rgba(255,255,255,.08);border:none;border-radius:8px;padding:5px 11px;color:rgba(255,255,255,.55);font-size:11px;font-weight:500;cursor:pointer;transition:background .2s;">Clear</button>
                    <kbd id="spotlight-esc" style="font-size:11px;padding:3px 9px;border-radius:7px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.14);color:rgba(255,255,255,.55);cursor:pointer;">ESC</kbd>
                </div>
            </div>
            <div id="spotlight-results" style="max-height:480px;overflow-y:auto;padding:8px 0;">
                <div id="spotlight-history-section" style="display:none;">
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 22px 6px;">
                        <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.35);">Recent</span>
                        <button id="spotlight-clear-history-btn" style="background:transparent;border:none;color:rgba(255,255,255,.35);font-size:11px;cursor:pointer;transition:color .2s;">Clear All</button>
                    </div>
                    <div id="spotlight-history-list"></div>
                    <div style="height:1px;background:rgba(255,255,255,.05);margin:8px 18px;"></div>
                </div>
                <div id="spotlight-empty" style="padding:44px 22px;text-align:center;color:rgba(255,255,255,.32);">
                    <i class="mdi mdi-lightning-bolt" style="font-size:44px;display:block;margin-bottom:14px;opacity:.38;"></i>
                    <span style="font-size:14px;">Start typing to search…</span>
                    <div style="margin-top:12px;font-size:11px;opacity:.38;">Try: Students, Classes, Payments, Reports</div>
                </div>
                <ul id="spotlight-list" style="list-style:none;margin:0;padding:0;display:none;"></ul>
                <div id="spotlight-loading" style="display:none;padding:44px;text-align:center;">
                    <div style="display:inline-block;width:28px;height:28px;border:2px solid rgba(255,255,255,.14);border-top-color:#4f8ef7;border-radius:50%;animation:loadingSpin .65s linear infinite;"></div>
                    <div style="margin-top:14px;font-size:12px;color:rgba(255,255,255,.4);">Searching<span class="typing-dot">.</span><span class="typing-dot">.</span><span class="typing-dot">.</span></div>
                </div>
            </div>
            <div style="padding:11px 22px;border-top:1px solid rgba(255,255,255,.06);display:flex;gap:20px;font-size:11px;color:rgba(255,255,255,.3);flex-wrap:wrap;">
                <span><kbd style="background:rgba(255,255,255,.1);border-radius:4px;padding:2px 5px;">⌘K</kbd> open</span>
                <span><kbd style="background:rgba(255,255,255,.1);border-radius:4px;padding:2px 5px;">↑↓</kbd> navigate</span>
                <span><kbd style="background:rgba(255,255,255,.1);border-radius:4px;padding:2px 5px;">↵</kbd> open</span>
                <span><kbd style="background:rgba(255,255,255,.1);border-radius:4px;padding:2px 5px;">ESC</kbd> close</span>
            </div>
        </div>
    </div>

    @yield('content')

    <footer class="footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <script>document.write(new Date().getFullYear())</script>
                    @php $school = App\Models\SchoolInformation::where('is_active', true)->first(); @endphp
                    &copy; {{ $school->school_name ?? 'Vite-ESchool' }}
                </div>
                <div class="col-sm-6"><div class="text-sm-end d-none d-sm-block">Created by Qudroid Systems</div></div>
            </div>
        </div>
    </footer>
</div><!-- /layout-wrapper -->

<button class="btn btn-dark btn-icon" id="back-to-top" title="Back to top"><i class="bi bi-caret-up fs-3xl"></i></button>
<div id="preloader"><div id="status"><div class="spinner-border text-primary avatar-sm" role="status"><span class="visually-hidden">Loading...</span></div></div></div>

<!-- Customizer trigger -->
<div class="customizer-setting d-none d-md-block">
    <div class="btn btn-info p-2 text-uppercase rounded-end-0 shadow-lg"
         data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas">
        <i class="bi bi-gear mb-1"></i> Customizer
    </div>
</div>

<!-- Theme Offcanvas -->
<div class="offcanvas offcanvas-end border-0" tabindex="-1" id="theme-settings-offcanvas">
    <div class="d-flex align-items-center bg-primary bg-gradient p-3 offcanvas-header">
        <div class="me-2"><h5 class="mb-1 text-white">Theme Customizer</h5><p class="text-white text-opacity-75 mb-0">Customize your experience</p></div>
        <button type="button" class="btn-close btn-close-white ms-auto" id="customizerclose-btn" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0"><div data-simplebar class="h-100"><div class="p-4">
        <h6 class="fs-md mb-1">Color Scheme</h6>
        <div class="row g-3">
            <div class="col-6">
                <div class="form-check card-radio">
                    <input class="form-check-input" type="radio" name="data-bs-theme" id="layout-mode-light" value="light">
                    <label class="form-check-label p-0 bg-transparent" for="layout-mode-light">
                        <img src="{{ asset('theme/layouts/assets/images/custom-theme/light-mode.png') }}" alt="" class="img-fluid">
                    </label>
                </div>
                <h5 class="fs-sm text-center fw-medium mt-2">Light</h5>
            </div>
            <div class="col-6">
                <div class="form-check card-radio dark">
                    <input class="form-check-input" type="radio" name="data-bs-theme" id="layout-mode-dark" value="dark">
                    <label class="form-check-label p-0 bg-transparent" for="layout-mode-dark">
                        <img src="{{ asset('theme/layouts/assets/images/custom-theme/dark-mode.png') }}" alt="" class="img-fluid">
                    </label>
                </div>
                <h5 class="fs-sm text-center fw-medium mt-2">Dark</h5>
            </div>
        </div>
        <div id="sidebar-color" class="mt-4">
            <h6 class="fs-md mb-1">Sidebar Color</h6>
            <div class="row">
                <div class="col-4"><div class="form-check sidebar-setting card-radio"><input class="form-check-input" type="radio" name="data-sidebar" id="sidebar-color-light" value="light"><label class="form-check-label p-0 avatar-md w-100" for="sidebar-color-light"><span class="d-flex gap-1 h-100"><span class="flex-shrink-0"><span class="bg-white border-end d-flex h-100 flex-column gap-1 p-1"><span class="d-block p-1 px-2 bg-primary-subtle rounded mb-2"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span></span></span><span class="flex-grow-1"><span class="d-flex h-100 flex-column"><span class="bg-light d-block p-1"></span></span></span></span></label></div><h5 class="fs-sm text-center fw-medium mt-2">Light</h5></div>
                <div class="col-4"><div class="form-check sidebar-setting card-radio"><input class="form-check-input" type="radio" name="data-sidebar" id="sidebar-color-dark" value="dark"><label class="form-check-label p-0 avatar-md w-100" for="sidebar-color-dark"><span class="d-flex gap-1 h-100"><span class="flex-shrink-0"><span class="bg-primary d-flex h-100 flex-column gap-1 p-1"><span class="d-block p-1 px-2 bg-soft-light rounded mb-2"></span><span class="d-block p-1 px-2 pb-0 bg-soft-light"></span></span></span><span class="flex-grow-1"><span class="d-flex h-100 flex-column"><span class="bg-light d-block p-1"></span></span></span></span></label></div><h5 class="fs-sm text-center fw-medium mt-2">Dark</h5></div>
            </div>
        </div>
        <div id="sidebar-size" class="mt-4">
            <h6 class="fw-semibold fs-base">Sidebar Size</h6>
            <div class="row">
                <div class="col-4"><div class="form-check sidebar-setting card-radio"><input class="form-check-input" type="radio" name="data-sidebar-size" id="sidebar-size-default" value="lg"><label class="form-check-label p-0 avatar-md w-100" for="sidebar-size-default"></label></div><h5 class="fs-sm text-center fw-medium mt-2">Default</h5></div>
                <div class="col-4"><div class="form-check sidebar-setting card-radio"><input class="form-check-input" type="radio" name="data-sidebar-size" id="sidebar-size-small-hover" value="sm-hover"><label class="form-check-label p-0 avatar-md w-100" for="sidebar-size-small-hover"></label></div><h5 class="fs-sm text-center fw-medium mt-2">Hover</h5></div>
            </div>
        </div>
        <div id="sidebar-view" class="mt-4">
            <h6 class="fw-semibold fs-base">Layout</h6>
            <div class="row">
                <div class="col-4"><div class="form-check sidebar-setting card-radio"><input class="form-check-input" type="radio" name="data-layout" id="customizer-layout01" value="vertical"><label class="form-check-label p-0 avatar-md w-100" for="customizer-layout01"></label></div><h5 class="fs-sm text-center fw-medium mt-2">Vertical</h5></div>
                <div class="col-4"><div class="form-check sidebar-setting card-radio"><input class="form-check-input" type="radio" name="data-layout" id="customizer-layout03" value="twocolumn"><label class="form-check-label p-0 avatar-md w-100" for="customizer-layout03"></label></div><h5 class="fs-sm text-center fw-medium mt-2">Two Column</h5></div>
            </div>
        </div>
        <div id="layout-width" class="mt-4">
            <h6 class="fw-semibold fs-base">Layout Width</h6>
            <div class="row"><div class="col-4"><div class="form-check sidebar-setting card-radio"><input class="form-check-input" type="radio" name="data-layout-width" id="layout-width-fluid" value="fluid"><label class="form-check-label p-0 avatar-md w-100" for="layout-width-fluid"></label></div><h5 class="fs-sm text-center fw-medium mt-2">Fluid</h5></div></div>
        </div>
        <div id="layout-position" class="mt-4">
            <h6 class="fw-semibold fs-base">Layout Position</h6>
            <div class="row"><div class="col-4"><div class="form-check sidebar-setting card-radio"><input class="form-check-input" type="radio" name="data-layout-position" id="layout-position-fixed" value="fixed"><label class="form-check-label p-0 avatar-md w-100" for="layout-position-fixed"></label></div><h5 class="fs-sm text-center fw-medium mt-2">Fixed</h5></div></div>
        </div>
        <div id="preloader-menu" class="mt-4">
            <h6 class="fw-semibold fs-base">Preloader</h6>
            <div class="row"><div class="col-4"><div class="form-check sidebar-setting card-radio"><input class="form-check-input" type="radio" name="data-preloader" id="preloader-view-none" value="disable"><label class="form-check-label p-0 avatar-md w-100" for="preloader-view-none"></label></div><h5 class="fs-sm text-center fw-medium mt-2">Disable</h5></div></div>
        </div>
        <div style="display:none;">
            <input type="radio" id="topbar-color-light" name="data-topbar" value="light">
            <input type="radio" id="topbar-color-dark"  name="data-topbar" value="dark">
        </div>
    </div></div></div>
    <div class="offcanvas-footer border-top p-3 text-center">
        <div class="row"><div class="col-6"><button type="button" class="btn btn-light w-100" id="reset-layout">Reset</button></div></div>
    </div>
</div>

<!-- =====================================================
     SCRIPTS
     ===================================================== -->
<script src="{{ asset('theme/layouts/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/js/app.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/js/plugins.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
(function () {
    'use strict';

    /* ── helpers ─────────────────────────────────────────── */
    const $ = (s, c) => (c || document).querySelector(s);
    const $$ = (s, c) => (c || document).querySelectorAll(s);

    /* ── SIDEBAR TOGGLE ──────────────────────────────────── */
    const ham     = document.getElementById('topnav-hamburger-icon');
    const overlay = document.getElementById('vertical-overlay');
    const body    = document.body;

    const isMobile = () => window.innerWidth < 1025;

    function toggleSidebar(e) {
        if (e) { e.preventDefault(); e.stopPropagation(); e.stopImmediatePropagation(); }
        isMobile()
            ? body.classList.toggle('vertical-sidebar-enable')
            : body.classList.toggle('sidebar-collapsed');
    }

    if (ham)     ham.addEventListener('click', toggleSidebar, true);
    if (overlay) overlay.addEventListener('click', () => body.classList.remove('vertical-sidebar-enable'));
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && isMobile() && body.classList.contains('vertical-sidebar-enable'))
            body.classList.remove('vertical-sidebar-enable');
    });

    let resizeT;
    window.addEventListener('resize', () => {
        clearTimeout(resizeT);
        resizeT = setTimeout(() => {
            if (!isMobile()) body.classList.remove('vertical-sidebar-enable');
            else             body.classList.remove('sidebar-collapsed');
        }, 200);
    });

    /* ── MANUAL DROPDOWN (animated) ─────────────────────── */
    function makeDropdown(btnId, panelId) {
        const btn   = document.getElementById(btnId);
        const panel = document.getElementById(panelId);
        if (!btn || !panel) return;

        Object.assign(panel.style, {
            display: 'none', opacity: '0',
            transform: 'translateY(-10px) scale(0.95)',
            transformOrigin: 'top right',
            transition: 'opacity .22s ease, transform .22s cubic-bezier(0.4,0,0.2,1)'
        });

        const isOpen = () => panel.style.display === 'block';

        const open = () => {
            panel.style.display = 'block';
            panel.getBoundingClientRect();
            panel.style.opacity = '1';
            panel.style.transform = 'translateY(0) scale(1)';
            btn.setAttribute('aria-expanded', 'true');
        };
        const close = () => {
            panel.style.opacity = '0';
            panel.style.transform = 'translateY(-8px) scale(0.95)';
            btn.setAttribute('aria-expanded', 'false');
            setTimeout(() => {
                if (panel.style.opacity === '0') panel.style.display = 'none';
            }, 220);
        };

        btn.addEventListener('click', e => { e.stopPropagation(); isOpen() ? close() : open(); });

        /* ── FIX 2: Do NOT close dropdowns when clicking Bootstrap-controlled
                     elements (modal triggers, dismiss buttons, modal backdrop,
                     and anything inside an open modal).               ────── */
        document.addEventListener('click', e => {
            if (
                e.target.closest('[data-bs-toggle]')   ||
                e.target.closest('[data-bs-dismiss]')  ||
                e.target.closest('[data-bs-target]')   ||
                e.target.closest('.modal')             ||
                e.target.closest('.modal-backdrop')
            ) return;
            if (!btn.contains(e.target) && !panel.contains(e.target)) close();
        });

        document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });

        $$('a', panel).forEach(a => {
            a.addEventListener('mouseenter', () => a.style.background = 'rgba(64,81,137,.08)');
            a.addEventListener('mouseleave', () => a.style.background = '');
        });
    }

    /* ── THEME ───────────────────────────────────────────── */
    function initTheme() {
        const html   = document.documentElement;
        const iconEl = document.getElementById('theme-icon');
        const ICONS  = {
            light: 'bi bi-sun align-middle fs-3xl',
            dark:  'bi bi-moon align-middle fs-3xl',
            auto:  'bi bi-moon-stars align-middle fs-3xl'
        };

        const scheme = m => m === 'auto'
            ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
            : m;

        const apply = mode => {
            const s = scheme(mode);
            html.setAttribute('data-bs-theme', s);
            html.setAttribute('data-topbar', s === 'dark' ? 'dark' : 'light');
            if (iconEl) {
                iconEl.style.transform  = 'rotate(90deg) scale(0)';
                iconEl.style.transition = 'transform .2s ease';
                setTimeout(() => {
                    iconEl.className  = ICONS[mode] || ICONS.light;
                    iconEl.style.transform = 'rotate(0deg) scale(1)';
                }, 180);
            }
            localStorage.setItem('app-theme', mode);
            const r = document.getElementById(s === 'dark' ? 'layout-mode-dark' : 'layout-mode-light');
            if (r) r.checked = true;
            $$('.theme-mode-item').forEach(a => {
                a.style.fontWeight = a.dataset.mode === mode ? '600' : '';
                a.style.color      = a.dataset.mode === mode ? 'var(--vz-primary,#405189)' : '';
            });
            const panel = document.getElementById('theme-dropdown');
            if (panel) { panel.style.opacity = '0'; setTimeout(() => panel.style.display = 'none', 220); }
        };

        apply(localStorage.getItem('app-theme') || 'light');
        $$('.theme-mode-item').forEach(a => a.addEventListener('click', e => { e.preventDefault(); apply(a.dataset.mode); }));
        $$('[name="data-bs-theme"]').forEach(r => r.addEventListener('change', () => apply(r.value)));
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            if (localStorage.getItem('app-theme') === 'auto') apply('auto');
        });
    }

    /* ── FIX 1: NPROGRESS — exclude ALL Bootstrap-controlled links ── */
    function initNProgress() {
        if (typeof NProgress === 'undefined') return;
        NProgress.configure({ showSpinner: false, speed: 380, minimum: 0.08 });

        $$('a[href]').forEach(a => {
            const h = a.getAttribute('href') || '';

            // Skip: empty, hash-only, hash-prefixed, JS, mailto, tel
            if (!h || h === '#' || h.startsWith('#') ||
                h.startsWith('javascript') ||
                h.startsWith('mailto') ||
                h.startsWith('tel')) return;

            // Skip: Bootstrap-controlled attributes
            if (a.hasAttribute('data-bs-toggle')  ||
                a.hasAttribute('data-bs-target')   ||
                a.hasAttribute('data-bs-dismiss')  ||
                a.hasAttribute('data-bs-slide')    ||
                a.hasAttribute('data-bs-slide-to')) return;

            // Skip: new-tab links
            if (a.getAttribute('target') === '_blank') return;

            // Skip: links inside modals (they may close/navigate internally)
            if (a.closest('.modal')) return;

            a.addEventListener('click', () => NProgress.start());
        });

        window.addEventListener('pageshow', () => NProgress.done());
        window.addEventListener('load',     () => NProgress.done());
    }

    /* ── ACTIVE SIDEBAR LINK ─────────────────────────────── */
    function initActiveSidebar() {
        const cur = window.location.pathname;
        $$('#navbar-nav .nav-sm a.nav-link').forEach(link => {
            try {
                const lp = new URL(link.href, location.origin).pathname;
                if (lp !== cur && !(lp.length > 1 && cur.startsWith(lp))) return;
                link.classList.add('nav-active-child');
                const col = link.closest('.collapse');
                if (!col) return;
                col.classList.add('show');
                const tog = $(`[data-bs-target="#${col.id}"],[href="#${col.id}"]`);
                if (tog) {
                    tog.setAttribute('aria-expanded', 'true');
                    tog.classList.remove('collapsed');
                    tog.classList.add('nav-active-parent');
                }
                setTimeout(() => link.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 400);
            } catch(e) {}
        });
    }

    /* ── RIPPLE ──────────────────────────────────────────── */
    function initRipple() {
        $$('#navbar-nav .nav-link').forEach(link => {
            link.addEventListener('click', e => {
                if (link.hasAttribute('data-bs-toggle')) return;
                const r    = document.createElement('span');
                r.className = 'nav-ripple';
                const rect = link.getBoundingClientRect();
                const s    = Math.max(rect.width, rect.height);
                r.style.cssText = `width:${s}px;height:${s}px;left:${e.clientX - rect.left - s / 2}px;top:${e.clientY - rect.top - s / 2}px;`;
                link.appendChild(r);
                setTimeout(() => r.parentNode?.removeChild(r), 650);
            });
        });
    }

    /* ── BACK TO TOP ─────────────────────────────────────── */
    function initBackToTop() {
        const btn = document.getElementById('back-to-top');
        if (!btn) return;
        window.addEventListener('scroll', () => btn.classList.toggle('show', window.scrollY > 300), { passive: true });
        btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    }

    /* ── FIX 4: killBsDropdown() REMOVED ────────────────────
       Disposing Bootstrap's internal Dropdown instance corrupts
       Bootstrap's ability to manage modals and other components.
       Our custom makeDropdown() handles the user-menu entirely,
       so Bootstrap's instance is never needed.               ── */

    /* ── IMAGE MODAL ─────────────────────────────────────── */
    function initImageModal() {
        const m = document.getElementById('imageViewModal');
        if (!m) return;
        m.addEventListener('show.bs.modal', e => {
            const img = document.getElementById('enlargedImage');
            const src = e.relatedTarget?.getAttribute('data-image');
            if (img && src) img.src = src;
        });
    }

    /* ── SEARCH TOOLTIP ──────────────────────────────────── */
    function initSearchTooltip() {
        const btn = document.getElementById('spotlight-trigger');
        const tip = $('.search-tooltip');
        if (!btn || !tip) return;
        btn.addEventListener('mouseenter', () => tip.style.opacity = '1');
        btn.addEventListener('mouseleave', () => tip.style.opacity = '0');
    }

    /* ── FIX 5: FORM NPROGRESS — skip forms inside modals ── */
    function initFormProgress() {
        if (typeof NProgress === 'undefined') return;
        $$('form').forEach(f => {
            // Skip forms inside modals — they submit via AJAX or navigate internally
            if (f.closest('.modal')) return;
            if (f.getAttribute('action') && !f.dataset.noProgress)
                f.addEventListener('submit', () => NProgress.start());
        });
    }

    /* ── RESET LAYOUT ────────────────────────────────────── */
    function initReset() {
        const btn = document.getElementById('reset-layout');
        if (btn) btn.addEventListener('click', () => {
            sessionStorage.clear();
            localStorage.removeItem('app-theme');
            location.reload();
        });
    }

    /* ── INIT ────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', () => {
        initTheme();
        makeDropdown('theme-toggle-btn', 'theme-dropdown');
        makeDropdown('user-menu-btn',    'user-dropdown');
        /* killBsDropdown() intentionally removed — see FIX 4 above */
        initActiveSidebar();
        initRipple();
        initBackToTop();
        initImageModal();
        initSearchTooltip();
        initReset();
        initNProgress();
        initFormProgress();
    });

})();
</script>

<!-- =====================================================
     SPOTLIGHT SEARCH
     ===================================================== -->
<script>
(function(){
    const PAGES = [
        {title:'Administration Dashboard',   url:'{{ route("dashboard") }}',                              icon:'mdi-gauge',              category:'Dashboards'},
        {title:'User Management',            url:'{{ route("users.index") }}',                            icon:'mdi-account-group',      category:'Users & Privileges'},
        {title:'Roles & Permissions',        url:'{{ route("roles.index") }}',                            icon:'mdi-shield-account',     category:'Users & Privileges'},
        {title:'All Students',               url:'{{ route("student.index") }}',                          icon:'mdi-school',             category:'Students'},
        {title:'Batch Registration',         url:'{{ route("studentbatchindex") }}',                      icon:'mdi-account-multiple-plus',category:'Students'},
        {title:'ID Card Generator',          url:'{{ route("student-id-cards.index") }}',                 icon:'mdi-card-account-details',category:'Students'},
        {title:'My Profile',                 url:'{{ route("users.overview", ["id" => Auth::id()]) }}',   icon:'mdi-account-circle',     category:'My Account'},
        {title:'Account Settings',           url:'{{ route("profile.settings", ["id" => Auth::id()]) }}', icon:'mdi-cog',                category:'My Account'},
        {title:'School Information',         url:'{{ route("school-information.index") }}',               icon:'mdi-domain',             category:'School Settings'},
        {title:'School Session',             url:'{{ route("session.index") }}',                          icon:'mdi-calendar-range',     category:'School Settings'},
        {title:'School Term',                url:'{{ route("term.index") }}',                             icon:'mdi-calendar',           category:'School Settings'},
        {title:'Classes',                    url:'{{ route("schoolclass.index") }}',                      icon:'mdi-google-classroom',   category:'School Settings'},
        {title:'Subjects',                   url:'{{ route("subject.index") }}',                          icon:'mdi-book-open-variant',  category:'Subjects'},
        {title:'My Class',                   url:'{{ route("myclass.index") }}',                          icon:'mdi-google-classroom',   category:'Classes & Records'},
        {title:'My Subject',                 url:'{{ route("mysubject.index") }}',                        icon:'mdi-book-open',          category:'Classes & Records'},
        {title:'Terminal Records',           url:'{{ route("myresultroom.index") }}',                     icon:'mdi-file-chart',         category:'Records & Results'},
        {title:'Terminal Result Reports',    url:'{{ route("studentreports.index") }}',                   icon:'mdi-file-document',      category:'Records & Results'},
        {title:'Terminal Result Broadsheet', url:'{{ route("broadsheet.index") }}',                       icon:'mdi-table-large',        category:'Records & Results'},
        {title:'Student Promotions',         url:'{{ route("promotions.index") }}',                       icon:'mdi-arrow-up-circle',    category:'Promotions'},
        {title:'Student Bill',               url:'{{ route("schoolpayment.index") }}',                    icon:'mdi-receipt',            category:'Finance'},
        {title:'Payment Portal',             url:'{{ route("payment.index") }}',                          icon:'mdi-wallet',             category:'Finance'},
        {title:'All Scholarships',           url:'{{ route("admin.scholarship.index") }}',                icon:'mdi-medal',              category:'Finance'},
        {title:'Payroll Periods',            url:'{{ route("payroll.periods") }}',                        icon:'mdi-calendar-clock',     category:'Payroll'},
        {title:'All Examinations',           url:'{{ route("exams.index") }}',                            icon:'mdi-clipboard-text',     category:'Exams & CBT'},
        {title:'CBT Exercise',               url:'{{ route("cbt.index") }}',                              icon:'mdi-monitor',            category:'Exams & CBT'},
        {title:'Admin Timetable',            url:'{{ route("timetable.index") }}',                        icon:'mdi-table-clock',        category:'Timetable'},
        {title:'Mark Attendance',            url:'{{ route("attendance.my-classes") }}',                  icon:'mdi-clipboard-check',    category:'Attendance'},
        {title:'Balance Sheet',              url:'{{ route("reports.financial.balance-sheet") }}',        icon:'mdi-scale-balance',      category:'Accounting'},
        {title:'Generate Transcript',        url:'{{ route("transcript.index") }}',                       icon:'mdi-file-account',       category:'Transcripts'},
        {title:'Admin Score Entry',          url:'{{ route("admin.score-entry.index") }}',                icon:'mdi-clipboard-edit',     category:'Admin Tools'},
    ];

    const COLORS = {
        'Dashboards':'#4f8ef7','Users & Privileges':'#405189','Students':'#e76f51','My Account':'#2a9d8f',
        'School Settings':'#6a0572','Subjects':'#e9c46a','Classes & Records':'#0a9396','Records & Results':'#457b9d',
        'Promotions':'#2a9d8f','Finance':'#10b981','Payroll':'#e76f51','Exams & CBT':'#f4a261',
        'Timetable':'#4f8ef7','Attendance':'#e9c46a','Accounting':'#10b981','Transcripts':'#457b9d','Admin Tools':'#ef4444'
    };

    const HKEY = 'spotlight_history_v2';
    const getHist  = () => { try { return JSON.parse(localStorage.getItem(HKEY) || '[]'); } catch(e) { return []; } };
    const saveHist = h  => localStorage.setItem(HKEY, JSON.stringify(h.slice(0, 8)));
    const addHist  = (q, r) => {
        if (!q || q.trim().length < 2) return;
        const h = getHist().filter(x => !(x.query === q && x.url === r.url));
        h.unshift({ query: q, url: r.url, title: r.title, icon: r.icon, category: r.category, ts: Date.now() });
        saveHist(h); renderHistory();
    };

    const overlay  = document.getElementById('spotlight-overlay');
    const box      = document.getElementById('spotlight-box');
    const input    = document.getElementById('spotlight-input');
    const emptyEl  = document.getElementById('spotlight-empty');
    const loadEl   = document.getElementById('spotlight-loading');
    const list     = document.getElementById('spotlight-list');
    const histSec  = document.getElementById('spotlight-history-section');
    const histList = document.getElementById('spotlight-history-list');
    const clearBtn = document.getElementById('spotlight-clear-history-btn');
    const clearTop = document.getElementById('spotlight-clear-history');
    const escBtn   = document.getElementById('spotlight-esc');
    const triggerD = document.getElementById('spotlight-trigger');
    const triggerM = document.getElementById('spotlight-trigger-mobile');

    let timer = null, activeIdx = -1, results = [];

    function open() {
        if (!overlay) return;
        overlay.style.display   = 'flex';
        overlay.style.animation = 'spotlightOverlayFadeIn .28s ease forwards';
        /* FIX 3: restore pointer-events when opening */
        overlay.style.pointerEvents = '';
        if (box) box.style.animation = 'spotlightModalBounceIn .4s cubic-bezier(.34,1.3,.64,1) forwards';
        setTimeout(() => input?.focus(), 120);
        renderHistory();
    }

    function close() {
        if (box)     box.style.animation     = 'spotlightModalFadeOut .2s ease forwards';
        if (overlay) {
            overlay.style.animation    = 'spotlightOverlayFadeOut .2s ease forwards';
            /* FIX 3: immediately block pointer events so the 200ms fade-out
               window cannot intercept Bootstrap modal trigger clicks          */
            overlay.style.pointerEvents = 'none';
        }
        setTimeout(() => {
            if (overlay) {
                overlay.style.display       = 'none';
                overlay.style.animation     = '';
                overlay.style.pointerEvents = ''; /* restore for next open */
            }
            if (input) input.value = '';
            showEmpty();
        }, 200);
    }

    function showEmpty() {
        if (emptyEl) {
            emptyEl.innerHTML = '<i class="mdi mdi-lightning-bolt" style="font-size:44px;display:block;margin-bottom:14px;opacity:.38;"></i><span style="font-size:14px;">Start typing to search…</span><div style="margin-top:12px;font-size:11px;opacity:.38;">Try: Students, Classes, Payments, Reports</div>';
            emptyEl.style.display = 'block';
        }
        if (loadEl) loadEl.style.display = 'none';
        if (list)   { list.style.display = 'none'; list.innerHTML = ''; }
        if (clearTop) clearTop.style.display = getHist().length > 0 ? 'block' : 'none';
        renderHistory();
        results = []; activeIdx = -1;
    }

    function showLoading() {
        if (emptyEl) emptyEl.style.display = 'none';
        if (loadEl)  loadEl.style.display  = 'block';
        if (list)    list.style.display    = 'none';
        if (histSec) histSec.style.display = 'none';
    }

    function renderHistory() {
        const h = getHist();
        if (h.length > 0 && (!input || !input.value.trim())) {
            if (histSec)  histSec.style.display  = 'block';
            if (histList) histList.innerHTML      = '';
            if (clearTop) clearTop.style.display = 'block';
            h.forEach((item, idx) => {
                const div = document.createElement('div');
                const c   = COLORS[item.category] || '#4f8ef7';
                div.style.cssText = 'display:flex;align-items:center;gap:12px;padding:9px 22px;cursor:pointer;transition:background .15s;border-radius:8px;margin:0 12px;';
                div.innerHTML = `<span style="width:30px;height:30px;border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:${c}20;"><i class="${item.icon||'mdi-history'} mdi" style="font-size:15px;color:${c};"></i></span>
                    <span style="flex:1;min-width:0;"><span style="display:block;font-size:13px;font-weight:500;color:rgba(255,255,255,.9);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${item.title}</span><span style="display:block;font-size:11px;color:rgba(255,255,255,.38);">${item.query}</span></span>
                    <button class="rm-hist" style="background:transparent;border:none;color:rgba(255,255,255,.3);cursor:pointer;padding:4px 8px;border-radius:5px;font-size:12px;transition:color .15s;">✕</button>`;
                div.querySelector('.rm-hist').addEventListener('click', e => {
                    e.stopPropagation();
                    const hh = getHist(); hh.splice(idx, 1); saveHist(hh); renderHistory();
                });
                div.addEventListener('click', () => { if (input) { input.value = item.query; performSearch(item.query); } });
                div.addEventListener('mouseenter', () => div.style.background = 'rgba(255,255,255,.05)');
                div.addEventListener('mouseleave', () => div.style.background = '');
                histList?.appendChild(div);
            });
        } else {
            if (histSec)  histSec.style.display  = 'none';
            if (clearTop) clearTop.style.display = 'none';
        }
    }

    function performSearch(q) {
        if (!q || !q.trim()) { showEmpty(); return; }
        showLoading();
        const sr = PAGES.filter(p =>
            p.title.toLowerCase().includes(q.toLowerCase()) ||
            p.category.toLowerCase().includes(q.toLowerCase())
        ).slice(0, 14);
        renderResults(sr);
        clearTimeout(timer);
        timer = setTimeout(() => {
            if (q.length < 2) return;
            fetch('{{ url("/api/search") }}?q=' + encodeURIComponent(q) + '&_token={{ csrf_token() }}',
                { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(r => r.ok ? r.json() : { results: [] })
            .then(d => {
                if (!input || input.value.trim() !== q) return;
                const merged = sr.concat(d.results || []);
                const seen   = {};
                renderResults(merged.filter(r => { if (seen[r.url]) return false; seen[r.url] = true; return true; }));
            }).catch(() => {});
        }, 300);
    }

    function renderResults(rs) {
        if (loadEl)  loadEl.style.display  = 'none';
        if (emptyEl) emptyEl.style.display = 'none';
        if (list)    { list.innerHTML = ''; list.style.display = 'block'; }
        if (histSec) histSec.style.display = 'none';
        activeIdx = -1; results = rs;

        if (!rs.length) {
            if (emptyEl) {
                emptyEl.innerHTML = `<i class="mdi mdi-magnify-close" style="font-size:40px;display:block;margin-bottom:14px;opacity:.38;"></i><span style="font-size:14px;">No results for "${input ? input.value : ''}"</span>`;
                emptyEl.style.display = 'block';
            }
            if (list) list.style.display = 'none';
            return;
        }

        const grouped = {};
        rs.forEach(r => { if (!grouped[r.category]) grouped[r.category] = []; grouped[r.category].push(r); });
        let idx = 0;
        Object.keys(grouped).forEach(cat => {
            const h = document.createElement('li');
            h.style.cssText = 'padding:10px 22px 5px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.32);';
            h.textContent = cat; list.appendChild(h);
            grouped[cat].forEach((r, gi) => {
                const li = document.createElement('li');
                li.className = 'spotlight-result-item';
                li.setAttribute('data-idx', idx);
                li.style.cssText = 'display:flex;align-items:center;gap:12px;padding:10px 22px;cursor:pointer;transition:all .18s;border-radius:8px;margin:2px 10px;';
                const c = COLORS[r.category] || '#4f8ef7';
                li.innerHTML = `<span style="width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:${c}20;"><i class="${r.icon||'mdi-chevron-right'} mdi" style="font-size:17px;color:${c};"></i></span>
                    <span style="flex:1;min-width:0;"><span class="result-title" style="display:block;font-size:14px;font-weight:500;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${r.title}</span><span style="display:block;font-size:11px;color:rgba(255,255,255,.38);margin-top:1px;">${r.subtitle||r.category}</span></span>
                    <i class="mdi mdi-arrow-right" style="font-size:15px;color:rgba(255,255,255,.22);flex-shrink:0;transition:transform .18s;"></i>`;
                li.addEventListener('mouseenter', () => { li.style.background = `${c}14`; activeIdx = idx; });
                li.addEventListener('mouseleave', () => { li.style.background = activeIdx === idx ? `${c}20` : ''; });
                li.addEventListener('click', () => { addHist(input ? input.value : '', r); window.location.href = r.url; });
                list.appendChild(li); idx++;
            });
        });
    }

    function highlightItem(items) {
        items.forEach((li, i) => {
            const active = (i === activeIdx);
            li.style.background = active ? 'rgba(79,142,247,.15)' : '';
            const t   = li.querySelector('.result-title');
            const arr = li.querySelector('.mdi-arrow-right');
            if (t)   t.style.color           = active ? '#7eb8fb' : '#fff';
            if (arr) arr.style.transform      = active ? 'translateX(5px)' : 'translateX(0)';
            if (active) li.scrollIntoView({ block: 'nearest' });
        });
    }

    if (triggerD) triggerD.addEventListener('click', open);
    if (triggerM) triggerM.addEventListener('click', open);
    if (escBtn)   escBtn.addEventListener('click', close);
    if (clearBtn) clearBtn.addEventListener('click', () => { localStorage.removeItem(HKEY); renderHistory(); showEmpty(); });
    if (clearTop) clearTop.addEventListener('click', () => { localStorage.removeItem(HKEY); renderHistory(); showEmpty(); });
    if (overlay)  overlay.addEventListener('click', e => { if (e.target === overlay) close(); });

    document.addEventListener('keydown', e => {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            overlay && overlay.style.display === 'flex' ? close() : open();
        }
        if (e.key === 'Escape' && overlay && overlay.style.display === 'flex') close();
    });

    if (input) {
        input.addEventListener('keydown', e => {
            const items = list.querySelectorAll('li[data-idx]');
            if (e.key === 'ArrowDown')      { e.preventDefault(); activeIdx = Math.min(activeIdx + 1, items.length - 1); highlightItem(items); }
            else if (e.key === 'ArrowUp')   { e.preventDefault(); activeIdx = Math.max(activeIdx - 1, 0); highlightItem(items); }
            else if (e.key === 'Enter' && activeIdx >= 0 && results[activeIdx]) {
                addHist(input.value, results[activeIdx]);
                window.location.href = results[activeIdx].url;
            }
        });
        input.addEventListener('input', () => {
            const q = input.value.trim();
            if (!q) { showEmpty(); renderHistory(); return; }
            if (clearTop) clearTop.style.display = 'none';
            performSearch(q);
        });
    }

    renderHistory();
})();
</script>

<!-- Route-specific JS -->
@if (Route::is('dashboard'))              @include('layouts.pages-assets.js.dashboard-list-js') @endif
@if (Route::is('users.*'))                @include('layouts.pages-assets.js.users-list-js') @endif
@if (Route::is('student-id-cards.*'))     @include('layouts.pages-assets.js.idcard-list-js') @endif
@if (Route::is('student.payments.*'))     @include('layouts.pages-assets.js.studentpayment-list-js') @endif
@if (Route::is('profile.*'))              @include('layouts.pages-assets.js.users-list-js') @endif
@if (Route::is('roles.*'))                @include('layouts.pages-assets.js.role-list-js') @endif
@if (Route::is('permissions.*'))          @include('layouts.pages-assets.js.permissions-list-js') @endif
@if (Route::is('session.*'))              @include('layouts.pages-assets.js.session-list-js') @endif
@if (Route::is('term.*'))                 @include('layouts.pages-assets.js.term-list-js') @endif
@if (Route::is('school-information.*'))   @include('layouts.pages-assets.js.schoolinformation-list-js') @endif
@if (Route::is('admin.school-info.*'))    @include('layouts.pages-assets.js.schoolinformation-list-js') @endif
@if (Route::is('schoolhouse.*'))          @include('layouts.pages-assets.js.schoolhouse-list-js') @endif
@if (Route::is('schoolarm.*'))            @include('layouts.pages-assets.js.arm-list-js') @endif
@if (Route::is('classcategories.*'))      @include('layouts.pages-assets.js.classcategory-list-js') @endif
@if (Route::is('schoolclass.*'))          @include('layouts.pages-assets.js.schoolclass-list-js') @endif
@if (Route::is('classteacher.*'))         @include('layouts.pages-assets.js.classteacher-list-js') @endif
@if (Route::is('subject.*'))              @include('layouts.pages-assets.js.subject-list-js') @endif
@if (Route::is('subjects.*'))             @include('layouts.pages-assets.js.subject-list-js') @endif
@if (Route::is('subjectteacher.*'))       @include('layouts.pages-assets.js.subjectteacher-list-js') @endif
@if (Route::is('subjectclass.*'))         @include('layouts.pages-assets.js.subjectclass-list-js') @endif
@if (Route::is('schoolbill.*'))           @include('layouts.pages-assets.js.schoolbill-list-js') @endif
@if (Route::is('schoolbilltermsession.*'))@include('layouts.pages-assets.js.schoolbilltermsession-list-js') @endif
@if (Route::is('student.*'))              @include('layouts.pages-assets.js.student-list-js') @endif
@if (Route::is('studentbatchindex'))      @include('layouts.pages-assets.js.studentbatch-list-js') @endif
@if (Route::is('myclass.*'))              @include('layouts.pages-assets.js.myclass-list-js') @endif
@if (Route::is('mysubject.*'))            @include('layouts.pages-assets.js.mysubject-list-js') @endif
@if (Route::is('viewstudent'))            @include('layouts.pages-assets.js.viewstudent-list-js') @endif
@if (Route::is('studentreports.*'))       @include('layouts.pages-assets.js.studentreport-list-js') @endif
@if (Route::is('broadsheet.*'))           @include('layouts.pages-assets.js.studentreport-list-js') @endif
@if (Route::is('studentmockreports.*'))   @include('layouts.pages-assets.js.studentmockreport-list-js') @endif
@if (Route::is('subjectoperation.*'))     @include('layouts.pages-assets.js.subjectoperation-list-js') @endif
@if (Route::is('subjects.subjectinfo'))   @include('layouts.pages-assets.js.subjectinfo-list-js') @endif
@if (Route::is('myresultroom.*'))         @include('layouts.pages-assets.js.myresultroom-list-js') @endif
@if (Route::is('assessment.*'))           @include('layouts.pages-assets.js.subjectscoresheet-list-js') @endif
@if (Route::is('assessments'))            @include('layouts.pages-assets.js.studentassessment-list-js') @endif
@if (Route::is('subjectscoresheet'))      @include('layouts.pages-assets.js.subjectscoresheet-list-js') @endif
@if (Route::is('subjectscoresheet-mock.*'))@include('layouts.pages-assets.js.subjectscoresheet-mock-list-js') @endif
@if (Route::is('studentresults*'))        @include('layouts.pages-assets.js.studentresults-list-js') @endif
@if (Route::is('schoolbill*'))            @include('layouts.pages-assets.js.schoolbill-list-js') @endif
@if (Route::is('schoolpayment*'))         @include('layouts.pages-assets.js.schoolpayment-list-js') @endif
@if (Route::is('analysis*'))              @include('layouts.pages-assets.js.analysis-list-js') @endif
@if (Route::is('exams*'))                 @include('layouts.pages-assets.js.exams-list-js') @endif
@if (Route::is('questions*'))             @include('layouts.pages-assets.js.questions-list-js') @endif
@if (Route::is('cbt*'))                   @include('layouts.pages-assets.js.cbt-list-js') @endif
@if (Route::is('classbroadsheet.*'))      @include('layouts.pages-assets.js.classbroadsheet-list-js') @endif
@if (Route::is('principalscomment.*'))    @include('layouts.pages-assets.js.principalscomment-list-js') @endif
@if (Route::is('myprincipalscomment.*'))  @include('layouts.pages-assets.js.myprincipalscomment-list-js') @endif
@if (Route::is('compulsorysubjectclass.*'))@include('layouts.pages-assets.js.compulsorysubjectclass-list-js') @endif
@if (Route::is('subjectvetting.*'))       @include('layouts.pages-assets.js.subjectvetting-list-js') @endif
@if (Route::is('mocksubjectvetting.*'))   @include('layouts.pages-assets.js.mocksubjectvetting-list-js') @endif
@if (Route::is('mysubjectvettings.*'))    @include('layouts.pages-assets.js.mysubjectvettings-list-js') @endif
@if (Route::is('mymocksubjectvettings.*'))@include('layouts.pages-assets.js.timetable-list-js') @endif
@if (Route::is('timetable.*'))            @include('layouts.pages-assets.js.timetable-list-js') @endif
@if (Route::is('rooms.*'))                @include('layouts.pages-assets.js.rooms-list-js') @endif
@if (Route::is('promotions.*'))           @include('layouts.pages-assets.js.promotions-list-js') @endif
@if (Route::is('attendance.*'))           @include('layouts.pages-assets.js.attendance-list-js') @endif
@if (Route::is('transcript.*'))           @include('layouts.pages-assets.js.attendance-list-js') @endif
@if (Route::is('admin.score-entry.*'))    @include('layouts.pages-assets.js.adminscoreentry-list-js') @endif
@if (Route::is('admin.scholarship.*') || Route::is('admin.discount.*') || Route::is('sibling.*') ||
    Route::is('payment.*') || Route::is('reports.financial.*') || Route::is('reports.analysis.*') ||
    Route::is('payroll.*') || Route::is('staff.payments.*'))
    @include('layouts.pages-assets.js.scholarship-list-js')
@endif

</body>
</html>
