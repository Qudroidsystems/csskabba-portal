@extends('layouts.master')

@section('content')

{{-- ═══════════════════════════════════════════════════════════
     STYLES
═══════════════════════════════════════════════════════════ --}}
<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap');

:root {
    --p-primary:    #1e3a5f;
    --p-accent:     #2563eb;
    --p-indigo:     #4f46e5;
    --p-success:    #16a34a;
    --p-warning:    #d97706;
    --p-danger:     #dc2626;
    --p-muted:      #6b7280;
    --p-border:     #e2e8f0;
    --p-bg:         #f8fafc;
    --p-surface:    #ffffff;
    --p-radius:     12px;
    --p-shadow:     0 2px 8px rgba(0,0,0,.07);
    --p-shadow-lg:  0 8px 32px rgba(0,0,0,.12);
}

*, *::before, *::after { box-sizing: border-box; }
body { font-family: 'Plus Jakarta Sans', sans-serif; }

@keyframes fadeInUp   { from { opacity:0; transform:translateY(18px); } to { opacity:1; transform:translateY(0); } }
@keyframes fadeInDown { from { opacity:0; transform:translateY(-14px);} to { opacity:1; transform:translateY(0); } }
@keyframes scaleIn    { from { opacity:0; transform:scale(.92);       } to { opacity:1; transform:scale(1);    } }
@keyframes pulse      { 0%,100%{transform:scale(1);}50%{transform:scale(1.05);} }
@keyframes rowIn      { from{opacity:0;transform:translateX(-8px);}to{opacity:1;transform:translateX(0);} }
@keyframes badgePop   { 0%{transform:scale(0.5);}70%{transform:scale(1.15);}100%{transform:scale(1);} }

