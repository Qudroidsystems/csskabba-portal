@extends('layouts.master')

@section('content')
<?php use Spatie\Permission\Models\Role; ?>

{{-- ═══════════════════════════════════════════════════════════
     STYLES (Simplified for modal testing)
═══════════════════════════════════════════════════════════ --}}
<style>
/* Basic modal styles that work with Bootstrap */
.modal.u-modal .modal-content {
    border: none;
    border-radius: 18px;
    overflow: hidden;
}

.u-modal-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #4f46e5 100%);
    padding: 22px 28px;
    position: relative;
}

.u-modal-hero h5 {
    color: #fff;
    font-weight: 700;
    font-size: 16px;
    margin: 0;
}

.u-modal-hero p {
    color: rgba(255,255,255,.72);
    font-size: 12px;
    margin: 4px 0 0;
}

.u-modal-hero .btn-close {
    position: absolute;
    top: 18px;
    right: 20px;
    filter: brightness(0) invert(1);
}

.u-modal-body {
    padding: 22px 24px;
    background: #f8fafc;
}

.u-modal-footer {
    padding: 14px 24px;
    background: #ffffff;
    border-top: 1px solid #e2e8f0;
}

.u-form-label {
    font-size: 11.5px;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    display: block;
    margin-bottom: 5px;
}

.u-form-input {
    width: 100%;
    border: 1.5px solid #e2e8f0;
    border-radius: 9px;
    padding: 10px 14px;
    font-size: 13px;
}

.u-form-input:focus {
    border-color: #2563eb;
    outline: none;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

.u-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 18px;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 600;
    border: none;
    cursor: pointer;
}

.u-btn.primary {
    background: linear-gradient(135deg, #2563eb, #4f46e5);
    color: #fff;
}

.u-btn.success {
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: #fff;
}

.u-btn.warning {
    background: linear-gradient(135deg, #d97706, #b45309);
    color: #fff;
}

.u-btn.danger {
    background: #dc2626;
    color: #fff;
}

.u-btn.ghost {
    background: #fff;
    color: #1e3a5f;
    border: 1.5px solid #e2e8f0;
}

/* Hero section */
.u-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 55%, #4f46e5 100%);
    border-radius: 12px;
    padding: 28px 32px;
    margin-bottom: 24px;
}

.u-hero h1 {
    font-size: 22px;
    font-weight: 800;
    color: #fff;
    margin: 0 0 6px;
}

.u-hero p {
    font-size: 13px;
    color: rgba(255,255,255,.75);
    margin: 0;
}

/* Alert styles */
.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 16px;
}

.alert-danger {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #dc2626;
}

