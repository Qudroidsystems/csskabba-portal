{{-- resources/views/admin/discount/assignments.blade.php --}}
@extends('layouts.master')

@section('content')

{{-- Select2 + SweetAlert2 CSS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

<style>
/* ── Design Tokens ─────────────────────────────────────────────────────── */
:root {
    --c-bg:       #f0f4f9;
    --c-surface:  #ffffff;
    --c-primary:  #1e3a5f;
    --c-accent:   #2563eb;
    --c-success:  #16a34a;
    --c-warning:  #d97706;
    --c-danger:   #dc2626;
    --c-muted:    #64748b;
    --c-border:   #e2e8f0;
    --c-text:     #1e293b;
    --radius-lg:  14px;
    --radius-md:  10px;
    --shadow-sm:  0 1px 4px rgba(0,0,0,.06);
    --shadow-md:  0 4px 16px rgba(0,0,0,.10);
    --shadow-lg:  0 8px 32px rgba(0,0,0,.14);
    --font-head:  'DM Sans', sans-serif;
    --font-body:  'Inter', sans-serif;
    --transition: .18s cubic-bezier(.4,0,.2,1);
}

@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap');

body { background: var(--c-bg); font-family: var(--font-body); }

/* ── Page Header ────────────────────────────────────────────────────────── */
.page-header-card {
    background: linear-gradient(135deg, var(--c-primary) 0%, #2e5f9e 100%);
    border-radius: var(--radius-lg);
    padding: 28px 32px;
    color: #fff;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.page-header-card::before {
    content: '';
    position: absolute; inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.page-header-card h4 { font-family: var(--font-head); font-weight: 700; font-size: 1.5rem; margin: 0 0 4px; }
.page-header-card .breadcrumb { margin: 0; }
.page-header-card .breadcrumb-item, .page-header-card .breadcrumb-item a { color: rgba(255,255,255,.75); font-size: 13px; }
.page-header-card .breadcrumb-item.active { color: #fff; }
.page-header-card .breadcrumb-item + .breadcrumb-item::before { color: rgba(255,255,255,.4); }

/* ── Stat Pills ─────────────────────────────────────────────────────────── */
.stat-pill {
    background: rgba(255,255,255,.15);
    border: 1px solid rgba(255,255,255,.2);
    border-radius: 8px;
    padding: 10px 18px;
    text-align: center;
    backdrop-filter: blur(8px);
}
.stat-pill .val { font-size: 1.6rem; font-weight: 700; font-family: var(--font-head); line-height: 1; }
.stat-pill .lbl { font-size: 11px; opacity: .75; margin-top: 2px; }

/* ── Filter Tabs ────────────────────────────────────────────────────────── */
.filter-tabs { border: none; gap: 6px; flex-wrap: wrap; margin-bottom: 20px; }
.filter-tabs .nav-link {
    border: 1.5px solid var(--c-border) !important;
    border-radius: 8px !important;
    color: var(--c-muted);
    font-size: 13px; font-weight: 500;
    padding: 6px 14px;
    transition: var(--transition);
    background: var(--c-surface);
}
.filter-tabs .nav-link:hover { border-color: var(--c-accent) !important; color: var(--c-accent); }
.filter-tabs .nav-link.active { background: var(--c-accent) !important; color: #fff !important; border-color: var(--c-accent) !important; }

/* ── Search + Toolbar ───────────────────────────────────────────────────── */
.toolbar-card {
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--radius-md);
    padding: 16px 20px;
    margin-bottom: 20px;
    box-shadow: var(--shadow-sm);
}
.search-wrap { position: relative; }
.search-wrap input { padding-left: 40px; border-radius: 8px; border: 1.5px solid var(--c-border); font-size: 14px; transition: var(--transition); height: 42px; }
.search-wrap input:focus { border-color: var(--c-accent); box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
.search-wrap .s-icon { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--c-muted); font-size: 16px; pointer-events: none; }
.btn-assign {
    background: linear-gradient(135deg, var(--c-accent), #1d4ed8);
    color: #fff; border: none; border-radius: 8px;
    padding: 10px 20px; font-weight: 600; font-size: 14px;
    transition: var(--transition); white-space: nowrap;
    box-shadow: 0 2px 8px rgba(37,99,235,.3);
}
.btn-assign:hover { transform: translateY(-1px); box-shadow: 0 4px 16px rgba(37,99,235,.4); color: #fff; }

/* ── Assignment Cards ───────────────────────────────────────────────────── */
.asgn-card {
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--radius-lg);
    padding: 20px;
    margin-bottom: 18px;
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}
.asgn-card::before {
    content: '';
    position: absolute; left: 0; top: 0; bottom: 0;
    width: 4px;
    background: var(--c-border);
    border-radius: 4px 0 0 4px;
    transition: var(--transition);
}
.asgn-card.status-active::before { background: var(--c-success); }
.asgn-card.status-expired::before { background: var(--c-danger); }
.asgn-card.status-removed::before { background: var(--c-muted); }
.asgn-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }

.asgn-avatar {
    width: 48px; height: 48px; border-radius: 50%;
    object-fit: cover; border: 2px solid var(--c-border);
    background: #e2e8f0; flex-shrink: 0;
}
.asgn-avatar-placeholder {
    width: 48px; height: 48px; border-radius: 50%;
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 16px; color: var(--c-accent); flex-shrink: 0;
    border: 2px solid rgba(37,99,235,.15);
}

.status-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;
    letter-spacing: .3px; text-transform: uppercase;
}
.status-active  { background: #dcfce7; color: #15803d; }
.status-expired { background: #fee2e2; color: #b91c1c; }
.status-removed { background: #f1f5f9; color: #64748b; }

.discount-chip {
    background: #eff6ff; color: var(--c-accent);
    border: 1px solid #bfdbfe; border-radius: 6px;
    font-size: 12px; font-weight: 600; padding: 2px 8px;
    display: inline-block;
}
.value-chip {
    background: #f0fdf4; color: var(--c-success);
    border: 1px solid #bbf7d0; border-radius: 6px;
    font-size: 13px; font-weight: 700; padding: 2px 10px;
}

.meta-row { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--c-muted); margin-top: 4px; }
.meta-row i { font-size: 13px; }

.btn-remove {
    background: #fff0f0; color: var(--c-danger);
    border: 1.5px solid #fca5a5; border-radius: 7px;
    font-size: 12px; font-weight: 600; padding: 6px 14px;
    transition: var(--transition);
}
.btn-remove:hover { background: var(--c-danger); color: #fff; border-color: var(--c-danger); }

/* ── Modal Overrides ────────────────────────────────────────────────────── */
.modal-content { border: none; border-radius: var(--radius-lg); overflow: hidden; }
.modal-header-assign {
    background: linear-gradient(135deg, var(--c-primary), #2e5f9e);
    color: #fff; padding: 20px 24px;
}
.modal-header-remove {
    background: linear-gradient(135deg, var(--c-danger), #ef4444);
    color: #fff; padding: 20px 24px;
}
.modal-body { padding: 24px; }
.modal-footer { border-top: 1px solid var(--c-border); padding: 16px 24px; }

/* ── Student Search Dropdown ────────────────────────────────────────────── */
.select2-container--default .select2-selection--single {
    height: 42px !important; border: 1.5px solid var(--c-border) !important;
    border-radius: 8px !important; display: flex; align-items: center;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 40px !important; padding-left: 12px !important; font-size: 14px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 40px !important;
}
.select2-container--default.select2-container--focus .select2-selection--single {
    border-color: var(--c-accent) !important;
    box-shadow: 0 0 0 3px rgba(37,99,235,.12) !important;
}
.select2-dropdown { border: 1.5px solid var(--c-border) !important; border-radius: 10px !important; box-shadow: var(--shadow-lg) !important; overflow: hidden; }
.select2-search--dropdown { padding: 10px !important; border-bottom: 1px solid var(--c-border); }
.select2-search--dropdown input { border-radius: 7px !important; border: 1.5px solid var(--c-border) !important; font-size: 13px !important; padding: 8px 12px !important; height: auto !important; }
.select2-results__option { padding: 0 !important; }
.select2-results__option--highlighted { background: #eff6ff !important; }
.select2-results__option[aria-selected=true] { background: #dbeafe !important; }

/* ── Student Result Item ────────────────────────────────────────────────── */
.s2-student-item {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 14px;
}
.s2-student-item .s2-avatar {
    width: 44px; height: 44px; border-radius: 50%;
    object-fit: cover; border: 2px solid var(--c-border); flex-shrink: 0;
}
.s2-student-item .s2-avatar-ph {
    width: 44px; height: 44px; border-radius: 50%;
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 15px; color: var(--c-accent); flex-shrink: 0;
}
.s2-student-item .s2-info { flex: 1; min-width: 0; }
.s2-student-item .s2-name { font-weight: 600; font-size: 14px; color: var(--c-text); }
.s2-student-item .s2-meta { font-size: 12px; color: var(--c-muted); margin-top: 2px; display: flex; gap: 10px; flex-wrap: wrap; }
.s2-student-item .s2-class-badge {
    background: #eff6ff; color: var(--c-accent); border: 1px solid #bfdbfe;
    border-radius: 4px; font-size: 11px; font-weight: 600; padding: 1px 7px;
}

/* ── Selected Student Preview ───────────────────────────────────────────── */
#studentPreview {
    background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
    border: 1.5px solid #bae6fd; border-radius: 10px; padding: 14px;
    margin-top: 12px; display: none;
}
#studentPreview .prev-avatar {
    width: 52px; height: 52px; border-radius: 50%;
    object-fit: cover; border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,.12); flex-shrink: 0;
}
#studentPreview .prev-avatar-ph {
    width: 52px; height: 52px; border-radius: 50%;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 18px; color: #fff; flex-shrink: 0;
    border: 3px solid #fff; box-shadow: 0 2px 8px rgba(37,99,235,.3);
}
#studentPreview .prev-name { font-weight: 700; font-size: 15px; color: var(--c-text); }
#studentPreview .prev-detail { font-size: 12px; color: var(--c-muted); margin-top: 3px; }
#studentPreview .prev-badge {
    background: #0ea5e9; color: #fff;
    border-radius: 6px; font-size: 11px; font-weight: 600; padding: 2px 8px;
}

/* ── Form Labels ────────────────────────────────────────────────────────── */
.form-label { font-weight: 600; font-size: 13px; color: var(--c-text); margin-bottom: 6px; }
.form-control, .form-select { font-size: 14px; border: 1.5px solid var(--c-border); border-radius: 8px; transition: var(--transition); }
.form-control:focus, .form-select:focus { border-color: var(--c-accent); box-shadow: 0 0 0 3px rgba(37,99,235,.12); }

/* ── Empty State ────────────────────────────────────────────────────────── */
.empty-state {
    text-align: center; padding: 60px 20px;
    background: var(--c-surface); border: 1px dashed var(--c-border);
    border-radius: var(--radius-lg);
}
.empty-state i { font-size: 3rem; color: var(--c-muted); margin-bottom: 12px; display: block; }
.empty-state h6 { color: var(--c-muted); font-weight: 500; }

/* ── Pagination ─────────────────────────────────────────────────────────── */
.pagination .page-link { border-radius: 8px; font-size: 13px; border-color: var(--c-border); color: var(--c-text); }
.pagination .page-item.active .page-link { background: var(--c-accent); border-color: var(--c-accent); }
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- ── Page Header ── --}}
    <div class="page-header-card mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4><i class="ri-shield-user-line me-2"></i>{{ $pagetitle }}</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.discount.index') }}">Discounts</a></li>
                        <li class="breadcrumb-item active">Assignments</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-3 flex-wrap">
                <div class="stat-pill">
                    <div class="val">{{ $statusCounts['active'] ?? 0 }}</div>
                    <div class="lbl">Active</div>
                </div>
                <div class="stat-pill">
                    <div class="val">{{ $statusCounts['expired'] ?? 0 }}</div>
                    <div class="lbl">Expired</div>
                </div>
                <div class="stat-pill">
                    <div class="val">{{ array_sum($statusCounts) }}</div>
                    <div class="lbl">Total</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Filter Tabs ── --}}
    <ul class="nav filter-tabs">
        <li class="nav-item">
            <a class="nav-link {{ !request('status') ? 'active' : '' }}" href="{{ route('admin.discount.assignments') }}">
                <i class="ri-apps-line me-1"></i>All
                <span class="badge bg-secondary ms-1" style="font-size:11px">{{ array_sum($statusCounts) }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('status') == 'active' ? 'active' : '' }}" href="{{ route('admin.discount.assignments', ['status' => 'active']) }}">
                <i class="ri-checkbox-circle-line me-1"></i>Active
                <span class="badge bg-success ms-1" style="font-size:11px">{{ $statusCounts['active'] ?? 0 }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('status') == 'expired' ? 'active' : '' }}" href="{{ route('admin.discount.assignments', ['status' => 'expired']) }}">
                <i class="ri-time-line me-1"></i>Expired
                <span class="badge ms-1" style="background:#fee2e2;color:#dc2626;font-size:11px">{{ $statusCounts['expired'] ?? 0 }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('status') == 'removed' ? 'active' : '' }}" href="{{ route('admin.discount.assignments', ['status' => 'removed']) }}">
                <i class="ri-close-circle-line me-1"></i>Removed
                <span class="badge bg-secondary ms-1" style="font-size:11px">{{ $statusCounts['removed'] ?? 0 }}</span>
            </a>
        </li>
    </ul>

    {{-- ── Toolbar ── --}}
    <div class="toolbar-card">
        <div class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="search-wrap">
                    <i class="ri-search-line s-icon"></i>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search by student name or admission number…">
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <button class="btn btn-assign" data-bs-toggle="modal" data-bs-target="#assignModal">
                    <i class="ri-user-add-line me-2"></i>Assign Discount
                </button>
            </div>
        </div>
    </div>

    {{-- ── Assignment Cards ── --}}
    <div class="row" id="assignmentsContainer">
        @forelse($assignments as $assignment)
        @php
            $student = $assignment->student;
            $initials = strtoupper(substr($student->firstname ?? 'S', 0, 1) . substr($student->lastname ?? '', 0, 1));
            $picUrl = $student->picture->picture ?? null;
        @endphp
        <div class="col-md-6 col-xl-4 assignment-item"
             data-search="{{ strtolower($student->firstname ?? '') }} {{ strtolower($student->lastname ?? '') }} {{ strtolower($student->admissionNo ?? '') }}">
            <div class="asgn-card status-{{ $assignment->status }}">

                {{-- Card Top: Student + Status --}}
                <div class="d-flex align-items-center gap-3 mb-3">
                    @if($picUrl)
                        <img src="{{ asset('storage/' . $picUrl) }}" alt="{{ $student->firstname }}" class="asgn-avatar">
                    @else
                        <div class="asgn-avatar-placeholder">{{ $initials }}</div>
                    @endif
                    <div class="flex-grow-1 min-width-0">
                        <div class="fw-bold text-truncate" style="font-size:15px;color:var(--c-text)">
                            {{ $student->firstname ?? '' }} {{ $student->lastname ?? '' }}
                        </div>
                        <div class="meta-row">
                            <i class="ri-id-card-line"></i>
                            <span>{{ $student->admissionNo ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <span class="status-badge status-{{ $assignment->status }}">
                        <i class="ri-{{ $assignment->status == 'active' ? 'checkbox-circle' : ($assignment->status == 'expired' ? 'time' : 'close-circle') }}-line"></i>
                        {{ ucfirst($assignment->status) }}
                    </span>
                </div>

                {{-- Divider --}}
                <hr style="border-color:var(--c-border);margin:0 0 14px">

                {{-- Discount Info --}}
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="small text-muted mb-1">Discount</div>
                        <span class="discount-chip">{{ $assignment->discount->title ?? 'N/A' }}</span>
                        <div class="small text-muted mt-1">{{ $assignment->discount->discount_no ?? '' }}</div>
                    </div>
                    <div class="text-end">
                        <div class="small text-muted mb-1">Value</div>
                        <span class="value-chip">
                            @if($assignment->value_type == 'percentage')
                                {{ $assignment->value }}%
                            @else
                                ₦{{ number_format($assignment->value, 2) }}
                            @endif
                        </span>
                    </div>
                </div>

                {{-- Dates --}}
                <div class="d-flex gap-3 mb-3">
                    <div>
                        <div class="small text-muted" style="font-size:11px">FROM</div>
                        <div style="font-size:13px;font-weight:600">
                            {{ \Carbon\Carbon::parse($assignment->effective_from)->format('d M Y') }}
                        </div>
                    </div>
                    <div style="color:var(--c-border);align-self:center;font-size:18px">→</div>
                    <div>
                        <div class="small text-muted" style="font-size:11px">TO</div>
                        <div style="font-size:13px;font-weight:600">
                            {{ $assignment->effective_to ? \Carbon\Carbon::parse($assignment->effective_to)->format('d M Y') : 'Ongoing' }}
                        </div>
                    </div>
                </div>

                @if($assignment->reason)
                <div class="mb-3" style="background:#f8fafc;border-radius:7px;padding:8px 12px;font-size:12px;color:var(--c-muted)">
                    <i class="ri-information-line me-1"></i>{{ Str::limit($assignment->reason, 80) }}
                </div>
                @endif

                @if($assignment->status == 'active')
                <button class="btn btn-remove w-100 remove-btn" data-id="{{ $assignment->id }}">
                    <i class="ri-user-unfollow-line me-1"></i>Remove Assignment
                </button>
                @endif
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="empty-state">
                <i class="ri-inbox-2-line"></i>
                <h6>No discount assignments found</h6>
                <p class="text-muted small">Use the "Assign Discount" button to create one.</p>
            </div>
        </div>
        @endforelse
    </div>

    @if($assignments->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $assignments->links() }}
    </div>
    @endif

</div>
</div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════
     ASSIGN DISCOUNT MODAL
     ════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:560px">
        <div class="modal-content">

            <div class="modal-header-assign">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:38px;height:38px;background:rgba(255,255,255,.2);border-radius:8px;display:flex;align-items:center;justify-content:center">
                        <i class="ri-user-add-line" style="font-size:18px;color:#fff"></i>
                    </div>
                    <div>
                        <h5 style="margin:0;font-family:var(--font-head);font-weight:700">Assign Discount</h5>
                        <small style="opacity:.75">Attach a discount to a student</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>

            <form id="assignForm">
                @csrf
                <div class="modal-body" style="padding:24px">

                    {{-- Step indicator --}}
                    <div class="d-flex gap-2 mb-4">
                        <div style="flex:1;height:4px;background:var(--c-accent);border-radius:4px"></div>
                        <div style="flex:1;height:4px;background:var(--c-border);border-radius:4px" id="step2bar"></div>
                    </div>

                    {{-- Discount Select --}}
                    <div class="mb-3">
                        <label class="form-label">Select Discount <span class="text-danger">*</span></label>
                        <select name="discount_id" id="discountSelect" class="form-select" required>
                            <option value="">— Choose a discount —</option>
                            @foreach($discounts ?? [] as $discount)
                                <option value="{{ $discount->id }}">
                                    {{ $discount->title }}
                                    @if($discount->value_type == 'percentage')
                                        · {{ $discount->value }}%
                                    @else
                                        · ₦{{ number_format($discount->value, 2) }}
                                    @endif
                                    ({{ $discount->discount_no }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Student Search --}}
                    <div class="mb-1">
                        <label class="form-label">Select Student <span class="text-danger">*</span></label>
                        <select name="student_id" id="studentSelect" class="form-select" required style="width:100%">
                            <option value="">— Search by name or admission no. —</option>
                        </select>
                        <small class="text-muted" style="font-size:12px">
                            <i class="ri-information-line"></i>
                            Select a discount first, then type at least 2 characters to search
                        </small>
                    </div>

                    {{-- Student Preview Card --}}
                    <div id="studentPreview">
                        <div class="d-flex align-items-center gap-3">
                            <div id="prevAvatarWrap"></div>
                            <div>
                                <div class="prev-name" id="prevName"></div>
                                <div class="prev-detail" id="prevDetail"></div>
                                <div class="mt-1" id="prevBadge"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Dates --}}
                    <div class="row g-3 mt-1">
                        <div class="col-6">
                            <label class="form-label">Effective From <span class="text-danger">*</span></label>
                            <input type="date" name="effective_from" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Effective To</label>
                            <input type="date" name="effective_to" class="form-control">
                            <div class="form-text" style="font-size:11px">Leave empty = ongoing</div>
                        </div>
                    </div>

                    {{-- Reason --}}
                    <div class="mt-3">
                        <label class="form-label">Reason <span class="text-muted">(optional)</span></label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="e.g. Scholarship recipient, staff ward…"></textarea>
                    </div>

                    <div class="alert alert-danger d-none mt-3 mb-0 py-2" id="assignErrors" style="font-size:13px;border-radius:8px"></div>
                </div>

                <div class="modal-footer" style="gap:10px">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-assign" id="submitAssignBtn" style="min-width:130px">
                        <i class="ri-save-line me-1"></i>Assign Discount
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════
     REMOVE CONFIRMATION MODAL
     ════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="removeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px">
        <div class="modal-content">
            <div class="modal-header-remove">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:38px;height:38px;background:rgba(255,255,255,.2);border-radius:8px;display:flex;align-items:center;justify-content:center">
                        <i class="ri-user-unfollow-line" style="font-size:18px;color:#fff"></i>
                    </div>
                    <div>
                        <h5 style="margin:0;font-family:var(--font-head);font-weight:700">Remove Assignment</h5>
                        <small style="opacity:.75">This action cannot be undone</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="color:var(--c-muted);font-size:14px">
                    Are you sure you want to remove this discount assignment from the student?
                    The student will <strong>lose access</strong> to the discount immediately.
                </p>
                <label class="form-label">Reason for Removal</label>
                <textarea id="removeReason" class="form-control" rows="3" placeholder="Optional — describe why this is being removed…"></textarea>
            </div>
            <div class="modal-footer gap-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRemoveBtn" style="border-radius:8px;font-weight:600">
                    <i class="ri-delete-bin-line me-1"></i>Confirm Removal
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;
let removeId = null;

