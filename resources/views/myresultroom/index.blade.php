{{-- resources/views/myresultroom/index.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
:root {
    --s-primary: #1e3a5f;
    --s-accent: #2563eb;
    --s-success: #16a34a;
    --s-warning: #d97706;
    --s-danger: #dc2626;
    --s-border: #e2e8f0;
    --s-radius: 12px;
}

.s-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #7c3aed 100%);
    border-radius: var(--s-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
}
.s-hero h1 {
    font-size: 22px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 6px;
}
.s-hero p {
    font-size: 13px;
    color: rgba(255,255,255,.75);
    margin: 0;
}

.stat-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: var(--s-radius);
    padding: 18px 20px;
    transition: all .15s;
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
}
.stat-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--s-primary);
}
.stat-label {
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
}

.filter-card {
    background: #fff;
    border-radius: var(--s-radius);
    border: 1px solid #e2e8f0;
    padding: 20px 24px;
    margin-bottom: 24px;
}

.table-card {
    background: #fff;
    border-radius: var(--s-radius);
    border: 1px solid #e2e8f0;
    overflow: hidden;
}
.table-card .table {
    margin-bottom: 0;
}
.table-card .table thead th {
    background: var(--s-primary);
    color: #fff;
    padding: 12px 16px;
    font-size: 13px;
    font-weight: 600;
}
.table-card .table tbody td {
    padding: 11px 16px;
    vertical-align: middle;
    font-size: 13px;
    border-bottom: 1px solid #e2e8f0;
}
.table-card .table tbody tr:hover td {
    background: #eff6ff;
}

.search-input {
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px 14px;
    font-size: 13px;
    min-width: 220px;
}
.search-input:focus {
    border-color: var(--s-accent);
    outline: none;
}
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css">

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- Hero Section --}}
    <div class="s-hero">
        <h1><i class="ri-dashboard-line me-2"></i>My Result Room</h1>
        <p>View and manage your assigned subjects, enter terminal results and mock examinations.</p>
    </div>

    {{-- Statistics Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value" id="statTotal">{{ $mysubjects->count() }}</div>
                <div class="stat-label">My Subjects</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value text-success" id="statTerminal">{{ $mysubjects->where('broadsheet_exists', true)->count() }}</div>
                <div class="stat-label">Terminal Records</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value text-warning" id="statMock">{{ $mysubjects->where('broadsheet_mock_exists', true)->count() }}</div>
                <div class="stat-label">Mock Records</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value text-info" id="statPending">{{ $mysubjects->where('broadsheet_exists', false)->count() }}</div>
                <div class="stat-label">Pending Entry</div>
            </div>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="filter-card">
        <form method="POST" action="{{ route('myresultroom.index') }}" id="filterForm">
            @csrf
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Session</label>
                    <select class="form-select" name="sessionid" id="sessionid" required>
                        <option value="">Select Session</option>
                        @foreach ($sessions as $session)
                            <option value="{{ $session->id }}" {{ request('sessionid') == $session->id ? 'selected' : '' }}>
                                {{ $session->session }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Term</label>
                    <select class="form-select" name="termid" id="termid" required>
                        <option value="">Select Term</option>
                        @foreach ($terms as $term)
                            <option value="{{ $term->id }}" {{ request('termid') == $term->id ? 'selected' : '' }}>
                                {{ $term->term }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ri-filter-3-line me-1"></i>Load Subjects
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Alert Messages --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Error!</strong> There were some problems.<br>
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
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

    {{-- Search Box --}}
    <div class="mb-3 text-end">
        <input type="text" id="searchInput" class="search-input" placeholder="Search by class, subject, or code...">
    </div>

    {{-- Subjects Table --}}
    <div class="table-card">
        <div class="table-responsive">
            <table class="table" id="subjectsTable">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Subject Code</th>
                        <th>Term</th>
                        <th>Session</th>
                        <th>Status</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mysubjects as $index => $subject)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="class-name">{{ $subject->schoolclass }}</td>
                            <td class="subject-name">{{ $subject->subject }}</td>
                            <td class="subject-code">{{ $subject->subjectcode }}</td>
                            <td>{{ $subject->term }}</td>
                            <td>{{ $subject->session }}</td>
                            <td>
                                @if ($subject->broadsheet_exists)
                                    <span class="badge bg-success">Terminal ✓</span>
                                @else
                                    <span class="badge bg-warning">Pending</span>
                                @endif
                                @if ($subject->broadsheet_mock_exists)
                                    <span class="badge bg-info ms-1">Mock ✓</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    @if ($subject->broadsheet_exists)
                                        <a href="{{ route('subjectscoresheet.index', [$subject->schoolclassid, $subject->subjectclassid, $subject->userid, $subject->termid, $subject->session_id]) }}"
                                           class="btn btn-success btn-sm" title="View Terminal Record">
                                            <i class="ri-file-list-line"></i> View
                                        </a>
                                    @else
                                        <a href="{{ route('subjectscoresheet.index', [$subject->schoolclassid, $subject->subjectclassid, $subject->userid, $subject->termid, $subject->session_id]) }}"
                                           class="btn btn-primary btn-sm" title="Enter Terminal Record">
                                            <i class="ri-edit-box-line"></i> Enter
                                        </a>
                                    @endif

                                    @if ($subject->broadsheet_mock_exists)
                                        <a href="{{ route('subjectscoresheet-mock.show', [$subject->schoolclassid, $subject->subjectclassid, $subject->userid, $subject->termid, $subject->session_id]) }}"
                                           class="btn btn-info btn-sm" title="View Mock Record">
                                            <i class="ri-eye-line"></i> Mock
                                        </a>
                                    @else
                                        <a href="{{ route('subjectscoresheet-mock.show', [$subject->schoolclassid, $subject->subjectclassid, $subject->userid, $subject->termid, $subject->session_id]) }}"
                                           class="btn btn-warning btn-sm" title="Enter Mock Record">
                                            <i class="ri-flask-line"></i> Mock
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="ri-book-open-line fs-1 d-block mb-2"></i>
                                No subjects found. Please select a session and term to load your subjects.
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Search functionality
    $('#searchInput').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();

        $('#subjectsTable tbody tr').each(function() {
            const className = $(this).find('.class-name').text().toLowerCase();
            const subjectName = $(this).find('.subject-name').text().toLowerCase();
            const subjectCode = $(this).find('.subject-code').text().toLowerCase();

            const matches = className.includes(searchTerm) ||
                           subjectName.includes(searchTerm) ||
                           subjectCode.includes(searchTerm);

            $(this).toggle(matches);
        });
    });

    // Update stats when filters are applied via form submission
    $('#filterForm').on('submit', function() {
        // Show loading state (optional)
        $(this).find('button[type="submit"]').html('<i class="ri-loader-4-line ri-spin me-1"></i> Loading...');
    });
});
</script>
@endsection
