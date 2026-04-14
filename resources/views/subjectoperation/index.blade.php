{{-- resources/views/subjectoperation/index.blade.php --}}
@extends('layouts.master')

@section('content')
@php $school = \App\Models\SchoolInformation::getActiveSchool(); @endphp

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Subject Registration</h4>
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
                <div class="alert alert-danger">
                    <strong>Error!</strong> There were some problems with your input.<br>
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
                                                <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->schoolarm }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-4 col-sm-6">
                                        <label class="form-label fw-medium small text-muted">Session</label>
                                        <select class="form-select" id="idsession">
                                            <option value="ALL">— Select Session —</option>
                                            @foreach ($schoolsessions as $session)
                                                <option value="{{ $session->id }}">{{ $session->session }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-2 col-sm-6">
                                        <button type="button" class="btn btn-primary w-100" onclick="filterData();">
                                            <i class="ri-search-line me-1"></i> Search
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Subject Teachers Card (ENHANCED UI) ── --}}
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
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAllSubjects();">
                                        <i class="ri-checkbox-multiple-line me-1"></i>Select All
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="deselectAllSubjects();">
                                        <i class="ri-checkbox-blank-line me-1"></i>Deselect All
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info border-0 mb-3 d-flex align-items-center gap-2" style="background:#eff6ff;">
                                    <i class="ri-information-line fs-5 text-primary flex-shrink-0"></i>
                                    <span class="small">Select the subjects you want to register or unregister students for. Subjects are grouped by term.</span>
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
                                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2">
                                                        {{ $termSubjects->count() }} subject{{ $termSubjects->count() !== 1 ? 's' : '' }}
                                                    </span>
                                                </div>
                                                <div class="row g-2">
                                                    @foreach ($termSubjects as $teacher)
                                                        <div class="col-xl-3 col-md-4 col-sm-6">
                                                            <div class="subject-check-card p-2 border rounded-3 d-flex align-items-center gap-2 bg-light bg-opacity-50"
                                                                 style="cursor:pointer;"
                                                                 data-subject-name="{{ $teacher->subjectname }}"
                                                                 data-teacher-name="{{ $teacher->staffname }}"
                                                                 data-term-id="{{ $teacher->termid }}"
                                                                 onclick="toggleSubjectCard(this)">
                                                                <input class="form-check-input subject-checkbox flex-shrink-0 mt-0"
                                                                       type="checkbox"
                                                                       id="subject-{{ $teacher->subjectclassid }}"
                                                                       data-subjectclassid="{{ $teacher->subjectclassid }}"
                                                                       data-staffid="{{ $teacher->userid }}"
                                                                       data-termid="{{ $teacher->termid }}"
                                                                       data-subject-name="{{ $teacher->subjectname }}"
                                                                       data-teacher-name="{{ $teacher->staffname }}"
                                                                       checked>
                                                                <label class="form-check-label small lh-sm mb-0 w-100"
                                                                       for="subject-{{ $teacher->subjectclassid }}"
                                                                       style="cursor:pointer;">
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
                                    @if(!$subjectTeachers || $subjectTeachers->isEmpty())
                                        <div class="text-center text-muted py-4">
                                            <i class="ri-book-2-line ri-2x mb-2 d-block"></i>
                                            Select a class and session to view subjects.
                                        </div>
                                    @endif
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
                                        <label class="form-label fw-medium small text-muted">Search Students</label>
                                        <div class="search-box">
                                   <!-- NEW -->
                                    <input type="text" class="form-control search" placeholder="Search students"
                                        oninput="filterData()">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <label class="form-label fw-medium small text-muted">Gender</label>
                                        <select class="form-select" id="idgender">
                                            <option value="ALL">Select Gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <label class="form-label fw-medium small text-muted">Admission No</label>
                                        <select class="form-select" id="idadmission">
                                            <option value="ALL">Select Admission No</option>
                                        </select>
                                    </div>
                                    <div class="col-xxl-2 col-sm-6">
                                        <button type="button" class="btn btn-secondary w-100" onclick="filterData();">
                                            <i class="bi bi-funnel align-baseline me-1"></i> Filters
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{--
                ===========================================================================
                DROP-IN REPLACEMENT  –  Students Table card + Selection Tray
                Replace the entire "Students Table" row block and the #selection-tray div
                inside card-body, then add the CSS and JS patches below.
                ===========================================================================
                --}}

                {{-- ── Students Table ── --}}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card border-0 shadow-sm sop-student-card">

                            {{-- Card header --}}
                            <div class="card-header d-flex align-items-center flex-wrap gap-2 sop-card-header">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-group-line me-2 text-primary"></i>Students
                                        <span class="badge bg-dark-subtle text-dark ms-1 rounded-pill" id="studentcount">
                                            {{ $students ? $students->total() : 0 }}
                                        </span>
                                    </h5>
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <div class="spinner-border spinner-border-sm text-primary d-none" id="register-loading-spinner" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#registeredClassesModal">
                                        <i class="ri-eye-line me-1"></i> View Registered
                                    </button>
                                    <button type="button" class="btn btn-warning btn-sm" id="viewArchivedBtn" onclick="openArchivedModal();">
                                        <i class="ri-archive-line me-1"></i> History
                                    </button>
                                </div>
                            </div>

                            {{-- Select-all bar (mimics the reference UI's check-all-row) --}}
                            <div class="sop-check-all-bar" id="sopCheckAllBar">
                                <label class="sop-check-all-label" for="checkAll">
                                    <input type="checkbox" class="sop-chk" id="checkAll">
                                    <span>Select all visible</span>
                                </label>
                            </div>

                            {{-- Table head row --}}
                            <div class="sop-table-head">
                                <div></div>
                                <div class="sop-th">Student</div>
                                <div class="sop-th d-none d-md-block">Adm. No</div>
                                <div class="sop-th d-none d-sm-block">Class</div>
                                <div class="sop-th">Gender</div>
                                <div class="sop-th">Action</div>
                            </div>

                            {{-- Table body – server-rendered rows go here --}}
                            <div id="studentTableBody" class="sop-table-body">
                                @include('subjectoperation.partials.student_rows')
                            </div>

                            {{-- Pagination --}}
                            <div class="d-flex justify-content-end p-3" id="pagination-container">
                                {{ $students ? $students->links('pagination::bootstrap-5') : '' }}
                            </div>

                            {{-- ── Selection Tray (NEW) ── --}}
                            <div class="sop-tray" id="selection-tray">
                                <div class="sop-tray-inner">
                                    <span class="sop-tray-count" id="tray-count"></span>
                                    <div class="sop-tray-chips" id="tray-chips"></div>
                                    <div class="sop-tray-actions">
                                        <button class="sop-btn-unreg" onclick="openUnregisterModal()">
                                            <i class="ri-user-unfollow-line"></i>
                                            Unregister
                                        </button>
                                        <button class="sop-btn-reg" onclick="registerSelectedStudentsBatch()">
                                            <i class="ri-user-add-line"></i>
                                            Register selected
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>


                 {{-- ══════════════════════════════════════════════════════════ --}}
                {{-- MODAL: Snapshot Name                                       --}}
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
                                    <label class="form-label fw-semibold" for="snapshotNotesInput">Notes <span class="text-muted fw-normal">(optional)</span></label>
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
                                <button type="button" class="btn btn-danger px-4" id="confirmUnregisterBtn" onclick="proceedUnregister();">
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
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header" style="background:#1e3a5f;">
                                <h5 class="modal-title text-white" id="registeredClassesModalLabel">
                                    <i class="ri-graduation-cap-line me-2"></i>Registered Classes Overview
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-3" style="background:#f5f7fa;">
                                <div id="registeredClassesContent">
                                    <div class="text-center text-muted py-5">
                                        <div class="spinner-border text-primary" style="width:3rem;height:3rem;" role="status"></div>
                                        <p class="mt-3 mb-0">Loading registration data...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-outline-primary" onclick="printRegisteredClasses();">
                                    <i class="ri-printer-line me-1"></i> Print / Export PDF
                                </button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="ri-close-line me-1"></i> Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════ --}}
                {{-- MODAL: Unregistered History                                --}}
                {{-- ══════════════════════════════════════════════════════════ --}}
                <div class="modal fade" id="archivedModal" tabindex="-1" aria-labelledby="archivedModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header border-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
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
                                        <button class="btn btn-sm btn-outline-secondary" onclick="loadArchivedPage(1);">
                                            <i class="ri-refresh-line"></i> Refresh
                                        </button>
                                        <button class="btn btn-sm btn-success d-none" id="restoreSelectedBtn" onclick="restoreSelected();">
                                            <i class="ri-refresh-line me-1"></i> Restore Selected
                                        </button>
                                        <button class="btn btn-sm btn-danger d-none" id="deleteSelectedBtn" onclick="permanentDeleteSelected();">
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
                                                oninput="filterDetailRows(this.value);">
                                            <button class="btn btn-outline-secondary" type="button"
                                                onclick="document.getElementById('detailSearchInput').value='';filterDetailRows('');"
                                                title="Clear search">
                                                <i class="ri-close-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <button class="btn btn-sm btn-success" id="detailRestoreAllBtn" onclick="restoreEntireSnapshot();">
                                            <i class="ri-refresh-line me-1"></i> Restore All
                                        </button>
                                        <button class="btn btn-sm btn-success d-none" id="detailRestoreSelectedBtn" onclick="restoreDetailSelected();">
                                            <i class="ri-refresh-line me-1"></i> Restore Selected
                                        </button>
                                        <button class="btn btn-sm btn-danger d-none" id="detailDeleteSelectedBtn" onclick="deleteDetailSelected();">
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
                                    onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}';">
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- /subjectList --}}
        </div>
    </div>
</div>

<style>

/* ============================================================
   SOP STUDENT TABLE  –  New UI/UX (matches student_selection_tray_ui.html)
   Add this entire block inside the <style> tag in index.blade.php.
   Prefix: sop-   (Subject-Operation)
   ============================================================ */

/* ── Card ── */
.sop-student-card { overflow: hidden; }
.sop-card-header  { background: var(--bs-body-bg); border-bottom: 0.5px solid var(--bs-border-color); }

/* ── Check-all bar ── */
.sop-check-all-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: var(--bs-secondary-bg);
    border-bottom: 0.5px solid var(--bs-border-color);
}
.sop-check-all-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: var(--bs-secondary-color);
    cursor: pointer;
    user-select: none;
}

