{{-- resources/views/sibling/create.blade.php --}}
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
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('sibling.index') }}" class="btn btn-light">
                    <i class="ri-arrow-left-line me-1"></i>Back to List
                </a>
            </div>
        </div>
    </div>

    <form id="createGroupForm">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <div class="form-section">
                    <h5><i class="ri-information-line me-2"></i>Family Information</h5>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Family Name <span class="text-danger">*</span></label>
                            <input type="text" name="family_name" class="form-control" required placeholder="e.g., Smith Family">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Parent Phone</label>
                            <input type="text" name="parent_phone" class="form-control" placeholder="Primary contact number">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Parent Email</label>
                            <input type="email" name="parent_email" class="form-control" placeholder="Family email address">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Address</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Family address"></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h5><i class="ri-user-line me-2"></i>Children / Students</h5>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Students <span class="text-danger">*</span></label>
                        <select name="student_ids[]" id="studentSelect" class="form-select select2" multiple="multiple" required>
                        </select>
                        <small class="text-muted">Select all children belonging to this family</small>
                    </div>
                </div>

                <div class="form-section">
                    <h5><i class="ri-discount-line me-2"></i>Sibling Discount (Optional)</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Discount Type</label>
                            <select name="discount_type" id="discountType" class="form-select">
                                <option value="">No Discount</option>
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed_per_child">Fixed Amount per Child (₦)</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="discountValueDiv" style="display: none;">
                            <label class="form-label fw-semibold" id="discountValueLabel">Discount Value</label>
                            <input type="number" name="discount_value" id="discountValue" class="form-control" step="0.01" placeholder="Enter value">
                        </div>
                    </div>
                    <div class="alert alert-info mt-3">
                        <i class="ri-information-line me-2"></i>
                        <strong>Note:</strong> Additional 5% discount for each subsequent child after the first.
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="form-section bg-light">
                    <h5><i class="ri-lightbulb-line me-2"></i>Quick Tips</h5>
                    <ul class="small text-muted mb-0 ps-3">
                        <li class="mb-2">✓ Create family groups for siblings</li>
                        <li class="mb-2">✓ Apply sibling discounts automatically</li>
                        <li class="mb-2">✓ Track family savings across all children</li>
                        <li class="mb-2">✓ Multiple students can be added per family</li>
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
                        <i class="ri-save-line me-1"></i>Create Family Group
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

    // Discount type toggle
    $('#discountType').on('change', function() {
        const discountValueDiv = $('#discountValueDiv');
        const discountValueLabel = $('#discountValueLabel');
        if (this.value === 'percentage') {
            discountValueDiv.show();
            discountValueLabel.text('Discount Value (%)');
        } else if (this.value === 'fixed_per_child') {
            discountValueDiv.show();
            discountValueLabel.text('Discount Value (₦ per child)');
        } else {
            discountValueDiv.hide();
        }
    });

    // Form submission
    $('#createGroupForm').on('submit', async function(e) {
        e.preventDefault();

        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Creating...');

        const formData = $(this).serialize();

        try {
            const response = await fetch('{{ route("sibling.store") }}', {
                method: 'POST',
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
