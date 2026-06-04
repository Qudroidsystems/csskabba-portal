@extends('layouts.master')

@section('content')
<?php use Spatie\Permission\Models\Role; ?>

<style>
/* Your existing styles here - keeping them compact */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap');

:root {
    --u-primary: #1e3a5f;
    --u-accent: #2563eb;
    --u-indigo: #4f46e5;
    --u-success: #16a34a;
    --u-warning: #d97706;
    --u-danger: #dc2626;
    --u-muted: #6b7280;
    --u-border: #e2e8f0;
    --u-bg: #f8fafc;
    --u-surface: #ffffff;
    --u-radius: 12px;
    --u-shadow: 0 2px 8px rgba(0,0,0,.07);
    --u-shadow-lg: 0 8px 32px rgba(0,0,0,.12);
}

/* Keyframes */
@keyframes fadeInUp { from { opacity:0; transform:translateY(18px); } to { opacity:1; transform:translateY(0); } }
@keyframes fadeInDown { from { opacity:0; transform:translateY(-14px);} to { opacity:1; transform:translateY(0); } }
@keyframes scaleIn { from { opacity:0; transform:scale(.92); } to { opacity:1; transform:scale(1); } }
@keyframes rowIn { from{opacity:0;transform:translateX(-8px);}to{opacity:1;transform:translateX(0);} }
@keyframes badgePop { 0%{transform:scale(0.5);}70%{transform:scale(1.15);}100%{transform:scale(1);} }

