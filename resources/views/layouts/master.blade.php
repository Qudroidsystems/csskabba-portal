<!doctype html>
<html lang="en" data-layout="vertical" data-sidebar="dark" data-sidebar-size="lg" data-preloader="disable" data-theme="default" data-topbar="light" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <title>{{ $pagetitle }} | Vite-ESchool 2.0</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
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
           MOBILE SIDEBAR — smooth slide animation
           ===================================================== */
        .vertical-overlay {
            position: fixed;
            inset: 0;
            z-index: 999;
            background: rgba(0,0,0,.45);
            display: none;
            backdrop-filter: blur(2px);
            transition: backdrop-filter .3s ease;
        }
        body.vertical-sidebar-enable .vertical-overlay {
            display: block;
            animation: fadeIn 0.3s ease forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; backdrop-filter: blur(0); }
            to { opacity: 1; backdrop-filter: blur(2px); }
        }

        /* Mobile sidebar with smooth transform animation */
        @media (max-width: 1024.98px) {
            .app-menu {
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: none;
            }
            body.vertical-sidebar-enable .app-menu {
                transform: translateX(0);
                box-shadow: 4px 0 24px rgba(0,0,0,.35);
            }
        }
        @media (min-width: 1025px) {
            body.vertical-sidebar-enable .app-menu {
                transform: none;
            }
        }

        /* =====================================================
           SPOTLIGHT SEARCH - Enhanced Mobile Support
           ===================================================== */
        #spotlight-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 10000;
            align-items: flex-start;
            justify-content: center;
            padding-top: 6vh;
            background: rgba(0,0,0,.75);
            backdrop-filter: blur(8px);
            animation: spotlightOverlayFadeIn 0.25s ease forwards;
        }
        #spotlight-box {
            width: 100%;
            max-width: 860px;
            margin: 0 24px;
            background: rgba(24,26,32,.98);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 28px;
            box-shadow: 0 32px 80px rgba(0,0,0,.6);
            overflow: hidden;
            animation: spotlightModalBounceIn 0.35s cubic-bezier(0.34, 1.3, 0.64, 1) forwards;
        }
        @media (max-width: 768px) {
            #spotlight-box {
                margin: 0 16px;
                border-radius: 20px;
            }
            #spotlight-input {
                font-size: 16px !important;
            }
        }
        @keyframes spotlightOverlayFadeIn {
            from { background: rgba(0,0,0,.2); backdrop-filter: blur(0); }
            to { background: rgba(0,0,0,.75); backdrop-filter: blur(8px); }
        }
        @keyframes spotlightOverlayFadeOut {
            from { background: rgba(0,0,0,.75); backdrop-filter: blur(8px); }
            to { background: rgba(0,0,0,.2); backdrop-filter: blur(0); }
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
        @keyframes loadingSpin {
            0% { transform: rotate(0); }
            100% { transform: rotate(360deg); }
        }
        .spotlight-result-item {
            animation: resultBounceIn 0.35s cubic-bezier(0.34, 1.3, 0.64, 1) forwards;
            opacity: 0;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .spotlight-result-item:hover {
            background: rgba(79,142,247,.12) !important;
            transform: translateX(4px);
        }
        .spotlight-result-item.top-match {
            animation: resultBounceIn 0.4s cubic-bezier(0.34, 1.3, 0.64, 1) forwards;
            border-left: 3px solid #4f8ef7;
            background: linear-gradient(90deg, rgba(79,142,247,.08) 0%, transparent 100%);
        }
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
        @media (max-width: 768px) {
            .search-tooltip { display: none; }
        }

        /* =====================================================
           BACK TO TOP
           ===================================================== */
        #back-to-top {
            opacity: 0;
            visibility: hidden;
            transform: translateY(12px);
            transition: opacity .3s, transform .3s, visibility .3s;
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 99;
        }
        #back-to-top.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        #back-to-top:hover {
            transform: translateY(-3px) !important;
        }
    </style>

    <!-- Route-specific CSS includes (same as original) -->
</head>