/* ── Custom Select2 Result Template ──────────────────────────────────── */
function formatStudentResult(student) {
    if (student.loading) {
        return $('<div class="s2-student-item" style="padding:12px 14px;color:#64748b"><i class="ri-loader-4-line me-2"></i>Searching…</div>');
    }
    if (!student.id) return student.text;

    const d = student.studentData || {};
    const initials = ((d.firstname || 'S').charAt(0) + (d.lastname || '').charAt(0)).toUpperCase();
    const avatarHtml = d.picture_url
        ? `<img src="${d.picture_url}" alt="" class="s2-avatar">`
        : `<div class="s2-avatar-ph">${initials}</div>`;

    const classLabel = d.class_name
        ? `<span class="s2-class-badge">${d.class_name}${d.arm ? ' ' + d.arm : ''}</span>`
        : '';

    return $(`
        <div class="s2-student-item">
            ${avatarHtml}
            <div class="s2-info">
                <div class="s2-name">${d.firstname || ''} ${d.lastname || ''}</div>
                <div class="s2-meta">
                    <span><i class="ri-id-card-line" style="margin-right:3px"></i>${d.admissionNo || 'N/A'}</span>
                    ${d.gender ? `<span>${d.gender}</span>` : ''}
                    ${classLabel}
                </div>
            </div>
        </div>
    `);
}

