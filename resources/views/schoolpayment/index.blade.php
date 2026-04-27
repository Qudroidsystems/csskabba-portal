{{-- resources/views/schoolpayment/index.blade.php --}}
@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<style>
:root {
    --pay-primary:  #1e3a5f;
    --pay-accent:   #2563eb;
    --pay-success:  #16a34a;
    --pay-warning:  #d97706;
    --pay-danger:   #dc2626;
    --pay-muted:    #6b7280;
    --pay-border:   #e2e8f0;
    --pay-bg:       #f8fafc;
    --pay-radius:   12px;
    --pay-shadow:   0 2px 8px rgba(0,0,0,.08);
}

/* ── Hero ───────────────────────────────── */
.pay-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--pay-radius);
    padding: 28px 32px; margin-bottom: 24px;
    position: relative; overflow: hidden;
}
.pay-hero::before {
    content:''; position:absolute; top:-60px; right:-60px;
    width:220px; height:220px; background:rgba(255,255,255,.06); border-radius:50%;
}
.pay-hero h1 { font-size:22px; font-weight:700; color:#fff; margin:0 0 6px; position:relative; }
.pay-hero p  { font-size:13px; color:rgba(255,255,255,.75); margin:0; position:relative; }

/* ── Stat cards ─────────────────────────── */
.stat-card {
    background:#fff; border:1px solid var(--pay-border);
    border-radius: var(--pay-radius); padding:18px 20px;
    transition: transform .15s, box-shadow .15s;
}
.stat-card:hover { transform:translateY(-2px); box-shadow:var(--pay-shadow); }
.stat-card .stat-value { font-size:28px; font-weight:700; color:var(--pay-primary); }
.stat-card .stat-label { font-size:12px; color:var(--pay-muted); margin-top:4px; }
.stat-card .stat-icon  { font-size:32px; opacity:.12; float:right; margin-top:-8px; }

/* ── Search bar ─────────────────────────── */
.search-bar {
    background:#fff; border:1.5px solid var(--pay-border);
    border-radius:10px; padding:8px 14px; font-size:13px;
    transition:border .15s; min-width:260px;
}
.search-bar:focus {
    border-color:var(--pay-accent); outline:none;
    box-shadow:0 0 0 3px rgba(37,99,235,.1);
}

/* ── Table ──────────────────────────────── */
.pay-table th {
    background:var(--pay-primary); color:#fff;
    padding:12px 16px; font-weight:600; font-size:13px; white-space:nowrap;
}
.pay-table td {
    padding:11px 16px; vertical-align:middle;
    border-bottom:1px solid var(--pay-border); font-size:13px;
}
.pay-table tr:hover td { background:#f0f9ff; }

/* ── Avatar ─────────────────────────────── */
.student-avatar {
    width:40px; height:40px; border-radius:50%; object-fit:cover;
    border:2px solid var(--pay-border);
}
.avatar-placeholder {
    width:40px; height:40px; border-radius:50%;
    background:linear-gradient(135deg,#dbeafe,#bfdbfe);
    display:inline-flex; align-items:center; justify-content:center;
    font-size:14px; font-weight:700; color:var(--pay-accent);
    border:2px solid var(--pay-border);
}

/* ── Badges ─────────────────────────────── */
.badge-class {
    display:inline-flex; align-items:center; gap:4px;
    background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0;
    padding:3px 8px; border-radius:20px; font-size:11px; font-weight:600;
}
.badge-schol {
    display:inline-flex; align-items:center; gap:3px;
    background:#fef9c3; color:#92400e; border:1px solid #fde68a;
    padding:2px 7px; border-radius:20px; font-size:10px; font-weight:600;
}
.badge-disc {
    display:inline-flex; align-items:center; gap:3px;
    background:#ede9fe; color:#6d28d9; border:1px solid #ddd6fe;
    padding:2px 7px; border-radius:20px; font-size:10px; font-weight:600;
}

/* ── DataTables overrides ───────────────── */
.dataTables_wrapper .dataTables_filter input {
    border:1.5px solid var(--pay-border); border-radius:8px;
    padding:7px 14px; margin-left:8px; font-size:13px;
}
.dataTables_wrapper .dataTables_filter input:focus {
    border-color:var(--pay-accent); outline:none;
    box-shadow:0 0 0 3px rgba(37,99,235,.1);
}
.dataTables_wrapper .dataTables_length select {
    border:1.5px solid var(--pay-border); border-radius:8px;
    padding:6px 10px; margin:0 6px; font-size:13px;
}
.dataTables_wrapper .dataTables_info { font-size:13px; color:var(--pay-muted); }
.dataTables_wrapper .paginate_button { border-radius:6px !important; font-size:13px !important; padding:4px 10px !important; }
.dataTables_wrapper .paginate_button.current,
.dataTables_wrapper .paginate_button.current:hover {
    background:var(--pay-accent) !important; border-color:var(--pay-accent) !important; color:#fff !important;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- Hero --}}
    <div class="pay-hero">
        <h1><i class="ri-wallet-line me-2"></i>Student Payment Portal</h1>
        <p>Select a student to process school fee payments for the current session.</p>
    </div>

    {{-- Stat cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-group-line"></i></div>
                <div class="stat-value" id="statTotal">{{ $student->count() }}</div>
                <div class="stat-label">Total Students (Current)</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-award-line"></i></div>
                <div class="stat-value text-warning" id="statSchol">{{ $student->where('has_scholarship', true)->count() }}</div>
                <div class="stat-label">With Scholarship</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-price-tag-3-line"></i></div>
                <div class="stat-value text-primary" id="statDisc">{{ $student->where('has_discount', true)->count() }}</div>
                <div class="stat-label">With Discount</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-team-line"></i></div>
                <div class="stat-value text-success">
                    {{ $student->map(fn($s) => $s->schoolclass . ' ' . $s->arm)->unique()->count() }}
                </div>
                <div class="stat-label">Active Classes</div>
            </div>
        </div>
    </div>

    {{-- Table card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold" style="color:var(--pay-primary)">
                    <i class="ri-list-check me-2"></i>All Students
                    <span class="badge bg-primary ms-2">{{ $student->count() }}</span>
                </h5>
            </div>
        </div>
        <div class="card-body">

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table pay-table w-100 mb-0" id="studentsTable">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th width="55">Photo</th>
                            <th>Student Name</th>
                            <th>Admission No</th>
                            <th>Class</th>
                            <th>Gender</th>
                            <th>Benefits</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($student as $i => $s)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                @if($s->picture)
                                    <img src="{{ Storage::url('images/studentavatar/' . $s->picture) }}"
                                         alt="{{ $s->firstname }}"
                                         class="student-avatar">
                                @else
                                    <div class="avatar-placeholder">
                                        {{ strtoupper(substr($s->firstname, 0, 1) . substr($s->lastname, 0, 1)) }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $s->firstname }} {{ $s->lastname }}</div>
                                <div class="text-muted small">{{ $s->term }} · {{ $s->session }}</div>
                            </td>
                            <td>
                                <span class="text-muted font-monospace small">{{ $s->admissionNo }}</span>
                            </td>
                            <td>
                                <span class="badge-class">
                                    <i class="ri-building-line"></i>
                                    {{ $s->schoolclass }} {{ $s->arm }}
                                </span>
                            </td>
                            <td>
                                <span class="text-muted small">{{ $s->gender ?? '—' }}</span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @if($s->has_scholarship)
                                        <span class="badge-schol"><i class="ri-award-line"></i>Scholarship</span>
                                    @endif
                                    @if($s->has_discount)
                                        <span class="badge-disc"><i class="ri-price-tag-3-line"></i>Discount</span>
                                    @endif
                                    @if(!$s->has_scholarship && !$s->has_discount)
                                        <span class="text-muted small">—</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('schoolpayment.termsession', $s->id) }}"
                                   class="btn btn-sm btn-primary">
                                    <i class="ri-wallet-line me-1"></i>Pay
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="ri-inbox-line d-block mb-2" style="font-size:2rem;opacity:.4"></i>
                                No students found in the current session.
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
    $('#studentsTable').DataTable({
        pageLength: 25,
        order: [[2, 'asc']],
        language: {
            search: '',
            searchPlaceholder: 'Search students...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_–_END_ of _TOTAL_ students',
            infoEmpty: 'No students found',
            zeroRecords: 'No matching students',
        },
        columnDefs: [{ orderable: false, targets: [1, 6, 7] }],
    });
});
</script>
@endsection
