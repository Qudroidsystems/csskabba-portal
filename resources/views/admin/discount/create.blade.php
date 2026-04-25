{{-- resources/views/admin/discount/create.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
:root {
    --disc-primary: #1e3a5f;
    --disc-border: #e2e8f0;
    --disc-radius: 12px;
}

.form-section {
    background: white;
    border: 1px solid var(--disc-border);
    border-radius: var(--disc-radius);
    padding: 24px;
    margin-bottom: 24px;
}
.form-section h5 {
    font-size: 16px;
    font-weight: 600;
    color: var(--disc-primary);
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--disc-border);
}
.section-icon {
    width: 32px; height: 32px;
    background: var(--disc-primary);
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
}
.section-icon i { color: white; font-size: 16px; }
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 fw-bold" style="color: var(--disc-primary);">
                        <i class="ri-discount-line me-2"></i>{{ $pagetitle }}
                    </h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.discount.index') }}">Discounts</a></li>
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('admin.discount.index') }}" class="btn btn-light">
                    <i class="ri-arrow-left-line me-1"></i>Back to List
                </a>
            </div>
        </div>
    </div>

    <form id="discountForm" action="{{ route('admin.discount.store') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-lg-8">
                {{-- Basic Information --}}
                <div class="form-section">
                    <div class="d-flex align-items-center mb-3">
                        <div class="section-icon"><i class="ri-information-line"></i></div>
                        <h5 class="mb-0">Basic Information</h5>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required placeholder="e.g., Early Payment Discount">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Describe the discount conditions..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Discount Type <span class="text-danger">*</span></label>
                            <select name="discount_type_id" class="form-select" required>
                                <option value="">Select Type</option>
                                @foreach($discountTypes ?? [] as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select">
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Discount Value --}}
                <div class="form-section">
                    <div class="d-flex align-items-center mb-3">
                        <div class="section-icon"><i class="ri-money-dollar-circle-line"></i></div>
                        <h5 class="mb-0">Discount Value</h5>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Value Type <span class="text-danger">*</span></label>
                            <select name="value_type" id="valueType" class="form-select" required>
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed_amount">Fixed Amount (₦)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" id="valueLabel">Value (%) <span class="text-danger">*</span></label>
                            <input type="number" name="value" id="value" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-md-6" id="maxAmountDiv">
                            <label class="form-label fw-semibold">Maximum Amount</label>
                            <input type="number" name="max_amount" class="form-control" step="0.01" placeholder="Optional - limits maximum discount">
                        </div>
                    </div>
                </div>

                {{-- Applicability --}}
                <div class="form-section">
                    <div class="d-flex align-items-center mb-3">
                        <div class="section-icon"><i class="ri-apps-line"></i></div>
                        <h5 class="mb-0">Applicability</h5>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Applies To <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 mb-3">
                                <div class="form-check">
                                    <input type="radio" name="applicable_to" id="allBills" value="all_bills" class="form-check-input" checked>
                                    <label class="form-check-label" for="allBills">All Bills</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" name="applicable_to" id="specificBills" value="specific_bills" class="form-check-input">
                                    <label class="form-check-label" for="specificBills">Specific Bills</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" name="applicable_to" id="specificCategories" value="specific_categories" class="form-check-input">
                                    <label class="form-check-label" for="specificCategories">Specific Categories</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12" id="billSelectionDiv" style="display: none;">
                            <label class="form-label fw-semibold">Select Bills</label>
                            <div class="border rounded-3 p-3" style="max-height: 200px; overflow-y: auto;">
                                @foreach($bills ?? [] as $bill)
                                    <div class="form-check mb-2">
                                        <input type="checkbox" name="applicable_bill_ids[]" value="{{ $bill->id }}" class="form-check-input" id="bill_{{ $bill->id }}">
                                        <label class="form-check-label" for="bill_{{ $bill->id }}">{{ $bill->title }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-12" id="categorySelectionDiv" style="display: none;">
                            <label class="form-label fw-semibold">Select Categories</label>
                            <div class="border rounded-3 p-3" style="max-height: 200px; overflow-y: auto;">
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="applicable_categories[]" value="TUITION" class="form-check-input" id="cat_tuition">
                                    <label class="form-check-label" for="cat_tuition">Tuition Fee</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="applicable_categories[]" value="DEV_LEVY" class="form-check-input" id="cat_dev">
                                    <label class="form-check-label" for="cat_dev">Development Levy</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="applicable_categories[]" value="ICT" class="form-check-input" id="cat_ict">
                                    <label class="form-check-label" for="cat_ict">ICT Fee</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="applicable_categories[]" value="SPORTS" class="form-check-input" id="cat_sports">
                                    <label class="form-check-label" for="cat_sports">Sports Fee</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="applicable_categories[]" value="LIBRARY" class="form-check-input" id="cat_library">
                                    <label class="form-check-label" for="cat_library">Library Fee</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Conditions --}}
                <div class="form-section">
                    <div class="d-flex align-items-center mb-3">
                        <div class="section-icon"><i class="ri-git-branch-line"></i></div>
                        <h5 class="mb-0">Conditions</h5>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Condition Type</label>
                            <select name="condition_type" id="conditionType" class="form-select">
                                <option value="none">No Condition</option>
                                <option value="early_payment">Early Payment</option>
                                <option value="min_amount">Minimum Amount</option>
                                <option value="sibling_count">Sibling Count</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="conditionValueDiv" style="display: none;">
                            <label class="form-label fw-semibold" id="conditionLabel">Condition Value</label>
                            <input type="number" name="condition_value" id="conditionValue" class="form-control" step="0.01" placeholder="Enter value">
                        </div>
                        <div class="col-md-6" id="daysBeforeDueDiv" style="display: none;">
                            <label class="form-label fw-semibold">Days Before Due Date</label>
                            <input type="number" name="days_before_due" class="form-control" placeholder="e.g., 7">
                        </div>
                    </div>
                </div>

                {{-- Stacking Rules --}}
                <div class="form-section">
                    <div class="d-flex align-items-center mb-3">
                        <div class="section-icon"><i class="ri-stack-line"></i></div>
                        <h5 class="mb-0">Stacking Rules</h5>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" name="stackable_with_scholarship" value="1" class="form-check-input" id="stackScholarship">
                                <label class="form-check-label" for="stackScholarship">Stackable with Scholarship</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" name="stackable_with_other_discounts" value="1" class="form-check-input" id="stackOther">
                                <label class="form-check-label" for="stackOther">Stackable with Other Discounts</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Stacking Priority</label>
                            <input type="number" name="stacking_priority" class="form-control" value="1" placeholder="Higher number = higher priority">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                {{-- Effective Dates --}}
                <div class="form-section">
                    <div class="d-flex align-items-center mb-3">
                        <div class="section-icon"><i class="ri-calendar-line"></i></div>
                        <h5 class="mb-0">Effective Dates</h5>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Effective From <span class="text-danger">*</span></label>
                            <input type="date" name="effective_from" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Effective To</label>
                            <input type="date" name="effective_to" class="form-control">
                            <small class="text-muted">Leave empty for ongoing</small>
                        </div>
                    </div>
                </div>

                {{-- Quick Tips --}}
                <div class="form-section bg-light">
                    <div class="d-flex align-items-center mb-3">
                        <div class="section-icon bg-success"><i class="ri-lightbulb-line"></i></div>
                        <h5 class="mb-0">Quick Tips</h5>
                    </div>
                    <ul class="small text-muted mb-0 ps-3">
                        <li class="mb-2">✓ Percentage discounts are calculated based on bill amount</li>
                        <li class="mb-2">✓ Fixed amount discounts deduct a set amount</li>
                        <li class="mb-2">✓ Early payment discounts apply when paid before due date</li>
                        <li class="mb-2">✓ Stacking allows multiple discounts to combine</li>
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
                        <i class="ri-save-line me-1"></i>Create Discount
                    </button>
                </div>
            </div>
        </div>
    </form>