function formatStudentSelection(student) {
    if (!student.id) return student.text;
    const d = student.studentData || {};
    return $(`<span>${d.firstname || ''} ${d.lastname || ''} &mdash; <small style="color:#64748b">${d.admissionNo || ''}</small></span>`);
}

/* ── Show student preview card after selection ───────────────────────── */
function showStudentPreview(data) {
    if (!data || !data.id) {
        $('#studentPreview').hide();
        return;
    }
    const d = data.studentData || {};
    const initials = ((d.firstname || 'S').charAt(0) + (d.lastname || '').charAt(0)).toUpperCase();

    const avatarHtml = d.picture_url
        ? `<img src="${d.picture_url}" alt="" class="prev-avatar">`
        : `<div class="prev-avatar-ph">${initials}</div>`;

    $('#prevAvatarWrap').html(avatarHtml);
    $('#prevName').text(`${d.firstname || ''} ${d.lastname || ''}`);
    $('#prevDetail').html(`<i class="ri-id-card-line me-1"></i>${d.admissionNo || 'N/A'}${d.gender ? ' · ' + d.gender : ''}`);

    const classText = d.class_name ? `${d.class_name}${d.arm ? ' ' + d.arm : ''}` : null;
    $('#prevBadge').html(classText
        ? `<span class="prev-badge"><i class="ri-book-open-line me-1"></i>${classText}</span>` + (d.session_name ? ` <span class="badge bg-light text-muted" style="font-size:11px">${d.session_name}</span>` : '')
        : ''
    );

    $('#studentPreview').fadeIn(200);
    $('#step2bar').css('background', 'var(--c-accent)');
}

