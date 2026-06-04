@extends('layouts.master')

@section('content')
<?php use Spatie\Permission\Models\Role; ?>

{{-- ═══════════════════════════════════════════════════════════
     STYLES
═══════════════════════════════════════════════════════════ --}}
<style>
/* Basic modal styles */
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
    text-decoration: none;
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

.alert-info {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #2563eb;
}

.table-container {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.table-container .header {
    padding: 14px 20px;
    border-bottom: 1px solid #e2e8f0;
    font-weight: bold;
}

.table-custom {
    width: 100%;
    margin-bottom: 0;
}

.table-custom thead {
    background: #1e3a5f;
    color: #fff;
}

.table-custom th,
.table-custom td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}

.table-custom tbody tr:hover {
    background: #f8fafc;
}

.btn-sm {
    padding: 4px 8px;
    font-size: 12px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    margin: 0 2px;
}

.btn-primary {
    background: #2563eb;
    color: #fff;
}

.btn-danger {
    background: #dc2626;
    color: #fff;
}

.btn-secondary {
    background: #6b7280;
    color: #fff;
}

.btn-success {
    background: #16a34a;
    color: #fff;
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
            <div class="col-auto d-flex gap-2">
                @can('Create user')
                <button type="button" class="u-btn primary" onclick="openAddUserModal()">
                    <i class="bi bi-plus-circle"></i> Add User
                </button>
                @endcan
            </div>
        </div>
    </div>

    {{-- Debug Info --}}
    <div class="alert alert-info mb-3" id="debugAlert">
        <strong>Debug Mode:</strong>
        <button type="button" class="btn btn-sm btn-primary" onclick="testModal()">Test Modal (Direct)</button>
        <span id="debugStatus" class="ms-2">Checking Bootstrap...</span>
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

    {{-- Users Table --}}
    <div class="table-container">
        <div class="header">Users List</div>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    @forelse ($data as $user)
                    <tr data-id="{{ $user->id }}">
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->getRoleNames()->implode(', ') ?: 'No Role' }}</td>
                        <td>
                            <button class="btn-sm btn-primary" onclick="editUser({{ $user->id }})">Edit</button>
                            <button class="btn-sm btn-danger" onclick="deleteUser({{ $user->id }})">Delete</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align:center; padding:40px;">No users found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         ADD USER MODAL
    ═══════════════════════════════════════════════════════════ --}}
    <div id="addUserModal" class="modal fade" tabindex="-1" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%); color:#fff;">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close btn-close-white" onclick="closeModal('addUserModal')"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm" onsubmit="return false;">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Name *</label>
                            <input type="text" id="userName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" id="userEmail" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role *</label>
                            <select id="userRole" class="form-control" required>
                                <option value="">Select Role</option>
                                @foreach (Role::all() as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password *</label>
                            <input type="password" id="userPassword" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password *</label>
                            <input type="password" id="userPasswordConfirm" class="form-control" required>
                        </div>
                        <div id="addUserError" class="alert alert-danger d-none"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addUserModal')">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitAddUser()">Create User</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         EDIT USER MODAL
    ═══════════════════════════════════════════════════════════ --}}
    <div id="editUserModal" class="modal fade" tabindex="-1" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #065f46, #16a34a); color:#fff;">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close btn-close-white" onclick="closeModal('editUserModal')"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm" onsubmit="return false;">
                        @csrf
                        <input type="hidden" id="editUserId">
                        <div class="mb-3">
                            <label class="form-label">Name *</label>
                            <input type="text" id="editUserName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" id="editUserEmail" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role *</label>
                            <select id="editUserRole" class="form-control" required>
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
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="submitEditUser()">Update User</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         DELETE CONFIRM MODAL
    ═══════════════════════════════════════════════════════════ --}}
    <div id="deleteUserModal" class="modal fade" tabindex="-1" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" onclick="closeModal('deleteUserModal')"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this user?</p>
                    <input type="hidden" id="deleteUserId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('deleteUserModal')">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete</button>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     BOOTSTRAP CSS & JS - Direct CDN
══════════════════════════════════════════════════════════════ --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
// Global modal variables
let addModal, editModal, deleteModal;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - initializing modals');

    // Check if Bootstrap is available
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap is NOT loaded!');
        document.getElementById('debugStatus').innerHTML = '<span class="text-danger">❌ Bootstrap not loaded!</span>';
        document.getElementById('debugStatus').style.color = '#dc2626';
        return;
    }

    console.log('Bootstrap version:', bootstrap.version);
    document.getElementById('debugStatus').innerHTML = '<span class="text-success">✅ Bootstrap ' + bootstrap.version + ' loaded!</span>';

    // Initialize modals
    const addModalEl = document.getElementById('addUserModal');
    const editModalEl = document.getElementById('editUserModal');
    const deleteModalEl = document.getElementById('deleteUserModal');

    if (addModalEl) {
        addModal = new bootstrap.Modal(addModalEl);
        console.log('Add user modal initialized');
    }

    if (editModalEl) {
        editModal = new bootstrap.Modal(editModalEl);
        console.log('Edit user modal initialized');
    }

    if (deleteModalEl) {
        deleteModal = new bootstrap.Modal(deleteModalEl);
        console.log('Delete user modal initialized');
    }
});