/* Hero */
.u-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 55%, #4f46e5 100%);
    border-radius: var(--u-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
    animation: fadeInDown .5s ease both;
}
.u-hero h1 { font-size:22px; font-weight:800; color:#fff; margin:0 0 6px; }
.u-hero p { font-size:13px; color:rgba(255,255,255,.75); margin:0; }

/* Stat cards */
.stat-card {
    background: var(--u-surface);
    border: 1px solid var(--u-border);
    border-radius: var(--u-radius);
    padding: 18px 20px;
    transition: transform .2s, box-shadow .2s;
    animation: fadeInUp .5s ease both;
}
.stat-card:hover { transform:translateY(-3px); box-shadow:var(--u-shadow-lg); }
.stat-card .stat-value { font-size:28px; font-weight:800; color:var(--u-primary); }
.stat-card .stat-label { font-size:12px; color:var(--u-muted); margin-top:5px; }
.stat-card .stat-icon { font-size:34px; opacity:.1; float:right; margin-top:-6px; }

/* Filter area */
.u-filter-card {
    background: var(--u-surface);
    border: 1px solid var(--u-border);
    border-top: 3px solid var(--u-accent);
    border-radius: var(--u-radius);
    padding: 16px 20px;
    animation: fadeInUp .5s .1s ease both;
}
.u-input {
    border: 1.5px solid var(--u-border);
    border-radius: 9px;
    padding: 9px 14px;
    font-size: 13px;
    width: 100%;
    background: #fff;
}
.u-input:focus { border-color:var(--u-accent); outline:none; box-shadow:0 0 0 3px rgba(37,99,235,.1); }
.u-input-icon-wrap { position:relative; }
.u-input-icon { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:#94a3b8; }
.u-input-icon-wrap .u-input { padding-left: 34px; }

/* Table */
.u-table-card {
    background: var(--u-surface);
    border: 1px solid var(--u-border);
    border-radius: var(--u-radius);
    overflow: hidden;
    box-shadow: var(--u-shadow);
    animation: fadeInUp .5s .15s ease both;
}
.u-table-card .card-header {
    background: var(--u-surface);
    border-bottom: 1px solid var(--u-border);
    padding: 14px 20px;
}
.u-table thead th {
    background: var(--u-primary);
    color: #fff;
    padding: 12px 16px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    border: none;
}
.u-table tbody td {
    padding: 11px 16px;
    vertical-align: middle;
    border-bottom: 1px solid var(--u-border);
    font-size: 13px;
}
.u-table tbody tr { animation: rowIn .3s ease both; }
.u-table tbody tr:hover td { background: #f0f9ff; }

/* Avatar */
.u-avatar {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: 700; color: #fff;
    border: 2px solid var(--u-border);
}

/* Role badges */
.u-role-pill {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; border-radius: 20px;
    font-size: 11px; font-weight: 600;
    animation: badgePop .3s ease;
}
.u-role-pill.student { background:#dbeafe; color:#1e40af; }
.u-role-pill.admin { background:#fce7f3; color:#9d174d; }
.u-role-pill.teacher { background:#d1fae5; color:#065f46; }
.u-role-pill.default { background:#f1f5f9; color:#475569; }

/* Action buttons */
.u-action-btn {
    width: 30px; height: 30px;
    border-radius: 7px;
    display: inline-flex; align-items: center; justify-content: center;
    border: none; cursor: pointer;
    transition: transform .15s;
}
.u-action-btn:hover { transform: translateY(-1px); }
.u-action-btn.view { background:#eff6ff; color:#2563eb; }
.u-action-btn.edit { background:#f0fdf4; color:#16a34a; }
.u-action-btn.del { background:#fef2f2; color:#dc2626; }

/* Buttons */
.u-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 9px 18px; border-radius: 9px;
    font-size: 13px; font-weight: 600;
    border: none; cursor: pointer;
    transition: transform .15s;
}
.u-btn:hover { transform: translateY(-1px); }
.u-btn.primary { background:linear-gradient(135deg,var(--u-accent),var(--u-indigo)); color:#fff; }
.u-btn.success { background:linear-gradient(135deg,#16a34a,#15803d); color:#fff; }
.u-btn.warning { background:linear-gradient(135deg,#d97706,#b45309); color:#fff; }
.u-btn.danger { background:var(--u-danger); color:#fff; }
.u-btn.ghost { background:#fff; color:var(--u-primary); border:1.5px solid var(--u-border); }

/* Modal styles */
.modal-content {
    border-radius: 18px;
    overflow: hidden;
}
.modal-header-custom {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #4f46e5 100%);
    padding: 22px 28px;
    position: relative;
}
.modal-header-custom h5 { color:#fff; font-weight:700; margin:0; }
.modal-header-custom p { color:rgba(255,255,255,.72); font-size:12px; margin:4px 0 0; }
.modal-header-custom .btn-close { filter:invert(1); opacity:.8; }

.u-form-label {
    font-size: 11.5px; font-weight: 700; color: var(--u-muted);
    text-transform: uppercase; letter-spacing: .4px;
    display: block; margin-bottom: 5px;
}
.u-form-input {
    width: 100%;
    border: 1.5px solid var(--u-border);
    border-radius: 9px;
    padding: 10px 14px;
    font-size: 13px;
}
.u-form-input:focus { border-color: var(--u-accent); outline:none; box-shadow:0 0 0 3px rgba(37,99,235,.1); }

.u-empty {
    text-align:center; padding:48px 24px; color: var(--u-muted);
}
.u-empty i { font-size:3rem; display:block; margin-bottom:12px; opacity:.3; }

/* Chart card */
.u-chart-card {
    background: var(--u-surface);
    border: 1px solid var(--u-border);
    border-radius: var(--u-radius);
    overflow: hidden;
    margin-bottom: 24px;
}
.u-chart-card .chart-header {
    padding: 14px 20px;
    border-bottom: 1px solid var(--u-border);
    font-weight: 700;
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    {{-- Breadcrumb --}}
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="#" style="color:var(--u-accent)">User Management</a></li>
                    <li class="breadcrumb-item active">Users</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Hero --}}
    <div class="u-hero">
        <div class="row align-items-center">
            <div class="col">
                <h1><i class="ri-team-line me-2"></i>User Management</h1>
                <p>Manage system users, roles, and student portal access from one place.</p>
            </div>
            <div class="col-auto">
                <div class="d-flex gap-2">
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
    </div>

    {{-- Alerts --}}
    @if ($errors->any())
    <div class="alert alert-danger border-0 rounded-3 mb-3">
        <strong>Error:</strong> {{ $errors->all()[0] }}
    </div>
    @endif
    @if (session('success'))
    <div class="alert alert-success border-0 rounded-3 mb-3">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    </div>
    @endif

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-group-line"></i></div>
                <div class="stat-value">{{ $data->count() }}</div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>
        @foreach(array_slice($role_counts, 0, 3, true) as $role => $count)
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon"><i class="ri-shield-user-line"></i></div>
                <div class="stat-value" style="color:var(--u-accent)">{{ $count }}</div>
                <div class="stat-label">{{ $role }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Chart --}}
    <div class="u-chart-card">
        <div class="chart-header">
            <i class="ri-bar-chart-2-line me-2" style="color:var(--u-accent)"></i>
            Users by Role
        </div>
        <div class="p-4">
            <canvas id="usersByRoleChart" height="80"></canvas>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="u-filter-card mb-3">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="u-form-label">Search</label>
                <div class="u-input-icon-wrap">
                    <i class="bi bi-search u-input-icon"></i>
                    <input type="text" id="liveSearch" class="u-input" placeholder="Name, email…">
                </div>
            </div>
            <div class="col-md-3">
                <label class="u-form-label">Role</label>
                <select id="roleFilter" class="u-input">
                    <option value="">All Roles</option>
                    @foreach ($roles as $role => $name)
                    <option value="{{ strtolower($name) }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="u-form-label">Email</label>
                <select id="emailFilter" class="u-input">
                    <option value="">All Emails</option>
                    @foreach ($data->take(10) as $user)
                    <option value="{{ strtolower($user->email) }}">{{ $user->email }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="button" class="u-btn ghost w-100" id="clearFilters">
                    <i class="bi bi-x-circle"></i> Clear
                </button>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="u-table-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="fw-bold">
                <i class="ri-list-check me-2" style="color:var(--u-accent)"></i>
                Users List
                <span class="badge bg-primary ms-2" id="userCountBadge">{{ $data->count() }}</span>
            </div>
            <button class="u-btn danger d-none" id="remove-actions" onclick="deleteMultiple()">
                <i class="ri-delete-bin-2-line"></i> Delete Selected
            </button>
        </div>
        <div class="table-responsive">
            <table class="table u-table mb-0">
                <thead>
                    <tr>
                        <th width="40"><input type="checkbox" id="checkAll"></th>
                        <th width="50"></th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Registered</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    @forelse ($data as $key => $user)
                    @php
                        $initials = strtoupper(substr($user->name,0,1) . (strpos($user->name,' ')!==false ? substr($user->name, strpos($user->name,' ')+1, 1) : ''));
                        $roleNames = $user->getRoleNames();
                    @endphp
                    <tr data-id="{{ $user->id }}" data-name="{{ strtolower($user->name) }}" data-email="{{ strtolower($user->email) }}" data-roles="{{ strtolower($roleNames->implode(',')) }}">
                        <td><input type="checkbox" class="row-check"></td>
                        <td><div class="u-avatar">{{ $initials ?: 'U' }}</div></td>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($roleNames->isNotEmpty())
                                @foreach($roleNames as $role)
                                    <span class="u-role-pill {{ strtolower($role) == 'student' ? 'student' : (strtolower($role) == 'admin' ? 'admin' : 'default') }}">
                                        {{ $role }}
                                    </span>
                                @endforeach
                            @else
                                <span class="u-role-pill default">No Role</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <button class="u-action-btn view" onclick="viewUser({{ $user->id }})" title="View">
                                    <i class="ph-eye"></i>
                                </button>
                                <button class="u-action-btn edit" onclick="editUser({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->email }}', '{{ $roleNames->implode(',') }}')" title="Edit">
                                    <i class="ph-pencil"></i>
                                </button>
                                <button class="u-action-btn del" onclick="deleteUser({{ $user->id }})" title="Delete">
                                    <i class="ph-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7"><div class="u-empty"><i class="ri-user-line"></i>No users found</div></td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
</div>
</div>

{{-- ==============================================
     ADD USER MODAL
============================================== --}}
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header-custom">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                <h5><i class="bi bi-person-plus me-2"></i>Add New User</h5>
                <p>Create a system user with role-based access</p>
            </div>
            <form id="addUserForm">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="u-form-label">Full Name</label>
                        <input type="text" id="add_name" class="u-form-input" required>
                    </div>
                    <div class="mb-3">
                        <label class="u-form-label">Email Address</label>
                        <input type="email" id="add_email" class="u-form-input" required>
                    </div>
                    <div class="mb-3">
                        <label class="u-form-label">Role(s)</label>
                        <select id="add_roles" class="u-form-input" multiple required>
                            @foreach (Role::all() as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="u-form-label">Password</label>
                            <input type="password" id="add_password" class="u-form-input" required>
                        </div>
                        <div class="col-md-6">
                            <label class="u-form-label">Confirm Password</label>
                            <input type="password" id="add_password_confirmation" class="u-form-input" required>
                        </div>
                    </div>
                    <div id="addUserError" class="alert alert-danger d-none mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="u-btn ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="u-btn primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ==============================================
     EDIT USER MODAL
============================================== --}}
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header-custom" style="background:linear-gradient(135deg,#065f46,#16a34a,#4ade80);">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                <h5><i class="ph-pencil me-2"></i>Edit User</h5>
                <p>Update user information and permissions</p>
            </div>
            <form id="editUserForm">
                <div class="modal-body p-4">
                    <input type="hidden" id="edit_id">
                    <div class="mb-3">
                        <label class="u-form-label">Full Name</label>
                        <input type="text" id="edit_name" class="u-form-input" required>
                    </div>
                    <div class="mb-3">
                        <label class="u-form-label">Email Address</label>
                        <input type="email" id="edit_email" class="u-form-input" required>
                    </div>
                    <div class="mb-3">
                        <label class="u-form-label">Role(s)</label>
                        <select id="edit_roles" class="u-form-input" multiple required>
                            @foreach (Role::all() as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="u-form-label">New Password <span class="text-muted">(optional)</span></label>
                            <input type="password" id="edit_password" class="u-form-input" placeholder="Leave blank to keep">
                        </div>
                        <div class="col-md-6">
                            <label class="u-form-label">Confirm Password</label>
                            <input type="password" id="edit_password_confirmation" class="u-form-input">
                        </div>
                    </div>
                    <div id="editUserError" class="alert alert-danger d-none mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="u-btn ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="u-btn success">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ==============================================
     DELETE CONFIRM MODAL
============================================== --}}
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px">
        <div class="modal-content text-center p-4">
            <div class="mb-3">
                <div style="width:70px;height:70px;border-radius:50%;background:#fef2f2;display:inline-flex;align-items:center;justify-content:center;">
                    <i class="bi bi-trash" style="font-size:28px;color:var(--u-danger)"></i>
                </div>
            </div>
            <h4 class="mb-2">Confirm Delete</h4>
            <p class="text-muted mb-4">This action cannot be undone. The user will be permanently removed.</p>
            <input type="hidden" id="delete_user_id">
            <div class="d-flex gap-3 justify-content-center">
                <button type="button" class="u-btn ghost" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="u-btn danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash"></i> Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ==============================================
     ADD STUDENT MODAL
============================================== --}}
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header-custom" style="background:linear-gradient(135deg,#064e3b,#059669,#34d399);">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                <h5><i class="bi bi-mortarboard me-2"></i>Add Student as User</h5>
                <p>Create portal access for a registered student</p>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="u-form-label">Select Student</label>
                    <select id="student_select" class="u-form-input" required>
                        <option value="">— Choose a student —</option>
                        @php
                            $students = \App\Models\Student::whereDoesntHave('user')->get();
                        @endphp
                        @foreach($students as $student)
                        <option value="{{ $student->id }}" data-name="{{ $student->name }}" data-email="{{ $student->email ?? '' }}" data-admission="{{ $student->admissionNo }}">
                            {{ $student->name }} ({{ $student->admissionNo }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="alert alert-info">
                    <i class="bi bi-envelope me-2"></i>
                    Email will be: <code id="previewEmail">student.email@school.com</code>
                </div>
                <div id="studentError" class="alert alert-danger d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="u-btn ghost" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="u-btn success" id="continueToCredentialsBtn">Continue <i class="bi bi-arrow-right ms-1"></i></button>
            </div>
        </div>
    </div>
</div>

{{-- ==============================================
     SET STUDENT CREDENTIALS MODAL
============================================== --}}
<div class="modal fade" id="setStudentCredentialsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header-custom">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                <h5><i class="bi bi-key me-2"></i>Set Student Credentials</h5>
                <p>Configure login details for the student</p>
            </div>
            <form id="studentCredentialsForm">
                <div class="modal-body p-4">
                    <input type="hidden" id="student_id" name="student_id">
                    <div class="mb-3">
                        <label class="u-form-label">Email Address</label>
                        <input type="email" id="student_email" name="email" class="u-form-input" required>
                    </div>
                    <div class="mb-3">
                        <label class="u-form-label">Username (Admission No)</label>
                        <input type="text" id="student_username" name="username" class="u-form-input" readonly>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <label class="u-form-label">Password</label>
                            <input type="text" id="student_password" name="password" class="u-form-input" required>
                        </div>
                        <div class="col-md-4">
                            <label class="u-form-label">&nbsp;</label>
                            <button type="button" class="u-btn ghost w-100" id="generatePasswordBtn">Generate</button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="u-form-label">Confirm Password</label>
                        <input type="password" id="student_password_confirmation" name="password_confirmation" class="u-form-input" required>
                    </div>
                    <div id="studentCredError" class="alert alert-danger d-none mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="u-btn ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="u-btn primary">Create Student User</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
// Wait for Bootstrap to be ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded, initializing...');

    // Test if Bootstrap is working
    if (typeof bootstrap !== 'undefined') {
        console.log('Bootstrap is loaded');
    } else {
        console.error('Bootstrap is NOT loaded');
    }
});

// Global functions for modals
function openAddUserModal() {
    const modalEl = document.getElementById('addUserModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

function openAddStudentModal() {
    const modalEl = document.getElementById('addStudentModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

function viewUser(id) {
    window.location.href = '/users/' + id;
}

function editUser(id, name, email, roles) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_email').value = email;

    // Set roles
    const roleArray = roles.split(',');
    const roleSelect = document.getElementById('edit_roles');
    for(let opt of roleSelect.options) {
        opt.selected = roleArray.includes(opt.value);
    }

    document.getElementById('edit_password').value = '';
    document.getElementById('edit_password_confirmation').value = '';
    document.getElementById('editUserError').classList.add('d-none');

    const modalEl = document.getElementById('editUserModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

let deleteUserId = null;
function deleteUser(id) {
    deleteUserId = id;
    document.getElementById('delete_user_id').value = id;
    const modalEl = document.getElementById('deleteUserModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

// Add User Form Submit
document.getElementById('addUserForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const name = document.getElementById('add_name').value;
    const email = document.getElementById('add_email').value;
    const roles = Array.from(document.getElementById('add_roles').selectedOptions).map(o => o.value);
    const password = document.getElementById('add_password').value;
    const password_confirmation = document.getElementById('add_password_confirmation').value;

    if (!name || !email || roles.length === 0 || !password) {
        showError('addUserError', 'Please fill all required fields');
        return;
    }

    if (password !== password_confirmation) {
        showError('addUserError', 'Passwords do not match');
        return;
    }

    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';

    axios.post('/users', {
        name: name,
        email: email,
        roles: roles,
        password: password,
        password_confirmation: password_confirmation,
        _token: '{{ csrf_token() }}'
    }).then(response => {
        Swal.fire('Success', 'User created successfully', 'success');
        location.reload();
    }).catch(error => {
        let msg = error.response?.data?.message || 'Error creating user';
        if (error.response?.data?.errors) {
            msg = Object.values(error.response.data.errors).flat().join(', ');
        }
        showError('addUserError', msg);
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Create User';
    });
});

// Edit User Form Submit
document.getElementById('editUserForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const id = document.getElementById('edit_id').value;
    const name = document.getElementById('edit_name').value;
    const email = document.getElementById('edit_email').value;
    const roles = Array.from(document.getElementById('edit_roles').selectedOptions).map(o => o.value);
    const password = document.getElementById('edit_password').value;
    const password_confirmation = document.getElementById('edit_password_confirmation').value;

    if (!name || !email || roles.length === 0) {
        showError('editUserError', 'Please fill all required fields');
        return;
    }

    if (password && password !== password_confirmation) {
        showError('editUserError', 'Passwords do not match');
        return;
    }

    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';

    const data = { name, email, roles, _token: '{{ csrf_token() }}' };
    if (password) {
        data.password = password;
        data.password_confirmation = password_confirmation;
    }

    axios.put(`/users/${id}`, data).then(response => {
        Swal.fire('Success', 'User updated successfully', 'success');
        location.reload();
    }).catch(error => {
        let msg = error.response?.data?.message || 'Error updating user';
        if (error.response?.data?.errors) {
            msg = Object.values(error.response.data.errors).flat().join(', ');
        }
        showError('editUserError', msg);
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Update User';
    });
});

// Confirm Delete
document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
    const id = document.getElementById('delete_user_id').value;

    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';

    axios.delete(`/users/${id}`, {
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    }).then(() => {
        Swal.fire('Deleted', 'User removed successfully', 'success');
        location.reload();
    }).catch(error => {
        Swal.fire('Error', error.response?.data?.message || 'Delete failed', 'error');
        this.disabled = false;
        this.innerHTML = '<i class="bi bi-trash"></i> Yes, Delete';
    });
});

// Student selection preview
document.getElementById('student_select')?.addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    if (option.value) {
        const name = option.dataset.name || '';
        const emailPreview = name.toLowerCase().replace(/[^a-z]/g, '.') + '@csskabba.ng';
        document.getElementById('previewEmail').textContent = emailPreview;
        document.getElementById('continueToCredentialsBtn').disabled = false;
    } else {
        document.getElementById('continueToCredentialsBtn').disabled = true;
    }
});

// Continue to credentials
document.getElementById('continueToCredentialsBtn')?.addEventListener('click', function() {
    const select = document.getElementById('student_select');
    const option = select.options[select.selectedIndex];

    if (!option.value) return;

    const studentId = option.value;
    const studentName = option.dataset.name || '';
    const studentEmail = option.dataset.email || '';
    const admissionNo = option.dataset.admission || '';

    // Generate email
    const generatedEmail = studentName.toLowerCase().replace(/[^a-z]/g, '.') + '@csskabba.ng';

    document.getElementById('student_id').value = studentId;
    document.getElementById('student_email').value = studentEmail || generatedEmail;
    document.getElementById('student_username').value = admissionNo.replace(/[\/\\]/g, '_');

    // Close first modal, open second
    bootstrap.Modal.getInstance(document.getElementById('addStudentModal')).hide();
    setTimeout(() => {
        const modalEl = document.getElementById('setStudentCredentialsModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }, 300);
});

// Generate random password
document.getElementById('generatePasswordBtn')?.addEventListener('click', function() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%';
    let password = '';
    for(let i = 0; i < 10; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('student_password').value = password;
    document.getElementById('student_password_confirmation').value = password;
});

// Student Credentials Form Submit
document.getElementById('studentCredentialsForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append('_token', '{{ csrf_token() }}');

    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';

    axios.post('{{ route("users.store-student") }}', formData)
        .then(response => {
            Swal.fire('Success', 'Student user created successfully', 'success');
            location.reload();
        })
        .catch(error => {
            let msg = error.response?.data?.message || 'Error creating student user';
            if (error.response?.data?.errors) {
                msg = Object.values(error.response.data.errors).flat().join('<br>');
            }
            const errorDiv = document.getElementById('studentCredError');
            errorDiv.innerHTML = msg;
            errorDiv.classList.remove('d-none');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Create Student User';
        });
});

// Helper functions
function showError(elementId, message) {
    const el = document.getElementById(elementId);
    el.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>' + message;
    el.classList.remove('d-none');
    setTimeout(() => el.classList.add('d-none'), 5000);
}

// Filter functionality
document.getElementById('liveSearch')?.addEventListener('keyup', filterTable);
document.getElementById('roleFilter')?.addEventListener('change', filterTable);
document.getElementById('emailFilter')?.addEventListener('change', filterTable);
document.getElementById('clearFilters')?.addEventListener('click', function() {
    document.getElementById('liveSearch').value = '';
    document.getElementById('roleFilter').value = '';
    document.getElementById('emailFilter').value = '';
    filterTable();
});

function filterTable() {
    const search = document.getElementById('liveSearch').value.toLowerCase();
    const roleFilter = document.getElementById('roleFilter').value.toLowerCase();
    const emailFilter = document.getElementById('emailFilter').value.toLowerCase();

    const rows = document.querySelectorAll('#usersTableBody tr[data-id]');
    let visibleCount = 0;

    rows.forEach(row => {
        const name = row.dataset.name || '';
        const email = row.dataset.email || '';
        const roles = row.dataset.roles || '';

        const matchSearch = !search || name.includes(search) || email.includes(search);
        const matchRole = !roleFilter || roles.includes(roleFilter);
        const matchEmail = !emailFilter || email === emailFilter;

        if (matchSearch && matchRole && matchEmail) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    document.getElementById('userCountBadge').textContent = visibleCount;
}

// Check all functionality
document.getElementById('checkAll')?.addEventListener('change', function() {
    document.querySelectorAll('.row-check:not(:disabled)').forEach(cb => {
        cb.checked = this.checked;
    });
    toggleDeleteButton();
});

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('row-check')) {
        toggleDeleteButton();
    }
});

function toggleDeleteButton() {
    const checked = document.querySelectorAll('.row-check:checked').length;
    const deleteBtn = document.getElementById('remove-actions');
    if (deleteBtn) {
        deleteBtn.classList.toggle('d-none', checked === 0);
    }
}

function deleteMultiple() {
    const ids = [];
    document.querySelectorAll('.row-check:checked').forEach(cb => {
        const id = cb.closest('tr').dataset.id;
        if (id) ids.push(id);
    });

    if (ids.length === 0) return;

    Swal.fire({
        title: `Delete ${ids.length} user(s)?`,
        text: 'This cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Yes, delete them!'
    }).then(result => {
        if (result.isConfirmed) {
            Promise.all(ids.map(id =>
                axios.delete(`/users/${id}`, { headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            )).then(() => {
                Swal.fire('Deleted!', `${ids.length} users removed.`, 'success');
                location.reload();
            }).catch(() => {
                Swal.fire('Error', 'Some deletions failed', 'error');
            });
        }
    });
}

// Chart
const chartCtx = document.getElementById('usersByRoleChart')?.getContext('2d');
if (chartCtx) {
    new Chart(chartCtx, {
        type: 'bar',
        data: {
            labels: @json(array_keys($role_counts)),
            datasets: [{
                label: 'Users',
                data: @json(array_values($role_counts)),
                backgroundColor: ['rgba(37,99,235,.75)', 'rgba(16,185,129,.75)', 'rgba(245,158,11,.75)', 'rgba(239,68,68,.75)'],
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
}
</script>

@endsection