<body>
<div id="layout-wrapper">

    <!-- ========== SIDEBAR (full content same as original) ========== -->
    <div class="app-menu navbar-menu">
        <div class="navbar-brand-box">
            @php
                use App\Models\SchoolInformation;
                $schoolInfo = SchoolInformation::getActiveSchool();
                $schoolName = $schoolInfo?->school_name ?? config('app.name', 'School System');
                $defaultLogo = asset('theme/layouts/assets/images/logo-dark.png');
                $defaultLogoLight = asset('theme/layouts/assets/images/logo-light.png');
            @endphp
            <a href="{{ url('/') }}" class="logo logo-dark">
                <span class="logo-sm"><img src="{{ $schoolInfo?->getLogoUrlAttribute() ?? $defaultLogo }}" alt="{{ $schoolName }}" style="height:80px;width:auto;border-radius:10px;object-fit:contain;padding:3px;background:rgb(39,38,38);"></span>
                <span class="logo-lg"><img src="{{ $schoolInfo?->getLogoUrlAttribute() ?? $defaultLogo }}" alt="{{ $schoolName }}" style="height:80px;width:auto;border-radius:12px;object-fit:contain;padding:2px;background:rgb(37,36,36);"></span>
            </a>
            <a href="{{ url('/') }}" class="logo logo-light">
                <span class="logo-sm"><img src="{{ $schoolInfo?->getLogoUrlAttribute() ?? $defaultLogoLight }}" alt="{{ $schoolName }}" style="height:45px;width:auto;border-radius:10px;object-fit:contain;padding:3px;background:rgb(40,39,39);"></span>
                <span class="logo-lg"><img src="{{ $schoolInfo?->getLogoUrlAttribute() ?? $defaultLogoLight }}" alt="{{ $schoolName }}" style="height:80px;width:auto;border-radius:12px;object-fit:contain;padding:2px;background:rgb(37,36,36);"></span>
            </a>
            <button type="button" class="btn btn-sm p-0 fs-3xl header-item float-end btn-vertical-sm-hover" id="vertical-hover">
                <i class="ri-record-circle-line"></i>
            </button>
        </div>

        <div id="scrollbar">
            <div class="container-fluid">
                <div id="two-column-menu"></div>
                <ul class="navbar-nav" id="navbar-nav">
                    <!-- Sidebar navigation items - include all your existing menu items here -->
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
                    <!-- Add all other sidebar items here -->
                </ul>
            </div>
        </div>

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
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-logout-btn"><i class="mdi mdi-logout"></i><span>Sign Out</span></button>
            </form>
        </div>
        @endauth
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
                    <div class="d-none d-md-inline-flex align-items-center" style="position:relative;">
                        <button type="button" id="spotlight-trigger" class="spotlight-trigger-btn" style="display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:10px;padding:7px 14px;cursor:pointer;transition:all .2s;min-width:220px;">
                            <i class="mdi mdi-magnify" style="font-size:16px;opacity:.6;"></i>
                            <span style="font-size:13px;opacity:.55;flex:1;text-align:left;">Search everything…</span>
                            <div style="display:flex;gap:4px;"><kbd style="font-size:10px;padding:2px 6px;border-radius:4px;background:rgba(255,255,255,.12);">⌘</kbd><kbd style="font-size:10px;padding:2px 6px;border-radius:4px;background:rgba(255,255,255,.12);">K</kbd></div>
                        </button>
                    </div>
                    <!-- Mobile search button -->
                    <button type="button" id="mobile-spotlight-trigger" class="d-md-none btn btn-icon btn-topbar btn-ghost-dark rounded-circle" style="width:38px;height:38px;">
                        <i class="mdi mdi-magnify fs-3xl"></i>
                    </button>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <div class="position-relative">
                        <button type="button" id="theme-toggle-btn" class="btn btn-icon btn-topbar btn-ghost-dark rounded-circle" style="width:38px;height:38px;"><i id="theme-icon" class="bi bi-sun align-middle fs-3xl"></i></button>
                        <div id="theme-dropdown" style="display:none;position:absolute;top:calc(100% + 8px);right:0;min-width:170px;background:#fff;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.12);z-index:9999;padding:6px;">
                            <a href="#" class="theme-mode-item d-flex align-items-center gap-2 px-3 py-2 rounded-2 text-decoration-none" data-mode="light"><i class="bi bi-sun"></i> Light</a>
                            <a href="#" class="theme-mode-item d-flex align-items-center gap-2 px-3 py-2 rounded-2 text-decoration-none" data-mode="dark"><i class="bi bi-moon"></i> Dark</a>
                            <a href="#" class="theme-mode-item d-flex align-items-center gap-2 px-3 py-2 rounded-2 text-decoration-none" data-mode="auto"><i class="bi bi-moon-stars"></i> Auto</a>
                        </div>
                    </div>

                    @php
                        $userdata = Auth::user();
                        $isStudent = $userdata->hasRole('student');
                        $fullName = $userdata->name ?? 'User';
                        $initials = collect(explode(' ', $fullName))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->implode('');
                        $srcPath = null;
                        if ($isStudent) {
                            $student = \App\Models\Student::where('id', $userdata->student_id)->first();
                            if ($student?->picture && \Illuminate\Support\Facades\Storage::disk('public')->exists('student_avatars/' . basename($student->picture)))
                                $srcPath = asset('storage/student_avatars/' . basename($student->picture));
                        } else {
                            if ($userdata->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists('staff_avatars/' . basename($userdata->avatar)))
                                $srcPath = asset('storage/staff_avatars/' . basename($userdata->avatar));
                        }
                    @endphp

                    <div class="dropdown position-relative ms-sm-3 header-item topbar-user">
                        <button type="button" id="user-menu-btn" class="btn shadow-none p-0">
                            <span class="d-flex align-items-center gap-2">
                                <span style="display:inline-block;width:42px;height:42px;">
                                    @if($srcPath)
                                        <img src="{{ $srcPath }}" alt="{{ $fullName }}" style="width:42px;height:42px;border-radius:10px;object-fit:cover;">
                                    @else
                                        <span style="display:flex;width:42px;height:42px;border-radius:10px;background:#405189;color:#fff;align-items:center;justify-content:center;">{{ $initials }}</span>
                                    @endif
                                </span>
                                <span class="d-none d-xl-flex"><span class="fw-medium" style="font-size:13px;">{{ $userdata->name }}</span></span>
                            </span>
                        </button>
                        <div id="user-dropdown" class="dropdown-menu dropdown-menu-end" style="display:none;position:absolute;top:calc(100% + 8px);right:0;min-width:220px;background:#fff;border-radius:12px;z-index:9999;"></div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ========== SPOTLIGHT SEARCH MODAL ========== -->
    <div id="spotlight-overlay">
        <div id="spotlight-box">
            <div style="display:flex;align-items:center;gap:16px;padding:20px 24px;border-bottom:1px solid rgba(255,255,255,.08);">
                <i class="mdi mdi-magnify" style="font-size:26px;color:#4f8ef7;"></i>
                <input id="spotlight-input" type="text" placeholder="Search for pages, students, staff, classes…" autocomplete="off" style="flex:1;background:transparent;border:none;outline:none;font-size:18px;color:#fff;caret-color:#4f8ef7;padding:8px 0;">
                <div style="display:flex;gap:8px;">
                    <button id="spotlight-clear-history" style="background:rgba(255,255,255,.08);border:none;border-radius:10px;padding:6px 12px;color:rgba(255,255,255,.6);font-size:12px;cursor:pointer;">Clear</button>
                    <kbd id="spotlight-esc" style="font-size:12px;padding:4px 10px;border-radius:8px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);color:rgba(255,255,255,.6);cursor:pointer;">ESC</kbd>
                </div>
            </div>
            <div id="spotlight-results" style="max-height:520px;overflow-y:auto;padding:12px 0;">
                <div id="spotlight-empty" style="padding:48px 24px;text-align:center;color:rgba(255,255,255,.35);">
                    <i class="mdi mdi-lightning-bolt" style="font-size:48px;display:block;margin-bottom:16px;"></i>
                    <span>Start typing to search…</span>
                </div>
                <div id="spotlight-loading" style="display:none;padding:48px;text-align:center;">
                    <div style="display:inline-block;width:32px;height:32px;border:2px solid rgba(255,255,255,.15);border-top-color:#4f8ef7;border-radius:50%;animation:loadingSpin .7s linear infinite;"></div>
                    <div style="margin-top:16px;color:rgba(255,255,255,.45);">Searching<span class="typing-dot">.</span><span class="typing-dot">.</span><span class="typing-dot">.</span></div>
                </div>
                <ul id="spotlight-list" style="list-style:none;margin:0;padding:0;display:none;"></ul>
            </div>
            <div style="padding:14px 24px;border-top:1px solid rgba(255,255,255,.07);display:flex;gap:24px;font-size:12px;color:rgba(255,255,255,.35);flex-wrap:wrap;">
                <span><kbd style="background:rgba(255,255,255,.1);border-radius:5px;padding:2px 6px;">⌘K</kbd> / <kbd style="background:rgba(255,255,255,.1);border-radius:5px;padding:2px 6px;">Ctrl+K</kbd> open</span>
                <span><kbd style="background:rgba(255,255,255,.1);border-radius:5px;padding:2px 6px;">↑↓</kbd> navigate</span>
                <span><kbd style="background:rgba(255,255,255,.1);border-radius:5px;padding:2px 6px;">↵</kbd> open</span>
            </div>
        </div>
    </div>

    @yield('content')

    <footer class="footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><script>document.write(new Date().getFullYear())</script> © {{ $schoolInfo->school_name ?? 'Vite-ESchool' }}</div>
                <div class="col-sm-6"><div class="text-sm-end d-none d-sm-block">Created by Qudroid Systems</div></div>
            </div>
        </div>
    </footer>
