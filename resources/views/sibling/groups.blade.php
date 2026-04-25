{{-- resources/views/sibling/groups.blade.php --}}
@extends('layouts.master')

@section('content')
<style>
:root {
    --sibling-primary: #1e3a5f;
    --sibling-accent: #2563eb;
    --sibling-success: #16a34a;
    --sibling-warning: #d97706;
    --sibling-danger: #dc2626;
    --sibling-border: #e2e8f0;
    --sibling-radius: 12px;
}

.sibling-hero {
    background: linear-gradient(135deg, var(--sibling-primary) 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--sibling-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
}
.sibling-hero h1 { font-size: 22px; font-weight: 700; color: white; margin: 0 0 6px; }
.sibling-hero p { font-size: 13px; color: rgba(255,255,255,.75); margin: 0; }

.stat-card {
    background: white;
    border: 1px solid var(--sibling-border);
    border-radius: var(--sibling-radius);
    padding: 18px 20px;
    transition: transform .15s, box-shadow .15s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.08); }
.stat-card .stat-value { font-size: 28px; font-weight: 700; color: var(--sibling-primary); }
.stat-card .stat-label { font-size: 12px; color: #6b7280; margin-top: 4px; }

.group-card {
    background: white;
    border: 1px solid var(--sibling-border);
    border-radius: var(--sibling-radius);
    padding: 20px;
    margin-bottom: 20px;
    transition: all 0.2s;
}
.group-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,.1);
}
.student-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #eff6ff;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    margin: 3px;
}
.discount-badge {
    background: #dcfce7;
    color: #16a34a;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    <div class="sibling-hero">
        <h1><i class="ri-group-line me-2"></i>{{ $pagetitle ?? 'Sibling Groups Management' }}</h1>
        <p>Manage family groups, apply sibling discounts, and track family savings across multiple children.</p>
    </div>

    {{-- Statistics Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-group-line"></i></div>
                <div class="stat-value" id="totalGroups">0</div>
                <div class="stat-label">Total Families</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-user-line"></i></div>
                <div class="stat-value" id="totalStudents">0</div>
                <div class="stat-label">Total Students</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-money-saved-line"></i></div>
                <div class="stat-value text-success" id="totalSavings">₦0</div>
                <div class="stat-label">Total Savings</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-discount-line"></i></div>
                <div class="stat-value text-warning" id="activeDiscounts">0</div>
                <div class="stat-label">Active Discounts</div>
            </div>
        </div>
    </div>

    {{-- Actions Bar --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="search-box">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search by family name...">
                        <i class="ri-search-line search-icon"></i>
                    </div>
                </div>
                <div class="col-md-6">
                    <select class="form-select" id="discountFilter">
                        <option value="">All Groups</option>
                        <option value="has_discount">Has Discount Applied</option>
                        <option value="no_discount">No Discount</option>
                    </select>
                </div>
                <div class="col-md-2 text-end">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createGroupModal">
                        <i class="ri-add-line me-1"></i>Create Family Group
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Groups List --}}
    <div id="groupsContainer"></div>

</div>
</div>
</div>

{{-- Create Group Modal --}}
<div class="modal fade" id="createGroupModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="ri-group-line me-2"></i>Create Family Group</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createGroupForm">
                @csrf
                <div class="modal-body">
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
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Select Students <span class="text-danger">*</span></label>
                            <select name="student_ids[]" id="studentSelect" class="form-select select2" multiple="multiple" required>
                                <option value="">Search for students...</option>
                            </select>
                            <small class="text-muted">Select all children belonging to this family</small>
                        </div>
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
                    <div class="alert alert-danger d-none mt-3" id="formErrors"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Family Group</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Group Modal --}}
<div class="modal fade" id="editGroupModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="ri-edit-line me-2"></i>Edit Family Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editGroupForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="editGroupId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Family Name <span class="text-danger">*</span></label>
                            <input type="text" name="family_name" id="editFamilyName" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Parent Phone</label>
                            <input type="text" name="parent_phone" id="editParentPhone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Parent Email</label>
                            <input type="email" name="parent_email" id="editParentEmail" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Address</label>
                            <textarea name="address" id="editAddress" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Select Students</label>
                            <select name="student_ids[]" id="editStudentSelect" class="form-select select2" multiple="multiple">
                                <option value="">Search for students...</option>
                            </select>
                        </div>
                    </div>
                    <div class="alert alert-danger d-none mt-3" id="editFormErrors"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Family Group</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Apply Discount Modal --}}