</div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const valueType = document.getElementById('valueType');
    const valueLabel = document.getElementById('valueLabel');

    valueType.addEventListener('change', function() {
        if (this.value === 'percentage') {
            valueLabel.textContent = 'Value (%)';
        } else {
            valueLabel.textContent = 'Value (₦)';
        }
    });

    // Applicability toggles
    const allBillsRadio = document.getElementById('allBills');
    const specificBillsRadio = document.getElementById('specificBills');
    const specificCategoriesRadio = document.getElementById('specificCategories');
    const billSelectionDiv = document.getElementById('billSelectionDiv');
    const categorySelectionDiv = document.getElementById('categorySelectionDiv');

    allBillsRadio.addEventListener('change', function() {
        billSelectionDiv.style.display = 'none';
        categorySelectionDiv.style.display = 'none';
    });
    specificBillsRadio.addEventListener('change', function() {
        billSelectionDiv.style.display = 'block';
        categorySelectionDiv.style.display = 'none';
    });
    specificCategoriesRadio.addEventListener('change', function() {
        billSelectionDiv.style.display = 'none';
        categorySelectionDiv.style.display = 'block';
    });

    // Condition type toggles
    const conditionType = document.getElementById('conditionType');
    const conditionValueDiv = document.getElementById('conditionValueDiv');
    const daysBeforeDueDiv = document.getElementById('daysBeforeDueDiv');
    const conditionLabel = document.getElementById('conditionLabel');

    conditionType.addEventListener('change', function() {
        conditionValueDiv.style.display = this.value !== 'none' ? 'block' : 'none';
        daysBeforeDueDiv.style.display = this.value === 'early_payment' ? 'block' : 'none';

        if (this.value === 'early_payment') {
            conditionLabel.textContent = 'Discount Percentage (%)';
        } else if (this.value === 'min_amount') {
            conditionLabel.textContent = 'Minimum Amount (₦)';
        } else if (this.value === 'sibling_count') {
            conditionLabel.textContent = 'Number of Siblings';
        }
    });

    // Form submission
    const form = document.getElementById('discountForm');
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

        const formData = new FormData(form);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
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
                    window.location.href = '{{ route("admin.discount.index") }}';
                });
            } else {
                let errorMsg = data.message || 'Something went wrong';
                if (data.errors) {
                    errorMsg = Object.values(data.errors).flat().join('\n');
                }
                Swal.fire({ icon: 'error', title: 'Error!', text: errorMsg });
            }
        } catch (error) {
            Swal.fire({ icon: 'error', title: 'Error!', text: 'Network error. Please try again.' });
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
});
</script>
@endsection
