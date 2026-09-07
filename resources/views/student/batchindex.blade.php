@extends('layouts.master')

@section('content')
<style>
    #batch-loader, #update-class-loader {
        backdrop-filter: blur(2px);
        font-size: 1.1rem;
        color: #333;
    }
    #batch-loader .spinner-border, #update-class-loader .spinner-border {
        width: 2rem;
        height: 2rem;
    }
    #deleteRecordModal .spinner-border {
        width: 1.5rem;
        height: 1.5rem;
    }
</style>
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Batch Uploads</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Student Management</a></li>
                                <li class="breadcrumb-item active">Batch Uploads</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End page title -->

            <!-- Batch Status Chart -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Batch Upload Status</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="batchStatusChart" height="100"></canvas>
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

            <div id="batchList">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xxl-4">
                                        <div class="search-box">
                                            <input type="text" class="form-control search" placeholder="Search batches">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <select class="form-control" id="idStatus" data-choices data-choices-search-false>
                                            <option value="all">Select Status</option>
                                            <option value="Processing">Processing</option>
                                            <option value="Partial">Partial</option>
                                            <option value="Success">Success</option>
                                            <option value="Failed">Failed</option>
                                        </select>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <select class="form-control" id="idClass" data-choices data-choices-search-false>
                                            <option value="all">Select Class</option>
                                            @foreach ($batch->pluck('schoolclass')->unique() as $class)
                                                <option value="{{ $class }}">{{ $class }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-2 col-sm-6">
                                        <button type="button" class="btn btn-secondary w-100" onclick="filterData();"><i class="bi bi-funnel align-baseline me-1"></i> Filters</button>
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
                                    <h5 class="card-title mb-0">Batch Uploads <span class="badge bg-dark-subtle text-dark ms-1">{{ $batch->count() }}</span></h5>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="d-flex flex-wrap align-items-start gap-2">
                                        @can('Create student-bulk-upload')
                                            <button class="btn btn-subtle-danger d-none" id="remove-actions" onclick="deleteMultiple()"><i class="ri-delete-bin-2-line"></i></button>
                                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#generateTemplateModal"><i class="bi bi-file-earmark-spreadsheet align-baseline me-1"></i> Generate Template</button>
                                            <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addBatchModal"><i class="bi bi-plus-circle align-baseline me-1"></i> New Batch Upload</button>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-centered align-middle table-nowrap mb-0" id="batchListTable">
                                        <thead class="table-active">
                                            <tr>
                                                <th><div class="form-check"><input class="form-check-input" type="checkbox" value="option" id="checkAll"><label class="form-check-label" for="checkAll"></label></div></th>
                                                <th class="sort cursor-pointer" data-sort="sn">SN</th>
                                                <th class="sort cursor-pointer" data-sort="title">Batch Title</th>
                                                <th class="sort cursor-pointer" data-sort="schoolclass">School Class</th>
                                                <th class="sort cursor-pointer" data-sort="arm">School Arm</th>
                                                <th class="sort cursor-pointer" data-sort="term">Term</th>
                                                <th class="sort cursor-pointer" data-sort="session">Session</th>
                                                <th class="sort cursor-pointer" data-sort="status">Status</th>
                                                <th class="sort cursor-pointer" data-sort="upload_date">Upload Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list form-check-all">
                                            @php $i = 0 @endphp
                                            @forelse ($batch as $sc)
                                                <tr>
                                                    <td class="id" data-id="{{ $sc->id }}">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="chk_child">
                                                            <label class="form-check-label"></label>
                                                        </div>
                                                    </td>
                                                    <td class="sn">{{ ++$i }}</td>
                                                    <td class="title">{{ $sc->title }}</td>
                                                    <td class="schoolclass">{{ $sc->schoolclass }}</td>
                                                    <td class="arm">{{ $sc->arm }}</td>
                                                    <td class="term">{{ $sc->term }}</td>
                                                    <td class="session">{{ $sc->session }}</td>
                                                    <td class="status" data-status="{{ $sc->status }}">
                                                        @php
                                                            $statusClass = match ($sc->status) {
                                                                'Success'    => 'success',
                                                                'Processing' => 'warning',
                                                                'Partial'    => 'info',
                                                                default      => 'danger',
                                                            };
                                                        @endphp
                                                        <span class="badge bg-{{ $statusClass }}">{{ $sc->status }}</span>
                                                    </td>
                                                    <td class="upload_date">{{ Carbon\Carbon::parse($sc->upload_date)->format('Y-m-d') }}</td>
                                                    <td>
                                                        <ul class="d-flex gap-2 list-unstyled mb-0">
                                                            @if (in_array($sc->status, ['Failed', 'Partial']))
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-warning btn-icon btn-sm view-errors-btn" data-id="{{ $sc->id }}" title="View Import Errors"><i class="ph-warning"></i></a>
                                                                </li>
                                                            @endif
                                                            @can('Create student-bulk-upload')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-primary btn-icon btn-sm update-item-btn" data-id="{{ $sc->id }}" data-schoolclass="{{ $sc->schoolclass }}" data-arm="{{ $sc->arm }}" data-schoolclassid="{{ $sc->schoolclassid }}" data-armid="{{ $sc->armid }}" data-classcategoryid="{{ $sc->classcategoryid ?? '' }}"><i class="ph-pencil"></i></a>
                                                                </li>
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn" data-id="{{ $sc->id }}"><i class="ph-trash"></i></a>
                                                                </li>
                                                            @endcan
                                                        </ul>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="10" class="noresult" style="display: block;">No results found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="row mt-3 align-items-center" id="pagination-element">
                                    <div class="col-sm">
                                        <div class="text-muted text-center text-sm-start">
                                            Showing <span class="fw-semibold">{{ $batch->count() }}</span> of <span class="fw-semibold">{{ $batch->count() }}</span> Results
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Generate Template Modal -->
            <div id="generateTemplateModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Generate Batch Upload Template</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body position-relative">
                            <div id="template-loader" class="d-none position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(255,255,255,0.8); z-index: 1000;">
                                <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
                                <span class="ms-2">Generating template...</span>
                            </div>

                            <p class="text-muted small">
                                Choose the class, term, and session this template is for. Those three
                                values are locked into the spreadsheet automatically — whoever fills it
                                in only needs to enter student details.
                            </p>

                            <div class="mb-3">
                                <label for="tpl_schoolclassid" class="form-label">School Class & Arm</label>
                                <select id="tpl_schoolclassid" class="form-control" data-choices data-choices-search-true required>
                                    <option value="">Select Class</option>
                                    @foreach ($schoolclasses as $sc)
                                        <option value="{{ $sc->id }}">{{ $sc->schoolclass }} - {{ $sc->arm }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="tpl_termid" class="form-label">Term</label>
                                <select id="tpl_termid" class="form-control" data-choices data-choices-search-true required>
                                    <option value="">Select Term</option>
                                    @foreach ($schoolterms as $sc)
                                        <option value="{{ $sc->id }}">{{ $sc->term }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="tpl_sessionid" class="form-label">Session</label>
                                <select id="tpl_sessionid" class="form-control" data-choices data-choices-search-true required>
                                    <option value="">Select Session</option>
                                    @foreach ($schoolsessions as $sc)
                                        <option value="{{ $sc->id }}">{{ $sc->session }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="tpl_rows" class="form-label">Number of blank rows</label>
                                <input type="number" id="tpl_rows" class="form-control" value="30" min="1" max="500">
                            </div>
                            <div class="alert alert-danger d-none" id="template-alert-error-msg"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="generate-template-btn">
                                <i class="bi bi-download me-1"></i> Generate &amp; Download
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Batch Modal -->
            <div id="addBatchModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 id="addModalLabel" class="modal-title">Add Batch Upload</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form class="tablelist-form" autocomplete="off" id="add-batch-form" action="{{ route('student.bulkuploadsave') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body position-relative">
                                <div id="batch-loader" class="d-none position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(255, 255, 255, 0.8); z-index: 1000;">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <span class="ms-2">Processing Batch...</span>
                                </div>
                                <div class="mb-3">
                                    <label for="title" class="form-label">Batch Title</label>
                                    <input type="text" id="title" name="title" class="form-control" placeholder="Enter batch title" required>
                                </div>
                                <div class="mb-3">
                                    <label for="schoolclassid" class="form-label">School Class & Arm</label>
                                    <select id="schoolclassid" name="schoolclassid" class="form-control" data-choices data-choices-search-true required>
                                        <option value="">Select Class</option>
                                        @foreach ($schoolclasses as $sc)
                                            <option value="{{ $sc->id }}">{{ $sc->schoolclass }} - {{ $sc->arm }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="termid" class="form-label">Term</label>
                                    <select id="termid" name="termid" class="form-control" data-choices data-choices-search-true required>
                                        <option value="">Select Term</option>
                                        @foreach ($schoolterms as $sc)
                                            <option value="{{ $sc->id }}">{{ $sc->term }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="sessionid" class="form-label">Session</label>
                                    <select id="sessionid" name="sessionid" class="form-control" data-choices data-choices-search-true required>
                                        <option value="">Select Session</option>
                                        @foreach ($schoolsessions as $sc)
                                            <option value="{{ $sc->id }}">{{ $sc->session }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="filesheet" class="form-label">Upload File</label>
                                    <input type="file" id="filesheet" name="filesheet" class="form-control" accept=".xlsx,.xls,.csv" required>
                                </div>
                                <div class="alert alert-danger d-none" id="alert-error-msg"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="add-btn">Add Batch</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Batch Import Progress Modal -->
            <div id="importProgressModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Importing Students</h5>
                        </div>
                        <div class="modal-body text-center">
                            <p class="text-muted mb-3" id="importProgressMessage">Starting import...</p>
                            <div class="progress mb-2" style="height: 24px;">
                                <div id="importProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
                            </div>
                            <p class="small text-muted" id="importProgressCount">0 / 0 rows</p>
                            <div id="importResultIcon" class="mt-3 d-none"></div>
                        </div>
                        <div class="modal-footer d-none" id="importProgressFooter">
                            <button type="button" class="btn btn-outline-warning d-none" id="importViewErrorsBtn">View Errors</button>
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="window.location.reload()">Close &amp; Refresh</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Import Errors Modal -->
            <div id="importErrorsModal" class="modal fade" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="importErrorsTitle">Import Errors</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="importErrorsLoading" class="text-center py-3">
                                <div class="spinner-border text-primary" role="status"></div>
                            </div>
                            <div id="importErrorsList" class="d-none"></div>
                            <div id="importErrorsEmpty" class="d-none text-muted text-center py-3">No detailed errors were recorded for this batch.</div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Update Class Modal -->
            <div id="updateClassModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Update Class</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form class="tablelist-form" autocomplete="off" id="update-class-form" action="{{ route('student.updateclass') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body position-relative">
                                <div id="update-class-loader" class="d-none position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(255, 255, 255, 0.8); z-index: 1000;">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <span class="ms-2">Updating Class...</span>
                                </div>
                                <div class="mb-3">
                                    <label for="update_batch_id" class="form-label">Batch ID</label>
                                    <input type="text" id="update_batch_id" name="batch_id" class="form-control" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="update_schoolclass" class="form-label">School Class Name</label>
                                    <input type="text" id="update_schoolclass" name="schoolclass" class="form-control" placeholder="Enter school class name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="update_arm" class="form-label">Arm Name</label>
                                    <input type="text" id="update_arm" name="arm" class="form-control" placeholder="Enter arm name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="update_schoolclassid" class="form-label">School Class ID</label>
                                    <input type="text" id="update_schoolclassid" name="schoolclassid" class="form-control" placeholder="Enter school class ID" required>
                                </div>
                                <div class="mb-3">
                                    <label for="update_armid" class="form-label">Arm ID</label>
                                    <input type="text" id="update_armid" name="armid" class="form-control" placeholder="Enter arm ID" required>
                                </div>
                                <div class="mb-3">
                                    <label for="update_classcategoryid" class="form-label">Class Category ID</label>
                                    <input type="text" id="update_classcategoryid" name="classcategoryid" class="form-control" placeholder="Enter class category ID" required>
                                </div>
                                <div class="alert alert-danger d-none" id="update-alert-error-msg"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="update-btn">Update Class</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Batch Modal -->
            <div id="deleteRecordModal" class="modal fade zoomIn" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="btn-close" id="deleteRecord-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-md-5">
                            <div class="text-center">
                                <div class="text-danger">
                                    <i class="bi bi-trash display-4"></i>
                                </div>
                                <div class="mt-4">
                                    <h3 class="mb-2">Are you sure?</h3>
                                    <p class="text-muted fs-lg mx-3 mb-0">Are you sure you want to remove this batch?</p>
                                </div>
                            </div>
                            <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                                <button type="button" class="btn w-sm btn-light btn-hover" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn w-sm btn-danger btn-hover" id="delete-record">
                                    <span id="delete-btn-text">Yes, Delete It!</span>
                                    <span id="delete-btn-loader" class="d-none">
                                        <span class="spinner-border spinner-border-sm me-1" role="status"></span>Deleting...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentDeleteId = null;
    let currentUpdateId = null;
    let importPollTimer = null;
    let lastProgressKey = null;

    document.addEventListener('DOMContentLoaded', function () {
        const deleteButtons = document.querySelectorAll('.remove-item-btn');
        const updateButtons = document.querySelectorAll('.update-item-btn');
        const deleteRecordModal = document.getElementById('deleteRecordModal');
        const updateClassModal = document.getElementById('updateClassModal');
        const deleteBtn = document.getElementById('delete-record');
        const updateForm = document.getElementById('update-class-form');
        const addBatchForm = document.getElementById('add-batch-form');

        // ===== Delete batch =====
        deleteButtons.forEach(button => {
            button.addEventListener('click', function () {
                currentDeleteId = this.getAttribute('data-id');
                if (deleteRecordModal) new bootstrap.Modal(deleteRecordModal).show();
            });
        });

        // ===== Update batch class =====
        updateButtons.forEach(button => {
            button.addEventListener('click', function () {
                currentUpdateId = this.getAttribute('data-id');
                document.getElementById('update_batch_id').value = currentUpdateId;
                document.getElementById('update_schoolclass').value = this.getAttribute('data-schoolclass');
                document.getElementById('update_arm').value = this.getAttribute('data-arm');
                document.getElementById('update_schoolclassid').value = this.getAttribute('data-schoolclassid');
                document.getElementById('update_armid').value = this.getAttribute('data-armid');
                document.getElementById('update_classcategoryid').value = this.getAttribute('data-classcategoryid') || '';
                if (updateClassModal) new bootstrap.Modal(updateClassModal).show();
            });
        });

        if (deleteBtn) {
            deleteBtn.addEventListener('click', handleDeleteConfirmation);
        }

        function handleDeleteConfirmation() {
            if (!currentDeleteId) return;

            const deleteBtnText = document.getElementById('delete-btn-text');
            const deleteBtnLoader = document.getElementById('delete-btn-loader');
            deleteBtnText.classList.add('d-none');
            deleteBtnLoader.classList.remove('d-none');
            deleteBtn.disabled = true;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            axios.delete(`/student/deletestudentbatch?studentbatchid=${currentDeleteId}`, {
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' }
            })
            .then(function (response) {
                const modal = bootstrap.Modal.getInstance(deleteRecordModal);
                if (modal) modal.hide();
                Swal.fire({ icon: 'success', title: 'Success', text: response.data.message || 'Batch deleted successfully!', showConfirmButton: false, timer: 1500 })
                    .then(() => window.location.reload());
            })
            .catch(function (error) {
                deleteBtnText.classList.remove('d-none');
                deleteBtnLoader.classList.add('d-none');
                deleteBtn.disabled = false;
                const modal = bootstrap.Modal.getInstance(deleteRecordModal);
                if (modal) modal.hide();
                Swal.fire({ icon: error.response?.status === 404 ? 'warning' : 'error', title: 'Error', text: error.response?.data?.message || 'Error deleting batch', showConfirmButton: true });
            });
        }

        // ===== Update class form =====
        if (updateForm) {
            updateForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const updateBtnText = document.getElementById('update-btn');
                const updateLoader = document.getElementById('update-class-loader');
                const errorMsg = document.getElementById('update-alert-error-msg');

                updateBtnText.disabled = true;
                updateLoader.classList.remove('d-none');

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                const formData = new FormData(updateForm);

                axios.post(updateForm.action, formData, { headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'multipart/form-data' } })
                .then(function (response) {
                    const modal = bootstrap.Modal.getInstance(updateClassModal);
                    if (modal) modal.hide();
                    Swal.fire({ icon: 'success', title: 'Success', text: response.data.message || 'Class updated successfully!', showConfirmButton: false, timer: 1500 })
                        .then(() => window.location.reload());
                })
                .catch(function (error) {
                    updateBtnText.disabled = false;
                    updateLoader.classList.add('d-none');
                    errorMsg.textContent = error.response?.data?.message || 'Error updating class';
                    errorMsg.classList.remove('d-none');
                });
            });
        }

        // ===== Generate template =====
        const generateBtn = document.getElementById('generate-template-btn');
        if (generateBtn) {
            generateBtn.addEventListener('click', function () {
                const schoolclassid = document.getElementById('tpl_schoolclassid').value;
                const termid = document.getElementById('tpl_termid').value;
                const sessionid = document.getElementById('tpl_sessionid').value;
                const rows = document.getElementById('tpl_rows').value || 30;
                const errorMsg = document.getElementById('template-alert-error-msg');
                const loader = document.getElementById('template-loader');

                errorMsg.classList.add('d-none');

                if (!schoolclassid || !termid || !sessionid) {
                    errorMsg.textContent = 'Please select class, term, and session.';
                    errorMsg.classList.remove('d-none');
                    return;
                }

                loader.classList.remove('d-none');
                generateBtn.disabled = true;

                axios({
                    method: 'GET',
                    url: '{{ route("student.batch.generateTemplate") }}',
                    params: { schoolclassid, termid, sessionid, rows },
                    responseType: 'blob',
                    timeout: 60000
                })
                .then(function (response) {
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;

                    let filename = 'student-batch-template.xlsx';
                    const contentDisposition = response.headers['content-disposition'];
                    if (contentDisposition) {
                        const match = contentDisposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                        if (match && match[1]) filename = match[1].replace(/['"]/g, '');
                    }

                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(url);

                    const modal = bootstrap.Modal.getInstance(document.getElementById('generateTemplateModal'));
                    if (modal) modal.hide();
                })
                .catch(async function (error) {
                    let message = 'Failed to generate template.';
                    if (error.response?.data instanceof Blob) {
                        try {
                            const text = await error.response.data.text();
                            message = JSON.parse(text).message || message;
                        } catch (e) {}
                    } else if (error.response?.data?.message) {
                        message = error.response.data.message;
                    }
                    errorMsg.textContent = message;
                    errorMsg.classList.remove('d-none');
                })
                .finally(function () {
                    loader.classList.add('d-none');
                    generateBtn.disabled = false;
                });
            });
        }

        // ===== Add batch (queued import) =====
        if (addBatchForm) {
            addBatchForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const loader = document.getElementById('batch-loader');
                const errorMsg = document.getElementById('alert-error-msg');
                const addBtn = document.getElementById('add-btn');

                errorMsg.classList.add('d-none');
                loader.classList.remove('d-none');
                addBtn.disabled = true;

                const formData = new FormData(addBatchForm);
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                axios.post(addBatchForm.action, formData, {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'multipart/form-data'
                    }
                })
                .then(function (response) {
                    loader.classList.add('d-none');
                    addBtn.disabled = false;

                    if (response.data.success) {
                        const addModal = bootstrap.Modal.getInstance(document.getElementById('addBatchModal'));
                        if (addModal) addModal.hide();

                        addBatchForm.reset();
                        startProgressPolling(response.data.progress_key, response.data.batch_id);
                    } else {
                        errorMsg.textContent = response.data.message || 'Failed to queue import.';
                        errorMsg.classList.remove('d-none');
                    }
                })
                .catch(function (error) {
                    loader.classList.add('d-none');
                    addBtn.disabled = false;
                    errorMsg.textContent = error.response?.data?.message || 'Failed to queue import.';
                    errorMsg.classList.remove('d-none');
                });
            });
        }

        function startProgressPolling(progressKey, batchId) {
            lastProgressKey = progressKey;

            const modal = new bootstrap.Modal(document.getElementById('importProgressModal'));
            const bar = document.getElementById('importProgressBar');
            const message = document.getElementById('importProgressMessage');
            const count = document.getElementById('importProgressCount');
            const resultIcon = document.getElementById('importResultIcon');
            const footer = document.getElementById('importProgressFooter');
            const viewErrorsBtn = document.getElementById('importViewErrorsBtn');

            bar.style.width = '0%';
            bar.textContent = '0%';
            bar.className = 'progress-bar progress-bar-striped progress-bar-animated';
            message.textContent = 'Starting import...';
            count.textContent = '0 / 0 rows';
            resultIcon.classList.add('d-none');
            resultIcon.innerHTML = '';
            footer.classList.add('d-none');
            viewErrorsBtn.classList.add('d-none');

            modal.show();

            if (importPollTimer) clearInterval(importPollTimer);

            importPollTimer = setInterval(function () {
                axios.get('{{ route("student.batch.importProgress") }}', { params: { progress_key: progressKey } })
                .then(function (response) {
                    const p = response.data.progress;
                    const pct = p.total > 0 ? Math.round((p.progress / p.total) * 100) : 0;

                    bar.style.width = pct + '%';
                    bar.textContent = pct + '%';
                    count.textContent = `${p.progress} / ${p.total} rows`;
                    message.textContent = p.message || '';

                    if (p.status === 'complete') {
                        clearInterval(importPollTimer);
                        bar.classList.remove('progress-bar-striped', 'progress-bar-animated');
                        bar.classList.add('bg-success');
                        resultIcon.classList.remove('d-none');
                        resultIcon.innerHTML = '<i class="bi bi-check-circle-fill text-success display-4"></i>';
                        footer.classList.remove('d-none');
                    } else if (p.status === 'partial') {
                        clearInterval(importPollTimer);
                        bar.classList.remove('progress-bar-striped', 'progress-bar-animated');
                        bar.classList.add('bg-warning');
                        resultIcon.classList.remove('d-none');
                        resultIcon.innerHTML = '<i class="bi bi-exclamation-triangle-fill text-warning display-4"></i>';
                        footer.classList.remove('d-none');
                        viewErrorsBtn.classList.remove('d-none');
                        viewErrorsBtn.onclick = () => showImportErrors(batchId);
                    } else if (p.status === 'failed') {
                        clearInterval(importPollTimer);
                        bar.classList.remove('progress-bar-striped', 'progress-bar-animated');
                        bar.classList.add('bg-danger');
                        resultIcon.classList.remove('d-none');
                        resultIcon.innerHTML = '<i class="bi bi-x-circle-fill text-danger display-4"></i>';
                        footer.classList.remove('d-none');
                        viewErrorsBtn.classList.remove('d-none');
                        viewErrorsBtn.onclick = () => showImportErrors(batchId);
                    }
                })
                .catch(function () {
                    // transient network hiccup — keep polling
                });
            }, 1500);
        }

        // ===== View import errors (from table row button or progress modal) =====
        document.querySelectorAll('.view-errors-btn').forEach(button => {
            button.addEventListener('click', function () {
                showImportErrors(this.getAttribute('data-id'));
            });
        });

        function showImportErrors(batchId) {
            const modalEl = document.getElementById('importErrorsModal');
            const modal = new bootstrap.Modal(modalEl);
            const loading = document.getElementById('importErrorsLoading');
            const list = document.getElementById('importErrorsList');
            const empty = document.getElementById('importErrorsEmpty');
            const title = document.getElementById('importErrorsTitle');

            loading.classList.remove('d-none');
            list.classList.add('d-none');
            empty.classList.add('d-none');
            list.innerHTML = '';
            title.textContent = 'Import Errors';

            modal.show();

            axios.get(`/student/batch/${batchId}/errors`)
                .then(function (response) {
                    loading.classList.add('d-none');
                    const data = response.data;
                    title.textContent = `Import Errors — ${data.title || 'Batch'}`;

                    if (!data.errors || data.errors.length === 0) {
                        empty.classList.remove('d-none');
                        return;
                    }

                    const html = data.errors.map(function (err) {
                        const rowLabel = err.row ? `Row ${err.row}` : 'General error';
                        const messages = Array.isArray(err.errors) ? err.errors.join('<br>') : err.errors;
                        return `
                            <div class="alert alert-warning mb-2">
                                <strong>${rowLabel}</strong>
                                ${err.attribute ? ` — <em>${err.attribute}</em>` : ''}
                                <div class="small mt-1">${messages}</div>
                            </div>
                        `;
                    }).join('');

                    list.innerHTML = html;
                    list.classList.remove('d-none');
                })
                .catch(function () {
                    loading.classList.add('d-none');
                    empty.textContent = 'Failed to load error details.';
                    empty.classList.remove('d-none');
                });
        }
    });
</script>
@endsection