.alert-success {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #16a34a;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- Hero --}}
    <div class="u-hero">
        <div class="row align-items-center">
            <div class="col">
                <h1><i class="ri-team-line me-2"></i>User Management</h1>
                <p>Manage system users, roles, and student portal access from one place.</p>
            </div>
            <div class="col-auto d-none d-md-flex gap-2">
                @can('Create user')
                <button type="button" class="u-btn primary" onclick="openAddUserModal()">
                    <i class="bi bi-plus-circle"></i> Add User
                </button>
                <button type="button" class="u-btn success" onclick="openAddStudentModal()">
                    <i class="bi bi-person-plus"></i> Add Student
                </button>
                @endcan
            </div>
        </div>
    </div>

    {{-- Test Modal Button --}}
    <div class="alert alert-info mb-3">
        <strong>Debug Mode:</strong>
        <button type="button" class="btn btn-sm btn-primary" onclick="testBootstrapModal()">Test Bootstrap Modal</button>
        <span id="debugStatus" class="ms-2"></span>
    </div>

    {{-- Alerts --}}
    @if ($errors->any())
    <div class="alert alert-danger">
        <strong>Error:</strong> {{ $errors->all()[0] }}
    </div>
    @endif
    @if (session('success'))
    <div class="alert alert-success">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    </div>
    @endif

    {{-- Simple Table --}}
    <div style="background:#fff; border-radius:12px; border:1px solid #e2e8f0; overflow:hidden;">
        <div style="padding:14px 20px; border-bottom:1px solid #e2e8f0;">
            <div class="fw-bold">Users List</div>
        </div>
        <div class="table-responsive">
            <table class="table mb-0" style="margin-bottom:0">
                <thead style="background:#1e3a5f">
                    <tr>
                        <th style="color:#fff; padding:12px 16px;">Name</th>
                        <th style="color:#fff; padding:12px 16px;">Email</th>
                        <th style="color:#fff; padding:12px 16px;">Role</th>
                        <th style="color:#fff; padding:12px 16px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->getRoleNames()->implode(', ') ?: 'No Role' }}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editUser({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}')">Edit</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteUser({{ $user->id }})">Delete</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4">No users found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         ADD USER MODAL
    ═══════════════════════════════════════════════════════════ --}}
    <div id="addUserModal" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%); color:#fff; border:none;">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" id="userName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" id="userEmail" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select id="userRole" class="form-select" required>
                                <option value="">Select Role</option>
                                @foreach (Role::all() as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" id="userPassword" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" id="userPasswordConfirm" class="form-control" required>
                        </div>
                        <div id="addUserError" class="alert alert-danger d-none"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitAddUser()">Create User</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         EDIT USER MODAL
    ═══════════════════════════════════════════════════════════ --}}
    <div id="editUserModal" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #065f46, #16a34a); color:#fff; border:none;">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="editUserId">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" id="editUserName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" id="editUserEmail" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select id="editUserRole" class="form-select" required>
                                <option value="">Select Role</option>
                                @foreach (Role::all() as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password (optional)</label>
                            <input type="password" id="editUserPassword" class="form-control" placeholder="Leave blank to keep current">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" id="editUserPasswordConfirm" class="form-control">
                        </div>
                        <div id="editUserError" class="alert alert-danger d-none"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="submitEditUser()">Update User</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         DELETE CONFIRM MODAL
    ═══════════════════════════════════════════════════════════ --}}
    <div id="deleteUserModal" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this user?</p>
                    <input type="hidden" id="deleteUserId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete</button>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     SCRIPTS
══════════════════════════════════════════════════════════════ --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
// Global variables
let addUserModal, editUserModal, deleteUserModal;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - initializing modals');

    // Check if Bootstrap is available
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap is NOT loaded!');
        document.getElementById('debugStatus').innerHTML = '<span class="text-danger">❌ Bootstrap not loaded!</span>';
        return;
    }

    console.log('Bootstrap is loaded:', typeof bootstrap);
    document.getElementById('debugStatus').innerHTML = '<span class="text-success">✅ Bootstrap loaded</span>';

    // Initialize modals
    const addModalEl = document.getElementById('addUserModal');
    const editModalEl = document.getElementById('editUserModal');
    const deleteModalEl = document.getElementById('deleteUserModal');

    if (addModalEl) {
        addUserModal = new bootstrap.Modal(addModalEl);
        console.log('Add user modal initialized');
    }

    if (editModalEl) {
        editUserModal = new bootstrap.Modal(editModalEl);
        console.log('Edit user modal initialized');
    }

    if (deleteModalEl) {
        deleteUserModal = new bootstrap.Modal(deleteModalEl);
        console.log('Delete user modal initialized');
    }
});

// Test function
function testBootstrapModal() {
    console.log('Testing modal...');
    if (addUserModal) {
        addUserModal.show();
        console.log('Modal shown');
    } else {
        console.error('Modal not initialized');
        alert('Modal not initialized. Check console for errors.');
    }
}

// Open Add User Modal
function openAddUserModal() {
    if (addUserModal) {
        document.getElementById('addUserForm').reset();
        document.getElementById('addUserError').classList.add('d-none');
        addUserModal.show();
    } else {
        console.error('Add user modal not initialized');
        alert('Modal not ready. Please refresh the page.');
    }
}

// Open Edit User Modal
function editUser(id, name, email) {
    if (editUserModal) {
        document.getElementById('editUserId').value = id;
        document.getElementById('editUserName').value = name;
        document.getElementById('editUserEmail').value = email;
        document.getElementById('editUserPassword').value = '';
        document.getElementById('editUserPasswordConfirm').value = '';
        document.getElementById('editUserError').classList.add('d-none');

        // Fetch current roles
        fetch(`/users/${id}/roles`)
            .then(res => res.json())
            .then(data => {
                if (data.roles && data.roles.length > 0) {
                    document.getElementById('editUserRole').value = data.roles[0];
                }
            })
            .catch(err => console.error('Error fetching roles:', err));

        editUserModal.show();
    }
}

