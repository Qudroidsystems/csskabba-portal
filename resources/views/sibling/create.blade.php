@extends('layouts.master')

@section('content')
<style>
:root {
    --sib-primary: #1e3a5f;
    --sib-accent: #2563eb;
    --sib-success: #16a34a;
    --sib-border: #e2e8f0;
    --sib-radius: 12px;
}

.form-section {
    background: white;
    border: 1px solid var(--sib-border);
    border-radius: var(--sib-radius);
    padding: 24px;
    margin-bottom: 24px;
}
.form-section h5 {
    font-size: 16px;
    font-weight: 600;
    color: var(--sib-primary);
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--sib-border);
}

/* Student Cards Container */
.selected-students-container {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid var(--sib-border);
    border-radius: var(--sib-radius);
    background: #f8fafc;
    min-height: 150px;
}
.selected-student-card {
    background: white;
    border: 1px solid var(--sib-border);
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 10px;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 12px;
}
.selected-student-card:hover {
    border-color: var(--sib-accent);
    box-shadow: 0 2px 8px rgba(0,0,0,.05);
}
.student-avatar-sm {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--sib-border);
    background: #f0f0f0;
}
.avatar-placeholder-sm {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: 700;
    color: white;
    border: 2px solid var(--sib-border);
}
.student-search-modal .modal-content {
    border-radius: 20px;
    overflow: hidden;
}
.student-result-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    cursor: pointer;
    border-bottom: 1px solid var(--sib-border);
    transition: background 0.15s;
}
.student-result-item:hover {
    background: #f0f9ff;
}
.student-result-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
}
.student-result-avatar-placeholder {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 700;
    color: white;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 fw-bold" style="color: var(--sib-primary);">
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
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#searchStudentModal">
                            <i class="ri-user-add-line me-1"></i>Add Students
                        </button>
                        <small class="text-muted ms-2">Click to search and add students to this family</small>
                    </div>

                    <div id="selectedStudentsContainer" class="selected-students-container p-3">
                        <div class="text-center text-muted py-4" id="noStudentsMsg">
                            <i class="ri-user-line ri-2x d-block mb-2"></i>
                            No students added yet. Click "Add Students" to add family members.
                        </div>
                        <div id="selectedStudentsList"></div>
                    </div>
                    <input type="hidden" name="student_ids" id="studentIdsInput" value="">
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
                        <strong>Note:</strong> Additional 5% discount for each subsequent child after the first (max 50%).
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

{{-- Search Student Modal --}}
<div class="modal fade" id="searchStudentModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content student-search-modal">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="ri-user-search-line me-2"></i>Search Students</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 border-bottom">
                    <div class="position-relative">
                        <input type="text" id="studentSearchInput" class="form-control ps-5" placeholder="Type name or admission number...">
                        <i class="ri-search-line position-absolute" style="left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                    </div>
                </div>
                <div id="studentSearchResults" class="student-search-results" style="max-height: 400px; overflow-y: auto;">
                    <div class="text-center text-muted py-5">
                        <i class="ri-user-search-line ri-2x d-block mb-2"></i>
                        Type at least 2 characters to search
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;
let selectedStudents = [];
let searchTimeout = null;

$(document).ready(function() {
    // Student search in modal
    $('#studentSearchInput').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();

        if (query.length < 2) {
            $('#studentSearchResults').html(`
                <div class="text-center text-muted py-5">
                    <i class="ri-user-search-line ri-2x d-block mb-2"></i>
                    Type at least 2 characters to search
                </div>
            `);
            return;
        }

        searchTimeout = setTimeout(() => {
            $('#studentSearchResults').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">Searching...</p>
                </div>
            `);

            $.ajax({
                url: '{{ route("sibling.search-students") }}',
                method: 'GET',
                data: { q: query },
                success: function(response) {
                    if (response.success && response.students.length) {
                        renderSearchResults(response.students);
                    } else {
                        $('#studentSearchResults').html(`
                            <div class="text-center text-muted py-5">
                                <i class="ri-user-unfollow-line ri-2x d-block mb-2"></i>
                                No students found
                            </div>
                        `);
                    }
                },
                error: function() {
                    $('#studentSearchResults').html(`
                        <div class="text-center text-danger py-5">
                            <i class="ri-error-warning-line ri-2x d-block mb-2"></i>
                            Search failed. Please try again.
                        </div>
                    `);
                }
            });
        }, 300);
    });

    function renderSearchResults(students) {
        const resultsHtml = students.map(s => `
            <div class="student-result-item" onclick="addStudent(${JSON.stringify(s).replace(/"/g, '&quot;')})">
                <div>
                    ${s.picture
                        ? `<img src="${s.picture}" class="student-result-avatar">`
                        : `<div class="student-result-avatar-placeholder">${s.initials}</div>`
                    }
                </div>
                <div style="flex: 1;">
                    <div class="fw-semibold">${s.firstname} ${s.lastname}</div>
                    <div class="small text-muted">Admission: ${s.admission_no} | Class: ${s.class}</div>
                </div>
                <div>
                    <i class="ri-add-circle-line text-primary fs-5"></i>
                </div>
            </div>
        `).join('');

        $('#studentSearchResults').html(resultsHtml);
    }

    // Reset modal when opened
    $('#searchStudentModal').on('show.bs.modal', function() {
        $('#studentSearchInput').val('');
        $('#studentSearchResults').html(`
            <div class="text-center text-muted py-5">
                <i class="ri-user-search-line ri-2x d-block mb-2"></i>
                Type at least 2 characters to search
            </div>
        `);
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

        if (selectedStudents.length === 0) {
            Swal.fire('Error!', 'Please add at least one student to the family group.', 'error');
            return;
        }

        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Creating...');

        const formData = $(this).serialize();
        const finalFormData = formData + `&student_ids[]=${selectedStudents.map(s => s.id).join('&student_ids[]=')}`;

        try {
            const response = await fetch('{{ route("sibling.store") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: finalFormData
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

// Global function to add student
function addStudent(student) {
    if (selectedStudents.some(s => s.id === student.id)) {
        Swal.fire('Info', 'Student already added to this family.', 'info');
        return;
    }

    selectedStudents.push(student);
    updateSelectedStudentsList();
    $('#searchStudentModal').modal('hide');
}

function removeStudent(studentId) {
    selectedStudents = selectedStudents.filter(s => s.id !== studentId);
    updateSelectedStudentsList();
}

function updateSelectedStudentsList() {
    const container = $('#selectedStudentsList');
    const noStudentsMsg = $('#noStudentsMsg');
    const studentIdsInput = $('#studentIdsInput');

    studentIdsInput.val(selectedStudents.map(s => s.id).join(','));

    if (selectedStudents.length === 0) {
        noStudentsMsg.show();
        container.html('');
        return;
    }

    noStudentsMsg.hide();

    const studentsHtml = selectedStudents.map(s => `
        <div class="selected-student-card">
            <div>
                ${s.picture
                    ? `<img src="${s.picture}" class="student-avatar-sm">`
                    : `<div class="avatar-placeholder-sm">${s.initials}</div>`
                }
            </div>
            <div style="flex: 1;">
                <div class="fw-semibold">${s.firstname} ${s.lastname}</div>
                <div class="small text-muted">Admission: ${s.admission_no} | Class: ${s.class}</div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeStudent(${s.id})">
                <i class="ri-close-line"></i>
            </button>
        </div>
    `).join('');

    container.html(studentsHtml);
}
</script>
@endsection
