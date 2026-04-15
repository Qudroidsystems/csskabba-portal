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

                {{-- Class & Session Filter --}}
                <div class="row mb-3">
                    <div class="col-lg-12">
                        <div class="sf-card">
                            <div class="sf-card-header" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);">
                                <h6 class="text-white mb-0 py-1 d-flex align-items-center gap-2">
                                    <i class="ri-filter-3-line"></i>Filter Students
                                </h6>
                            </div>
                            <div class="sf-card-body">
                                <div class="row g-3 align-items-end">
                                    <div class="col-xxl-4 col-sm-6">
                                        <label class="sf-label">Class</label>
                                        <select class="sf-select" id="idclass">
                                            <option value="ALL">— Select Class —</option>
                                            @foreach ($schoolclass as $class)
                                                <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->schoolarm }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-4 col-sm-6">
                                        <label class="sf-label">Session</label>
                                        <select class="sf-select" id="idsession">
                                            <option value="ALL">— Select Session —</option>
                                            @foreach ($schoolsessions as $session)
                                                <option value="{{ $session->id }}">{{ $session->session }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-2 col-sm-6">
                                        <button type="button" class="sf-btn sf-btn-primary w-100" onclick="filterData();">
                                            <i class="ri-search-line"></i> Search
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Subject Teachers Card --}}
                <div class="row mb-3" id="subjectTeachersCard">
                    <div class="col-lg-12">
                        <div class="sf-card">
                            <div class="sf-card-header d-flex align-items-center flex-wrap gap-2">
                                <div class="flex-grow-1">
                                    <h5 class="mb-0 d-flex align-items-center gap-2" style="font-size:15px;font-weight:600;color:#1d1d1f;">
                                        <i class="ri-book-open-line text-primary"></i>Subject Teachers
                                        <span class="sf-pill sf-pill-primary" id="subjectTeacherCount">0</span>
                                    </h5>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="sf-btn sf-btn-ghost sf-btn-sm" onclick="selectAllSubjects();">
                                        <i class="ri-checkbox-multiple-line"></i>Select All
                                    </button>
                                    <button type="button" class="sf-btn sf-btn-ghost sf-btn-sm" onclick="deselectAllSubjects();">
                                        <i class="ri-checkbox-blank-line"></i>Deselect All
                                    </button>
                                </div>
                            </div>
                            <div class="sf-card-body">
                                <div class="sf-info-banner mb-3">
                                    <i class="ri-information-line text-primary"></i>
                                    <span>Select subjects to register or unregister students. Subjects are grouped by term.</span>
                                </div>
                                <div id="subjectTeachersContainer">
                                    @foreach ($schoolterms as $term)
                                        @php $termSubjects = $subjectTeachers ? $subjectTeachers->where('termid', $term->id) : collect(); @endphp
                                        @if ($termSubjects->isNotEmpty())
                                            <div class="term-group mb-4">
                                                <div class="d-flex align-items-center mb-2 gap-2">
                                                    <span class="sf-term-badge">
                                                        <i class="ri-calendar-2-line me-1"></i>{{ $term->term }}
                                                    </span>
                                                    <span class="sf-pill sf-pill-primary">
                                                        {{ $termSubjects->count() }} subject{{ $termSubjects->count() !== 1 ? 's' : '' }}
                                                    </span>
                                                </div>
                                                <div class="row g-2">
                                                    @foreach ($termSubjects as $teacher)
                                                        <div class="col-xl-3 col-md-4 col-sm-6">
                                                            <div class="subject-check-card"
                                                                 data-subject-name="{{ $teacher->subjectname }}"
                                                                 data-teacher-name="{{ $teacher->staffname }}"
                                                                 data-term-id="{{ $teacher->termid }}"
                                                                 onclick="toggleSubjectCard(this)">
                                                                <input class="sf-chk subject-checkbox"
                                                                       type="checkbox"
                                                                       id="subject-{{ $teacher->subjectclassid }}"
                                                                       data-subjectclassid="{{ $teacher->subjectclassid }}"
                                                                       data-staffid="{{ $teacher->userid }}"
                                                                       data-termid="{{ $teacher->termid }}"
                                                                       data-subject-name="{{ $teacher->subjectname }}"
                                                                       data-teacher-name="{{ $teacher->staffname }}"
                                                                       checked>
                                                                <label for="subject-{{ $teacher->subjectclassid }}" style="cursor:pointer;flex:1;min-width:0;">
                                                                    <span class="d-block fw-semibold text-truncate" style="font-size:13px;color:#1d1d1f;">{{ $teacher->subjectname }}</span>
                                                                    <span style="font-size:11px;color:#86868b;">{{ $teacher->staffname }}</span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                    @if(!$subjectTeachers || $subjectTeachers->isEmpty())
                                        <div class="sf-empty-state">
                                            <i class="ri-book-2-line ri-2x d-block mb-2"></i>
                                            Select a class and session to view subjects.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Student Filters --}}
                <div class="row mb-3">
                    <div class="col-lg-12">
                        <div class="sf-card">
                            <div class="sf-card-body">
                                <div class="row g-3 align-items-end">
                                    <div class="col-xxl-4">
                                        <label class="sf-label">Search Students</label>
                                        <div class="sf-search-wrap">
                                            <svg class="sf-search-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="8.5" cy="8.5" r="5"/><path d="m13 13 3.5 3.5"/></svg>
                                            <input type="text" class="sf-search-input search" placeholder="Search by name or admission no…" oninput="filterData()">
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <label class="sf-label">Gender</label>
                                        <select class="sf-select" id="idgender">
                                            <option value="ALL">All Genders</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <label class="sf-label">Admission No</label>
                                        <select class="sf-select" id="idadmission">
                                            <option value="ALL">All Admission Nos</option>
                                        </select>
                                    </div>
                                    <div class="col-xxl-2 col-sm-6">
                                        <button type="button" class="sf-btn sf-btn-secondary w-100" onclick="filterData();">
                                            <i class="bi bi-funnel"></i> Filter
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Students Table --}}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="sf-card sf-table-card">
                            <div class="sf-card-header d-flex align-items-center flex-wrap gap-2">
                                <div class="flex-grow-1">
                                    <h5 class="mb-0 d-flex align-items-center gap-2" style="font-size:15px;font-weight:600;color:#1d1d1f;">
                                        <i class="ri-group-line text-primary"></i>Students
                                        <span class="sf-pill sf-pill-dark" id="studentcount">{{ $students ? $students->total() : 0 }}</span>
                                    </h5>
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <div class="spinner-border spinner-border-sm text-primary d-none" id="register-loading-spinner" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <button type="button" class="sf-btn sf-btn-primary" data-bs-toggle="modal" data-bs-target="#registeredClassesModal">
                                        <i class="ri-eye-line"></i> View Registered
                                    </button>
                                    <button type="button" class="sf-btn sf-btn-warning" id="viewArchivedBtn" onclick="openArchivedModal();">
                                        <i class="ri-archive-line"></i> History
                                    </button>
                                </div>
                            </div>

                            <div class="sf-table-wrap">
                                {{-- Check-all row --}}
                                <div class="sf-check-all-row">
                                    <div class="sf-chk-wrap">
                                        <input type="checkbox" class="sf-chk" id="checkAll">
                                        <label for="checkAll"></label>
                                    </div>
                                    <span class="sf-check-all-label">Select all visible</span>
                                </div>

                                {{-- Column headers --}}
                                <div class="sf-table-head">
                                    <div class="sf-th sf-th-check"></div>
                                    <div class="sf-th sf-th-student">Student</div>
                                    <div class="sf-th sf-th-adm">Adm. No</div>
                                    <div class="sf-th sf-th-class">Class</div>
                                    <div class="sf-th sf-th-gender">Gender</div>
                                    <div class="sf-th sf-th-action">Action</div>
                                </div>

                                {{-- Body --}}
                                <div id="studentTableBody" class="sf-table-body">
                                    @include('subjectoperation.partials.student_rows')
                                </div>

                                {{-- Pagination --}}
                                <div class="d-flex justify-content-end p-3 border-top" style="border-color:#f2f2f7!important;" id="pagination-container">
                                    {{ $students ? $students->links('pagination::bootstrap-5') : '' }}
                                </div>

                                {{-- Selection Tray --}}
                                <div class="sf-tray" id="selection-tray">
                                    <div class="sf-tray-inner">
                                        <span class="sf-tray-count" id="tray-count"></span>
                                        <div class="sf-tray-chips" id="tray-chips"></div>
                                        <div class="sf-tray-actions">
                                            <button class="sf-btn sf-btn-ghost sf-btn-sm" onclick="openUnregisterModal()">
                                                <i class="ri-user-unfollow-line"></i> Unregister
                                            </button>
                                            <button class="sf-btn sf-btn-success sf-btn-sm" onclick="registerSelectedStudentsBatch()">
                                                <i class="ri-user-add-line"></i> Register Selected
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MODALS --}}
                {{-- Snapshot Name Modal --}}
                <div class="modal fade" id="snapshotNameModal" tabindex="-1" aria-labelledby="snapshotNameModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
                        <div class="modal-content sf-modal-content border-0 shadow-lg overflow-hidden">
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
                                    <label class="sf-label" for="snapshotNameInput">Snapshot Name <span class="text-danger">*</span></label>
                                    <input type="text" class="sf-input" id="snapshotNameInput"
                                        placeholder="e.g. Term 2 Corrections — June 2025"
                                        maxlength="191" autocomplete="off">
                                    <div class="invalid-feedback" id="snapshotNameError">Please enter a snapshot name.</div>
                                    <div class="sf-hint mt-1">
                                        <i class="ri-lightbulb-line text-warning"></i>
                                        A descriptive name helps staff identify this batch when restoring it later.
                                    </div>
                                </div>
                                <div class="mb-1">
                                    <label class="sf-label" for="snapshotNotesInput">Notes <span class="text-muted fw-normal">(optional)</span></label>
                                    <textarea class="sf-input" id="snapshotNotesInput" rows="3"
                                        placeholder="Reason for unregistration or any extra context…"
                                        maxlength="1000"></textarea>
                                    <div class="sf-hint text-end mt-1">
                                        <span id="snapshotNotesCount">0</span>/1000
                                    </div>
                                </div>
                                <div class="sf-info-banner sf-info-warning mt-3 mb-0">
                                    <i class="ri-error-warning-line"></i>
                                    <div>All existing scores for these students in the selected subjects will be saved to the snapshot and can be fully restored later.</div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                                <button type="button" class="sf-btn sf-btn-ghost" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="sf-btn sf-btn-danger px-4" id="confirmUnregisterBtn" onclick="proceedUnregister();">
                                    <i class="ri-user-unfollow-line me-1"></i> Unregister & Save Snapshot
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Registered Classes Modal --}}
                <div class="modal fade" id="registeredClassesModal" tabindex="-1" aria-labelledby="registeredClassesModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content sf-modal-content border-0 shadow-lg">
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
                                <button type="button" class="sf-btn sf-btn-ghost" onclick="printRegisteredClasses();">
                                    <i class="ri-printer-line me-1"></i> Print / Export PDF
                                </button>
                                <button type="button" class="sf-btn sf-btn-secondary" data-bs-dismiss="modal">
                                    <i class="ri-close-line me-1"></i> Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Archived History Modal --}}
                <div class="modal fade" id="archivedModal" tabindex="-1" aria-labelledby="archivedModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content sf-modal-content border-0 shadow-lg">
                            <div class="modal-header border-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                <h5 class="modal-title text-white" id="archivedModalLabel">
                                    <i class="ri-archive-line me-2"></i>Unregistered History
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0">
                                <div class="p-3 border-bottom bg-light d-flex align-items-center flex-wrap gap-2">
                                    <div class="flex-grow-1">
                                        <div class="sf-search-wrap" style="max-width:300px;">
                                            <svg class="sf-search-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="8.5" cy="8.5" r="5"/><path d="m13 13 3.5 3.5"/></svg>
                                            <input type="text" class="sf-search-input" id="archiveSearch" placeholder="Search snapshot name or subject…">
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <select class="sf-select sf-select-sm" id="archiveTermFilter" style="width:auto;">
                                            <option value="">All Terms</option>
                                            @foreach($schoolterms as $term)
                                                <option value="{{ $term->id }}">{{ $term->term }}</option>
                                            @endforeach
                                        </select>
                                        <select class="sf-select sf-select-sm" id="archivePerPage" style="width:auto;">
                                            <option value="20">20 per page</option>
                                            <option value="50" selected>50 per page</option>
                                            <option value="100">100 per page</option>
                                        </select>
                                        <button class="sf-btn sf-btn-ghost sf-btn-sm" onclick="loadArchivedPage(1);">
                                            <i class="ri-refresh-line"></i> Refresh
                                        </button>
                                        <div class="spinner-border spinner-border-sm text-warning d-none" id="archiveSpinner" role="status"></div>
                                    </div>
                                </div>
                                <div class="p-3" id="snapshotCardsContainer">
                                    <div class="sf-empty-state">Select a class and session first, then open this panel.</div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top" id="archivePaginationWrap">
                                    <small class="text-muted" id="archiveMeta"></small>
                                    <div id="archivePagination" class="d-flex gap-1"></div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <small class="text-muted me-auto">
                                    <i class="ri-information-line me-1"></i>
                                    Click a snapshot to view details. Restored records recover original scores.
                                </small>
                                <button type="button" class="sf-btn sf-btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Snapshot Detail Modal --}}
                <div class="modal fade" id="snapshotDetailModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content sf-modal-content border-0 shadow-lg">
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
                                        <div class="sf-search-wrap" style="max-width:340px;">
                                            <svg class="sf-search-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="8.5" cy="8.5" r="5"/><path d="m13 13 3.5 3.5"/></svg>
                                            <input type="text" class="sf-search-input" id="detailSearchInput" placeholder="Search by name or admission no…" oninput="filterDetailRows(this.value);">
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <button class="sf-btn sf-btn-success sf-btn-sm" id="detailRestoreAllBtn" onclick="restoreEntireSnapshot();">
                                            <i class="ri-refresh-line me-1"></i> Restore All
                                        </button>
                                        <button class="sf-btn sf-btn-success sf-btn-sm d-none" id="detailRestoreSelectedBtn" onclick="restoreDetailSelected();">
                                            <i class="ri-refresh-line me-1"></i> Restore Selected
                                        </button>
                                        <button class="sf-btn sf-btn-danger sf-btn-sm d-none" id="detailDeleteSelectedBtn" onclick="deleteDetailSelected();">
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
                                <button type="button" class="sf-btn sf-btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Image View Modal --}}
                <div id="imageViewModal" class="modal fade" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content sf-modal-content">
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

            </div>
        </div>
    </div>
