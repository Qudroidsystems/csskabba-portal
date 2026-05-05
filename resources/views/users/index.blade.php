@extends('layouts.master')

@section('content')
<?php
use Spatie\Permission\Models\Role;
?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <!-- Start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Users</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">User Management</a></li>
                                <li class="breadcrumb-item active">Users</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End page title -->

            <!-- Users by Role Chart -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Users by Role</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="usersByRoleChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Whoops!</strong> There were some problems with your input.<br><br>
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div id="userList">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xxl-3">
                                        <div class="search-box">
                                            <input type="text" class="form-control search" placeholder="Search users">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <select class="form-control" id="idRole"
                                            data-choices data-choices-search-false data-choices-removeItem>
                                            <option value="all">Select Role</option>
                                            @foreach ($roles as $role => $name)
                                            <option value="{{ $role }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-2 col-sm-6">
                                        <select class="form-control" id="idEmail"
                                            data-choices data-choices-search-false data-choices-removeItem>
                                            <option value="all">Select Email</option>
                                            @foreach ($data as $user)
                                            <option value="{{ $user->email }}">{{ $user->email }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-1 col-sm-6">
                                        <button type="button" class="btn btn-secondary w-100"
                                            onclick="filterData();">
                                            <i class="bi bi-funnel align-baseline me-1"></i> Filters
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">
                                        Users
                                        <span class="badge bg-dark-subtle text-dark ms-1">{{ $data->count() }}</span>
                                    </h5>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="d-flex flex-wrap align-items-start gap-2">
                                        <button class="btn btn-subtle-danger d-none" id="remove-actions"
                                            onclick="deleteMultiple()">
                                            <i class="ri-delete-bin-2-line"></i>
                                        </button>
                                        @can('Create user')
                                        <button type="button" class="btn btn-primary add-btn"
                                            data-bs-toggle="modal" data-bs-target="#showModal">
                                            <i class="bi bi-plus-circle align-baseline me-1"></i> Add User
                                        </button>
                                        <button type="button" class="btn btn-success add-btn"
                                            data-bs-toggle="modal" data-bs-target="#addStudentModal">
                                            <i class="bi bi-person-plus align-baseline me-1"></i> Add Student
                                        </button>
                                        <button type="button" class="btn btn-warning"
                                            data-bs-toggle="modal" data-bs-target="#massStudentModal">
                                            <i class="bi bi-people-fill me-1"></i> Mass Manage Students
                                        </button>
                                        @endcan
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-centered align-middle table-nowrap mb-0" id="userListTable">
                                        <thead class="table-active">
                                            <tr>
                                                <th>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            value="option" id="checkAll">
                                                        <label class="form-check-label" for="checkAll"></label>
                                                    </div>
                                                </th>
                                                <th class="sort cursor-pointer" data-sort="name">Name</th>
                                                <th class="sort cursor-pointer" data-sort="email">Email</th>
                                                <th class="sort cursor-pointer" data-sort="role">Role</th>
                                                <th class="sort cursor-pointer" data-sort="datereg">Date Registered</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list form-check-all">
                                            @forelse ($data as $key => $user)
                                            <tr>
                                                <td class="id" data-id="{{ $user->id }}">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="chk_child">
                                                        <label class="form-check-label"></label>
                                                    </div>
                                                </td>
                                                <td class="name" data-name="{{ $user->name }}">
                                                    <div class="d-flex align-items-center">
                                                        <div>
                                                            <h6 class="mb-0">
                                                                <a href="{{ route('users.show', $user->id) }}"
                                                                    class="text-reset products">
                                                                    {{ $user->name }}
                                                                </a>
                                                            </h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="email" data-email="{{ $user->email }}">
                                                    {{ $user->email }}
                                                </td>
                                                <td class="role"
                                                    data-roles="{{ $user->getRoleNames()->implode(',') }}">
                                                    <div>
                                                        @if(!empty($user->getRoleNames()))
                                                        @foreach($user->getRoleNames() as $val)
                                                        <label class="badge bg-primary">{{ $val }}</label>
                                                        @endforeach
                                                        @else
                                                        <label class="badge bg-secondary">No roles</label>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="datereg">
                                                    {{ $user->created_at->format('Y-m-d') }}
                                                </td>
                                                <td>
                                                    <ul class="d-flex gap-2 list-unstyled mb-0">
                                                        @can('View user')
                                                        <li>
                                                            <a href="{{ route('users.show', $user->id) }}"
                                                                class="btn btn-subtle-primary btn-icon btn-sm"
                                                                title="View">
                                                                <i class="ph-eye"></i>
                                                            </a>
                                                        </li>
                                                        @endcan

                                                        @can('Update user')
                                                        <li>
                                                            <a href="javascript:void(0);"
                                                                class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn"
                                                                title="Edit">
                                                                <i class="ph-pencil"></i>
                                                            </a>
                                                        </li>
                                                        @endcan

                                                        {{-- Reset password button - student users only --}}
                                                        @can('Update user')
                                                        @if($user->hasRole('Student'))
                                                        <li>
                                                            <button type="button"
                                                                class="btn btn-subtle-warning btn-icon btn-sm reset-student-pwd-btn"
                                                                data-user-id="{{ $user->id }}"
                                                                data-user-name="{{ $user->name }}"
                                                                title="Reset Password">
                                                                <i class="bi bi-key-fill"></i>
                                                            </button>
                                                        </li>
                                                        @endif
                                                        @endcan

                                                        @can('Delete user')
                                                        <li>
                                                            <a href="javascript:void(0);"
                                                                class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn"
                                                                title="Delete">
                                                                <i class="ph-trash"></i>
                                                            </a>
                                                        </li>
                                                        @endcan
                                                    </ul>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="7" class="noresult" style="display:block;">
                                                    No results found
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row mt-3 align-items-center" id="pagination-element">
                                    <div class="col-sm">
                                        <div class="text-muted text-center text-sm-start">
                                            Showing <span class="fw-semibold" id="pagination-showing"></span>
                                            of <span class="fw-semibold" id="pagination-total"></span> Results
                                        </div>
                                    </div>
                                    <div class="col-sm-auto mt-3 mt-sm-0">
                                        <div class="pagination-wrap hstack gap-2 justify-content-center">
                                            <ul class="pagination listjs-pagination mb-0"></ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ADD USER MODAL -->
            <div id="showModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 id="addModalLabel" class="modal-title">Add User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <form class="tablelist-form" autocomplete="off" id="add-user-form">
                            <div class="modal-body">
                                <input type="hidden" id="add-id-field" name="id">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" id="name" name="name" class="form-control"
                                        placeholder="Enter name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" id="email" name="email" class="form-control"
                                        placeholder="Enter email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="role" class="form-label">Role</label>
                                    <select id="role" name="roles[]" class="form-control" multiple required>
                                        @foreach (Role::all() as $role)
                                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" id="password" name="password" class="form-control"
                                        placeholder="Enter password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <input type="password" id="password_confirmation"
                                        name="password_confirmation" class="form-control"
                                        placeholder="Confirm password" required>
                                </div>
                                <div class="alert alert-danger d-none" id="alert-error-msg"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="add-btn">Add User</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- EDIT USER MODAL -->
            <div id="editModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 id="editModalLabel" class="modal-title">Edit User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <form class="tablelist-form" autocomplete="off" id="edit-user-form">
                            <div class="modal-body">
                                <input type="hidden" id="edit-id-field" name="id">
                                <div class="mb-3">
                                    <label for="edit-name" class="form-label">Name</label>
                                    <input type="text" id="edit-name" name="name" class="form-control"
                                        placeholder="Enter name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-email" class="form-label">Email</label>
                                    <input type="email" id="edit-email" name="email" class="form-control"
                                        placeholder="Enter email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-role" class="form-label">Role</label>
                                    <select id="edit-role" name="roles[]" class="form-control" multiple required>
                                        @foreach (Role::all() as $role)
                                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-password" class="form-label">Password (optional)</label>
                                    <input type="password" id="edit-password" name="password" class="form-control"
                                        placeholder="Enter new password">
                                </div>
                                <div class="mb-3">
                                    <label for="edit-password_confirmation" class="form-label">
                                        Confirm Password
                                    </label>
                                    <input type="password" id="edit-password_confirmation"
                                        name="password_confirmation" class="form-control"
                                        placeholder="Confirm new password">
                                </div>
                                <div class="alert alert-danger d-none" id="alert-error-msg"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="update-btn">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- DELETE USER MODAL -->
            <div id="deleteRecordModal" class="modal fade zoomIn" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="btn-close" id="deleteRecord-close"
                                data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-md-5">
                            <div class="text-center">
                                <div class="text-danger">
                                    <i class="bi bi-trash display-4"></i>
                                </div>
                                <div class="mt-4">
                                    <h3 class="mb-2">Are you sure?</h3>
                                    <p class="text-muted fs-lg mx-3 mb-0">
                                        Are you sure you want to remove this record?
                                    </p>
                                </div>
                            </div>
                            <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                                <button type="button" class="btn w-sm btn-light btn-hover"
                                    data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn w-sm btn-danger btn-hover"
                                    id="delete-record">Yes, Delete!</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ADD STUDENT MODAL (single) -->
            <div id="addStudentModal" class="modal fade" tabindex="-1" aria-hidden="true"
                data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Student as User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="student-search" class="form-label">Search student</label>
                                <input type="text" id="student-search" class="form-control"
                                    placeholder="Admission no, name..." autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label for="student-select" class="form-label">Select Student</label>
                                <select id="student-select" class="form-control" required>
                                    <option value="">-- Choose a student --</option>
                                </select>
                            </div>
                            <div class="alert alert-info">
                                <small>Email will be auto-generated as: firstname.lastname@csskabba.ng</small>
                            </div>
                            <div class="alert alert-danger d-none" id="student-select-error"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="proceed-to-credentials" disabled>
                                Proceed to Credentials
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SET STUDENT CREDENTIALS MODAL (single) -->
            <div id="setStudentCredentialsModal" class="modal fade" tabindex="-1" aria-hidden="true"
                data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Set Credentials for Student</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <form class="tablelist-form" autocomplete="off" id="add-student-credentials-form">
                            <div class="modal-body">
                                <input type="hidden" id="student-id-field" name="student_id">
                                <input type="hidden" id="student-name-field" name="name">
                                <div class="mb-3">
                                    <label for="student-user-email" class="form-label">Email</label>
                                    <input type="email" id="student-user-email" name="email"
                                        class="form-control" placeholder="Enter email (required)" required>
                                    <div class="form-text">
                                        Will be auto-generated as firstname.lastname@csskabba.ng
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="student-username" class="form-label">
                                        Username (Admission Number)
                                    </label>
                                    <input type="text" id="student-username" name="username"
                                        class="form-control" readonly required>
                                </div>
                                <div class="mb-3">
                                    <label for="student-password" class="form-label">Temporary Password</label>
                                    <div class="input-group">
                                        <input type="password" id="student-password" name="password"
                                            class="form-control"
                                            placeholder="Temporary password will be generated" required>
                                        <button type="button" class="btn btn-outline-secondary"
                                            id="generate-temp-password">Generate</button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="student-password_confirmation" class="form-label">
                                        Confirm Password
                                    </label>
                                    <input type="password" id="student-password_confirmation"
                                        name="password_confirmation" class="form-control"
                                        placeholder="Confirm password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="student-role" class="form-label">
                                        Role <span class="text-danger">*</span>
                                    </label>
                                    <select id="student-role" name="roles[]" class="form-control" required>
                                        <option value="Student" selected>Student</option>
                                    </select>
                                    <div class="form-text">Student role is assigned by default.</div>
                                </div>
                                <div class="alert alert-danger d-none" id="student-credentials-error"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                                    onclick="resetStudentCredentialsModal()">Close</button>
                                <button type="submit" class="btn btn-primary" id="create-student-user">
                                    Create User
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Include Mass Student Modal -->
            @include('users.partials.mass-student-modal')

        </div>
    </div>
</div>

{{-- Scripts - Using Online CDNs --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/list.js@2.3.1/dist/list.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/scripts/choices.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// ==================== MAIN APPLICATION SCRIPT ====================
(function() {
    'use strict';

    // Global variables
    var perPage = 100,
        editlist = false,
        checkAll = document.getElementById("checkAll"),
        options = {
            valueNames: ["id", "name", "email", "role", "datereg"],
            page: perPage,
            pagination: true,
            item: '缘<td class="id" data-id><div class="form-check"><input class="form-check-input" type="checkbox" name="chk_child"><label class="form-check-label"></label></div></td><td class="name" data-name><div class="d-flex align-items-center"><div><h6 class="mb-0"><a href="#" class="text-reset products"></a></h6></div></div></td><td class="email" data-email></td><td class="role" data-roles><div></div></td><td class="datereg"></td><td><ul class="d-flex gap-2 list-unstyled mb-0"><li><a href="#" class="btn btn-subtle-primary btn-icon btn-sm"><i class="ph-eye"></i></a></li><li><a href="javascript:void(0);" class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn"><i class="ph-pencil"></i></a></li><li><a href="javascript:void(0);" class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn"><i class="ph-trash"></i></a></li></ul></td>'
        },
        userList = new List("userList", options),
        addIdField = document.getElementById("add-id-field"),
        addNameField = document.getElementById("name"),
        addEmailField = document.getElementById("email"),
        addRoleField = document.getElementById("role"),
        addPasswordField = document.getElementById("password"),
        addPasswordConfirmField = document.getElementById("password_confirmation"),
        editIdField = document.getElementById("edit-id-field"),
        editNameField = document.getElementById("edit-name"),
        editEmailField = document.getElementById("edit-email"),
        editRoleField = document.getElementById("edit-role"),
        editPasswordField = document.getElementById("edit-password"),
        editPasswordConfirmField = document.getElementById("edit-password_confirmation"),
        addRoleVal = null,
        editRoleVal = null,
        roleFilterVal = null,
        emailFilterVal = null;

    // Make globally accessible
    window.userList = userList;
    window.editlist = editlist;
    window.editIdField = editIdField;
    window.editNameField = editNameField;
    window.editEmailField = editEmailField;
    window.editRoleField = editRoleField;
    window.editRoleVal = editRoleVal;
    window.ensureAxios = ensureAxios;

    function ensureAxios() {
        if (typeof axios === 'undefined') {
            console.error("Axios is not defined");
            Swal.fire({
                icon: "error",
                title: "Configuration error",
                text: "Axios library is missing",
                showConfirmButton: true
            });
            return false;
        }
        return true;
    }

    function refreshCallbacks() {
        console.log("refreshCallbacks executed");
        var removeButtons = document.getElementsByClassName("remove-item-btn");
        var editButtons = document.getElementsByClassName("edit-item-btn");

        Array.from(removeButtons).forEach(function(btn) {
            btn.removeEventListener("click", handleRemoveClick);
            btn.addEventListener("click", handleRemoveClick);
        });

        Array.from(editButtons).forEach(function(btn) {
            btn.removeEventListener("click", handleEditClick);
            btn.addEventListener("click", handleEditClick);
        });
    }

    function handleRemoveClick(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log("Remove button clicked");

        var btn = e.target.closest("tr");
        if (!btn) {
            console.error("Could not find parent tr");
            return;
        }

        var idElement = btn.querySelector(".id");
        if (!idElement) {
            console.error("Could not find .id element");
            return;
        }

        var itemId = idElement.getAttribute("data-id");
        if (!itemId) {
            console.error("No data-id attribute found");
            return;
        }

        console.log("Deleting user ID:", itemId);

        var deleteBtn = document.getElementById("delete-record");
        if (deleteBtn) {
            var newDeleteBtn = deleteBtn.cloneNode(true);
            deleteBtn.parentNode.replaceChild(newDeleteBtn, deleteBtn);

            newDeleteBtn.addEventListener("click", function() {
                if (!ensureAxios()) return;
                axios.delete(`/users/${itemId}`, {
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                }).then(function() {
                    userList.remove("id", itemId);
                    Swal.fire({
                        icon: "success",
                        title: "User deleted successfully!",
                        showConfirmButton: false,
                        timer: 2000
                    });
                    var modal = bootstrap.Modal.getInstance(document.getElementById("deleteRecordModal"));
                    if (modal) modal.hide();
                }).catch(function(error) {
                    Swal.fire({
                        icon: "error",
                        title: "Error deleting user",
                        text: error.response?.data?.message || "An error occurred"
                    });
                });
            });
        }

        var modal = new bootstrap.Modal(document.getElementById("deleteRecordModal"));
        modal.show();
    }

    function handleEditClick(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log("Edit button clicked");

        var tr = e.target.closest("tr");
        if (!tr) {
            console.error("Could not find parent tr");
            return;
        }

        var idElement = tr.querySelector(".id");
        if (!idElement) {
            console.error("Could not find .id element");
            return;
        }

        var itemId = idElement.getAttribute("data-id");
        if (!itemId) {
            console.error("No data-id attribute found");
            return;
        }

        console.log("Editing user ID:", itemId);

        editIdField.value = itemId;

        var nameElement = tr.querySelector(".name a");
        editNameField.value = nameElement ? nameElement.innerText : "";

        var emailElement = tr.querySelector(".email");
        editEmailField.value = emailElement ? emailElement.innerText : "";

        var roleElement = tr.querySelector(".role");
        var roles = roleElement ? roleElement.getAttribute("data-roles")?.split(",").filter(r => r.trim()) : [];

        if (typeof Choices !== 'undefined' && editRoleVal) {
            editRoleVal.removeActiveItems();
            editRoleVal.setChoiceByValue(roles);
        } else if (editRoleField) {
            Array.from(editRoleField.options).forEach(option => {
                option.selected = roles.includes(option.value);
            });
        }

        var modal = new bootstrap.Modal(document.getElementById("editModal"));
        modal.show();
    }

    function ischeckboxcheck() {
        const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
        checkboxes.forEach((checkbox) => {
            checkbox.removeEventListener("change", handleCheckboxChange);
            checkbox.addEventListener("change", handleCheckboxChange);
        });
    }

    function handleCheckboxChange(e) {
        const row = e.target.closest("tr");
        if (e.target.checked) {
            row.classList.add("table-active");
        } else {
            row.classList.remove("table-active");
        }
        const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
        const removeActions = document.getElementById("remove-actions");
        if (removeActions) {
            removeActions.classList.toggle("d-none", checkedCount === 0);
        }
        if (checkAll) {
            const allCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]');
            checkAll.checked = allCheckboxes.length > 0 && allCheckboxes.length === checkedCount;
        }
    }

    function clearAddFields() {
        if (addIdField) addIdField.value = "";
        if (addNameField) addNameField.value = "";
        if (addEmailField) addEmailField.value = "";
        if (addPasswordField) addPasswordField.value = "";
        if (addPasswordConfirmField) addPasswordConfirmField.value = "";
        if (typeof Choices !== 'undefined' && addRoleVal) {
            addRoleVal.setChoiceByValue([]);
        } else if (addRoleField) {
            Array.from(addRoleField.options).forEach(option => option.selected = false);
        }
    }

    function clearEditFields() {
        if (editIdField) editIdField.value = "";
        if (editNameField) editNameField.value = "";
        if (editEmailField) editEmailField.value = "";
        if (editPasswordField) editPasswordField.value = "";
        if (editPasswordConfirmField) editPasswordConfirmField.value = "";
        if (typeof Choices !== 'undefined' && editRoleVal) {
            editRoleVal.setChoiceByValue([]);
        } else if (editRoleField) {
            Array.from(editRoleField.options).forEach(option => option.selected = false);
        }
    }

    function filterData() {
        var searchInput = document.querySelector(".search-box input.search");
        var searchValue = searchInput ? searchInput.value.toLowerCase() : "";
        var roleSelect = document.getElementById("idRole");
        var emailSelect = document.getElementById("idEmail");
        var selectedRole = (typeof Choices !== 'undefined' && roleFilterVal) ? roleFilterVal.getValue(true) : (roleSelect ? roleSelect.value : "all");
        var selectedEmail = (typeof Choices !== 'undefined' && emailFilterVal) ? emailFilterVal.getValue(true) : (emailSelect ? emailSelect.value : "all");

        userList.filter(function(item) {
            var nameMatch = item.values().name.toLowerCase().includes(searchValue);
            var emailMatch = item.values().email.toLowerCase().includes(searchValue);
            var roleMatch = selectedRole === "all" || item.values().role.split(",").includes(selectedRole);
            var emailSelectMatch = selectedEmail === "all" || item.values().email === selectedEmail;
            return (nameMatch || emailMatch) && roleMatch && emailSelectMatch;
        });
    }

    function deleteMultiple() {
        const ids_array = [];
        const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
        checkboxes.forEach((checkbox) => {
            if (checkbox.checked) {
                const id = checkbox.closest("tr").querySelector(".id").getAttribute("data-id");
                ids_array.push(id);
            }
        });

        if (ids_array.length > 0) {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    if (!ensureAxios()) return;
                    Promise.all(ids_array.map((id) => {
                        return axios.delete(`/users/${id}`, {
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                        });
                    })).then(() => {
                        ids_array.forEach(id => userList.remove("id", id));
                        Swal.fire("Deleted!", "Your data has been deleted.", "success");
                    }).catch((error) => {
                        Swal.fire("Error!", error.response?.data?.message || "Failed to delete users", "error");
                    });
                }
            });
        } else {
            Swal.fire("Please select at least one checkbox");
        }
    }

    function clearWhatsAppFields() {
        var userIdField = document.getElementById("whatsapp-user-id");
        var emailField = document.getElementById("whatsapp-email");
        var passwordField = document.getElementById("whatsapp-password");
        var phoneField = document.getElementById("whatsapp-phone");
        var linkContainer = document.getElementById("whatsapp-link-container");
        var linkElement = document.getElementById("whatsapp-link");
        var previewElement = document.getElementById("whatsapp-message-preview");

        if (userIdField) userIdField.value = "";
        if (emailField) emailField.value = "";
        if (passwordField) passwordField.value = "";
        if (phoneField) phoneField.value = "";
        if (linkContainer) linkContainer.classList.add("d-none");
        if (linkElement) linkElement.href = "#";
        if (previewElement) previewElement.textContent = "";
    }

    // Make functions globally accessible
    window.filterData = filterData;
    window.deleteMultiple = deleteMultiple;
    window.clearWhatsAppFields = clearWhatsAppFields;
    window.refreshCallbacks = refreshCallbacks;
    window.ischeckboxcheck = ischeckboxcheck;

    // DOM Content Loaded
    document.addEventListener("DOMContentLoaded", function() {
        console.log("DOM loaded, initializing...");

        // Initialize Choices.js
        if (typeof Choices !== 'undefined') {
            var roleElement = document.getElementById("role");
            var editRoleElement = document.getElementById("edit-role");
            var idRoleElement = document.getElementById("idRole");
            var idEmailElement = document.getElementById("idEmail");

            if (roleElement) addRoleVal = new Choices(roleElement, { searchEnabled: true, removeItemButton: true });
            if (editRoleElement) editRoleVal = new Choices(editRoleElement, { searchEnabled: true, removeItemButton: true });
            if (idRoleElement) roleFilterVal = new Choices(idRoleElement, { searchEnabled: true });
            if (idEmailElement) emailFilterVal = new Choices(idEmailElement, { searchEnabled: true });

            window.editRoleVal = editRoleVal;
        }

        // Update pagination display
        var showingEl = document.getElementById("pagination-showing");
        var totalEl = document.getElementById("pagination-total");
        if (showingEl) showingEl.innerText = Math.min(perPage, userList.items.length);
        if (totalEl) totalEl.innerText = userList.items.length;

        // List.js update event
        userList.on("updated", function(e) {
            const noResultElement = document.getElementsByClassName("noresult")[0];
            if (noResultElement) {
                noResultElement.style.display = e.matchingItems.length === 0 ? "block" : "none";
            }
            if (showingEl) showingEl.innerText = e.matchingItems.length;
            if (totalEl) totalEl.innerText = userList.items.length;
            setTimeout(() => {
                refreshCallbacks();
                ischeckboxcheck();
            }, 100);
        });

        refreshCallbacks();
        ischeckboxcheck();

        // CheckAll functionality
        if (checkAll) {
            checkAll.onclick = function() {
                var checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
                checkboxes.forEach((checkbox) => {
                    checkbox.checked = this.checked;
                    const row = checkbox.closest("tr");
                    if (checkbox.checked) {
                        row.classList.add("table-active");
                    } else {
                        row.classList.remove("table-active");
                    }
                });
                const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
                const removeActions = document.getElementById("remove-actions");
                if (removeActions) removeActions.classList.toggle("d-none", checkedCount === 0);
            };
        }

        // WhatsApp link generation
        var generateBtn = document.getElementById("generate-whatsapp-link");
        if (generateBtn) {
            generateBtn.addEventListener("click", function() {
                const phoneNumber = document.getElementById("whatsapp-phone")?.value;
                const email = document.getElementById("whatsapp-email")?.value;
                const password = document.getElementById("whatsapp-password")?.value;

                if (!phoneNumber || !phoneNumber.match(/^\+[1-9]\d{1,14}$/)) {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Please enter a valid phone number in E.164 format (e.g., +1234567890)"
                    });
                    return;
                }

                const message = encodeURIComponent(`Your account credentials to the school portal:\nUsername: ${email}\nPassword: ${password}\nPlease change your password after logging in.`);
                const whatsappLink = `https://wa.me/${phoneNumber}?text=${message}`;

                const linkContainer = document.getElementById("whatsapp-link-container");
                const linkElement = document.getElementById("whatsapp-link");
                const previewElement = document.getElementById("whatsapp-message-preview");

                if (linkElement) linkElement.href = whatsappLink;
                if (previewElement) previewElement.textContent = decodeURIComponent(message);
                if (linkContainer) linkContainer.classList.remove("d-none");

                Swal.fire({
                    icon: "success",
                    title: "WhatsApp link generated!",
                    showConfirmButton: false,
                    timer: 2000
                });
            });
        }

        // Add User Form
        var addUserForm = document.getElementById("add-user-form");
        if (addUserForm) {
            addUserForm.addEventListener("submit", function(e) {
                e.preventDefault();
                var errorMsg = document.getElementById("alert-error-msg");
                if (!errorMsg) return;

                errorMsg.classList.remove("d-none");
                setTimeout(() => errorMsg.classList.add("d-none"), 3000);

                if (!addNameField || !addNameField.value) {
                    errorMsg.innerHTML = "Please enter a name";
                    return;
                }
                if (!addEmailField || !addEmailField.value) {
                    errorMsg.innerHTML = "Please enter an email";
                    return;
                }
                if (!addRoleField || !addRoleField.selectedOptions.length) {
                    errorMsg.innerHTML = "Please select at least one role";
                    return;
                }
                if (!addPasswordField || !addPasswordField.value) {
                    errorMsg.innerHTML = "Please enter a password";
                    return;
                }
                if (addPasswordField.value !== addPasswordConfirmField.value) {
                    errorMsg.innerHTML = "Passwords do not match";
                    return;
                }

                if (!ensureAxios()) return;

                var roles = (typeof Choices !== 'undefined' && addRoleVal)
                    ? addRoleVal.getValue(true)
                    : Array.from(addRoleField.selectedOptions).map(option => option.value);

                axios.post('/users', {
                    name: addNameField.value,
                    email: addEmailField.value,
                    roles: roles,
                    password: addPasswordField.value,
                    password_confirmation: addPasswordConfirmField.value,
                    _token: document.querySelector('meta[name="csrf-token"]').content
                }).then(function(response) {
                    userList.add({
                        id: response.data.user.id,
                        name: response.data.user.name,
                        email: response.data.user.email,
                        role: response.data.user.roles.join(','),
                        datereg: new Date().toISOString().slice(0, 10)
                    });
                    userList.reIndex();
                    userList.update();
                    Swal.fire({
                        icon: "success",
                        title: "User added successfully!",
                        showConfirmButton: false,
                        timer: 2000
                    });
                    var addModal = bootstrap.Modal.getInstance(document.getElementById("showModal"));
                    if (addModal) addModal.hide();

                    // Show WhatsApp modal
                    var whatsappModalEl = document.getElementById("whatsappModal");
                    if (whatsappModalEl) {
                        var userIdField = document.getElementById("whatsapp-user-id");
                        var emailField = document.getElementById("whatsapp-email");
                        var passwordField = document.getElementById("whatsapp-password");
                        var phoneField = document.getElementById("whatsapp-phone");

                        if (userIdField) userIdField.value = response.data.user.id;
                        if (emailField) emailField.value = response.data.user.email;
                        if (passwordField) passwordField.value = response.data.user.password || "";
                        if (phoneField) phoneField.value = response.data.user.phone_number || "";

                        var whatsappModal = new bootstrap.Modal(whatsappModalEl);
                        whatsappModal.show();
                    }
                }).catch(function(error) {
                    var message = error.response?.data?.message || "Error adding user";
                    if (error.response?.status === 422) {
                        message = Object.values(error.response.data.errors || {}).flat().join(", ");
                    }
                    errorMsg.innerHTML = message;
                });
            });
        }

        // Edit User Form
        var editUserForm = document.getElementById("edit-user-form");
        if (editUserForm) {
            editUserForm.addEventListener("submit", function(e) {
                e.preventDefault();
                const updateBtn = document.getElementById("update-btn");
                if (updateBtn) updateBtn.disabled = true;

                const errorMsg = document.getElementById("alert-error-msg");
                if (errorMsg) {
                    errorMsg.classList.add("d-none");
                }

                if (!editNameField || !editNameField.value) {
                    if (errorMsg) {
                        errorMsg.innerHTML = "Please enter a name";
                        errorMsg.classList.remove("d-none");
                        setTimeout(() => errorMsg.classList.add("d-none"), 3000);
                    }
                    if (updateBtn) updateBtn.disabled = false;
                    return;
                }
                if (!editEmailField || !editEmailField.value) {
                    if (errorMsg) {
                        errorMsg.innerHTML = "Please enter an email";
                        errorMsg.classList.remove("d-none");
                        setTimeout(() => errorMsg.classList.add("d-none"), 3000);
                    }
                    if (updateBtn) updateBtn.disabled = false;
                    return;
                }
                if (!editRoleField || !editRoleField.selectedOptions.length) {
                    if (errorMsg) {
                        errorMsg.innerHTML = "Please select at least one role";
                        errorMsg.classList.remove("d-none");
                        setTimeout(() => errorMsg.classList.add("d-none"), 3000);
                    }
                    if (updateBtn) updateBtn.disabled = false;
                    return;
                }
                if (editPasswordField && editPasswordField.value && editPasswordField.value !== editPasswordConfirmField.value) {
                    if (errorMsg) {
                        errorMsg.innerHTML = "Passwords do not match";
                        errorMsg.classList.remove("d-none");
                        setTimeout(() => errorMsg.classList.add("d-none"), 3000);
                    }
                    if (updateBtn) updateBtn.disabled = false;
                    return;
                }

                if (!ensureAxios()) {
                    if (errorMsg) {
                        errorMsg.innerHTML = "Axios library is missing";
                        errorMsg.classList.remove("d-none");
                        setTimeout(() => errorMsg.classList.add("d-none"), 3000);
                    }
                    if (updateBtn) updateBtn.disabled = false;
                    return;
                }

                const roles = (typeof Choices !== 'undefined' && editRoleVal)
                    ? editRoleVal.getValue(true)
                    : Array.from(editRoleField.selectedOptions).map(option => option.value);

                const data = {
                    name: editNameField.value,
                    email: editEmailField.value,
                    roles: roles,
                    _token: document.querySelector('meta[name="csrf-token"]')?.content || '',
                };
                if (editPasswordField && editPasswordField.value) {
                    data.password = editPasswordField.value;
                    data.password_confirmation = editPasswordConfirmField.value;
                }

                axios.put(`/users/${editIdField.value}`, data, {
                    headers: { 'X-CSRF-TOKEN': data._token }
                })
                .then(function(response) {
                    userList.items.forEach(item => {
                        if (item.values().id === response.data.user.id) {
                            item.values({
                                id: response.data.user.id,
                                name: response.data.user.name,
                                email: response.data.user.email,
                                role: response.data.user.roles.join(','),
                                datereg: item.values().datereg
                            });
                        }
                    });
                    userList.reIndex();
                    userList.update();
                    Swal.fire({
                        icon: "success",
                        title: "User updated successfully!",
                        showConfirmButton: false,
                        timer: 2000
                    });
                    var editModal = bootstrap.Modal.getInstance(document.getElementById("editModal"));
                    if (editModal) editModal.hide();

                    if (data.password) {
                        var whatsappModalEl = document.getElementById("whatsappModal");
                        if (whatsappModalEl) {
                            var userIdField = document.getElementById("whatsapp-user-id");
                            var emailField = document.getElementById("whatsapp-email");
                            var passwordField = document.getElementById("whatsapp-password");
                            var phoneField = document.getElementById("whatsapp-phone");

                            if (userIdField) userIdField.value = response.data.user.id;
                            if (emailField) emailField.value = response.data.user.email;
                            if (passwordField) passwordField.value = response.data.user.password || data.password;
                            if (phoneField) phoneField.value = response.data.user.phone_number || "";

                            var whatsappModal = new bootstrap.Modal(whatsappModalEl);
                            whatsappModal.show();
                        }
                    }
                    if (updateBtn) updateBtn.disabled = false;
                })
                .catch(function(error) {
                    let message = error.response?.data?.message || "Error updating user";
                    if (error.response?.status === 422) {
                        message = Object.values(error.response.data.errors || {}).flat().join(", ");
                    }
                    if (errorMsg) {
                        errorMsg.innerHTML = message;
                        errorMsg.classList.remove("d-none");
                        setTimeout(() => errorMsg.classList.add("d-none"), 3000);
                    }
                    if (updateBtn) updateBtn.disabled = false;
                });
            });
        }

        // Modal event handlers
        var showModal = document.getElementById("showModal");
        if (showModal) {
            showModal.addEventListener("show.bs.modal", function() {
                var addModalLabel = document.getElementById("addModalLabel");
                var addBtn = document.getElementById("add-btn");
                if (addModalLabel) addModalLabel.innerHTML = "Add User";
                if (addBtn) addBtn.innerHTML = "Add User";
            });
            showModal.addEventListener("hidden.bs.modal", clearAddFields);
        }

        var editModal = document.getElementById("editModal");
        if (editModal) {
            editModal.addEventListener("show.bs.modal", function() {
                var editModalLabel = document.getElementById("editModalLabel");
                var updateBtn = document.getElementById("update-btn");
                if (editModalLabel) editModalLabel.innerHTML = "Edit User";
                if (updateBtn) updateBtn.innerHTML = "Update";
            });
            editModal.addEventListener("hidden.bs.modal", clearEditFields);
        }

        var whatsappModal = document.getElementById("whatsappModal");
        if (whatsappModal) {
            whatsappModal.addEventListener("hidden.bs.modal", clearWhatsAppFields);
        }

        // Chart
        var ctx = document.getElementById("usersByRoleChart")?.getContext("2d");
        if (ctx) {
            new Chart(ctx, {
                type: "bar",
                data: {
                    labels: @json(array_keys($role_counts)),
                    datasets: [{
                        label: "Users by Role",
                        data: @json(array_values($role_counts)),
                        backgroundColor: ["#4e73df","#1cc88a","#36b9cc","#f6c23e","#e74a3b"],
                        borderColor: ["#4e73df","#1cc88a","#36b9cc","#f6c23e","#e74a3b"],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: { y: { beginAtZero: true } },
                    plugins: { legend: { position: "top" } }
                }
            });
        }

        // Student password reset
        $(document).on('click', '.reset-student-pwd-btn', function() {
            const userId = $(this).data('user-id');
            const userName = $(this).data('user-name');

            Swal.fire({
                title: 'Reset Password?',
                html: `Reset password for <strong>${userName}</strong>?<br><br>A new random password will be generated.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Reset Password',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processing...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    fetch(`/users/reset-single-password/${userId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Password Reset!',
                                html: `
                                    New password for <strong>${data.user.name}</strong>:<br>
                                    <code style="font-size: 24px; background: #f0f0f0; padding: 10px; display: inline-block; margin: 10px 0; letter-spacing: 2px;">${data.password}</code><br>
                                    <button class="btn btn-info mt-2" onclick="navigator.clipboard.writeText('${data.password}')">Copy Password</button>
                                `,
                                icon: 'success',
                                showConfirmButton: true
                            });
                        } else {
                            Swal.fire('Error', data.message || 'Failed to reset password', 'error');
                        }
                    })
                    .catch(() => {
                        Swal.fire('Error', 'Network error occurred', 'error');
                    });
                }
            });
        });

        // Single Student Add Modal Logic
        const addStudentModalEl = document.getElementById('addStudentModal');
        const credentialsModalEl = document.getElementById('setStudentCredentialsModal');

        if (addStudentModalEl && credentialsModalEl) {
            const addStudentModal = new bootstrap.Modal(addStudentModalEl);
            const credentialsModal = new bootstrap.Modal(credentialsModalEl);
            const searchInput = document.getElementById('student-search');
            const studentSelect = document.getElementById('student-select');
            const proceedBtn = document.getElementById('proceed-to-credentials');
            const errorEl = document.getElementById('student-select-error');
            let selectedStudent = null;

            addStudentModalEl.addEventListener('show.bs.modal', () => loadStudentsForSingle(''));

            function loadStudentsForSingle(search = '') {
                if (proceedBtn) proceedBtn.disabled = true;
                if (errorEl) errorEl.classList.add('d-none');

                let url = '{{ route("get.students") }}?limit=500&has_account=no';
                if (search.trim()) url += `&search=${encodeURIComponent(search.trim())}`;

                fetch(url)
                    .then(r => r.json())
                    .then(data => {
                        if (!data.success) {
                            if (errorEl) {
                                errorEl.textContent = data.message || 'Failed to load students';
                                errorEl.classList.remove('d-none');
                            }
                            return;
                        }
                        if (studentSelect) {
                            studentSelect.innerHTML = '<option value="">Choose a student...</option>';
                            data.students.forEach(s => {
                                const opt = document.createElement('option');
                                opt.value = s.id;
                                opt.textContent = `${s.name} (${s.admissionNo})`;
                                opt.dataset.name = s.name;
                                opt.dataset.email = s.email || '';
                                opt.dataset.admission = s.admissionNo || '';
                                studentSelect.appendChild(opt);
                            });
                            if (proceedBtn) proceedBtn.disabled = data.students.length === 0;
                        }
                    })
                    .catch(() => {
                        if (errorEl) {
                            errorEl.textContent = 'Network error – please try again';
                            errorEl.classList.remove('d-none');
                        }
                    });
            }

            if (searchInput) {
                searchInput.addEventListener('input', debounce(e => {
                    loadStudentsForSingle(e.target.value.trim());
                }, 350));
            }

            if (studentSelect) {
                studentSelect.addEventListener('change', function() {
                    const opt = this.options[this.selectedIndex];
                    if (!opt.value) {
                        if (proceedBtn) proceedBtn.disabled = true;
                        selectedStudent = null;
                        return;
                    }
                    selectedStudent = {
                        id: opt.value,
                        name: opt.dataset.name,
                        email: opt.dataset.email,
                        admissionNo: opt.dataset.admission,
                    };
                    if (proceedBtn) proceedBtn.disabled = false;
                });
            }

            if (proceedBtn) {
                proceedBtn.addEventListener('click', () => {
                    if (!selectedStudent) return;
                    var studentIdField = document.getElementById('student-id-field');
                    var studentNameField = document.getElementById('student-name-field');
                    var studentUserEmail = document.getElementById('student-user-email');
                    var studentUsername = document.getElementById('student-username');

                    if (studentIdField) studentIdField.value = selectedStudent.id;
                    if (studentNameField) studentNameField.value = selectedStudent.name;
                    if (studentUserEmail) studentUserEmail.value = selectedStudent.email;
                    if (studentUsername) studentUsername.value = (selectedStudent.admissionNo || '').replace(/[\/\\]/g, '_');

                    addStudentModal.hide();
                    setTimeout(() => credentialsModal.show(), 300);
                });
            }

            var generateTempPassword = document.getElementById('generate-temp-password');
            if (generateTempPassword) {
                generateTempPassword.addEventListener('click', () => {
                    const temp = Math.random().toString(36).slice(-8) + Math.random().toString(36).slice(-4).toUpperCase();
                    var studentPassword = document.getElementById('student-password');
                    var studentPasswordConfirm = document.getElementById('student-password_confirmation');
                    if (studentPassword) studentPassword.value = temp;
                    if (studentPasswordConfirm) studentPasswordConfirm.value = temp;
                });
            }

            var addStudentCredentialsForm = document.getElementById('add-student-credentials-form');
            if (addStudentCredentialsForm) {
                addStudentCredentialsForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    formData.append('_token', '{{ csrf_token() }}');

                    fetch('{{ route("users.store-student") }}', {
                        method: 'POST',
                        body: formData,
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Success!', data.message, 'success');
                            credentialsModal.hide();
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            const err = document.getElementById('student-credentials-error');
                            if (err) {
                                err.innerHTML = data.errors ? Object.values(data.errors).flat().join('<br>') : (data.message || 'Error occurred');
                                err.classList.remove('d-none');
                            }
                        }
                    })
                    .catch(() => {
                        Swal.fire('Error', 'Network error – please try again', 'error');
                    });
                });
            }

            window.resetStudentCredentialsModal = function() {
                var studentIdField = document.getElementById('student-id-field');
                var studentNameField = document.getElementById('student-name-field');
                var studentUserEmail = document.getElementById('student-user-email');
                var studentUsername = document.getElementById('student-username');
                var studentPassword = document.getElementById('student-password');
                var studentPasswordConfirm = document.getElementById('student-password_confirmation');
                var studentCredentialsError = document.getElementById('student-credentials-error');

                if (studentIdField) studentIdField.value = '';
                if (studentNameField) studentNameField.value = '';
                if (studentUserEmail) studentUserEmail.value = '';
                if (studentUsername) studentUsername.value = '';
                if (studentPassword) studentPassword.value = '';
                if (studentPasswordConfirm) studentPasswordConfirm.value = '';
                if (studentCredentialsError) studentCredentialsError.classList.add('d-none');
            };

            if (credentialsModalEl) {
                credentialsModalEl.addEventListener('hidden.bs.modal', window.resetStudentCredentialsModal);
            }
        }

        function debounce(func, wait) {
            let timeout;
            return (...args) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }
    });
})();
</script>

@endsection
