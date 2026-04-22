@extends('layouts.master')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <div id="selectionAlert" class="alert alert-info alert-dismissible fade show"
                 role="alert" style="display:none; position:fixed; top:0; left:0; right:0; z-index:1050; margin:0 auto; max-width:90%;">
                <span id="selectionAlertText">No selections made.</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <div class="row" style="margin-top:60px;">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ $pagetitle }}</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item active">Student Mock Reports</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @if (session('status') || session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') ?? session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div id="studentList">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xxl-3 col-sm-6">
                                        <select class="form-control" id="idclass" name="schoolclassid">
                                            <option value="ALL">Select Class</option>
                                            @foreach ($schoolclasses as $class)
                                                <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->arm }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <select class="form-control" id="idsession" name="sessionid">
                                            <option value="ALL">Select Session</option>
                                            @foreach ($schoolsessions as $session)
                                                <option value="{{ $session->id }}">{{ $session->session }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6" id="termSelectContainer" style="display:none;">
                                        <select class="form-control" id="idterm" name="termid">
                                            <option value="ALL">Select Term</option>
                                            <option value="1">First Term</option>
                                            <option value="2">Second Term</option>
                                            <option value="3">Third Term</option>
                                        </select>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <div class="search-box">
                                            <input type="text" class="form-control search" id="searchInput" placeholder="Search students...">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6 d-flex gap-2">
                                        <button type="button" class="btn btn-secondary w-50" id="searchBtn"
                                                style="display:none;" onclick="filterData()">
                                            <i class="bi bi-search me-1"></i> Search
                                        </button>
                                        <button type="button" class="btn btn-primary w-50" id="printAllBtn"
                                                style="display:none;" onclick="printAllResults()">
                                            <i class="bi bi-printer me-1"></i> Print Selected Results
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
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    Students
                                    <span class="badge bg-dark-subtle text-dark ms-1" id="studentcount">
                                        {{ $allstudents ? $allstudents->total() : 0 }}
                                    </span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-centered align-middle table-nowrap mb-0" id="studentListTable">
                                        <thead class="table-active">
                                            <tr>
                                                <th>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="checkAll">
                                                        <label class="form-check-label" for="checkAll"></label>
                                                    </div>
                                                </th>
                                                <th>Admission No</th>
                                                <th>Picture</th>
                                                <th>Last Name</th>
                                                <th>First Name</th>
                                                <th>Other Name</th>
                                                <th>Gender</th>
                                                <th>Class</th>
                                                <th>Arm</th>
                                                <th>Session</th>
                                            </tr>
                                        </thead>
                                        <tbody id="studentTableBody">
                                            @include('studentmockreports.partials.student_rows')
                                        </tbody>
                                    </table>
                                    <div class="d-flex justify-content-end mt-3" id="pagination-container">
                                        {{ $allstudents ? $allstudents->links('pagination::bootstrap-5') : '' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Image Modal --}}
                <div id="imageViewModal" class="modal fade" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Student Image</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img id="enlargedImage" src="" alt="Student Image" class="img-fluid"
                                     onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}';">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Column Selection Modal --}}
                <div class="modal fade" id="columnSelectionModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Select Columns for Mock PDF Report</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div id="columnSelectionLoader" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-2">Loading column options...</p>
                                </div>
                                <div id="columnSelectionForm" style="display:none;">
                                    <div class="card mb-3">
                                        <div class="card-header"><h6 class="mb-0">Student Information</h6></div>
                                        <div class="card-body"><div class="row" id="studentInfoColumns"></div></div>
                                    </div>
                                    <div class="card mb-3">
                                        <div class="card-header"><h6 class="mb-0">Scores & Metrics</h6></div>
                                        <div class="card-body"><div class="row" id="scoreColumns"></div></div>
                                    </div>
                                    <div class="card">
                                        <div class="card-header"><h6 class="mb-0">Other</h6></div>
                                        <div class="card-body"><div class="row" id="otherColumns"></div></div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="saveColumnSelection" disabled>
                                    Apply & Generate PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function updateSelectionAlert() {
        const cls     = document.getElementById('idclass');
        const ses     = document.getElementById('idsession');
        const trm     = document.getElementById('idterm');
        const checked = document.querySelectorAll('tbody input[name="chk_child"]:checked');
        const alert   = document.getElementById('selectionAlert');
        const text    = document.getElementById('selectionAlertText');

        if (cls.value !== 'ALL' && ses.value !== 'ALL') {
            const parts = [];
            if (cls.value !== 'ALL') parts.push('Class: ' + cls.options[cls.selectedIndex].text);
            if (ses.value !== 'ALL') parts.push('Session: ' + ses.options[ses.selectedIndex].text);
            if (trm.value !== 'ALL') parts.push('Term: ' + trm.options[trm.selectedIndex].text);
            parts.push('Selected: ' + checked.length);
            alert.style.display = 'block';
            text.innerText = parts.join(' | ');
        } else {
            alert.style.display = 'none';
        }
    }

    function updateSearchButtonVisibility() {
        const cls    = document.getElementById('idclass');
        const ses    = document.getElementById('idsession');
        const btn    = document.getElementById('searchBtn');
        const termC  = document.getElementById('termSelectContainer');
        const printB = document.getElementById('printAllBtn');
        btn.style.display   = (cls.value !== 'ALL' && ses.value !== 'ALL') ? 'block' : 'none';
        termC.style.display = 'none';
        printB.style.display = 'none';
        updateSelectionAlert();
    }

    function updateTermSelectVisibility() {
        const count = parseInt(document.getElementById('studentcount').innerText);
        document.getElementById('termSelectContainer').style.display = count > 0 ? 'block' : 'none';
        document.getElementById('printAllBtn').style.display = 'none';
        updateSelectionAlert();
    }

    function updatePrintButtonVisibility() {
        const trm     = document.getElementById('idterm');
        const checked = document.querySelectorAll('tbody input[name="chk_child"]:checked');
        document.getElementById('printAllBtn').style.display =
            (trm.value !== 'ALL' && checked.length > 0) ? 'block' : 'none';
        updateSelectionAlert();
    }

    function filterData() {
        const cls    = document.getElementById('idclass').value;
        const ses    = document.getElementById('idsession').value;
        const trm    = document.getElementById('idterm').value;
        const search = document.getElementById('searchInput').value.trim();

        if (cls === 'ALL' || ses === 'ALL') {
            Swal.fire({ icon: 'warning', title: 'Missing Selection', text: 'Please select a valid class and session.' });
            return;
        }

        const tbody = document.getElementById('studentTableBody');
        tbody.innerHTML = '<tr><td colspan="10" class="text-center">Loading...</td></tr>';

        axios.get('{{ route("studentmockreports.index") }}', {
            params: { search, schoolclassid: cls, sessionid: ses, termid: trm },
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' }
        }).then(res => {
            tbody.innerHTML = res.data.tableBody || '<tr><td colspan="10" class="text-center">No students found.</td></tr>';
            document.getElementById('pagination-container').innerHTML = res.data.pagination || '';
            document.getElementById('studentcount').innerText = res.data.studentCount || '0';
            setupPaginationLinks();
            setupCheckboxListeners();
            updateTermSelectVisibility();
            updatePrintButtonVisibility();
        }).catch(err => {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Error loading data.</td></tr>';
            Swal.fire({ icon: 'error', title: 'Error', text: err.response?.data?.message || 'Failed to fetch data.' });
        });
    }

    function printAllResults() {
        const cls     = document.getElementById('idclass').value;
        const ses     = document.getElementById('idsession').value;
        const trm     = document.getElementById('idterm').value;
        const checked = document.querySelectorAll('tbody input[name="chk_child"]:checked');
        const ids     = Array.from(checked).map(cb => cb.value);

        if (cls === 'ALL' || ses === 'ALL' || trm === 'ALL') {
            Swal.fire({ icon: 'warning', title: 'Missing Selection', text: 'Please select class, session, and term.' });
            return;
        }
        if (ids.length === 0) {
            Swal.fire({ icon: 'warning', title: 'No Students Selected', text: 'Please select at least one student.' });
            return;
        }

        const columnModal = new bootstrap.Modal(document.getElementById('columnSelectionModal'));
        columnModal.show();
        loadColumnOptions(cls, ses, trm, ids);
    }

    function loadColumnOptions(classId, sessionId, termId, studentIds) {
        const loader  = document.getElementById('columnSelectionLoader');
        const form    = document.getElementById('columnSelectionForm');
        const saveBtn = document.getElementById('saveColumnSelection');

        loader.style.display = 'block';
        form.style.display   = 'none';
        saveBtn.disabled     = true;

        window.currentPrintParams = { classId, sessionId, termId, studentIds };

        fetch('{{ route("studentmockreports.column-options") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ schoolclassid: classId, sessionid: sessionId, termid: termId })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                populateColumnOptions(data.columns);
                loader.style.display = 'none';
                form.style.display   = 'block';
                saveBtn.disabled     = false;
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to load column options.' });
            }
        })
        .catch(() => {
            Swal.fire({ icon: 'error', title: 'Network Error', text: 'Failed to load column options.' });
        });
    }

    function populateColumnOptions(columns) {
        const sections = {
            'studentInfoColumns': columns.student_info,
            'scoreColumns':       columns.scores,
            'otherColumns':       columns.other,
        };

        Object.entries(sections).forEach(([containerId, cols]) => {
            const container = document.getElementById(containerId);
            container.innerHTML = '';
            if (!cols) return;
            Object.entries(cols).forEach(([key, config]) => {
                const div = document.createElement('div');
                div.className = 'col-md-4 col-sm-6 mb-2';
                div.innerHTML = `
                    <div class="form-check">
                        <input class="form-check-input column-checkbox" type="checkbox"
                               id="col_${key}" data-column="${key}" ${config.default ? 'checked' : ''}>
                        <label class="form-check-label" for="col_${key}">${config.label}</label>
                    </div>`;
                container.appendChild(div);
            });
        });
    }

    document.getElementById('saveColumnSelection').addEventListener('click', function () {
        const selectedColumns = Array.from(document.querySelectorAll('.column-checkbox:checked'))
            .map(cb => cb.dataset.column);

        if (selectedColumns.length === 0) {
            Swal.fire({ icon: 'warning', title: 'No Columns Selected', text: 'Please select at least one column.' });
            return;
        }

        const params      = window.currentPrintParams;
        const columnModal = bootstrap.Modal.getInstance(document.getElementById('columnSelectionModal'));
        columnModal.hide();

        Swal.fire({
            title: 'Generating Mock PDF',
            html: `<p>Students: <strong>${params.studentIds.length}</strong></p><p>Please wait...</p>`,
            icon: 'info', showConfirmButton: false, allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("studentmockreports.exportClassMockResultsPdf") }}';
        form.target = '_blank';

        const addInput = (name, value) => {
            const i = document.createElement('input');
            i.type = 'hidden'; i.name = name; i.value = value;
            form.appendChild(i);
        };

        addInput('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        addInput('schoolclassid', params.classId);
        addInput('sessionid', params.sessionId);
        addInput('termid', params.termId);

        params.studentIds.forEach((id, i) => addInput(`studentIds[${i}]`, id));
        selectedColumns.forEach((col, i) => addInput(`selectedColumns[${i}]`, col));

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);

        setTimeout(() => Swal.close(), 2000);
    });

    function setupPaginationLinks() {
        document.querySelectorAll('#pagination-container a').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                if (this.href && !this.classList.contains('disabled')) loadPage(this.href);
            });
        });
    }

    function loadPage(url) {
        const tbody = document.getElementById('studentTableBody');
        tbody.innerHTML = '<tr><td colspan="10" class="text-center">Loading...</td></tr>';
        axios.get(url, {
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' }
        }).then(res => {
            tbody.innerHTML = res.data.tableBody || '<tr><td colspan="10" class="text-center">No students found.</td></tr>';
            document.getElementById('pagination-container').innerHTML = res.data.pagination || '';
            document.getElementById('studentcount').innerText = res.data.studentCount || '0';
            setupPaginationLinks();
            setupCheckboxListeners();
            updateTermSelectVisibility();
            updatePrintButtonVisibility();
        }).catch(() => {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Error loading data.</td></tr>';
        });
    }

    function setupCheckboxListeners() {
        const checkAll  = document.getElementById('checkAll');
        const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');

        if (checkAll) {
            checkAll.addEventListener('change', function () {
                checkboxes.forEach(cb => {
                    cb.checked = this.checked;
                    cb.closest('tr').classList.toggle('table-active', this.checked);
                });
                updatePrintButtonVisibility();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function () {
                this.closest('tr').classList.toggle('table-active', this.checked);
                const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
                const total        = document.querySelectorAll('tbody input[name="chk_child"]').length;
                document.getElementById('checkAll').checked = checkedCount === total && total > 0;
                updatePrintButtonVisibility();
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        setupCheckboxListeners();

        const cls = document.getElementById('idclass');
        const ses = document.getElementById('idsession');
        const trm = document.getElementById('idterm');

        cls.addEventListener('change', function () {
            updateSearchButtonVisibility();
            trm.value = 'ALL';
            document.getElementById('studentTableBody').innerHTML =
                '<tr><td colspan="10" class="text-center">Select class and session to view students.</td></tr>';
            document.getElementById('pagination-container').innerHTML = '';
            document.getElementById('studentcount').innerText = '0';
        });

        ses.addEventListener('change', function () {
            updateSearchButtonVisibility();
            trm.value = 'ALL';
            document.getElementById('studentTableBody').innerHTML =
                '<tr><td colspan="10" class="text-center">Select class and session to view students.</td></tr>';
            document.getElementById('pagination-container').innerHTML = '';
            document.getElementById('studentcount').innerText = '0';
        });

        trm.addEventListener('change', function () {
            updatePrintButtonVisibility();
            if (this.value !== 'ALL') filterData();
        });

        const modal = document.getElementById('imageViewModal');
        if (modal) {
            modal.addEventListener('show.bs.modal', function (e) {
                const img = e.relatedTarget?.getAttribute('data-image');
                modal.querySelector('#enlargedImage').src = img || '{{ asset('storage/student_avatars/unnamed.jpg') }}';
            });
        }
    });
</script>
@endsection
