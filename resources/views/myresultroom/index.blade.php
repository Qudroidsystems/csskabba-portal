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

.stat-card {
    background:#fff; border:1px solid var(--s-border);
    border-radius:var(--s-radius); padding:18px 20px;
    transition:transform .15s, box-shadow .15s;
}
.stat-card:hover { transform:translateY(-2px); box-shadow:var(--s-shadow); }
.stat-card .stat-value { font-size:28px; font-weight:700; color:var(--s-primary); }
.stat-card .stat-label { font-size:12px; color:var(--s-muted); margin-top:4px; }
.stat-card .stat-icon  { font-size:32px; opacity:.12; float:right; margin-top:-8px; }

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
    text-decoration: none;
    display: inline-block;
}
.btn-terminal {
    background:var(--s-success); color:#fff;
}
.btn-terminal:hover { background:#15803d; color:#fff; }
.btn-mock {
    background:#fef3c7; color:#b45309;
    border:1px solid #fde68a;
}
.btn-mock:hover { background:#fde68a; color:#b45309; }

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

.table-view-card {
    background:#fff; border-radius:var(--s-radius);
    border:1px solid var(--s-border);
    overflow-x: auto;
}
.table-view-card .table thead th {
    background:var(--s-primary);
    color:#fff;
    padding:12px 16px;
    font-size:13px;
    white-space: nowrap;
}
.table-view-card .table tbody td {
    padding:11px 16px;
    vertical-align: middle;
    font-size:13px;
}

.view-toggle {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
}
.view-toggle-btn {
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    border: 1.5px solid var(--s-border);
    background: #fff;
}
.view-toggle-btn.active {
    background: var(--s-accent);
    border-color: var(--s-accent);
    color: #fff;
}
.search-input {
    border:1.5px solid var(--s-border);
    border-radius:8px;
    padding:8px 14px;
    font-size:13px;
    min-width: 220px;
}
#s-page-loader {
    position:fixed; inset:0; z-index:9999;
    background:rgba(15,23,42,.55);
    backdrop-filter:blur(3px);
    display:flex; align-items:center; justify-content:center;
    opacity:0; visibility:hidden;
    transition:opacity .22s;
}
#s-page-loader.active { opacity:1; visibility:visible; }
.s-loader-card {
    background:#fff; border-radius:16px;
    padding:32px 40px; text-align:center;
}
.s-loader-spinner {
    width:52px; height:52px; margin:0 auto 16px;
    border:4px solid #e2e8f0;
    border-top-color:var(--s-accent);
    border-radius:50%;
    animation:s-spin .75s linear infinite;
}
@keyframes s-spin { to { transform:rotate(360deg); } }
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css">

<div id="s-page-loader">
    <div class="s-loader-card">
        <div class="s-loader-spinner"></div>
        <div class="s-loader-label">Loading subjects...</div>
    </div>
</div>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="s-hero">
        <h1><i class="ri-dashboard-line me-2"></i>My Result Room</h1>
        <p>View and manage your assigned subjects, enter terminal results and mock examinations.</p>
    </div>

    {{-- Stats Row --}}
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

    {{-- Filter Section --}}
    <div class="filter-card">
        <form id="filterForm" method="POST" action="{{ route('myresultroom.index') }}">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="filter-label"><i class="ri-calendar-line me-1"></i> Academic Session</label>
                    <select name="sessionid" id="sessionid" class="filter-select" required>
                        <option value="">Select Session</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}" {{ request('sessionid') == $session->id ? 'selected' : '' }}>
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
                            <option value="{{ $term->id }}" {{ request('termid') == $term->id ? 'selected' : '' }}>
                                {{ $term->term }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ri-filter-3-line me-1"></i>Load Subjects
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Error Messages --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <i class="ri-error-warning-line me-1"></i>
            @foreach($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <i class="ri-error-warning-line me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3">
            <i class="ri-checkbox-circle-line me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- View Controls --}}
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
            <input type="text" id="searchInput" class="search-input" placeholder="Search subjects...">
        </div>
    </div>

    {{-- Grid View --}}
    <div id="gridView">
        <div id="subjectsContainer">
            @if(isset($mysubjects) && $mysubjects->count() > 0)
                <div class="subjects-grid">
                    @foreach($mysubjects as $subject)
                        <div class="subject-card">
                            <div class="subject-card-header">
                                <h4 class="subject-name">{{ $subject->subject }}</h4>
                                <span class="subject-code">{{ $subject->subjectcode }}</span>
                            </div>
                            <div class="subject-card-body">
                                <div class="subject-info-row">
                                    <i class="ri-group-line"></i>
                                    <span><strong>Class:</strong> {{ $subject->schoolclass }}</span>
                                </div>
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
                                @php
                                    $terminalUrl = route('subjectscoresheet.index', [
                                        $subject->schoolclassid,
                                        $subject->subjectclassid,
                                        $subject->userid,
                                        $subject->termid,
                                        $subject->session_id
                                    ]);
                                    $mockUrl = route('subjectscoresheet-mock.show', [
                                        $subject->schoolclassid,
                                        $subject->subjectclassid,
                                        $subject->userid,
                                        $subject->termid,
                                        $subject->session_id
                                    ]);
                                @endphp
                                <a href="{{ $terminalUrl }}" class="btn-action btn-terminal">
                                    <i class="ri-file-list-line me-1"></i> {{ $subject->broadsheet_exists ? 'View Terminal' : 'Enter Terminal' }}
                                </a>
                                <a href="{{ $mockUrl }}" class="btn-action btn-mock">
                                    <i class="ri-flask-line me-1"></i> {{ $subject->broadsheet_mock_exists ? 'View Mock' : 'Enter Mock' }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="ri-book-open-line"></i>
                    <h5>No subjects assigned yet</h5>
                    <p>Please select a session and term to view your assigned subjects.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Table View --}}
    <div id="tableView" style="display: none;">
        <div class="table-view-card">
            <table class="table">
                <thead>
                    <tr><th>#</th><th>Class</th><th>Subject</th><th>Code</th><th>Term</th><th>Session</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @if(isset($mysubjects) && $mysubjects->count() > 0)
                        @foreach($mysubjects as $index => $subject)
                            @php
                                $terminalUrl = route('subjectscoresheet.index', [
                                    $subject->schoolclassid,
                                    $subject->subjectclassid,
                                    $subject->userid,
                                    $subject->termid,
                                    $subject->session_id
                                ]);
                                $mockUrl = route('subjectscoresheet-mock.show', [
                                    $subject->schoolclassid,
                                    $subject->subjectclassid,
                                    $subject->userid,
                                    $subject->termid,
                                    $subject->session_id
                                ]);
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $subject->schoolclass }}</td>
                                <td><strong>{{ $subject->subject }}</strong></td>
                                <td><span class="badge bg-primary bg-opacity-10 text-primary">{{ $subject->subjectcode }}</span></td>
                                <td>{{ $subject->term }}</td>
                                <td>{{ $subject->session }}</td>
                                <td>
                                    @if($subject->broadsheet_exists)<span class="badge bg-success">Terminal ✓</span>@endif
                                    @if($subject->broadsheet_mock_exists)<span class="badge bg-info ms-1">Mock ✓</span>@endif
                                    @if(!$subject->broadsheet_exists && !$subject->broadsheet_mock_exists)<span class="badge bg-warning">Pending</span>@endif
                                 </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ $terminalUrl }}" class="btn btn-sm {{ $subject->broadsheet_exists ? 'btn-success' : 'btn-primary' }}">
                                            <i class="ri-file-list-line"></i>
                                        </a>
                                        <a href="{{ $mockUrl }}" class="btn btn-sm {{ $subject->broadsheet_mock_exists ? 'btn-info' : 'btn-warning' }}">
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
    let currentView = 'grid';
    let currentSubjects = @json($mysubjects ?? []);

    function updateStats() {
        const total = currentSubjects.length;
        const terminal = currentSubjects.filter(s => s.broadsheet_exists).length;
        const mock = currentSubjects.filter(s => s.broadsheet_mock_exists).length;
        const pending = currentSubjects.filter(s => !s.broadsheet_exists).length;

        $('#statTotal').text(total);
        $('#statTerminal').text(terminal);
        $('#statMock').text(mock);
        $('#statPending').text(pending);
    }

    function showLoader() { $('#s-page-loader').addClass('active'); }
    function hideLoader() { $('#s-page-loader').removeClass('active'); }

    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        showLoader();

        $.ajax({
            url: '{{ route("myresultroom.index") }}',
            type: 'POST',
            data: $(this).serialize(),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(response) {
                if (response.success && response.data) {
                    currentSubjects = response.data.mysubjects || [];
                    location.reload();
                } else {
                    hideLoader();
                    alert(response.message || 'No subjects found');
                }
            },
            error: function(xhr) {
                hideLoader();
                alert('Error loading subjects');
            }
        });
    });

    function filterSubjects(searchTerm) {
        const term = searchTerm.toLowerCase();
        if (currentView === 'grid') {
            $('.subject-card').each(function() {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.includes(term));
            });
        } else {
            $('#tableView tbody tr').each(function() {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.includes(term));
            });
        }
    }

    $('#searchInput').on('keyup', function() {
        filterSubjects($(this).val());
    });

    $('#gridViewBtn').on('click', function() {
        currentView = 'grid';
        $(this).addClass('active');
        $('#tableViewBtn').removeClass('active');
        $('#gridView').show();
        $('#tableView').hide();
        filterSubjects($('#searchInput').val());
    });

    $('#tableViewBtn').on('click', function() {
        currentView = 'table';
        $(this).addClass('active');
        $('#gridViewBtn').removeClass('active');
        $('#gridView').hide();
        $('#tableView').show();
        filterSubjects($('#searchInput').val());
    });

    updateStats();
});
</script>
@endsection
