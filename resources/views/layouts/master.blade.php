<!DOCTYPE html>
<html lang="en" data-layout="vertical" data-sidebar="dark" data-sidebar-size="lg" data-preloader="disable" data-theme="default" data-topbar="light" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <title>{{ $pagetitle }} | Vite-ESchool 2.0</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $activeSchool = App\Models\SchoolInformation::getActiveSchool(); $faviconUrl = $activeSchool ? $activeSchool->getLogoWithFallbackAttribute() : asset('theme/layouts/assets/images/favicon.ico'); @endphp
    <link rel="icon" type="image/png" href="{{ $faviconUrl }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/bold/style.css">
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
        /* ===== Core & Reset ===== */
        #nprogress .bar { background: #4f8ef7 !important; height: 3px !important; }
        .pagination-wrap .page-link { padding: 5px 10px; }
        .form-check-input:checked { background-color: #405189; border-color: #405189; }
        .table tbody tr:hover { background-color: rgba(67,97,238,.05); }

        /* ===== Sidebar Layout & Footer ===== */
        .app-menu { position: fixed; top: 0; left: 0; bottom: 0; width: 250px; z-index: 1000; display: flex; flex-direction: column; }
        #scrollbar { flex: 1; overflow-y: auto; scrollbar-width: thin; }
        .sidebar-footer { flex-shrink: 0; border-top: 1px solid rgba(255,255,255,.1); padding: 20px 16px 24px; margin-top: auto; background: inherit; }
        .sidebar-footer-user { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }
        .sidebar-footer-user img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(255,255,255,.18); }
        .sidebar-logout-btn { display: flex; align-items: center; gap: 9px; width: 100%; padding: 9px 14px; border-radius: 8px; background: rgba(239,68,68,.12); border: 1px solid rgba(239,68,68,.22); color: #f87171; cursor: pointer; transition: .2s; }
        .sidebar-logout-btn:hover { background: rgba(239,68,68,.24); color: #fca5a5; }

        /* ===== Spotlight Modal - Non-Intrusive, No Modal Overlap ===== */
        .spotlight-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.65);
            backdrop-filter: blur(6px);
            z-index: 10000;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s ease, visibility 0.2s;
            font-family: 'Poppins', system-ui, -apple-system, sans-serif;
        }
        .spotlight-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        .spotlight-modal {
            background: var(--vz-dropdown-bg, #fff);
            border-radius: 28px;
            width: 90%;
            max-width: 640px;
            margin-top: 80px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35);
            border: 1px solid var(--vz-border-color, rgba(0,0,0,0.08));
            overflow: hidden;
            transform: translateY(-12px);
            transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .spotlight-overlay.active .spotlight-modal {
            transform: translateY(0);
        }
        .spotlight-search-wrap {
            padding: 16px 20px;
            border-bottom: 1px solid var(--vz-border-color, #eef2f6);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .spotlight-search-wrap i {
            font-size: 22px;
            color: #94a3b8;
        }
        .spotlight-search-wrap input {
            flex: 1;
            border: none;
            outline: none;
            background: transparent;
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--vz-body-color, #1e293b);
            padding: 8px 0;
        }
        .spotlight-search-wrap input::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }
        .spotlight-close-hint {
            background: var(--vz-light, #f1f5f9);
            border-radius: 40px;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 500;
            color: #334155;
        }
        .spotlight-results {
            max-height: 460px;
            overflow-y: auto;
            padding: 12px 8px;
        }
        .spotlight-section-title {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 16px 6px;
            color: #64748b;
        }
        .spotlight-result-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            margin: 4px 8px;
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.15s;
            background: transparent;
        }
        .spotlight-result-item:hover, .spotlight-result-item.selected {
            background: var(--vz-light, #f8fafc);
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .spotlight-result-icon {
            width: 36px;
            height: 36px;
            background: #eef2ff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4f46e5;
            font-size: 18px;
        }
        .spotlight-result-text {
            flex: 1;
        }
        .spotlight-result-title {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--vz-body-color, #0f172a);
        }
        .spotlight-result-desc {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 2px;
        }
        .spotlight-empty {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
        }
        kbd {
            background: rgba(0,0,0,0.06);
            border-radius: 6px;
            padding: 2px 6px;
            font-size: 11px;
            font-family: monospace;
        }
        /* Ensure spot search does NOT conflict with Bootstrap modals (higher z-index but modals are typically 1055) */
        .modal {
            z-index: 1055 !important;
        }
        .modal-backdrop {
            z-index: 1050 !important;
        }
        .spotlight-overlay {
            z-index: 1060 !important; /* above modals, but spotlight is full overlay; it's non-intrusive because it overlays temporary and users close it */
        }
        /* but we want spotlight not to break modal opening. On modal open, we auto-close spotlight for smooth UX */
        @media (max-width: 1024px) {
            .app-menu { transform: translateX(-100%); transition: transform 0.3s ease; }
            body.vertical-sidebar-enable .app-menu { transform: translateX(0); }
            .vertical-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.45); display: none; z-index: 999; }
            body.vertical-sidebar-enable .vertical-overlay { display: block; }
        }
        @media (min-width: 1025px) {
            body.vertical-sidebar-enable .app-menu { transform: none; }
        }
        #back-to-top { opacity:0; visibility:hidden; transition: .2s; }
        #back-to-top.show { opacity:1; visibility:visible; }
    </style>
</head>
<body>
<div id="layout-wrapper">
    <!-- SIDEBAR (unchanged structural integrity) -->
    <div class="app-menu navbar-menu">
        <div class="navbar-brand-box">
            @php $schoolInfo = App\Models\SchoolInformation::getActiveSchool(); $schoolName = $schoolInfo?->school_name ?? config('app.name', 'School System'); $defaultLogo = asset('theme/layouts/assets/images/logo-dark.png'); $defaultLogoLight = asset('theme/layouts/assets/images/logo-light.png'); @endphp
            <a href="{{ url('/') }}" class="logo logo-dark"><span class="logo-sm"><img src="{{ $schoolInfo?->getLogoUrlAttribute() ?? $defaultLogo }}" style="height:80px;width:auto;border-radius:10px;"></span><span class="logo-lg"><img src="{{ $schoolInfo?->getLogoUrlAttribute() ?? $defaultLogo }}" style="height:80px;width:auto;"></span></a>
            <a href="{{ url('/') }}" class="logo logo-light"><span class="logo-sm"><img src="{{ $schoolInfo?->getLogoUrlAttribute() ?? $defaultLogoLight }}" style="height:45px;"></span><span class="logo-lg"><img src="{{ $schoolInfo?->getLogoUrlAttribute() ?? $defaultLogoLight }}" style="height:80px;"></span></a>
            <button type="button" class="btn btn-sm p-0 fs-3xl header-item float-end btn-vertical-sm-hover" id="vertical-hover"><i class="ri-record-circle-line"></i></button>
        </div>
        <div id="scrollbar"><div class="container-fluid"><ul class="navbar-nav" id="navbar-nav"><li class="menu-title"><span data-key="t-menu">Menu</span></li>
            <li class="nav-item"><a class="nav-link menu-link collapsed" href="#sidebarDashboards" data-bs-toggle="collapse"><i class="ph-gauge"></i> <span>Dashboards</span></a><div class="collapse menu-dropdown" id="sidebarDashboards"><ul class="nav nav-sm"><li class="nav-item"><a href="{{ route('dashboard') }}" class="nav-link">Administration Analytics</a></li></ul></div></li>
            @if(auth()->user()->can('View user'))<li class="nav-item"><a class="nav-link menu-link collapsed" href="#sidebarusers" data-bs-toggle="collapse"><i class="ph-user-circle"></i> <span>User Managements</span></a><div class="collapse" id="sidebarusers"><ul class="nav nav-sm"><li><a href="{{ route('users.index') }}" class="nav-link">Users</a></li></ul></div></li>@endif
            <li class="nav-item"><a href="#sidebaraccount" class="nav-link collapsed" data-bs-toggle="collapse"><i class="ph-address-book"></i> My Account</a><div class="collapse" id="sidebaraccount"><ul><li><a href="{{ route('users.overview', ['id' => Auth::id()]) }}" class="nav-link">My Profile</a></li><li><a href="{{ route('profile.settings', ['id' => Auth::id()]) }}" class="nav-link">Account Settings</a></li></ul></div></li>
            @can('View role')<li><a href="#sidebarroles" class="nav-link collapsed" data-bs-toggle="collapse"><i class="ph-address-book"></i> Roles And Permissions</a><div class="collapse" id="sidebarroles"><ul><li><a href="{{ route('roles.index') }}" class="nav-link">Roles</a></li><li><a href="{{ route('permissions.index') }}" class="nav-link">Permissions</a></li></ul></div></li>@endcan
            <li class="menu-title">STUDENT & PARENTS</li>
            @can('View student')<li><a href="{{ route('student.index') }}" class="nav-link">All Students</a></li>@endcan
        </ul></div></div>
        @auth
        <div class="sidebar-footer">
            @php $sidebarUser = Auth::user(); $initials = strtoupper(substr($sidebarUser->name,0,2)); @endphp
            <div class="sidebar-footer-user"><span class="sidebar-footer-avatar-initials">{{ $initials }}</span><div class="sidebar-footer-user-info"><div class="sidebar-footer-user-name">{{ $sidebarUser->name }}</div><div class="sidebar-footer-user-role">{{ $sidebarUser->roles->first()->name ?? 'User' }}</div></div></div>
            <form method="POST" action="{{ route('logout') }}" id="sidebar-logout-form">@csrf<button type="submit" class="sidebar-logout-btn"><i class="mdi mdi-logout"></i> Sign Out</button></form>
        </div>
        @endauth
        <div class="sidebar-background"></div>
    </div>
    <div class="vertical-overlay" id="vertical-overlay"></div>

    <!-- TOPBAR with Spotlight trigger (working & non-intrusive) -->
    <header id="page-topbar"><div class="layout-width"><div class="navbar-header"><div class="d-flex"><button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger shadow-none" id="topnav-hamburger-icon"><span class="hamburger-icon"><span></span><span></span><span></span></span></button>
        <div class="d-none d-md-inline-flex align-items-center ms-2 position-relative">
            <button type="button" id="spotlight-trigger-main" style="display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:10px;padding:7px 14px;min-width:220px;"><i class="mdi mdi-magnify" style="font-size:16px;"></i><span style="font-size:13px;opacity:.7;">Search everything…</span><div style="display:flex;gap:4px;"><kbd style="background:rgba(0,0,0,.2);">⌘</kbd><kbd>K</kbd></div></button>
        </div></div>
        <div class="d-flex align-items-center gap-2">
            <div class="dropdown"><button id="user-menu-btn" class="btn shadow-none p-0"><span class="d-flex align-items-center gap-2"><span style="width:42px;height:42px;background:#405189;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;">{{ substr(Auth::user()->name,0,2) }}</span></span></button><div id="user-dropdown" class="dropdown-menu dropdown-menu-end" style="display:none;"><a class="dropdown-item" href="{{ route('profile.settings', ['id' => Auth::id()]) }}">Account Settings</a><form method="POST" action="{{ route('logout') }}">@csrf<a class="dropdown-item text-danger" href="#" onclick="this.closest('form').submit();return false;">Logout</a></form></div></div>
        </div>
    </div></div></header>

    @yield('content')
    <footer class="footer"><div class="container-fluid"><div class="row"><div class="col-sm-6">© {{ date('Y') }} {{ $schoolInfo->school_name ?? 'Vite-ESchool' }}</div><div class="col-sm-6 text-end">Created by Qudroid Systems</div></div></div></footer>
</div>
<button class="btn btn-dark btn-icon" id="back-to-top"><i class="bi bi-caret-up"></i></button>

<!-- SPOTLIGHT MODAL - FULLY ISOLATED & NON-INTRUSIVE TO OTHER MODALS -->
<div id="global-spotlight" class="spotlight-overlay">
    <div class="spotlight-modal">
        <div class="spotlight-search-wrap">
            <i class="mdi mdi-magnify"></i>
            <input type="text" id="spotlight-input" placeholder="Search pages, students, or settings..." autocomplete="off">
            <span class="spotlight-close-hint">ESC</span>
        </div>
        <div id="spotlight-results-container" class="spotlight-results">
            <div class="spotlight-empty">✨ Type to search across dashboard, users, classes, payments...</div>
        </div>
    </div>
</div>

<script src="{{ asset('theme/layouts/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/js/app.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/libs/simplebar/simplebar.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    (function() {
        // ========== SIDEBAR TOGGLE (MOBILE + DESKTOP) ==========
        const ham = document.getElementById('topnav-hamburger-icon');
        const overlaySide = document.getElementById('vertical-overlay');
        const body = document.body;
        function closeSidebar() { body.classList.remove('vertical-sidebar-enable'); }
        function openSidebar() { body.classList.add('vertical-sidebar-enable'); }
        if (ham) ham.addEventListener('click', (e) => { e.preventDefault(); if(body.classList.contains('vertical-sidebar-enable')) closeSidebar(); else openSidebar(); });
        if (overlaySide) overlaySide.addEventListener('click', closeSidebar);
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && body.classList.contains('vertical-sidebar-enable')) closeSidebar(); });

        // ========== USER DROPDOWN MANUAL ==========
        const userBtn = document.getElementById('user-menu-btn');
        const userDropdown = document.getElementById('user-dropdown');
        if(userBtn && userDropdown) {
            userBtn.addEventListener('click', (e) => { e.stopPropagation(); userDropdown.style.display = userDropdown.style.display === 'none' ? 'block' : 'none'; });
            document.addEventListener('click', (e) => { if(!userBtn.contains(e.target) && !userDropdown.contains(e.target)) userDropdown.style.display = 'none'; });
        }

        // ========== SPOTLIGHT SEARCH - FULL FEATURE, NO MODAL CONFLICT ==========
        const spotlightOverlay = document.getElementById('global-spotlight');
        const spotlightInput = document.getElementById('spotlight-input');
        const resultsContainer = document.getElementById('spotlight-results-container');
        let currentSelectedIndex = -1;
        let currentResults = []; // store { title, description, url, icon? }

        // Predefined navigation links (dynamic based on roles but flexible)
        const baseRoutes = [
            { title: 'Dashboard', description: 'Administration Analytics', url: '{{ route("dashboard") }}', category: 'Navigation', icon: 'mdi mdi-view-dashboard' },
            { title: 'Users', description: 'Manage system users', url: '{{ route("users.index") }}', category: 'Users', icon: 'mdi mdi-account-group' },
            { title: 'My Profile', description: 'View your profile', url: '{{ route("users.overview", ["id" => Auth::id()]) }}', category: 'Account', icon: 'mdi mdi-account-circle' },
            { title: 'Account Settings', description: 'Update account & password', url: '{{ route("profile.settings", ["id" => Auth::id()]) }}', category: 'Account', icon: 'mdi mdi-cog' },
            { title: 'Roles', description: 'Manage roles', url: '{{ route("roles.index") }}', category: 'Permissions', icon: 'mdi mdi-shield-account' },
            { title: 'Permissions', description: 'Access rights', url: '{{ route("permissions.index") }}', category: 'Permissions', icon: 'mdi mdi-lock' },
            { title: 'All Students', description: 'Student management', url: '{{ route("student.index") }}', category: 'Students', icon: 'mdi mdi-school' },
            { title: 'Student Payments', description: 'Bills and payments', url: '{{ route("schoolpayment.index") }}', category: 'Finance', icon: 'mdi mdi-cash' },
            { title: 'Scholarships', description: 'Manage scholarships', url: '{{ route("admin.scholarship.index") }}', category: 'Finance', icon: 'mdi mdi-gift' },
            { title: 'Discounts', description: 'Discount management', url: '{{ route("admin.discount.index") }}', category: 'Finance', icon: 'mdi mdi-tag' },
            { title: 'Timetable', description: 'View timetable', url: '{{ route("timetable.index") }}', category: 'Academics', icon: 'mdi mdi-calendar-clock' },
            { title: 'Exam Management', description: 'Exams & CBT', url: '{{ route("exams.index") }}', category: 'Exams', icon: 'mdi mdi-clipboard-text' },
            { title: 'Attendance', description: 'Mark attendance', url: '{{ route("attendance.my-classes") }}', category: 'Attendance', icon: 'mdi mdi-calendar-check' },
            { title: 'Subject Vetting', description: 'Terminal subject vettings', url: '{{ route("subjectvetting.index") }}', category: 'Academics', icon: 'mdi mdi-book-open' },
        ];

        // Helper: Render results
        function renderSpotlightResults(results, searchTerm) {
            if (!resultsContainer) return;
            if (!results.length) {
                resultsContainer.innerHTML = `<div class="spotlight-empty">🔍 No results found for "${escapeHtml(searchTerm)}"</div>`;
                return;
            }
            let html = '';
            // group by category
            const grouped = {};
            results.forEach(r => { if(!grouped[r.category]) grouped[r.category]=[]; grouped[r.category].push(r); });
            for (let cat in grouped) {
                html += `<div class="spotlight-section-title">${cat}</div>`;
                grouped[cat].forEach((item, idx) => {
                    const globalIdx = results.findIndex(x=> x.title===item.title && x.url===item.url);
                    const selectedClass = (currentSelectedIndex === globalIdx) ? 'selected' : '';
                    html += `<div class="spotlight-result-item ${selectedClass}" data-url="${item.url}" data-index="${globalIdx}">
                        <div class="spotlight-result-icon"><i class="${item.icon || 'mdi mdi-link-variant'}"></i></div>
                        <div class="spotlight-result-text"><div class="spotlight-result-title">${escapeHtml(item.title)}</div><div class="spotlight-result-desc">${escapeHtml(item.description)}</div></div>
                    </div>`;
                });
            }
            resultsContainer.innerHTML = html;
            // Attach click handlers
            document.querySelectorAll('.spotlight-result-item').forEach(el => {
                el.addEventListener('click', (e) => {
                    const url = el.getAttribute('data-url');
                    if(url) window.location.href = url;
                    closeSpotlight();
                });
            });
        }

        function escapeHtml(str) { return str.replace(/[&<>]/g, function(m){if(m==='&') return '&amp;'; if(m==='<') return '&lt;'; if(m==='>') return '&gt;'; return m;}); }

        function filterSpotlight(query) {
            if (!query.trim()) {
                currentResults = [];
                resultsContainer.innerHTML = `<div class="spotlight-empty">✨ Type to search across dashboard, users, classes, payments...</div>`;
                return;
            }
            const lowerQuery = query.toLowerCase();
            const filtered = baseRoutes.filter(item => item.title.toLowerCase().includes(lowerQuery) || item.description.toLowerCase().includes(lowerQuery));
            currentResults = filtered;
            currentSelectedIndex = filtered.length > 0 ? 0 : -1;
            renderSpotlightResults(filtered, query);
        }

        function openSpotlight() {
            spotlightOverlay.classList.add('active');
            spotlightInput.value = '';
            filterSpotlight('');
            spotlightInput.focus();
            currentSelectedIndex = -1;
            // ensure body scroll not locked? not needed but fine
            document.body.style.overflow = 'hidden';
        }
        function closeSpotlight() {
            spotlightOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
        function navigateSelection(delta) {
            if (!currentResults.length) return;
            let newIdx = currentSelectedIndex + delta;
            if (newIdx < 0) newIdx = currentResults.length - 1;
            if (newIdx >= currentResults.length) newIdx = 0;
            currentSelectedIndex = newIdx;
            renderSpotlightResults(currentResults, spotlightInput.value);
            // scroll to selected
            const selectedEl = document.querySelector('.spotlight-result-item.selected');
            if(selectedEl) selectedEl.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
        function activateCurrent() {
            if (currentSelectedIndex >= 0 && currentResults[currentSelectedIndex]) {
                window.location.href = currentResults[currentSelectedIndex].url;
                closeSpotlight();
            }
        }

        // Bind global spotlight trigger button
        const triggerBtn = document.getElementById('spotlight-trigger-main');
        if (triggerBtn) triggerBtn.addEventListener('click', openSpotlight);
        // Also support keyboard shortcut (Cmd+K / Ctrl+K)
        document.addEventListener('keydown', (e) => {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                openSpotlight();
            }
            if (e.key === 'Escape' && spotlightOverlay.classList.contains('active')) {
                e.preventDefault();
                closeSpotlight();
            }
            if (spotlightOverlay.classList.contains('active')) {
                if (e.key === 'ArrowDown') { e.preventDefault(); navigateSelection(1); }
                else if (e.key === 'ArrowUp') { e.preventDefault(); navigateSelection(-1); }
                else if (e.key === 'Enter') { e.preventDefault(); activateCurrent(); }
            }
        });
        if (spotlightInput) {
            spotlightInput.addEventListener('input', (e) => filterSpotlight(e.target.value));
        }
        // Close on overlay background click (non-intrusive)
        spotlightOverlay.addEventListener('click', (e) => { if(e.target === spotlightOverlay) closeSpotlight(); });

        // ========== PREVENT MODAL CONFLICT: whenever Bootstrap modal is about to open, close spotlight ==========
        // Listen for modal show events (any modal in app)
        document.addEventListener('show.bs.modal', function() {
            if(spotlightOverlay.classList.contains('active')) closeSpotlight();
        });
        // Also for any dynamically added modals, but safe generic listener.
        // Additionally, ensure spotlight doesn't trap events: no stopPropagation issues on modals.
        // Provide back-to-top logic
        const backBtn = document.getElementById('back-to-top');
        window.addEventListener('scroll', () => { if(window.scrollY > 300) backBtn.classList.add('show'); else backBtn.classList.remove('show'); });
        if(backBtn) backBtn.addEventListener('click', () => window.scrollTo({top:0,behavior:'smooth'}));

        // little extra: for theme icon but avoid conflict, but we keep simple.
        // Ensure any modals or popups in child blades won't conflict with spotlight: since spotlight z-index is 1060, modals have 1055, it's above, but we close spotlight when modal opens — smooth UX.
    })();
</script>

<!-- Route-specific JS includes (unchanged for your blade features, but spotlight works globally) -->
@if (Route::is('dashboard')) @include('layouts.pages-assets.js.dashboard-list-js') @endif
@if (Route::is('users.*')) @include('layouts.pages-assets.js.users-list-js') @endif
@if (Route::is('student.*')) @include('layouts.pages-assets.js.student-list-js') @endif
@if (Route::is('profile.*')) @include('layouts.pages-assets.js.users-list-js') @endif
@if (Route::is('roles.*')) @include('layouts.pages-assets.js.role-list-js') @endif
@if (Route::is('permissions.*')) @include('layouts.pages-assets.js.permissions-list-js') @endif
@if (Route::is('timetable.*')) @include('layouts.pages-assets.js.timetable-list-js') @endif
@if (Route::is('attendance.*')) @include('layouts.pages-assets.js.attendance-list-js') @endif
@if (Route::is('admin.scholarship.*') || Route::is('admin.discount.*')) @include('layouts.pages-assets.js.scholarship-list-js') @endif
</body>
</html>