// Direct modal test function
function testModal() {
    console.log('testModal() called');

    if (addModal) {
        console.log('Showing add modal');
        addModal.show();
    } else {
        console.error('addModal is null/undefined');
        alert('Modal not initialized. Please refresh the page.');
    }
}

// Close modal function
function closeModal(modalId) {
    const modalElement = document.getElementById(modalId);
    if (modalElement) {
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
            modal.hide();
        }
    }
}

// Open Add User Modal
function openAddUserModal() {
    console.log('openAddUserModal() called');

    if (!addModal) {
        console.error('Add modal not initialized');
        // Try to reinitialize
        const modalEl = document.getElementById('addUserModal');
        if (modalEl) {
            addModal = new bootstrap.Modal(modalEl);
            console.log('Reinitialized add modal');
        } else {
            alert('Modal element not found');
            return;
        }
    }

    // Reset form
    document.getElementById('addUserForm').reset();
    document.getElementById('addUserError').classList.add('d-none');

    addModal.show();
}

// Edit user function
async function editUser(id) {
    console.log('editUser() called for id:', id);

    if (!editModal) {
        const modalEl = document.getElementById('editUserModal');
        if (modalEl) {
            editModal = new bootstrap.Modal(modalEl);
        } else {
            alert('Edit modal not found');
            return;
        }
    }

    try {
        // Fetch user data
        const response = await axios.get(`/users/${id}/edit`);
        const user = response.data;

        document.getElementById('editUserId').value = user.id;
        document.getElementById('editUserName').value = user.name;
        document.getElementById('editUserEmail').value = user.email;

        // Set role
        if (user.roles && user.roles.length > 0) {
            document.getElementById('editUserRole').value = user.roles[0];
        }

        document.getElementById('editUserPassword').value = '';
        document.getElementById('editUserPasswordConfirm').value = '';
        document.getElementById('editUserError').classList.add('d-none');

        editModal.show();
    } catch (error) {
        console.error('Error fetching user:', error);
        alert('Error loading user data');
    }
}

// Delete user function
function deleteUser(id) {
    console.log('deleteUser() called for id:', id);

    if (!deleteModal) {
        const modalEl = document.getElementById('deleteUserModal');
        if (modalEl) {
            deleteModal = new bootstrap.Modal(modalEl);
        } else {
            alert('Delete modal not found');
            return;
        }
    }

    document.getElementById('deleteUserId').value = id;
    deleteModal.show();
}

// Submit Add User
async function submitAddUser() {
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

    const submitBtn = event.target;
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'Creating...';

    try {
        const response = await axios.post('/users', {
            name: name,
            email: email,
            roles: [role],
            password: password,
            password_confirmation: passwordConfirm,
            _token: document.querySelector('meta[name="csrf-token"]').content
        });

        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'User created successfully',
            timer: 2000,
            showConfirmButton: false
        });

        addModal.hide();

        setTimeout(() => {
            location.reload();
        }, 2000);

    } catch (error) {
        let message = 'Error creating user';
        if (error.response && error.response.data && error.response.data.message) {
            message = error.response.data.message;
        }
        if (error.response && error.response.data && error.response.data.errors) {
            message = Object.values(error.response.data.errors).flat().join(', ');
        }
        showError(errorDiv, message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

// Submit Edit User
async function submitEditUser() {
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

    const submitBtn = event.target;
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'Updating...';

    try {
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

        await axios.post(`/users/${id}`, data);

        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'User updated successfully',
            timer: 2000,
            showConfirmButton: false
        });

        editModal.hide();

        setTimeout(() => {
            location.reload();
        }, 2000);

    } catch (error) {
        let message = 'Error updating user';
        if (error.response && error.response.data && error.response.data.message) {
            message = error.response.data.message;
        }
        showError(errorDiv, message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

// Confirm Delete
async function confirmDelete() {
    const id = document.getElementById('deleteUserId').value;
    const deleteBtn = event.target;
    const originalText = deleteBtn.innerHTML;
    deleteBtn.disabled = true;
    deleteBtn.innerHTML = 'Deleting...';

    try {
        await axios.delete(`/users/${id}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        Swal.fire({
            icon: 'success',
            title: 'Deleted!',
            text: 'User deleted successfully',
            timer: 2000,
            showConfirmButton: false
        });

        deleteModal.hide();

        setTimeout(() => {
            location.reload();
        }, 2000);

    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Error deleting user'
        });
        deleteBtn.disabled = false;
        deleteBtn.innerHTML = originalText;
    }
}

// Helper function
function showError(element, message) {
    element.innerHTML = message;
    element.classList.remove('d-none');
    setTimeout(() => {
        element.classList.add('d-none');
    }, 5000);
}
</script>

<style>
/* Additional modal styles to ensure visibility */
.modal {
    z-index: 1060;
}
.modal-backdrop {
    z-index: 1050;
}
.form-control, .form-select {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px 12px;
    width: 100%;
}
.form-control:focus, .form-select:focus {
    border-color: #2563eb;
    outline: none;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
.form-label {
    font-weight: 600;
    margin-bottom: 5px;
    display: block;
}
.btn {
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
}
.btn-primary { background: #2563eb; color: white; }
.btn-danger { background: #dc2626; color: white; }
.btn-secondary { background: #6b7280; color: white; }
.btn-success { background: #16a34a; color: white; }
.btn-sm { padding: 4px 8px; font-size: 12px; }
</style>

@endsection