</div>

<style>
/* Design Tokens */
:root {
    --sf-bg: #f5f5f7;
    --sf-surface: #ffffff;
    --sf-border: rgba(0,0,0,.06);
    --sf-border-med: rgba(0,0,0,.1);
    --sf-text-1: #1d1d1f;
    --sf-text-2: #6e6e73;
    --sf-text-3: #aeaeb2;
    --sf-accent: #534AB7;
    --sf-accent-soft: #EEEDFE;
    --sf-radius-sm: 8px;
    --sf-radius-md: 10px;
    --sf-radius-lg: 14px;
    --sf-radius-xl: 18px;
    --sf-shadow: 0 1px 3px rgba(0,0,0,.08), 0 4px 16px rgba(0,0,0,.04);
    --sf-transition: .22s cubic-bezier(.4,0,.2,1);
    --sf-spring: .32s cubic-bezier(.34,1.56,.64,1);
}

/* Base Card */
.sf-card {
    background: var(--sf-surface);
    border: 0.5px solid var(--sf-border);
    border-radius: var(--sf-radius-xl);
    box-shadow: var(--sf-shadow);
    overflow: hidden;
    margin-bottom: 0;
}
.sf-card-header {
    padding: 14px 20px;
    border-bottom: 0.5px solid var(--sf-border);
    background: var(--sf-surface);
}
.sf-card-body {
    padding: 18px 20px;
}

