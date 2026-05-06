@extends('layouts.master')

@section('content')
<style>
:root {
    --sib-primary: #1e3a5f;
    --sib-accent: #2563eb;
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
                            <input type="text" name="family_name" id="family_name" class="form-control" value="{{ $group->family_name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Parent Phone</label>
                            <input type="text" name="parent_phone" id="parent_phone" class="form-control" value="{{ $group->parent_phone }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Parent Email</label>
                            <input type="email" name="parent_email" id="parent_email" class="form-control" value="{{ $group->parent_email }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Address</label>
                            <textarea name="address" id="address" class="form-control" rows="2">{{ $group->address }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h5><i class="ri-user-line me-2"></i>Children / Students</h5>
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#searchStudentModal">
                            <i class="ri-user-add-line me-1"></i>Add/Remove Students
                        </button>
                        <small class="text-muted ms-2">Click to search and manage students in this family</small>
                    </div>

                    <div id="selectedStudentsContainer" class="selected-students-container p-3">
                        <div id="selectedStudentsList"></div>
                    </div>
                    <input type="hidden" name="student_ids" id="studentIdsInput" value="">
                </div>
            </div>

            <div class="col-lg-4">
                <div class="form-section bg-light">
                    <h5><i class="ri-bar-chart-line me-2"></i>Statistics</h5>
                    <div class="mb-3">
                        <div class="small text-muted">Total Children</div>
                        <div class="fs-4 fw-bold" id="totalChildrenCount">{{ count($initialStudents) }}</div>
                    </div>
                    @if($group->discount_value)
                    <div class="mb-3">
                        <div class="small text-muted">Current Discount</div>
                        <div class="fs-5 text-success">
                            {{ $group->discount_type === 'percentage' ? $group->discount_value . '%' : '₦' . number_format($group->discount_value, 2) . ' per child' }}
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
// Get initial students data from PHP
const initialStudentsData = @json($initialStudents);
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
let selectedStudents = [];
let searchTimeout = null;

console.log('Initial students data:', initialStudentsData);
console.log('Initial students count:', initialStudentsData ? initialStudentsData.length : 0);

$(document).ready(function() {
    // Copy initial students to selectedStudents
    if (initialStudentsData && initialStudentsData.length > 0) {
        selectedStudents = [...initialStudentsData];
        console.log('Loaded', selectedStudents.length, 'students');
    } else {
        console.log('No initial students found');
    }

    // Update the UI
    updateSelectedStudentsList();

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

            const searchUrl = '/sibling/search-students';

            $.ajax({
                url: searchUrl,
                method: 'GET',
                data: { q: query },
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.success && response.students && response.students.length) {
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
                error: function(xhr) {
                    console.error('Search error:', xhr);
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
            <div class="student-result-item" onclick='addStudent(${JSON.stringify(s).replace(/'/g, "\\'")})'>
                <div>
                    ${s.picture
                        ? `<img src="${s.picture}" class="student-result-avatar" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'student-result-avatar-placeholder\'>${s.initials}</div>'">`
                        : `<div class="student-result-avatar-placeholder">${escapeHtml(s.initials)}</div>`
                    }
                </div>
                <div style="flex: 1;">
                    <div class="fw-semibold">${escapeHtml(s.firstname)} ${escapeHtml(s.lastname)}</div>
                    <div class="small text-muted">Admission: ${escapeHtml(s.admission_no)} | Class: ${escapeHtml(s.class)}</div>
                </div>
                <div>
                    ${selectedStudents.some(existing => existing.id === s.id)
                        ? '<i class="ri-checkbox-circle-line text-success fs-5"></i>'
                        : '<i class="ri-add-circle-line text-primary fs-5"></i>'
                    }
                </div>
            </div>
        `).join('');

        $('#studentSearchResults').html(resultsHtml);
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text).replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }

    $('#searchStudentModal').on('show.bs.modal', function() {
        $('#studentSearchInput').val('');
        $('#studentSearchResults').html(`
            <div class="text-center text-muted py-5">
                <i class="ri-user-search-line ri-2x d-block mb-2"></i>
                Type at least 2 characters to search
            </div>
        `);
    });

    // Form submission
    $('#editGroupForm').on('submit', async function(e) {
        e.preventDefault();

        if (selectedStudents.length === 0) {
            Swal.fire('Error!', 'Please add at least one student to the family group.', 'error');
            return;
        }

        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');

        const id = $('input[name="id"]').val();

        const formData = new FormData();
        formData.append('_token', CSRF_TOKEN);
        formData.append('_method', 'PUT');
        formData.append('family_name', $('#family_name').val());
        formData.append('parent_phone', $('#parent_phone').val());
        formData.append('parent_email', $('#parent_email').val());
        formData.append('address', $('#address').val());

        selectedStudents.forEach(s => {
            formData.append('student_ids[]', s.id);
        });

        try {
            const response = await fetch(`/sibling/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                },
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
            console.error('Submit error:', error);
            Swal.fire('Error!', 'Something went wrong. Please try again.', 'error');
        } finally {
            submitBtn.prop('disabled', false).html(originalText);
        }
    });
});

// Global functions
function addStudent(student) {
    if (selectedStudents.some(s => s.id === student.id)) {
        Swal.fire('Info', 'Student already in this family.', 'info');
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
    const studentIdsInput = $('#studentIdsInput');
    const totalChildrenCount = $('#totalChildrenCount');

    studentIdsInput.val(selectedStudents.map(s => s.id).join(','));
    if (totalChildrenCount.length) totalChildrenCount.text(selectedStudents.length);

    console.log('Updating student list, count:', selectedStudents.length);

    if (selectedStudents.length === 0) {
        container.html(`
            <div class="text-center text-muted py-4">
                <i class="ri-user-line ri-2x d-block mb-2"></i>
                No students in this family. Click "Add/Remove Students" to add family members.
            </div>
        `);
        return;
    }

    const studentsHtml = selectedStudents.map(s => `
        <div class="selected-student-card">
            <div>
                ${s.picture
                    ? `<img src="${s.picture}" class="student-avatar-sm" onerror="this.onerror=null; this.style.display='none'; this.parentElement.innerHTML='<div class=\'avatar-placeholder-sm\'>${escapeHtmlStatic(s.initials)}</div>'">`
                    : `<div class="avatar-placeholder-sm">${escapeHtmlStatic(s.initials)}</div>`
                }
            </div>
            <div style="flex: 1;">
                <div class="fw-semibold">${escapeHtmlStatic(s.firstname)} ${escapeHtmlStatic(s.lastname)}</div>
                <div class="small text-muted">Admission: ${escapeHtmlStatic(s.admission_no)} | Class: ${escapeHtmlStatic(s.class)}</div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeStudent(${s.id})">
                <i class="ri-close-line"></i>
            </button>
        </div>
    `).join('');

    container.html(studentsHtml);
}

function escapeHtmlStatic(text) {
    if (!text) return '';
    return String(text).replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}
</script>
@endsection
