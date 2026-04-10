{{-- resources/views/subjectoperation/index.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            {{-- Page Title --}}
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 fw-semibold">
                            <i class="ri-user-star-line me-2 text-primary"></i>Subject Registration
                        </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('subjects.index') }}">Student Management</a></li>
                                <li class="breadcrumb-item active">Subject Registration</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong><i class="ri-error-warning-line me-1"></i> Error!</strong> There were some problems with your input.
                    <ul class="mb-0 mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-checkbox-circle-line me-1"></i> {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div id="subjectList">

                {{-- ── Class & Session Filter ── --}}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header border-bottom-0 pb-0" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);">
                                <h6 class="text-white mb-0 py-1"><i class="ri-filter-3-line me-2"></i>Filter Students</h6>
                            </div>
                            <div class="card-body pt-3">
                                <div class="row g-3 align-items-end">
                                    <div class="col-xxl-4 col-sm-6">
                                        <label class="form-label fw-medium small text-muted">Class</label>
                                        <select class="form-select" id="idclass">
                                            <option value="ALL">— Select Class —</option>
                                            @foreach ($schoolclass as $class)
                                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                                    {{ $class->schoolclass }} {{ $class->schoolarm }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-4 col-sm-6">
                                        <label class="form-label fw-medium small text-muted">Session</label>
                                        <select class="form-select" id="idsession">
                                            <option value="ALL">— Select Session —</option>
                                            @foreach ($schoolsessions as $session)
                                                <option value="{{ $session->id }}" {{ request('session_id') == $session->id ? 'selected' : '' }}>
                                                    {{ $session->session }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-2 col-sm-6">
                                        <button type="button" class="btn btn-primary w-100" onclick="filterData()">
                                            <i class="ri-search-line me-1"></i> Search
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Subject Teachers Card ── --}}
                <div class="row" id="subjectTeachersCard">
                    <div class="col-lg-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header d-flex align-items-center flex-wrap gap-2">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-book-open-line me-2 text-primary"></i>Subject Teachers
                                        <span class="badge bg-primary-subtle text-primary ms-1 rounded-pill" id="subjectTeacherCount">0</span>
                                    </h5>
                                </div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAllSubjects()">
                                        <i class="ri-checkbox-multiple-line me-1"></i>Select All
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="deselectAllSubjects()">
                                        <i class="ri-checkbox-blank-line me-1"></i>Deselect All
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info border-0 mb-3 d-flex align-items-center gap-2" style="background:#eff6ff;">
                                    <i class="ri-information-line fs-5 text-primary flex-shrink-0"></i>
                                    <span class="small">Select the subjects you want to register or unregister students for.</span>
                                </div>
                                <div id="subjectTeachersContainer">
                                    @foreach ($schoolterms as $term)
                                        @php $termSubjects = $subjectTeachers ? $subjectTeachers->where('termid', $term->id) : collect(); @endphp
                                        @if ($termSubjects->isNotEmpty())
                                            <div class="term-group mb-4">
                                                <div class="d-flex align-items-center mb-2 gap-2">
                                                    <span class="badge text-white px-3 py-2 rounded-pill" style="background:linear-gradient(135deg,#667eea,#764ba2);">
                                                        <i class="ri-calendar-2-line me-1"></i>{{ $term->term }}
                                                    </span>
                                                </div>
                                                <div class="row g-2">
                                                    @foreach ($termSubjects as $teacher)
                                                        <div class="col-xl-3 col-md-4 col-sm-6">
                                                            <div class="subject-check-card p-2 border rounded-3 d-flex align-items-center gap-2 bg-light bg-opacity-50"
                                                                 style="cursor:pointer;" onclick="toggleSubjectCard(this)">
                                                                <input class="form-check-input subject-checkbox flex-shrink-0 mt-0"
                                                                    type="checkbox"
                                                                    id="subject-{{ $teacher->subjectclassid }}"
                                                                    data-subjectclassid="{{ $teacher->subjectclassid }}"
                                                                    data-staffid="{{ $teacher->userid }}"
                                                                    data-termid="{{ $teacher->termid }}" checked>
                                                                <label class="form-check-label small lh-sm mb-0 w-100"
                                                                       for="subject-{{ $teacher->subjectclassid }}" style="cursor:pointer;">
                                                                    <span class="fw-semibold d-block text-truncate">{{ $teacher->subjectname }}</span>
                                                                    <span class="text-muted" style="font-size:0.75rem;">{{ $teacher->staffname }}</span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Student Filters ── --}}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="row g-3 align-items-end">
                                    <div class="col-xxl-4">
                                        <label class="form-label fw-medium small text-muted">Search</label>
                                        <div class="search-box">
                                            <input type="text" class="form-control search" placeholder="Search students…">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <label class="form-label fw-medium small text-muted">Gender</label>
                                        <select class="form-select" id="idgender">
                                            <option value="ALL">All Genders</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <label class="form-label fw-medium small text-muted">Admission No</label>
                                        <select class="form-select" id="idadmission">
                                            <option value="ALL">All Admission Nos</option>
                                        </select>
                                    </div>
                                    <div class="col-xxl-2 col-sm-6">
                                        <button type="button" class="btn btn-secondary w-100" onclick="filterData()">
                                            <i class="ri-filter-3-line me-1"></i> Filter
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Students Table ── --}}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header d-flex align-items-center flex-wrap gap-2">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-group-line me-2 text-primary"></i>Students
                                        <span class="badge bg-dark-subtle text-dark ms-1 rounded-pill" id="studentcount">{{ $students ? $students->total() : 0 }}</span>
                                    </h5>
                                </div>
                                <div class="d-flex gap-2 flex-wrap align-items-center">
                                    <button type="button" class="btn btn-success d-none" id="register-selected-btn"
                                        onclick="registerSelectedStudentsBatch()" aria-label="Register selected students">
                                        <i class="ri-user-add-line me-1"></i>Register Selected
                                    </button>
                                    <button type="button" class="btn btn-danger d-none" id="unregister-selected-btn"
                                        onclick="openUnregisterModal()" aria-label="Unregister selected students">
                                        <i class="ri-user-unfollow-line me-1"></i>Unregister Selected
                                    </button>
                                    <div class="spinner-border text-primary d-none" id="register-loading-spinner" role="status" style="width:1.5rem;height:1.5rem;">
                                        <span class="visually-hidden">Loading…</span>
                                    </div>
                                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#registeredClassesModal">
                                        <i class="ri-eye-line me-1"></i> View Registered
                                    </button>
                                    <button type="button" class="btn btn-warning" onclick="openArchivedModal()">
                                        <i class="ri-archive-line me-1"></i> Unregistered History
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle table-nowrap mb-0" id="subjectListTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="40" class="text-center">
                                                    <input class="form-check-input" type="checkbox" id="checkAll">
                                                </th>
                                                <th width="50">S/N</th>
                                                <th>Admission No</th>
                                                <th>Student Name</th>
                                                <th>Class</th>
                                                <th>Gender</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="studentTableBody">
                                            @include('subjectoperation.partials.student_rows')
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-end p-3" id="pagination-container">
                                    {{ $students ? $students->links('pagination::bootstrap-5') : '' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- /subjectList --}}
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- MODAL: Snapshot Name — shown BEFORE unregistration        --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="snapshotNameModal" tabindex="-1" aria-labelledby="snapshotNameModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-0 pb-0" style="background:linear-gradient(135deg,#f5576c 0%,#f093fb 100%);">
                <div class="py-1">
                    <h5 class="modal-title text-white fw-semibold" id="snapshotNameModalLabel">
                        <i class="ri-archive-line me-2"></i>Name this Unregistration
                    </h5>
                    <p class="text-white-50 small mb-0">Give this snapshot a name so you can find it later.</p>
                </div>
                <button type="button" class="btn-close btn-close-white ms-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex gap-2 flex-wrap mb-4" id="snapshotSummaryPills">
                    <span class="badge rounded-pill bg-danger-subtle text-danger px-3 py-2" id="snapshotStudentCount"></span>
                    <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis px-3 py-2" id="snapshotSubjectCount"></span>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="snapshotNameInput">
                        Snapshot Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="snapshotNameInput"
                        placeholder="e.g. Term 2 Corrections — June 2025"
                        maxlength="191" autocomplete="off">
                    <div class="invalid-feedback" id="snapshotNameError">Please enter a snapshot name.</div>
                    <div class="form-text">
                        <i class="ri-lightbulb-line me-1 text-warning"></i>
                        A descriptive name helps staff identify this batch when restoring it later.
                    </div>
                </div>
                <div class="mb-1">
                    <label class="form-label fw-semibold" for="snapshotNotesInput">
                        Notes <span class="text-muted fw-normal">(optional)</span>
                    </label>
                    <textarea class="form-control" id="snapshotNotesInput" rows="3"
                        placeholder="Reason for unregistration or any extra context…"
                        maxlength="1000"></textarea>
                    <div class="form-text text-end">
                        <span id="snapshotNotesCount">0</span>/1000
                    </div>
                </div>
                <div class="alert alert-warning d-flex gap-2 align-items-start mt-3 mb-0 py-2">
                    <i class="ri-error-warning-line fs-5 flex-shrink-0"></i>
                    <div class="small">
                        All existing scores for these students in the selected subjects will be saved to the snapshot and can be fully restored later.
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger px-4" id="confirmUnregisterBtn" onclick="proceedUnregister()">
                    <i class="ri-user-unfollow-line me-1"></i> Unregister & Save Snapshot
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- MODAL: Registered Classes                                  --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="registeredClassesModal" tabindex="-1" aria-labelledby="registeredClassesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header border-0 px-4 py-3" style="background:linear-gradient(90deg,#1e3a5f 0%,#2563eb 60%,#7c3aed 100%);">
                <h5 class="modal-title text-white fw-semibold d-flex align-items-center gap-2" id="registeredClassesModalLabel">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="opacity:.85;flex-shrink:0;">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                    </svg>
                    Registered Classes Overview
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" style="background:#f4f6fb;" id="registeredClassesContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" style="width:3rem;height:3rem;" role="status"></div>
                    <p class="mt-3 text-muted mb-0">Loading registration data…</p>
                </div>
            </div>
            <div class="modal-footer border-0 bg-white px-4">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- MODAL: Unregistered History (snapshot list)               --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="archivedModal" tabindex="-1" aria-labelledby="archivedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0" style="background:linear-gradient(135deg,#f093fb 0%,#f5576c 100%);">
                <h5 class="modal-title text-white" id="archivedModalLabel">
                    <i class="ri-archive-line me-2"></i>Unregistered History
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 border-bottom bg-light d-flex align-items-center flex-wrap gap-2">
                    <div class="flex-grow-1">
                        <input type="text" class="form-control form-control-sm" id="archiveSearch"
                            placeholder="Search snapshot name or subject…" style="max-width:300px;">
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <select class="form-select form-select-sm" id="archiveTermFilter" style="width:auto;">
                            <option value="">All Terms</option>
                            @foreach($schoolterms as $term)
                                <option value="{{ $term->id }}">{{ $term->term }}</option>
                            @endforeach
                        </select>
                        <select class="form-select form-select-sm" id="archivePerPage" style="width:auto;">
                            <option value="20">20 per page</option>
                            <option value="50" selected>50 per page</option>
                            <option value="100">100 per page</option>
                            <option value="150">150 per page</option>
                        </select>
                        <button class="btn btn-sm btn-outline-secondary" onclick="loadArchivedPage(1)">
                            <i class="ri-refresh-line"></i> Refresh
                        </button>
                        <button class="btn btn-sm btn-success d-none" id="restoreSelectedBtn" onclick="restoreSelected()">
                            <i class="ri-refresh-line me-1"></i> Restore Selected
                        </button>
                        <button class="btn btn-sm btn-danger d-none" id="deleteSelectedBtn" onclick="permanentDeleteSelected()">
                            <i class="ri-delete-bin-line me-1"></i> Delete Selected
                        </button>
                        <div class="spinner-border spinner-border-sm text-warning d-none" id="archiveSpinner" role="status"></div>
                    </div>
                </div>
                <div class="p-3" id="snapshotCardsContainer">
                    <div class="text-center text-muted py-4">
                        Select a class and session first, then open this panel.
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top" id="archivePaginationWrap">
                    <small class="text-muted" id="archiveMeta"></small>
                    <div id="archivePagination" class="d-flex gap-1"></div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <small class="text-muted me-auto">
                    <i class="ri-information-line me-1"></i>
                    Click a snapshot to view student details. Restored records are fully re-registered with original scores.
                </small>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- MODAL: Snapshot Detail                                     --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="snapshotDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0" style="background:linear-gradient(135deg,#4facfe 0%,#00f2fe 100%);">
                <div>
                    <h5 class="modal-title text-white fw-semibold" id="snapshotDetailTitle">Snapshot Detail</h5>
                    <p class="text-white-50 small mb-0" id="snapshotDetailSubtitle"></p>
                </div>
                <button type="button" class="btn-close btn-close-white ms-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="snapshotNotesBanner" class="alert alert-info d-flex gap-2 align-items-start m-3 mb-0 d-none">
                    <i class="ri-sticky-note-line fs-5 flex-shrink-0"></i>
                    <div id="snapshotNotesText" class="small"></div>
                </div>
                <div class="px-3 pt-3 pb-2 border-bottom">
                    <div class="mb-2">
                        <div class="input-group input-group-sm" style="max-width:340px;">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="ri-search-line text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0 ps-0"
                                id="detailSearchInput"
                                placeholder="Search by name or admission no…"
                                oninput="filterDetailRows(this.value)">
                            <button class="btn btn-outline-secondary" type="button"
                                onclick="document.getElementById('detailSearchInput').value='';filterDetailRows('');"
                                title="Clear search">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button class="btn btn-sm btn-success" id="detailRestoreAllBtn" onclick="restoreEntireSnapshot()">
                            <i class="ri-refresh-line me-1"></i> Restore All
                        </button>
                        <button class="btn btn-sm btn-success d-none" id="detailRestoreSelectedBtn" onclick="restoreDetailSelected()">
                            <i class="ri-refresh-line me-1"></i> Restore Selected
                        </button>
                        <button class="btn btn-sm btn-danger d-none" id="detailDeleteSelectedBtn" onclick="deleteDetailSelected()">
                            <i class="ri-delete-bin-line me-1"></i> Delete Selected
                        </button>
                        <div class="spinner-border spinner-border-sm text-primary d-none ms-1" id="detailSpinner" role="status"></div>
                        <span class="text-muted small ms-auto" id="detailStudentMeta"></span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr id="snapshotDetailHeaderRow">
                                <th style="width:36px;">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" id="detailCheckAll">
                                    </div>
                                </th>
                                <th>Student</th>
                                <th>Adm. No</th>
                                <th>Gender</th>
                            </tr>
                        </thead>
                        <tbody id="snapshotDetailBody">
                            <tr><td colspan="10" class="text-center text-muted py-4">Loading…</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Image View Modal --}}