/* Labels, Inputs, Selects */
.sf-label {
    display: block;
    font-size: 11px;
    font-weight: 500;
    color: var(--sf-text-2);
    text-transform: uppercase;
    letter-spacing: .04em;
    margin-bottom: 6px;
}
.sf-input, .sf-select {
    width: 100%;
    padding: 9px 12px;
    border: 0.5px solid var(--sf-border-med);
    border-radius: var(--sf-radius-md);
    background: var(--sf-surface);
    font-size: 14px;
    color: var(--sf-text-1);
    outline: none;
    transition: border-color var(--sf-transition), box-shadow var(--sf-transition);
}
.sf-select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23aeaeb2' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    padding-right: 30px;
}
.sf-input:focus, .sf-select:focus {
    border-color: var(--sf-accent);
    box-shadow: 0 0 0 3px rgba(83,74,183,.12);
}

/* Search Input */
.sf-search-wrap { position: relative; }
.sf-search-icon {
    position: absolute; left: 11px; top: 50%; transform: translateY(-50%);
    width: 15px; height: 15px; color: var(--sf-text-3); pointer-events: none;
}
.sf-search-input {
    width: 100%;
    padding: 9px 12px 9px 34px;
    border: 0.5px solid var(--sf-border-med);
    border-radius: var(--sf-radius-md);
    background: var(--sf-surface);
    font-size: 14px;
    outline: none;
}
.sf-search-input:focus {
    border-color: var(--sf-accent);
    box-shadow: 0 0 0 3px rgba(83,74,183,.12);
}