/* ── Table head ── */
.sop-table-head {
    display: grid;
    grid-template-columns: 40px 1fr 130px 100px 90px 110px;
    padding: 0 16px;
    border-bottom: 0.5px solid var(--bs-border-color);
}
.sop-th {
    padding: 9px 0;
    font-size: 10.5px;
    font-weight: 600;
    color: var(--bs-secondary-color);
    text-transform: uppercase;
    letter-spacing: .05em;
}

/* ── Table body ── */
.sop-table-body { }

/* ── Student row ── */
.sop-student-row {
    display: grid;
    grid-template-columns: 40px 1fr 130px 100px 90px 110px;
    padding: 0 16px;
    border-bottom: 0.5px solid var(--bs-border-color);
    align-items: center;
    min-height: 58px;
    cursor: pointer;
    transition: background .18s cubic-bezier(.4, 0, .2, 1);
    animation: sopRowIn .32s cubic-bezier(.34, 1.56, .64, 1) both;
    position: relative;
}
.sop-student-row:last-child { border-bottom: none; }
.sop-student-row:hover      { background: var(--bs-secondary-bg); }

/* Selected state */
.sop-student-row.sop-selected {
    background: #EEEDFE !important;
}
.sop-student-row.sop-selected:hover {
    background: #E4E2FB !important;
}

