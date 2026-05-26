@extends('layouts.master')
@section('content')
<style>
/* =====================================================
   SCHOOL ANALYTICS DASHBOARD — Enhanced v2
   ===================================================== */

@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Space+Grotesk:wght@400;500;600;700&display=swap');

:root {
    --dash-radius: 16px;
    --dash-radius-sm: 10px;
    --dash-transition: 0.25s cubic-bezier(0.4,0,0.2,1);
    --dash-shadow: 0 2px 12px rgba(0,0,0,0.06);
    --dash-shadow-hover: 0 8px 28px rgba(0,0,0,0.12);
}

/* === Keyframes === */
@keyframes fadeUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
@keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
@keyframes scaleIn { from { opacity:0; transform:scale(0.96); } to { opacity:1; transform:scale(1); } }
@keyframes countUp { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
@keyframes pulseRing {
    0% { box-shadow: 0 0 0 0 rgba(79,142,247,0.35); }
    70% { box-shadow: 0 0 0 8px rgba(79,142,247,0); }
    100% { box-shadow: 0 0 0 0 rgba(79,142,247,0); }
}
@keyframes barGrow { from { width:0; } to { width:var(--bar-w); } }
@keyframes shimmerSlide {
    0% { background-position: -400px 0; }
    100% { background-position: 400px 0; }
}
@keyframes spinLoader {
    to { transform: rotate(360deg); }
}
@keyframes dotBounce {
    0%,80%,100% { transform:scale(0); }
    40% { transform:scale(1); }
}
@keyframes modalSlideIn {
    from { opacity:0; transform:translateY(24px) scale(0.97); }
    to   { opacity:1; transform:translateY(0) scale(1); }
}
@keyframes overlayFadeIn {
    from { opacity:0; } to { opacity:1; }
}

/* === Base === */
.dash-page { font-family: 'DM Sans', sans-serif; }
.dash-title { font-family: 'Space Grotesk', sans-serif; }

/* === Live indicator === */
.live-dot {
    display:inline-flex; align-items:center; gap:7px;
    font-size:12px; font-weight:500; color:#64748b;
}
.live-dot::before {
    content:''; width:8px; height:8px; border-radius:50%;
    background:#10b981; animation:pulseRing 2s infinite;
    flex-shrink:0;
}

/* === Stat Cards === */
.stat-card {
    background:#fff; border:1px solid #f1f5f9;
    border-radius:var(--dash-radius); padding:20px;
    transition: all var(--dash-transition);
    animation: fadeUp 0.5s ease both;
    cursor:pointer; position:relative; overflow:hidden;
}
.stat-card::before {
    content:''; position:absolute; inset:0;
    background:linear-gradient(135deg,transparent 60%,rgba(255,255,255,0.6));
    pointer-events:none;
}
.stat-card:hover {
    transform:translateY(-4px);
    box-shadow:var(--dash-shadow-hover);
    border-color:#e2e8f0;
}
.stat-card:hover .stat-icon-wrap { transform:scale(1.08) rotate(4deg); }
.stat-card:nth-child(1){ animation-delay:.00s; }
.stat-card:nth-child(2){ animation-delay:.06s; }
.stat-card:nth-child(3){ animation-delay:.12s; }
.stat-card:nth-child(4){ animation-delay:.18s; }
.stat-card:nth-child(5){ animation-delay:.24s; }
.stat-card:nth-child(6){ animation-delay:.30s; }
.stat-card:nth-child(7){ animation-delay:.36s; }
.stat-card:nth-child(8){ animation-delay:.42s; }

.stat-icon-wrap {
    width:52px; height:52px; border-radius:14px;
    display:flex; align-items:center; justify-content:center;
    font-size:22px; transition:transform var(--dash-transition);
    flex-shrink:0;
}
.stat-label { font-size:11px; font-weight:600; letter-spacing:.6px; text-transform:uppercase; color:#94a3b8; }
.stat-value { font-family:'Space Grotesk',sans-serif; font-size:28px; font-weight:700; color:#0f172a; line-height:1; margin:4px 0; }
.stat-sub { font-size:12px; color:#64748b; display:flex; align-items:center; gap:5px; }
.stat-badge { display:inline-flex; align-items:center; gap:3px; padding:2px 7px; border-radius:20px; font-size:11px; font-weight:600; }
.stat-badge.up { background:#dcfce7; color:#16a34a; }
.stat-badge.down { background:#fee2e2; color:#dc2626; }
.stat-badge.neu { background:#f1f5f9; color:#475569; }

.stat-bar { height:4px; border-radius:4px; background:#f1f5f9; margin-top:14px; overflow:hidden; }
.stat-bar-fill { height:100%; border-radius:4px; animation:barGrow .9s ease both; animation-delay:.4s; }

/* Avatars strip on card */
.avatar-strip { display:flex; margin-top:10px; }
.avatar-strip .av {
    width:24px; height:24px; border-radius:50%; border:2px solid #fff;
    object-fit:cover; margin-left:-6px; background:#e2e8f0;
    font-size:9px; font-weight:700; color:#64748b;
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0;
}
.avatar-strip .av:first-child { margin-left:0; }
.avatar-strip .av-more {
    background:#e2e8f0; color:#475569;
    font-size:9px; font-weight:600;
}

/* More link */
.card-more-link {
    font-size:11px; font-weight:600; color:#6366f1;
    text-decoration:none; display:inline-flex; align-items:center; gap:3px;
    transition:gap var(--dash-transition);
}
.card-more-link:hover { gap:6px; }

/* === Section Cards (Charts/Tables) === */
.section-card {
    background:#fff; border:1px solid #f1f5f9;
    border-radius:var(--dash-radius); overflow:hidden;
    animation:scaleIn .5s ease both;
    transition:box-shadow var(--dash-transition);
}
.section-card:hover { box-shadow:var(--dash-shadow-hover); }
.section-card-header {
    padding:18px 20px 14px;
    border-bottom:1px solid #f8fafc;
    display:flex; align-items:center; justify-content:between;
}
.section-card-title { font-family:'Space Grotesk',sans-serif; font-size:15px; font-weight:600; color:#0f172a; }
.section-card-sub { font-size:12px; color:#94a3b8; }
.section-card-body { padding:16px 20px 20px; }

/* === Table styles === */
.dash-table { width:100%; border-collapse:collapse; font-size:13px; }
.dash-table thead th {
    padding:8px 10px; font-size:10.5px; font-weight:600;
    letter-spacing:.5px; text-transform:uppercase; color:#94a3b8;
    border-bottom:1px solid #f1f5f9; white-space:nowrap;
}
.dash-table tbody tr { transition:background var(--dash-transition); }
.dash-table tbody tr:hover { background:#f8fafc; }
.dash-table td { padding:10px 10px; border-bottom:1px solid #f8fafc; color:#334155; vertical-align:middle; }
.dash-table tbody tr:last-child td { border-bottom:none; }

/* Score pill */
.score-pill {
    display:inline-flex; align-items:center; justify-content:center;
    padding:2px 9px; border-radius:20px; font-size:12px; font-weight:600; min-width:44px;
}
.score-A { background:#dcfce7; color:#16a34a; }
.score-B { background:#dbeafe; color:#2563eb; }
.score-C { background:#fef9c3; color:#ca8a04; }
.score-D { background:#fee2e2; color:#dc2626; }
.score-F { background:#fce7f3; color:#be185d; }

/* Mini progress in table */
.mini-prog { height:5px; border-radius:5px; background:#f1f5f9; overflow:hidden; min-width:60px; }
.mini-prog-fill { height:100%; border-radius:5px; transition:width .6s ease; }

/* Student avatar in table */
.stud-av {
    width:32px; height:32px; border-radius:50%; object-fit:cover;
    border:2px solid #e2e8f0; background:#e2e8f0;
    font-size:11px; font-weight:700; color:#64748b;
    display:inline-flex; align-items:center; justify-content:center;
    flex-shrink:0;
}

/* Rank badge */
.rank-badge {
    width:22px; height:22px; border-radius:50%; display:inline-flex;
    align-items:center; justify-content:center; font-size:11px; font-weight:700;
}
.rank-1 { background:#fef3c7; color:#d97706; }
.rank-2 { background:#e2e8f0; color:#64748b; }
.rank-3 { background:#fce7f3; color:#c026d3; }
.rank-n { background:#f8fafc; color:#94a3b8; }

/* Subject teacher pill */
.teacher-chip {
    display:inline-flex; align-items:center; gap:5px;
    background:#f8fafc; border:1px solid #e2e8f0;
    border-radius:20px; padding:3px 8px 3px 5px; font-size:11px;
}
.teacher-chip-av {
    width:18px; height:18px; border-radius:50%; object-fit:cover;
    background:#e2e8f0; font-size:8px; font-weight:700; color:#64748b;
    display:inline-flex; align-items:center; justify-content:center;
}

/* === Chart section === */
.chart-wrap { position:relative; width:100%; }

/* === Activity timeline === */
.timeline-item {
    display:flex; gap:12px; padding:10px 0;
    animation:fadeUp .4s ease both;
    border-bottom:1px solid #f8fafc;
}
.timeline-item:last-child { border-bottom:none; }
.timeline-dot {
    width:36px; height:36px; border-radius:10px;
    display:flex; align-items:center; justify-content:center;
    font-size:15px; flex-shrink:0;
}
.timeline-item:nth-child(1){ animation-delay:.05s; }
.timeline-item:nth-child(2){ animation-delay:.10s; }
.timeline-item:nth-child(3){ animation-delay:.15s; }
.timeline-item:nth-child(4){ animation-delay:.20s; }
.timeline-item:nth-child(5){ animation-delay:.25s; }

/* === Quick Actions === */
.qa-btn {
    display:flex; flex-direction:column; align-items:center; gap:8px;
    padding:16px 12px; border:1px solid #f1f5f9;
    border-radius:var(--dash-radius-sm); text-decoration:none;
    transition:all var(--dash-transition); background:#fff; text-align:center;
}
.qa-btn:hover { background:#f8fafc; border-color:#e2e8f0; transform:translateY(-2px); }
.qa-btn i { font-size:24px; }
.qa-btn span { font-size:12px; font-weight:500; color:#475569; }

/* Scholarship overview pill */
.scholarship-bar {
    height:8px; border-radius:8px; overflow:hidden;
    background:#f1f5f9; display:flex;
}

/* === MODAL SYSTEM === */
.dash-modal-overlay {
    position:fixed; inset:0; background:rgba(15,23,42,0.55);
    z-index:9000; display:flex; align-items:center; justify-content:center;
    padding:16px; opacity:0; pointer-events:none;
    transition:opacity .2s ease;
    backdrop-filter: blur(2px);
}
.dash-modal-overlay.open { opacity:1; pointer-events:all; animation:overlayFadeIn .2s ease; }
.dash-modal {
    background:#fff; border-radius:20px; width:100%; max-width:860px;
    max-height:88vh; display:flex; flex-direction:column;
    overflow:hidden; transform:translateY(20px) scale(0.97); opacity:0;
    transition:all .22s cubic-bezier(0.4,0,0.2,1);
    box-shadow:0 20px 60px rgba(0,0,0,0.18);
}
.dash-modal-overlay.open .dash-modal { transform:none; opacity:1; }
.dash-modal-header {
    padding:20px 24px 16px; border-bottom:1px solid #f1f5f9;
    display:flex; align-items:center; justify-content:space-between; flex-shrink:0;
}
.dash-modal-title { font-family:'Space Grotesk',sans-serif; font-size:18px; font-weight:700; color:#0f172a; }
.dash-modal-close {
    width:32px; height:32px; border-radius:8px; display:flex;
    align-items:center; justify-content:center; background:#f8fafc;
    border:none; cursor:pointer; font-size:16px; color:#64748b;
    transition:all var(--dash-transition);
}
.dash-modal-close:hover { background:#fee2e2; color:#dc2626; }
.dash-modal-body { padding:20px 24px; overflow-y:auto; flex:1; }
.dash-modal-footer {
    padding:14px 24px; border-top:1px solid #f1f5f9;
    display:flex; align-items:center; justify-content:flex-end; gap:10px; flex-shrink:0;
}

/* Modal tabs */
.modal-tabs { display:flex; gap:4px; background:#f8fafc; border-radius:10px; padding:4px; margin-bottom:20px; }
.modal-tab {
    flex:1; padding:7px 12px; border-radius:7px; border:none; background:transparent;
    font-size:13px; font-weight:500; color:#64748b; cursor:pointer;
    transition:all var(--dash-transition);
}
.modal-tab.active { background:#fff; color:#0f172a; font-weight:600; box-shadow:0 1px 4px rgba(0,0,0,0.08); }
.modal-tab-panel { display:none; }
.modal-tab-panel.active { display:block; animation:fadeIn .2s ease; }

/* Info grid in modal */
.info-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:12px; }
.info-kpi {
    background:#f8fafc; border-radius:12px; padding:14px 16px;
}
.info-kpi-label { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:#94a3b8; }
.info-kpi-value { font-family:'Space Grotesk',sans-serif; font-size:22px; font-weight:700; color:#0f172a; }

/* Skeleton loader */
.skeleton {
    background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);
    background-size:800px 100%;
    animation:shimmerSlide 1.4s infinite;
    border-radius:6px;
}

/* Loading spinner */
.modal-loader { display:flex; gap:8px; justify-content:center; padding:40px 0; }
.modal-loader span {
    width:10px; height:10px; border-radius:50%; background:#6366f1;
    animation:dotBounce 1.4s infinite ease-in-out;
}
.modal-loader span:nth-child(2){ animation-delay:.16s; }
.modal-loader span:nth-child(3){ animation-delay:.32s; }

/* Filter bar */
.filter-bar { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px; align-items:center; }
.filter-btn {
    padding:5px 12px; border:1px solid #e2e8f0; border-radius:20px;
    background:#fff; font-size:12px; font-weight:500; color:#64748b;
    cursor:pointer; transition:all var(--dash-transition);
}
.filter-btn.active, .filter-btn:hover { background:#6366f1; border-color:#6366f1; color:#fff; }

/* Responsive */
@media(max-width:768px){
    .stat-value { font-size:22px; }
    .dash-modal { max-height:95vh; }
    .info-grid { grid-template-columns:1fr 1fr; }
}

/* Color helpers */
.bg-indigo-soft { background:#eef2ff; } .text-indigo { color:#6366f1; }
.bg-blue-soft    { background:#dbeafe; } .text-blue    { color:#2563eb; }
.bg-emerald-soft { background:#d1fae5; } .text-emerald { color:#059669; }
.bg-rose-soft    { background:#ffe4e6; } .text-rose    { color:#f43f5e; }
.bg-amber-soft   { background:#fef3c7; } .text-amber   { color:#d97706; }
.bg-purple-soft  { background:#f3e8ff; } .text-purple  { color:#9333ea; }
.bg-sky-soft     { background:#e0f2fe; } .text-sky     { color:#0284c7; }
.bg-teal-soft    { background:#ccfbf1; } .text-teal    { color:#0d9488; }
</style>

<div class="main-content dash-page">
<div class="page-content">
<div class="container-fluid">

{{-- ===== Page Header ===== --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h4 class="mb-1 fw-bold dash-title" style="color:#0f172a;font-size:22px;">School Analytics</h4>
                <span class="live-dot">Live · updated {{ now()->format('g:i A') }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if($currentSession)
                <span class="badge" style="background:#eef2ff;color:#6366f1;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;">
                    {{ $currentSession->session }}
                </span>
                @endif
                @if($currentTerm)
                <span class="badge" style="background:#f0fdf4;color:#16a34a;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;">
                    {{ $currentTerm->term }}
                </span>
                @endif
                <ol class="breadcrumb m-0 bg-transparent">
                    <li class="breadcrumb-item"><a href="#" style="color:#94a3b8;font-size:12px;">Dashboards</a></li>
                    <li class="breadcrumb-item active" style="font-size:12px;">Analytics</li>
                </ol>
            </div>
        </div>
    </div>
</div>

@hasrole('Super Admin')

{{-- ===== ROW 1: Primary Stats ===== --}}
<div class="row g-3 mb-3">

    {{-- Total Population --}}
    <div class="col-xxl-3 col-md-6">
        <div class="stat-card" onclick="openModal('populationModal')">
            <div class="d-flex align-items-start justify-content-between">
                <div class="flex-grow-1">
                    <p class="stat-label mb-0">Total Population</p>
                    <div class="stat-value counter-value mt-1" data-target="{{ $total_population }}">0</div>
                    <div class="stat-sub mt-1">
                        @php $pp = is_numeric($population_percentage) ? $population_percentage : 0; @endphp
                        <span class="stat-badge {{ $pp >= 0 ? 'up' : 'down' }}">
                            <i class="bi bi-arrow-{{ $pp >= 0 ? 'up' : 'down' }}" style="font-size:9px;"></i>{{ abs($pp) }}%
                        </span>
                        <span>vs last month</span>
                    </div>
                    {{-- Avatar strip of recent students --}}
                    <div class="avatar-strip mt-2">
                        @foreach(\App\Models\Student::latest()->take(5)->get() as $rs)
                        @php $pic = $rs->picture?->picture; @endphp
                        @if($pic && $pic !== 'unnamed.jpg')
                        <img src="{{ asset('storage/student_avatars/'.$pic) }}" class="av" title="{{ $rs->firstname }}">
                        @else
                        <div class="av" title="{{ $rs->firstname }}">{{ strtoupper(substr($rs->firstname,0,1).substr($rs->lastname,0,1)) }}</div>
                        @endif
                        @endforeach
                        @if($total_population > 5)
                        <div class="av av-more">+{{ number_format($total_population - 5) }}</div>
                        @endif
                    </div>
                </div>
                <div class="stat-icon-wrap bg-indigo-soft text-indigo"><i class="ph-users-three"></i></div>
            </div>
            <div class="stat-bar mt-3">
                @php $pw = min(100, abs($pp)*5); @endphp
                <div class="stat-bar-fill" style="width:{{ $pw }}%;--bar-w:{{ $pw }}%;background:linear-gradient(90deg,#6366f1,#8b5cf6);"></div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <small class="text-muted" style="font-size:11px;">Students + Staff</small>
                <a class="card-more-link" onclick="event.stopPropagation();openModal('populationModal')">Details <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>

    {{-- Staff Count --}}
    <div class="col-xxl-3 col-md-6">
        <div class="stat-card" onclick="openModal('staffModal')">
            <div class="d-flex align-items-start justify-content-between">
                <div class="flex-grow-1">
                    <p class="stat-label mb-0">Staff Members</p>
                    <div class="stat-value counter-value mt-1" data-target="{{ $staff_count }}">0</div>
                    <div class="stat-sub mt-1">
                        @php $sp = is_numeric($staff_percentage) ? $staff_percentage : 0; @endphp
                        <span class="stat-badge {{ $sp >= 0 ? 'up' : 'down' }}">
                            <i class="bi bi-arrow-{{ $sp >= 0 ? 'up' : 'down' }}" style="font-size:9px;"></i>{{ abs($sp) }}%
                        </span>
                        <span>vs last month</span>
                    </div>
                    <div class="avatar-strip mt-2">
                        @foreach(\App\Models\User::whereHas('roles',fn($q)=>$q->whereIn('name',['staff','teacher','admin']))->latest()->take(5)->get() as $su)
                        @if($su->avatar)
                        <img src="{{ asset('storage/'.$su->avatar) }}" class="av" title="{{ $su->name }}">
                        @else
                        <div class="av" title="{{ $su->name }}">{{ strtoupper(substr($su->name,0,2)) }}</div>
                        @endif
                        @endforeach
                    </div>
                </div>
                <div class="stat-icon-wrap bg-amber-soft text-amber"><i class="ph-chalkboard-teacher"></i></div>
            </div>
            <div class="stat-bar mt-3">
                @php $sbw = min(100, abs($sp)*5); @endphp
                <div class="stat-bar-fill" style="width:{{ $sbw }}%;--bar-w:{{ $sbw }}%;background:linear-gradient(90deg,#f59e0b,#ef4444);"></div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <small class="text-muted" style="font-size:11px;">
                    @foreach($staff_by_role as $role => $cnt)<span class="me-1">{{ ucfirst($role) }}: {{ $cnt }}</span>@endforeach
                </small>
                <a class="card-more-link" onclick="event.stopPropagation();openModal('staffModal')">Details <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>

    {{-- Male Students --}}
    <div class="col-xxl-3 col-md-6">
        <div class="stat-card" onclick="openModal('genderModal')">
            <div class="d-flex align-items-start justify-content-between">
                <div class="flex-grow-1">
                    <p class="stat-label mb-0">Male Students</p>
                    <div class="stat-value counter-value mt-1" data-target="{{ $gender_counts['Male'] }}">0</div>
                    <div class="stat-sub mt-1">
                        @php $mp = is_numeric($male_percentage) ? $male_percentage : 0; @endphp
                        <span class="stat-badge {{ $mp >= 0 ? 'up' : 'down' }}">
                            <i class="bi bi-arrow-{{ $mp >= 0 ? 'up' : 'down' }}" style="font-size:9px;"></i>{{ abs($mp) }}%
                        </span>
                        <span>vs last month</span>
                    </div>
                    @php $mPct = $total_population > 0 ? round(($gender_counts['Male']/$total_population)*100,1) : 0; @endphp
                    <div class="mt-2" style="font-size:12px;color:#94a3b8;">{{ $mPct }}% of total enrollment</div>
                </div>
                <div class="stat-icon-wrap bg-sky-soft text-sky"><i class="ph-gender-male"></i></div>
            </div>
            <div class="stat-bar mt-3">
                <div class="stat-bar-fill" style="width:{{ $mPct }}%;--bar-w:{{ $mPct }}%;background:linear-gradient(90deg,#0ea5e9,#06b6d4);"></div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <small class="text-muted" style="font-size:11px;">{{ $mPct }}% of population</small>
                <a class="card-more-link" onclick="event.stopPropagation();openModal('genderModal')">Breakdown <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>

    {{-- Female Students --}}
    <div class="col-xxl-3 col-md-6">
        <div class="stat-card" onclick="openModal('genderModal')">
            <div class="d-flex align-items-start justify-content-between">
                <div class="flex-grow-1">
                    <p class="stat-label mb-0">Female Students</p>
                    <div class="stat-value counter-value mt-1" data-target="{{ $gender_counts['Female'] }}">0</div>
                    <div class="stat-sub mt-1">
                        @php $fp = is_numeric($female_percentage) ? abs($female_percentage) : 0; @endphp
                        <span class="stat-badge {{ $female_percentage >= 0 ? 'up' : 'down' }}">
                            <i class="bi bi-arrow-{{ $female_percentage >= 0 ? 'up' : 'down' }}" style="font-size:9px;"></i>{{ $fp }}%
                        </span>
                        <span>vs last month</span>
                    </div>
                    @php $fPct = $total_population > 0 ? round(($gender_counts['Female']/$total_population)*100,1) : 0; @endphp
                    <div class="mt-2" style="font-size:12px;color:#94a3b8;">{{ $fPct }}% of total enrollment</div>
                </div>
                <div class="stat-icon-wrap bg-rose-soft text-rose"><i class="ph-gender-female"></i></div>
            </div>
            <div class="stat-bar mt-3">
                <div class="stat-bar-fill" style="width:{{ $fPct }}%;--bar-w:{{ $fPct }}%;background:linear-gradient(90deg,#f43f5e,#f97316);"></div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <small class="text-muted" style="font-size:11px;">{{ $fPct }}% of population</small>
                <a class="card-more-link" onclick="event.stopPropagation();openModal('genderModal')">Breakdown <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>

{{-- ===== ROW 2: Secondary Stats ===== --}}
<div class="row g-3 mb-4">

    <div class="col-xxl-3 col-md-6">
        <div class="stat-card" onclick="openModal('enrollmentModal')">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <p class="stat-label mb-0">New Enrollments</p>
                    <div class="stat-value counter-value mt-1" data-target="{{ $status_counts['New Student'] ?? 0 }}">0</div>
                    <div class="stat-sub mt-1"><i class="bi bi-person-plus text-success me-1"></i>This session</div>
                </div>
                <div class="stat-icon-wrap bg-emerald-soft text-emerald"><i class="ph-rocket-launch"></i></div>
            </div>
            <div class="stat-bar mt-3">
                @php $newPct = $total_population > 0 ? min(100,round((($status_counts['New Student']??0)/$total_population)*100)) : 0; @endphp
                <div class="stat-bar-fill" style="width:{{ $newPct }}%;--bar-w:{{ $newPct }}%;background:#10b981;"></div>
            </div>
            <div class="d-flex justify-content-between mt-2">
                <small class="text-muted" style="font-size:11px;">Returning: {{ number_format($status_counts['Old Student']??0) }}</small>
                <a class="card-more-link" onclick="event.stopPropagation();openModal('enrollmentModal')">View all <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <div class="col-xxl-3 col-md-6">
        <div class="stat-card" onclick="openModal('classesModal')">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <p class="stat-label mb-0">Active Classes</p>
                    <div class="stat-value counter-value mt-1" data-target="{{ $total_classes ?? 0 }}">0</div>
                    <div class="stat-sub mt-1"><i class="bi bi-grid-3x3 text-purple me-1"></i>Across all arms</div>
                </div>
                <div class="stat-icon-wrap bg-purple-soft text-purple"><i class="ph-graduation-cap"></i></div>
            </div>
            <div class="stat-bar mt-3">
                <div class="stat-bar-fill" style="width:80%;--bar-w:80%;background:linear-gradient(90deg,#9333ea,#c026d3);"></div>
            </div>
            <div class="d-flex justify-content-between mt-2">
                <small class="text-muted" style="font-size:11px;">Avg {{ $total_classes > 0 ? round($total_population/$total_classes) : 0 }} students/class</small>
                <a class="card-more-link" onclick="event.stopPropagation();openModal('classesModal')">View all <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <div class="col-xxl-3 col-md-6">
        <div class="stat-card" onclick="openModal('subjectsModal')">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <p class="stat-label mb-0">Total Subjects</p>
                    <div class="stat-value counter-value mt-1" data-target="{{ $total_subjects ?? 0 }}">0</div>
                    <div class="stat-sub mt-1"><i class="bi bi-book text-teal me-1"></i>Active subjects</div>
                </div>
                <div class="stat-icon-wrap bg-teal-soft text-teal"><i class="ph-book-open"></i></div>
            </div>
            <div class="stat-bar mt-3">
                <div class="stat-bar-fill" style="width:70%;--bar-w:70%;background:linear-gradient(90deg,#0d9488,#0ea5e9);"></div>
            </div>
            <div class="d-flex justify-content-between mt-2">
                <small class="text-muted" style="font-size:11px;">Assigned to {{ $total_classes }} classes</small>
                <a class="card-more-link" onclick="event.stopPropagation();openModal('subjectsModal')">View all <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <div class="col-xxl-3 col-md-6">
        <div class="stat-card" onclick="openModal('attendanceModal')">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <p class="stat-label mb-0">Attendance Rate</p>
                    <div class="stat-value mt-1" style="color:{{ $overall_attendance_rate >= 75 ? '#16a34a' : ($overall_attendance_rate >= 50 ? '#d97706' : '#dc2626') }};">
                        {{ $overall_attendance_rate }}%
                    </div>
                    <div class="stat-sub mt-1"><i class="bi bi-calendar-check text-info me-1"></i>This term</div>
                </div>
                <div class="stat-icon-wrap bg-blue-soft text-blue"><i class="ph-calendar-check"></i></div>
            </div>
            <div class="stat-bar mt-3">
                <div class="stat-bar-fill" style="width:{{ $overall_attendance_rate }}%;--bar-w:{{ $overall_attendance_rate }}%;background:{{ $overall_attendance_rate >= 75 ? '#16a34a' : ($overall_attendance_rate >= 50 ? '#f59e0b' : '#ef4444') }};"></div>
            </div>
            <div class="d-flex justify-content-between mt-2">
                <small class="text-muted" style="font-size:11px;">{{ $overall_attendance_rate >= 75 ? 'Good standing' : ($overall_attendance_rate >= 50 ? 'Needs attention' : 'Critical') }}</small>
                <a class="card-more-link" onclick="event.stopPropagation();openModal('attendanceModal')">Details <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>

{{-- ===== ROW 3: Charts Row ===== --}}
<div class="row g-3 mb-4">

    {{-- Academic Performance Bar Chart --}}
    <div class="col-xxl-8">
        <div class="section-card">
            <div class="section-card-header d-flex justify-content-between align-items-start">
                <div>
                    <div class="section-card-title">Academic Performance by Class</div>
                    <div class="section-card-sub">Average total scores — {{ $currentTerm?->term ?? 'Current Term' }}</div>
                </div>
                <div class="d-flex gap-2">
                    <button class="filter-btn active" onclick="toggleChartView('perf-bar','bar',this)" style="font-size:11px;padding:4px 10px;">Bar</button>
                    <button class="filter-btn" onclick="toggleChartView('perf-bar','horizontalBar',this)" style="font-size:11px;padding:4px 10px;">H-Bar</button>
                    <button class="filter-btn" onclick="openModal('academicModal')" style="font-size:11px;padding:4px 10px;">Full Details →</button>
                </div>
            </div>
            <div class="section-card-body">
                <div style="display:flex;flex-wrap:wrap;gap:16px;margin-bottom:12px;font-size:11px;color:#94a3b8;">
                    <span style="display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;border-radius:2px;background:#6366f1;"></span>Avg Score</span>
                    <span style="display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;border-radius:2px;background:#e2e8f0;"></span>Pass threshold (40%)</span>
                </div>
                <div class="chart-wrap" style="height:280px;">
                    <canvas id="academicPerformanceChart" role="img" aria-label="Bar chart of academic performance by class">Academic performance by class scores</canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Grade Distribution --}}
    <div class="col-xxl-4">
        <div class="section-card">
            <div class="section-card-header d-flex justify-content-between align-items-start">
                <div>
                    <div class="section-card-title">Grade Distribution</div>
                    <div class="section-card-sub">Current term breakdown</div>
                </div>
                <button class="filter-btn" onclick="openModal('gradeModal')" style="font-size:11px;padding:4px 10px;">Expand →</button>
            </div>
            <div class="section-card-body">
                <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:10px;font-size:11px;">
                    @foreach(['A1'=>'#16a34a','B2'=>'#2563eb','B3'=>'#0284c7','C4'=>'#d97706','C5'=>'#ea580c','C6'=>'#dc2626','D7'=>'#9333ea','E8'=>'#be185d','F9'=>'#64748b'] as $g=>$c)
                    @if(isset($grade_distribution[$g]) && $grade_distribution[$g] > 0)
                    <span style="display:flex;align-items:center;gap:3px;color:#64748b;"><span style="width:8px;height:8px;border-radius:2px;background:{{ $c }};"></span>{{ $g }}: {{ $grade_distribution[$g] }}</span>
                    @endif
                    @endforeach
                </div>
                <div class="chart-wrap" style="height:240px;">
                    <canvas id="gradeDistributionChart" role="img" aria-label="Grade distribution chart">Grade distribution for current term</canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== ROW 4: Population + Trends ===== --}}
<div class="row g-3 mb-4">
    <div class="col-xxl-5">
        <div class="section-card">
            <div class="section-card-header d-flex justify-content-between align-items-center">
                <div>
                    <div class="section-card-title">Population Breakdown</div>
                    <div class="section-card-sub">Gender & staff distribution</div>
                </div>
                <button class="filter-btn" onclick="openModal('populationModal')" style="font-size:11px;padding:4px 10px;">Details →</button>
            </div>
            <div class="section-card-body">
                <div style="display:flex;flex-wrap:wrap;gap:12px;margin-bottom:10px;font-size:11px;">
                    <span style="display:flex;align-items:center;gap:4px;color:#64748b;"><span style="width:10px;height:10px;border-radius:2px;background:#0ea5e9;"></span>Male: {{ $gender_counts['Male'] }}</span>
                    <span style="display:flex;align-items:center;gap:4px;color:#64748b;"><span style="width:10px;height:10px;border-radius:2px;background:#f43f5e;"></span>Female: {{ $gender_counts['Female'] }}</span>
                    <span style="display:flex;align-items:center;gap:4px;color:#64748b;"><span style="width:10px;height:10px;border-radius:2px;background:#f59e0b;"></span>Staff: {{ $staff_count }}</span>
                </div>
                <div class="chart-wrap" style="height:260px;">
                    <canvas id="populationChart" role="img" aria-label="Population breakdown donut chart">Population by gender and staff</canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xxl-7">
        <div class="section-card">
            <div class="section-card-header d-flex justify-content-between align-items-center">
                <div>
                    <div class="section-card-title">Enrollment Trends</div>
                    <div class="section-card-sub">Monthly new students — last 12 months</div>
                </div>
                <div class="d-flex gap-2">
                    <span style="display:flex;align-items:center;gap:4px;font-size:11px;color:#94a3b8;"><span style="width:10px;height:3px;background:#6366f1;border-radius:2px;"></span>New Students</span>
                </div>
            </div>
            <div class="section-card-body">
                <div class="chart-wrap" style="height:260px;">
                    <canvas id="trendsChart" role="img" aria-label="Enrollment trends line chart">Monthly enrollment trends</canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== ROW 5: Top Students + Subject Performance ===== --}}
<div class="row g-3 mb-4">

    {{-- Top Students --}}
    <div class="col-xxl-6">
        <div class="section-card">
            <div class="section-card-header d-flex justify-content-between align-items-center">
                <div>
                    <div class="section-card-title">Top Performing Students</div>
                    <div class="section-card-sub">Highest averages this term</div>
                </div>
                <button class="filter-btn" onclick="openModal('topStudentsModal')" style="font-size:11px;padding:4px 10px;">Full List →</button>
            </div>
            <div class="section-card-body">
                <table class="dash-table">
                    <thead><tr>
                        <th>#</th><th>Student</th><th>Adm No</th><th>Avg</th><th></th>
                    </tr></thead>
                    <tbody>
                    @forelse($top_students ?? [] as $index => $student)
                    <tr>
                        <td>
                            <span class="rank-badge {{ $index === 0 ? 'rank-1' : ($index === 1 ? 'rank-2' : ($index === 2 ? 'rank-3' : 'rank-n')) }}">
                                {{ $index < 3 ? ['🥇','🥈','🥉'][$index] : $index+1 }}
                            </span>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                @php
                                  $stObj = \App\Models\Student::where('admissionNo',$student['admission_no'])->first();
                                  $stPic = $stObj?->picture?->picture;
                                @endphp
                                @if($stPic && $stPic !== 'unnamed.jpg')
                                <img src="{{ asset('storage/student_avatars/'.$stPic) }}" class="stud-av">
                                @else
                                <div class="stud-av">{{ strtoupper(substr($student['name'],0,2)) }}</div>
                                @endif
                                <span style="font-weight:500;font-size:13px;">{{ $student['name'] }}</span>
                            </div>
                        </td>
                        <td style="color:#94a3b8;font-size:12px;">{{ $student['admission_no'] }}</td>
                        <td>
                            @php $avg = $student['average']; @endphp
                            <span class="score-pill {{ $avg >= 75 ? 'score-A' : ($avg >= 60 ? 'score-B' : ($avg >= 50 ? 'score-C' : ($avg >= 40 ? 'score-D' : 'score-F'))) }}">
                                {{ $avg }}%
                            </span>
                        </td>
                        <td>
                            <div class="mini-prog" style="width:70px;">
                                <div class="mini-prog-fill" style="width:{{ $avg }}%;background:{{ $avg >= 75 ? '#16a34a' : ($avg >= 60 ? '#2563eb' : '#f59e0b') }};"></div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-3" style="font-size:13px;">No academic data available yet</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Subject Performance --}}
    <div class="col-xxl-6">
        <div class="section-card">
            <div class="section-card-header d-flex justify-content-between align-items-center">
                <div>
                    <div class="section-card-title">Subject Performance</div>
                    <div class="section-card-sub">With assigned teachers</div>
                </div>
                <button class="filter-btn" onclick="openModal('subjectPerfModal')" style="font-size:11px;padding:4px 10px;">Full Details →</button>
            </div>
            <div class="section-card-body">
                <table class="dash-table">
                    <thead><tr>
                        <th>Subject</th><th>Teacher</th><th>Avg</th><th>Pass Rate</th><th>High</th>
                    </tr></thead>
                    <tbody>
                    @forelse($subject_performance ?? [] as $subj)
                    <tr>
                        <td style="font-weight:500;font-size:13px;">{{ $subj['subject_name'] }}</td>
                        <td>
                            @php
                              $teacher = \App\Models\SubjectTeacher::whereHas('subject', fn($q)=>$q->where('subject',$subj['subject_name']))
                                ->with('staff')->latest()->first();
                            @endphp
                            @if($teacher && $teacher->staff)
                            <div class="teacher-chip">
                                @if($teacher->staff->avatar)
                                <img src="{{ asset('storage/'.$teacher->staff->avatar) }}" class="teacher-chip-av">
                                @else
                                <div class="teacher-chip-av">{{ strtoupper(substr($teacher->staff->name,0,2)) }}</div>
                                @endif
                                <span>{{ Str::limit(explode(' ',$teacher->staff->name)[0]??'',12) }}</span>
                            </div>
                            @else
                            <span style="font-size:11px;color:#94a3b8;">—</span>
                            @endif
                        </td>
                        <td><span class="score-pill {{ $subj['avg_score'] >= 60 ? 'score-B' : ($subj['avg_score'] >= 40 ? 'score-C' : 'score-D') }}">{{ $subj['avg_score'] }}%</span></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:6px;">
                                <div class="mini-prog" style="width:50px;"><div class="mini-prog-fill" style="width:{{ $subj['pass_rate'] }}%;background:#10b981;"></div></div>
                                <span style="font-size:11px;color:#64748b;">{{ $subj['pass_rate'] }}%</span>
                            </div>
                        </td>
                        <td style="font-size:12px;color:#94a3b8;">{{ $subj['max_score'] }}%</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-3" style="font-size:13px;">No data available</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ===== ROW 6: Activity + Quick Actions ===== --}}
<div class="row g-3 mb-4">
    <div class="col-xxl-7">
        <div class="section-card">
            <div class="section-card-header">
                <div class="section-card-title">Recent Activity</div>
                <div class="section-card-sub">Latest updates across school</div>
            </div>
            <div class="section-card-body">
                @forelse($recent_activities ?? [] as $act)
                <div class="timeline-item">
                    <div class="timeline-dot bg-{{ $act['color'] }}-soft text-{{ $act['color'] }}">
                        <i class="{{ $act['icon'] }}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div style="font-weight:500;font-size:13px;color:#0f172a;">{{ $act['title'] }}</div>
                        <div style="font-size:12px;color:#64748b;margin-top:2px;">{{ $act['description'] }}</div>
                        <div style="font-size:11px;color:#94a3b8;margin-top:3px;"><i class="bi bi-clock me-1"></i>{{ $act['time'] }}</div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4" style="color:#94a3b8;font-size:13px;">No recent activities</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-xxl-5">
        <div class="section-card">
            <div class="section-card-header">
                <div class="section-card-title">Quick Actions</div>
                <div class="section-card-sub">Frequently used features</div>
            </div>
            <div class="section-card-body">
                <div class="row g-2">
                    @php $qas = [
                        ['route'=>route('student.index'),    'icon'=>'ph-users-four',      'class'=>'text-indigo', 'label'=>'Students',   'sub'=>'Manage'],
                        ['route'=>route('staff.payments.dashboard'), 'icon'=>'ph-wallet', 'class'=>'text-emerald','label'=>'Payroll',    'sub'=>'Staff pay'],
                        ['route'=>route('exams.index'),      'icon'=>'ph-clipboard-text',  'class'=>'text-amber',  'label'=>'Exams',      'sub'=>'Manage'],
                        ['route'=>route('attendance.my-classes'),'icon'=>'ph-calendar-check','class'=>'text-blue','label'=>'Attendance', 'sub'=>'Mark'],
                    ]; @endphp
                    @foreach($qas as $qa)
                    <div class="col-6">
                        <a href="{{ $qa['route'] }}" class="qa-btn">
                            <i class="{{ $qa['icon'] }} {{ $qa['class'] }}" style="font-size:26px;"></i>
                            <div>
                                <div style="font-size:13px;font-weight:600;color:#0f172a;">{{ $qa['label'] }}</div>
                                <div style="font-size:11px;color:#94a3b8;">{{ $qa['sub'] }}</div>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>

                {{-- Finance snapshot --}}
                <div style="margin-top:16px;padding:14px;background:#f8fafc;border-radius:12px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <span style="font-size:12px;font-weight:600;color:#334155;">Fee Collection</span>
                        <span style="font-size:12px;font-weight:700;color:#6366f1;">{{ $collection_rate }}%</span>
                    </div>
                    <div class="scholarship-bar">
                        <div style="width:{{ $collection_rate }}%;background:linear-gradient(90deg,#6366f1,#8b5cf6);border-radius:8px;"></div>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-top:6px;">
                        <span style="font-size:10px;color:#94a3b8;">Collected: ₦{{ number_format($total_payments) }}</span>
                        <span style="font-size:10px;color:#94a3b8;">Target: ₦{{ number_format($total_bills) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== Class Capacity Heatmap ===== --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="section-card">
            <div class="section-card-header d-flex justify-content-between align-items-center">
                <div>
                    <div class="section-card-title">Class Capacity & Utilization</div>
                    <div class="section-card-sub">Current enrollment vs capacity per class</div>
                </div>
                <button class="filter-btn" onclick="openModal('classesModal')" style="font-size:11px;padding:4px 10px;">Full Details →</button>
            </div>
            <div class="section-card-body">
                <div class="row g-2">
                    @foreach($class_capacity_data ?? [] as $cls)
                    @php
                        $util = $cls['utilization'];
                        $color = $util >= 90 ? '#ef4444' : ($util >= 70 ? '#f59e0b' : '#10b981');
                        $bg = $util >= 90 ? '#fee2e2' : ($util >= 70 ? '#fef3c7' : '#dcfce7');
                    @endphp
                    <div class="col-xxl-2 col-md-4 col-6">
                        <div style="background:{{ $bg }};border-radius:12px;padding:12px;text-align:center;cursor:pointer;transition:transform .2s;" onclick="openModal('classesModal')">
                            <div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.4px;">{{ $cls['class_name'] }} {{ $cls['arm'] }}</div>
                            <div style="font-family:'Space Grotesk',sans-serif;font-size:22px;font-weight:700;color:{{ $color }};margin:4px 0;">{{ $cls['students'] }}</div>
                            <div style="font-size:10px;color:#64748b;">/ {{ $cls['capacity'] }} capacity</div>
                            <div style="height:4px;background:rgba(0,0,0,0.08);border-radius:4px;margin-top:8px;overflow:hidden;">
                                <div style="width:{{ min(100,$util) }}%;height:100%;background:{{ $color }};border-radius:4px;"></div>
                            </div>
                            <div style="font-size:10px;font-weight:600;color:{{ $color }};margin-top:4px;">{{ $util }}%</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===================================================
     MODALS
     =================================================== --}}

{{-- Population Modal --}}
<div class="dash-modal-overlay" id="populationModal" onclick="if(event.target===this)closeModal('populationModal')">
    <div class="dash-modal" style="max-width:780px;">
        <div class="dash-modal-header">
            <div class="dash-modal-title">Population Overview</div>
            <button class="dash-modal-close" onclick="closeModal('populationModal')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="dash-modal-body">
            <div class="info-grid mb-4">
                <div class="info-kpi"><div class="info-kpi-label">Total</div><div class="info-kpi-value">{{ number_format($total_population) }}</div></div>
                <div class="info-kpi"><div class="info-kpi-label">Male</div><div class="info-kpi-value" style="color:#0ea5e9;">{{ number_format($gender_counts['Male']) }}</div></div>
                <div class="info-kpi"><div class="info-kpi-label">Female</div><div class="info-kpi-value" style="color:#f43f5e;">{{ number_format($gender_counts['Female']) }}</div></div>
                <div class="info-kpi"><div class="info-kpi-label">Staff</div><div class="info-kpi-value" style="color:#f59e0b;">{{ number_format($staff_count) }}</div></div>
            </div>
            <div style="height:300px;"><canvas id="popModalChart" role="img" aria-label="Population modal chart">Detailed population breakdown</canvas></div>
        </div>
    </div>
</div>

{{-- Academic Modal --}}
<div class="dash-modal-overlay" id="academicModal" onclick="if(event.target===this)closeModal('academicModal')">
    <div class="dash-modal" style="max-width:900px;">
        <div class="dash-modal-header">
            <div class="dash-modal-title">Academic Performance — Detailed</div>
            <button class="dash-modal-close" onclick="closeModal('academicModal')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="dash-modal-body">
            <div class="modal-tabs" id="academicTabs">
                <button class="modal-tab active" onclick="switchTab('academicTabs','tab-perf')">By Class</button>
                <button class="modal-tab" onclick="switchTab('academicTabs','tab-grade')">Grade Distribution</button>
                <button class="modal-tab" onclick="switchTab('academicTabs','tab-subj')">Subject Analysis</button>
            </div>
            <div class="modal-tab-panel active" id="tab-perf">
                <div class="info-grid mb-3">
                    @foreach($academic_performance ?? [] as $perf)
                    <div class="info-kpi">
                        <div class="info-kpi-label">{{ $perf['class_name'] }}</div>
                        <div class="info-kpi-value" style="font-size:18px;">{{ $perf['avg_score'] }}%</div>
                        <div class="mini-prog mt-1"><div class="mini-prog-fill" style="width:{{ $perf['avg_score'] }}%;background:#6366f1;"></div></div>
                    </div>
                    @endforeach
                </div>
                <div style="height:280px;"><canvas id="academicModalChart" role="img" aria-label="Academic performance modal chart">Academic performance detail</canvas></div>
            </div>
            <div class="modal-tab-panel" id="tab-grade">
                <div style="height:320px;"><canvas id="gradeModalChart" role="img" aria-label="Grade distribution modal chart">Grade distribution detail</canvas></div>
            </div>
            <div class="modal-tab-panel" id="tab-subj">
                <table class="dash-table">
                    <thead><tr><th>Subject</th><th>Teacher</th><th>Avg Score</th><th>Pass Rate</th><th>Highest</th><th>Lowest</th></tr></thead>
                    <tbody>
                    @forelse($subject_performance ?? [] as $sp)
                    @php $t2 = \App\Models\SubjectTeacher::whereHas('subject',fn($q)=>$q->where('subject',$sp['subject_name']))->with('staff')->latest()->first(); @endphp
                    <tr>
                        <td style="font-weight:500;">{{ $sp['subject_name'] }}</td>
                        <td>
                            @if($t2?->staff)
                            <div class="teacher-chip">
                                @if($t2->staff->avatar)<img src="{{ asset('storage/'.$t2->staff->avatar) }}" class="teacher-chip-av">@else<div class="teacher-chip-av">{{ strtoupper(substr($t2->staff->name,0,2)) }}</div>@endif
                                <span>{{ $t2->staff->name }}</span>
                            </div>
                            @else—@endif
                        </td>
                        <td><span class="score-pill {{ $sp['avg_score']>=60?'score-B':($sp['avg_score']>=40?'score-C':'score-D') }}">{{ $sp['avg_score'] }}%</span></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:6px;">
                                <div class="mini-prog" style="width:60px;"><div class="mini-prog-fill" style="width:{{ $sp['pass_rate'] }}%;background:#10b981;"></div></div>
                                <span style="font-size:11px;">{{ $sp['pass_rate'] }}%</span>
                            </div>
                        </td>
                        <td style="font-size:12px;color:#16a34a;font-weight:600;">{{ $sp['max_score'] }}%</td>
                        <td style="font-size:12px;color:#dc2626;">{{ $sp['min_score'] }}%</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">No data available</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Top Students Modal --}}
<div class="dash-modal-overlay" id="topStudentsModal" onclick="if(event.target===this)closeModal('topStudentsModal')">
    <div class="dash-modal">
        <div class="dash-modal-header">
            <div class="dash-modal-title">Top Performing Students</div>
            <button class="dash-modal-close" onclick="closeModal('topStudentsModal')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="dash-modal-body">
            <div class="info-grid mb-4">
                <div class="info-kpi"><div class="info-kpi-label">Analysed</div><div class="info-kpi-value">{{ count($top_students??[]) }}</div></div>
                @if(count($top_students??[]) > 0)
                <div class="info-kpi"><div class="info-kpi-label">Class Leader</div><div class="info-kpi-value" style="font-size:14px;">{{ ($top_students[0]['name']??'N/A') }}</div></div>
                <div class="info-kpi"><div class="info-kpi-label">Top Score</div><div class="info-kpi-value" style="color:#16a34a;">{{ ($top_students[0]['average']??0) }}%</div></div>
                @endif
            </div>
            <table class="dash-table">
                <thead><tr><th>Rank</th><th>Student</th><th>Admission No</th><th>Average</th><th>Grade</th></tr></thead>
                <tbody>
                @forelse($top_students ?? [] as $i => $stu)
                @php $stO2 = \App\Models\Student::where('admissionNo',$stu['admission_no'])->first(); $stP2 = $stO2?->picture?->picture; @endphp
                <tr>
                    <td><span class="rank-badge {{ $i===0?'rank-1':($i===1?'rank-2':($i===2?'rank-3':'rank-n')) }}">{{ $i+1 }}</span></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            @if($stP2 && $stP2!=='unnamed.jpg')<img src="{{ asset('storage/student_avatars/'.$stP2) }}" class="stud-av">@else<div class="stud-av">{{ strtoupper(substr($stu['name'],0,2)) }}</div>@endif
                            <span style="font-weight:500;">{{ $stu['name'] }}</span>
                        </div>
                    </td>
                    <td style="font-size:12px;color:#94a3b8;">{{ $stu['admission_no'] }}</td>
                    <td><span class="score-pill {{ $stu['average']>=75?'score-A':($stu['average']>=60?'score-B':($stu['average']>=50?'score-C':($stu['average']>=40?'score-D':'score-F'))) }}">{{ $stu['average'] }}%</span></td>
                    <td style="font-size:12px;font-weight:600;">{{ $stu['average']>=75?'A1':($stu['average']>=70?'B2':($stu['average']>=65?'B3':($stu['average']>=60?'C4':($stu['average']>=55?'C5':($stu['average']>=50?'C6':($stu['average']>=45?'D7':($stu['average']>=40?'E8':'F9'))))))) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-3">No data available</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="dash-modal-footer">
            <a href="{{ route('student.index') }}" class="btn btn-sm" style="background:#6366f1;color:#fff;border-radius:8px;font-size:12px;padding:7px 16px;">View All Students</a>
        </div>
    </div>
</div>

{{-- Staff Modal --}}
<div class="dash-modal-overlay" id="staffModal" onclick="if(event.target===this)closeModal('staffModal')">
    <div class="dash-modal">
        <div class="dash-modal-header">
            <div class="dash-modal-title">Staff Directory</div>
            <button class="dash-modal-close" onclick="closeModal('staffModal')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="dash-modal-body">
            <div class="info-grid mb-4">
                <div class="info-kpi"><div class="info-kpi-label">Total Staff</div><div class="info-kpi-value">{{ $staff_count }}</div></div>
                @foreach($staff_by_role as $role => $cnt)
                <div class="info-kpi"><div class="info-kpi-label">{{ ucfirst($role) }}</div><div class="info-kpi-value" style="font-size:20px;">{{ $cnt }}</div></div>
                @endforeach
            </div>
            <table class="dash-table">
                <thead><tr><th>Staff</th><th>Role</th><th>Joined</th></tr></thead>
                <tbody>
                @foreach(\App\Models\User::whereHas('roles',fn($q)=>$q->whereIn('name',['staff','teacher','admin']))->with('roles')->latest()->take(20)->get() as $st)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            @if($st->avatar)<img src="{{ asset('storage/'.$st->avatar) }}" class="stud-av">
                            @else<div class="stud-av">{{ strtoupper(substr($st->name,0,2)) }}</div>@endif
                            <div><div style="font-weight:500;font-size:13px;">{{ $st->name }}</div><div style="font-size:11px;color:#94a3b8;">{{ $st->email }}</div></div>
                        </div>
                    </td>
                    <td><span class="badge" style="background:#eef2ff;color:#6366f1;font-size:11px;padding:3px 8px;border-radius:20px;">{{ $st->roles->first()?->name ?? 'Staff' }}</span></td>
                    <td style="font-size:12px;color:#94a3b8;">{{ $st->created_at->format('d M Y') }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Gender Modal --}}
<div class="dash-modal-overlay" id="genderModal" onclick="if(event.target===this)closeModal('genderModal')">
    <div class="dash-modal" style="max-width:640px;">
        <div class="dash-modal-header">
            <div class="dash-modal-title">Gender Breakdown</div>
            <button class="dash-modal-close" onclick="closeModal('genderModal')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="dash-modal-body">
            <div class="info-grid mb-4">
                <div class="info-kpi"><div class="info-kpi-label">Male</div><div class="info-kpi-value" style="color:#0ea5e9;">{{ $gender_counts['Male'] }}</div></div>
                <div class="info-kpi"><div class="info-kpi-label">Female</div><div class="info-kpi-value" style="color:#f43f5e;">{{ $gender_counts['Female'] }}</div></div>
                <div class="info-kpi"><div class="info-kpi-label">Ratio M:F</div><div class="info-kpi-value" style="font-size:16px;">{{ $gender_counts['Female']>0 ? round($gender_counts['Male']/$gender_counts['Female'],2) : '—' }}:1</div></div>
            </div>
            <div style="height:260px;"><canvas id="genderModalChart" role="img" aria-label="Gender breakdown chart">Gender distribution</canvas></div>
        </div>
    </div>
</div>

{{-- Classes Modal --}}
<div class="dash-modal-overlay" id="classesModal" onclick="if(event.target===this)closeModal('classesModal')">
    <div class="dash-modal" style="max-width:900px;">
        <div class="dash-modal-header">
            <div class="dash-modal-title">Class Details & Utilization</div>
            <button class="dash-modal-close" onclick="closeModal('classesModal')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="dash-modal-body">
            <div class="info-grid mb-4">
                <div class="info-kpi"><div class="info-kpi-label">Total Classes</div><div class="info-kpi-value">{{ $total_classes }}</div></div>
                <div class="info-kpi"><div class="info-kpi-label">Avg Students</div><div class="info-kpi-value">{{ $total_classes > 0 ? round($total_population/$total_classes) : 0 }}</div></div>
                <div class="info-kpi"><div class="info-kpi-label">Term Students</div><div class="info-kpi-value">{{ number_format($students_in_current_term) }}</div></div>
            </div>
            <table class="dash-table">
                <thead><tr><th>Class</th><th>Students</th><th>Capacity</th><th>Utilization</th><th>Status</th></tr></thead>
                <tbody>
                @foreach($class_capacity_data ?? [] as $cls)
                @php $util=$cls['utilization']; @endphp
                <tr>
                    <td style="font-weight:500;">{{ $cls['class_name'] }} {{ $cls['arm'] }}</td>
                    <td style="font-family:'Space Grotesk',sans-serif;font-weight:600;">{{ $cls['students'] }}</td>
                    <td style="color:#94a3b8;">{{ $cls['capacity'] }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div class="mini-prog" style="width:80px;">
                                <div class="mini-prog-fill" style="width:{{ min(100,$util) }}%;background:{{ $util>=90?'#ef4444':($util>=70?'#f59e0b':'#10b981') }};"></div>
                            </div>
                            <span style="font-size:12px;font-weight:600;">{{ $util }}%</span>
                        </div>
                    </td>
                    <td>
                        <span class="stat-badge {{ $util>=90?'down':($util>=70?'neu':'up') }}" style="font-size:11px;">
                            {{ $util>=90?'Full':($util>=70?'High':'Optimal') }}
                        </span>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Subjects Modal --}}
<div class="dash-modal-overlay" id="subjectsModal" onclick="if(event.target===this)closeModal('subjectsModal')">
    <div class="dash-modal">
        <div class="dash-modal-header">
            <div class="dash-modal-title">Subjects & Teachers</div>
            <button class="dash-modal-close" onclick="closeModal('subjectsModal')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="dash-modal-body">
            <div class="info-grid mb-4">
                <div class="info-kpi"><div class="info-kpi-label">Total Subjects</div><div class="info-kpi-value">{{ $total_subjects }}</div></div>
            </div>
            <table class="dash-table">
                <thead><tr><th>Subject</th><th>Teachers Assigned</th><th>Classes</th></tr></thead>
                <tbody>
                @foreach(\App\Models\Subject::withCount('subjectTeachers')->take(30)->get() as $sub)
                <tr>
                    <td style="font-weight:500;">{{ $sub->subject }}</td>
                    <td>
                        <div style="display:flex;flex-wrap:wrap;gap:4px;">
                            @foreach($sub->subjectTeachers()->with('staff')->distinct('staffid')->take(3)->get() as $st)
                            @if($st->staff)
                            <div class="teacher-chip">
                                @if($st->staff->avatar)<img src="{{ asset('storage/'.$st->staff->avatar) }}" class="teacher-chip-av">@else<div class="teacher-chip-av">{{ strtoupper(substr($st->staff->name,0,2)) }}</div>@endif
                                <span>{{ explode(' ',$st->staff->name)[0] }}</span>
                            </div>
                            @endif
                            @endforeach
                            @if($sub->subject_teachers_count > 3)<span style="font-size:11px;color:#94a3b8;">+{{ $sub->subject_teachers_count-3 }} more</span>@endif
                        </div>
                    </td>
                    <td>
                        <span style="font-size:12px;background:#eef2ff;color:#6366f1;padding:2px 8px;border-radius:20px;">
                            {{ $sub->subjectTeachers()->distinct('schoolclassid')->join('subjectclass','subjectclass.subjectteacherid','=','subjectteacher.id')->distinct()->count('schoolclassid') }} classes
                        </span>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Grade Modal --}}
<div class="dash-modal-overlay" id="gradeModal" onclick="if(event.target===this)closeModal('gradeModal')">
    <div class="dash-modal" style="max-width:640px;">
        <div class="dash-modal-header">
            <div class="dash-modal-title">Grade Distribution — Detail</div>
            <button class="dash-modal-close" onclick="closeModal('gradeModal')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="dash-modal-body">
            @php $totalGrades = array_sum($grade_distribution ?? []); @endphp
            <div class="info-grid mb-4">
                <div class="info-kpi"><div class="info-kpi-label">Total Scores</div><div class="info-kpi-value">{{ number_format($totalGrades) }}</div></div>
                <div class="info-kpi"><div class="info-kpi-label">Pass (A1-C6)</div><div class="info-kpi-value" style="color:#16a34a;">{{ number_format(array_sum(array_filter($grade_distribution??[], fn($v,$k)=>in_array($k,['A1','B2','B3','C4','C5','C6']),ARRAY_FILTER_USE_BOTH))) }}</div></div>
                <div class="info-kpi"><div class="info-kpi-label">Fail (D7-F9)</div><div class="info-kpi-value" style="color:#dc2626;">{{ number_format(array_sum(array_filter($grade_distribution??[], fn($v,$k)=>in_array($k,['D7','E8','F9']),ARRAY_FILTER_USE_BOTH))) }}</div></div>
            </div>
            <div style="height:300px;"><canvas id="gradeExpandChart" role="img" aria-label="Expanded grade distribution">Grade distribution expanded view</canvas></div>
        </div>
    </div>
</div>

{{-- Attendance Modal --}}
<div class="dash-modal-overlay" id="attendanceModal" onclick="if(event.target===this)closeModal('attendanceModal')">
    <div class="dash-modal" style="max-width:640px;">
        <div class="dash-modal-header">
            <div class="dash-modal-title">Attendance Summary</div>
            <button class="dash-modal-close" onclick="closeModal('attendanceModal')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="dash-modal-body">
            <div class="info-grid mb-4">
                <div class="info-kpi"><div class="info-kpi-label">Overall Rate</div><div class="info-kpi-value" style="color:{{ $overall_attendance_rate>=75?'#16a34a':($overall_attendance_rate>=50?'#d97706':'#dc2626') }};">{{ $overall_attendance_rate }}%</div></div>
                <div class="info-kpi"><div class="info-kpi-label">Term</div><div class="info-kpi-value" style="font-size:16px;">{{ $currentTerm?->term ?? '—' }}</div></div>
                <div class="info-kpi"><div class="info-kpi-label">Session</div><div class="info-kpi-value" style="font-size:14px;">{{ $currentSession?->session ?? '—' }}</div></div>
            </div>
            <div style="height:260px;"><canvas id="attendanceModalChart" role="img" aria-label="Attendance gauge">Attendance rate gauge</canvas></div>
            <div class="text-center mt-2" style="font-size:12px;color:#94a3b8;">
                {{ $overall_attendance_rate >= 75 ? 'Excellent attendance rate — well above threshold' : ($overall_attendance_rate >= 50 ? 'Attendance needs monitoring' : 'Critical: attendance requires immediate attention') }}
            </div>
        </div>
        <div class="dash-modal-footer">
            <a href="{{ route('attendance.my-classes') }}" class="btn btn-sm" style="background:#6366f1;color:#fff;border-radius:8px;font-size:12px;padding:7px 16px;">Manage Attendance</a>
        </div>
    </div>
</div>

{{-- Enrollment Modal --}}
<div class="dash-modal-overlay" id="enrollmentModal" onclick="if(event.target===this)closeModal('enrollmentModal')">
    <div class="dash-modal" style="max-width:720px;">
        <div class="dash-modal-header">
            <div class="dash-modal-title">Enrollment Breakdown</div>
            <button class="dash-modal-close" onclick="closeModal('enrollmentModal')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="dash-modal-body">
            <div class="info-grid mb-4">
                <div class="info-kpi"><div class="info-kpi-label">New Students</div><div class="info-kpi-value" style="color:#059669;">{{ number_format($status_counts['New Student']??0) }}</div></div>
                <div class="info-kpi"><div class="info-kpi-label">Returning</div><div class="info-kpi-value" style="color:#6366f1;">{{ number_format($status_counts['Old Student']??0) }}</div></div>
                <div class="info-kpi"><div class="info-kpi-label">Total</div><div class="info-kpi-value">{{ number_format($total_population) }}</div></div>
            </div>
            <div style="height:280px;"><canvas id="enrollModalChart" role="img" aria-label="Enrollment chart">Enrollment breakdown</canvas></div>
        </div>
    </div>
</div>

{{-- Subject Perf Modal --}}
<div class="dash-modal-overlay" id="subjectPerfModal" onclick="if(event.target===this)closeModal('subjectPerfModal')">
    <div class="dash-modal" style="max-width:900px;">
        <div class="dash-modal-header">
            <div class="dash-modal-title">Subject Performance — Full Report</div>
            <button class="dash-modal-close" onclick="closeModal('subjectPerfModal')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="dash-modal-body">
            <div style="height:300px;margin-bottom:16px;"><canvas id="subjPerfModalChart" role="img" aria-label="Subject performance bar chart">Subject performance comparison</canvas></div>
            <table class="dash-table">
                <thead><tr><th>Subject</th><th>Teacher</th><th>Avg</th><th>Max</th><th>Min</th><th>Pass Rate</th></tr></thead>
                <tbody>
                @forelse($subject_performance ?? [] as $sp2)
                @php $t3 = \App\Models\SubjectTeacher::whereHas('subject',fn($q)=>$q->where('subject',$sp2['subject_name']))->with('staff')->latest()->first(); @endphp
                <tr>
                    <td style="font-weight:500;">{{ $sp2['subject_name'] }}</td>
                    <td>
                        @if($t3?->staff)
                        <div style="display:flex;align-items:center;gap:7px;">
                            @if($t3->staff->avatar)<img src="{{ asset('storage/'.$t3->staff->avatar) }}" class="stud-av" style="width:28px;height:28px;">@else<div class="stud-av" style="width:28px;height:28px;font-size:9px;">{{ strtoupper(substr($t3->staff->name,0,2)) }}</div>@endif
                            <span style="font-size:12px;">{{ $t3->staff->name }}</span>
                        </div>
                        @else—@endif
                    </td>
                    <td><span class="score-pill {{ $sp2['avg_score']>=60?'score-B':($sp2['avg_score']>=40?'score-C':'score-D') }}">{{ $sp2['avg_score'] }}%</span></td>
                    <td style="color:#16a34a;font-weight:600;font-size:12px;">{{ $sp2['max_score'] }}%</td>
                    <td style="color:#dc2626;font-size:12px;">{{ $sp2['min_score'] }}%</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <div class="mini-prog" style="width:70px;"><div class="mini-prog-fill" style="width:{{ $sp2['pass_rate'] }}%;background:#10b981;"></div></div>
                            <span style="font-size:11px;font-weight:600;">{{ $sp2['pass_rate'] }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-3">No data available</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endhasrole

</div></div></div>

{{-- ===================================================
     SCRIPTS
     =================================================== --}}
<script src="{{ asset('theme/layouts/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
/* ======================================
   PHP DATA → JS
   ====================================== */
const DASH = {
  genderCounts: { Male: {{ $gender_counts['Male']??0 }}, Female: {{ $gender_counts['Female']??0 }} },
  staffCount:   {{ $staff_count ?? 0 }},
  classPerf:    @json(array_column($academic_performance ?? [], 'avg_score')),
  classLabels:  @json(array_column($academic_performance ?? [], 'class_name')),
  gradeDist:    @json($grade_distribution ?? []),
  trends:       { months: @json(array_column($yearly_trends??[], 'month')), students: @json(array_column($yearly_trends??[], 'students')) },
  subjects:     { names: @json(array_column($subject_performance??[], 'subject_name')), avgs: @json(array_column($subject_performance??[], 'avg_score')), pass: @json(array_column($subject_performance??[], 'pass_rate')) },
  attendance:   {{ $overall_attendance_rate ?? 0 }},
  statusCounts: { New: {{ $status_counts['New Student']??0 }}, Old: {{ $status_counts['Old Student']??0 }} },
};

/* ======================================
   COUNTER ANIMATION
   ====================================== */
function animateCounter(el, target) {
  if (target === 0) { el.textContent = '0'; return; }
  let current = 0;
  const step = Math.ceil(target / 55);
  const timer = setInterval(() => {
    current = Math.min(current + step, target);
    el.textContent = current.toLocaleString();
    if (current >= target) clearInterval(timer);
  }, 18);
}
document.querySelectorAll('.counter-value').forEach(el => {
  animateCounter(el, parseInt(el.dataset.target) || 0);
});

/* ======================================
   CHART DEFAULTS
   ====================================== */
Chart.defaults.font.family = "'DM Sans', sans-serif";
Chart.defaults.font.size   = 12;
Chart.defaults.color       = '#94a3b8';

const PALETTE = {
  indigo: '#6366f1', blue: '#3b82f6', emerald: '#10b981', rose: '#f43f5e',
  amber: '#f59e0b', purple: '#9333ea', sky: '#0ea5e9', teal: '#0d9488',
  slate: '#64748b', red: '#ef4444', green: '#16a34a', orange: '#ea580c',
};
const GRADE_COLORS = { A1:'#16a34a',B2:'#2563eb',B3:'#0284c7',C4:'#d97706',C5:'#ea580c',C6:'#dc2626',D7:'#9333ea',E8:'#be185d',F9:'#94a3b8' };

/* ======================================
   CHART INSTANCES MAP
   ====================================== */
const charts = {};

function makeChart(id, config) {
  const ctx = document.getElementById(id)?.getContext('2d');
  if (!ctx) return null;
  if (charts[id]) charts[id].destroy();
  charts[id] = new Chart(ctx, config);
  return charts[id];
}

/* ======================================
   ACADEMIC PERFORMANCE CHART
   ====================================== */
function buildAcademicChart(type = 'bar') {
  makeChart('academicPerformanceChart', {
    type: type === 'horizontalBar' ? 'bar' : 'bar',
    data: {
      labels: DASH.classLabels,
      datasets: [{
        label: 'Average Score (%)',
        data: DASH.classPerf,
        backgroundColor: DASH.classPerf.map(v => v >= 75 ? 'rgba(99,102,241,0.8)' : v >= 50 ? 'rgba(99,102,241,0.6)' : 'rgba(239,68,68,0.7)'),
        borderRadius: 8,
        borderSkipped: false,
      },{
        label: 'Pass Threshold',
        data: DASH.classLabels.map(() => 40),
        type: 'line',
        borderColor: '#ef4444',
        borderDash: [6,4],
        borderWidth: 1.5,
        pointRadius: 0,
        fill: false,
      }]
    },
    options: {
      indexAxis: type === 'horizontalBar' ? 'y' : 'x',
      responsive: true, maintainAspectRatio: false,
      plugins: { legend:{ display:false }, tooltip:{ callbacks:{ label: ctx => ` ${ctx.dataset.label}: ${ctx.raw}%` } } },
      scales: {
        y: { beginAtZero:true, max:100, grid:{ color:'rgba(0,0,0,0.04)' }, ticks:{ callback: v => v+'%' } },
        x: { grid:{ display:false } }
      },
      animation: { duration:900, easing:'easeInOutQuart' }
    }
  });
}
buildAcademicChart();

/* ======================================
   GRADE DISTRIBUTION CHART
   ====================================== */
const gradeKeys = Object.keys(DASH.gradeDist);
const gradeVals = Object.values(DASH.gradeDist);

makeChart('gradeDistributionChart', {
  type: 'bar',
  data: {
    labels: gradeKeys,
    datasets: [{
      label: 'Students',
      data: gradeVals,
      backgroundColor: gradeKeys.map(g => GRADE_COLORS[g] || '#94a3b8'),
      borderRadius: 6,
      borderSkipped: false,
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend:{ display:false }, tooltip:{ callbacks:{ label: ctx => ` ${ctx.raw} students` } } },
    scales: {
      y: { beginAtZero:true, grid:{ color:'rgba(0,0,0,0.04)' } },
      x: { grid:{ display:false } }
    },
    animation: { duration:1000 }
  }
});

/* ======================================
   POPULATION DOUGHNUT
   ====================================== */
makeChart('populationChart', {
  type: 'doughnut',
  data: {
    labels: ['Male Students','Female Students','Staff'],
    datasets: [{
      data: [DASH.genderCounts.Male, DASH.genderCounts.Female, DASH.staffCount],
      backgroundColor: ['#0ea5e9','#f43f5e','#f59e0b'],
      borderColor: '#fff',
      borderWidth: 3,
      hoverOffset: 12,
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    cutout: '68%',
    plugins: {
      legend: { display:false },
      tooltip: { callbacks: { label: ctx => {
        const t = ctx.dataset.data.reduce((a,b)=>a+b,0);
        return ` ${ctx.label}: ${ctx.raw.toLocaleString()} (${t > 0 ? Math.round(ctx.raw/t*100) : 0}%)`;
      }}}
    },
    animation: { animateScale:true, animateRotate:true, duration:1200 }
  }
});

/* ======================================
   TRENDS LINE CHART
   ====================================== */
makeChart('trendsChart', {
  type: 'line',
  data: {
    labels: DASH.trends.months,
    datasets: [{
      label: 'New Students',
      data: DASH.trends.students,
      borderColor: '#6366f1',
      backgroundColor: 'rgba(99,102,241,0.08)',
      fill: true,
      tension: 0.45,
      pointBackgroundColor: '#6366f1',
      pointBorderColor: '#fff',
      pointRadius: 4,
      pointHoverRadius: 7,
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend:{ display:false }, tooltip:{ callbacks:{ label: ctx => ` ${ctx.raw} new students` } } },
    scales: {
      y: { beginAtZero:true, grid:{ color:'rgba(0,0,0,0.04)' } },
      x: { grid:{ display:false }, ticks:{ maxRotation:40, autoSkip:true } }
    },
    animation: { duration:1200 }
  }
});

/* ======================================
   CHART TOGGLE
   ====================================== */
function toggleChartView(id, type, btn) {
  document.querySelectorAll('#' + id.replace(/[^a-z]/gi,'') + 'parent .filter-btn')?.forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  buildAcademicChart(type);
}

/* ======================================
   MODAL SYSTEM
   ====================================== */
let activeModals = [];

function openModal(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.add('open');
  document.body.style.overflow = 'hidden';
  activeModals.push(id);
  setTimeout(() => initModalCharts(id), 80);
}

function closeModal(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.remove('open');
  activeModals = activeModals.filter(m => m !== id);
  if (activeModals.length === 0) document.body.style.overflow = '';
}

document.addEventListener('keydown', e => {
  if (e.key === 'Escape' && activeModals.length) closeModal(activeModals[activeModals.length-1]);
});

/* ======================================
   MODAL CHART INITIALIZERS
   ====================================== */
const modalChartInited = {};

function initModalCharts(modalId) {
  if (modalChartInited[modalId]) return;
  modalChartInited[modalId] = true;

  if (modalId === 'populationModal') {
    makeChart('popModalChart', {
      type: 'bar',
      data: {
        labels: ['Male','Female','Staff'],
        datasets: [{
          data: [DASH.genderCounts.Male, DASH.genderCounts.Female, DASH.staffCount],
          backgroundColor: ['#0ea5e9','#f43f5e','#f59e0b'],
          borderRadius: 8,
        }]
      },
      options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } }, scales:{ x:{ grid:{ display:false } } }, animation:{ duration:700 } }
    });
  }

  if (modalId === 'genderModal') {
    makeChart('genderModalChart', {
      type: 'pie',
      data: {
        labels: ['Male Students','Female Students'],
        datasets: [{
          data: [DASH.genderCounts.Male, DASH.genderCounts.Female],
          backgroundColor: ['#0ea5e9','#f43f5e'],
          borderColor: '#fff',
          borderWidth: 3,
          hoverOffset: 10,
        }]
      },
      options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } }, animation:{ animateScale:true, duration:800 } }
    });
  }

  if (modalId === 'academicModal') {
    makeChart('academicModalChart', {
      type: 'bar',
      data: {
        labels: DASH.classLabels,
        datasets: [{
          label: 'Avg Score',
          data: DASH.classPerf,
          backgroundColor: DASH.classPerf.map(v => v>=75?'#16a34a':v>=50?'#6366f1':'#ef4444'),
          borderRadius: 8,
        }]
      },
      options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true,max:100 } }, animation:{ duration:700 } }
    });
    makeChart('gradeModalChart', {
      type: 'bar',
      data: {
        labels: gradeKeys,
        datasets: [{
          data: gradeVals,
          backgroundColor: gradeKeys.map(g => GRADE_COLORS[g]||'#94a3b8'),
          borderRadius: 8,
        }]
      },
      options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } }, animation:{ duration:700 } }
    });
  }

  if (modalId === 'gradeModal') {
    makeChart('gradeExpandChart', {
      type: 'bar',
      data: {
        labels: gradeKeys,
        datasets: [{
          label: 'Students',
          data: gradeVals,
          backgroundColor: gradeKeys.map(g => GRADE_COLORS[g]||'#94a3b8'),
          borderRadius: 8,
          borderSkipped: false,
        }]
      },
      options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false }, tooltip:{ callbacks:{ label: c => ` ${c.raw} students` } } }, animation:{ duration:700 } }
    });
  }

  if (modalId === 'attendanceModal') {
    const rate = DASH.attendance;
    makeChart('attendanceModalChart', {
      type: 'doughnut',
      data: {
        labels: ['Present','Absent'],
        datasets: [{
          data: [rate, 100-rate],
          backgroundColor: [rate>=75?'#16a34a':rate>=50?'#f59e0b':'#ef4444', '#f1f5f9'],
          borderColor: '#fff',
          borderWidth: 4,
          hoverOffset: 0,
        }]
      },
      options: {
        responsive:true, maintainAspectRatio:false, cutout:'75%',
        plugins: {
          legend:{ display:false },
          tooltip:{ callbacks:{ label: c => ` ${c.raw}%` } }
        },
        animation:{ animateScale:true, duration:900 }
      }
    });
  }

  if (modalId === 'enrollmentModal') {
    makeChart('enrollModalChart', {
      type: 'doughnut',
      data: {
        labels: ['New Students','Returning Students'],
        datasets: [{
          data: [DASH.statusCounts.New, DASH.statusCounts.Old],
          backgroundColor: ['#10b981','#6366f1'],
          borderColor: '#fff',
          borderWidth: 4,
          hoverOffset: 10,
        }]
      },
      options: { responsive:true, maintainAspectRatio:false, cutout:'62%', plugins:{ legend:{ display:false } }, animation:{ animateScale:true, duration:800 } }
    });
  }

  if (modalId === 'subjectPerfModal') {
    makeChart('subjPerfModalChart', {
      type: 'bar',
      data: {
        labels: DASH.subjects.names,
        datasets: [{
          label: 'Avg Score',
          data: DASH.subjects.avgs,
          backgroundColor: DASH.subjects.avgs.map(v => v>=60?'#6366f1':v>=40?'#f59e0b':'#ef4444'),
          borderRadius: 8,
        },{
          label: 'Pass Rate',
          data: DASH.subjects.pass,
          backgroundColor: 'rgba(16,185,129,0.2)',
          borderColor: '#10b981',
          borderWidth: 2,
          type: 'line',
          tension: 0.3,
          pointRadius: 4,
          yAxisID: 'y',
        }]
      },
      options: {
        responsive:true, maintainAspectRatio:false,
        plugins:{ legend:{ display:false } },
        scales: { y:{ beginAtZero:true,max:100,grid:{ color:'rgba(0,0,0,0.04)' } }, x:{ grid:{ display:false } } },
        animation:{ duration:700 }
      }
    });
  }
}

/* ======================================
   MODAL TAB SWITCHING
   ====================================== */
function switchTab(groupId, panelId) {
  const tabs = document.querySelectorAll(`#${groupId} .modal-tab`);
  const panels = document.querySelectorAll('.modal-tab-panel');

  tabs.forEach((t, i) => {
    const isActive = t.getAttribute('onclick').includes(panelId);
    t.classList.toggle('active', isActive);
  });
  panels.forEach(p => p.classList.toggle('active', p.id === panelId));
}
</script>

@endsection