/* ── Hero ── */
.p-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 55%, #4f46e5 100%);
    border-radius: var(--p-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
    animation: fadeInDown .5s ease both;
}
.p-hero::before {
    content:''; position:absolute; top:-70px; right:-70px;
    width:240px; height:240px;
    background:rgba(255,255,255,.06); border-radius:50%;
    animation: pulse 6s ease-in-out infinite;
}
.p-hero::after {
    content:''; position:absolute; bottom:-50px; right:140px;
    width:150px; height:150px;
    background:rgba(255,255,255,.04); border-radius:50%;
}
.p-hero h1 { font-size:22px; font-weight:800; color:#fff; margin:0 0 6px; position:relative; letter-spacing:-.3px; }
.p-hero p  { font-size:13px; color:rgba(255,255,255,.75); margin:0; position:relative; }

/* ── Stat cards ── */
.p-stat-card {
    background: var(--p-surface);
    border: 1px solid var(--p-border);
    border-radius: var(--p-radius);
    padding: 18px 20px;
    transition: transform .2s, box-shadow .2s;
    animation: fadeInUp .5s ease both;
    position: relative;
    overflow: hidden;
}
.p-stat-card::before {
    content:''; position:absolute; bottom:0; left:0; right:0; height:3px;
    border-radius:0 0 var(--p-radius) var(--p-radius);
    background: linear-gradient(90deg, var(--p-accent), var(--p-indigo));
    transform: scaleX(0); transform-origin: left;
    transition: transform .3s ease;
}
.p-stat-card:hover { transform:translateY(-3px); box-shadow:var(--p-shadow-lg); }
.p-stat-card:hover::before { transform: scaleX(1); }
.p-stat-card .stat-value { font-size:28px; font-weight:800; color:var(--p-primary); line-height:1; }
.p-stat-card .stat-label { font-size:12px; color:var(--p-muted); margin-top:5px; font-weight:500; }
.p-stat-card .stat-icon  { font-size:34px; opacity:.1; float:right; margin-top:-6px; }

/* ── Filter area ── */
.p-filter-card {
    background: var(--p-surface);
    border: 1px solid var(--p-border);
    border-top: 3px solid var(--p-accent);
    border-radius: var(--p-radius);
    padding: 16px 20px;
    animation: fadeInUp .5s .1s ease both;
}
.p-input {
    border: 1.5px solid var(--p-border);
    border-radius: 9px;
    padding: 9px 14px;
    font-size: 13px;
    font-family: inherit;
    transition: border-color .2s, box-shadow .2s;
    width: 100%;
    background: #fff;
}
.p-input:focus { border-color:var(--p-accent); outline:none; box-shadow:0 0 0 3px rgba(37,99,235,.1); }
.p-input-icon-wrap { position:relative; }
.p-input-icon { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:13px; pointer-events:none; }
.p-input-icon-wrap .p-input { padding-left: 34px; }
.p-form-label {
    font-size: 11.5px; font-weight: 700; color: var(--p-muted);
    text-transform: uppercase; letter-spacing: .4px;
    display: block; margin-bottom: 5px;
}

/* ── Table card ── */
.p-table-card {
    background: var(--p-surface);
    border: 1px solid var(--p-border);
    border-radius: var(--p-radius);
    overflow: hidden;
    box-shadow: var(--p-shadow);
    animation: fadeInUp .5s .15s ease both;
}
.p-table-card .card-header {
    background: var(--p-surface);
    border-bottom: 1px solid var(--p-border);
    padding: 14px 20px;
}
.p-table thead th {
    background: var(--p-primary);
    color: #fff;
    padding: 12px 16px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    white-space: nowrap;
    border: none;
}
.p-table tbody td {
    padding: 11px 16px;
    vertical-align: middle;
    border-bottom: 1px solid var(--p-border);
    font-size: 13px;
    transition: background .12s;
}
.p-table tbody tr { animation: rowIn .3s ease both; }
.p-table tbody tr:hover td { background: #f0f9ff; }
.p-table tbody tr:last-child td { border-bottom: none; }

/* ── Avatar ── */
.p-avatar {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: 700; color: #fff;
    border: 2px solid var(--p-border);
    flex-shrink: 0;
    transition: transform .2s;
    overflow: hidden;
}
.p-avatar img { width:100%; height:100%; object-fit:cover; }
.p-avatar:hover { transform: scale(1.1); }

/* ── Badges / pills ── */
.p-pill {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; border-radius: 20px;
    font-size: 11px; font-weight: 600;
    animation: badgePop .3s ease;
    white-space: nowrap;
}
.p-pill.status-active   { background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; }
.p-pill.status-inactive { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
.p-pill.status-default  { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }
.p-pill.scholarship     { background:#fef3c7; color:#92400e; border:1px solid #fde68a; }
.p-pill.discount        { background:#dbeafe; color:#1e40af; border:1px solid #bfdbfe; }
.p-pill.none            { background:#f8fafc; color:#94a3b8; border:1px solid #e2e8f0; }

/* ── Buttons ── */
.p-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 9px 18px; border-radius: 9px;
    font-size: 13px; font-weight: 600;
    border: none; cursor: pointer;
    transition: transform .15s, box-shadow .15s, opacity .15s;
    text-decoration: none;
}
.p-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(0,0,0,.15); opacity: .92; color:#fff; }
.p-btn.primary  { background:linear-gradient(135deg,var(--p-accent),var(--p-indigo)); color:#fff; }
.p-btn.ghost    { background:#fff; color:var(--p-primary); border:1.5px solid var(--p-border); }
.p-btn.ghost:hover { color:var(--p-primary); box-shadow:none; opacity:1; background:#f8fafc; }
.p-action-btn {
    width: 30px; height: 30px;
    border-radius: 7px;
    display: inline-flex; align-items: center; justify-content: center;
    border: none; cursor: pointer;
    font-size: 13px;
    transition: transform .15s, box-shadow .15s;
    text-decoration: none;
}
.p-action-btn:hover { transform: translateY(-1px); box-shadow: 0 3px 10px rgba(0,0,0,.15); }
.p-action-btn.pay  { background:#eff6ff; color:#2563eb; }

/* ── Empty state ── */
.p-empty { text-align:center; padding:48px 24px; color: var(--p-muted); }
.p-empty i { font-size:3rem; display:block; margin-bottom:12px; opacity:.3; }

/* ── Stagger animation for rows ── */
.p-table tbody tr:nth-child(1)  { animation-delay: .03s; }
.p-table tbody tr:nth-child(2)  { animation-delay: .06s; }
.p-table tbody tr:nth-child(3)  { animation-delay: .09s; }
.p-table tbody tr:nth-child(4)  { animation-delay: .12s; }
.p-table tbody tr:nth-child(5)  { animation-delay: .15s; }
.p-table tbody tr:nth-child(6)  { animation-delay: .18s; }
.p-table tbody tr:nth-child(7)  { animation-delay: .21s; }
.p-table tbody tr:nth-child(8)  { animation-delay: .24s; }
.p-table tbody tr:nth-child(9)  { animation-delay: .27s; }
.p-table tbody tr:nth-child(10) { animation-delay: .30s; }
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- Breadcrumb --}}
    <div class="row mb-1" style="animation:fadeInDown .4s ease both;">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb m-0 small">
                    <li class="breadcrumb-item"><a href="#" style="color:var(--p-accent)">Payments</a></li>
                    <li class="breadcrumb-item active text-muted">Student Payments</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Hero --}}
    <div class="p-hero">
        <div class="row align-items-center">
            <div class="col">
                <h1><i class="ri-wallet-3-line me-2"></i>Student Payments</h1>
                <p>Select a student to view bills, record payments, and manage scholarships or discounts.</p>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
    @if (session('error'))
    <div class="alert alert-danger border-0 rounded-3 mb-3" style="animation:fadeInDown .4s ease both;">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
    </div>
    @endif
    @if (session('success'))
    <div class="alert alert-success border-0 rounded-3 mb-3" style="animation:fadeInDown .4s ease both;">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    </div>
    @endif

    {{-- Stat Cards --}}
    @php
        $totalStudents      = $student->count();
        $scholarshipCount   = $student->where('has_scholarship', true)->count();
        $discountCount      = $student->where('has_discount', true)->count();
        $activeCount        = $student->filter(fn($s) => strtolower((string) $s->student_status) === 'active')->count();
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3" style="animation-delay:.05s">
            <div class="p-stat-card">
                <div class="stat-icon"><i class="ri-group-line"></i></div>
                <div class="stat-value">{{ $totalStudents }}</div>
                <div class="stat-label">Total Students</div>
            </div>
        </div>
        <div class="col-6 col-md-3" style="animation-delay:.08s">
            <div class="p-stat-card">
                <div class="stat-icon"><i class="ri-user-follow-line"></i></div>
                <div class="stat-value" style="color:var(--p-success)">{{ $activeCount }}</div>
                <div class="stat-label">Active Students</div>
            </div>
        </div>
        <div class="col-6 col-md-3" style="animation-delay:.11s">
            <div class="p-stat-card">
                <div class="stat-icon"><i class="ri-medal-line"></i></div>
                <div class="stat-value" style="color:var(--p-warning)">{{ $scholarshipCount }}</div>
                <div class="stat-label">On Scholarship</div>
            </div>
        </div>
        <div class="col-6 col-md-3" style="animation-delay:.14s">
            <div class="p-stat-card">
                <div class="stat-icon"><i class="ri-price-tag-3-line"></i></div>
                <div class="stat-value" style="color:var(--p-accent)">{{ $discountCount }}</div>
                <div class="stat-label">On Discount</div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="p-filter-card mb-3">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="p-form-label">Search</label>
                <div class="p-input-icon-wrap">
                    <i class="bi bi-search p-input-icon"></i>
                    <input type="text" id="liveSearch" class="p-input" placeholder="Name, admission no…">
                </div>
            </div>
            <div class="col-md-3">
                <label class="p-form-label">Class</label>
                <select id="classFilter" class="p-input">
                    <option value="">All Classes</option>
                    @foreach ($student->pluck('schoolclass')->filter()->unique()->sort() as $className)
                    <option value="{{ strtolower($className) }}">{{ $className }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="p-form-label">Status</label>
                <select id="statusFilter" class="p-input">
                    <option value="">All Statuses</option>
                    @foreach ($student->pluck('student_status')->filter()->unique()->sort() as $status)
                    <option value="{{ strtolower($status) }}">{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="button" class="p-btn ghost w-100" id="clearFilters">
                    <i class="bi bi-x-circle"></i> Clear
                </button>
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="p-table-card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div class="fw-bold" style="color:var(--p-primary);font-size:14px;">
                <i class="ri-list-check me-2" style="color:var(--p-accent)"></i>
                Students
                <span id="studentCountBadge" class="badge ms-2" style="background:var(--p-accent);font-size:11px;font-weight:600;">{{ $totalStudents }}</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table p-table mb-0" id="studentsTable">
                <thead>
                    <tr>
                        <th width="50"></th>
                        <th class="sortable" data-col="name">Name</th>
                        <th>Admission No</th>
                        <th>Class</th>
                        <th>Term / Session</th>
                        <th>Status</th>
                        <th>Fee Adjustments</th>
                        <th width="90">Action</th>
                    </tr>
                </thead>
                <tbody id="studentsTableBody">
                    @forelse ($student as $key => $s)
                    @php
                        $initials = strtoupper(substr($s->firstname ?? '', 0, 1) . substr($s->lastname ?? '', 0, 1));
                        $avatarUrl = ($s->picture && $s->picture !== 'unnamed.jpg' && $s->picture !== '')
                            ? asset('storage/images/student_avatars/' . $s->picture)
                            : null;
                        $avatarColors = ['#667eea','#f093fb','#4facfe','#43e97b','#fa709a','#30cfd0'];
                        $avatarColor = $avatarColors[$key % count($avatarColors)];
                        $statusKey = strtolower((string) $s->student_status);
                        $statusCls = $statusKey === 'active' ? 'status-active' : ($statusKey === '' ? 'status-default' : 'status-inactive');
                    @endphp
                    <tr data-id="{{ $s->id }}"
                        data-name="{{ strtolower($s->full_name ?? trim(($s->firstname ?? '').' '.($s->lastname ?? ''))) }}"
                        data-admission="{{ strtolower($s->admissionNo ?? '') }}"
                        data-class="{{ strtolower($s->schoolclass ?? '') }}"
                        data-status="{{ strtolower($s->student_status ?? '') }}">
                        <td>
                            <div class="p-avatar" style="background:linear-gradient(135deg,{{ $avatarColor }} 0%,{{ $avatarColors[($key+2)%count($avatarColors)] }} 100%)">
                                @if($avatarUrl)
                                    <img src="{{ $avatarUrl }}" alt="{{ $s->full_name }}" onerror="this.remove()">
                                @else
                                    {{ $initials ?: 'ST' }}
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold" style="color:var(--p-primary)">{{ $s->full_name ?? trim(($s->firstname ?? '').' '.($s->lastname ?? '')) }}</div>
                            <div class="text-muted small">{{ $s->gender }}</div>
                        </td>
                        <td class="text-muted small">{{ $s->admissionNo ?: '—' }}</td>
                        <td>
                            <span class="text-muted small">{{ $s->schoolclass ?: '—' }}{{ $s->arm ? ' '.$s->arm : '' }}</span>
                        </td>
                        <td class="text-muted small">{{ $s->term ?: '—' }} · {{ $s->session ?: '—' }}</td>
                        <td>
                            @if($s->student_status)
                                <span class="p-pill {{ $statusCls }}"><i class="bi bi-circle-fill" style="font-size:6px"></i>{{ $s->student_status }}</span>
                            @else
                                <span class="p-pill status-default"><i class="bi bi-dash"></i>Unknown</span>
                            @endif
                        </td>
                        <td>
                            @if($s->has_scholarship)
                                <span class="p-pill scholarship"><i class="bi bi-award-fill"></i>Scholarship</span>
                            @endif
                            @if($s->has_discount)
                                <span class="p-pill discount"><i class="bi bi-tag-fill"></i>Discount</span>
                            @endif
                            @if(!$s->has_scholarship && !$s->has_discount)
                                <span class="p-pill none"><i class="bi bi-dash"></i>None</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('schoolpayment.termSession', $s->id) }}" class="p-action-btn pay" title="Manage Payment">
                                <i class="bi bi-cash-coin"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyRow">
                        <td colspan="8">
                            <div class="p-empty">
                                <i class="ri-user-line"></i>
                                No students found for the current session
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex align-items-center justify-content-between px-4 py-3 border-top" style="background:var(--p-bg);">
            <div class="text-muted small">
                Showing <span id="showingCount" class="fw-semibold text-dark">{{ $totalStudents }}</span>
                of <span id="totalCount" class="fw-semibold text-dark">{{ $totalStudents }}</span> students
            </div>
        </div>
    </div>

</div>
</div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     SCRIPTS
══════════════════════════════════════════════════════════════ --}}
<script>
document.addEventListener('DOMContentLoaded', function() {

    const allRows = () => Array.from(document.querySelectorAll('#studentsTableBody tr[data-id]'));

    function applyFilters() {
        const search = document.getElementById('liveSearch')?.value.toLowerCase().trim() || '';
        const cls    = document.getElementById('classFilter')?.value.toLowerCase().trim() || '';
        const status = document.getElementById('statusFilter')?.value.toLowerCase().trim() || '';
        let shown = 0;

        allRows().forEach(row => {
            const name = row.dataset.name || '';
            const admission = row.dataset.admission || '';
            const rowClass = row.dataset.class || '';
            const rowStatus = row.dataset.status || '';

            const matchSearch = !search || name.includes(search) || admission.includes(search);
            const matchClass  = !cls || rowClass === cls;
            const matchStatus = !status || rowStatus === status;
            const visible = matchSearch && matchClass && matchStatus;

            row.style.display = visible ? '' : 'none';
            if (visible) shown++;
        });

        const showingSpan = document.getElementById('showingCount');
        if (showingSpan) showingSpan.textContent = shown;
        const countBadge = document.getElementById('studentCountBadge');
        if (countBadge) countBadge.textContent = shown;

        let empty = document.getElementById('noResults');
        if (shown === 0 && allRows().length > 0) {
            if (!empty) {
                empty = document.createElement('tr');
                empty.id = 'noResults';
                empty.innerHTML = `<td colspan="8"><div class="p-empty"><i class="ri-search-line"></i>No students match your filters</div></td>`;
                document.getElementById('studentsTableBody')?.appendChild(empty);
            }
        } else if (empty) empty.remove();
    }

    document.getElementById('liveSearch')?.addEventListener('input', applyFilters);
    document.getElementById('classFilter')?.addEventListener('change', applyFilters);
    document.getElementById('statusFilter')?.addEventListener('change', applyFilters);
    document.getElementById('clearFilters')?.addEventListener('click', () => {
        if (document.getElementById('liveSearch')) document.getElementById('liveSearch').value = '';
        if (document.getElementById('classFilter')) document.getElementById('classFilter').value = '';
        if (document.getElementById('statusFilter')) document.getElementById('statusFilter').value = '';
        applyFilters();
    });

    // Sort by name
    document.querySelector('[data-col="name"]')?.addEventListener('click', function() {
        this.dataset.dir = this.dataset.dir === 'asc' ? 'desc' : 'asc';
        const dir = this.dataset.dir;
        const tbody = document.getElementById('studentsTableBody');
        if (!tbody) return;
        const rows = allRows();
        rows.sort((a, b) => dir === 'asc'
            ? a.dataset.name.localeCompare(b.dataset.name)
            : b.dataset.name.localeCompare(a.dataset.name));
        rows.forEach(r => tbody.appendChild(r));
    });

    applyFilters();
});
</script>
@endsection