/* Row-in animation – staggered via inline animation-delay */
@keyframes sopRowIn {
    from { opacity: 0; transform: translateY(7px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ── Custom checkbox ── */
.sop-chk {
    width: 17px;
    height: 17px;
    border-radius: 5px;
    border: 1.5px solid var(--bs-border-color);
    appearance: none;
    -webkit-appearance: none;
    cursor: pointer;
    background: var(--bs-body-bg);
    transition: all .18s cubic-bezier(.34, 1.56, .64, 1);
    position: relative;
    flex-shrink: 0;
}
.sop-chk:checked {
    background: #534AB7;
    border-color: #534AB7;
}
.sop-chk:checked::after {
    content: '';
    position: absolute;
    left: 4px; top: 1.5px;
    width: 6px; height: 9px;
    border: 2px solid #fff;
    border-top: none;
    border-left: none;
    transform: rotate(42deg);
}
.sop-chk:focus-visible { outline: 2px solid #534AB7; outline-offset: 2px; }

.sop-chk-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ── Avatar ── */
.sop-avatar {
    width: 34px; height: 34px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 600;
    flex-shrink: 0;
}
.sop-avatar-img {
    width: 34px; height: 34px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--bs-border-color);
    flex-shrink: 0;
}
.sop-student-info {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}
.sop-name {
    font-size: 13.5px;
    font-weight: 500;
    color: var(--bs-body-color);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.sop-adm {
    font-size: 11.5px;
    color: var(--bs-secondary-color);
}
.sop-td {
    font-size: 12.5px;
    color: var(--bs-secondary-color);
}

/* ── Inline badges ── */
.sop-badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 9px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 500;
    white-space: nowrap;
}
.sop-badge-f { background: #FBEAF0; color: #993556; }
.sop-badge-m { background: #E6F1FB; color: #0C447C; }

/* ── Empty state ── */
.sop-no-result {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 48px 20px;
    color: var(--bs-secondary-color);
    font-size: 14px;
    gap: 10px;
}
.sop-no-result svg {
    width: 42px; height: 42px;
    opacity: .3;
}

/* ── Selection Tray ── */
.sop-tray {
    position: sticky;
    bottom: 0; left: 0; right: 0;
    background: var(--bs-body-bg);
    border-top: 0.5px solid rgba(83, 74, 183, .25);
    padding: 11px 16px;
    transform: translateY(110%);
    transition: transform .38s cubic-bezier(.34, 1.2, .64, 1);
    z-index: 20;
    border-radius: 0 0 .75rem .75rem;
}
.sop-tray.sop-tray-visible {
    transform: translateY(0);
}
.sop-tray-inner {
    display: flex;
    align-items: center;
    gap: 12px;
}
.sop-tray-count {
    font-size: 12px;
    color: #534AB7;
    font-weight: 600;
    white-space: nowrap;
    flex-shrink: 0;
}

/* ── Chips ── */
.sop-tray-chips {
    display: flex;
    gap: 6px;
    flex: 1;
    overflow-x: auto;
    padding: 2px 0;
    scrollbar-width: none;
}
.sop-tray-chips::-webkit-scrollbar { display: none; }

.sop-chip {
    display: flex;
    align-items: center;
    gap: 5px;
    background: #EEEDFE;
    border: 0.5px solid #AFA9EC;
    border-radius: 20px;
    padding: 4px 10px 4px 5px;
    flex-shrink: 0;
    font-size: 12px;
    color: #3C3489;
    animation: sopChipIn .32s cubic-bezier(.34, 1.56, .64, 1) both;
}
@keyframes sopChipIn {
    from { opacity: 0; transform: scale(.55) translateX(-10px); }
    to   { opacity: 1; transform: scale(1)  translateX(0); }
}

/* Chip exit (added via JS) */
.sop-chip.sop-chip-exit {
    animation: sopChipOut .22s cubic-bezier(.55, 0, 1, .45) both !important;
}
@keyframes sopChipOut {
    from { opacity: 1; transform: scale(1); max-width: 200px; margin-right: 0; }
    to   { opacity: 0; transform: scale(.4); max-width: 0; padding: 0; margin-right: -6px; }
}

.sop-chip-avatar {
    width: 22px; height: 22px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 9px; font-weight: 600;
}
.sop-chip-x {
    width: 15px; height: 15px;
    border-radius: 50%;
    background: rgba(83, 74, 183, .18);
    border: none;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    margin-left: 2px;
    transition: background .15s;
    flex-shrink: 0;
    padding: 0;
    line-height: 1;
}
.sop-chip-x:hover { background: rgba(83, 74, 183, .38); }

/* ── Tray action buttons ── */
.sop-tray-actions {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
}
.sop-btn-reg {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 8px;
    border: none;
    background: #534AB7;
    color: #fff;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: background .15s, transform .1s;
}
.sop-btn-reg:hover  { background: #3C3489; }
.sop-btn-reg:active { transform: scale(.97); }
.sop-btn-unreg {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 8px;
    border: 0.5px solid var(--bs-border-color);
    background: var(--bs-secondary-bg);
    color: var(--bs-body-color);
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: background .15s, border-color .15s, transform .1s;
}
.sop-btn-unreg:hover  { border-color: var(--bs-primary); }
.sop-btn-unreg:active { transform: scale(.97); }

/* ── Responsive tweaks ── */
@media (max-width: 575.98px) {
    .sop-table-head,
    .sop-student-row {
        grid-template-columns: 40px 1fr 90px 110px;
    }
    .sop-tray-actions { gap: 4px; }
    .sop-btn-reg, .sop-btn-unreg { padding: 7px 10px; font-size: 12px; }
}

@keyframes chipSpring {
    from { opacity:0; transform:scale(.5) translateX(-10px); }
    to   { opacity:1; transform:scale(1)  translateX(0); }
}
.chip-av {
    width:22px; height:22px; border-radius:50%;
    background:#ddd6fe; color:#4c1d95;
    font-size:9px; font-weight:600;
    display:flex; align-items:center; justify-content:center;
}
.chip-remove {
    width:14px; height:14px; border-radius:50%;
    background:rgba(109,40,217,.15); border:none;
    cursor:pointer; display:flex; align-items:center; justify-content:center;
    margin-left:2px; transition:background .15s; padding:0;
    line-height:1;
}
.chip-remove:hover { background:rgba(109,40,217,.35); }
/* ── Subject check card styles ── */
.subject-check-card {
    transition: all .18s ease;
    border-color: #e2e8f0 !important;
    background: #ffffff !important;
}
.subject-check-card:hover {
    border-color: #667eea !important;
    background: #f8f4ff !important;
}
.subject-check-card.is-checked {
    border-color: #667eea !important;
    background: #ede9fe !important;
    border-width: 2px !important;
}

/* Term group styling */
.term-group {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Search box styling */
.search-box {
    position: relative;
}
.search-box .search-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 18px;
    color: #aaa;
}
.search-box input {
    padding-right: 35px;
}

/* Print styles */
@media print {
    body * { visibility: hidden; }
    #printableArea, #printableArea * { visibility: visible; }
    #printableArea { position: absolute; top: 0; left: 0; width: 100%; }
    .no-print { display: none !important; }
    @page { size: A4; margin: 15mm; }
}


</style>
@endsection

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ============================================================================
// GLOBALS
// ============================================================================
const ROUTES = {
    batchRegister   : '{{ route("subjectregistration.batch") }}',
    unregister      : '{{ route("subjects.destroy") }}',
    getRegistered   : '{{ route("subjects.registered-classes") }}',
    getArchived     : '{{ route("subjectoperation.archived") }}',
    getSnapshot     : '{{ route("subjectoperation.snapshot.detail") }}',
    restore         : '{{ route("subjectoperation.restore") }}',
    permanentDelete : '{{ route("subjectoperation.archive.batch-delete") }}',
    index           : '{{ route("subjects.index") }}',
};
const CSRF       = '{{ csrf_token() }}';
const AVATAR_URL = '{{ asset("storage") }}';

// School info for PDF print
window._schoolInfo = {
    name   : @json($school?->school_name    ?? 'School'),
    address: @json($school?->school_address ?? ''),
    phone  : @json($school?->school_phone   ?? ''),
    email  : @json($school?->school_email   ?? ''),
    motto  : @json($school?->school_motto   ?? ''),
    logo   : @json($school?->logo_url       ?? null),
};

let archiveCurrentPage  = 1;
let archiveMeta         = {};
let archiveSearchTimer  = null;
let currentSnapshotMeta = null;
let currentSnapshotRows = [];

// ============================================================================
// SWEET ALERT HELPER
// ============================================================================
function showSweetAlert(title, message, type, success = true) {
    Swal.fire({
        title,
        html: `<div class="d-flex align-items-center justify-content-center gap-2">
                <span style="font-size:2rem;">${success ? '🎉' : '😞'}</span>
                <span>${message}</span>
               </div>`,
        icon: success ? 'success' : 'error',
        confirmButtonColor: success ? '#28a745' : '#dc3545',
        confirmButtonText: success ? 'Great!' : 'Okay',
        timer: success ? 3000 : 5000,
        showConfirmButton: true,
    });
}

// ============================================================================
// SUBJECT CHECKBOXES - ENHANCED UI
// ============================================================================
function toggleSubjectCard(card) {
    const cb = card.querySelector('input[type="checkbox"]');
    cb.checked = !cb.checked;
    card.classList.toggle('is-checked', cb.checked);
    updateSubjectCount();
}

function updateSubjectCount() {
    const total = document.querySelectorAll('.subject-checkbox:checked').length;
    const countElement = document.getElementById('subjectTeacherCount');
    if (countElement) countElement.textContent = total;
}

function selectAllSubjects() {
    document.querySelectorAll('.subject-checkbox').forEach(cb => {
        cb.checked = true;
        const card = cb.closest('.subject-check-card');
        if (card) card.classList.add('is-checked');
    });
    updateSubjectCount();
}

function deselectAllSubjects() {
    document.querySelectorAll('.subject-checkbox').forEach(cb => {
        cb.checked = false;
        const card = cb.closest('.subject-check-card');
        if (card) card.classList.remove('is-checked');
    });
    updateSubjectCount();
}

function initializeSubjectCards() {
    document.querySelectorAll('.subject-checkbox:checked').forEach(cb => {
        const card = cb.closest('.subject-check-card');
        if (card) card.classList.add('is-checked');
    });
    updateSubjectCount();
}

// ============================================================================
// BUILD SUBJECT→TEACHER LOOKUP FROM CHECKBOX DATA ATTRIBUTES
// ============================================================================
function buildSubjectTeacherLookup() {
    const lookup = {};

    document.querySelectorAll('.subject-checkbox').forEach(cb => {
        const termId = String(cb.dataset.termid ?? '').trim();
        const subjectName = cb.dataset.subjectName ?? '';
        const teacherName = cb.dataset.teacherName ?? '';

        // Also try to get from parent card if data attributes are empty
        let finalSubjectName = subjectName;
        let finalTeacherName = teacherName;

        if (!finalSubjectName || !finalTeacherName) {
            const card = cb.closest('.subject-check-card');
            if (card) {
                finalSubjectName = finalSubjectName || card.dataset.subjectName || '';
                finalTeacherName = finalTeacherName || card.dataset.teacherName || '';

                // Fallback to DOM scraping
                if (!finalTeacherName) {
                    const teacherSpan = card.querySelector('.text-muted');
                    if (teacherSpan) finalTeacherName = teacherSpan.textContent.trim();
                }
                if (!finalSubjectName) {
                    const subjectSpan = card.querySelector('.fw-semibold');
                    if (subjectSpan) finalSubjectName = subjectSpan.textContent.trim();
                }
            }
        }

        if (!finalSubjectName || !finalTeacherName) return;

        const key = `${finalSubjectName.toLowerCase()}||${termId}`;

        if (!lookup[key]) lookup[key] = [];
        if (!lookup[key].includes(finalTeacherName)) lookup[key].push(finalTeacherName);
    });

    return lookup;
}

function resolveTeacher(subjectName, termId, lookup) {
    const key = `${subjectName.trim().toLowerCase()}||${String(termId ?? '').trim()}`;

    if (lookup[key] && lookup[key].length) {
        return lookup[key].join(', ');
    }

    // Fallback: match subject name across any term (handles term_id mismatch)
    const prefix = subjectName.trim().toLowerCase() + '||';
    for (const [k, v] of Object.entries(lookup)) {
        if (k.startsWith(prefix) && v.length) {
            return v.join(', ');
        }
    }

    return '—';
}

// ============================================================================
// DOM READY
// ============================================================================
document.addEventListener('DOMContentLoaded', function () {
    // Initialize subject cards
    initializeSubjectCards();

    // Image modal
    const imgModal = document.getElementById('imageViewModal');
    if (imgModal) {
        imgModal.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            const src = btn?.getAttribute('data-image');
            document.getElementById('enlargedImage').src = src || '{{ asset("storage/student_avatars/unnamed.jpg") }}';
        });
    }

    // Registered-classes modal — load on open
    document.getElementById('registeredClassesModal')?.addEventListener('show.bs.modal', loadRegisteredClasses);

    // Archive per-page change
    document.getElementById('archivePerPage')?.addEventListener('change', () => loadArchivedPage(1));

    // Snapshot notes character counter
    document.getElementById('snapshotNotesInput')?.addEventListener('input', function () {
        document.getElementById('snapshotNotesCount').textContent = this.value.length;
    });

    // Archive search debounce
    document.getElementById('archiveSearch')?.addEventListener('input', function () {
        clearTimeout(archiveSearchTimer);
        archiveSearchTimer = setTimeout(() => loadArchivedPage(1), 400);
    });

    // Archive term filter
    document.getElementById('archiveTermFilter')?.addEventListener('change', () => loadArchivedPage(1));

    // checkAll for students
    document.getElementById('checkAll')?.addEventListener('change', function () {
        document.querySelectorAll('#studentTableBody input[name="chk_child"]').forEach(cb => {
            cb.checked = this.checked;
            cb.closest('tr')?.classList.toggle('table-active', this.checked);
        });
        toggleBatchButtons();
    });

    // Individual student checkbox delegation
    document.addEventListener('change', function (e) {
        if (e.target?.name === 'chk_child') {
            e.target.closest('tr')?.classList.toggle('table-active', e.target.checked);
            toggleBatchButtons();
            const all     = document.querySelectorAll('#studentTableBody input[name="chk_child"]');
            const checked = document.querySelectorAll('#studentTableBody input[name="chk_child"]:checked');
            const ca      = document.getElementById('checkAll');
            if (ca) ca.checked = all.length > 0 && all.length === checked.length;
        }
    });

    setupPaginationLinks();

    if (typeof Choices !== 'undefined') {
        ['idclass', 'idsession', 'idgender', 'idadmission'].forEach(id => {
            const el = document.getElementById(id);
            if (el) new Choices(el, { searchEnabled: true });
        });
    }
});

// ============================================================================
// HELPERS
// ============================================================================
function escapeHtml(str) {
    if (!str) return str ?? '';
    return String(str).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
}

function setSpinner(on) {
    document.getElementById('register-loading-spinner')?.classList.toggle('d-none', !on);
}

async function apiFetch(url, method, body) {
    const res  = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify(body),
    });
    const data = await res.json();
    if (!res.ok && !data.success) throw new Error(data.message || `HTTP ${res.status}`);
    return data;
}

function getSelectedStudentIds() {
    return [...document.querySelectorAll('#studentTableBody input[name="chk_child"]:checked')]
        .map(cb => parseInt(cb.closest('tr').querySelector('.id').dataset.id));
}

function getSelectedSubjectClasses() {
    return [...document.querySelectorAll('.subject-checkbox:checked')].map(cb => ({
        subjectclassid: parseInt(cb.dataset.subjectclassid),
        staffid       : parseInt(cb.dataset.staffid),
        termid        : parseInt(cb.dataset.termid),
    }));
}

// function toggleBatchButtons() {
//     const any = document.querySelectorAll('#studentTableBody input[name="chk_child"]:checked').length > 0;
//     document.getElementById('register-selected-btn')?.classList.toggle('d-none', !any);
//     document.getElementById('unregister-selected-btn')?.classList.toggle('d-none', !any);
// }
function toggleBatchButtons() {
    const checked = [...document.querySelectorAll('#studentTableBody input[name="chk_child"]:checked')];
    const tray    = document.getElementById('selection-tray');
    const chips   = document.getElementById('tray-chips');
    const count   = document.getElementById('tray-count');

    if (!checked.length) {
        tray?.classList.remove('tray-visible');
        return;
    }

    tray?.classList.add('tray-visible');
    count.textContent = `${checked.length} student${checked.length !== 1 ? 's' : ''} selected`;

    chips.innerHTML = checked.map(cb => {
        const row  = cb.closest('tr');
        const name = row?.querySelector('.fw-semibold')?.textContent?.trim() ?? 'Student';
        const initials = name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();
        const id   = row?.querySelector('.id')?.dataset?.id;
        return `<div class="tray-chip">
            <div class="chip-av">${initials}</div>
            ${name.split(' ')[0]}
            <button class="chip-remove" onclick="uncheckStudent('${id}', this)" title="Remove">×</button>
        </div>`;
    }).join('');
}

function uncheckStudent(id, btn) {
    const row = document.querySelector(`#studentTableBody tr .id[data-id="${id}"]`)?.closest('tr');
    if (!row) return;
    const cb = row.querySelector('input[name="chk_child"]');
    if (cb) { cb.checked = false; row.classList.remove('table-active'); }
    toggleBatchButtons();
}
// ============================================================================
// FILTER / SEARCH (AJAX)
// ============================================================================
function filterData() {
    const classId   = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;

    if (classId === 'ALL' || sessionId === 'ALL') {
        Swal.fire({ icon: 'warning', title: 'Missing filters', text: 'Please select a class and session.', showConfirmButton: true });
        return;
    }

    const search    = document.querySelector('.search-box input.search')?.value ?? '';
    const gender    = document.getElementById('idgender').value;
    const admission = document.getElementById('idadmission').value;

    const tableBody                = document.getElementById('studentTableBody');
    const subjectTeachersContainer = document.getElementById('subjectTeachersContainer');
    const subjectTeachersCard      = document.getElementById('subjectTeachersCard');
    const subjectTeacherCount      = document.getElementById('subjectTeacherCount');

    if (tableBody) tableBody.innerHTML = '<tr><td colspan="7" class="text-center">Loading…</td></tr>';
    if (subjectTeachersContainer) subjectTeachersContainer.innerHTML = '<div class="col-12 text-center">Loading subject teachers…</div>';

    const params = new URLSearchParams({ class_id: classId, session_id: sessionId, search, gender, admissionno: admission });

    fetch(ROUTES.index + '?' + params.toString(), {
        headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
    })
    .then(r => r.text())
    .then(html => {
        const doc  = new DOMParser().parseFromString(html, 'text/html');
        const pick = (id) => ({ fresh: doc.getElementById(id), live: document.getElementById(id) });

        const tb = pick('studentTableBody');
        if (tb.fresh && tb.live) tb.live.innerHTML = tb.fresh.innerHTML;

        const pg = pick('pagination-container');
        if (pg.fresh && pg.live) pg.live.innerHTML = pg.fresh.innerHTML;

        const sc = pick('studentcount');
        if (sc.fresh && sc.live) sc.live.textContent = sc.fresh.textContent;

        const stc = pick('subjectTeachersContainer');
        if (stc.fresh && subjectTeachersContainer) {
            subjectTeachersContainer.innerHTML = stc.fresh.innerHTML;
            const count = subjectTeachersContainer.querySelectorAll('.subject-checkbox').length;
            if (subjectTeacherCount) subjectTeacherCount.textContent = count;
            if (subjectTeachersCard) subjectTeachersCard.style.display = count > 0 ? 'block' : 'none';
            initializeSubjectCards();
        }

        const admNos = [...new Set(
            [...doc.querySelectorAll('#studentTableBody .admissionno')]
                .map(el => el.dataset.admissionno || el.textContent.trim())
                .filter(Boolean)
        )].sort();
        updateAdmissionNoOptions(admNos.map(a => ({ admissionno: a })));

        updateSubjectCount();
        setupPaginationLinks();
    })
    .catch(err => {
        if (tableBody) tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading data. Please try again.</td></tr>';
        Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'Failed to fetch filtered data.', showConfirmButton: true });
    });
}