<div class="modal fade" id="applyDiscountModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="ri-discount-line me-2"></i>Apply Family Discount</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="applyDiscountForm">
                @csrf
                <input type="hidden" name="group_id" id="discountGroupId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Discount Type <span class="text-danger">*</span></label>
                        <select name="discount_type" id="applyDiscountType" class="form-select" required>
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed_per_child">Fixed Amount per Child (₦)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" id="applyDiscountValueLabel">Discount Value (%)</label>
                        <input type="number" name="discount_value" id="applyDiscountValue" class="form-control" step="0.01" required>
                    </div>
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        <strong>Note:</strong> Additional 5% discount for each subsequent child after the first.
                    </div>
                    <div class="alert alert-danger d-none" id="discountFormErrors"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Apply Discount</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="ri-delete-bin-line me-2"></i>Delete Family Group</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this family group?</p>
                <p class="text-muted small">This will also remove any sibling discounts applied to students in this group.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

{{-- View Group Modal --}}
<div class="modal fade" id="viewGroupModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="ri-group-line me-2"></i>Family Group Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewGroupContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">Loading...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;
let currentDeleteId = null;

$(document).ready(function() {
    // Initialize Select2 for student search (Create modal)
    $('#studentSelect').select2({
        dropdownParent: $('#createGroupModal'),
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
        minimumInputLength: 2
    });

    // Initialize Select2 for student search (Edit modal)
    $('#editStudentSelect').select2({
        dropdownParent: $('#editGroupModal'),
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
        minimumInputLength: 2
    });

    // Discount type toggle in create modal
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

    // Discount type toggle in apply discount modal
    $('#applyDiscountType').on('change', function() {
        const valueLabel = $('#applyDiscountValueLabel');
        if (this.value === 'percentage') {
            valueLabel.text('Discount Value (%)');
        } else {
            valueLabel.text('Discount Value (₦ per child)');
        }
    });

    // Load groups
    loadGroups();

    // Search and filter
    $('#searchInput').on('keyup', function() {
        filterGroups();
    });

    $('#discountFilter').on('change', function() {
        filterGroups();
    });

    function filterGroups() {
        const searchTerm = $('#searchInput').val().toLowerCase();
        const discountFilter = $('#discountFilter').val();

        $('.group-card').each(function() {
            const familyName = $(this).data('family-name')?.toLowerCase() || '';
            const hasDiscount = $(this).data('has-discount') === true;

            let matchesSearch = familyName.includes(searchTerm) || searchTerm === '';
            let matchesFilter = true;

            if (discountFilter === 'has_discount') {
                matchesFilter = hasDiscount;
            } else if (discountFilter === 'no_discount') {
                matchesFilter = !hasDiscount;
            }

            $(this).toggle(matchesSearch && matchesFilter);
        });
    }

    // Load all groups
    function loadGroups() {
        $.ajax({
            url: '{{ route("sibling.index") }}',
            type: 'GET',
            data: { ajax: true },
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
            success: function(response) {
                if (response.success) {
                    renderGroups(response.data);
                    updateStats(response.stats);
                }
            },
            error: function() {
                $('#groupsContainer').html('<div class="alert alert-danger text-center">Failed to load family groups.</div>');
            }
        });
    }

    function renderGroups(groups) {
        const container = $('#groupsContainer');
        if (!groups || groups.length === 0) {
            container.html(`
                <div class="alert alert-info text-center py-5">
                    <i class="ri-inbox-line ri-2x d-block mb-2"></i>
                    No family groups found. Click "Create Family Group" to add one.
                </div>
            `);
            return;
        }

        let html = '<div class="row">';
        groups.forEach(group => {
            const studentsHtml = group.students.map(s => `
                <span class="student-badge">
                    <i class="ri-user-line"></i> ${s.name} (${s.admission_no})
                </span>
            `).join('');

            const discountHtml = group.discount_value ? `
                <div class="discount-badge mt-2">
                    <i class="ri-discount-line me-1"></i>
                    ${group.discount_type === 'percentage' ? group.discount_value + '% off' : '₦' + group.discount_value + ' per child'}
                </div>
            ` : '<span class="text-muted small">No discount applied</span>';

            html += `
                <div class="col-md-6 col-lg-4">
                    <div class="group-card" data-family-name="${group.family_name}" data-has-discount="${!!group.discount_value}">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="mb-1 fw-bold">${group.family_name}</h5>
                                <small class="text-muted">Group #: ${group.group_no}</small>
                            </div>
                            ${discountHtml}
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted mb-2">Children (${group.students.length})</div>
                            <div class="d-flex flex-wrap gap-1">
                                ${studentsHtml || '<span class="text-muted">No students assigned</span>'}
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button class="btn btn-sm btn-info view-group" data-id="${group.id}">
                                <i class="ri-eye-line"></i> View
                            </button>
                            <button class="btn btn-sm btn-primary edit-group" data-id="${group.id}">
                                <i class="ri-pencil-line"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-success apply-discount" data-id="${group.id}">
                                <i class="ri-discount-line"></i> Discount
                            </button>
                            <button class="btn btn-sm btn-danger delete-group" data-id="${group.id}" data-name="${group.family_name}">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>

                        <div class="mt-2 pt-2 border-top">
                            <small class="text-muted">
                                <i class="ri-phone-line me-1"></i> ${group.parent_phone || 'N/A'} |
                                <i class="ri-mail-line me-1"></i> ${group.parent_email || 'N/A'}
                            </small>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.html(html);
    }

    function updateStats(stats) {
        if (stats) {
            $('#totalGroups').text(stats.total_groups || 0);
            $('#totalStudents').text(stats.total_students || 0);
            $('#totalSavings').text('₦' + (stats.total_savings?.toLocaleString() || '0'));
            $('#activeDiscounts').text(stats.active_discounts || 0);
        }
    }

    // Create Group Form Submit
    $('#createGroupForm').on('submit', async function(e) {
        e.preventDefault();
        const formData = $(this).serialize();

        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Creating...');

        try {
            const response = await fetch('{{ route("sibling.store") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                Swal.fire('Success!', data.message, 'success');
                $('#createGroupModal').modal('hide');
                $('#createGroupForm')[0].reset();
                $('#studentSelect').val(null).trigger('change');
                loadGroups();
            } else {
                $('#formErrors').removeClass('d-none').html(data.message);
            }
        } catch (error) {
            Swal.fire('Error!', 'Something went wrong', 'error');
        } finally {
            submitBtn.prop('disabled', false).html(originalText);
        }
    });

    // View Group
    $(document).on('click', '.view-group', function() {
        const id = $(this).data('id');
        const modal = new bootstrap.Modal(document.getElementById('viewGroupModal'));

        $.ajax({
            url: `/sibling-groups/${id}`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const group = response.data.group;
                    const students = response.data.students;

                    const studentsList = students.map(s => `
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${s.name}</strong><br>
                                    <small class="text-muted">Admission: ${s.admission_no}</small>
                                </div>
                                <span class="badge bg-info">Class: ${s.class}</span>
                            </div>
                        </li>
                    `).join('');

                    $('#viewGroupContent').html(`
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3">
                                    <div class="small text-muted">Family Name</div>
                                    <div class="fw-bold fs-5">${group.family_name}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3">
                                    <div class="small text-muted">Group Number</div>
                                    <div class="fw-bold"><code>${group.group_no}</code></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3">
                                    <div class="small text-muted">Parent Contact</div>
                                    <div><i class="ri-phone-line me-1"></i> ${group.parent_phone || 'N/A'}</div>
                                    <div><i class="ri-mail-line me-1"></i> ${group.parent_email || 'N/A'}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3">
                                    <div class="small text-muted">Discount Applied</div>
                                    <div class="fw-bold text-success">
                                        ${group.discount_value ? (group.discount_type === 'percentage' ? group.discount_value + '%' : '₦' + group.discount_value + ' per child') : 'None'}
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="border rounded-3 p-3">
                                    <div class="small text-muted mb-2">Address</div>
                                    <div>${group.address || 'N/A'}</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="border rounded-3 p-3">
                                    <div class="small text-muted mb-2">Children (${students.length})</div>
                                    <ul class="list-group list-group-flush">${studentsList || '<li class="list-group-item">No students assigned</li>'}</ul>
                                </div>
                            </div>
                        </div>
                    `);
                    modal.show();
                }
            },
            error: function() {
                $('#viewGroupContent').html('<div class="alert alert-danger">Failed to load group details.</div>');
            }
        });
    });

    // Edit Group
    $(document).on('click', '.edit-group', function() {
        const id = $(this).data('id');

        $.ajax({
            url: `/sibling-groups/${id}`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const group = response.data.group;
                    const students = response.data.students;

                    $('#editGroupId').val(group.id);
                    $('#editFamilyName').val(group.family_name);
                    $('#editParentPhone').val(group.parent_phone);
                    $('#editParentEmail').val(group.parent_email);
                    $('#editAddress').val(group.address);

                    // Populate selected students
                    const selectedStudentIds = students.map(s => s.id);
                    $('#editStudentSelect').val(selectedStudentIds).trigger('change');

                    new bootstrap.Modal(document.getElementById('editGroupModal')).show();
                }
            }
        });
    });

    // Edit Group Form Submit
    $('#editGroupForm').on('submit', async function(e) {
        e.preventDefault();
        const id = $('#editGroupId').val();
        const formData = $(this).serialize();

        try {
            const response = await fetch(`/sibling-groups/${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                Swal.fire('Success!', data.message, 'success');
                $('#editGroupModal').modal('hide');
                loadGroups();
            } else {
                $('#editFormErrors').removeClass('d-none').html(data.message);
            }
        } catch (error) {
            Swal.fire('Error!', 'Something went wrong', 'error');
        }
    });

    // Apply Discount Button
    $(document).on('click', '.apply-discount', function() {
        const id = $(this).data('id');
        $('#discountGroupId').val(id);
        new bootstrap.Modal(document.getElementById('applyDiscountModal')).show();
    });

    // Apply Discount Form Submit
    $('#applyDiscountForm').on('submit', async function(e) {
        e.preventDefault();
        const formData = $(this).serialize();

        try {
            const response = await fetch('{{ route("sibling.apply-discount") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                Swal.fire('Success!', data.message, 'success');
                $('#applyDiscountModal').modal('hide');
                loadGroups();
            } else {
                $('#discountFormErrors').removeClass('d-none').html(data.message);
            }
        } catch (error) {
            Swal.fire('Error!', 'Something went wrong', 'error');
        }
    });

    // Delete Group
    $(document).on('click', '.delete-group', function() {
        currentDeleteId = $(this).data('id');
        const familyName = $(this).data('name');
        $('#deleteModal').find('.modal-body p:first').text(`Are you sure you want to delete "${familyName}" family group?`);
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    });

    $('#confirmDeleteBtn').on('click', async function() {
        if (!currentDeleteId) return;

        try {
            const response = await fetch(`/sibling-groups/${currentDeleteId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN }
            });
            const data = await response.json();
            if (data.success) {
                Swal.fire('Deleted!', data.message, 'success');
                $('#deleteModal').modal('hide');
                loadGroups();
            } else {
                Swal.fire('Error!', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error!', 'Something went wrong', 'error');
        }
        currentDeleteId = null;
    });
});
</script>
@endsection
