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
    padding: 28px 32px; margin-bottom: 24px;
    position: relative; overflow: hidden;
}
.s-hero::before {
    content:''; position:absolute; top:-60px; right:-60px;
    width:220px; height:220px; background:rgba(255,255,255,.06); border-radius:50%;
}
.s-hero::after {
    content:''; position:absolute; bottom:-80px; left:-30px;
    width:260px; height:260px; background:rgba(255,255,255,.03); border-radius:50%;
}
.s-hero h1 { font-size:22px; font-weight:700; color:#fff; margin:0 0 6px; position:relative; }
.s-hero p  { font-size:13px; color:rgba(255,255,255,.75); margin:0; position:relative; }

/* ── Stat cards ──────────────────────────────────────────── */
.stat-card {
    background:#fff; border:1px solid var(--s-border);
    border-radius:var(--s-radius); padding:18px 20px;
    transition:transform .15s, box-shadow .15s;
}
.stat-card:hover { transform:translateY(-2px); box-shadow:var(--s-shadow); }
.stat-card .stat-value { font-size:28px; font-weight:700; color:var(--s-primary); }
.stat-card .stat-label { font-size:12px; color:var(--s-muted); margin-top:4px; }
.stat-card .stat-icon  { font-size:32px; opacity:.12; float:right; margin-top:-8px; }

/* ── Filter Bar ─────────────────────────────────────────── */
.filter-card {
    background:#fff; border-radius:var(--s-radius);
    border:1px solid var(--s-border); padding:20px 24px;
    margin-bottom:24px;
}
.filter-label {
    font-size:13px; font-weight:600; color:#374151;
    margin-bottom:6px; display:block;
}
.filter-select {
    border:1.5px solid var(--s-border); border-radius:8px;
    padding:9px 14px; font-size:13px; width:100%;
    transition:border .15s;
}
.filter-select:focus {
    border-color:var(--s-accent); outline:none;
    box-shadow:0 0 0 3px rgba(37,99,235,.1);
}

/* ── Subject Cards Grid ────────────────────────────────── */
.subjects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 20px;
    margin-top: 8px;
}
.subject-card {
    background:#fff; border:1px solid var(--s-border);
    border-radius:var(--s-radius); overflow:hidden;
    transition:transform .18s, box-shadow .18s;
    position:relative;
}
.subject-card:hover {
    transform:translateY(-3px);
    box-shadow:0 12px 28px rgba(0,0,0,.1);
}
.subject-card-header {
    background:linear-gradient(135deg, #f1f5f9 0%, #e9eef3 100%);
    padding:14px 20px;
    border-bottom:1px solid var(--s-border);
    display:flex; justify-content:space-between; align-items:center;
}
.subject-card-header .subject-name {
    font-weight:700; color:var(--s-primary);
    font-size:16px; margin:0;
}
.subject-card-header .subject-code {
    background:#fff; padding:3px 10px;
    border-radius:20px; font-size:11px;
    font-weight:600; color:var(--s-accent);
    border:1px solid #cbd5e1;
}
.subject-card-body {
    padding:16px 20px;
}
.subject-info-row {
    display:flex; align-items:center;
    margin-bottom:12px; font-size:13px;
}
.subject-info-row i {
    width:24px; color:var(--s-muted);
    font-size:16px;
}
.subject-info-row span {
    color:#374151;
}
.subject-badge {
    display:inline-flex; align-items:center;
    padding:4px 12px; border-radius:20px;
    font-size:11px; font-weight:600;
    margin-right:8px; margin-bottom:8px;
}
.badge-terminal {
    background:#e0f2fe; color:#0369a1;
}
.badge-mock {
    background:#fef3c7; color:#b45309;
}
.badge-pending {
    background:#fed7aa; color:#9a3412;
}
.subject-card-footer {
    background:#fafcff;
    padding:14px 20px;
    border-top:1px solid var(--s-border);
    display:flex; gap:12px;
}
.btn-action {
    flex:1; padding:8px; border-radius:8px;
    font-size:12px; font-weight:600;
    text-align:center; transition:all .15s;
    cursor:pointer; border:none;
    text-decoration: none;
    display: inline-block;
}
.btn-terminal {
    background:var(--s-success); color:#fff;
}
.btn-terminal:hover { background:#15803d; transform:translateY(-1px); color:#fff; }
.btn-mock {
    background:#fef3c7; color:#b45309;
    border:1px solid #fde68a;
}
.btn-mock:hover { background:#fde68a; color:#b45309; }
.btn-view {
    background:#f1f5f9; color:#334155;
    border:1px solid #e2e8f0;
}
.btn-view:hover { background:#e2e8f0; }

/* ── Empty State ───────────────────────────────────────── */
.empty-state {
    text-align:center; padding:60px 20px;
    background:#fff; border-radius:var(--s-radius);
    border:1px solid var(--s-border);
}
.empty-state i {
    font-size:64px; color:#cbd5e1; margin-bottom:16px;
}
.empty-state h5 {
    font-size:18px; font-weight:600; color:#64748b;
    margin-bottom:8px;
}
.empty-state p {
    font-size:13px; color:var(--s-muted);
}

/* ── Table View (for when grid is too many) ───────────────── */
.table-view-card {
    background:#fff; border-radius:var(--s-radius);
    border:1px solid var(--s-border);
    overflow-x: auto;
}
.table-view-card .table {
    margin-bottom: 0;
}
.table-view-card .table thead th {
    background:var(--s-primary);
    color:#fff;
    padding:12px 16px;
    font-size:13px;
    font-weight:600;
    white-space: nowrap;
}
.table-view-card .table tbody td {
    padding:11px 16px;
    vertical-align: middle;
    font-size:13px;
    border-bottom:1px solid var(--s-border);
}
.table-view-card .table tbody tr:hover td {
    background:#eff6ff;
}
.view-toggle {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
    justify-content: flex-end;
}
.view-toggle-btn {
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    border: 1.5px solid var(--s-border);
    background: #fff;
    transition: all .15s;
}
.view-toggle-btn.active {
    background: var(--s-accent);
    border-color: var(--s-accent);
    color: #fff;
}

/* ── Toast notifications ───────────────────────────────── */
#s-toast-stack {
    position:fixed; bottom:24px; right:24px;
    z-index:10000; display:flex;
    flex-direction:column-reverse; gap:10px;
    pointer-events:none;
}
.s-toast {
    pointer-events:all;
    background:#fff; border-radius:10px;
    box-shadow:0 8px 28px rgba(0,0,0,.14);
    padding:14px 18px; min-width:280px; max-width:360px;
    display:flex; align-items:flex-start; gap:12px;
    border-left:4px solid var(--s-accent);
    transform:translateX(120%);
    transition:transform .3s cubic-bezier(.34,1.56,.64,1);
}
.s-toast.show { transform:translateX(0); }
.s-toast.s-toast-success { border-left-color:var(--s-success); }
.s-toast.s-toast-error   { border-left-color:var(--s-danger);  }
.s-toast.s-toast-warning { border-left-color:var(--s-warning); }
.s-toast .s-toast-icon { font-size:20px; line-height:1; flex-shrink:0; margin-top:1px; }

/* ── Full-page loader ──────────────────────────────────── */
#s-page-loader {
    position:fixed; inset:0; z-index:9999;
    background:rgba(15,23,42,.55);
    backdrop-filter:blur(3px);
    display:flex; flex-direction:column;
    align-items:center; justify-content:center;
    opacity:0; visibility:hidden;
    transition:opacity .22s, visibility .22s;
}
#s-page-loader.active { opacity:1; visibility:visible; }
.s-loader-card {
    background:#fff; border-radius:16px;
    padding:32px 40px; text-align:center;
    box-shadow:0 24px 64px rgba(0,0,0,.22);
    min-width:220px;
}
.s-loader-spinner {
    width:52px; height:52px; margin:0 auto 16px;
    border:4px solid #e2e8f0;
    border-top-color:var(--s-accent);
    border-radius:50%;
    animation:s-spin .75s linear infinite;
}
@keyframes s-spin { to { transform:rotate(360deg); } }
.s-loader-label { font-size:14px; font-weight:600; color:var(--s-primary); }

/* Search input */
.search-input {
    border:1.5px solid var(--s-border);
    border-radius:8px;
    padding:8px 14px;
    font-size:13px;
    min-width: 220px;
}
.search-input:focus {
    border-color:var(--s-accent);
    outline:none;
    box-shadow:0 0 0 3px rgba(37,99,235,.1);
}
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css">

{{-- Full-page loader overlay --}}
<div id="s-page-loader">
    <div class="s-loader-card">
        <div class="s-loader-spinner"></div>
        <div class="s-loader-label" id="s-loader-label">Loading subjects...</div>
    </div>
</div>

{{-- Toast notification stack --}}
<div id="s-toast-stack"></div>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- Hero Section --}}
    <div class="s-hero">
        <h1><i class="ri-dashboard-line me-2"></i>My Result Room</h1>
        <p>View and manage your assigned subjects, enter terminal results and mock examinations.</p>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-book-open-line"></i></div>
                <div class="stat-value" id="statTotal">0</div>
                <div class="stat-label">My Subjects</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-checkbox-circle-line"></i></div>
                <div class="stat-value text-success" id="statTerminal">0</div>
                <div class="stat-label">Terminal Records</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-flask-line"></i></div>
                <div class="stat-value text-warning" id="statMock">0</div>
                <div class="stat-label">Mock Records</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-time-line"></i></div>
                <div class="stat-value text-info" id="statPending">0</div>
                <div class="stat-label">Pending Entry</div>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="filter-card">
        <form id="filterForm" method="POST" action="{{ route('myresultroom.index') }}">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="filter-label"><i class="ri-calendar-line me-1"></i> Academic Session</label>
                    <select name="sessionid" id="sessionid" class="filter-select" required>
                        <option value="">Select Session</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}" {{ old('sessionid', request('sessionid')) == $session->id ? 'selected' : '' }}>
                                {{ $session->session }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="filter-label"><i class="ri-survey-line me-1"></i> Term</label>
                    <select name="termid" id="termid" class="filter-select" required>
                        <option value="">Select Term</option>
                        @foreach($terms as $term)
                            <option value="{{ $term->id }}" {{ old('termid', request('termid')) == $term->id ? 'selected' : '' }}>
                                {{ $term->term }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100" id="filterBtn">
                        <i class="ri-filter-3-line me-1"></i>Load Subjects
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Alert Messages --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <i class="ri-error-warning-line me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <i class="ri-error-warning-line me-1"></i>
            @foreach($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3">
            <i class="ri-checkbox-circle-line me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- View Toggle & Search --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="view-toggle">
            <button type="button" class="view-toggle-btn active" id="gridViewBtn">
                <i class="ri-grid-line me-1"></i> Grid View
            </button>
            <button type="button" class="view-toggle-btn" id="tableViewBtn">
                <i class="ri-table-line me-1"></i> Table View
            </button>
        </div>
        <div>
            <input type="text" id="searchInput" class="search-input" placeholder="Search by class, subject, or code...">
        </div>
    </div>

    {{-- Subjects Grid View --}}
    <div id="gridView">
        <div id="subjectsContainer">
            @if(isset($mysubjects) && $mysubjects->count() > 0)
                <div class="subjects-grid">
                    @foreach($mysubjects as $subject)
                        <div class="subject-card" data-subject-name="{{ $subject->subject }}" data-subject-code="{{ $subject->subjectcode }}" data-class="{{ $subject->schoolclass }}">
                            <div class="subject-card-header">
                                <h4 class="subject-name">{{ $subject->subject }}</h4>
                                <span class="subject-code">{{ $subject->subjectcode }}</span>
                            </div>
                            <div class="subject-card-body">
                                <div class="subject-info-row">
                                    <i class="ri-group-line"></i>
                                    <span><strong>Class:</strong> {{ $subject->schoolclass }}</span>
                                </div>
                                @if($subject->classcategories && $subject->classcategories != 'N/A')
                                <div class="subject-info-row">
                                    <i class="ri-price-tag-3-line"></i>
                                    <span><strong>Categories:</strong> {{ $subject->classcategories }}</span>
                                </div>
                                @endif
                                <div class="subject-info-row">
                                    <i class="ri-calendar-event-line"></i>
                                    <span><strong>Term:</strong> {{ $subject->term }} | <strong>Session:</strong> {{ $subject->session }}</span>
                                </div>
                                <div class="mt-2">
                                    @if($subject->broadsheet_exists)
                                        <span class="subject-badge badge-terminal"><i class="ri-check-line me-1"></i>Terminal Record</span>
                                    @else
                                        <span class="subject-badge badge-pending"><i class="ri-time-line me-1"></i>Pending Entry</span>
                                    @endif
                                    @if($subject->broadsheet_mock_exists)
                                        <span class="subject-badge badge-mock"><i class="ri-flask-line me-1"></i>Mock Record</span>
                                    @endif
                                </div>
                            </div>
                            <div class="subject-card-footer">
                                @if($subject->broadsheet_exists)
                                    <a href="{{ route('subjectscoresheet.index', [$subject->schoolclassid, $subject->subjectclassid, $subject->userid, $subject->termid, $subject->session_id]) }}"
                                       class="btn-action btn-terminal">
                                        <i class="ri-file-list-line me-1"></i> View Terminal
                                    </a>
                                @else
                                    <a href="{{ route('subjectscoresheet.index', [$subject->schoolclassid, $subject->subjectclassid, $subject->userid, $subject->termid, $subject->session_id]) }}"
                                       class="btn-action btn-terminal">
                                        <i class="ri-edit-box-line me-1"></i> Enter Terminal
                                    </a>
                                @endif

                                @if($subject->broadsheet_mock_exists)
                                    <a href="{{ route('subjectscoresheet-mock.show', [$subject->schoolclassid, $subject->subjectclassid, $subject->userid, $subject->termid, $subject->session_id]) }}"
                                       class="btn-action btn-mock">
                                        <i class="ri-eye-line me-1"></i> View Mock
                                    </a>
                                @else
                                    <a href="{{ route('subjectscoresheet-mock.show', [$subject->schoolclassid, $subject->subjectclassid, $subject->userid, $subject->termid, $subject->session_id]) }}"
                                       class="btn-action btn-mock">
                                        <i class="ri-flask-line me-1"></i> Enter Mock
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="ri-book-open-line"></i>
                    <h5>No subjects assigned yet</h5>
                    <p>Please select a session and term to view your assigned subjects.<br>If you have selected filters, no subjects are assigned to you for this period.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Subjects Table View (hidden by default) --}}
    <div id="tableView" style="display: none;">
        <div class="table-view-card">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Code</th>
                        <th>Term</th>
                        <th>Session</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tableViewBody">
                    @if(isset($mysubjects) && $mysubjects->count() > 0)
                        @foreach($mysubjects as $index => $subject)
                            <tr data-subject-name="{{ $subject->subject }}" data-subject-code="{{ $subject->subjectcode }}" data-class="{{ $subject->schoolclass }}">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $subject->schoolclass }}</td>
                                <td><strong>{{ $subject->subject }}</strong></td>
                                <td><span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1">{{ $subject->subjectcode }}</span></td>
                                <td>{{ $subject->term }}</td>
                                <td>{{ $subject->session }}</td>
                                <td>
                                    @if($subject->broadsheet_exists)
                                        <span class="badge bg-success">Terminal ✓</span>
                                    @else
                                        <span class="badge bg-warning">Pending</span>
                                    @endif
                                    @if($subject->broadsheet_mock_exists)
                                        <span class="badge bg-info ms-1">Mock ✓</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('subjectscoresheet.index', [$subject->schoolclassid, $subject->subjectclassid, $subject->userid, $subject->termid, $subject->session_id]) }}"
                                           class="btn btn-sm {{ $subject->broadsheet_exists ? 'btn-success' : 'btn-primary' }}" title="Terminal Record">
                                            <i class="ri-file-list-line"></i>
                                        </a>
                                        <a href="{{ route('subjectscoresheet-mock.show', [$subject->schoolclassid, $subject->subjectclassid, $subject->userid, $subject->termid, $subject->session_id]) }}"
                                           class="btn btn-sm {{ $subject->broadsheet_mock_exists ? 'btn-info' : 'btn-warning' }}" title="Mock Record">
                                            <i class="ri-flask-line"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

