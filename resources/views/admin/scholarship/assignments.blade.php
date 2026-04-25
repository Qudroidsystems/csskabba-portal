{{-- resources/views/admin/scholarship/assignments.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
:root {
    --sch-primary: #1e3a5f;
    --sch-success: #16a34a;
    --sch-warning: #d97706;
    --sch-danger: #dc2626;
    --sch-border: #e2e8f0;
}

.status-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500;
}
.status-active { background: #dcfce7; color: #16a34a; }
.status-pending { background: #fef3c7; color: #d97706; }
.status-approved { background: #dbeafe; color: #2563eb; }
.status-expired { background: #fee2e2; color: #dc2626; }
.status-revoked { background: #f3f4f6; color: #6b7280; }
</style>

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
                <div>
                    <a href="{{ route('admin.scholarship.index') }}" class="btn btn-light">
                        <i class="ri-arrow-left-line me-1"></i>Back to Scholarships
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Status Filter Tabs --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ !request('status') ? 'active' : '' }}" href="{{ route('admin.scholarship.assignments') }}">
                All <span class="badge bg-secondary ms-1">{{ array_sum($statusCounts) }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('status') == 'active' ? 'active' : '' }}" href="{{ route('admin.scholarship.assignments', ['status' => 'active']) }}">
                Active <span class="badge bg-success ms-1">{{ $statusCounts['active'] ?? 0 }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('status') == 'pending' ? 'active' : '' }}" href="{{ route('admin.scholarship.assignments', ['status' => 'pending']) }}">
                Pending <span class="badge bg-warning ms-1">{{ $statusCounts['pending'] ?? 0 }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('status') == 'approved' ? 'active' : '' }}" href="{{ route('admin.scholarship.assignments', ['status' => 'approved']) }}">
                Approved <span class="badge bg-info ms-1">{{ $statusCounts['approved'] ?? 0 }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('status') == 'expired' ? 'active' : '' }}" href="{{ route('admin.scholarship.assignments', ['status' => 'expired']) }}">
                Expired <span class="badge bg-secondary ms-1">{{ $statusCounts['expired'] ?? 0 }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('status') == 'revoked' ? 'active' : '' }}" href="{{ route('admin.scholarship.assignments', ['status' => 'revoked']) }}">
                Revoked <span class="badge bg-danger ms-1">{{ $statusCounts['revoked'] ?? 0 }}</span>
            </a>
        </li>
    </ul>

    {{-- Search Bar --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="search-box">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search by student name or admission number...">
                        <i class="ri-search-line search-icon"></i>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-success" id="assignScholarshipBtn" data-bs-toggle="modal" data-bs-target="#assignModal">
                        <i class="ri-add-line me-1"></i>Assign Scholarship
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Assignments Table --}}
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
                                    {{ \Carbon\Carbon::parse($assignment->effective_from)->format('d M Y') }}
                                    @if($assignment->effective_to)
                                        → {{ \Carbon\Carbon::parse($assignment->effective_to)->format('d M Y') }}
                                    @else
                                        → Ongoing
                                    @endif
                                </td>
                                <td>{{ $assignment->assignedBy->name ?? 'System' }}</td>
                                <td>{{ $assignment->created_at->format('d M Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        @if($assignment->status == 'active')
                                        <button class="btn btn-danger revoke-btn" data-id="{{ $assignment->id }}" title="Revoke">
                                            <i class="ri-close-line"></i>
                                        </button>
                                        @endif
                                    </div>
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
        <div class="card-footer bg-white">
            {{ $assignments->links() }}
        </div>
        @endif
    </div>

</div>
</div>
</div>

{{-- Assign Scholarship Modal --}}
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
                        <select name="scholarship_id" class="form-select" required>
                            <option value="">-- Select Scholarship --</option>
                            @foreach($scholarships ?? [] as $scholarship)
                                <option value="{{ $scholarship->id }}">{{ $scholarship->title }} ({{ $scholarship->scholarship_no }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Student <span class="text-danger">*</span></label>
                        <select name="student_id" class="form-select select2" required>
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
                    <button type="submit" class="btn btn-primary">Assign Scholarship</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Revoke Confirmation Modal --}}
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
                    <label class="form-label fw-semibold">Reason for Revocation <span class="text-danger">*</span></label>
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

$(document).ready(function() {
    // Initialize Select2 for student search
    $('.select2').select2({
        dropdownParent: $('#assignModal'),
        ajax: {
            url: '{{ route("admin.scholarship.eligible-students") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term,
                    scholarship_id: $('select[name="scholarship_id"]').val()
                };
            },
            processResults: function(data) {
                return {
                    results: data.students?.map(s => ({ id: s.id, text: s.firstname + ' ' + s.lastname + ' (' + s.admissionNo + ')' })) || []
                };
            },
            cache: true
        },
        placeholder: 'Search for a student...',
        minimumInputLength: 2
    });

    // Load scholarship details when selected
    $('select[name="scholarship_id"]').on('change', function() {
        const scholarshipId = $(this).val();
        if (scholarshipId) {
            // Get scholarship details via AJAX
            fetch(`/admin/scholarship/${scholarshipId}/edit-json`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const sch = data.data;
                        $('#value_type_display').val(sch.value_type == 'percentage' ? 'Percentage (%)' : 'Fixed Amount (₦)');
                        $('#value_display').val(sch.value_type == 'percentage' ? sch.value + '%' : '₦' + sch.value);
                    }
                });
        }
    });

    // Assign Form Submit
    $('#assignForm').on('submit', async function(e) {
        e.preventDefault();
        const formData = $(this).serialize();

        try {
            const response = await fetch('{{ route("admin.scholarship.assign") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                Swal.fire('Success!', data.message, 'success').then(() => location.reload());
            } else {
                $('#assignErrors').removeClass('d-none').html(data.message);
            }
        } catch (error) {
            Swal.fire('Error!', 'Something went wrong', 'error');
        }
    });

    // Revoke Button Click
    $('.revoke-btn').on('click', function() {
        revokeId = $(this).data('id');
        $('#revokeModal').modal('show');
    });

    $('#confirmRevokeBtn').on('click', async function() {
        if (!revokeId) return;
        const reason = $('#revokeReason').val();
        if (!reason) {
            Swal.fire('Error!', 'Please provide a reason for revocation', 'error');
            return;
        }

        try {
            const response = await fetch(`/admin/scholarship/assignment/${revokeId}/revoke`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: JSON.stringify({ reason: reason })
            });
            const data = await response.json();
            if (data.success) {
                Swal.fire('Revoked!', data.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error!', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error!', 'Something went wrong', 'error');
        }
        $('#revokeModal').modal('hide');
    });

    // Search functionality
    $('#searchInput').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#assignmentsTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
});
</script>
@endsection
