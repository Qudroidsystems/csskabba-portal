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
.btn-create {
    background: var(--cb-teal);
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-create:hover {
    background: #0f766e;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(13,148,136,0.3);
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

/* Button Styles */
.btn-icon {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s;
}
.btn-icon i {
    font-size: 13px;
}
.btn-primary-icon {
    background: #e0f2fe;
    color: #0369a1;
}
.btn-primary-icon:hover {
    background: #0ea5e9;
    color: white;
    transform: translateY(-2px);
}
.btn-broadsheet {
    background: #dcfce7;
    color: #15803d;
}
.btn-broadsheet:hover {
    background: #22c55e;
    color: white;
    transform: translateY(-2px);
}

/* Badge */
.cb-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}
.badge-current {
    background: #dcfce7;
    color: #15803d;
}
.badge-past {
    background: #f1f5f9;
    color: #64748b;
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

/* Responsive */
@media (max-width: 768px) {
    .cb-hero { padding: 24px 20px; }
    .cb-hero h1 { font-size: 22px; }
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
    }
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

<!-- Hero Section -->
<div class="cb-hero">
    <h1><i class="ri-graduation-cap-line me-2"></i>My Classes</h1>
    <p>View your assigned classes, manage students, and access broadsheet reports.</p>
    <div class="meta-pills">
        <span class="cb-meta-pill"><i class="ri-user-line"></i>{{ Auth::user()->name }}</span>
        <span class="cb-meta-pill"><i class="ri-calendar-line"></i>{{ date('F j, Y') }}</span>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4 col-6">
        <div class="cb-stat">
            <div class="stat-accent" style="background:linear-gradient(90deg,var(--cb-navy),var(--cb-teal));"></div>
            <div class="stat-ico"><i class="ri-book-open-line"></i></div>
            <div class="stat-value">{{ $myclass->total() }}</div>
            <div class="stat-label">Current Classes</div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="cb-stat">
            <div class="stat-accent" style="background:linear-gradient(90deg,var(--cb-sky),#38bdf8);"></div>
            <div class="stat-ico"><i class="ri-group-line"></i></div>
            <div class="stat-value text-info" id="totalStudents">—</div>
            <div class="stat-label">Total Students</div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="cb-stat">
            <div class="stat-accent" style="background:linear-gradient(90deg,var(--cb-amber),#fcd34d);"></div>
            <div class="stat-ico"><i class="ri-time-line"></i></div>
            <div class="stat-value text-warning">{{ $myclasshistory->count() }}</div>
            <div class="stat-label">Class History</div>
        </div>
    </div>
</div>

<!-- Current Classes Card -->
<div class="cb-card">
    <div class="cb-card-header">
        <h5>
            <i class="ri-table-alt-line" style="color:var(--cb-teal)"></i>
            My Assigned Classes
            <span class="cb-badge badge-current ms-2">{{ $myclass->total() }} Classes</span>
        </h5>
        @can('Create my-class')
        <button type="button" class="btn-create" data-bs-toggle="modal" data-bs-target="#addClassModal">
            <i class="ri-add-line"></i> Create Class Setting
        </button>
        @endcan
    </div>

    <div style="overflow-x: auto;">
        <table class="cb-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Class</th>
                    <th>Arm</th>
                    <th>Term</th>
                    <th>Session</th>
                    <th>Students</th>
                    <th>Broadsheet</th>
                </tr>
            </thead>
            <tbody>
                @php $i = ($myclass->currentPage() - 1) * $myclass->perPage() @endphp
                @forelse ($myclass as $sc)
                <tr>
                    <td data-label="#">{{ ++$i }}</td>
                    <td data-label="Class"><strong>{{ $sc->schoolclass }}</strong></td>
                    <td data-label="Arm"><span class="cb-badge badge-current">{{ $sc->schoolarm }}</span></td>
                    <td data-label="Term">{{ $sc->term }}</td>
                    <td data-label="Session">{{ $sc->session }}</td>
                    <td data-label="Students">
                        <a href="{{ route('viewstudent', [$sc->schoolclassid, $sc->termid, $sc->sessionid]) }}" class="btn-icon btn-primary-icon">
                            <i class="ri-group-line"></i> View Students
                        </a>
                    </td>
                    <td data-label="Broadsheet">
                        <a href="{{ route('classbroadsheet.viewcomments', [$sc->schoolclassid, $sc->sessionid, $sc->termid]) }}" class="btn-icon btn-broadsheet">
                            <i class="ri-file-text-line"></i> View Broadsheet ({{ $sc->term }})
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class="ri-inbox-line"></i>
                            <h6>No Classes Assigned</h6>
                            <p>You haven't been assigned to any classes for the current session.</p>
                            <small class="text-muted">Contact the administrator to assign you to classes.</small>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($myclass->total() > 0)
    <div style="padding: 20px 24px; border-top: 1px solid var(--cb-border);">
        <div class="row align-items-center">
            <div class="col-sm">
                <div class="text-muted" style="font-size: 12px;">
                    Showing <span class="fw-semibold">{{ $myclass->count() }}</span> of <span class="fw-semibold">{{ $myclass->total() }}</span> classes
                </div>
            </div>
            <div class="col-sm-auto">
                <div class="pagination-wrap">
                    @if($myclass->onFirstPage())
                        <span class="page-item disabled">Prev</span>
                    @else
                        <a class="page-item" href="{{ $myclass->previousPageUrl() }}">Prev</a>
                    @endif

                    @foreach ($myclass->getUrlRange(1, $myclass->lastPage()) as $page => $url)
                        @if($page == $myclass->currentPage())
                            <span class="page-item active">{{ $page }}</span>
                        @else
                            <a class="page-item" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($myclass->hasMorePages())
                        <a class="page-item" href="{{ $myclass->nextPageUrl() }}">Next</a>
                    @else
                        <span class="page-item disabled">Next</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Class History Section -->
@if($myclasshistory->count() > 0)
<div class="cb-card mt-4">
    <div class="cb-card-header">
        <h5><i class="ri-history-line" style="color:var(--cb-teal)"></i> Class History</h5>
    </div>
    <div style="overflow-x: auto;">
        <table class="cb-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Class</th>
                    <th>Arm</th>
                    <th>Term</th>
                    <th>Session</th>
                    <th>Updated</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($myclasshistory as $index => $history)
                <tr>
                    <td data-label="#">{{ $index + 1 }}</td>
                    <td data-label="Class"><strong>{{ $history->schoolclass }}</strong></td>
                    <td data-label="Arm"><span class="cb-badge badge-past">{{ $history->schoolarm }}</span></td>
                    <td data-label="Term">{{ $history->term }}</td>
                    <td data-label="Session">{{ $history->session }}</td>
                    <td data-label="Updated">{{ $history->updated_at->format('d M Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

</div></div></div>

<script>
// Calculate total students
var totalStudents = 0;
@foreach($myclass as $sc)
    @php
        $studentCount = \App\Models\Studentclass::where('schoolclassid', $sc->schoolclassid)
            ->where('sessionid', $sc->sessionid)
            ->count();
    @endphp
    totalStudents += {{ $studentCount }};
@endforeach
document.getElementById('totalStudents').textContent = totalStudents;

// Toast notification helper (if needed)
function showToast(message, type) {
    // Simple alert for now, can be enhanced
    console.log(message);
}
</script>

@endsection
