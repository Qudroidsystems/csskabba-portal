{{-- resources/views/admin/discount/assignments.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
:root {
    --disc-primary: #1e3a5f;
    --disc-success: #16a34a;
    --disc-warning: #d97706;
    --disc-danger: #dc2626;
    --disc-border: #e2e8f0;
}

.status-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500;
}
.status-active { background: #dcfce7; color: #16a34a; }
.status-expired { background: #fee2e2; color: #dc2626; }
.status-removed { background: #f3f4f6; color: #6b7280; }

.assignment-card {
    background: white;
    border: 1px solid var(--disc-border);
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 16px;
    transition: all 0.2s;
}
.assignment-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 fw-bold" style="color: var(--disc-primary);">
                        <i class="ri-user-settings-line me-2"></i>{{ $pagetitle }}
                    </h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.discount.index') }}">Discounts</a></li>
                            <li class="breadcrumb-item active">Assignments</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('admin.discount.index') }}" class="btn btn-light">
                        <i class="ri-arrow-left-line me-1"></i>Back to Discounts
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Status Filter Tabs --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ !request('status') ? 'active' : '' }}" href="{{ route('admin.discount.assignments') }}">
                All <span class="badge bg-secondary ms-1">{{ array_sum($statusCounts) }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('status') == 'active' ? 'active' : '' }}" href="{{ route('admin.discount.assignments', ['status' => 'active']) }}">
                Active <span class="badge bg-success ms-1">{{ $statusCounts['active'] ?? 0 }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('status') == 'expired' ? 'active' : '' }}" href="{{ route('admin.discount.assignments', ['status' => 'expired']) }}">
                Expired <span class="badge bg-secondary ms-1">{{ $statusCounts['expired'] ?? 0 }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('status') == 'removed' ? 'active' : '' }}" href="{{ route('admin.discount.assignments', ['status' => 'removed']) }}">
                Removed <span class="badge bg-danger ms-1">{{ $statusCounts['removed'] ?? 0 }}</span>
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
                    <button class="btn btn-success" id="assignDiscountBtn" data-bs-toggle="modal" data-bs-target="#assignModal">
                        <i class="ri-add-line me-1"></i>Assign Discount
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Assignments Grid --}}
    <div class="row" id="assignmentsContainer">
        @forelse($assignments as $assignment)
        <div class="col-md-6 col-lg-4">
            <div class="assignment-card" data-search="{{ strtolower($assignment->student->firstname ?? '') }} {{ strtolower($assignment->student->lastname ?? '') }} {{ $assignment->student->admissionNo ?? '' }}">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="mb-1 fw-bold">{{ $assignment->discount->title ?? 'N/A' }}</h6>
                        <small class="text-muted">{{ $assignment->discount->discount_no ?? 'N/A' }}</small>
                    </div>
                    <span class="status-badge status-{{ $assignment->status }}">
                        {{ ucfirst($assignment->status) }}
                    </span>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-muted">Student</div>
                            <div class="fw-semibold">{{ $assignment->student->firstname ?? '' }} {{ $assignment->student->lastname ?? '' }}</div>
                            <div class="small text-muted">Adm: {{ $assignment->student->admissionNo ?? 'N/A' }}</div>
                        </div>
                        <div class="text-end">
                            <div class="small text-muted">Discount Value</div>
                            <div class="fw-bold text-success">
                                @if($assignment->value_type == 'percentage')
                                    {{ $assignment->value }}%
                                @else
                                    ₦{{ number_format($assignment->value, 2) }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="row">
                        <div class="col-6">
                            <div class="small text-muted">Effective From</div>
                            <div>{{ \Carbon\Carbon::parse($assignment->effective_from)->format('d M Y') }}</div>
                        </div>
                        <div class="col-6">
                            <div class="small text-muted">Effective To</div>
                            <div>{{ $assignment->effective_to ? \Carbon\Carbon::parse($assignment->effective_to)->format('d M Y') : 'Ongoing' }}</div>
                        </div>
                    </div>
                </div>

                @if($assignment->status == 'active')
                <div class="d-flex gap-2 mt-2">
                    <button class="btn btn-sm btn-danger remove-btn flex-grow-1" data-id="{{ $assignment->id }}">
                        <i class="ri-close-line me-1"></i>Remove
                    </button>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info text-center py-5">
                <i class="ri-inbox-line ri-2x d-block mb-2"></i>
                No discount assignments found.
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

{{-- Assign Discount Modal --}}
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="ri-add-line me-2"></i>Assign Discount</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Discount <span class="text-danger">*</span></label>
                        <select name="discount_id" class="form-select" required>
                            <option value="">-- Select Discount --</option>
                            @foreach($discounts ?? [] as $discount)
                                <option value="{{ $discount->id }}">{{ $discount->title }} ({{ $discount->discount_no }})</option>
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
                        <label class="form-label fw-semibold">Effective From <span class="text-danger">*</span></label>
                        <input type="date" name="effective_from" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Effective To</label>
                        <input type="date" name="effective_to" class="form-control">
                        <small class="text-muted">Leave empty for ongoing</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reason (Optional)</label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="Reason for discount assignment"></textarea>
                    </div>
                    <div class="alert alert-danger d-none" id="assignErrors"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Discount</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Remove Confirmation Modal --}}
<div class="modal fade" id="removeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="ri-close-line me-2"></i>Remove Discount</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to remove this discount from the student?</p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Reason for Removal</label>
                    <textarea id="removeReason" class="form-control" rows="3" placeholder="Optional reason..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRemoveBtn">Remove</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;
let removeId = null;

$(document).ready(function() {
    // Initialize Select2 for student search
    $('.select2').select2({
        dropdownParent: $('#assignModal'),
        ajax: {
            url: '{{ route("admin.discount.eligible-students") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term,
                    discount_id: $('select[name="discount_id"]').val()
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

    // Assign Form Submit
    $('#assignForm').on('submit', async function(e) {
        e.preventDefault();
        const formData = $(this).serialize();

        try {
            const response = await fetch('{{ route("admin.discount.assign") }}', {
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

    // Remove Button Click
    $('.remove-btn').on('click', function() {
        removeId = $(this).data('id');
        $('#removeModal').modal('show');
    });

    $('#confirmRemoveBtn').on('click', async function() {
        if (!removeId) return;

        try {
            const response = await fetch(`/admin/discount/assignment/${removeId}/remove`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: JSON.stringify({ reason: $('#removeReason').val() })
            });
            const data = await response.json();
            if (data.success) {
                Swal.fire('Removed!', data.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error!', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error!', 'Something went wrong', 'error');
        }
        $('#removeModal').modal('hide');
    });

    // Search functionality
    $('#searchInput').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('.assignment-card').each(function() {
            const searchData = $(this).data('search') || '';
            $(this).closest('.col-md-6, .col-lg-4').toggle(searchData.includes(value) || value === '');
        });
    });
});
</script>
@endsection
