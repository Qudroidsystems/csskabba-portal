@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">

<style>
    :root {
        --school-primary: #1e3a5f;
        --school-accent: #2563eb;
        --school-success: #16a34a;
        --school-warning: #d97706;
        --school-danger: #dc2626;
        --school-muted: #6b7280;
        --school-border: #e2e8f0;
        --school-bg: #f8fafc;
        --school-radius: 12px;
        --school-shadow: 0 2px 8px rgba(0,0,0,.08);
    }

    .school-hero {
        background: linear-gradient(135deg, var(--school-primary) 0%, #2563eb 60%, #4f46e5 100%);
        border-radius: var(--school-radius);
        padding: 28px 32px;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }
    .school-hero h1 { font-size: 22px; font-weight: 700; color: #fff; margin: 0 0 6px; }
    .school-hero p { font-size: 13px; color: rgba(255,255,255,.75); margin: 0; }

    .phone-input-group {
        background: #f8fafc;
        border: 1.5px solid var(--school-border);
        border-radius: 8px;
        padding: 12px;
    }
    .phone-input-item {
        display: flex;
        gap: 8px;
        margin-bottom: 10px;
        align-items: center;
    }
    .remove-phone-btn {
        background: none;
        border: none;
        color: var(--school-danger);
        cursor: pointer;
        padding: 8px;
        border-radius: 6px;
    }
    .remove-phone-btn:hover { background: #fee2e2; }

    .stat-card {
        background: #fff;
        border: 1px solid var(--school-border);
        border-radius: var(--school-radius);
        padding: 18px 20px;
        transition: transform .15s, box-shadow .15s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--school-shadow);
    }
    .stat-card .stat-value { font-size: 28px; font-weight: 700; color: var(--school-primary); }
    .stat-card .stat-label { font-size: 12px; color: var(--school-muted); margin-top: 4px; }

    .school-table th {
        background: var(--school-primary);
        color: #fff;
        padding: 12px 16px;
        font-weight: 600;
        font-size: 13px;
    }
    .school-table td {
        padding: 12px 16px;
        vertical-align: middle;
        border-bottom: 1px solid var(--school-border);
        font-size: 13px;
    }
    .school-table tr:hover td { background: #eff6ff; }

    .phone-badge {
        display: inline-block;
        background: #f3f4f6;
        padding: 4px 10px;
        border-radius: 20px;
        margin: 2px;
        font-size: 12px;
    }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <div class="school-hero">
                <h1><i class="ri-school-line me-2"></i>{{ $pagetitle ?? 'School Information Management' }}</h1>
                <p>Manage school information, logos, stamps, and operational dates</p>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-value">{{ $data->total() }}</div>
                        <div class="stat-label">Total Schools</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-value text-success">{{ $status_counts['Active'] ?? 0 }}</div>
                        <div class="stat-label">Active Schools</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-value text-secondary">{{ $status_counts['Inactive'] ?? 0 }}</div>
                        <div class="stat-label">Inactive Schools</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-value" id="openedCount">—</div>
                        <div class="stat-label">Total Times Opened</div>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                    <h5 class="mb-0 fw-semibold" style="color:var(--school-primary)">
                        <i class="ri-list-check me-2"></i>Schools
                        <span class="badge bg-primary ms-2">{{ $data->total() }}</span>
                    </h5>
                    <div class="ms-auto">
                        @can('Create schoolinformation')
                            <button type="button" class="btn btn-primary" onclick="openAddModal()">
                                <i class="ri-add-line me-1"></i> Add School
                            </button>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-xxl-3">
                            <input type="text" class="form-control" id="searchInput" placeholder="Search schools...">
                        </div>
                        <div class="col-xxl-3 col-sm-6">
                            <select class="form-control" id="idStatus">
                                <option value="all">All Status</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-xxl-3 col-sm-6">
                            <select class="form-control" id="idEmail">
                                <option value="all">All Emails</option>
                                @foreach ($data as $school)
                                    <option value="{{ $school->school_email }}">{{ $school->school_email }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xxl-1 col-sm-6">
                            <button type="button" class="btn btn-secondary w-100" onclick="filterData()">
                                <i class="ri-filter-line me-1"></i> Filter
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table school-table align-middle table-nowrap mb-0">
                            <thead class="table-active">
                                <tr>
                                    <th width="40"><input class="form-check-input" type="checkbox" id="checkAll"></th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone(s)</th>
                                    <th>Status</th>
                                    <th>Times Opened</th>
                                    <th>Date Opened</th>
                                    <th>Date Closed</th>
                                    <th>Next Term</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $school)
                                    <tr>
                                        <td><input class="form-check-input row-checkbox" type="checkbox" value="{{ $school->id }}"></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($school->getLogoUrlAttribute())
                                                    <img src="{{ $school->getLogoUrlAttribute() }}" alt="logo" class="rounded me-2" style="width: 32px; height: 32px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                        <i class="ri-building-line text-muted"></i>
                                                    </div>
                                                @endif
                                                <a href="{{ route('admin.school-info.show', $school->id) }}" class="text-reset fw-medium">{{ $school->school_name }}</a>
                                            </div>
                                        </td>
                                        <td>{{ $school->school_email }}</td>
                                        <td>
                                            @php
                                                $phones = is_array($school->school_phones) ? $school->school_phones : json_decode($school->school_phones ?? '[]', true);
                                            @endphp
                                            @if(!empty($phones))
                                                @foreach($phones as $phone)
                                                    <span class="phone-badge">{{ $phone }}</span>
                                                @endforeach
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $school->is_active ? 'success' : 'secondary' }}">
                                                {{ $school->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>{{ $school->no_of_times_school_opened }}</td>
                                        <td>{{ $school->date_school_opened?->format('Y-m-d') ?? '-' }}</td>
                                        <td>{{ $school->date_school_closed?->format('Y-m-d') ?? '-' }}</td>
                                        <td>{{ $school->date_next_term_begins?->format('Y-m-d') ?? '-' }}</td>
                                        <td>{{ $school->created_at->format('Y-m-d') }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                @can('View schoolinformation')
                                                    <a href="{{ route('admin.school-info.show', $school->id) }}" class="btn btn-sm btn-subtle-primary" title="View">
                                                        <i class="ph-eye"></i>
                                                    </a>
                                                @endcan
                                                @can('Update schoolinformation')
                                                    <button class="btn btn-sm btn-subtle-secondary edit-school-btn" data-id="{{ $school->id }}" title="Edit">
                                                        <i class="ph-pencil"></i>
                                                    </button>
                                                @endcan
                                                @can('Delete schoolinformation')
                                                    <button class="btn btn-sm btn-subtle-danger delete-school-btn" data-id="{{ $school->id }}" data-name="{{ $school->school_name }}" title="Delete">
                                                        <i class="ph-trash"></i>
                                                    </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-5">No schools found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-4 align-items-center">
                        <div class="col-sm">
                            Showing <span class="fw-semibold">{{ $data->count() }}</span> of <span class="fw-semibold">{{ $data->total() }}</span> Results
                        </div>
                        <div class="col-sm-auto">{{ $data->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ADD/EDIT MODAL --}}
<div class="modal fade" id="schoolModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add School</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="schoolForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="schoolId" name="id">

                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="formErrors"></div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">School Name <span class="text-danger">*</span></label>
                            <input type="text" id="school_name" name="school_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" id="school_email" name="school_email" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address <span class="text-danger">*</span></label>
                        <textarea id="school_address" name="school_address" class="form-control" rows="2" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone Numbers <span class="text-danger">*</span></label>
                        <div class="phone-input-group">
                            <div id="phoneInputsList"></div>
                            <button type="button" class="btn btn-sm btn-outline-primary w-100 mt-2" onclick="addPhoneInput()">
                                <i class="ri-add-line"></i> Add Another Phone
                            </button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Website</label>
                            <input type="url" id="school_website" name="school_website" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Motto</label>
                            <input type="text" id="school_motto" name="school_motto" class="form-control">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Times Opened <span class="text-danger">*</span></label>
                            <input type="number" id="no_of_times_school_opened" name="no_of_times_school_opened" class="form-control" min="0" value="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date School Opened</label>
                            <input type="date" id="date_school_opened" name="date_school_opened" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date School Closed</label>
                            <input type="date" id="date_school_closed" name="date_school_closed" class="form-control">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Next Term Begins</label>
                            <input type="date" id="date_next_term_begins" name="date_next_term_begins" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3 pt-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active">
                                <label class="form-check-label" for="is_active">Set as Active School</label>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="fw-semibold mb-3">School Assets</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label>School Logo</label>
                            <input type="file" id="school_logo" name="school_logo" class="form-control" accept="image/*">
                            <div id="school-logo-preview" class="mt-2 text-center"></div>
                        </div>
                        <div class="col-md-4">
                            <label>App Logo</label>
                            <input type="file" id="app_logo" name="app_logo" class="form-control" accept="image/*">
                            <div id="app-logo-preview" class="mt-2 text-center"></div>
                        </div>
                        <div class="col-md-4">
                            <label>School Stamp</label>
                            <input type="file" id="school_stamp" name="school_stamp" class="form-control" accept="image/*">
                            <div id="school-stamp-preview" class="mt-2 text-center"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save School</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- DELETE MODAL --}}
<div class="modal fade" id="deleteRecordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-5">
                <i class="bi bi-trash display-4 text-danger"></i>
                <h3 class="mt-4">Are you sure?</h3>
                <p class="text-muted">You are about to delete <strong id="deleteItemName"></strong></p>
                <div class="mt-4">
                    <button class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger" id="confirmDeleteBtn">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    let currentDeleteId = null;
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    document.addEventListener('DOMContentLoaded', function() {
        initStatusChart();
        initEventListeners();
        updateOpenedCount();
        initPhoneInputs();
    });

    function initStatusChart() {
        const ctx = document.getElementById("schoolsByStatusChart");
        if (!ctx) return;
        const statusData = JSON.parse(ctx.getAttribute('data-status') || '{"Active":0,"Inactive":0}');
        new Chart(ctx.getContext("2d"), {
            type: "bar",
            data: {
                labels: Object.keys(statusData),
                datasets: [{
                    label: "Number of Schools",
                    data: Object.values(statusData),
                    backgroundColor: ["#16a34a", "#6b7280"],
                    borderRadius: 6
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });
    }

    function updateOpenedCount() {
        let total = 0;
        document.querySelectorAll('table tbody tr td:nth-child(6)').forEach(cell => {
            total += parseInt(cell.textContent) || 0;
        });
        document.getElementById('openedCount').textContent = total;
    }

    function initPhoneInputs() {
        addPhoneInput('');
    }

    function addPhoneInput(value = '') {
        const container = document.getElementById('phoneInputsList');
        const div = document.createElement('div');
        div.className = 'phone-input-item';
        div.innerHTML = `
            <input type="text" class="form-control phone-input" name="school_phones[]" value="${value}" placeholder="e.g. +234 801 234 5678" required>
            <button type="button" class="remove-phone-btn" onclick="removePhoneInput(this)"><i class="ri-delete-bin-line"></i></button>
        `;
        container.appendChild(div);
    }

    function removePhoneInput(btn) {
        if (document.querySelectorAll('.phone-input-item').length > 1) {
            btn.parentElement.remove();
        }
    }

    function getPhonesArray() {
        return Array.from(document.querySelectorAll('.phone-input'))
                    .map(input => input.value.trim())
                    .filter(phone => phone !== '');
    }

    function setPhonesArray(phones = []) {
        document.getElementById('phoneInputsList').innerHTML = '';
        if (phones.length === 0) {
            addPhoneInput('');
        } else {
            phones.forEach(phone => addPhoneInput(phone));
        }
    }

    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(previewId).innerHTML = `<img src="${e.target.result}" class="img-thumbnail" style="max-width:100px;max-height:100px;">`;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // ==================== FORM SUBMIT ====================
    document.getElementById('schoolForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const schoolId = document.getElementById('schoolId').value;

        // Refresh phones
        formData.delete('school_phones[]');
        getPhonesArray().forEach(phone => formData.append('school_phones[]', phone));

        if (schoolId) formData.append('_method', 'PUT');

        const url = schoolId ? `/school-info/${schoolId}` : '/school-info';
        const btn = document.getElementById('saveBtn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Saving...';

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();

            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Success!', text: data.message, timer: 2000 });
                setTimeout(() => location.reload(), 1500);
            } else {
                let html = '<ul class="mb-0">';
                if (data.errors) {
                    Object.values(data.errors).flat().forEach(err => html += `<li>${err}</li>`);
                } else {
                    html += `<li>${data.message || 'Error occurred'}</li>`;
                }
                html += '</ul>';
                document.getElementById('formErrors').innerHTML = html;
                document.getElementById('formErrors').classList.remove('d-none');
            }
        } catch (error) {
            Swal.fire('Error', 'Network error. Please try again.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });

    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Add School';
        document.getElementById('schoolForm').reset();
        document.getElementById('schoolId').value = '';
        document.getElementById('formErrors').classList.add('d-none');
        setPhonesArray([]);
        ['school-logo-preview', 'app-logo-preview', 'school-stamp-preview'].forEach(id => {
            document.getElementById(id).innerHTML = '';
        });
        new bootstrap.Modal(document.getElementById('schoolModal')).show();
    }

    function openEditModal(id) {
        document.getElementById('modalTitle').textContent = 'Edit School';
        document.getElementById('schoolId').value = id;
        document.getElementById('formErrors').classList.add('d-none');

        const btn = document.getElementById('saveBtn');
        const orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Loading...';

        fetch(`/school-info/${id}/edit-json`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const s = data.school;
                    document.getElementById('school_name').value = s.school_name || '';
                    document.getElementById('school_email').value = s.school_email || '';
                    document.getElementById('school_address').value = s.school_address || '';
                    document.getElementById('school_website').value = s.school_website || '';
                    document.getElementById('school_motto').value = s.school_motto || '';
                    document.getElementById('no_of_times_school_opened').value = s.no_of_times_school_opened || 0;
                    document.getElementById('date_school_opened').value = s.date_school_opened || '';
                    document.getElementById('date_school_closed').value = s.date_school_closed || '';
                    document.getElementById('date_next_term_begins').value = s.date_next_term_begins || '';
                    document.getElementById('is_active').checked = s.is_active;

                    setPhonesArray(s.school_phones || []);

                    if (s.logo_url) document.getElementById('school-logo-preview').innerHTML = `<img src="${s.logo_url}" class="img-thumbnail" style="max-width:100px">`;
                    if (s.app_logo_url) document.getElementById('app-logo-preview').innerHTML = `<img src="${s.app_logo_url}" class="img-thumbnail" style="max-width:100px">`;
                    if (s.stamp_url) document.getElementById('school-stamp-preview').innerHTML = `<img src="${s.stamp_url}" class="img-thumbnail" style="max-width:100px">`;

                    new bootstrap.Modal(document.getElementById('schoolModal')).show();
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = orig;
            });
    }

    function openDeleteModal(id, name) {
        currentDeleteId = id;
        document.getElementById('deleteItemName').textContent = name;
        new bootstrap.Modal(document.getElementById('deleteRecordModal')).show();
    }

    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (!currentDeleteId) return;
        // Delete logic here (same as before)
        fetch(`/school-info/${currentDeleteId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Deleted!', data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        });
    });

    function initEventListeners() {
        document.querySelectorAll('.edit-school-btn').forEach(btn => {
            btn.addEventListener('click', () => openEditModal(btn.dataset.id));
        });

        document.querySelectorAll('.delete-school-btn').forEach(btn => {
            btn.addEventListener('click', () => openDeleteModal(btn.dataset.id, btn.dataset.name));
        });
    }

    function filterData() {
        // Your filter logic
        console.log('Filter applied');
    }

    // Make functions globally available
    window.openAddModal = openAddModal;
    window.openEditModal = openEditModal;
    window.addPhoneInput = addPhoneInput;
    window.removePhoneInput = removePhoneInput;
    window.filterData = filterData;
</script>
@endsection
