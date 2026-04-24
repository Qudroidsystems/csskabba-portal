{{-- resources/views/payment/student-list.blade.php --}}
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

.pay-hero {
    background: linear-gradient(135deg, var(--pay-primary) 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--pay-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
}

.student-card {
    background: white;
    border: 1px solid var(--pay-border);
    border-radius: var(--pay-radius);
    padding: 16px;
    transition: all 0.2s ease;
    cursor: pointer;
}
.student-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,.1);
    border-color: var(--pay-accent);
}
.student-avatar {
    width: 60px; height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--pay-accent);
}
.savings-badge {
    background: #dcfce7;
    color: #16a34a;
    border-radius: 20px;
    padding: 4px 10px;
    font-size: 11px;
    font-weight: 600;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.6);
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

    <div class="pay-hero">
        <h1 class="text-white"><i class="ri-wallet-line me-2"></i>{{ $pagetitle }}</h1>
        <p class="text-white-50 mb-0">Select a student to view payment details, make payments, or generate invoices.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="search-box">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by name or admission number...">
                <i class="ri-search-line search-icon"></i>
            </div>
        </div>
        <div class="col-md-3">
            <select id="classFilter" class="form-select">
                <option value="">All Classes</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->arm }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select id="savingsFilter" class="form-select">
                <option value="">All Students</option>
                <option value="has_scholarship">Has Scholarship</option>
                <option value="has_discount">Has Discount</option>
                <option value="no_savings">No Savings Applied</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-primary w-100" id="resetFilters">
                <i class="ri-refresh-line me-1"></i>Reset
            </button>
        </div>
    </div>

    <div class="row g-3" id="studentsGrid">
        @forelse($students as $student)
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="student-card" data-student-id="{{ $student->id }}"
                     data-name="{{ strtolower($student->firstname . ' ' . $student->lastname) }}"
                     data-admission="{{ $student->admissionNo }}"
                     data-class="{{ $student->schoolclassid }}"
                     data-term="{{ $student->termid ?? '' }}"
                     data-session="{{ $student->sessionid ?? '' }}">
                    <div class="d-flex align-items-start gap-3">
                        <img src="{{ $student->avatar ?? asset('assets/images/default-avatar.png') }}" alt="Student" class="student-avatar">
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold">{{ $student->firstname }} {{ $student->lastname }}</h6>
                            <small class="text-muted d-block">{{ $student->admissionNo }}</small>
                            <small class="text-muted d-block">{{ $student->schoolclass }} {{ $student->arm }}</small>
                            <small class="text-muted d-block">{{ $student->term }} | {{ $student->session }}</small>

                            <div class="mt-2 d-flex gap-2 flex-wrap">
                                @if($student->has_scholarship)
                                    <span class="savings-badge"><i class="ri-graduation-cap-line me-1"></i>Scholarship</span>
                                @endif
                                @if($student->has_discount)
                                    <span class="savings-badge"><i class="ri-discount-line me-1"></i>Discount</span>
                                @endif
                                @if($student->total_savings > 0)
                                    <span class="savings-badge"><i class="ri-money-saved-line me-1"></i>Saved ₦{{ number_format($student->total_savings) }}</span>
                                @endif
                            </div>
                        </div>
                        <i class="ri-arrow-right-s-line text-muted"></i>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center py-5">
                    <i class="ri-inbox-line ri-2x d-block mb-2"></i>
                    No students found.
                </div>
            </div>
        @endforelse
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
    const searchInput = document.getElementById('searchInput');
    const classFilter = document.getElementById('classFilter');
    const savingsFilter = document.getElementById('savingsFilter');
    const resetBtn = document.getElementById('resetFilters');
    const cards = document.querySelectorAll('.student-card');
    const loadingOverlay = document.getElementById('loadingOverlay');

    function filterStudents() {
        const searchTerm = searchInput?.value.toLowerCase() || '';
        const classValue = classFilter?.value || '';
        const savingsValue = savingsFilter?.value || '';

        cards.forEach(card => {
            const name = card.dataset.name || '';
            const admission = card.dataset.admission || '';
            const studentClass = card.dataset.class || '';
            const hasScholarship = card.querySelector('.savings-badge:contains("Scholarship")') !== null;
            const hasDiscount = card.querySelector('.savings-badge:contains("Discount")') !== null;

            let matchesSearch = name.includes(searchTerm) || admission.includes(searchTerm);
            let matchesClass = !classValue || studentClass === classValue;
            let matchesSavings = true;

            if (savingsValue === 'has_scholarship') matchesSavings = hasScholarship;
            else if (savingsValue === 'has_discount') matchesSavings = hasDiscount;
            else if (savingsValue === 'no_savings') matchesSavings = !hasScholarship && !hasDiscount;

            card.closest('.col-md-6, .col-lg-4, .col-xl-3')?.style.setProperty('display', (matchesSearch && matchesClass && matchesSavings) ? '' : 'none');
        });
    }

    searchInput?.addEventListener('input', filterStudents);
    classFilter?.addEventListener('change', filterStudents);
    savingsFilter?.addEventListener('change', filterStudents);

    resetBtn?.addEventListener('click', () => {
        if (searchInput) searchInput.value = '';
        if (classFilter) classFilter.value = '';
        if (savingsFilter) savingsFilter.value = '';
        filterStudents();
    });

    cards.forEach(card => {
        card.addEventListener('click', function() {
            const studentId = this.dataset.studentId;
            const termId = this.dataset.term;
            const sessionId = this.dataset.session;

            if (studentId) {
                // Show term/session selection modal first
                showTermSessionModal(studentId);
            }
        });
    });

    function showTermSessionModal(studentId) {
        Swal.fire({
            title: 'Select Term & Session',
            html: `
                <div class="mb-3">
                    <label class="form-label fw-semibold">Term</label>
                    <select id="termSelect" class="form-select">
                        <option value="">-- Select Term --</option>
                        @foreach($terms ?? [] as $term)
                            <option value="{{ $term->id }}">{{ $term->term }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Session</label>
                    <select id="sessionSelect" class="form-select">
                        <option value="">-- Select Session --</option>
                        @foreach($sessions ?? [] as $session)
                            <option value="{{ $session->id }}">{{ $session->session }}</option>
                        @endforeach
                    </select>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'View Payments',
            preConfirm: () => {
                const termId = document.getElementById('termSelect').value;
                const sessionId = document.getElementById('sessionSelect').value;
                if (!termId || !sessionId) {
                    Swal.showValidationMessage('Please select both term and session');
                    return false;
                }
                return { termId, sessionId };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `/payment/student/${studentId}/class/${classId}/term/${result.value.termId}/session/${result.value.sessionId}`;
            }
        });
    }
});
</script>
@endsection
