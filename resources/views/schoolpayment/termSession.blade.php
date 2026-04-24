{{-- resources/views/schoolpayment/termSession.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
:root {
    --pay-primary: #1e3a5f;
    --pay-accent: #2563eb;
    --pay-success: #16a34a;
    --pay-warning: #d97706;
    --pay-border: #e2e8f0;
    --pay-radius: 12px;
}

.term-hero {
    background: linear-gradient(135deg, var(--pay-primary) 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--pay-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    display: none;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="term-hero">
        <h1 class="text-white"><i class="ri-wallet-line me-2"></i>Student Payment Portal</h1>
        <p class="text-white-50 mb-0">Select term and session to view payment details and make payments</p>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-semibold">Select Term and Session</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-4">
                        <a href="{{ route('schoolpayment.index') }}" class="btn btn-outline-primary">
                            <i class="ri-arrow-left-line me-1"></i>Back to Students
                        </a>
                    </div>

                    <form id="termSessionForm">
                        @csrf
                        <input type="hidden" name="studentId" value="{{ $id }}">

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Select Term <span class="text-danger">*</span></label>
                                <select name="termid" id="termid" class="form-select form-select-lg" required>
                                    <option value="">-- Select Term --</option>
                                    @foreach ($schoolterms as $term)
                                        <option value="{{ $term->id }}">{{ $term->term }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Select Session <span class="text-danger">*</span></label>
                                <select name="sessionid" id="sessionid" class="form-select form-select-lg" required>
                                    <option value="">-- Select Session --</option>
                                    @foreach ($schoolsessions as $session)
                                        <option value="{{ $session->id }}">{{ $session->session }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg px-5" id="submitBtn">
                                <i class="ri-search-eye-line me-2"></i>View Payment Details
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>

<div class="loading-overlay" id="loadingOverlay">
    <div class="bg-white rounded-3 p-4 text-center">
        <div class="spinner-border text-primary mb-3"></div>
        <div class="fw-semibold">Loading payment details...</div>
        <small class="text-muted">Please wait</small>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('termSessionForm');
    const termSelect = document.getElementById('termid');
    const sessionSelect = document.getElementById('sessionid');
    const submitBtn = document.getElementById('submitBtn');
    const loadingOverlay = document.getElementById('loadingOverlay');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const termid = termSelect.value;
        const sessionid = sessionSelect.value;

        if (!termid || !sessionid) {
            Swal.fire({
                icon: 'warning',
                title: 'Incomplete Selection',
                text: 'Please select both term and session to continue.',
                confirmButtonColor: '#2563eb'
            });
            return;
        }

        loadingOverlay.style.display = 'flex';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';

        const formData = new FormData(form);

        fetch('{{ route("schoolpayment.termsessionpayments") }}?' + new URLSearchParams(formData), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Display payment details in a modal or redirect
                showPaymentDetails(data);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'No payment records found.'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load payment details. Please try again.'
            });
        })
        .finally(() => {
            loadingOverlay.style.display = 'none';
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="ri-search-eye-line me-2"></i>View Payment Details';
        });
    });

    function showPaymentDetails(data) {
        // You can redirect to a payment details page
        window.location.href = `{{ route('schoolpayment.termsessionpayments') }}?studentId=${data.student_id}&termid=${termSelect.value}&sessionid=${sessionSelect.value}`;
    }
});
</script>
@endsection