$(document).ready(function () {

    /* ── Init Select2 ──────────────────────────────────────────────── */
    $('#studentSelect').select2({
        dropdownParent: $('#assignModal'),
        placeholder: 'Search by name or admission number…',
        minimumInputLength: 2,
        allowClear: true,
        templateResult: formatStudentResult,
        templateSelection: formatStudentSelection,
        ajax: {
            url: '{{ route("admin.discount.eligible-students") }}',
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return {
                    q: params.term,
                    discount_id: $('#discountSelect').val()
                };
            },
            processResults: function (data) {
                if (!data.success || !data.students) return { results: [] };
                return {
                    results: data.students.map(s => ({
                        id: s.id,
                        text: `${s.firstname} ${s.lastname} (${s.admissionNo})`,
                        studentData: s
                    }))
                };
            },
            error: function () {
                return { results: [] };
            },
            cache: true
        }
    });

    /* ── Show preview when student selected ─────────────────────────── */
    $('#studentSelect').on('select2:select', function (e) {
        showStudentPreview(e.params.data);
    });
    $('#studentSelect').on('select2:unselect select2:clear', function () {
        $('#studentPreview').hide();
        $('#step2bar').css('background', 'var(--c-border)');
    });

    /* ── Reset on discount change ────────────────────────────────────── */
    $('#discountSelect').on('change', function () {
        $('#studentSelect').val(null).trigger('change');
        $('#studentPreview').hide();
        $('#step2bar').css('background', 'var(--c-border)');
    });

    /* ── Assign Form Submit ───────────────────────────────────────────── */
    $('#assignForm').on('submit', async function (e) {
        e.preventDefault();
        const btn = $('#submitAssignBtn');
        btn.prop('disabled', true).html('<i class="ri-loader-4-line me-1"></i>Assigning…');

        const discountId = $('#discountSelect').val();
        const studentId  = $('#studentSelect').val();

        if (!discountId || !studentId) {
            $('#assignErrors').removeClass('d-none').text('Please select both a discount and a student.');
            btn.prop('disabled', false).html('<i class="ri-save-line me-1"></i>Assign Discount');
            return;
        }

        try {
            const response = await fetch('{{ route("admin.discount.assign") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: $(this).serialize()
            });
            const data = await response.json();
            if (data.success) {
                $('#assignModal').modal('hide');
                Swal.fire({
                    icon: 'success', title: 'Assigned!',
                    text: data.message,
                    confirmButtonColor: 'var(--c-accent)',
                    timer: 2200, timerProgressBar: true
                }).then(() => location.reload());
            } else {
                $('#assignErrors').removeClass('d-none')
                    .html(`<i class="ri-error-warning-line me-1"></i>${data.message}`);
            }
        } catch (error) {
            Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
        }

        btn.prop('disabled', false).html('<i class="ri-save-line me-1"></i>Assign Discount');
    });

    /* ── Reset modal on close ─────────────────────────────────────────── */
    $('#assignModal').on('hidden.bs.modal', function () {
        document.getElementById('assignForm').reset();
        $('#studentSelect').val(null).trigger('change');
        $('#studentPreview').hide();
        $('#assignErrors').addClass('d-none').text('');
        $('#step2bar').css('background', 'var(--c-border)');
    });

    /* ── Remove Button Click ─────────────────────────────────────────── */
    $(document).on('click', '.remove-btn', function () {
        removeId = $(this).data('id');
        $('#removeReason').val('');
        $('#removeModal').modal('show');
    });

    /* ── Confirm Remove ──────────────────────────────────────────────── */
    $('#confirmRemoveBtn').on('click', async function () {
        if (!removeId) return;
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="ri-loader-4-line me-1"></i>Removing…');

        try {
            const response = await fetch(`/admin/discount/assignment/${removeId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({ reason: $('#removeReason').val() })
            });
            const data = await response.json();
            $('#removeModal').modal('hide');
            if (data.success) {
                Swal.fire({
                    icon: 'success', title: 'Removed!',
                    text: data.message,
                    confirmButtonColor: 'var(--c-accent)',
                    timer: 2000, timerProgressBar: true
                }).then(() => location.reload());
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Something went wrong.', 'error');
        }

        btn.prop('disabled', false).html('<i class="ri-delete-bin-line me-1"></i>Confirm Removal');
        removeId = null;
    });

    /* ── Client-side Search Filter ───────────────────────────────────── */
    $('#searchInput').on('input', function () {
        const val = $(this).val().toLowerCase().trim();
        $('.assignment-item').each(function () {
            const txt = ($(this).data('search') || '').toLowerCase();
            $(this).toggle(!val || txt.includes(val));
        });
    });
});
</script>

@endsection
