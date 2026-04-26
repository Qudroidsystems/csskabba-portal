{{-- resources/views/admin/scholarship/assignments.blade.php --}}
@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<style>
:root {
    --sch-primary: #1e3a5f;
    --sch-accent:  #2563eb;
    --sch-border:  #e2e8f0;
    --sch-radius:  12px;
}

/* ── Status badges ─────────────────────────────────────── */
.status-badge { display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;letter-spacing:.3px; }
.status-active   { background:#dcfce7;color:#16a34a; }
.status-pending  { background:#fef3c7;color:#d97706; }
.status-approved { background:#dbeafe;color:#2563eb; }
.status-expired  { background:#fee2e2;color:#dc2626; }
.status-revoked  { background:#f3f4f6;color:#6b7280; }

/* ── Assign modal ──────────────────────────────────────── */
#assignModal .modal-dialog { max-width: 680px; }
#assignModal .modal-content { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,.18); }

.modal-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
    padding: 28px 32px 24px;
    position: relative; overflow: hidden;
}
.modal-hero::before {
    content:''; position:absolute; top:-40px; right:-40px;
    width:160px; height:160px; background:rgba(255,255,255,.06); border-radius:50%;
}
.modal-hero::after {
    content:''; position:absolute; bottom:-60px; left:-20px;
    width:200px; height:200px; background:rgba(255,255,255,.04); border-radius:50%;
}
.modal-hero h5 { color:#fff; font-size:18px; font-weight:700; margin:0 0 4px; position:relative; }
.modal-hero p  { color:rgba(255,255,255,.7); font-size:13px; margin:0; position:relative; }
.modal-hero .btn-close-hero {
    position:absolute; top:16px; right:20px;
    background:rgba(255,255,255,.15); border:none; border-radius:8px;
    width:32px; height:32px; display:flex; align-items:center; justify-content:center;
    cursor:pointer; color:#fff; font-size:18px; line-height:1; transition:background .15s;
    z-index:1;
}
.modal-hero .btn-close-hero:hover { background:rgba(255,255,255,.25); }

.modal-steps {
    display:flex; gap:0; border-bottom:1px solid var(--sch-border);
    background:#f8fafc;
}
.modal-step {
    flex:1; padding:14px 16px; text-align:center; cursor:pointer;
    border-bottom:3px solid transparent; transition:all .2s;
    position:relative;
}
.modal-step.active { border-bottom-color:#2563eb; background:#fff; }
.modal-step .step-num {
    width:24px; height:24px; border-radius:50%;
    background:#e2e8f0; color:#6b7280;
    font-size:12px; font-weight:700;
    display:inline-flex; align-items:center; justify-content:center;
    margin-bottom:4px; transition:all .2s;
}
.modal-step.active   .step-num { background:#2563eb; color:#fff; }
.modal-step.complete .step-num { background:#16a34a; color:#fff; }
.modal-step .step-label { font-size:12px; font-weight:500; color:#6b7280; display:block; }
.modal-step.active .step-label { color:#1e3a5f; }

.step-panel { display:none; padding:24px 28px; }
.step-panel.active { display:block; }

/* ── Scholarship card selector ─────────────────────────── */
.sch-card-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; max-height:300px; overflow-y:auto; padding:2px; }
.sch-card {
    border:2px solid var(--sch-border); border-radius:12px; padding:14px;
    cursor:pointer; transition:all .2s; position:relative; background:#fff;
}
.sch-card:hover { border-color:#93c5fd; background:#eff6ff; }
.sch-card.selected { border-color:#2563eb; background:#eff6ff; box-shadow:0 0 0 3px rgba(37,99,235,.12); }
.sch-card .sch-card-title { font-size:13px; font-weight:600; color:#1e3a5f; margin-bottom:4px; }
.sch-card .sch-card-meta  { font-size:11px; color:#6b7280; }
.sch-card .sch-card-badge {
    position:absolute; top:10px; right:10px;
    font-size:10px; font-weight:600; padding:2px 8px; border-radius:20px;
}
.sch-card .sch-card-value {
    margin-top:8px; padding:6px 10px; background:#f1f5f9; border-radius:8px;
    font-size:12px; font-weight:600; color:#2563eb;
}
.sch-card.selected .sch-check {
    display:flex !important;
}
.sch-check {
    display:none; position:absolute; top:-8px; left:-8px;
    width:22px; height:22px; background:#2563eb; border-radius:50%;
    align-items:center; justify-content:center; color:#fff; font-size:12px;
}

/* ── Student card search ────────────────────────────────── */
.student-search-wrap { position:relative; margin-bottom:16px; }
.student-search-wrap input {
    border-radius:10px; padding:10px 16px 10px 40px;
    border:1.5px solid var(--sch-border); width:100%;
    font-size:14px; transition:border .15s;
}
.student-search-wrap input:focus { border-color:#2563eb; outline:none; box-shadow:0 0 0 3px rgba(37,99,235,.1); }
.student-search-wrap .search-ico { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#9ca3af; }

.student-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; max-height:280px; overflow-y:auto; padding:2px; }
.student-card {
    border:2px solid var(--sch-border); border-radius:12px; padding:12px;
    cursor:pointer; transition:all .2s; display:flex; align-items:center; gap:12px;
    background:#fff; position:relative;
}
.student-card:hover   { border-color:#93c5fd; background:#f0f9ff; }
.student-card.selected{ border-color:#2563eb; background:#eff6ff; box-shadow:0 0 0 3px rgba(37,99,235,.12); }
.student-avatar {
    width:44px; height:44px; border-radius:50%; object-fit:cover;
    border:2px solid #e2e8f0; flex-shrink:0;
}
.student-avatar-placeholder {
    width:44px; height:44px; border-radius:50%; flex-shrink:0;
    background:linear-gradient(135deg,#667eea,#764ba2);
    display:flex; align-items:center; justify-content:center;
    color:#fff; font-weight:700; font-size:15px; border:2px solid #e2e8f0;
}
.student-card .s-name { font-size:13px; font-weight:600; color:#1e3a5f; }
.student-card .s-no   { font-size:11px; color:#6b7280; }
.student-card .s-check {
    display:none; position:absolute; top:-8px; right:-8px;
    width:22px; height:22px; background:#2563eb; border-radius:50%;
    align-items:center; justify-content:center; color:#fff; font-size:11px;
}
.student-card.selected .s-check { display:flex; }

.student-loading { text-align:center; padding:32px; color:#9ca3af; }
.student-empty   { text-align:center; padding:32px; color:#9ca3af; }

/* ── Step 3 summary ─────────────────────────────────────── */
.summary-card {
    background:#f8fafc; border:1px solid var(--sch-border);
    border-radius:12px; padding:20px;
}
.summary-row { display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid var(--sch-border); }
.summary-row:last-child { border:none; }
.summary-row .s-key { font-size:13px; color:#6b7280; }
.summary-row .s-val { font-size:13px; font-weight:600; color:#1e3a5f; }

.selected-student-preview {
    display:flex; align-items:center; gap:14px;
    background:#eff6ff; border:1px solid #bfdbfe; border-radius:12px; padding:14px 18px; margin-bottom:20px;
}

/* ── Modal footer ───────────────────────────────────────── */
.modal-footer-custom {
    padding:16px 28px; background:#f8fafc;
    border-top:1px solid var(--sch-border);
    display:flex; justify-content:space-between; align-items:center;
}

/* ── Scrollbar ──────────────────────────────────────────── */
.sch-card-grid::-webkit-scrollbar,
.student-grid::-webkit-scrollbar { width:4px; }
.sch-card-grid::-webkit-scrollbar-thumb,
.student-grid::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:4px; }

/* ── No scholarship alert ───────────────────────────────── */
.no-sch-alert {
    text-align:center; padding:40px 20px; color:#9ca3af;
}
.no-sch-alert i { font-size:40px; display:block; margin-bottom:10px; opacity:.4; }
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 fw-bold" style="color:var(--sch-primary)">
                        <i class="ri-user-star-line me-2"></i>{{ $pagetitle }}
                    </h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.scholarship.index') }}">Scholarships</a></li>
                            <li class="breadcrumb-item active">Assignments</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('admin.scholarship.index') }}" class="btn btn-light">
                    <i class="ri-arrow-left-line me-1"></i>Back to Scholarships
                </a>
            </div>
        </div>
    </div>

    {{-- Status tabs --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ !request('status') ? 'active' : '' }}" href="{{ route('admin.scholarship.assignments') }}">
                All <span class="badge bg-secondary ms-1">{{ array_sum($statusCounts) }}</span>
            </a>
        </li>
        @foreach(['active'=>'success','pending'=>'warning','approved'=>'info','expired'=>'secondary','revoked'=>'danger'] as $s=>$c)
        <li class="nav-item">
            <a class="nav-link {{ request('status')==$s ? 'active' : '' }}"
               href="{{ route('admin.scholarship.assignments',['status'=>$s]) }}">
                {{ ucfirst($s) }} <span class="badge bg-{{ $c }} ms-1">{{ $statusCounts[$s]??0 }}</span>
            </a>
        </li>
        @endforeach
    </ul>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="search-box">
                        <input type="text" class="form-control" id="searchInput"
                               placeholder="Search by student name or admission number...">
                        <i class="ri-search-line search-icon"></i>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-success" id="openAssignModal">
                        <i class="ri-add-line me-1"></i>Assign Scholarship
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-semibold"><i class="ri-list-check me-2"></i>Scholarship Assignments</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="assignmentsTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th><th>Scholarship</th><th>Student</th>
                            <th>Admission No</th><th>Value</th><th>Status</th>
                            <th>Effective Period</th><th>Assigned By</th><th>Date</th><th width="80">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $index => $a)
                        <tr>
                            <td>{{ $assignments->firstItem() + $index }}</td>
                            <td>{{ $a->scholarship->title ?? 'N/A' }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($a->student?->picture?->picture)
                                        <img src="{{ asset('storage/student_avatars/'.$a->student->picture->picture) }}"
                                             style="width:30px;height:30px;border-radius:50%;object-fit:cover;">
                                    @else
                                        <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:700;">
                                            {{ strtoupper(substr($a->student->firstname??'?',0,1)) }}
                                        </div>
                                    @endif
                                    {{ $a->student->firstname??'' }} {{ $a->student->lastname??'' }}
                                </div>
                            </td>
                            <td>{{ $a->student->admissionNo ?? 'N/A' }}</td>
                            <td>
                                @if($a->value_type=='percentage')
                                    <span class="badge bg-info text-white">{{ $a->value }}%</span>
                                @else
                                    <span class="badge bg-success text-white">₦{{ number_format($a->value,2) }}</span>
                                @endif
                            </td>
                            <td><span class="status-badge status-{{ $a->status }}">{{ ucfirst($a->status) }}</span></td>
                            <td>
                                <small>
                                    {{ \Carbon\Carbon::parse($a->effective_from)->format('d M Y') }}
                                    → {{ $a->effective_to ? \Carbon\Carbon::parse($a->effective_to)->format('d M Y') : 'Ongoing' }}
                                </small>
                            </td>
                            <td>{{ $a->assignedBy->name ?? 'System' }}</td>
                            <td>{{ $a->created_at->format('d M Y') }}</td>
                            <td>
                                @if($a->status=='active')
                                <button class="btn btn-sm btn-danger revoke-btn" data-id="{{ $a->id }}" title="Revoke">
                                    <i class="ri-close-line"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="10" class="text-center py-5 text-muted">
                            <i class="ri-inbox-line ri-2x d-block mb-2"></i>No assignments found.
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($assignments->hasPages())
        <div class="card-footer bg-white">{{ $assignments->links() }}</div>
        @endif
    </div>

</div>
</div>
</div>

{{-- ═══════════════════════════════════════════════════════
     ASSIGN MODAL — multi-step
═══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="assignModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            {{-- Hero header --}}
            <div class="modal-hero">
                <button class="btn-close-hero" id="closeAssignModal">✕</button>
                <h5><i class="ri-graduation-cap-line me-2"></i>Assign Scholarship</h5>
                <p>Select a scholarship, choose a student and set the dates.</p>
            </div>

            {{-- Step indicators --}}
            <div class="modal-steps">
                <div class="modal-step active" data-step="1">
                    <div class="step-num">1</div>
                    <span class="step-label">Scholarship</span>
                </div>
                <div class="modal-step" data-step="2">
                    <div class="step-num">2</div>
                    <span class="step-label">Student</span>
                </div>
                <div class="modal-step" data-step="3">
                    <div class="step-num">3</div>
                    <span class="step-label">Dates & Confirm</span>
                </div>
            </div>

            {{-- Step 1: Choose scholarship --}}
            <div class="step-panel active" id="step1">
                <p class="text-muted small mb-3">Select a scholarship to assign:</p>

                @if(($scholarships ?? collect())->isEmpty())
                    <div class="no-sch-alert">
                        <i class="ri-graduation-cap-line"></i>
                        <div class="fw-semibold mb-1">No scholarships available</div>
                        <div class="small">Create an active or draft scholarship first.</div>
                        <a href="{{ route('admin.scholarship.create') }}" class="btn btn-sm btn-primary mt-3">
                            <i class="ri-add-line me-1"></i>Create Scholarship
                        </a>
                    </div>
                @else
                    <div class="sch-card-grid" id="schCardGrid">
                        @foreach($scholarships as $sch)
                        <div class="sch-card" data-id="{{ $sch->id }}"
                             data-title="{{ $sch->title }}"
                             data-no="{{ $sch->scholarship_no }}"
                             data-vtype="{{ $sch->value_type }}"
                             data-value="{{ $sch->value }}"
                             data-status="{{ $sch->status }}">
                            <div class="sch-check"><i class="ri-check-line"></i></div>
                            <span class="sch-card-badge status-{{ $sch->status }}">{{ ucfirst($sch->status) }}</span>
                            <div class="sch-card-title">{{ $sch->title }}</div>
                            <div class="sch-card-meta">{{ $sch->scholarship_no }} · {{ $sch->type->name ?? 'N/A' }}</div>
                            <div class="sch-card-value">
                                @if($sch->value_type=='percentage')
                                    <i class="ri-percent-line me-1"></i>{{ $sch->value }}% discount
                                @else
                                    <i class="ri-money-naira-circle-line me-1"></i>₦{{ number_format($sch->value,2) }} off
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Step 2: Choose student --}}
            <div class="step-panel" id="step2">
                <div class="student-search-wrap">
                    <i class="ri-search-line search-ico"></i>
                    <input type="text" id="studentSearchInput"
                           placeholder="Type at least 2 characters to search students...">
                </div>
                <div class="student-grid" id="studentGrid">
                    <div class="student-loading" style="grid-column:1/-1">
                        <i class="ri-user-search-line ri-2x d-block mb-2" style="opacity:.3"></i>
                        <div class="small">Search to find students eligible for the selected scholarship.</div>
                    </div>
                </div>
            </div>

            {{-- Step 3: Dates + summary --}}
            <div class="step-panel" id="step3">
                <div class="selected-student-preview" id="selectedStudentPreview"></div>

                <div class="summary-card mb-4" id="schSummary"></div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Effective From <span class="text-danger">*</span></label>
                        <input type="date" id="effectiveFrom" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Effective To</label>
                        <input type="date" id="effectiveTo" class="form-control">
                        <small class="text-muted">Leave empty for ongoing</small>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Reason (optional)</label>
                        <input type="text" id="assignReason" class="form-control" placeholder="e.g., Academic excellence award">
                    </div>
                </div>

                <div class="alert alert-danger mt-3 d-none" id="assignError"></div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer-custom">
                <button class="btn btn-light" id="stepBackBtn" style="display:none;">
                    <i class="ri-arrow-left-line me-1"></i>Back
                </button>
                <div class="ms-auto d-flex gap-2">
                    <button class="btn btn-light" id="cancelAssignBtn">Cancel</button>
                    <button class="btn btn-primary" id="stepNextBtn" disabled>
                        Next <i class="ri-arrow-right-line ms-1"></i>
                    </button>
                    <button class="btn btn-success d-none" id="submitAssignBtn">
                        <i class="ri-check-line me-1"></i>Assign Scholarship
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Revoke Modal --}}
<div class="modal fade" id="revokeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
        <div class="modal-content border-0" style="border-radius:16px;overflow:hidden">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="ri-close-circle-line me-2"></i>Revoke Assignment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">This will revoke the scholarship and the student will no longer receive the discount.</p>
                <label class="form-label fw-semibold small">Reason <span class="text-danger">*</span></label>
                <textarea id="revokeReason" class="form-control" rows="3" placeholder="Provide a reason..."></textarea>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger" id="confirmRevokeBtn">Revoke</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;

// ── State ──────────────────────────────────────────────────────────────────
let currentStep      = 1;
let selectedSch      = null;   // { id, title, no, vtype, value, status }
let selectedStudent  = null;   // { id, firstname, lastname, admissionNo, avatar }
let revokeId         = null;
let studentSearchTO  = null;

// ── Modal open/close ───────────────────────────────────────────────────────
const assignModal = new bootstrap.Modal(document.getElementById('assignModal'));

document.getElementById('openAssignModal').addEventListener('click', () => {
    resetModal();
    assignModal.show();
});
document.getElementById('closeAssignModal').addEventListener('click', () => assignModal.hide());
document.getElementById('cancelAssignBtn').addEventListener('click', () => assignModal.hide());

function resetModal() {
    currentStep     = 1;
    selectedSch     = null;
    selectedStudent = null;
    showStep(1);
    document.querySelectorAll('.sch-card').forEach(c => c.classList.remove('selected'));
    document.getElementById('studentGrid').innerHTML = `
        <div class="student-loading" style="grid-column:1/-1">
            <i class="ri-user-search-line ri-2x d-block mb-2" style="opacity:.3"></i>
            <div class="small">Search to find eligible students.</div>
        </div>`;
    document.getElementById('studentSearchInput').value = '';
    document.getElementById('effectiveFrom').value = '{{ date("Y-m-d") }}';
    document.getElementById('effectiveTo').value   = '';
    document.getElementById('assignReason').value  = '';
    document.getElementById('assignError').classList.add('d-none');
    updateButtons();
}

// ── Step navigation ────────────────────────────────────────────────────────
function showStep(n) {
    currentStep = n;
    document.querySelectorAll('.step-panel').forEach((p,i) => {
        p.classList.toggle('active', i+1 === n);
    });
    document.querySelectorAll('.modal-step').forEach((s,i) => {
        s.classList.toggle('active',   i+1 === n);
        s.classList.toggle('complete', i+1 <  n);
    });
    if (n === 3) buildSummary();
    updateButtons();
}

function updateButtons() {
    const back   = document.getElementById('stepBackBtn');
    const next   = document.getElementById('stepNextBtn');
    const submit = document.getElementById('submitAssignBtn');

    back.style.display   = currentStep > 1 ? 'inline-flex' : 'none';
    next.classList.toggle('d-none',   currentStep === 3);
    submit.classList.toggle('d-none', currentStep !== 3);

    if (currentStep === 1) next.disabled = !selectedSch;
    if (currentStep === 2) next.disabled = !selectedStudent;
    if (currentStep === 3) next.disabled = false;
}

document.getElementById('stepNextBtn').addEventListener('click', () => {
    if (currentStep < 3) showStep(currentStep + 1);
});
document.getElementById('stepBackBtn').addEventListener('click', () => {
    if (currentStep > 1) showStep(currentStep - 1);
});

// ── Step 1: scholarship card selection ────────────────────────────────────
document.querySelectorAll('.sch-card').forEach(card => {
    card.addEventListener('click', function () {
        document.querySelectorAll('.sch-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        selectedSch = {
            id:     this.dataset.id,
            title:  this.dataset.title,
            no:     this.dataset.no,
            vtype:  this.dataset.vtype,
            value:  this.dataset.value,
            status: this.dataset.status,
        };
        selectedStudent = null; // reset if scholarship changes
        updateButtons();
    });
});

// ── Step 2: student search ─────────────────────────────────────────────────
document.getElementById('studentSearchInput').addEventListener('input', function () {
    clearTimeout(studentSearchTO);
    const q = this.value.trim();
    if (q.length < 2) return;
    studentSearchTO = setTimeout(() => fetchStudents(q), 300);
});

async function fetchStudents(q) {
    const grid = document.getElementById('studentGrid');
    grid.innerHTML = `<div class="student-loading" style="grid-column:1/-1">
        <span class="spinner-border spinner-border-sm me-2"></span>Searching...</div>`;

    try {
        const params = new URLSearchParams({ q, scholarship_id: selectedSch?.id ?? '' });
        const res    = await fetch(`{{ route('admin.scholarship.eligible-students') }}?${params}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data   = await res.json();
        renderStudents(data.students || []);
    } catch {
        grid.innerHTML = `<div class="student-empty" style="grid-column:1/-1">Failed to load students.</div>`;
    }
}

function renderStudents(students) {
    const grid = document.getElementById('studentGrid');
    if (!students.length) {
        grid.innerHTML = `<div class="student-empty" style="grid-column:1/-1">
            <i class="ri-user-unfollow-line ri-2x d-block mb-2" style="opacity:.3"></i>
            No eligible students found. Try a different search term.</div>`;
        return;
    }

    grid.innerHTML = students.map(s => {
        const initials = (s.firstname[0] + s.lastname[0]).toUpperCase();
        const avatar   = s.avatar
            ? `<img src="${s.avatar}" class="student-avatar" alt="${s.firstname}">`
            : `<div class="student-avatar-placeholder">${initials}</div>`;
        return `
        <div class="student-card" data-id="${s.id}"
             data-firstname="${s.firstname}" data-lastname="${s.lastname}"
             data-no="${s.admissionNo}" data-avatar="${s.avatar ?? ''}">
            <div class="s-check"><i class="ri-check-line"></i></div>
            ${avatar}
            <div>
                <div class="s-name">${s.firstname} ${s.lastname}</div>
                <div class="s-no">${s.admissionNo}</div>
            </div>
        </div>`;
    }).join('');

    // Bind click
    grid.querySelectorAll('.student-card').forEach(card => {
        card.addEventListener('click', function () {
            grid.querySelectorAll('.student-card').forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            selectedStudent = {
                id:          this.dataset.id,
                firstname:   this.dataset.firstname,
                lastname:    this.dataset.lastname,
                admissionNo: this.dataset.no,
                avatar:      this.dataset.avatar,
            };
            updateButtons();
        });
    });
}

// ── Step 3: summary ────────────────────────────────────────────────────────
function buildSummary() {
    // Student preview
    const initials = (selectedStudent.firstname[0] + selectedStudent.lastname[0]).toUpperCase();
    const avatar   = selectedStudent.avatar
        ? `<img src="${selectedStudent.avatar}" style="width:52px;height:52px;border-radius:50%;object-fit:cover;border:2px solid #bfdbfe">`
        : `<div style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:18px;border:2px solid #bfdbfe;">${initials}</div>`;

    document.getElementById('selectedStudentPreview').innerHTML = `
        ${avatar}
        <div>
            <div style="font-weight:700;color:#1e3a5f;font-size:15px">${selectedStudent.firstname} ${selectedStudent.lastname}</div>
            <div style="font-size:12px;color:#6b7280">${selectedStudent.admissionNo}</div>
        </div>
        <div class="ms-auto">
            <span class="status-badge status-active"><i class="ri-check-line"></i>Selected</span>
        </div>`;

    // Scholarship summary
    const valueDisplay = selectedSch.vtype === 'percentage'
        ? `${selectedSch.value}% discount`
        : `₦${parseFloat(selectedSch.value).toLocaleString()} off`;

    document.getElementById('schSummary').innerHTML = `
        <div class="summary-row"><span class="s-key">Scholarship</span><span class="s-val">${selectedSch.title}</span></div>
        <div class="summary-row"><span class="s-key">Reference No.</span><span class="s-val"><code>${selectedSch.no}</code></span></div>
        <div class="summary-row"><span class="s-key">Value</span><span class="s-val text-primary">${valueDisplay}</span></div>
        <div class="summary-row"><span class="s-key">Status</span><span class="s-val">${selectedSch.status.charAt(0).toUpperCase()+selectedSch.status.slice(1)}</span></div>`;
}

// ── Submit assignment ──────────────────────────────────────────────────────
document.getElementById('submitAssignBtn').addEventListener('click', async function () {
    const errBox = document.getElementById('assignError');
    errBox.classList.add('d-none');

    const from = document.getElementById('effectiveFrom').value;
    if (!from) {
        errBox.textContent = 'Please set an effective from date.';
        errBox.classList.remove('d-none');
        return;
    }

    const btn          = this;
    const originalHTML = btn.innerHTML;
    btn.disabled       = true;
    btn.innerHTML      = '<span class="spinner-border spinner-border-sm me-2"></span>Assigning...';

    try {
        const body = new URLSearchParams({
            scholarship_id:  selectedSch.id,
            student_id:      selectedStudent.id,
            effective_from:  from,
            effective_to:    document.getElementById('effectiveTo').value,
            reason:          document.getElementById('assignReason').value,
            _token:          CSRF,
        });

        const res  = await fetch('{{ route("admin.scholarship.assign") }}', {
            method: 'POST',
            headers: {
                'Content-Type':     'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN':     CSRF,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body,
        });
        const data = await res.json();

        if (data.success) {
            assignModal.hide();
            Swal.fire({
                icon: 'success', title: 'Assigned!', text: data.message,
                confirmButtonColor: '#2563eb',
            }).then(() => location.reload());
        } else {
            errBox.textContent = data.message || 'Something went wrong.';
            errBox.classList.remove('d-none');
        }
    } catch {
        errBox.textContent = 'Network error. Please try again.';
        errBox.classList.remove('d-none');
    } finally {
        btn.disabled  = false;
        btn.innerHTML = originalHTML;
    }
});

// ── Revoke ─────────────────────────────────────────────────────────────────
document.addEventListener('click', e => {
    const btn = e.target.closest('.revoke-btn');
    if (!btn) return;
    revokeId = btn.dataset.id;
    document.getElementById('revokeReason').value = '';
    new bootstrap.Modal(document.getElementById('revokeModal')).show();
});

document.getElementById('confirmRevokeBtn').addEventListener('click', async function () {
    if (!revokeId) return;
    const reason = document.getElementById('revokeReason').value.trim();
    if (!reason) { Swal.fire('Error!','Please provide a reason','error'); return; }

    try {
        const res  = await fetch(`/admin/scholarship/assignment/${revokeId}/revoke`, {
            method: 'POST',
            headers: {
                'Content-Type':     'application/json',
                'X-CSRF-TOKEN':     CSRF,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ reason }),
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire('Revoked!', data.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Error!', data.message, 'error');
        }
    } catch {
        Swal.fire('Error!', 'Something went wrong', 'error');
    }
    bootstrap.Modal.getInstance(document.getElementById('revokeModal'))?.hide();
});

// ── Table search ───────────────────────────────────────────────────────────
document.getElementById('searchInput').addEventListener('keyup', function () {
    const val = this.value.toLowerCase();
    document.querySelectorAll('#assignmentsTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
    });
});
</script>
@endsection
