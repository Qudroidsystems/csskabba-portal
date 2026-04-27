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

/* ── Hero ── */
.pay-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--pay-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.pay-hero::before {
    content: '';
    position: absolute; top: -60px; right: -60px;
    width: 220px; height: 220px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
}
.pay-hero h1 { font-size: 22px; font-weight: 700; color: #fff; margin: 0 0 6px; position: relative; }
.pay-hero p  { font-size: 13px; color: rgba(255,255,255,.75); margin: 0; position: relative; }

/* ── Stat cards ── */
.stat-card {
    background: #fff;
    border: 1px solid var(--pay-border);
    border-radius: var(--pay-radius);
    padding: 18px 20px;
    transition: transform .15s, box-shadow .15s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: var(--pay-shadow); }
.stat-card .stat-value { font-size: 28px; font-weight: 700; color: var(--pay-primary); }
.stat-card .stat-label { font-size: 12px; color: var(--pay-muted); margin-top: 4px; }
.stat-card .stat-icon  { font-size: 32px; opacity: .12; float: right; margin-top: -8px; }

/* ── Table ── */
.pay-table th {
    background: var(--pay-primary);
    color: #fff;
    padding: 12px 16px;
    font-weight: 600;
    font-size: 13px;
    white-space: nowrap;
}
.pay-table td {
    padding: 11px 16px;
    vertical-align: middle;
    border-bottom: 1px solid var(--pay-border);
    font-size: 13px;
}
.pay-table tr:hover td { background: #f0f9ff; }

/* ── Avatars ── */
.student-avatar {
    width: 40px; height: 40px;
    border-radius: 50%; object-fit: cover;
    border: 2px solid var(--pay-border);
}
.avatar-placeholder {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: 700; color: var(--pay-accent);
    border: 2px solid var(--pay-border);
}

/* ── Badges ── */
.badge-class {
    display: inline-flex; align-items: center; gap: 4px;
    background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0;
    padding: 3px 8px; border-radius: 20px; font-size: 11px; font-weight: 600;
}
.badge-schol {
    display: inline-flex; align-items: center; gap: 3px;
    background: #fef9c3; color: #92400e; border: 1px solid #fde68a;
    padding: 2px 7px; border-radius: 20px; font-size: 10px; font-weight: 600;
}
.badge-disc {
    display: inline-flex; align-items: center; gap: 3px;
    background: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe;
    padding: 2px 7px; border-radius: 20px; font-size: 10px; font-weight: 600;
}

/* ── DataTable search ── */
.dataTables_wrapper .dataTables_filter input {
    border: 1.5px solid var(--pay-border);
    border-radius: 8px;
    padding: 7px 14px;
    margin-left: 8px;
    font-size: 13px;
}
.dataTables_wrapper .dataTables_filter input:focus {
    border-color: var(--pay-accent);
    outline: none;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

/* ── Term/Session Modal ── */
#termSessionModal .modal-content {
    border: none;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 24px 64px rgba(0,0,0,.2);
}
.ts-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #4f46e5 100%);
    padding: 24px 28px;
    position: relative;
    overflow: hidden;
}
.ts-hero::before {
    content: '';
    position: absolute; top: -40px; right: -40px;
    width: 140px; height: 140px;
    background: rgba(255,255,255,.07);
    border-radius: 50%;
}
.ts-hero h5 { color: #fff; font-weight: 700; font-size: 16px; margin: 0; position: relative; }
.ts-hero p  { color: rgba(255,255,255,.72); font-size: 12px; margin: 5px 0 0; position: relative; }
.ts-hero .btn-close { position: absolute; top: 18px; right: 20px; filter: invert(1); opacity: .8; }

.ts-student-chip {
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 12px;
    padding: 12px 16px;
    display: flex; align-items: center; gap: 12px;
    margin-bottom: 22px;
}
.ts-chip-avatar {
    width: 42px; height: 42px;
    border-radius: 50%;
    background: linear-gradient(135deg, #dbeafe, #93c5fd);
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: 700; color: var(--pay-accent);
    flex-shrink: 0;
    border: 2px solid #bae6fd;
}
.ts-chip-name { font-size: 14px; font-weight: 700; color: var(--pay-primary); line-height: 1.3; }
.ts-chip-meta { font-size: 11px; color: var(--pay-muted); margin-top: 2px; }

.ts-label {
    font-size: 12px; font-weight: 700;
    color: #374151; margin-bottom: 7px;
    display: flex; align-items: center; gap: 6px;
    text-transform: uppercase; letter-spacing: .04em;
}
.ts-label i { color: var(--pay-accent); font-size: 14px; }

.ts-select {
    width: 100%;
    border: 1.5px solid var(--pay-border);
    border-radius: 10px;
    padding: 11px 14px;
    font-size: 13px;
    background: #fff;
    transition: border .15s, box-shadow .15s;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2.5'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
}
.ts-select:focus {
    border-color: var(--pay-accent);
    outline: none;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

.ts-btn {
    width: 100%;
    background: linear-gradient(135deg, #2563eb, #4f46e5);
    color: #fff; border: none;
    border-radius: 10px;
    padding: 13px 24px;
    font-size: 14px; font-weight: 600;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: opacity .15s, transform .1s;
    margin-top: 4px;
}
.ts-btn:hover  { opacity: .91; transform: translateY(-1px); }
.ts-btn:active { transform: translateY(0); }
.ts-btn:disabled { opacity: .6; cursor: not-allowed; transform: none; }
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
                <div class="stat-value">{{ $student->count() }}</div>
                <div class="stat-label">Total Students (Current)</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-award-line"></i></div>
                <div class="stat-value text-warning">{{ $student->where('has_scholarship', true)->count() }}</div>
                <div class="stat-label">With Scholarship</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-price-tag-3-line"></i></div>
                <div class="stat-value text-primary">{{ $student->where('has_discount', true)->count() }}</div>
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

    {{-- Student table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-semibold" style="color:var(--pay-primary)">
                <i class="ri-list-check me-2"></i>All Students
                <span class="badge bg-primary ms-2">{{ $student->count() }}</span>
            </h5>
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
                                         alt="{{ $s->firstname }}" class="student-avatar">
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
                                {{-- Opens inline modal instead of navigating away --}}
                                <button type="button"
                                        class="btn btn-sm btn-primary open-pay-modal"
                                        data-id="{{ $s->id }}"
                                        data-name="{{ $s->firstname }} {{ $s->lastname }}"
                                        data-class="{{ $s->schoolclass }} {{ $s->arm }}"
                                        data-admission="{{ $s->admissionNo }}"
                                        data-initials="{{ strtoupper(substr($s->firstname,0,1).substr($s->lastname,0,1)) }}">
                                    <i class="ri-wallet-line me-1"></i>Pay
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
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

{{-- ══════════════ TERM / SESSION MODAL ══════════════ --}}
<div class="modal fade" id="termSessionModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px">
        <div class="modal-content">

            <div class="ts-hero">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <h5><i class="ri-wallet-3-line me-2"></i>Process Payment</h5>
                <p>Choose a term and session to view and record fees.</p>
            </div>

            <div class="p-4">
                {{-- Student chip --}}
                <div class="ts-student-chip">
                    <div class="ts-chip-avatar" id="tsInitials">—</div>
                    <div>
                        <div class="ts-chip-name" id="tsName">—</div>
                        <div class="ts-chip-meta">
                            <span id="tsClass"></span>
                            <span class="mx-1 text-muted">·</span>
                            <span class="font-monospace" id="tsAdmission"></span>
                        </div>
                    </div>
                </div>

                {{-- Form --}}
                <form id="tsForm" method="GET" action="{{ route('schoolpayment.termsessionpayments') }}">
                    <input type="hidden" name="studentId" id="tsStudentId">

                    <div class="mb-3">
                        <label class="ts-label">
                            <i class="ri-calendar-check-line"></i>Term
                            <span class="text-danger ms-1">*</span>
                        </label>
                        <select name="termid" id="tsTermId" class="ts-select" required>
                            <option value="">— Select Term —</option>
                            @foreach(\App\Models\Schoolterm::orderBy('id')->get() as $term)
                                <option value="{{ $term->id }}">{{ $term->term }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="ts-label">
                            <i class="ri-time-line"></i>Session
                            <span class="text-danger ms-1">*</span>
                        </label>
                        <select name="sessionid" id="tsSessionId" class="ts-select" required>
                            <option value="">— Select Session —</option>
                            @foreach(\App\Models\Schoolsession::orderBy('id', 'desc')->get() as $session)
                                <option value="{{ $session->id }}">{{ $session->session }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="ts-btn" id="tsProceedBtn">
                        <i class="ri-search-eye-line"></i>
                        View Payment Details
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {

    // DataTable
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

    // Open modal and populate student info
    document.querySelectorAll('.open-pay-modal').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('tsStudentId').value    = this.dataset.id;
            document.getElementById('tsName').textContent   = this.dataset.name;
            document.getElementById('tsClass').textContent  = this.dataset.class;
            document.getElementById('tsAdmission').textContent = this.dataset.admission;
            document.getElementById('tsInitials').textContent  = this.dataset.initials;
            document.getElementById('tsTermId').value        = '';
            document.getElementById('tsSessionId').value     = '';
            document.getElementById('tsProceedBtn').disabled = false;
            document.getElementById('tsProceedBtn').innerHTML =
                '<i class="ri-search-eye-line"></i> View Payment Details';

            new bootstrap.Modal(document.getElementById('termSessionModal')).show();
        });
    });

    // Form validation before submit
    document.getElementById('tsForm').addEventListener('submit', function (e) {
        const termid    = document.getElementById('tsTermId').value;
        const sessionid = document.getElementById('tsSessionId').value;

        if (!termid || !sessionid) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Incomplete Selection',
                text: 'Please select both a term and a session to continue.',
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'OK'
            });
            return;
        }

        const btn = document.getElementById('tsProceedBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
    });

});
</script>
@endsection