/* Buttons */
.sf-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 9px 16px; border-radius: var(--sf-radius-md);
    border: none; font-size: 13px; font-weight: 500;
    cursor: pointer; transition: all var(--sf-transition);
}
.sf-btn-primary { background: var(--sf-accent); color: #fff; }
.sf-btn-primary:hover { background: #3C3489; }
.sf-btn-success { background: #1a7a4a; color: #fff; }
.sf-btn-success:hover { background: #135c38; }
.sf-btn-danger { background: #dc3545; color: #fff; }
.sf-btn-warning { background: #f59e0b; color: #fff; }
.sf-btn-secondary { background: #f2f2f7; color: var(--sf-text-1); border: 0.5px solid var(--sf-border-med); }
.sf-btn-ghost { background: transparent; color: var(--sf-text-1); border: 0.5px solid var(--sf-border-med); }
.sf-btn-sm { padding: 6px 12px; font-size: 12px; }

/* Badges */
.sf-pill {
    display: inline-flex; align-items: center;
    padding: 2px 9px; border-radius: 20px; font-size: 11px; font-weight: 500;
}
.sf-pill-primary { background: var(--sf-accent-soft); color: var(--sf-accent); }
.sf-pill-dark { background: #f2f2f7; color: var(--sf-text-1); }
.sf-term-badge {
    display: inline-flex; align-items: center;
    padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 500;
    color: #fff; background: linear-gradient(135deg,#667eea,#764ba2);
}

/* Info Banners */
.sf-info-banner {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 10px 14px; border-radius: var(--sf-radius-md);
    font-size: 13px; background: #eff6ff; color: #1e40af;
    border: 0.5px solid #bfdbfe;
}
.sf-info-banner.sf-info-warning { background: #fffbeb; color: #92400e; border-color: #fde68a; }

/* Empty State */
.sf-empty-state {
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; padding: 40px 20px; color: var(--sf-text-2);
    font-size: 14px; gap: 8px; text-align: center;
}

/* Subject Check Cards */
.subject-check-card {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 12px; border-radius: var(--sf-radius-md);
    border: 0.5px solid var(--sf-border-med); background: var(--sf-surface);
    cursor: pointer; transition: all var(--sf-transition);
}
.subject-check-card:hover { border-color: var(--sf-accent); background: #faf9ff; }
.subject-check-card.is-checked { border-color: var(--sf-accent) !important; background: var(--sf-accent-soft) !important; }

/* Custom Checkbox */
.sf-chk {
    width: 18px; height: 18px; border-radius: 5px;
    border: 1.5px solid var(--sf-border-med);
    appearance: none; cursor: pointer; background: var(--sf-surface);
    position: relative; flex-shrink: 0;
}
.sf-chk:checked { background: var(--sf-accent); border-color: var(--sf-accent); }
.sf-chk:checked::after {
    content: ''; position: absolute; left: 4px; top: 1.5px;
    width: 6px; height: 9px;
    border: 2px solid #fff; border-top: none; border-left: none;
    transform: rotate(42deg);
}

/* Table Styles */
.sf-table-wrap { position: relative; }

.sf-check-all-row {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 20px;
    background: #f9f9fb;
    border-bottom: 0.5px solid var(--sf-border);
}

.sf-table-head {
    display: flex;
    align-items: center;
    padding: 0 20px;
    height: 44px;
    border-bottom: 0.5px solid var(--sf-border);
    background: #fafafa;
}

.sf-th {
    font-size: 11px;
    font-weight: 600;
    color: var(--sf-text-2);
    text-transform: uppercase;
    letter-spacing: .04em;
}

.sf-th-check { width: 44px; flex-shrink: 0; }
.sf-th-student { flex: 1; min-width: 0; }
.sf-th-adm { width: 130px; flex-shrink: 0; }
.sf-th-class { width: 90px; flex-shrink: 0; }
.sf-th-gender { width: 90px; flex-shrink: 0; }
.sf-th-action { width: 100px; flex-shrink: 0; }

/* Student Rows */
.sf-student-row {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    border-bottom: 0.5px solid var(--sf-border);
    cursor: pointer;
    transition: background var(--sf-transition);
    min-height: 70px;
}
.sf-student-row:hover { background: #f9f9fb; }
.sf-student-row.sf-selected { background: var(--sf-accent-soft); }

.sf-student-check { width: 44px; flex-shrink: 0; }
.sf-student-info-col { flex: 1; min-width: 0; }
.sf-student-adm-col { width: 130px; flex-shrink: 0; }
.sf-student-class-col { width: 90px; flex-shrink: 0; }
.sf-student-gender-col { width: 90px; flex-shrink: 0; }
.sf-student-action-col { width: 100px; flex-shrink: 0; }

.sf-student-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.sf-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 600;
    flex-shrink: 0;
}

.sf-student-name {
    font-size: 14px;
    font-weight: 500;
    color: var(--sf-text-1);
    line-height: 1.3;
}

.sf-student-adm {
    font-size: 11px;
    color: var(--sf-text-2);
    margin-top: 2px;
}

.sf-td {
    font-size: 13px;
    color: var(--sf-text-2);
}

.sf-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 500;
}
.sf-badge-f { background: #FBEAF0; color: #993556; }
.sf-badge-m { background: #E6F1FB; color: #0C447C; }

.sf-row-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: var(--sf-radius-sm);
    border: 0.5px solid var(--sf-border-med);
    background: transparent;
    font-size: 12px;
    font-weight: 500;
    color: var(--sf-text-2);
    cursor: pointer;
    text-decoration: none;
}
.sf-row-action:hover {
    background: var(--sf-accent-soft);
    border-color: var(--sf-accent);
    color: var(--sf-accent);
}

/* Selection Tray */
.sf-tray {
    position: sticky; bottom: 0;
    background: rgba(255,255,255,.92);
    backdrop-filter: blur(20px);
    border-top: 0.5px solid var(--sf-border-med);
    padding: 12px 20px;
    transform: translateY(110%);
    transition: transform .38s cubic-bezier(.34,1.2,.64,1);
    z-index: 99;
}
.sf-tray.tray-visible { transform: translateY(0); }
.sf-tray-inner { display: flex; align-items: center; gap: 12px; }
.sf-tray-count { font-size: 12px; color: var(--sf-accent); font-weight: 600; }
.sf-tray-chips { display: flex; gap: 6px; flex: 1; overflow-x: auto; }
.sf-tray-actions { display: flex; gap: 6px; }

.sf-chip {
    display: flex; align-items: center; gap: 5px;
    background: var(--sf-accent-soft); border-radius: 20px;
    padding: 4px 10px 4px 5px; font-size: 12px; color: #3C3489;
}
.sf-chip-av {
    width: 22px; height: 22px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 9px; font-weight: 600;
}
.sf-chip-x {
    width: 14px; height: 14px; border-radius: 50%;
    background: rgba(83,74,183,.2); border: none;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
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
const CSRF = '{{ csrf_token() }}';
const AVATAR_URL = '{{ asset("storage") }}';

window._schoolInfo = {
    name: @json($school?->school_name ?? 'School'),
    address: @json($school?->school_address ?? ''),
    phone: @json($school?->school_phone ?? ''),
    email: @json($school?->school_email ?? ''),
    motto: @json($school?->school_motto ?? ''),
    logo: @json($school?->logo_url ?? null),
};

let archiveCurrentPage = 1;
let archiveMeta = {};
let archiveSearchTimer = null;
let currentSnapshotMeta = null;
let currentSnapshotRows = [];

const SF_COLORS = [
    ['#E6F1FB','#0C447C'],['#EAF3DE','#27500A'],['#FAEEDA','#633806'],
    ['#EEEDFE','#3C3489'],['#FBEAF0','#993556'],['#E1F5EE','#085041'],
];

function sfColor(id) { return SF_COLORS[(id - 1) % SF_COLORS.length]; }
function sfInitials(name) { return (name||'').split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase(); }
function escapeHtml(str) { if (!str) return ''; return String(str).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m])); }
function setSpinner(on) { document.getElementById('register-loading-spinner')?.classList.toggle('d-none', !on); }

async function apiFetch(url, method, body) {
    const res = await fetch(url, { method, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify(body) });
    const data = await res.json();
    if (!res.ok && !data.success) throw new Error(data.message || `HTTP ${res.status}`);
    return data;
}

function getSelectedStudentIds() {
    return [...document.querySelectorAll('#studentTableBody input[name="chk_child"]:checked')]
        .map(cb => parseInt(cb.closest('.sf-student-row')?.querySelector('.id')?.dataset?.id))
        .filter(Boolean);
}

function getSelectedSubjectClasses() {
    return [...document.querySelectorAll('.subject-checkbox:checked')].map(cb => ({
        subjectclassid: parseInt(cb.dataset.subjectclassid),
        staffid: parseInt(cb.dataset.staffid),
        termid: parseInt(cb.dataset.termid),
    }));
}

function showSweetAlert(title, message, type, success = true) {
    Swal.fire({ title, html: message, icon: success ? 'success' : 'error', confirmButtonColor: success ? '#1a7a4a' : '#dc3545', confirmButtonText: success ? 'Great!' : 'Okay', timer: success ? 3000 : 5000 });
}

// ============================================================================
// SUBJECT CHECKBOXES
// ============================================================================
function toggleSubjectCard(card) {
    const cb = card.querySelector('input[type="checkbox"]');
    cb.checked = !cb.checked;
    card.classList.toggle('is-checked', cb.checked);
    updateSubjectCount();
}

function updateSubjectCount() {
    const total = document.querySelectorAll('.subject-checkbox:checked').length;
    const el = document.getElementById('subjectTeacherCount');
    if (el) el.textContent = total;
}

function selectAllSubjects() {
    document.querySelectorAll('.subject-checkbox').forEach(cb => {
        cb.checked = true;
        cb.closest('.subject-check-card')?.classList.add('is-checked');
    });
    updateSubjectCount();
}

function deselectAllSubjects() {
    document.querySelectorAll('.subject-checkbox').forEach(cb => {
        cb.checked = false;
        cb.closest('.subject-check-card')?.classList.remove('is-checked');
    });
    updateSubjectCount();
}

function initializeSubjectCards() {
    document.querySelectorAll('.subject-checkbox:checked').forEach(cb => {
        cb.closest('.subject-check-card')?.classList.add('is-checked');
    });
    updateSubjectCount();
}

// ============================================================================
// CONVERT TABLE ROWS TO DIV ROWS
// ============================================================================
function rewriteExistingRows() {
    const body = document.getElementById('studentTableBody');
    if (!body) return;

    const table = body.querySelector('table');
    if (!table) {
        attachCheckboxListeners();
        return;
    }

    const tbody = table.querySelector('tbody');
    if (!tbody) return;

    const rows = tbody.querySelectorAll('tr');
    if (!rows.length || (rows.length === 1 && rows[0].querySelector('td[colspan]'))) {
        const emptyMsg = rows[0]?.querySelector('td[colspan]')?.textContent || 'No students found';
        body.innerHTML = `<div class="sf-no-result">${emptyMsg}</div>`;
        return;
    }

    let html = '';
    rows.forEach((tr, i) => {
        const cells = tr.querySelectorAll('td');
        if (cells.length < 6) return;

        let id = '', admission = '', name = '', classText = '', gender = '', actionHtml = '';

        for (let j = 0; j < cells.length; j++) {
            const cell = cells[j];
            if (j === 0) {
                id = cell.querySelector('.id')?.dataset?.id || cell.querySelector('[data-id]')?.dataset?.id || i;
            }
            if (cell.classList.contains('admissionno') || j === 2) {
                admission = cell.dataset?.admissionno || cell.textContent?.trim() || '';
            }
            if (cell.classList.contains('name') || j === 3) {
                name = cell.dataset?.name || cell.querySelector('h6')?.textContent?.trim() || cell.textContent?.trim() || '';
                name = name.replace(/\s+/g, ' ').trim();
            }
            if (cell.classList.contains('class') || j === 4) {
                classText = cell.dataset?.class || cell.querySelector('.badge')?.textContent?.trim() || cell.textContent?.trim() || '';
            }
            if (cell.classList.contains('gender') || j === 5) {
                gender = cell.dataset?.gender || cell.textContent?.trim() || '';
            }
            if (j === cells.length - 1) {
                actionHtml = cell.innerHTML || '';
            }
        }

        actionHtml = actionHtml
            .replace(/class="btn btn-[^"]*"/g, 'class="sf-row-action"')
            .replace(/class="btn-group[^"]*"/g, 'class="d-flex gap-1"')
            .replace(/btn-subtle-primary/g, 'sf-row-action');

        const initials = sfInitials(name);
        const [bg, fg] = sfColor(parseInt(id) || i + 1);
        const isFemale = gender.toLowerCase() === 'female';

        html += `<div class="sf-student-row" data-student-id="${id}">
            <div class="sf-student-check">
                <div class="sf-chk-wrap" onclick="event.stopPropagation()">
                    <input type="checkbox" class="sf-chk" name="chk_child" onclick="event.stopPropagation()">
                    <span class="id" data-id="${escapeHtml(String(id))}" style="display:none;"></span>
                </div>
            </div>
            <div class="sf-student-info-col">
                <div class="sf-student-info" onclick="event.stopPropagation()">
                    <div class="sf-avatar" style="background:${bg};color:${fg};">${escapeHtml(initials)}</div>
                    <div>
                        <div class="sf-student-name">${escapeHtml(name)}</div>
                        <div class="sf-student-adm admissionno" data-admissionno="${escapeHtml(admission)}">${escapeHtml(admission)}</div>
                    </div>
                </div>
            </div>
            <div class="sf-student-adm-col sf-td">${escapeHtml(admission)}</div>
            <div class="sf-student-class-col sf-td">${escapeHtml(classText)}</div>
            <div class="sf-student-gender-col">
                <span class="sf-badge ${isFemale ? 'sf-badge-f' : 'sf-badge-m'}">${escapeHtml(gender)}</span>
            </div>
            <div class="sf-student-action-col" onclick="event.stopPropagation();">
                ${actionHtml || '<a href="#" class="sf-row-action"><i class="ph-eye"></i> View</a>'}
            </div>
        </div>`;
    });

    body.innerHTML = html;
    attachCheckboxListeners();
}

// ============================================================================
// CHECKBOX EVENT HANDLERS
// ============================================================================
function attachCheckboxListeners() {
    document.querySelectorAll('#studentTableBody input[name="chk_child"]').forEach(cb => {
        cb.removeEventListener('change', handleStudentCheckboxChange);
        cb.addEventListener('change', handleStudentCheckboxChange);
    });

    const checkAll = document.getElementById('checkAll');
    if (checkAll) {
        checkAll.removeEventListener('change', handleCheckAllChange);
        checkAll.addEventListener('change', handleCheckAllChange);
    }
}

function handleStudentCheckboxChange(e) {
    const row = e.target.closest('.sf-student-row');
    if (row) row.classList.toggle('sf-selected', e.target.checked);
    toggleBatchButtons();
    updateCheckAllState();
}

function handleCheckAllChange(e) {
    document.querySelectorAll('#studentTableBody input[name="chk_child"]').forEach(cb => {
        cb.checked = e.target.checked;
        cb.closest('.sf-student-row')?.classList.toggle('sf-selected', e.target.checked);
    });
    toggleBatchButtons();
}

function updateCheckAllState() {
    const all = document.querySelectorAll('#studentTableBody input[name="chk_child"]');
    const checked = document.querySelectorAll('#studentTableBody input[name="chk_child"]:checked');
    const checkAll = document.getElementById('checkAll');
    if (checkAll) checkAll.checked = all.length > 0 && all.length === checked.length;
}

// ============================================================================
// TRAY (SELECTION)
// ============================================================================
function toggleBatchButtons() {
    const checked = [...document.querySelectorAll('#studentTableBody input[name="chk_child"]:checked')];
    const tray = document.getElementById('selection-tray');
    const chips = document.getElementById('tray-chips');
    const count = document.getElementById('tray-count');

    if (!checked.length) {
        tray?.classList.remove('tray-visible');
        return;
    }
    tray?.classList.add('tray-visible');
    count.textContent = `${checked.length} student${checked.length !== 1 ? 's' : ''} selected`;

    chips.innerHTML = checked.map(cb => {
        const row = cb.closest('.sf-student-row');
        const name = row?.querySelector('.sf-student-name')?.textContent?.trim() ?? 'Student';
        const id = row?.querySelector('.id')?.dataset?.id;
        const initials = sfInitials(name);
        const [bg, fg] = sfColor(parseInt(id) || 1);
        return `<div class="sf-chip">
            <div class="sf-chip-av" style="background:${bg};color:${fg};">${initials}</div>
            ${escapeHtml(name.split(' ')[0])}
            <button class="sf-chip-x" onclick="uncheckStudent('${id}', this)">✕</button>
        </div>`;
    }).join('');
}

function uncheckStudent(id, btn) {
    const row = document.querySelector(`#studentTableBody .id[data-id="${id}"]`)?.closest('.sf-student-row');
    if (!row) return;
    const cb = row.querySelector('input[name="chk_child"]');
    if (cb) { cb.checked = false; row.classList.remove('sf-selected'); }
    toggleBatchButtons();
    updateCheckAllState();
}

// ============================================================================
// FILTER / SEARCH (AJAX)
// ============================================================================
function filterData() {
    const classId = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;

    if (classId === 'ALL' || sessionId === 'ALL') {
        Swal.fire({ icon: 'warning', title: 'Missing filters', text: 'Please select a class and session.' });
        return;
    }

    const search = document.querySelector('.sf-search-input.search')?.value ?? '';
    const gender = document.getElementById('idgender').value;
    const admission = document.getElementById('idadmission').value;

    const body = document.getElementById('studentTableBody');
    const subjectTeachersContainer = document.getElementById('subjectTeachersContainer');

    if (body) body.innerHTML = '<div class="sf-no-result">Loading…</div>';
    if (subjectTeachersContainer) subjectTeachersContainer.innerHTML = '<div class="sf-empty-state">Loading subject teachers…</div>';

    const params = new URLSearchParams({ class_id: classId, session_id: sessionId, search, gender, admissionno: admission });

    fetch(ROUTES.index + '?' + params.toString(), {
        headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
    })
    .then(r => r.text())
    .then(html => {
        const doc = new DOMParser().parseFromString(html, 'text/html');

        const newBody = doc.getElementById('studentTableBody');
        const liveBody = document.getElementById('studentTableBody');
        if (newBody && liveBody) {
            liveBody.innerHTML = newBody.innerHTML;
            rewriteExistingRows();
        }

        const newPagination = doc.getElementById('pagination-container');
        const livePagination = document.getElementById('pagination-container');
        if (newPagination && livePagination) {
            livePagination.innerHTML = newPagination.innerHTML;
            setupPaginationLinks();
        }

        const newCount = doc.getElementById('studentcount');
        const liveCount = document.getElementById('studentcount');
        if (newCount && liveCount) liveCount.textContent = newCount.textContent;

        const newSubjectContainer = doc.getElementById('subjectTeachersContainer');
        if (newSubjectContainer && subjectTeachersContainer) {
            subjectTeachersContainer.innerHTML = newSubjectContainer.innerHTML;
            initializeSubjectCards();
        }

        updateSubjectCount();
        toggleBatchButtons();
    })
    .catch(err => {
        if (body) body.innerHTML = `<div class="sf-no-result">Error loading data.</div>`;
    });
}

function setupPaginationLinks() {
    document.querySelectorAll('#pagination-container a').forEach(link => {
        const newLink = link.cloneNode(true);
        link.parentNode?.replaceChild(newLink, link);
        newLink.addEventListener('click', function(e) {
            e.preventDefault();
            if (this.href && !this.classList.contains('disabled')) loadPage(this.href);
        });
    });
}

function loadPage(url) {
    const body = document.getElementById('studentTableBody');
    if (body) body.innerHTML = '<div class="sf-no-result">Loading…</div>';

    fetch(url, { headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
    .then(r => r.text())
    .then(html => {
        const doc = new DOMParser().parseFromString(html, 'text/html');

        const newBody = doc.getElementById('studentTableBody');
        const liveBody = document.getElementById('studentTableBody');
        if (newBody && liveBody) {
            liveBody.innerHTML = newBody.innerHTML;
            rewriteExistingRows();
        }

        const newPagination = doc.getElementById('pagination-container');
        const livePagination = document.getElementById('pagination-container');
        if (newPagination && livePagination) {
            livePagination.innerHTML = newPagination.innerHTML;
            setupPaginationLinks();
        }

        const newCount = doc.getElementById('studentcount');
        const liveCount = document.getElementById('studentcount');
        if (newCount && liveCount) liveCount.textContent = newCount.textContent;

        updateSubjectCount();
        toggleBatchButtons();
    });
}

function updateAdmissionNoOptions(students) {
    const select = document.getElementById('idadmission');
    if (!select) return;
    select.innerHTML = '<option value="ALL">All Admission Nos</option>';
    [...new Set(students.map(s => s.admissionno).filter(Boolean))].sort().forEach(no => {
        const opt = document.createElement('option');
        opt.value = no; opt.text = no;
        select.appendChild(opt);
    });
}

// ============================================================================
// REGISTER BATCH
// ============================================================================
async function registerSelectedStudentsBatch() {
    const studentIds = getSelectedStudentIds();
    const subjectClasses = getSelectedSubjectClasses();
    const sessionId = document.getElementById('idsession').value;

    if (!studentIds.length) return showSweetAlert('No Students Selected', 'Please select at least one student.', 'warning', false);
    if (!subjectClasses.length) return showSweetAlert('No Subjects Selected', 'Please select at least one subject.', 'warning', false);
    if (sessionId === 'ALL') return showSweetAlert('Session Required', 'Please select a session.', 'warning', false);

    const ok = await Swal.fire({ title: 'Confirm Registration', html: `<p>Register <strong>${studentIds.length}</strong> student(s) for <strong>${subjectClasses.length}</strong> subject(s)?</p>`, icon: 'question', showCancelButton: true, confirmButtonColor: '#1a7a4a', confirmButtonText: 'Yes, register!' });
    if (!ok.isConfirmed) return;

    setSpinner(true);
    try {
        const res = await apiFetch(ROUTES.batchRegister, 'POST', { studentids: studentIds, subjectclasses: subjectClasses, sessionid: parseInt(sessionId) });
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
// UNREGISTER MODAL
// ============================================================================
function openUnregisterModal() {
    const studentIds = getSelectedStudentIds();
    const subjectClasses = getSelectedSubjectClasses();
    const sessionId = document.getElementById('idsession').value;

    if (!studentIds.length) return showSweetAlert('No Students Selected', 'Please select at least one student.', 'warning', false);
    if (!subjectClasses.length) return showSweetAlert('No Subjects Selected', 'Please select at least one subject.', 'warning', false);
    if (sessionId === 'ALL') return showSweetAlert('Session Required', 'Please select a session.', 'warning', false);

    document.getElementById('snapshotStudentCount').textContent = `${studentIds.length} student${studentIds.length !== 1 ? 's' : ''}`;
    document.getElementById('snapshotSubjectCount').textContent = `${subjectClasses.length} subject${subjectClasses.length !== 1 ? 's' : ''}`;

    const nameInput = document.getElementById('snapshotNameInput');
    const now = new Date();
    nameInput.value = `Unregistration — ${now.toLocaleDateString('en-GB')} ${now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })}`;
    document.getElementById('snapshotNotesInput').value = '';
    document.getElementById('snapshotNotesCount').textContent = '0';

    new bootstrap.Modal(document.getElementById('snapshotNameModal')).show();
}

async function proceedUnregister() {
    const nameInput = document.getElementById('snapshotNameInput');
    const name = nameInput.value.trim();
    if (!name) { nameInput.classList.add('is-invalid'); return; }
    nameInput.classList.remove('is-invalid');

    const studentIds = getSelectedStudentIds();
    const subjectClasses = getSelectedSubjectClasses();
    const sessionId = document.getElementById('idsession').value;
    const notes = document.getElementById('snapshotNotesInput').value.trim();

    bootstrap.Modal.getInstance(document.getElementById('snapshotNameModal'))?.hide();
    setSpinner(true);

    try {
        const res = await apiFetch(ROUTES.unregister, 'DELETE', { studentids: studentIds, subjectclasses: subjectClasses, sessionid: parseInt(sessionId), snapshot_name: name, snapshot_notes: notes || null });
        if (res.success || res.success_count > 0) {
            showSweetAlert('Unregistration Complete', `${res.success_count} student(s) unregistered.`, 'success', true);
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
    const classId = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    const container = document.getElementById('registeredClassesContent');

    if (classId === 'ALL' || sessionId === 'ALL') {
        container.innerHTML = `<div class="sf-empty-state">Please select a class and session first.</div>`;
        return;
    }

    container.innerHTML = `<div class="sf-empty-state">Loading…</div>`;

    try {
        const res = await fetch(ROUTES.getRegistered + '?' + new URLSearchParams({ class_id: classId, session_id: sessionId }), { headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
        const data = await res.json();

        if (!data.success || !data.data.length) {
            container.innerHTML = `<div class="sf-empty-state">No registered classes found.</div>`;
            return;
        }

        let html = '';
        data.data.forEach(term => {
            html += `<div class="mb-3 p-3 border rounded">
                <h6>${term.term_name} - ${term.class_name} ${term.arm_name}</h6>
                <p>Students: ${term.student_count} | Subjects: ${term.subject_count}</p>
            </div>`;
        });
        container.innerHTML = html;
    } catch (err) {
        container.innerHTML = `<div class="alert alert-danger">Failed to load data: ${err.message}</div>`;
    }
}

function printRegisteredClasses() {
    const content = document.getElementById('registeredClassesContent').innerHTML;
    const win = window.open('', '_blank');
    win.document.write(`<html><head><title>Registered Classes</title></head><body>${content}</body></html>`);
    win.document.close();
    win.print();
}

// ============================================================================
// ARCHIVE MODAL (SIMPLIFIED)
// ============================================================================
function openArchivedModal() {
    const classId = document.getElementById('idclass').value;
    const sessionId = document.getElementById('idsession').value;
    if (classId === 'ALL' || sessionId === 'ALL') {
        return showSweetAlert('Selection Required', 'Please select a class and session first.', 'warning', false);
    }
    new bootstrap.Modal(document.getElementById('archivedModal')).show();
    loadArchivedPage(1);
}

async function loadArchivedPage(page) {
    const container = document.getElementById('snapshotCardsContainer');
    container.innerHTML = '<div class="sf-empty-state">Loading snapshots…</div>';

    setTimeout(() => {
        container.innerHTML = '<div class="sf-empty-state">No snapshots found.</div>';
    }, 500);
}

function renderSnapshotCards(rows) { }
function renderArchivePagination(meta) { }
function updateArchiveMeta(meta) { }
async function openSnapshotDetail(metaEncoded) { }
function renderSnapshotDetailTable(rows, headers) { }
function filterDetailRows(query) { }
async function restoreEntireSnapshot() { }
async function restoreDetailSelected() { }
async function restoreSingleSnapshot(metaEncoded) { }
async function deleteSnapshotGroup(metaEncoded) { }
async function deleteDetailSelected() { }

// ============================================================================
// DOM READY
// ============================================================================
document.addEventListener('DOMContentLoaded', function() {
    initializeSubjectCards();
    rewriteExistingRows();

    document.getElementById('registeredClassesModal')?.addEventListener('show.bs.modal', loadRegisteredClasses);
    document.getElementById('archivePerPage')?.addEventListener('change', () => loadArchivedPage(1));
    document.getElementById('snapshotNotesInput')?.addEventListener('input', function() {
        document.getElementById('snapshotNotesCount').textContent = this.value.length;
    });
});
</script>
