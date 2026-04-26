{{-- resources/views/admin/scholarship/assignments.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
:root {
    --sch-primary: #1e3a5f;
    --sch-border: #e2e8f0;
}
.status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; }
.status-active   { background: #dcfce7; color: #16a34a; }
.status-pending  { background: #fef3c7; color: #d97706; }
.status-approved { background: #dbeafe; color: #2563eb; }
.status-expired  { background: #fee2e2; color: #dc2626; }
.status-revoked  { background: #f3f4f6; color: #6b7280; }
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 fw-bold" style="color: var(--sch-primary);">
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
        @foreach(['active' => 'success', 'pending' => 'warning', 'approved' => 'info', 'expired' => 'secondary', 'revoked' => 'danger'] as $status => $color)
        <li class="nav-item">
            <a class="nav-link {{ request('status') == $status ? 'active' : '' }}"
               href="{{ route('admin.scholarship.assignments', ['status' => $status]) }}">
                {{ ucfirst($status) }}
                <span class="badge bg-{{ $color }} ms-1">{{ $statusCounts[$status] ?? 0 }}</span>
            </a>
        </li>
        @endforeach
    </ul>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" id="searchInput"
                           placeholder="Search by student name or admission number...">
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#assignModal">
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
                            <th>#</th>
                            <th>Scholarship</th>
                            <th>Student Name</th>
                            <th>Admission No</th>
                            <th>Value</th>
                            <th>Status</th>
                            <th>Effective Period</th>
                            <th>Assigned By</th>
                            <th>Date</th>
                            <th width="100">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $index => $assignment)
                        <tr>
                            <td>{{ $assignments->firstItem() + $index }}</td>
                            <td>{{ $assignment->scholarship->title ?? 'N/A' }}</td>
                            <td>{{ $assignment->student->firstname ?? '' }} {{ $assignment->student->lastname ?? '' }}</td>
                            <td>{{ $assignment->student->admissionNo ?? 'N/A' }}</td>
                            <td>
                                @if($assignment->value_type == 'percentage')
                                    {{ $assignment->value }}%
                                @else
                                    ₦{{ number_format($assignment->value, 2) }}
                                @endif
                            </td>
                            <td>
                                <span class="status-badge status-{{ $assignment->status }}">
                                    {{ ucfirst($assignment->status) }}
                                </span>
                            </td>
                            <td>
                                <small>
                                    {{ \Carbon\Carbon::parse($assignment->effective_from)->format('d M Y') }}
                                    @if($assignment->effective_to)
                                        → {{ \Carbon\Carbon::parse($assignment->effective_to)->format('d M Y') }}
                                    @else
                                        → Ongoing
                                    @endif
                                </small>
                            </td>
                            <td>{{ $assignment->assignedBy->name ?? 'System' }}</td>
                            <td>{{ $assignment->created_at->format('d M Y') }}</td>
                            <td>
                                @if($assignment->status == 'active')
                                <button class="btn btn-sm btn-danger revoke-btn" data-id="{{ $assignment->id }}" title="Revoke">
                                    <i class="ri-close-line"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">No scholarship assignments found.</td>
                        </tr>
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

{{-- Assign Modal --}}
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="ri-add-line me-2"></i>Assign Scholarship</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Scholarship <span class="text-danger">*</span></label>
                        <select name="scholarship_id" class="form-select" id="scholarshipSelect" required>
                            <option value="">-- Select Scholarship --</option>
                            @foreach($scholarships ?? [] as $scholarship)
                                <option value="{{ $scholarship->id }}">{{ $scholarship->title }} ({{ $scholarship->scholarship_no }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Student <span class="text-danger">*</span></label>
                        <select name="student_id" class="form-select select2-student" id="studentSelect" required>
                            <option value="">-- Search Student --</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Value Type</label>
                        <input type="text" id="value_type_display" class="form-control" readonly disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Value</label>
                        <input type="text" id="value_display" class="form-control" readonly disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Effective From <span class="text-danger">*</span></label>
                        <input type="date" name="effective_from" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Effective To</label>
                        <input type="date" name="effective_to" class="form-control">
                        <small class="text-muted">Leave empty for ongoing</small>
                    </div>
                    <div class="alert alert-danger d-none" id="assignErrors"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="assignSubmitBtn">Assign Scholarship</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Revoke Modal --}}
<div class="modal fade" id="revokeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="ri-close-line me-2"></i>Revoke Scholarship</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to revoke this scholarship assignment?</p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Reason <span class="text-danger">*</span></label>
                    <textarea id="revokeReason" class="form-control" rows="3" placeholder="Please provide a reason..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRevokeBtn">Revoke</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;
let revokeId = null;

$(document).ready(function () {

    // ── Select2 for student search ──────────────────────────────────────
    $('.select2-student').select2({
        dropdownParent: $('#assignModal'),
        placeholder:    'Search for a student...',
        minimumInputLength: 2,
        ajax: {
            url:      '{{ route("admin.scholarship.eligible-students") }}',
            dataType: 'json',
            delay:    250,
            headers:  { 'X-Requested-With': 'XMLHttpRequest' },
            data: function (params) {
                return {
                    q:               params.term,
                    scholarship_id:  $('#scholarshipSelect').val(),
                };
            },
            processResults: function (data) {
                return {
                    results: (data.students || []).map(s => ({
                        id:   s.id,
                        text: s.firstname + ' ' + s.lastname + ' (' + s.admissionNo + ')',
                    })),
                };
            },
            cache: true,
        },
    });

    // ── Load scholarship details on change ──────────────────────────────
    $('#scholarshipSelect').on('change', function () {
        const id = $(this).val();
        $('#value_type_display').val('');
        $('#value_display').val('');
        // Trigger Select2 to reload (different scholarship = different exclusion list)
        $('.select2-student').val(null).trigger('change');

        if (!id) return;
        fetch(`/admin/scholarship/${id}/edit-json`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const s = data.data;
                $('#value_type_display').val(s.value_type === 'percentage' ? 'Percentage (%)' : 'Fixed Amount (₦)');
                $('#value_display').val(s.value_type === 'percentage' ? s.value + '%' : '₦' + s.value);
            }
        })
        .catch(() => {});
    });

    // ── Assign form submit ──────────────────────────────────────────────
    $('#assignForm').on('submit', async function (e) {
        e.preventDefault();

        const btn          = $('#assignSubmitBtn');
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Assigning...');

        try {
            const response = await fetch('{{ route("admin.scholarship.assign") }}', {
                method: 'POST',
                headers: {
                    'Content-Type':     'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN':     CSRF_TOKEN,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: $(this).serialize(),
            });
            const data = await response.json();
            if (data.success) {
                Swal.fire('Success!', data.message, 'success').then(() => location.reload());
            } else {
                let msg = data.message || 'Something went wrong';
                if (data.errors) msg = Object.values(data.errors).flat().join('\n');
                $('#assignErrors').removeClass('d-none').text(msg);
            }
        } catch (err) {
            Swal.fire('Error!', 'Something went wrong', 'error');
        } finally {
            btn.prop('disabled', false).html(originalText);
        }
    });

    // ── Revoke ──────────────────────────────────────────────────────────
    $(document).on('click', '.revoke-btn', function () {
        revokeId = $(this).data('id');
        $('#revokeReason').val('');
        $('#revokeModal').modal('show');
    });

    $('#confirmRevokeBtn').on('click', async function () {
        if (!revokeId) return;
        const reason = $('#revokeReason').val().trim();
        if (!reason) {
            Swal.fire('Error!', 'Please provide a reason for revocation', 'error');
            return;
        }
        try {
            const response = await fetch(`/admin/scholarship/assignment/${revokeId}/revoke`, {
                method: 'POST',
                headers: {
                    'Content-Type':     'application/json',
                    'X-CSRF-TOKEN':     CSRF_TOKEN,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ reason }),
            });
            const data = await response.json();
            if (data.success) {
                Swal.fire('Revoked!', data.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error!', data.message, 'error');
            }
        } catch (err) {
            Swal.fire('Error!', 'Something went wrong', 'error');
        }
        $('#revokeModal').modal('hide');
    });

    // ── Client-side search filter ───────────────────────────────────────
    $('#searchInput').on('keyup', function () {
        const val = $(this).val().toLowerCase();
        $('#assignmentsTable tbody tr').each(function () {
            $(this).toggle($(this).text().toLowerCase().includes(val));
        });
    });
});
</script>
@endsection