</div>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    const CSRF = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';
    let currentView = 'grid';
    let currentSubjects = @json($mysubjects ?? []);

    // Page loader
    const PageLoader = {
        show(label = 'Loading...') {
            $('#s-loader-label').text(label);
            $('#s-page-loader').addClass('active');
        },
        hide() {
            setTimeout(() => $('#s-page-loader').removeClass('active'), 200);
        }
    };

    // Toast notification
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

    // Update stat counters
    function updateStats(subjects) {
        const total = subjects.length;
        const terminal = subjects.filter(s => s.broadsheet_exists).length;
        const mock = subjects.filter(s => s.broadsheet_mock_exists).length;
        const pending = subjects.filter(s => !s.broadsheet_exists).length;

        $('#statTotal').text(total);
        $('#statTerminal').text(terminal);
        $('#statMock').text(mock);
        $('#statPending').text(pending);
    }

    // Handle filter form submission with AJAX
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();

        const sessionid = $('#sessionid').val();
        const termid = $('#termid').val();

        if (!sessionid || !termid) {
            toast('warning', 'Selection Required', 'Please select both session and term.');
            return;
        }

        PageLoader.show('Loading your subjects...');

        $.ajax({
            url: '{{ route("myresultroom.index") }}',
            type: 'POST',
            data: {
                sessionid: sessionid,
                termid: termid,
                _token: CSRF
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success && response.data) {
                    currentSubjects = response.data.mysubjects || [];
                    renderSubjects(currentSubjects);
                    updateStats(currentSubjects);
                    toast('success', 'Success', response.message || 'Subjects loaded successfully.');
                } else {
                    currentSubjects = [];
                    renderSubjects([]);
                    toast('info', 'No Subjects', response.message || 'No subjects found for the selected criteria.');
                }
                PageLoader.hide();
            },
            error: function(xhr) {
                PageLoader.hide();
                let errorMsg = 'Failed to load subjects. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                toast('error', 'Error', errorMsg);
            }
        });
    });

    // Render subjects based on current view
    function renderSubjects(subjects) {
        if (currentView === 'grid') {
            renderGridView(subjects);
        } else {
            renderTableView(subjects);
        }
        updateStats(subjects);
    }

    // Render grid view
    function renderGridView(subjects) {
        const container = $('#subjectsContainer');

        if (!subjects || subjects.length === 0) {
            container.html(`
                <div class="empty-state">
                    <i class="ri-book-open-line"></i>
                    <h5>No subjects assigned yet</h5>
                    <p>Please select a session and term to view your assigned subjects.</p>
                </div>
            `);
            return;
        }

        let html = '<div class="subjects-grid">';

        subjects.forEach(subject => {
            const terminalUrl = "{{ route('subjectscoresheet.index', ['__class__', '__subjectclass__', '__user__', '__term__', '__session__']) }}"
                .replace('__class__', subject.schoolclassid)
                .replace('__subjectclass__', subject.subjectclassid)
                .replace('__user__', subject.userid)
                .replace('__term__', subject.termid)
                .replace('__session__', subject.session_id);

            const mockUrl = "{{ route('subjectscoresheet-mock.show', ['__class__', '__subjectclass__', '__user__', '__term__', '__session__']) }}"
                .replace('__class__', subject.schoolclassid)
                .replace('__subjectclass__', subject.subjectclassid)
                .replace('__user__', subject.userid)
                .replace('__term__', subject.termid)
                .replace('__session__', subject.session_id);

            html += `
                <div class="subject-card" data-subject-name="${escapeHtml(subject.subject)}" data-subject-code="${escapeHtml(subject.subjectcode)}" data-class="${escapeHtml(subject.schoolclass)}">
                    <div class="subject-card-header">
                        <h4 class="subject-name">${escapeHtml(subject.subject)}</h4>
                        <span class="subject-code">${escapeHtml(subject.subjectcode)}</span>
                    </div>
                    <div class="subject-card-body">
                        <div class="subject-info-row">
                            <i class="ri-group-line"></i>
                            <span><strong>Class:</strong> ${escapeHtml(subject.schoolclass)}</span>
                        </div>
                        ${subject.classcategories && subject.classcategories !== 'N/A' ? `
                        <div class="subject-info-row">
                            <i class="ri-price-tag-3-line"></i>
                            <span><strong>Categories:</strong> ${escapeHtml(subject.classcategories)}</span>
                        </div>
                        ` : ''}
                        <div class="subject-info-row">
                            <i class="ri-calendar-event-line"></i>
                            <span><strong>Term:</strong> ${escapeHtml(subject.term)} | <strong>Session:</strong> ${escapeHtml(subject.session)}</span>
                        </div>
                        <div class="mt-2">
                            ${subject.broadsheet_exists ?
                                '<span class="subject-badge badge-terminal"><i class="ri-check-line me-1"></i>Terminal Record</span>' :
                                '<span class="subject-badge badge-pending"><i class="ri-time-line me-1"></i>Pending Entry</span>'}
                            ${subject.broadsheet_mock_exists ?
                                '<span class="subject-badge badge-mock"><i class="ri-flask-line me-1"></i>Mock Record</span>' : ''}
                        </div>
                    </div>
                    <div class="subject-card-footer">
                        <a href="${terminalUrl}" class="btn-action btn-terminal">
                            <i class="ri-file-list-line me-1"></i> ${subject.broadsheet_exists ? 'View Terminal' : 'Enter Terminal'}
                        </a>
                        <a href="${mockUrl}" class="btn-action btn-mock">
                            <i class="ri-flask-line me-1"></i> ${subject.broadsheet_mock_exists ? 'View Mock' : 'Enter Mock'}
                        </a>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        container.html(html);
    }

    // Render table view
    function renderTableView(subjects) {
        const tbody = $('#tableViewBody');

        if (!subjects || subjects.length === 0) {
            tbody.html('<tr><td colspan="8" class="text-center text-muted">No subjects found</td></tr>');
            return;
        }

        let html = '';
        subjects.forEach((subject, index) => {
            const terminalUrl = "{{ route('subjectscoresheet.index', ['__class__', '__subjectclass__', '__user__', '__term__', '__session__']) }}"
                .replace('__class__', subject.schoolclassid)
                .replace('__subjectclass__', subject.subjectclassid)
                .replace('__user__', subject.userid)
                .replace('__term__', subject.termid)
                .replace('__session__', subject.session_id);

            const mockUrl = "{{ route('subjectscoresheet-mock.show', ['__class__', '__subjectclass__', '__user__', '__term__', '__session__']) }}"
                .replace('__class__', subject.schoolclassid)
                .replace('__subjectclass__', subject.subjectclassid)
                .replace('__user__', subject.userid)
                .replace('__term__', subject.termid)
                .replace('__session__', subject.session_id);

            html += `
                <tr data-subject-name="${escapeHtml(subject.subject)}" data-subject-code="${escapeHtml(subject.subjectcode)}" data-class="${escapeHtml(subject.schoolclass)}">
                    <td>${index + 1}</td>
                    <td>${escapeHtml(subject.schoolclass)}</td>
                    <td><strong>${escapeHtml(subject.subject)}</strong></td>
                    <td><span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1">${escapeHtml(subject.subjectcode)}</span></td>
                    <td>${escapeHtml(subject.term)}</td>
                    <td>${escapeHtml(subject.session)}</td>
                    <td>
                        ${subject.broadsheet_exists ? '<span class="badge bg-success">Terminal ✓</span>' : '<span class="badge bg-warning">Pending</span>'}
                        ${subject.broadsheet_mock_exists ? '<span class="badge bg-info ms-1">Mock ✓</span>' : ''}
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="${terminalUrl}" class="btn btn-sm ${subject.broadsheet_exists ? 'btn-success' : 'btn-primary'}" title="Terminal Record">
                                <i class="ri-file-list-line"></i>
                            </a>
                            <a href="${mockUrl}" class="btn btn-sm ${subject.broadsheet_mock_exists ? 'btn-info' : 'btn-warning'}" title="Mock Record">
                                <i class="ri-flask-line"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            `;
        });
        tbody.html(html);
    }

    // Search functionality
    $('#searchInput').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();

        if (currentView === 'grid') {
            $('.subject-card').each(function() {
                const subjectName = $(this).data('subject-name')?.toLowerCase() || '';
                const subjectCode = $(this).data('subject-code')?.toLowerCase() || '';
                const className = $(this).data('class')?.toLowerCase() || '';

                const matches = subjectName.includes(searchTerm) ||
                               subjectCode.includes(searchTerm) ||
                               className.includes(searchTerm);

                $(this).toggle(matches);
            });
        } else {
            $('#tableViewBody tr').each(function() {
                const subjectName = $(this).data('subject-name')?.toLowerCase() || '';
                const subjectCode = $(this).data('subject-code')?.toLowerCase() || '';
                const className = $(this).data('class')?.toLowerCase() || '';

                const matches = subjectName.includes(searchTerm) ||
                               subjectCode.includes(searchTerm) ||
                               className.includes(searchTerm);

                $(this).toggle(matches);
            });
        }
    });

    // View toggle
    $('#gridViewBtn').on('click', function() {
        currentView = 'grid';
        $(this).addClass('active');
        $('#tableViewBtn').removeClass('active');
        $('#gridView').show();
        $('#tableView').hide();
        renderGridView(currentSubjects);
    });

    $('#tableViewBtn').on('click', function() {
        currentView = 'table';
        $(this).addClass('active');
        $('#gridViewBtn').removeClass('active');
        $('#gridView').hide();
        $('#tableView').show();
        renderTableView(currentSubjects);
    });

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }

    // Initial stats update
    if (currentSubjects.length > 0) {
        updateStats(currentSubjects);
    }
});
</script>
@endsection
