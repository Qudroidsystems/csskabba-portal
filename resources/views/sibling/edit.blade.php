@extends('layouts.master')

@section('content')
<style>
:root {
    --sib-primary: #1e3a5f;
    --sib-accent:  #2563eb;
    --sib-border:  #e2e8f0;
    --sib-radius:  12px;
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
    max-height: 420px;
    overflow-y: auto;
    border: 1px solid var(--sib-border);
    border-radius: var(--sib-radius);
    background: #f8fafc;
    min-height: 150px;
    padding: 12px;
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
.selected-student-card:last-child { margin-bottom: 0; }
.selected-student-card:hover {
    border-color: var(--sib-accent);
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
}

/* Avatar — image and placeholder sit in the same slot; one is hidden */
.avatar-wrap {
    position: relative;
    width: 48px;
    height: 48px;
    flex-shrink: 0;
}
.student-avatar-sm,
.avatar-placeholder-sm {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: 2px solid var(--sib-border);
    position: absolute;
    top: 0; left: 0;
}
.student-avatar-sm {
    object-fit: cover;
    background: #f0f0f0;
}
.avatar-placeholder-sm {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: 700;
    color: white;
}

/* Search modal */
.student-search-modal .modal-content { border-radius: 20px; overflow: hidden; }
.student-result-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    cursor: pointer;
    border-bottom: 1px solid var(--sib-border);
    transition: background 0.15s;
}
.student-result-item:hover { background: #f0f9ff; }
.student-result-item:last-child { border-bottom: none; }
.result-avatar-wrap {
    position: relative;
    width: 44px;
    height: 44px;
    flex-shrink: 0;
}
.student-result-avatar,
.student-result-avatar-placeholder {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    position: absolute;
    top: 0; left: 0;
}
.student-result-avatar { object-fit: cover; }
.student-result-avatar-placeholder {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 700;
    color: white;
}

.already-added-badge {
    font-size: 11px;
    background: #dcfce7;
    color: #166534;
    padding: 2px 8px;
    border-radius: 99px;
    white-space: nowrap;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- Page header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 fw-bold" style="color: var(--sib-primary);">
                        <i class="ri-group-line me-2"></i>{{ $pagetitle ?? 'Edit Family Group' }}
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
        <input type="hidden" name="id" value="{{ $group->id ?? '' }}">

        <div class="row">
            {{-- Left column --}}
            <div class="col-lg-8">

                {{-- Family Info --}}
                <div class="form-section">
                    <h5><i class="ri-information-line me-2"></i>Family Information</h5>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Group Number</label>
                            <input type="text" class="form-control bg-light"
                                   value="{{ $group->group_no ?? '' }}" readonly disabled>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Family Name <span class="text-danger">*</span></label>
                            <input type="text" name="family_name" id="family_name"
                                   class="form-control" value="{{ $group->family_name ?? '' }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Parent Phone</label>
                            <input type="text" name="parent_phone" id="parent_phone"
                                   class="form-control" value="{{ $group->parent_phone ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Parent Email</label>
                            <input type="email" name="parent_email" id="parent_email"
                                   class="form-control" value="{{ $group->parent_email ?? '' }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Address</label>
                            <textarea name="address" id="address" class="form-control"
                                      rows="2">{{ $group->address ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Students --}}
                <div class="form-section">
                    <h5><i class="ri-user-line me-2"></i>Children / Students</h5>
                    <div class="mb-3 d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-primary"
                                data-bs-toggle="modal" data-bs-target="#searchStudentModal">
                            <i class="ri-user-add-line me-1"></i>Add / Remove Students
                        </button>
                        <small class="text-muted">Search and manage students in this family group</small>
                    </div>

                    {{-- Selected students list --}}
                    <div id="selectedStudentsContainer" class="selected-students-container">
                        <div id="selectedStudentsList">
                            {{-- Rendered by JS --}}
                        </div>
                    </div>

                    {{-- Hidden: comma-separated IDs (for reference; actual POST uses student_ids[]) --}}
                    <input type="hidden" name="_student_ids_csv" id="studentIdsInput" value="">
                </div>

            </div>

            {{-- Right column --}}
            <div class="col-lg-4">
                <div class="form-section bg-light">
                    <h5><i class="ri-bar-chart-line me-2"></i>Statistics</h5>
                    <div class="mb-3">
                        <div class="small text-muted">Total Children</div>
                        <div class="fs-4 fw-bold" id="totalChildrenCount">
                            {{ count($initialStudents ?? []) }}
                        </div>
                    </div>
                    @if(!empty($group->discount_value))
                    <div class="mb-3">
                        <div class="small text-muted">Current Discount</div>
                        <div class="fs-5 text-success">
                            @if($group->discount_type === 'percentage')
                                {{ $group->discount_value }}%
                            @else
                                ₦{{ number_format($group->discount_value, 2) }} per child
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                <div class="form-section bg-light">
                    <h5><i class="ri-lightbulb-line me-2"></i>Quick Tips</h5>
                    <ul class="small text-muted mb-0 ps-3">
                        <li class="mb-2">✓ Update family information as needed</li>
                        <li class="mb-2">✓ Add or remove children from the family</li>
                        <li class="mb-2">✓ Apply sibling discounts from the main list</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Submit row --}}
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
                <h5 class="modal-title">
                    <i class="ri-user-search-line me-2"></i>Search &amp; Manage Students
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 border-bottom">
                    <div class="position-relative">
                        <input type="text" id="studentSearchInput"
                               class="form-control ps-5"
                               placeholder="Type name or admission number...">
                        <i class="ri-search-line position-absolute"
                           style="left:14px;top:50%;transform:translateY(-50%);color:#94a3b8;"></i>
                    </div>
                </div>
                <div id="studentSearchResults"
                     style="max-height:400px;overflow-y:auto;">
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
/* ---------------------------------------------------------------
   1.  Bootstrap initial students from PHP — always an array
--------------------------------------------------------------- */
let initialStudentsData = [];
try {
    const raw = @json($initialStudents ?? []);
    initialStudentsData = Array.isArray(raw) ? raw : [];
} catch (e) {
    console.error('[SiblingEdit] Failed to parse initialStudents:', e);
    initialStudentsData = [];
}

