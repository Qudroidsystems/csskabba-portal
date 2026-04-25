{{-- resources/views/sibling/edit.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
.form-section {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
}
.form-section h5 {
    font-size: 16px;
    font-weight: 600;
    color: #1e3a5f;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e2e8f0;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 fw-bold" style="color: #1e3a5f;">
                        <i class="ri-group-line me-2"></i>{{ $pagetitle }}
                    </h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('sibling.index') }}">Family Groups</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('sibling.index') }}" class="btn btn-light">
                    <i class="ri-arrow-left-line me-1"></i>Back to List
                </a>
            </div>
        </div>
    </div>

    <form id="editGroupForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="id" value="{{ $group->id }}">

        <div class="row">
            <div class="col-lg-8">
                <div class="form-section">
                    <h5><i class="ri-information-line me-2"></i>Family Information</h5>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Group Number</label>
                            <input type="text" class="form-control bg-light" value="{{ $group->group_no }}" readonly disabled>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Family Name <span class="text-danger">*</span></label>
                            <input type="text" name="family_name" class="form-control" value="{{ $group->family_name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Parent Phone</label>
                            <input type="text" name="parent_phone" class="form-control" value="{{ $group->parent_phone }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Parent Email</label>
                            <input type="email" name="parent_email" class="form-control" value="{{ $group->parent_email }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Address</label>
                            <textarea name="address" class="form-control" rows="2">{{ $group->address }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h5><i class="ri-user-line me-2"></i>Children / Students</h5>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Students <span class="text-danger">*</span></label>
                        <select name="student_ids[]" id="studentSelect" class="form-select select2" multiple="multiple" required>
                            @foreach($group->students as $student)
                                <option value="{{ $student->id }}" selected>{{ $student->firstname }} {{ $student->lastname }} ({{ $student->admissionNo }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Select all children belonging to this family</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="form-section bg-light">
                    <h5><i class="ri-bar-chart-line me-2"></i>Statistics</h5>
                    <div class="mb-3">
                        <div class="small text-muted">Total Children</div>
                        <div class="fs-4 fw-bold">{{ $group->students->count() }}</div>
                    </div>
                    @if($group->discount_value)
                    <div class="mb-3">
                        <div class="small text-muted">Current Discount</div>
                        <div class="fs-5 text-success">
                            {{ $group->discount_type === 'percentage' ? $group->discount_value . '%' : '₦' . $group->discount_value . ' per child' }}
                        </div>
                    </div>
                    @endif
                </div>

                <div class="form-section bg-light">
                    <h5><i class="ri-lightbulb-line me-2"></i>Quick Tips</h5>
                    <ul class="small text-muted mb-0 ps-3">
                        <li class="mb-2">✓ Update family information as needed</li>
                        <li class="mb-2">✓ Add or remove children from the family</li>
                        <li class="mb-2">✓ Apply sibling discount from the main list</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="form-section text-end">
                    <button type="button" class="btn btn-light me-2" onclick="window.history.back()">
                        <i class="ri-close-line me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="ri-save-line me-1"></i>Update Family Group
                    </button>
                </div>
            </div>
        </div>
    </form>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;
const currentStudentIds = @json($group->students->pluck('id')->toArray());

$(document).ready(function() {
    // Initialize Select2 for student search
    $('#studentSelect').select2({
        ajax: {
            url: '{{ route("sibling.search-students") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { q: params.term };
            },
            processResults: function(data) {
                return {
                    results: data.students?.map(s => ({ id: s.id, text: s.text })) || []
                };
            },
            cache: true
        },
        placeholder: 'Search for students...',
        minimumInputLength: 2,
        width: '100%'
    });

    // Form submission
    $('#editGroupForm').on('submit', async function(e) {
        e.preventDefault();

        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');

        const id = $('input[name="id"]').val();
        const formData = $(this).serialize();

        try {
            const response = await fetch(`/sibling/${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = '{{ route("sibling.index") }}';
                });
            } else {
                let errorMsg = data.message || 'Validation error';
                if (data.errors) {
                    errorMsg = Object.values(data.errors).flat().join('\n');
                }
                Swal.fire('Error!', errorMsg, 'error');
            }
        } catch (error) {
            Swal.fire('Error!', 'Something went wrong', 'error');
        } finally {
            submitBtn.prop('disabled', false).html(originalText);
        }
    });
});
</script>
@endsection