<div id="imageViewModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Student Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="enlargedImage" src="" alt="Student Image" class="img-fluid"
                    onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'">
            </div>
        </div>
    </div>
</div>

<style>
/* ── Subject check cards ── */
.subject-check-card { transition: border-color .15s, background .15s; }
.subject-check-card:hover { background: #eff6ff !important; border-color: #93c5fd !important; }
.subject-check-card:has(.subject-checkbox:checked) { background: #eff6ff !important; border-color: #3b82f6 !important; }

/* ── Registered Classes modal ── */
.rc-term-header {
    padding: 12px 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    background: linear-gradient(90deg, #1e3a5f 0%, #2563eb 60%, #7c3aed 100%);
}
.rc-term-header h6 {
    color: #fff;
    font-size: 14px;
    font-weight: 500;
    margin: 0 0 2px;
    line-height: 1.3;
}
.rc-term-header h6 .rc-session {
    opacity: 0.7;
    font-weight: 400;
}
.rc-term-header small {
    color: rgba(255,255,255,0.65);
    font-size: 12px;
}
.rc-term-badges { display: flex; gap: 8px; flex-shrink: 0; }
.rc-badge-blue   { font-size: 11px; padding: 3px 12px; border-radius: 20px; font-weight: 500; background: #E6F1FB; color: #0C447C; white-space: nowrap; }
.rc-badge-purple { font-size: 11px; padding: 3px 12px; border-radius: 20px; font-weight: 500; background: #EEEDFE; color: #3C3489; white-space: nowrap; }

/* Subject cells grid */
.reg-subjects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    border-top: 1px solid #e9ecef;
}
.reg-subject-cell {
    padding: 12px 14px;
    border-right: 1px solid #e9ecef;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    gap: 10px;
    align-items: flex-start;
}
.reg-num-circle {
    width: 26px; height: 26px;
    border-radius: 50%;
    background: #EEEDFE; color: #3C3489;
    font-size: 11px; font-weight: 600;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; margin-top: 1px;
}
.reg-subject-name {
    font-size: 13px; font-weight: 600; color: #1e293b; line-height: 1.3;
}
.reg-subject-teacher {
    font-size: 11px; color: #64748b; margin-top: 3px;
    display: flex; align-items: center; gap: 4px; line-height: 1.4;
}
.reg-subject-teacher svg { flex-shrink: 0; opacity: 0.55; }
.reg-subject-teacher.unassigned { font-style: italic; opacity: 0.6; }
.reg-student-pill {
    font-size: 10px; background: #EAF3DE; color: #27500A;
    padding: 2px 8px; border-radius: 20px;
    display: inline-flex; align-items: center; gap: 3px; margin-top: 5px;
}
.reg-student-pill svg { flex-shrink: 0; }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
window.ROUTES     = { batchRegister: '{{ route("subjectregistration.batch") }}',
unregister: '{{ route("subjects.destroy") }}',
getRegistered: '{{ route("subjects.registered-classes") }}',
getArchived: '{{ route("subjectoperation.archived") }}',
getSnapshot: '{{ route("subjectoperation.snapshot.detail") }}',
restore: '{{ route("subjectoperation.restore") }}',
permanentDelete: '{{ route("subjectoperation.archive.batch-delete") }}',
index: '{{ route("subjects.index") }}' };
window.CSRF       = '{{ csrf_token() }}';
window.AVATAR_URL = '{{ asset("storage") }}';
</script>
{{-- <script src="{{ asset('js/subjectoperation.js') }}"></script> --}}

@endsection