const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content
                   || '{{ csrf_token() }}';

/* ---------------------------------------------------------------
   2.  Global selected-students state
--------------------------------------------------------------- */
let selectedStudents = [];   // populated from initialStudentsData on DOM ready
let searchTimeout   = null;

/* ---------------------------------------------------------------
   3.  Utility: safe HTML escaping
--------------------------------------------------------------- */
function esc(text) {
    if (text == null) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

/* ---------------------------------------------------------------
   4.  Derive initials — never blank
--------------------------------------------------------------- */
function getInitials(student) {
    if (student.initials && student.initials.trim()) return student.initials.trim();
    const f = (student.firstname || 'X').charAt(0);
    const l = (student.lastname  || 'X').charAt(0);
    return (f + l).toUpperCase();
}

/* ---------------------------------------------------------------
   5.  Render the avatar HTML for a student object
       Uses both <img> + placeholder div in the same wrapper;
       the onerror on the img swaps visibility so fallback always works.
--------------------------------------------------------------- */
function avatarHtml(student, size) {
    const initials = getInitials(student);
    const imgClass  = size === 'sm' ? 'student-avatar-sm'              : 'student-result-avatar';
    const phClass   = size === 'sm' ? 'avatar-placeholder-sm'          : 'student-result-avatar-placeholder';
    const wrapClass = size === 'sm' ? 'avatar-wrap'                    : 'result-avatar-wrap';

    const imgTag = student.picture
        ? `<img src="${esc(student.picture)}" class="${imgClass}"
                onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">`
        : '';

    const phDisplay = student.picture ? 'display:none;' : '';

    return `
        <div class="${wrapClass}">
            ${imgTag}
            <div class="${phClass}" style="${phDisplay}">${esc(initials)}</div>
        </div>`;
}

/* ---------------------------------------------------------------
   6.  Render selected-students list
--------------------------------------------------------------- */
function updateSelectedStudentsList() {
    const container        = document.getElementById('selectedStudentsList');
    const studentIdsInput  = document.getElementById('studentIdsInput');
    const totalCount       = document.getElementById('totalChildrenCount');

    // Keep hidden CSV input in sync (informational; form uses student_ids[] appended in JS)
    studentIdsInput.value = selectedStudents.map(s => s.id).join(',');
    if (totalCount) totalCount.textContent = selectedStudents.length;

    if (selectedStudents.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="ri-user-add-line ri-2x d-block mb-2"></i>
                No students in this family yet.<br>
                Click <strong>Add / Remove Students</strong> to add family members.
            </div>`;
        return;
    }

    container.innerHTML = selectedStudents.map(s => `
        <div class="selected-student-card">
            ${avatarHtml(s, 'sm')}
            <div style="flex:1;min-width:0;">
                <div class="fw-semibold text-truncate">
                    ${esc(s.firstname)} ${esc(s.lastname)}
                </div>
                <div class="small text-muted">
                    Admission: ${esc(s.admission_no)} &nbsp;|&nbsp; Class: ${esc(s.class)}
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger flex-shrink-0"
                    onclick="removeStudent(${s.id})" title="Remove from family">
                <i class="ri-close-line"></i>
            </button>
        </div>
    `).join('');
}

/* ---------------------------------------------------------------
   7.  Global: add / remove student
--------------------------------------------------------------- */
function addStudent(student) {
    if (selectedStudents.some(s => s.id === student.id)) {
        // Toggle off — remove instead
        removeStudent(student.id);
        return;
    }
    selectedStudents.push(student);
    updateSelectedStudentsList();
    // Re-render search results to update the checkmark/add icon
    triggerSearchResultsRefresh();
}

function removeStudent(studentId) {
    selectedStudents = selectedStudents.filter(s => s.id !== studentId);
    updateSelectedStudentsList();
    triggerSearchResultsRefresh();
}

/* ---------------------------------------------------------------
   8.  Re-render search results (updates add/remove icons)
--------------------------------------------------------------- */
function triggerSearchResultsRefresh() {
    // If the modal is open and there are results, re-render them
    const resultsEl = document.getElementById('studentSearchResults');
    if (!resultsEl) return;
    // Find all result items and update their icon
    resultsEl.querySelectorAll('.student-result-item').forEach(item => {
        const sid     = parseInt(item.dataset.studentId, 10);
        const isAdded = selectedStudents.some(s => s.id === sid);
        const icon    = item.querySelector('.result-action-icon');
        if (icon) {
            icon.className = isAdded
                ? 'ri-checkbox-circle-line text-success fs-5 result-action-icon'
                : 'ri-add-circle-line text-primary fs-5 result-action-icon';
        }
    });
}

/* ---------------------------------------------------------------
   9.  Render search results list
--------------------------------------------------------------- */
function renderSearchResults(students) {
    if (!students || students.length === 0) {
        document.getElementById('studentSearchResults').innerHTML = `
            <div class="text-center text-muted py-5">
                <i class="ri-user-unfollow-line ri-2x d-block mb-2"></i>
                No students found
            </div>`;
        return;
    }

    const html = students.map(s => {
        const isAdded    = selectedStudents.some(ex => ex.id === s.id);
        const actionIcon = isAdded
            ? 'ri-checkbox-circle-line text-success'
            : 'ri-add-circle-line text-primary';
        const actionLabel = isAdded ? 'Remove' : 'Add';

        return `
            <div class="student-result-item" data-student-id="${s.id}"
                 onclick='handleResultClick(${JSON.stringify(s)})'>
                ${avatarHtml(s, 'result')}
                <div style="flex:1;min-width:0;">
                    <div class="fw-semibold text-truncate">
                        ${esc(s.firstname)} ${esc(s.lastname)}
                    </div>
                    <div class="small text-muted">
                        Admission: ${esc(s.admission_no)} &nbsp;|&nbsp; Class: ${esc(s.class)}
                    </div>
                </div>
                <div class="d-flex align-items-center gap-1 flex-shrink-0">
                    <span class="small text-muted">${actionLabel}</span>
                    <i class="${actionIcon} fs-5 result-action-icon"></i>
                </div>
            </div>`;
    }).join('');

    document.getElementById('studentSearchResults').innerHTML = html;
}

/* ---------------------------------------------------------------
   10. Handle result click — add or remove
--------------------------------------------------------------- */
function handleResultClick(student) {
    if (selectedStudents.some(s => s.id === student.id)) {
        removeStudent(student.id);
    } else {
        addStudent(student);
    }
}

/* ---------------------------------------------------------------
   11. DOM ready
--------------------------------------------------------------- */
$(document).ready(function () {

    // Copy initial data into working state
    selectedStudents = initialStudentsData.length ? [...initialStudentsData] : [];
    updateSelectedStudentsList();

    /* ---- Student search input ---- */
    $('#studentSearchInput').on('input', function () {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();

        if (query.length < 2) {
            $('#studentSearchResults').html(`
                <div class="text-center text-muted py-5">
                    <i class="ri-user-search-line ri-2x d-block mb-2"></i>
                    Type at least 2 characters to search
                </div>`);
            return;
        }

        $('#studentSearchResults').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary"></div>
                <p class="mt-2 text-muted">Searching...</p>
            </div>`);

        searchTimeout = setTimeout(() => {
            $.ajax({
                url: '/sibling/search-students',
                method: 'GET',
                data: { q: query },
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                success(response) {
                    if (response.success) {
                        renderSearchResults(response.students || []);
                    } else {
                        $('#studentSearchResults').html(`
                            <div class="text-center text-danger py-5">
                                <i class="ri-error-warning-line ri-2x d-block mb-2"></i>
                                Search failed. Please try again.
                            </div>`);
                    }
                },
                error(xhr) {
                    console.error('[SiblingEdit] Search error:', xhr.responseText);
                    $('#studentSearchResults').html(`
                        <div class="text-center text-danger py-5">
                            <i class="ri-error-warning-line ri-2x d-block mb-2"></i>
                            Search failed. Please try again.
                        </div>`);
                }
            });
        }, 300);
    });

    /* ---- Reset modal on close ---- */
    $('#searchStudentModal').on('show.bs.modal', function () {
        $('#studentSearchInput').val('');
        $('#studentSearchResults').html(`
            <div class="text-center text-muted py-5">
                <i class="ri-user-search-line ri-2x d-block mb-2"></i>
                Type at least 2 characters to search
            </div>`);
    });

    /* ---- Form submission ---- */
    $('#editGroupForm').on('submit', async function (e) {
        e.preventDefault();

        if (selectedStudents.length === 0) {
            Swal.fire('Error!', 'Please add at least one student to the family group.', 'error');
            return;
        }

        const submitBtn   = $('#submitBtn');
        const originalHtml = submitBtn.html();
        submitBtn.prop('disabled', true)
                 .html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');

        const id       = $('input[name="id"]').val();
        const formData = new FormData();
        formData.append('_token',      CSRF_TOKEN);
        formData.append('_method',     'PUT');
        formData.append('family_name', $('#family_name').val());
        formData.append('parent_phone', $('#parent_phone').val());
        formData.append('parent_email', $('#parent_email').val());
        formData.append('address',     $('#address').val());

        selectedStudents.forEach(s => formData.append('student_ids[]', s.id));

        try {
            const response = await fetch(`/sibling/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept':       'application/json',
                },
                body: formData,
            });

            const data = await response.json();

            if (data.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message || 'Family group updated successfully!',
                    confirmButtonText: 'OK',
                });
                window.location.href = '{{ route("sibling.index") }}';
            } else {
                let errorMsg = data.message || 'An error occurred.';
                if (data.errors) {
                    errorMsg = Object.values(data.errors).flat().join('\n');
                }
                Swal.fire('Error!', errorMsg, 'error');
            }
        } catch (err) {
            console.error('[SiblingEdit] Submit error:', err);
            Swal.fire('Error!', 'Something went wrong. Please try again.', 'error');
        } finally {
            submitBtn.prop('disabled', false).html(originalHtml);
        }
    });

});
</script>
@endsection