// ============================================================================
// PAGINATION (AJAX)
// ============================================================================
function setupPaginationLinks() {
    document.querySelectorAll('#pagination-container a').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            if (this.href && !this.classList.contains('disabled')) loadPage(this.href);
        });
    });
}

function loadPage(url) {
    const tableBody                = document.getElementById('studentTableBody');
    const subjectTeachersContainer = document.getElementById('subjectTeachersContainer');
    const subjectTeachersCard      = document.getElementById('subjectTeachersCard');
    const subjectTeacherCount      = document.getElementById('subjectTeacherCount');

    if (tableBody) tableBody.innerHTML = '<tr><td colspan="7" class="text-center">Loading…</td></tr>';

    fetch(url, { headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
    .then(r => r.text())
    .then(html => {
        const doc  = new DOMParser().parseFromString(html, 'text/html');
        const pick = (id) => ({ fresh: doc.getElementById(id), live: document.getElementById(id) });

        const tb = pick('studentTableBody');
        if (tb.fresh && tb.live) tb.live.innerHTML = tb.fresh.innerHTML;

        const pg = pick('pagination-container');
        if (pg.fresh && pg.live) pg.live.innerHTML = pg.fresh.innerHTML;

        const sc = pick('studentcount');
        if (sc.fresh && sc.live) sc.live.textContent = sc.fresh.textContent;

        const stc = pick('subjectTeachersContainer');
        if (stc.fresh && subjectTeachersContainer) {
            subjectTeachersContainer.innerHTML = stc.fresh.innerHTML;
            const count = subjectTeachersContainer.querySelectorAll('.subject-checkbox').length;
            if (subjectTeacherCount) subjectTeacherCount.textContent = count;
            if (subjectTeachersCard) subjectTeachersCard.style.display = count > 0 ? 'block' : 'none';
            initializeSubjectCards();
        }

        updateSubjectCount();
        setupPaginationLinks();
    })
    .catch(() => {
        if (tableBody) tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading data. Please try again.</td></tr>';
    });
}

// ============================================================================
// ADMISSION NO DROPDOWN
// ============================================================================
function updateAdmissionNoOptions(students) {
    const select = document.getElementById('idadmission');
    if (!select) return;
    select.innerHTML = '<option value="ALL">Select Admission No</option>';
    [...new Set(students.map(s => s.admissionno).filter(Boolean))].sort().forEach(no => {
        const opt = document.createElement('option');
        opt.value = no;
        opt.text  = no;
        select.appendChild(opt);
    });
}

// ============================================================================
// REGISTER BATCH
// ============================================================================
async function registerSelectedStudentsBatch() {
    const studentIds     = getSelectedStudentIds();
    const subjectClasses = getSelectedSubjectClasses();
    const sessionId      = document.getElementById('idsession').value;

    if (!studentIds.length)     return showSweetAlert('No Students Selected',  'Please select at least one student.',  'warning', false);
    if (!subjectClasses.length) return showSweetAlert('No Subjects Selected',  'Please select at least one subject.',  'warning', false);
    if (sessionId === 'ALL')    return showSweetAlert('Session Required',       'Please select a session.',             'warning', false);

    const ok = await Swal.fire({
        title: 'Confirm Registration',
        html : `<div class="text-center"><span style="font-size:3rem;">📚</span>
                <p class="mt-2">Register <strong>${studentIds.length}</strong> student(s) for <strong>${subjectClasses.length}</strong> subject(s)?</p></div>`,
        icon : 'question', showCancelButton: true,
        confirmButtonColor: '#28a745', cancelButtonColor: '#6c757d', confirmButtonText: 'Yes, register!',
    });
    if (!ok.isConfirmed) return;

    setSpinner(true);
    try {
        const res = await apiFetch(ROUTES.batchRegister, 'POST', {
            studentids    : studentIds,
            subjectclasses: subjectClasses,
            sessionid     : parseInt(sessionId),
        });
        if (res.success) {
            showSweetAlert('Registration Successful!', res.message, 'success', true);
            setTimeout(() => location.reload(), 2000);
        } else {
            showSweetAlert('Registration Failed', res.message || 'Some students could not be registered.', 'error', false);
        }
    } catch (err) {
        showSweetAlert('Error', 'Registration failed: ' + err.message, 'error', false);
    } finally {
        setSpinner(false);
    }
}

// ============================================================================
// OPEN UNREGISTER MODAL
// ============================================================================
function openUnregisterModal() {
    const studentIds     = getSelectedStudentIds();
    const subjectClasses = getSelectedSubjectClasses();
    const sessionId      = document.getElementById('idsession').value;

    if (!studentIds.length)     return showSweetAlert('No Students Selected',  'Please select at least one student.',  'warning', false);
    if (!subjectClasses.length) return showSweetAlert('No Subjects Selected',  'Please select at least one subject.',  'warning', false);
    if (sessionId === 'ALL')    return showSweetAlert('Session Required',       'Please select a session.',             'warning', false);

    document.getElementById('snapshotStudentCount').textContent = `${studentIds.length} student${studentIds.length !== 1 ? 's' : ''}`;
    document.getElementById('snapshotSubjectCount').textContent = `${subjectClasses.length} subject${subjectClasses.length !== 1 ? 's' : ''}`;

    const nameInput = document.getElementById('snapshotNameInput');
    nameInput.value = '';
    nameInput.classList.remove('is-invalid');
    document.getElementById('snapshotNotesInput').value = '';
    document.getElementById('snapshotNotesCount').textContent = '0';

    const now     = new Date();
    const dateStr = now.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    const timeStr = now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
    nameInput.value = `Unregistration — ${dateStr} ${timeStr}`;

    new bootstrap.Modal(document.getElementById('snapshotNameModal')).show();
}

// ============================================================================
// PROCEED UNREGISTER
// ============================================================================
async function proceedUnregister() {
    const nameInput  = document.getElementById('snapshotNameInput');
    const notesInput = document.getElementById('snapshotNotesInput');
    const name       = nameInput.value.trim();

    if (!name) { nameInput.classList.add('is-invalid'); return; }
    nameInput.classList.remove('is-invalid');

    const studentIds     = getSelectedStudentIds();
    const subjectClasses = getSelectedSubjectClasses();
    const sessionId      = document.getElementById('idsession').value;

    bootstrap.Modal.getInstance(document.getElementById('snapshotNameModal'))?.hide();
    setSpinner(true);

    try {
        const res = await apiFetch(ROUTES.unregister, 'DELETE', {
            studentids    : studentIds,
            subjectclasses: subjectClasses,
            sessionid     : parseInt(sessionId),
            snapshot_name : name,
            snapshot_notes: notesInput.value.trim() || null,
        });

        if (res.success || res.success_count > 0) {
            showSweetAlert(
                'Unregistration Complete',
                `${res.success_count} student(s) unregistered.<br><small class="text-muted">Snapshot saved as "<strong>${escapeHtml(name)}</strong>"</small>`,
                'success', true
            );
            setTimeout(() => location.reload(), 2500);
        } else {
            showSweetAlert('Unregistration Failed', res.message || 'No students were unregistered.', 'error', false);
        }
    } catch (err) {
        showSweetAlert('Error', 'Unregistration failed: ' + err.message, 'error', false);
    } finally {
        setSpinner(false);
    }
}

// ============================================================================
// REGISTERED CLASSES MODAL
// ============================================================================
async function loadRegisteredClasses() {
    const classId   = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    const container = document.getElementById('registeredClassesContent');

    if (classId === 'ALL' || sessionId === 'ALL') {
        container.innerHTML = `<div class="text-center py-5">
            <i class="ri-error-warning-line ri-3x text-warning"></i>
            <p class="text-muted mt-3 mb-0">Please select a class and session first.</p></div>`;
        return;
    }

    container.innerHTML = `<div class="text-center py-5">
        <div class="spinner-border text-primary" style="width:3rem;height:3rem;"></div>
        <p class="mt-3 text-muted">Loading…</p></div>`;

    try {
        const res  = await fetch(
            ROUTES.getRegistered + '?' + new URLSearchParams({ class_id: classId, session_id: sessionId }),
            { headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } }
        );
        const data = await res.json();

        if (!data.success || !data.data.length) {
            container.innerHTML = `<div class="text-center py-5">
                <i class="ri-information-line ri-3x text-muted"></i>
                <p class="text-muted mt-3 mb-0">No registered classes found.</p></div>`;
            return;
        }

        const subjectTeacherLookup = buildSubjectTeacherLookup();

        const termMap = {};
        data.data.forEach(row => {
            const key = row.term_name;
            if (!termMap[key]) {
                termMap[key] = {
                    class_name   : row.class_name,
                    arm_name     : row.arm_name     ?? '',
                    session_name : row.session_name,
                    term_name    : row.term_name,
                    term_id      : String(row.term_id ?? row.termid ?? '').trim(),
                    student_count: row.student_count,
                    subject_count: row.subject_count,
                    subjects     : [],
                };
            }
            if (row.subject_name) {
                termMap[key].subjects.push({
                    name   : row.subject_name,
                    teacher: null,
                    count  : row.student_count ?? '',
                });
            }
        });

        Object.values(termMap).forEach(term => {
            if (!term.subjects.length) {
                const raw     = data.data.find(r => r.term_name === term.term_name);
                const subjStr = raw?.subjects ?? '';
                subjStr.split(',').map(s => s.trim()).filter(Boolean).forEach(name => {
                    term.subjects.push({ name, teacher: null, count: '' });
                });
            }
        });

        Object.values(termMap).forEach(term => {
            term.subjects = term.subjects.map(s => ({
                ...s,
                teacher: resolveTeacher(s.name, term.term_id, subjectTeacherLookup),
            }));
        });

        let html = '';
        Object.values(termMap).forEach(term => {
            const fallbackCount = term.student_count ?? '';

            const subjectCells = term.subjects.map((s, i) => {
                const displayCount = s.count ? String(s.count) : (fallbackCount ? String(fallbackCount) : '');
                return `
                <div style="padding:10px 14px;border-right:0.5px solid #e5e7eb;border-bottom:0.5px solid #e5e7eb;display:flex;gap:10px;align-items:flex-start;" data-subject-cell>
                    <div style="width:24px;height:24px;border-radius:50%;background:#EEEDFE;color:#3C3489;font-size:11px;font-weight:500;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;" data-cell-num>${i + 1}</div>
                    <div style="min-width:0;" data-cell-info>
                        <div style="font-size:13px;font-weight:500;color:var(--bs-body-color);line-height:1.35;" data-cell-subject>${escapeHtml(s.name)}</div>
                        <div style="font-size:11px;color:var(--bs-secondary-color);margin-top:3px;display:flex;align-items:center;gap:4px;" data-cell-teacher-row>
                            <svg style="width:11px;height:11px;flex-shrink:0;opacity:.5;" viewBox="0 0 16 16" fill="currentColor"><path d="M8 8a3 3 0 100-6 3 3 0 000 6zm-5 5a5 5 0 0110 0H3z"/></svg>
                            <span data-cell-teacher>${escapeHtml(s.teacher)}</span>
                        </div>
                        ${displayCount
                            ? `<span style="font-size:10px;background:#EAF3DE;color:#27500A;padding:2px 8px;border-radius:20px;display:inline-block;margin-top:5px;" data-cell-count>${escapeHtml(displayCount)} students</span>`
                            : ''}
                    </div>
                </div>`;
            }).join('');

            html += `
            <div class="mb-3" data-term-block="${escapeHtml(term.term_name)}" data-term-student-count="${escapeHtml(String(fallbackCount))}">
                <div style="background:var(--bs-body-bg);border-radius:12px;border:0.5px solid #dee2e6;overflow:hidden;">
                    <div style="padding:10px 14px;border-bottom:0.5px solid #dee2e6;display:flex;justify-content:space-between;align-items:center;background:var(--bs-body-bg);" data-term-header>
                        <div>
                            <div style="font-size:13px;font-weight:500;color:var(--bs-body-color);" data-term-title>${escapeHtml(term.class_name)} ${escapeHtml(term.arm_name)} — ${escapeHtml(term.session_name)}</div>
                            <div style="font-size:11px;color:var(--bs-secondary-color);margin-top:2px;" data-term-subtitle>${escapeHtml(term.term_name)}</div>
                        </div>
                        <div style="display:flex;gap:6px;" data-term-pills>
                            <span style="font-size:11px;background:#E6F1FB;color:#0C447C;padding:3px 10px;border-radius:20px;font-weight:500;white-space:nowrap;">${term.student_count} students</span>
                            <span style="font-size:11px;background:#EEEDFE;color:#3C3489;padding:3px 10px;border-radius:20px;font-weight:500;white-space:nowrap;">${term.subject_count} subjects</span>
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));" data-subjects-grid>
                        ${subjectCells}
                    </div>
                </div>
            </div>`;
        });

        container.innerHTML = html;

    } catch (err) {
        container.innerHTML = `<div class="alert alert-danger m-3">Failed to load data: ${err.message}</div>`;
    }
}

// ============================================================================
// PRINT REGISTERED CLASSES
// ============================================================================
function printRegisteredClasses() {
    const container = document.getElementById('registeredClassesContent');
    const school    = window._schoolInfo || {};

    const termBlocks = container.querySelectorAll('[data-term-block]');

    if (!termBlocks.length) {
        Swal.fire({ icon: 'warning', title: 'Nothing to print', text: 'Load the registered classes first, then print.', showConfirmButton: true });
        return;
    }

    const now = new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'long', year: 'numeric' });

    let termsHtml = '';

    termBlocks.forEach(block => {
        const titleText = block.querySelector('[data-term-title]')?.textContent?.trim()    ?? '';
        const termText  = block.querySelector('[data-term-subtitle]')?.textContent?.trim() ?? '';

        const pillSpans  = [...(block.querySelectorAll('[data-term-pills] span') ?? [])];
        const pillsHtml  = pillSpans.map(p =>
            `<span style="background:#E6F1FB;color:#0C447C;padding:3px 10px;border-radius:20px;font-size:9pt;font-weight:500;margin-left:6px;">${escapeHtml(p.textContent.trim())}</span>`
        ).join('');

        const termStudentCount = block.dataset.termStudentCount ?? '';

        const cells = [...block.querySelectorAll('[data-subject-cell]')];
        let rows = '';
        cells.forEach((cell, idx) => {
            const subjName = cell.querySelector('[data-cell-subject]')?.textContent?.trim() ?? '';
            const teacher  = cell.querySelector('[data-cell-teacher]')?.textContent?.trim() ?? '—';
            const countEl   = cell.querySelector('[data-cell-count]');
            const countText = countEl
                ? countEl.textContent.trim()
                : (termStudentCount ? `${termStudentCount} students` : '');

            if (!subjName) return;

            rows += `<tr>
                <td style="width:36px;text-align:center;padding:8px 10px;border-bottom:0.5pt solid #e5e7eb;color:#6b7280;font-size:10pt;">${idx + 1}</td>
                <td style="padding:8px 12px;border-bottom:0.5pt solid #e5e7eb;font-size:10pt;font-weight:500;color:#111827;">${escapeHtml(subjName)}</td>
                <td style="padding:8px 12px;border-bottom:0.5pt solid #e5e7eb;font-size:10pt;color:#374151;">${escapeHtml(teacher)}</td>
                <td style="padding:8px 12px;border-bottom:0.5pt solid #e5e7eb;font-size:10pt;text-align:center;">
                    ${countText ? `<span style="background:#EAF3DE;color:#27500A;padding:2px 8px;border-radius:20px;font-size:9pt;">${escapeHtml(countText)}</span>` : '—'}
                </td>
             </tr>`;
        });

        termsHtml += `
        <div style="margin-bottom:24pt;break-inside:avoid;page-break-inside:avoid;">
            <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1.5pt solid #1e3a5f;padding-bottom:8pt;margin-bottom:0;">
                <div>
                    <div style="font-size:12pt;font-weight:700;color:#1e3a5f;">${escapeHtml(titleText)}</div>
                    <div style="font-size:9pt;color:#6b7280;margin-top:2pt;">${escapeHtml(termText)}</div>
                </div>
                <div>${pillsHtml}</div>
            </div>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:#f3f4f6;">
                        <th style="width:36px;padding:7px 10px;text-align:center;font-size:9pt;color:#6b7280;font-weight:500;border-bottom:1pt solid #d1d5db;">#</th>
                        <th style="padding:7px 12px;text-align:left;font-size:9pt;color:#6b7280;font-weight:500;border-bottom:1pt solid #d1d5db;">Subject</th>
                        <th style="padding:7px 12px;text-align:left;font-size:9pt;color:#6b7280;font-weight:500;border-bottom:1pt solid #d1d5db;">Teacher</th>
                        <th style="padding:7px 12px;text-align:center;font-size:9pt;color:#6b7280;font-weight:500;border-bottom:1pt solid #d1d5db;">Students</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>`;
    });

    const logoHtml = school.logo
        ? `<img src="${escapeHtml(school.logo)}" style="height:60pt;width:60pt;object-fit:contain;" alt="School Logo">`
        : `<div style="width:60pt;height:60pt;border-radius:50%;background:#1e3a5f;display:flex;align-items:center;justify-content:center;color:#fff;font-size:22pt;font-weight:700;">${(school.name || 'S').charAt(0)}</div>`;

    const printHtml = `<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Subject Registration Report — ${escapeHtml(school.name ?? '')}</title>
    <style>
        @page { size: A4; margin: 18mm 16mm 18mm 16mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #111827; background: #fff; font-size: 10pt; }
        .page-header { display: flex; align-items: center; gap: 16pt; padding-bottom: 14pt; border-bottom: 2pt solid #1e3a5f; margin-bottom: 18pt; }
        .school-info h1 { font-size: 16pt; font-weight: 700; color: #1e3a5f; margin-bottom: 3pt; }
        .school-info p { font-size: 8.5pt; color: #6b7280; line-height: 1.65; margin: 0; }
        .school-info .motto { font-size: 8pt; color: #1e3a5f; font-style: italic; margin-top: 5pt; }
        .doc-meta { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 18pt; }
        .doc-meta h2 { font-size: 13pt; font-weight: 700; color: #111827; }
        .doc-meta .sub { font-size: 8.5pt; color: #6b7280; margin-top: 3pt; }
        .doc-meta .date { font-size: 8.5pt; color: #6b7280; }
        .footer { margin-top: 24pt; padding-top: 8pt; border-top: 0.5pt solid #e5e7eb; display: flex; justify-content: space-between; font-size: 8pt; color: #9ca3af; }
        @media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
    </style>
</head>
<body>
    <div class="page-header">
        ${logoHtml}
        <div class="school-info">
            <h1>${escapeHtml(school.name ?? '')}</h1>
            ${school.address ? `<p>${escapeHtml(school.address)}</p>` : ''}
            ${(school.phone || school.email) ? `<p>${[school.phone, school.email].filter(Boolean).map(escapeHtml).join(' &nbsp;|&nbsp; ')}</p>` : ''}
            ${school.motto ? `<p class="motto">${escapeHtml(school.motto)}</p>` : ''}
        </div>
    </div>
    <div class="doc-meta">
        <div>
            <h2>Subject registration report</h2>
            <div class="sub">Registered subjects with assigned teachers per term</div>
        </div>
        <div class="date">Printed: ${now}</div>
    </div>
    ${termsHtml}
    <div class="footer">
        <span>${escapeHtml(school.name ?? '')} — Subject Registration Report</span>
        <span>Generated ${now}</span>
    </div>
</body>
</html>`;

    const win = window.open('', '_blank', 'width=900,height=1100');
    if (!win) {
        Swal.fire({ icon: 'error', title: 'Popup blocked', text: 'Please allow popups for this site to enable printing.', showConfirmButton: true });
        return;
    }
    win.document.write(printHtml);
    win.document.close();
    win.onload = () => { win.focus(); win.print(); };
}