// Open Delete User Modal
function deleteUser(id) {
    if (deleteUserModal) {
        document.getElementById('deleteUserId').value = id;
        deleteUserModal.show();
    }
}

// Submit Add User
function submitAddUser() {
    const name = document.getElementById('userName').value.trim();
    const email = document.getElementById('userEmail').value.trim();
    const role = document.getElementById('userRole').value;
    const password = document.getElementById('userPassword').value;
    const passwordConfirm = document.getElementById('userPasswordConfirm').value;
    const errorDiv = document.getElementById('addUserError');

    if (!name) {
        showError(errorDiv, 'Please enter a name');
        return;
    }
    if (!email) {
        showError(errorDiv, 'Please enter an email');
        return;
    }
    if (!role) {
        showError(errorDiv, 'Please select a role');
        return;
    }
    if (!password) {
        showError(errorDiv, 'Please enter a password');
        return;
    }
    if (password !== passwordConfirm) {
        showError(errorDiv, 'Passwords do not match');
        return;
    }

    errorDiv.classList.add('d-none');

    axios.post('/users', {
        name: name,
        email: email,
        roles: [role],
        password: password,
        password_confirmation: passwordConfirm,
        _token: document.querySelector('meta[name="csrf-token"]').content
    })
    .then(response => {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'User created successfully',
            timer: 2000,
            showConfirmButton: false
        });
        addUserModal.hide();
        setTimeout(() => location.reload(), 2000);
    })
    .catch(error => {
        let message = 'Error creating user';
        if (error.response && error.response.data && error.response.data.message) {
            message = error.response.data.message;
        }
        if (error.response && error.response.data && error.response.data.errors) {
            message = Object.values(error.response.data.errors).flat().join(', ');
        }
        showError(errorDiv, message);
    });
}

// Submit Edit User
function submitEditUser() {
    const id = document.getElementById('editUserId').value;
    const name = document.getElementById('editUserName').value.trim();
    const email = document.getElementById('editUserEmail').value.trim();
    const role = document.getElementById('editUserRole').value;
    const password = document.getElementById('editUserPassword').value;
    const passwordConfirm = document.getElementById('editUserPasswordConfirm').value;
    const errorDiv = document.getElementById('editUserError');

    if (!name) {
        showError(errorDiv, 'Please enter a name');
        return;
    }
    if (!email) {
        showError(errorDiv, 'Please enter an email');
        return;
    }
    if (!role) {
        showError(errorDiv, 'Please select a role');
        return;
    }
    if (password && password !== passwordConfirm) {
        showError(errorDiv, 'Passwords do not match');
        return;
    }

    errorDiv.classList.add('d-none');

    const data = {
        name: name,
        email: email,
        roles: [role],
        _token: document.querySelector('meta[name="csrf-token"]').content,
        _method: 'PUT'
    };

    if (password) {
        data.password = password;
        data.password_confirmation = passwordConfirm;
    }

    axios.post(`/users/${id}`, data)
    .then(response => {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'User updated successfully',
            timer: 2000,
            showConfirmButton: false
        });
        editUserModal.hide();
        setTimeout(() => location.reload(), 2000);
    })
    .catch(error => {
        let message = 'Error updating user';
        if (error.response && error.response.data && error.response.data.message) {
            message = error.response.data.message;
        }
        showError(errorDiv, message);
    });
}

// Confirm Delete
function confirmDelete() {
    const id = document.getElementById('deleteUserId').value;

    axios.delete(`/users/${id}`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        Swal.fire({
            icon: 'success',
            title: 'Deleted!',
            text: 'User deleted successfully',
            timer: 2000,
            showConfirmButton: false
        });
        deleteUserModal.hide();
        setTimeout(() => location.reload(), 2000);
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Error deleting user'
        });
    });
}

// Helper function
function showError(element, message) {
    element.innerHTML = message;
    element.classList.remove('d-none');
    setTimeout(() => {
        element.classList.add('d-none');
    }, 5000);
}

// Open Add Student Modal (placeholder)
function openAddStudentModal() {
    alert('Add student functionality - implement as needed');
}
</script>

@endsection
