{{-- resources/views/myresultroom/index.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
:root {
    --s-primary:  #1e3a5f;
    --s-accent:   #2563eb;
    --s-success:  #16a34a;
    --s-warning:  #d97706;
    --s-danger:   #dc2626;
    --s-muted:    #6b7280;
    --s-border:   #e2e8f0;
    --s-bg:       #f8fafc;
    --s-radius:   12px;
    --s-shadow:   0 2px 8px rgba(0,0,0,.08);
}

/* ── Hero ────────────────────────────────────────────────── */
.s-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #7c3aed 100%);
    border-radius: var(--s-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.s-hero::before {
    content:'';
    position:absolute;
    top:-60px;
    right:-60px;
    width:220px;
    height:220px;
    background:rgba(255,255,255,.06);
    border-radius:50%;
}
.s-hero::after {
    content:'';
    position:absolute;
    bottom:-80px;
    left:-30px;
    width:260px;
    height:260px;
    background:rgba(255,255,255,.03);
    border-radius:50%;
}
.s-hero h1 {
    font-size:22px;
    font-weight:700;
    color:#fff;
    margin:0 0 6px;
    position:relative;
}
.s-hero p {
    font-size:13px;
    color:rgba(255,255,255,.75);
    margin:0;
    position:relative;
}

/* ── Stat cards ──────────────────────────────────────────── */
.stat-card {
    background:#fff;
    border:1px solid var(--s-border);
    border-radius:var(--s-radius);
    padding:18px 20px;
    transition:transform .15s, box-shadow .15s;
}
.stat-card:hover {
    transform:translateY(-2px);
    box-shadow:var(--s-shadow);
}
.stat-card .stat-value {
    font-size:28px;
    font-weight:700;
    color:var(--s-primary);
}
.stat-card .stat-label {
    font-size:12px;
    color:var(--s-muted);
    margin-top:4px;
}
.stat-card .stat-icon {
    font-size:32px;
    opacity:.12;
    float:right;
    margin-top:-8px;
}

/* ── Filter card ─────────────────────────────────────────── */
.filter-card {
    background:#fff;
    border:1px solid var(--s-border);
    border-radius:var(--s-radius);
    margin-bottom:24px;
    transition:box-shadow .15s;
}
.filter-card:hover {
    box-shadow:var(--s-shadow);
}
.filter-card .card-header {
    background:var(--s-bg);
    border-bottom:1px solid var(--s-border);
    padding:14px 20px;
    font-weight:600;
    font-size:14px;
}
.filter-select {
    border:1.5px solid var(--s-border);
    border-radius:8px;
    padding:9px 14px;
    font-size:13px;
    width:100%;
    transition:border .15s, box-shadow .15s;
}
.filter-select:focus {
    border-color:var(--s-accent);
    outline:none;
    box-shadow:0 0 0 3px rgba(37,99,235,.1);
}
.filter-btn {
    background:var(--s-primary);
    border:none;
    border-radius:8px;
    padding:9px 20px;
    font-size:13px;
    font-weight:500;
    color:#fff;
    transition:background .15s, transform .1s;
}
.filter-btn:hover {
    background:#0f2b44;
    transform:translateY(-1px);
}

/* ── Table ───────────────────────────────────────────────── */
.s-table th {
    background:var(--s-primary);
    color:#fff;
    padding:12px 16px;
    font-weight:600;
    font-size:13px;
    white-space:nowrap;
}
.s-table td {
    padding:11px 16px;
    vertical-align:middle;
    border-bottom:1px solid var(--s-border);
    font-size:13px;
}
.s-table tr:hover td {
    background:#eff6ff;
}

/* ── Status badges ───────────────────────────────────────── */
.status-badge {
    display:inline-flex;
    align-items:center;
    padding:4px 12px;
    border-radius:20px;
    font-size:11px;
    font-weight:600;
}
.status-badge.completed {
    background:#e8f5e9;
    color:#2e7d32;
}
.status-badge.pending {
    background:#fff3e0;
    color:#ef6c00;
}
.status-badge.not-started {
    background:#f3e5f5;
    color:#7b1fa2;
}

/* ── Action buttons ──────────────────────────────────────── */
.action-btn {
    background:none;
    border:none;
    font-size:18px;
    width:32px;
    height:32px;
    border-radius:8px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    transition:all .15s;
    color:var(--s-muted);
    cursor:pointer;
}
.action-btn:hover {
    background:#e2e8f0;
    color:var(--s-primary);
}
.action-btn.enter:hover {
    background:#e8f5e9;
    color:#2e7d32;
}
.action-btn.mock:hover {
    background:#fff3e0;
    color:#ef6c00;
}

/* ── DataTables overrides ────────────────────────────────── */
.dataTables_wrapper .dataTables_filter input {
    border:1.5px solid var(--s-border);
    border-radius:8px;
    padding:7px 14px;
    margin-left:8px;
    font-size:13px;
    transition:border .15s;
}
.dataTables_wrapper .dataTables_filter input:focus {
    border-color:var(--s-accent);
    outline:none;
    box-shadow:0 0 0 3px rgba(37,99,235,.1);
}
.dataTables_wrapper .dataTables_length select {
    border:1.5px solid var(--s-border);
    border-radius:8px;
    padding:6px 10px;
    margin:0 6px;
    font-size:13px;
}
.dataTables_wrapper .dataTables_info {
    font-size:13px;
    color:var(--s-muted);
}
.dataTables_wrapper .paginate_button {
    border-radius:6px !important;
    font-size:13px !important;
    padding:4px 10px !important;
}
.dataTables_wrapper .paginate_button.current,
.dataTables_wrapper .paginate_button.current:hover {
    background:var(--s-accent) !important;
    border-color:var(--s-accent) !important;
    color:#fff !important;
}

/* ── Full-page loader overlay ────────────────────────────── */
#s-page-loader {
    position:fixed;
    inset:0;
    z-index:9999;
    background:rgba(15,23,42,.55);
    backdrop-filter:blur(3px);
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    opacity:0;
    visibility:hidden;
    transition:opacity .22s, visibility .22s;
}
#s-page-loader.active {
    opacity:1;
    visibility:visible;
}
.s-loader-card {
    background:#fff;
    border-radius:16px;
    padding:32px 40px;
    text-align:center;
    box-shadow:0 24px 64px rgba(0,0,0,.22);
    min-width:220px;
}
.s-loader-spinner {
    width:52px;
    height:52px;
    margin:0 auto 16px;
    border:4px solid #e2e8f0;
    border-top-color:var(--s-accent);
    border-radius:50%;
    animation:s-spin .75s linear infinite;
}
@keyframes s-spin {
    to {
        transform:rotate(360deg);
    }
}
.s-loader-label {
    font-size:14px;
    font-weight:600;
    color:var(--s-primary);
    margin-bottom:12px;
}
.s-progress-wrap {
    width:160px;
    height:5px;
    background:#e2e8f0;
    border-radius:99px;
    overflow:hidden;
    margin:0 auto;
}
.s-progress-bar {
    height:100%;
    width:0%;
    background:linear-gradient(90deg, var(--s-accent), #7c3aed);
    border-radius:99px;
    transition:width .35s ease;
}

/* ── Toast notifications ─────────────────────────────────── */
#s-toast-stack {
    position:fixed;
    bottom:24px;
    right:24px;
    z-index:10000;
    display:flex;
    flex-direction:column-reverse;
    gap:10px;
    pointer-events:none;
}
.s-toast {
    pointer-events:all;
    background:#fff;
    border-radius:10px;
    box-shadow:0 8px 28px rgba(0,0,0,.14);
    padding:14px 18px;
    min-width:280px;
    max-width:360px;
    display:flex;
    align-items:flex-start;
    gap:12px;
    border-left:4px solid var(--s-accent);
    transform:translateX(120%);
    transition:transform .3s cubic-bezier(.34,1.56,.64,1);
}
.s-toast.show {
    transform:translateX(0);
}
.s-toast.s-toast-success {
    border-left-color:var(--s-success);
}
.s-toast.s-toast-error {
    border-left-color:var(--s-danger);
}
.s-toast.s-toast-warning {
    border-left-color:var(--s-warning);
}
.s-toast .s-toast-icon {
    font-size:20px;
    line-height:1;
    flex-shrink:0;
    margin-top:1px;
}
.s-toast-success .s-toast-icon {
    color:var(--s-success);
}
.s-toast-error .s-toast-icon {
    color:var(--s-danger);
}
.s-toast-warning .s-toast-icon {
    color:var(--s-warning);
}
.s-toast .s-toast-body {
    flex:1;
}
.s-toast .s-toast-title {
    font-size:13px;
    font-weight:700;
    color:#111827;
    margin-bottom:2px;
}
.s-toast .s-toast-msg {
    font-size:12px;
    color:var(--s-muted);
    line-height:1.4;
}
.s-toast .s-toast-close {
    background:none;
    border:none;
    cursor:pointer;
    color:var(--s-muted);
    font-size:16px;
    line-height:1;
    padding:0;
    flex-shrink:0;
}

/* ── Responsive ──────────────────────────────────────────── */
@media (max-width:768px) {
    .stat-card .stat-value {
        font-size:22px;
    }
    .s-hero {
        padding:20px 24px;
    }
    .filter-card .row > div {
        margin-bottom:12px;
    }
}
</style>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

{{-- ═══ Full-page loader overlay ═══ --}}
<div id="s-page-loader">
    <div class="s-loader-card">
        <div class="s-loader-spinner"></div>
        <div class="s-loader-label" id="s-loader-label">Loading subjects…</div>
        <div class="s-progress-wrap">
            <div class="s-progress-bar" id="s-progress-bar"></div>
        </div>
    </div>
</div>

{{-- ═══ Toast notification stack ═══ --}}
<div id="s-toast-stack"></div>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- Hero Section --}}
    <div class="s-hero">
        <h1><i class="ri-file-list-line me-2"></i>My Result Room</h1>
        <p>View and manage results for subjects assigned to you across different terms and sessions.</p>
    </div>

    {{-- Statistics Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-book-open-line"></i></div>
                <div class="stat-value" id="statTotalSubjects">0</div>
                <div class="stat-label">Total Subjects</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-checkbox-circle-line"></i></div>
                <div class="stat-value text-success" id="statCompleted">0</div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-time-line"></i></div>
                <div class="stat-value text-warning" id="statPending">0</div>
                <div class="stat-label">In Progress</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-flask-line"></i></div>
                <div class="stat-value text-info" id="statMock">0</div>
                <div class="stat-label">Mock Available</div>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Whoops!</strong> There were some problems.
            <ul class="mb-0 mt-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filter Card --}}
    <div class="card filter-card">
        <div class="card-header">
            <i class="ri-filter-3-line me-2"></i>Filter Subjects
        </div>
        <div class="card-body">
            <form method="POST" id="filterForm" action="{{ route('myresultroom.index') }}" autocomplete="off">
                @csrf
                <div class="row align-items-end g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold small">Term <span class="text-danger">*</span></label>
                        <select name="termid" id="termid" class="filter-select" required>
                            <option value="">-- Select Term --</option>
                            @foreach($terms as $term)
                                <option value="{{ $term->id }}" {{ old('termid') == $term->id ? 'selected' : '' }}>{{ $term->term }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold small">Session <span class="text-danger">*</span></label>
                        <select name="sessionid" id="sessionid" class="filter-select" required>
                            <option value="">-- Select Session --</option>
                            @foreach($sessions as $session)
                                <option value="{{ $session->id }}" {{ old('sessionid') == $session->id ? 'selected' : '' }}>{{ $session->session }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="filter-btn w-100" id="filterBtn">
                            <i class="ri-search-line me-2"></i>Load
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Subjects Table Card --}}
    <div class="card border-0 shadow-sm" id="tableCard" @if($mysubjects->isEmpty()) style="display: none;" @endif>
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold" style="color:var(--s-primary)">
                    <i class="ri-list-check me-2"></i>My Assigned Subjects
                    <span class="badge bg-primary ms-2" id="totalBadge">{{ $mysubjects->count() }}</span>
                </h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table s-table w-100 mb-0" id="subjectTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Class</th>
                            <th>Category</th>
                            <th>Subject</th>
                            <th>Code</th>
                            <th>Term/Session</th>
                            <th>Status</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mysubjects as $index => $subject)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><span class="fw-semibold">{{ $subject->schoolclass ?? 'N/A' }}</span></td>
                                <td>{{ $subject->classcategories ?? 'N/A' }}</td>
                                <td>{{ $subject->subject ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2" style="border-radius:6px;font-size:12px;">
                                        {{ $subject->subjectcode ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <small>{{ $subject->term ?? '' }} {{ $subject->session ?? '' }}</small>
                                </td>
                                <td>
                                    @php
                                        $statusClass = 'not-started';
                                        $statusText = 'Not Started';
                                        if($subject->broadsheet_exists ?? false) {
                                            $statusClass = 'completed';
                                            $statusText = 'Completed';
                                        } elseif($subject->broadsheet_mock_exists ?? false) {
                                            $statusClass = 'pending';
                                            $statusText = 'Mock Ready';
                                        }
                                    @endphp
                                    <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        @if(isset($subject->id))
                                            <a href="{{ route('myresultroom.enter', $subject->id) }}" class="action-btn enter" title="Enter Results">
                                                <i class="ri-file-copy-line"></i>
                                            </a>
                                        @endif
                                        @if(($subject->broadsheet_mock_exists ?? false) && isset($subject->id))
                                            <a href="{{ route('myresultroom.mock', $subject->id) }}" class="action-btn mock" title="Mock Results">
                                                <i class="ri-survey-line"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="ri-inbox-line me-1"></i> No subjects found. Please select a term and session above.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>
</div>

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function () {
    let dataTable = null;

    // Initialize DataTable if there are rows
    function initDataTable() {
        if ($('#subjectTable tbody tr').length > 1 || ($('#subjectTable tbody tr').length === 1 && $('#subjectTable tbody tr td').attr('colspan') !== '8')) {
            if (dataTable) {
                dataTable.destroy();
            }

            dataTable = $('#subjectTable').DataTable({
                dom: "<'row align-items-center mb-3'<'col-sm-6'l><'col-sm-6 text-end'f>>" +
                     "<'row'<'col-12'tr>>" +
                     "<'row align-items-center mt-3'<'col-sm-5'i><'col-sm-7 text-end'p>>",
                language: {
                    search: '',
                    searchPlaceholder: 'Search subjects…',
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_–_END_ of _TOTAL_ entries',
                    infoEmpty: 'No subjects found',
                    zeroRecords: 'No matching subjects',
                },
                order: [[1, 'asc']],
                pageLength: 15,
                responsive: true,
            });
        }
    }

    // Update statistics from server-side data
    function updateStats() {
        @php
            $total = $mysubjects->count();
            $completed = $mysubjects->filter(function($s) { return $s->broadsheet_exists ?? false; })->count();
            $mockAvailable = $mysubjects->filter(function($s) { return $s->broadsheet_mock_exists ?? false; })->count();
            $pending = $total - $completed;
        @endphp

        $('#statTotalSubjects').text({{ $total }});
        $('#statCompleted').text({{ $completed }});
        $('#statPending').text({{ $pending }});
        $('#statMock').text({{ $mockAvailable }});
        $('#totalBadge').text({{ $total }});
    }

    // Toast notification system
    function toast(type, title, msg, duration = 4000) {
        const icons = {
            success: 'ri-checkbox-circle-fill',
            error: 'ri-close-circle-fill',
            warning: 'ri-alert-fill',
            info: 'ri-information-fill'
        };
        const id = 'toast-' + Date.now();
        const $el = $(`
            <div class="s-toast s-toast-${type}" id="${id}">
                <span class="s-toast-icon"><i class="${icons[type] || icons.info}"></i></span>
                <div class="s-toast-body">
                    <div class="s-toast-title">${title}</div>
                    ${msg ? `<div class="s-toast-msg">${msg}</div>` : ''}
                </div>
                <button class="s-toast-close" onclick="$('#${id}').remove()">×</button>
            </div>
        `);
        $('#s-toast-stack').append($el);
        setTimeout(() => $el.addClass('show'), 20);
        if (duration > 0) {
            setTimeout(() => {
                $el.removeClass('show');
                setTimeout(() => $el.remove(), 350);
            }, duration);
        }
    }

    // Page Loader
    const PageLoader = {
        _prog: 0,
        _timer: null,
        show(label = 'Processing…') {
            $('#s-loader-label').text(label);
            $('#s-progress-bar').css('width', '0%');
            $('#s-page-loader').addClass('active');
            this._prog = 0;
            this._startTicker();
        },
        _startTicker() {
            PageLoader._timer = setInterval(() => {
                if (PageLoader._prog < 85) {
                    PageLoader._prog += Math.random() * 8;
                    $('#s-progress-bar').css('width', Math.min(PageLoader._prog, 85) + '%');
                }
            }, 220);
        },
        hide() {
            clearInterval(this._timer);
            $('#s-progress-bar').css('width', '100%');
            setTimeout(() => $('#s-page-loader').removeClass('active'), 350);
        }
    };

    // Handle form submission with loading state
    $('#filterForm').on('submit', function(e) {
        const termid = $('#termid').val();
        const sessionid = $('#sessionid').val();

        if (!termid || !sessionid) {
            e.preventDefault();
            toast('warning', 'Missing Selection', 'Please select both term and session.');
            return false;
        }

        PageLoader.show('Loading subjects...');
        $('#filterBtn').html('<i class="ri-loader-4-line ri-spin me-2"></i>Loading...').prop('disabled', true);

        // Form will submit normally, loader shows while page reloads
        setTimeout(() => {
            // This ensures loader doesn't get stuck if response is slow
        }, 100);
    });

    // Show loader on page load if filtering
    @if(request()->isMethod('post') || (old('termid') && old('sessionid')))
        PageLoader.show('Loading subjects...');
        $(window).on('load', function() {
            setTimeout(function() {
                PageLoader.hide();
            }, 500);
        });
    @endif

    // Initialize
    updateStats();
    initDataTable();

    // Show success toast if coming from successful operation
    @if(session('success'))
        toast('success', 'Success', '{{ session('success') }}');
    @endif

    @if(session('error'))
        toast('error', 'Error', '{{ session('error') }}');
    @endif
});
</script>
@endsection