// ============================================================================
// ARCHIVE (SNAPSHOT LIST) MODAL
// ============================================================================
function openArchivedModal() {
    const classId   = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;

    if (classId === 'ALL' || sessionId === 'ALL') {
        return showSweetAlert('Selection Required', 'Please select a class and session first.', 'warning', false);
    }

    archiveCurrentPage = 1;
    new bootstrap.Modal(document.getElementById('archivedModal')).show();
    loadArchivedPage(1);
}

async function loadArchivedPage(page) {
    archiveCurrentPage = page;

    const classId   = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    const termId    = document.getElementById('archiveTermFilter').value;
    const search    = document.getElementById('archiveSearch').value.trim();
    const perPage   = document.getElementById('archivePerPage').value;

    if (classId === 'ALL' || sessionId === 'ALL') return;

    const spinner   = document.getElementById('archiveSpinner');
    const container = document.getElementById('snapshotCardsContainer');

    spinner?.classList.remove('d-none');
    container.innerHTML = `<div class="text-center py-4">
        <div class="spinner-border spinner-border-sm text-warning me-2"></div> Loading snapshots…</div>`;

    try {
        const params = new URLSearchParams({ class_id: classId, session_id: sessionId, page, per_page: perPage });
        if (termId) params.set('term_id', termId);
        if (search) params.set('search', search);

        const res  = await fetch(ROUTES.getArchived + '?' + params.toString(), {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();

        if (!data.success) {
            container.innerHTML = `<div class="text-center text-danger py-4">${data.message}</div>`;
            return;
        }

        archiveMeta = data.meta;
        renderSnapshotCards(data.data);
        renderArchivePagination(data.meta);
        updateArchiveMeta(data.meta);
    } catch (err) {
        container.innerHTML = `<div class="text-center text-danger py-4">Error: ${err.message}</div>`;
    } finally {
        spinner?.classList.add('d-none');
    }
}

function renderSnapshotCards(rows) {
    const container  = document.getElementById('snapshotCardsContainer');
    const restoreBtn = document.getElementById('restoreSelectedBtn');
    const deleteBtn  = document.getElementById('deleteSelectedBtn');

    if (!rows.length) {
        container.innerHTML = `<div class="text-center text-muted py-5">
            <i class="ri-archive-line ri-3x d-block mb-2"></i>No unregistration snapshots found.</div>`;
        restoreBtn?.classList.add('d-none');
        deleteBtn?.classList.add('d-none');
        return;
    }

    restoreBtn?.classList.add('d-none');
    deleteBtn?.classList.add('d-none');

    const groups = {};
    rows.forEach(row => {
        const key = `${row.snapshot_name}__${row.subjectclassid}__${row.termid}`;
        if (!groups[key]) groups[key] = { ...row, subjects: [] };
        groups[key].subjects.push({
            subjectname   : row.subjectname,
            subjectcode   : row.subjectcode,
            staffname     : row.staffname,
            student_count : row.student_count,
            subjectclassid: row.subjectclassid,
            termid        : row.termid,
            sessionid     : row.sessionid,
            staffid       : row.staffid,
            archive_id    : row.archive_id,
        });
    });

    let html = '<div class="row g-3">';

    Object.values(groups).forEach(group => {
        const unregDate = group.unregistered_at
            ? new Date(group.unregistered_at).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' })
            : '—';

        const subjectPills = group.subjects.map(s =>
            `<span class="badge bg-primary-subtle text-primary me-1 mb-1">${escapeHtml(s.subjectname)}</span>`
        ).join('');

        const metaEncoded = encodeURIComponent(JSON.stringify({
            snapshot_name : group.snapshot_name,
            subjectclassid: group.subjectclassid,
            termid        : group.termid,
            sessionid     : group.sessionid,
            staffid       : group.staffid,
            archive_id    : group.archive_id,
        }));

        html += `
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100 snapshot-card" style="cursor:pointer;transition:transform .15s,box-shadow .15s;"
                 onclick="openSnapshotDetail('${metaEncoded}')"
                 onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,.12)';"
                 onmouseleave="this.style.transform='';this.style.boxShadow='';">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                        <div class="flex-grow-1 min-w-0">
                            <h6 class="fw-semibold mb-0 text-truncate" title="${escapeHtml(group.snapshot_name)}">
                                <i class="ri-camera-line text-danger me-1"></i>${escapeHtml(group.snapshot_name)}
                            </h6>
                            <small class="text-muted">${unregDate}</small>
                        </div>
                        <span class="badge bg-danger-subtle text-danger rounded-pill flex-shrink-0">
                            ${group.student_count} student${group.student_count !== 1 ? 's' : ''}
                        </span>
                    </div>
                    ${group.snapshot_notes
                        ? `<p class="text-muted small fst-italic mb-2" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">"${escapeHtml(group.snapshot_notes)}"</p>`
                        : ''}
                    <div class="mb-2">${subjectPills}</div>
                    <div class="d-flex justify-content-between align-items-center mt-auto pt-1 border-top">
                        <small class="text-muted"><i class="ri-user-star-line me-1"></i>${escapeHtml(group.staffname ?? '—')}</small>
                        <small class="text-muted"><span class="badge bg-warning-subtle text-warning-emphasis">${escapeHtml(group.termname)}</span></small>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 d-flex gap-2 py-2">
                    <button class="btn btn-sm btn-outline-primary flex-grow-1" onclick="event.stopPropagation();openSnapshotDetail('${metaEncoded}');">
                        <i class="ri-eye-line me-1"></i> View
                    </button>
                    <button class="btn btn-sm btn-outline-success flex-grow-1" onclick="event.stopPropagation();restoreSingleSnapshot('${metaEncoded}');">
                        <i class="ri-refresh-line me-1"></i> Restore
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation();deleteSnapshotGroup('${metaEncoded}');" title="Delete snapshot">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            </div>
        </div>`;
    });

    html += '</div>';
    container.innerHTML = html;
}

function renderArchivePagination(meta) {
    const container = document.getElementById('archivePagination');
    if (!meta || meta.last_page <= 1) { container.innerHTML = ''; return; }

    const delta = 3;
    let html = `<button class="btn btn-sm btn-outline-secondary ${meta.current_page === 1 ? 'disabled' : ''}"
                        onclick="loadArchivedPage(${meta.current_page - 1})">‹</button>`;
    for (let p = 1; p <= meta.last_page; p++) {
        if (p === 1 || p === meta.last_page || (p >= meta.current_page - delta && p <= meta.current_page + delta)) {
            html += `<button class="btn btn-sm ${p === meta.current_page ? 'btn-warning' : 'btn-outline-secondary'}"
                             onclick="loadArchivedPage(${p})">${p}</button>`;
        } else if (p === meta.current_page - delta - 1 || p === meta.current_page + delta + 1) {
            html += `<span class="btn btn-sm btn-outline-secondary disabled">…</span>`;
        }
    }
    html += `<button class="btn btn-sm btn-outline-secondary ${meta.current_page === meta.last_page ? 'disabled' : ''}"
                     onclick="loadArchivedPage(${meta.current_page + 1})">›</button>`;
    container.innerHTML = html;
}

function updateArchiveMeta(meta) {
    const el = document.getElementById('archiveMeta');
    if (!meta || !meta.total) { if (el) el.textContent = ''; return; }
    const from = (meta.current_page - 1) * meta.per_page + 1;
    const to   = Math.min(meta.current_page * meta.per_page, meta.total);
    el.textContent = `Showing ${from}–${to} of ${meta.total} snapshots`;
}

// ============================================================================
// SNAPSHOT DETAIL MODAL
// ============================================================================
async function openSnapshotDetail(metaEncoded) {
    currentSnapshotMeta = JSON.parse(decodeURIComponent(metaEncoded));

    document.getElementById('snapshotDetailTitle').textContent    = currentSnapshotMeta.snapshot_name;
    document.getElementById('snapshotDetailSubtitle').textContent = '';
    document.getElementById('snapshotNotesBanner')?.classList.add('d-none');

    const searchInput = document.getElementById('detailSearchInput');
    if (searchInput) searchInput.value = '';

    document.getElementById('snapshotDetailBody').innerHTML =
        '<tr><td colspan="10" class="text-center py-4"><div class="spinner-border spinner-border-sm me-2"></div>Loading students…</div></td></tr>';
    document.getElementById('detailRestoreSelectedBtn')?.classList.add('d-none');
    document.getElementById('detailDeleteSelectedBtn')?.classList.add('d-none');

    new bootstrap.Modal(document.getElementById('snapshotDetailModal')).show();

    try {
        const params = new URLSearchParams({
            snapshot_name : currentSnapshotMeta.snapshot_name,
            subjectclassid: currentSnapshotMeta.subjectclassid,
            termid        : currentSnapshotMeta.termid,
            sessionid     : currentSnapshotMeta.sessionid,
            staffid       : currentSnapshotMeta.staffid,
        });

        const res  = await fetch(ROUTES.getSnapshot + '?' + params.toString(), {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();

        if (!data.success) {
            document.getElementById('snapshotDetailBody').innerHTML =
                `<tr><td colspan="10" class="text-center text-danger py-4">${data.message}</td></tr>`;
            return;
        }

        currentSnapshotRows = data.rows;

        if (data.snapshot_notes) {
            document.getElementById('snapshotNotesBanner')?.classList.remove('d-none');
            document.getElementById('snapshotNotesText').textContent = data.snapshot_notes;
        }

        document.getElementById('detailStudentMeta').textContent =
            `${data.total_students} student${data.total_students !== 1 ? 's' : ''} in this snapshot`;

        renderSnapshotDetailTable(data.rows, data.assessment_headers);
    } catch (err) {
        document.getElementById('snapshotDetailBody').innerHTML =
            `<tr><td colspan="10" class="text-center text-danger py-4">Error: ${err.message}</td></tr>`;
    }
}

function renderSnapshotDetailTable(rows, assessmentHeaders) {
    const headerRow = document.getElementById('snapshotDetailHeaderRow');
    while (headerRow.cells.length > 4) headerRow.deleteCell(headerRow.cells.length - 1);

    (assessmentHeaders || []).forEach(a => {
        const th = document.createElement('th');
        th.textContent = a.assessment_name || `Assessment ${a.assessment_id}`;
        headerRow.appendChild(th);
    });
    const totalTh = document.createElement('th');
    totalTh.textContent = 'Total';
    headerRow.appendChild(totalTh);

    let html = '';
    rows.forEach(row => {
        const name    = [row.lastname, row.firstname, row.othername].filter(Boolean).join(' ');
        const picFile = row.picture ? row.picture.split('/').pop() : null;
        const pic     = picFile
            ? `${AVATAR_URL}/student_avatars/${picFile}`
            : `${AVATAR_URL}/student_avatars/unnamed.jpg`;

        const genderBadge = row.gender === 'Female'
            ? `<span class="badge text-white" style="background:#e84393;">${escapeHtml(row.gender)}</span>`
            : `<span class="badge text-white" style="background:#1a6fd4;">${escapeHtml(row.gender ?? '—')}</span>`;

        let scoresCells = '';
        let total       = 0;
        (assessmentHeaders || []).forEach(a => {
            const score = (row.assessment_scores || []).find(s => s.assessment_id == a.assessment_id);
            const val   = score ? parseFloat(score.score) : 0;
            total += val;
            scoresCells += `<td class="text-center fw-medium">${val > 0 ? val.toFixed(1) : '<span class="text-muted">—</span>'}</td>`;
        });
        scoresCells += `<td class="text-center fw-bold ${total > 0 ? 'text-success' : 'text-muted'}">${total > 0 ? total.toFixed(1) : '—'}</td>`;

        const searchKey = `${name} ${row.admissionno ?? ''}`.toLowerCase();
        html += `<tr data-archive-id="${row.archive_id}" data-search="${escapeHtml(searchKey)}">
            <td><div class="form-check mb-0"><input class="form-check-input detail-chk" type="checkbox" value="${row.archive_id}"></div></td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <img src="${pic}" class="rounded-circle" style="width:34px;height:34px;object-fit:cover;border:2px solid #e9ecef;"
                         onerror="this.src='${AVATAR_URL}/student_avatars/unnamed.jpg'">
                    <span class="fw-medium">${escapeHtml(name)}</span>
                </div>
              </td>
            <td class="text-muted small">${escapeHtml(row.admissionno ?? '—')}</td>
            <td>${genderBadge}</td>
            ${scoresCells}
         </tr>`;
    });

    document.getElementById('snapshotDetailBody').innerHTML =
        html || '<tr><td colspan="10" class="text-center text-muted py-4">No students found.</td></tr>';

    document.getElementById('detailCheckAll')?.addEventListener('change', function () {
        document.querySelectorAll('.detail-chk').forEach(cb => cb.checked = this.checked);
        toggleDetailButtons();
    });
    document.querySelectorAll('.detail-chk').forEach(cb => cb.addEventListener('change', toggleDetailButtons));
}

function toggleDetailButtons() {
    const any = document.querySelectorAll('.detail-chk:checked').length > 0;
    document.getElementById('detailRestoreSelectedBtn')?.classList.toggle('d-none', !any);
    document.getElementById('detailDeleteSelectedBtn')?.classList.toggle('d-none', !any);
}

function filterDetailRows(query) {
    const q     = query.toLowerCase().trim();
    const rows  = document.querySelectorAll('#snapshotDetailBody tr[data-search]');
    let visible = 0;
    rows.forEach(tr => {
        const match = !q || tr.dataset.search.includes(q);
        tr.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    const total = currentSnapshotRows.length;
    const meta  = document.getElementById('detailStudentMeta');
    if (meta) {
        meta.textContent = q
            ? `${visible} of ${total} student${total !== 1 ? 's' : ''} shown`
            : `${total} student${total !== 1 ? 's' : ''} in this snapshot`;
    }
}

// ============================================================================
// RESTORE
// ============================================================================
async function restoreEntireSnapshot() {
    if (!currentSnapshotRows.length) return;
    await doRestore(currentSnapshotRows.map(r => r.archive_id), 'all students in this snapshot');
}

async function restoreDetailSelected() {
    const ids = [...document.querySelectorAll('.detail-chk:checked')].map(cb => parseInt(cb.value));
    if (!ids.length) return;
    await doRestore(ids, `${ids.length} selected student${ids.length !== 1 ? 's' : ''}`);
}

async function doRestore(archiveIds, label) {
    const ok = await Swal.fire({
        title: 'Restore Registration?',
        html : `<p>Restore <strong>${label}</strong>? Their original scores will be recovered.</p>`,
        icon : 'question', showCancelButton: true, confirmButtonColor: '#28a745', confirmButtonText: 'Yes, restore!',
    });
    if (!ok.isConfirmed) return;

    const spinner = document.getElementById('detailSpinner');
    spinner?.classList.remove('d-none');
    try {
        const res = await apiFetch(ROUTES.restore, 'POST', { archive_ids: archiveIds });
        if (res.success || res.total_restored > 0) {
            showSweetAlert('Restored!', `${res.total_restored || archiveIds.length} registration(s) restored with original scores.`, 'success', true);
            bootstrap.Modal.getInstance(document.getElementById('snapshotDetailModal'))?.hide();
            loadArchivedPage(archiveCurrentPage);
        } else {
            showSweetAlert('Restore Failed', res.message || 'Could not restore.', 'error', false);
        }
    } catch (err) {
        showSweetAlert('Error', 'Restore failed: ' + err.message, 'error', false);
    } finally {
        spinner?.classList.add('d-none');
    }
}

async function restoreSingleSnapshot(metaEncoded) {
    const meta = JSON.parse(decodeURIComponent(metaEncoded));
    const ok = await Swal.fire({
        title: 'Restore Snapshot?',
        html : `<p>Restore all students in snapshot "<strong>${escapeHtml(meta.snapshot_name)}</strong>"?<br>Original scores will be recovered.</p>`,
        icon : 'question', showCancelButton: true, confirmButtonColor: '#28a745', confirmButtonText: 'Yes, restore all!',
    });
    if (!ok.isConfirmed) return;

    const spinner = document.getElementById('archiveSpinner');
    spinner?.classList.remove('d-none');
    try {
        const params = new URLSearchParams({
            snapshot_name : meta.snapshot_name,
            subjectclassid: meta.subjectclassid,
            termid        : meta.termid,
            sessionid     : meta.sessionid,
            staffid       : meta.staffid,
        });
        const detailRes  = await fetch(ROUTES.getSnapshot + '?' + params.toString(), { headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
        const detailData = await detailRes.json();

        if (!detailData.success || !detailData.rows?.length) {
            showSweetAlert('Not Found', detailData.message || 'Snapshot records not found.', 'error', false);
            return;
        }

        const ids = detailData.rows.map(r => r.archive_id);
        const res = await apiFetch(ROUTES.restore, 'POST', { archive_ids: ids });
        if (res.success || res.total_restored > 0) {
            showSweetAlert('Restored!', `${res.total_restored || ids.length} registration(s) restored.`, 'success', true);
            loadArchivedPage(archiveCurrentPage);
        } else {
            showSweetAlert('Restore Failed', res.message, 'error', false);
        }
    } catch (err) {
        showSweetAlert('Error', err.message, 'error', false);
    } finally {
        spinner?.classList.add('d-none');
    }
}

// ============================================================================
// DELETE SNAPSHOTS
// ============================================================================
async function deleteSnapshotGroup(metaEncoded) {
    const meta = JSON.parse(decodeURIComponent(metaEncoded));
    const ok = await Swal.fire({
        title: 'Delete Snapshot?',
        html : `<p class="text-danger">Permanently delete snapshot "<strong>${escapeHtml(meta.snapshot_name)}</strong>"?<br>This cannot be undone.</p>`,
        icon : 'error', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, delete permanently',
    });
    if (!ok.isConfirmed) return;

    const spinner = document.getElementById('archiveSpinner');
    spinner?.classList.remove('d-none');
    try {
        const params = new URLSearchParams({
            snapshot_name : meta.snapshot_name,
            subjectclassid: meta.subjectclassid,
            termid        : meta.termid,
            sessionid     : meta.sessionid,
            staffid       : meta.staffid,
        });
        const detailRes  = await fetch(ROUTES.getSnapshot + '?' + params.toString(), { headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
        const detailData = await detailRes.json();

        if (!detailData.success || !detailData.rows?.length) {
            showSweetAlert('Not Found', detailData.message || 'Snapshot records not found.', 'error', false);
            return;
        }

        const ids = detailData.rows.map(r => r.archive_id);
        const res = await apiFetch(ROUTES.permanentDelete, 'DELETE', { archive_ids: ids });
        if (res.success) {
            showSweetAlert('Deleted', `${res.deleted || ids.length} record(s) permanently deleted.`, 'success', false);
            loadArchivedPage(archiveCurrentPage);
        } else {
            showSweetAlert('Delete Failed', res.message, 'error', false);
        }
    } catch (err) {
        showSweetAlert('Error', err.message, 'error', false);
    } finally {
        spinner?.classList.add('d-none');
    }
}

async function deleteDetailSelected() {
    const ids = [...document.querySelectorAll('.detail-chk:checked')].map(cb => parseInt(cb.value));
    if (!ids.length) return;

    const ok = await Swal.fire({
        title: 'Permanently Delete?',
        html : `<p class="text-danger">Delete <strong>${ids.length}</strong> record(s) permanently?</p>`,
        icon : 'error', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, delete permanently',
    });
    if (!ok.isConfirmed) return;

    const spinner = document.getElementById('detailSpinner');
    spinner?.classList.remove('d-none');
    try {
        const res = await apiFetch(ROUTES.permanentDelete, 'DELETE', { archive_ids: ids });
        if (res.success) {
            showSweetAlert('Deleted', `${res.deleted || ids.length} record(s) permanently deleted.`, 'success', false);
            bootstrap.Modal.getInstance(document.getElementById('snapshotDetailModal'))?.hide();
            loadArchivedPage(archiveCurrentPage);
        } else {
            showSweetAlert('Delete Failed', res.message, 'error', false);
        }
    } catch (err) {
        showSweetAlert('Error', err.message, 'error', false);
    } finally {
        spinner?.classList.add('d-none');
    }
}



// ============================================================================
//  SOP STUDENT TABLE  –  JS patches
//  Replace the entire toggleBatchButtons() function and add the helpers below.
//  Everything else in your existing <script> block stays as-is.
// ============================================================================

// ── State ────────────────────────────────────────────────────────────────────
// We track selected student IDs in a Set for O(1) add/delete,
// mirroring the standalone reference UI.
const _sopSelected = new Set();   // Set<studentId (number)>

// ── Utility ──────────────────────────────────────────────────────────────────
function sopInitials(name) {
    return name.trim().split(/\s+/).slice(0, 2).map(w => w[0]).join('').toUpperCase();
}

// ── Row animation re-trigger after AJAX reload ────────────────────────────────
// Call this after filterData() / loadPage() inject new rows.
function sopAnimateRows() {
    document.querySelectorAll('.sop-student-row').forEach((row, i) => {
        row.style.animationDelay = `${i * 35}ms`;
        // Force reflow so the animation replays even if class was already on the element
        row.style.animation = 'none';
        row.offsetHeight;          // reflow
        row.style.animation = '';  // restore (CSS handles the rest)
    });
}

// ── Row click – toggle selection ──────────────────────────────────────────────
function sopToggleRow(id, event) {
    // Ignore clicks that originated on the checkbox itself
    // (the checkbox's own `change` event handles that path)
    if (event && event.target && event.target.classList.contains('sop-chk')) return;

    const row = document.querySelector(`.sop-student-row[data-id="${id}"]`);
    if (!row) return;

    const cb = row.querySelector('input.sop-chk[name="chk_child"]');
    if (!cb) return;

    const willSelect = !_sopSelected.has(id);
    if (willSelect) {
        _sopSelected.add(id);
        cb.checked = true;
        row.classList.add('sop-selected');
    } else {
        _sopSelected.delete(id);
        cb.checked = false;
        row.classList.remove('sop-selected');
    }

    sopSyncTray();
    sopSyncCheckAll();
}

// ── Checkbox change (from individual <input> change event) ───────────────────
function sopOnCheckboxChange(cb) {
    const row = cb.closest('.sop-student-row');
    if (!row) return;
    const id = parseInt(cb.dataset.id);

    if (cb.checked) {
        _sopSelected.add(id);
        row.classList.add('sop-selected');
    } else {
        _sopSelected.delete(id);
        row.classList.remove('sop-selected');
    }

    sopSyncTray();
    sopSyncCheckAll();
}

// ── Select-all checkbox ───────────────────────────────────────────────────────
function sopToggleAll(checked) {
    document.querySelectorAll('#studentTableBody input.sop-chk[name="chk_child"]').forEach(cb => {
        const row = cb.closest('.sop-student-row');
        const id  = parseInt(cb.dataset.id);
        cb.checked = checked;
        if (checked) {
            _sopSelected.add(id);
            row && row.classList.add('sop-selected');
        } else {
            _sopSelected.delete(id);
            row && row.classList.remove('sop-selected');
        }
    });
    sopSyncTray();
}

// ── Sync the check-all state ──────────────────────────────────────────────────
function sopSyncCheckAll() {
    const all     = document.querySelectorAll('#studentTableBody input.sop-chk[name="chk_child"]');
    const checked = document.querySelectorAll('#studentTableBody input.sop-chk[name="chk_child"]:checked');
    const ca      = document.getElementById('checkAll');
    if (!ca) return;
    ca.checked       = all.length > 0 && all.length === checked.length;
    ca.indeterminate = checked.length > 0 && checked.length < all.length;
}

// ── Tray ──────────────────────────────────────────────────────────────────────
function sopSyncTray() {
    const tray      = document.getElementById('selection-tray');
    const chipsWrap = document.getElementById('tray-chips');
    const countEl   = document.getElementById('tray-count');

    if (!_sopSelected.size) {
        tray && tray.classList.remove('sop-tray-visible');
        return;
    }

    tray && tray.classList.add('sop-tray-visible');
    countEl.textContent = `${_sopSelected.size} student${_sopSelected.size !== 1 ? 's' : ''} selected`;

    // Re-render only chips whose IDs are new (preserve existing ones to avoid re-animating)
    const existingIds = new Set(
        [...chipsWrap.querySelectorAll('.sop-chip[data-chip-id]')].map(el => parseInt(el.dataset.chipId))
    );

    // Remove chips for deselected students (with exit animation)
    chipsWrap.querySelectorAll('.sop-chip[data-chip-id]').forEach(chip => {
        const chipId = parseInt(chip.dataset.chipId);
        if (!_sopSelected.has(chipId)) {
            chip.classList.add('sop-chip-exit');
            chip.addEventListener('animationend', () => chip.remove(), { once: true });
        }
    });

    // Add chips for newly selected students
    _sopSelected.forEach(id => {
        if (existingIds.has(id)) return; // already rendered

        const row = document.querySelector(`.sop-student-row[data-id="${id}"]`);
        const cb  = row ? row.querySelector('input.sop-chk[name="chk_child"]') : null;

        const name     = cb ? (cb.dataset.name      || row.dataset.name     || 'Student') : 'Student';
        const initials = cb ? (cb.dataset.initials   || sopInitials(name))                : sopInitials(name);
        const colorBg  = cb ? (cb.dataset.colorBg    || '#EEEDFE')                        : '#EEEDFE';
        const colorFg  = cb ? (cb.dataset.colorFg    || '#3C3489')                        : '#3C3489';
        const firstName = name.split(' ')[0];

        const chip = document.createElement('div');
        chip.className         = 'sop-chip';
        chip.dataset.chipId    = id;
        chip.innerHTML = `
            <div class="sop-chip-avatar" style="background:${colorBg};color:${colorFg};">${initials}</div>
            <span>${firstName}</span>
            <button class="sop-chip-x" title="Remove" onclick="sopRemoveChip(${id}, this)">
                <svg width="8" height="8" viewBox="0 0 8 8" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M1 1l6 6M7 1L1 7"/>
                </svg>
            </button>`;
        chipsWrap.appendChild(chip);
    });

    // Also keep the old-style register/unregister buttons in sync
    // (for back-compat with the existing header buttons)
    const regBtn   = document.getElementById('register-selected-btn');
    const unregBtn = document.getElementById('unregister-selected-btn');
    regBtn   && regBtn.classList.toggle('d-none',   !_sopSelected.size);
    unregBtn && unregBtn.classList.toggle('d-none', !_sopSelected.size);
}

// ── Remove a chip (with animation, then deselect row) ────────────────────────
function sopRemoveChip(id, btn) {
    const chip = btn ? btn.closest('.sop-chip') : document.querySelector(`.sop-chip[data-chip-id="${id}"]`);

    // Animate chip out
    if (chip) {
        chip.classList.add('sop-chip-exit');
        chip.addEventListener('animationend', () => chip.remove(), { once: true });
    }

    // Deselect row
    _sopSelected.delete(id);
    const row = document.querySelector(`.sop-student-row[data-id="${id}"]`);
    const cb  = row ? row.querySelector('input.sop-chk[name="chk_child"]') : null;
    if (cb)  cb.checked = false;
    if (row) row.classList.remove('sop-selected');

    sopSyncCheckAll();

    // If tray is now empty, hide it
    if (!_sopSelected.size) {
        const tray = document.getElementById('selection-tray');
        tray && tray.classList.remove('sop-tray-visible');
    } else {
        const countEl = document.getElementById('tray-count');
        if (countEl) countEl.textContent = `${_sopSelected.size} student${_sopSelected.size !== 1 ? 's' : ''} selected`;
    }
}

// ── Replacement for getSelectedStudentIds() ────────────────────────────────
// Override the existing function so the rest of your code still works.
function getSelectedStudentIds() {
    return [..._sopSelected];
}

// ── Override toggleBatchButtons (old function still called by the delegation
//    listener added in DOMContentLoaded) ──────────────────────────────────────
function toggleBatchButtons() {
    // Rebuild _sopSelected from the current checkboxes (keeps state in sync
    // when rows are re-rendered by AJAX and old checkboxes disappear)
    const checked = document.querySelectorAll('#studentTableBody input.sop-chk[name="chk_child"]:checked');
    checked.forEach(cb => _sopSelected.add(parseInt(cb.dataset.id)));

    // Remove ids that are no longer in the DOM
    const allIds = new Set(
        [...document.querySelectorAll('#studentTableBody input.sop-chk[name="chk_child"]')]
            .map(cb => parseInt(cb.dataset.id))
    );
    [..._sopSelected].forEach(id => { if (!allIds.has(id)) _sopSelected.delete(id); });

    sopSyncTray();
    sopSyncCheckAll();
}

// ── Bind events after every DOM (re-)render ───────────────────────────────────
// Call this after AJAX loads new rows (same place you call setupPaginationLinks)
function sopBindTableEvents() {
    // Row checkboxes
    document.querySelectorAll('#studentTableBody input.sop-chk[name="chk_child"]').forEach(cb => {
        // Remove old listeners by cloning (simplest approach for dynamic content)
        const fresh = cb.cloneNode(true);
        cb.parentNode.replaceChild(fresh, cb);
        fresh.addEventListener('change', () => sopOnCheckboxChange(fresh));
    });

    // Restore visual state for already-selected rows
    _sopSelected.forEach(id => {
        const row = document.querySelector(`.sop-student-row[data-id="${id}"]`);
        const cb  = row ? row.querySelector('input.sop-chk[name="chk_child"]') : null;
        if (row) row.classList.add('sop-selected');
        if (cb)  cb.checked = true;
    });

    sopSyncCheckAll();
    sopAnimateRows();
}

// ── DOMContentLoaded – wire up the check-all box and initial bind ─────────────
document.addEventListener('DOMContentLoaded', function () {
    const checkAll = document.getElementById('checkAll');
    if (checkAll) {
        checkAll.addEventListener('change', function () {
            sopToggleAll(this.checked);
        });
    }

    sopBindTableEvents();
});

// ============================================================================
//  PATCH filterData() and loadPage() in your existing code:
//  At the END of the .then(html => { ... }) callback inside both functions,
//  add this line:
//
//      sopBindTableEvents();
//
//  That's all – no other changes needed to filterData() or loadPage().
// ============================================================================
// Stubs for UI symmetry
async function restoreSelected()         { /* handled per-card */ }
async function permanentDeleteSelected() { /* handled per-card */ }
</script>