</div>

<button class="btn btn-dark btn-icon" id="back-to-top"><i class="bi bi-caret-up fs-3xl"></i></button>

<script src="{{ asset('theme/layouts/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/js/app.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/js/plugins.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
(function() {
    'use strict';

    // =====================================================
    // SIDEBAR TOGGLE - Mobile & Desktop
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
        if (e) e.preventDefault();
        body.classList.contains('vertical-sidebar-enable') ? closeSidebar() : openSidebar();
    }
    if (ham) ham.addEventListener('click', toggleSidebar);
    if (overlay) overlay.addEventListener('click', closeSidebar);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && body.classList.contains('vertical-sidebar-enable')) closeSidebar();
    });

    // =====================================================
    // SPOTLIGHT SEARCH - Fully Functional with Animations
    // =====================================================
    const spotlightOverlay = document.getElementById('spotlight-overlay');
    const spotlightInput = document.getElementById('spotlight-input');
    const spotlightList = document.getElementById('spotlight-list');
    const spotlightEmpty = document.getElementById('spotlight-empty');
    const spotlightLoading = document.getElementById('spotlight-loading');
    const spotlightTrigger = document.getElementById('spotlight-trigger');
    const mobileTrigger = document.getElementById('mobile-spotlight-trigger');
    const escBtn = document.getElementById('spotlight-esc');
    const clearHistoryBtn = document.getElementById('spotlight-clear-history');

    let searchTimeout = null;
    let currentResults = [];
    let activeIndex = -1;
    const HISTORY_KEY = 'spotlight_search_history';

    // Static pages for search
    const STATIC_PAGES = [
        { title: 'Administration Dashboard', url: '{{ route("dashboard") }}', icon: 'mdi-gauge', category: 'Dashboards' },
        { title: 'User Management', url: '{{ route("users.index") }}', icon: 'mdi-account-group', category: 'Users' },
        { title: 'All Students', url: '{{ route("student.index") }}', icon: 'mdi-school', category: 'Students' },
        { title: 'My Profile', url: '{{ route("users.overview", ["id" => Auth::id()]) }}', icon: 'mdi-account-circle', category: 'Account' },
    ];

    function getHistory() {
        try { return JSON.parse(localStorage.getItem(HISTORY_KEY) || '[]'); } catch(e) { return []; }
    }

    function saveHistory(history) {
        localStorage.setItem(HISTORY_KEY, JSON.stringify(history.slice(0, 10)));
    }

    function addToHistory(query, result) {
        if (!query || query.trim().length < 2) return;
        let history = getHistory();
        history = history.filter(item => !(item.query === query && item.url === result.url));
        history.unshift({ query: query, url: result.url, title: result.title, icon: result.icon, category: result.category, ts: Date.now() });
        saveHistory(history);
    }

    function openSpotlight() {
        if (!spotlightOverlay) return;
        spotlightOverlay.style.display = 'flex';
        spotlightOverlay.style.animation = 'spotlightOverlayFadeIn 0.25s ease forwards';
        const box = document.getElementById('spotlight-box');
        if (box) box.style.animation = 'spotlightModalBounceIn 0.35s cubic-bezier(0.34, 1.3, 0.64, 1) forwards';
        setTimeout(() => { if (spotlightInput) spotlightInput.focus(); }, 100);
        if (spotlightInput) spotlightInput.value = '';
        showEmpty();
    }

    function closeSpotlight() {
        const box = document.getElementById('spotlight-box');
        if (box) box.style.animation = 'spotlightModalFadeOut 0.2s ease forwards';
        if (spotlightOverlay) spotlightOverlay.style.animation = 'spotlightOverlayFadeOut 0.2s ease forwards';
        setTimeout(() => {
            if (spotlightOverlay) spotlightOverlay.style.display = 'none';
            if (spotlightInput) spotlightInput.value = '';
            showEmpty();
        }, 200);
    }

    function showEmpty() {
        if (spotlightEmpty) spotlightEmpty.style.display = 'block';
        if (spotlightLoading) spotlightLoading.style.display = 'none';
        if (spotlightList) { spotlightList.style.display = 'none'; spotlightList.innerHTML = ''; }
        currentResults = [];
        activeIndex = -1;
    }

    function showLoading() {
        if (spotlightEmpty) spotlightEmpty.style.display = 'none';
        if (spotlightLoading) spotlightLoading.style.display = 'block';
        if (spotlightList) spotlightList.style.display = 'none';
    }

    function searchStatic(query) {
        const lowerQuery = query.toLowerCase().trim();
        return STATIC_PAGES.filter(p => p.title.toLowerCase().includes(lowerQuery) || p.category.toLowerCase().includes(lowerQuery)).slice(0, 15);
    }

    function renderResults(results) {
        if (!spotlightList) return;
        if (spotlightLoading) spotlightLoading.style.display = 'none';
        if (spotlightEmpty) spotlightEmpty.style.display = 'none';
        spotlightList.innerHTML = '';
        spotlightList.style.display = 'block';
        currentResults = results;
        activeIndex = -1;

        if (results.length === 0) {
            if (spotlightEmpty) {
                spotlightEmpty.innerHTML = '<i class="mdi mdi-magnify-close" style="font-size:42px;display:block;margin-bottom:16px;opacity:.4;"></i><span>No results found</span>';
                spotlightEmpty.style.display = 'block';
            }
            spotlightList.style.display = 'none';
            return;
        }

        // Group by category
        const grouped = {};
        results.forEach(r => { if (!grouped[r.category]) grouped[r.category] = []; grouped[r.category].push(r); });

        let idx = 0;
        Object.keys(grouped).forEach(cat => {
            const header = document.createElement('li');
            header.style.cssText = 'padding:12px 24px 6px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.35);';
            header.textContent = cat;
            spotlightList.appendChild(header);

            grouped[cat].forEach(r => {
                const li = document.createElement('li');
                li.className = 'spotlight-result-item';
                li.setAttribute('data-index', idx);
                li.style.cssText = 'display:flex;align-items:center;gap:14px;padding:12px 24px;cursor:pointer;transition:all .2s;border-radius:10px;margin:4px 12px;';
                li.innerHTML = `
                    <span style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:rgba(79,142,247,.15);"><i class="${r.icon || 'mdi-chevron-right'} mdi" style="font-size:18px;color:#4f8ef7;"></i></span>
                    <span style="flex:1;"><span style="display:block;font-size:15px;font-weight:500;color:#fff;">${r.title}</span><span style="display:block;font-size:12px;color:rgba(255,255,255,.4);margin-top:2px;">${r.category}</span></span>
                    <i class="mdi mdi-arrow-right" style="font-size:16px;color:rgba(255,255,255,.25);transition:transform .2s;"></i>
                `;
                li.addEventListener('click', () => {
                    addToHistory(spotlightInput ? spotlightInput.value : '', r);
                    window.location.href = r.url;
                });
                li.addEventListener('mouseenter', () => { li.style.background = 'rgba(79,142,247,.12)'; activeIndex = idx; });
                li.addEventListener('mouseleave', () => { li.style.background = ''; });
                spotlightList.appendChild(li);
                idx++;
            });
        });
    }

    function performSearch(query) {
        if (!query || !query.trim()) { showEmpty(); return; }
        showLoading();
        const results = searchStatic(query);
        renderResults(results);

        if (searchTimeout) clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            // Simulate API search (replace with actual endpoint)
            if (query.length >= 2) {
                // You can add API call here: fetch('/api/search?q=' + encodeURIComponent(query))
            }
        }, 300);
    }

    // Spotlight event listeners
    if (spotlightTrigger) spotlightTrigger.addEventListener('click', openSpotlight);
    if (mobileTrigger) mobileTrigger.addEventListener('click', openSpotlight);
    if (escBtn) escBtn.addEventListener('click', closeSpotlight);
    if (clearHistoryBtn) clearHistoryBtn.addEventListener('click', () => { localStorage.removeItem(HISTORY_KEY); });
    if (spotlightOverlay) spotlightOverlay.addEventListener('click', (e) => { if (e.target === spotlightOverlay) closeSpotlight(); });

    if (spotlightInput) {
        spotlightInput.addEventListener('input', (e) => performSearch(e.target.value));
        spotlightInput.addEventListener('keydown', (e) => {
            const items = spotlightList.querySelectorAll('.spotlight-result-item');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, items.length - 1);
                items.forEach((item, i) => {
                    if (i === activeIndex) {
                        item.style.background = 'rgba(79,142,247,.18)';
                        item.scrollIntoView({ block: 'nearest' });
                    } else {
                        item.style.background = '';
                    }
                });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                items.forEach((item, i) => {
                    if (i === activeIndex) {
                        item.style.background = 'rgba(79,142,247,.18)';
                        item.scrollIntoView({ block: 'nearest' });
                    } else {
                        item.style.background = '';
                    }
                });
            } else if (e.key === 'Enter' && activeIndex >= 0 && currentResults[activeIndex]) {
                const result = currentResults[activeIndex];
                addToHistory(spotlightInput.value, result);
                window.location.href = result.url;
            }
        });
    }

    document.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            if (spotlightOverlay && spotlightOverlay.style.display === 'flex') closeSpotlight();
            else openSpotlight();
        }
    });

    // =====================================================
    // THEME TOGGLE
    // =====================================================
    const themeBtn = document.getElementById('theme-toggle-btn');
    const themeDropdown = document.getElementById('theme-dropdown');
    if (themeBtn && themeDropdown) {
        themeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            themeDropdown.style.display = themeDropdown.style.display === 'block' ? 'none' : 'block';
        });
        document.addEventListener('click', (e) => {
            if (!themeBtn.contains(e.target) && !themeDropdown.contains(e.target)) {
                themeDropdown.style.display = 'none';
            }
        });
    }

    function applyTheme(mode) {
        const html = document.documentElement;
        const scheme = mode === 'auto' ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') : mode;
        html.setAttribute('data-bs-theme', scheme);
        html.setAttribute('data-topbar', scheme === 'dark' ? 'dark' : 'light');
        localStorage.setItem('app-theme', mode);
        const icon = document.getElementById('theme-icon');
        if (icon) icon.className = mode === 'light' ? 'bi bi-sun align-middle fs-3xl' : (mode === 'dark' ? 'bi bi-moon align-middle fs-3xl' : 'bi bi-moon-stars align-middle fs-3xl');
    }

    applyTheme(localStorage.getItem('app-theme') || 'light');
    document.querySelectorAll('.theme-mode-item').forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            applyTheme(item.getAttribute('data-mode'));
            if (themeDropdown) themeDropdown.style.display = 'none';
        });
    });

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if (localStorage.getItem('app-theme') === 'auto') applyTheme('auto');
    });

    // =====================================================
    // BACK TO TOP
    // =====================================================
    const backToTop = document.getElementById('back-to-top');
    if (backToTop) {
        window.addEventListener('scroll', () => {
            backToTop.classList.toggle('show', window.scrollY > 300);
        });
        backToTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    }

    // =====================================================
    // USER DROPDOWN
    // =====================================================
    const userBtn = document.getElementById('user-menu-btn');
    const userDropdown = document.getElementById('user-dropdown');
    if (userBtn && userDropdown) {
        userBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.style.display = userDropdown.style.display === 'block' ? 'none' : 'block';
        });
        document.addEventListener('click', (e) => {
            if (!userBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.style.display = 'none';
            }
        });

        // Populate user dropdown
        userDropdown.innerHTML = `
            <div class="dropdown-header"><h6 class="mb-0">Welcome back!</h6><small class="text-muted">{{ $userdata->name }}</small></div>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="{{ route('users.overview', $userdata->id) }}"><i class="mdi mdi-account-circle me-2"></i>My Profile</a>
            <a class="dropdown-item" href="{{ route('profile.settings', ['id' => $userdata->id]) }}"><i class="mdi mdi-cog me-2"></i>Account Settings</a>
            <div class="dropdown-divider"></div>
            <form method="POST" action="{{ route('logout') }}" id="topbar-logout-form">@csrf<a class="dropdown-item text-danger" href="#" onclick="document.getElementById('topbar-logout-form').submit();"><i class="mdi mdi-logout me-2"></i>Logout</a></form>
        `;
    }
})();
</script>

</body>
</html>
