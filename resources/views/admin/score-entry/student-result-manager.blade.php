@extends('layouts.master')

@section('content')
<style>
    .student-result-manager { padding: 0; }

    .srm-hero {
        background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #7c3aed 100%);
        border-radius: 12px; padding: 28px 32px; margin-bottom: 24px; color: white;
    }
    .srm-hero-actions { margin-top: 20px; display: flex; gap: 12px; flex-wrap: wrap; }
    .btn-hero {
        background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3);
        color: white; padding: 8px 20px; border-radius: 8px; font-size: 14px;
        font-weight: 500; transition: all 0.2s; text-decoration: none;
        display: inline-flex; align-items: center; gap: 8px;
    }
    .btn-hero:hover { background: rgba(255,255,255,0.3); color: white; transform: translateY(-2px); }

    .filter-card {
        background: #fff; border-radius: 12px; border: 1px solid #e2e8f0;
        padding: 20px 24px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .stats-dashboard {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px; margin-bottom: 28px;
    }
    .stat-card-enhanced {
        background: #fff; border-radius: 16px; border: 1px solid #e2e8f0;
        overflow: hidden; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .stat-card-enhanced:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.1); }
    .stat-card-header { padding: 16px 20px 0 20px; display: flex; align-items: center; justify-content: space-between; }
    .stat-card-header h3 { font-size: 14px; font-weight: 600; color: #64748b; margin: 0; text-transform: uppercase; letter-spacing: 0.5px; }
    .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
    .stat-card-body { padding: 8px 20px 20px 20px; }
    .stat-main-value { font-size: 36px; font-weight: 800; color: #1e293b; line-height: 1.2; margin-bottom: 4px; }
    .stat-footer { background: #f8fafc; padding: 12px 20px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; }

    .students-table-card {
        background: #fff; border-radius: 16px; border: 1px solid #e2e8f0;
        overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .card-header-custom {
        background: #f8fafc; padding: 16px 20px; border-bottom: 1px solid #e2e8f0;
        display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;
    }
    .search-input-wrapper { position: relative; width: 280px; }
    .search-input-wrapper i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
    .search-input-custom {
        padding-left: 36px; border-radius: 10px; border: 1px solid #e2e8f0; height: 40px; width: 100%;
    }
    .search-input-custom:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }

    .students-table { width: 100%; border-collapse: collapse; }
    .students-table th { background: #1e3a5f; color: white; padding: 14px 16px; font-weight: 600; font-size: 13px; }
    .students-table td { padding: 12px 16px; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
    .students-table tbody tr { transition: background 0.2s; cursor: pointer; }
    .students-table tbody tr:hover { background: #f8fafc; }

    .student-avatar {
        width: 40px; height: 40px; border-radius: 50%;
        background: linear-gradient(135deg, #2563eb, #4f46e5);
        color: white; display: flex; align-items: center; justify-content: center;
        font-weight: bold; font-size: 16px; flex-shrink: 0;
    }
    .student-avatar img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }

    .status-badge { display: inline-flex; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .status-complete   { background: #dcfce7; color: #15803d; }
    .status-incomplete { background: #fef3c7; color: #b45309; }
    .status-pending    { background: #fee2e2; color: #dc2626; }

    .btn-sm-custom { padding: 6px 14px; font-size: 12px; border-radius: 8px; border: none; cursor: pointer; transition: all 0.2s; }
    .btn-primary-custom { background: #2563eb; color: white; }
    .btn-primary-custom:hover { background: #1d4ed8; transform: translateY(-1px); }

    .empty-state { text-align: center; padding: 60px 20px; }
    .empty-state i { font-size: 48px; color: #cbd5e1; margin-bottom: 16px; }

    /* Modal */
    .modal-xl-custom { max-width: 90%; width: 1200px; }
    .modal-header-custom {
        background: linear-gradient(135deg, #1e3a5f, #2563eb);
        color: white; padding: 20px 24px; border-radius: 12px 12px 0 0;
    }
    .student-info-header {
        display: flex; align-items: center; gap: 24px; padding: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px; color: white; margin-bottom: 24px; flex-wrap: wrap;
    }
    .student-avatar-large {
        width: 80px; height: 80px; border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: flex; align-items: center; justify-content: center;
        font-size: 32px; font-weight: bold; flex-shrink: 0;
    }
    .student-avatar-large img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
    .summary-stats { display: flex; gap: 16px; margin-top: 12px; flex-wrap: wrap; }
    .stat-box { background: rgba(255,255,255,0.15); border-radius: 10px; padding: 8px 16px; text-align: center; min-width: 90px; }
    .stat-box .label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.8; }
    .stat-box .value { font-size: 20px; font-weight: bold; }

    .subject-score-card {
        background: #f8fafc; border-radius: 12px; padding: 16px;
        margin-bottom: 20px; border: 1px solid #e2e8f0;
    }
    .subject-header {
        background: #1e3a5f; color: white; padding: 12px 16px; border-radius: 8px;
        margin-bottom: 16px; display: flex; justify-content: space-between;
        align-items: center; flex-wrap: wrap; gap: 12px;
    }
    .assessment-row {
        display: flex; align-items: center; gap: 16px; margin-bottom: 10px;
        padding: 10px 12px; background: white; border-radius: 8px; flex-wrap: wrap;
    }
    .assessment-label { width: 140px; font-weight: 600; color: #475569; }
    .assessment-input { flex: 1; max-width: 130px; }
    .assessment-input input {
        width: 100%; padding: 8px 12px; border: 1.5px solid #e2e8f0;
        border-radius: 8px; text-align: center; font-size: 14px;
    }
    .assessment-input input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
    .assessment-score { width: 80px; font-weight: 600; color: #2563eb; }
    .subject-total {
        margin-top: 12px; padding-top: 12px; border-top: 1px solid #e2e8f0;
        display: flex; justify-content: flex-end; gap: 24px; font-weight: 600;
    }
    .btn-save-subject {
        background: #10b981; color: white; border: none; padding: 6px 16px;
        border-radius: 8px; cursor: pointer; transition: all 0.2s; font-size: 13px; font-weight: 500;
    }
    .btn-save-subject:hover { background: #059669; transform: translateY(-1px); }
    .btn-save-subject:disabled { background: #9ca3af; cursor: not-allowed; transform: none; }

    .loading-spinner {
        display: inline-block; width: 16px; height: 16px;
        border: 2px solid #f3f3f3; border-top: 2px solid #2563eb;
        border-radius: 50%; animation: spin 1s linear infinite;
    }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

    .modal-body-scroll { max-height: 65vh; overflow-y: auto; padding: 20px; }
    .modal-body-scroll::-webkit-scrollbar { width: 6px; }
    .modal-body-scroll::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 3px; }
    .modal-body-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }

    .no-subjects-msg { background: #fef3c7; border: 1px solid #fde68a; border-radius: 8px; padding: 16px; color: #92400e; text-align: center; }

    @media (max-width: 768px) {
        .stats-dashboard { grid-template-columns: repeat(2, 1fr); gap: 12px; }
        .stat-main-value { font-size: 28px; }
        .students-table { font-size: 12px; }
        .students-table th, .students-table td { padding: 8px 10px; }
        .assessment-row { flex-direction: column; align-items: flex-start; }
        .assessment-label { width: 100%; }
        .assessment-input { max-width: 100%; width: 100%; }
        .search-input-wrapper { width: 100%; }
        .card-header-custom { flex-direction: column; }
        .modal-xl-custom { max-width: 95%; }
    }
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">
<div class="student-result-manager">

    <div class="srm-hero">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1 class="mb-2"><i class="ri-user-settings-line me-2"></i>Student Result Manager</h1>
                <p class="mb-0">Enter or edit results for individual students across all their registered subjects.</p>
                <p class="mt-2 mb-0"><i class="ri-information-line me-1"></i> Select a student to view all subjects and enter scores.</p>
            </div>
            <div class="srm-hero-actions">
                <a href="{{ route('admin.score-entry.index') }}" class="btn-hero">
                    <i class="ri-arrow-left-line"></i> Back to Teacher View
                </a>
                <a href="{{ route('admin.score-entry.lock-management') }}" class="btn-hero">
                    <i class="ri-shield-lock-line"></i> Lock Manager
                </a>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="filter-card">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Class <span class="text-danger">*</span></label>
                <select id="classFilter" class="form-select">
                    <option value="">— Select Class —</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">
                            {{ $class->schoolclass }} {{ $class->armRelation->arm ?? $class->arm ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Term <span class="text-danger">*</span></label>
                <select id="termFilter" class="form-select">
                    <option value="">— Select Term —</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}">{{ $term->term }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Session <span class="text-danger">*</span></label>
                <select id="sessionFilter" class="form-select">
                    <option value="">— Select Session —</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}">{{ $session->session }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-primary w-100" id="loadStudentsBtn">
                    <i class="ri-user-search-line me-1"></i> Load Students
                </button>
            </div>
        </div>
    </div>

    {{-- Stats Dashboard --}}
    <div class="stats-dashboard" id="statsDashboard" style="display: none;">
        <div class="stat-card-enhanced">
            <div class="stat-card-header">
                <h3>Total Students</h3>
                <div class="stat-icon" style="background: #dbeafe; color: #2563eb;"><i class="ri-user-line"></i></div>
            </div>
            <div class="stat-card-body">
                <div class="stat-main-value" id="statTotalStudents">0</div>
                <div class="text-muted small">Enrolled this term</div>
            </div>
            <div class="stat-footer"><i class="ri-calendar-line me-1"></i> Current term enrollment</div>
        </div>
        <div class="stat-card-enhanced">
            <div class="stat-card-header">
                <h3>Complete Records</h3>
                <div class="stat-icon" style="background: #dcfce7; color: #10b981;"><i class="ri-check-double-line"></i></div>
            </div>
            <div class="stat-card-body">
                <div class="stat-main-value text-success" id="statCompleteRecords">0</div>
                <div class="text-muted small">All subjects entered</div>
            </div>
            <div class="stat-footer">
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-success" id="completionProgress" style="width: 0%"></div>
                </div>
                <small class="mt-2 d-block" id="completionText">0% completion rate</small>
            </div>
        </div>
        <div class="stat-card-enhanced">
            <div class="stat-card-header">
                <h3>Avg. Performance</h3>
                <div class="stat-icon" style="background: #fef3c7; color: #f59e0b;"><i class="ri-bar-chart-line"></i></div>
            </div>
            <div class="stat-card-body">
                <div class="stat-main-value" id="statAvgScore">0</div>
                <div class="text-muted small">Class average score</div>
            </div>
            <div class="stat-footer"><i class="ri-trending-up-line me-1"></i> Across all subjects</div>
        </div>
        <div class="stat-card-enhanced">
            <div class="stat-card-header">
                <h3>Avg. GPA</h3>
                <div class="stat-icon" style="background: #e0e7ff; color: #4f46e5;"><i class="ri-star-fill"></i></div>
            </div>
            <div class="stat-card-body">
                <div class="stat-main-value" id="statAvgGPA">0.00</div>
                <div class="text-muted small">Class GPA</div>
            </div>
            <div class="stat-footer"><i class="ri-information-line me-1"></i> 5.0 Scale</div>
        </div>
    </div>

    {{-- Students Table --}}
    <div class="students-table-card">
        <div class="card-header-custom">
            <h5 class="mb-0"><i class="ri-group-line me-2"></i>Students List</h5>
            <div class="search-input-wrapper">
                <i class="ri-search-line"></i>
                <input type="text" id="studentSearchInput" class="search-input-custom" placeholder="Search by name or admission...">
            </div>
        </div>
        <div style="overflow-x: auto;">
            <table class="students-table">
                <thead>
                    <tr>
                        <th style="width: 60px">Photo</th>
                        <th>Admission No</th>
                        <th>Student Name</th>
                        <th>Average Score</th>
                        <th>Grade</th>
                        <th>GPA</th>
                        <th>Status</th>
                        <th style="width: 120px">Action</th>
                    </tr>
                </thead>
                <tbody id="studentsTableBody">
                    <tr>
                        <td colspan="8" class="empty-state">
                            <i class="ri-filter-line"></i>
                            <h5>Select Class, Term, and Session</h5>
                            <p class="text-muted">Choose a class, term, and session then click "Load Students"</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
</div>
</div>
</div>

{{-- Student Results Modal --}}
<div class="modal fade" id="studentResultsModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl-custom">
        <div class="modal-content">
            <div class="modal-header-custom">
                <h5 class="modal-title"><i class="ri-file-list-line me-2"></i>Student Results Entry</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body-scroll">
                <div id="modalStudentInfo"></div>
                <div id="modalSubjectsContainer"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveAllSubjectsBtn">
                    <i class="ri-save-line me-1"></i> Save All Changes
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
/* ─── State ─────────────────────────────────────────────────────────────── */
let currentStudentData  = null;
let currentAssessments  = [];
let allStudentsData     = [];
let currentFilters      = { class_id: null, term_id: null, session_id: null };

const ROUTES = {
    getStudents  : '{{ route("admin.score-entry.get-student-results") }}',
    updateSubject: '{{ route("admin.score-entry.update-student-subject-score") }}',
    bulkUpdate   : '{{ route("admin.score-entry.bulk-update-student-scores") }}',
};

/* ─── DOM refs ──────────────────────────────────────────────────────────── */
const classFilter        = document.getElementById('classFilter');
const termFilter         = document.getElementById('termFilter');
const sessionFilter      = document.getElementById('sessionFilter');
const loadStudentsBtn    = document.getElementById('loadStudentsBtn');
const studentSearchInput = document.getElementById('studentSearchInput');

/* ─── Event wiring ──────────────────────────────────────────────────────── */
loadStudentsBtn.addEventListener('click', loadStudents);
document.getElementById('saveAllSubjectsBtn').addEventListener('click', saveAllSubjects);
studentSearchInput.addEventListener('input', filterStudents);

/* ─── Load students ─────────────────────────────────────────────────────── */
async function loadStudents() {
    const classId   = classFilter.value;
    const termId    = termFilter.value;
    const sessionId = sessionFilter.value;

    if (!classId || !termId || !sessionId) {
        Swal.fire('Warning', 'Please select Class, Term, and Session', 'warning');
        return;
    }

    currentFilters = { class_id: classId, term_id: termId, session_id: sessionId };

    // FIX: disable button while loading, re-enable in finally
    const origHtml = loadStudentsBtn.innerHTML;
    loadStudentsBtn.disabled = true;
    loadStudentsBtn.innerHTML = '<span class="loading-spinner"></span> Loading...';

    try {
        const response = await fetch(ROUTES.getStudents, {
            method : 'POST',
            headers: {
                'Content-Type' : 'application/json',
                'X-CSRF-TOKEN' : '{{ csrf_token() }}',
                'Accept'       : 'application/json',
            },
            body: JSON.stringify(currentFilters),
        });

        const result = await response.json();

        if (result.success) {
            allStudentsData    = result.data;
            currentAssessments = result.assessments || [];
            renderStudentsTable(allStudentsData);
            updateStats(allStudentsData);
            document.getElementById('statsDashboard').style.display = 'grid';
            Swal.fire({ icon: 'success', title: 'Loaded', text: `${allStudentsData.length} student(s) loaded.`, timer: 1500, showConfirmButton: false });
        } else {
            Swal.fire('Error', result.message || 'Failed to load students', 'error');
            renderEmptyTable('No students found. Check that students are registered for the selected class/session.');
            document.getElementById('statsDashboard').style.display = 'none';
        }
    } catch (error) {
        console.error('loadStudents error:', error);
        Swal.fire('Error', 'Network error. Please try again.', 'error');
        renderEmptyTable();
        document.getElementById('statsDashboard').style.display = 'none';
    } finally {
        // Always re-enable the button
        loadStudentsBtn.disabled  = false;
        loadStudentsBtn.innerHTML = origHtml;
    }
}

/* ─── Stats ─────────────────────────────────────────────────────────────── */
function updateStats(students) {
    let completeRecords = 0, totalAverage = 0, totalGPA = 0;
    students.forEach(s => {
        const completedSubjects = (s.subjects || []).filter(sub => sub.total > 0).length;
        if (completedSubjects === s.total_subjects && s.total_subjects > 0) completeRecords++;
        totalAverage += s.average || 0;
        totalGPA     += s.gpa     || 0;
    });
    const n               = students.length || 1;
    const completionRate  = Math.round((completeRecords / students.length) * 100) || 0;

    document.getElementById('statTotalStudents').textContent  = students.length;
    document.getElementById('statCompleteRecords').textContent= completeRecords;
    document.getElementById('statAvgScore').textContent       = (totalAverage / n).toFixed(2);
    document.getElementById('statAvgGPA').textContent         = (totalGPA     / n).toFixed(2);
    document.getElementById('completionProgress').style.width = completionRate + '%';
    document.getElementById('completionText').textContent     = completionRate + '% completion rate';
}

/* ─── Filter / search ───────────────────────────────────────────────────── */
function filterStudents() {
    const q = studentSearchInput.value.toLowerCase().trim();
    if (!q) { renderStudentsTable(allStudentsData); return; }
    const filtered = allStudentsData.filter(s =>
        s.full_name.toLowerCase().includes(q) ||
        (s.admission_no && s.admission_no.toLowerCase().includes(q))
    );
    renderStudentsTable(filtered);
}

/* ─── Render students table ─────────────────────────────────────────────── */
function renderStudentsTable(students) {
    const tbody = document.getElementById('studentsTableBody');
    if (!students || students.length === 0) {
        tbody.innerHTML = `
            <tr><td colspan="8" class="empty-state">
                <i class="ri-user-unfollow-line"></i>
                <h5>No students found</h5>
                <p class="text-muted">Try adjusting your search or filters</p>
            </td></tr>`;
        return;
    }
    tbody.innerHTML = students.map(s => `
        <tr onclick="openStudentModal(${s.student_id})" style="cursor:pointer;">
            <td>
                <div class="student-avatar">
                    ${s.photo
                        ? `<img src="${s.photo}" alt="Photo" onerror="this.style.display='none'">`
                        : escapeHtml(getInitials(s.full_name))
                    }
                </div>
            </td>
            <td><strong>${escapeHtml(s.admission_no || 'N/A')}</strong></td>
            <td>${escapeHtml(s.full_name)}</td>
            <td><strong>${s.average || 0}</strong></td>
            <td><span class="status-badge ${getGradeClass(s.average_grade)}">${s.average_grade || 'F'}</span></td>
            <td>${s.gpa || 0}</td>
            <td>${getStatusBadge(s)}</td>
            <td>
                <button class="btn-sm-custom btn-primary-custom"
                        onclick="event.stopPropagation(); openStudentModal(${s.student_id})">
                    <i class="ri-edit-line"></i> Enter Scores
                </button>
            </td>
        </tr>
    `).join('');
}

function renderEmptyTable(msg) {
    document.getElementById('studentsTableBody').innerHTML = `
        <tr><td colspan="8" class="empty-state">
            <i class="ri-inbox-line"></i>
            <h5>No students found</h5>
            <p class="text-muted">${msg || 'Select a class, term, and session to load students'}</p>
        </td></tr>`;
}

/* ─── Helpers ───────────────────────────────────────────────────────────── */
function getInitials(n) {
    if (!n) return '?';
    const p = n.trim().split(' ');
    return (p[0]?.charAt(0) || '') + (p[1]?.charAt(0) || '');
}
function getGradeClass(g) {
    if (!g) return 'status-pending';
    if (g.startsWith('A')) return 'status-complete';
    if (g.startsWith('B') || g.startsWith('C')) return 'status-incomplete';
    return 'status-pending';
}
function getStatusBadge(s) {
    const total     = s.total_subjects || 0;
    const completed = (s.subjects || []).filter(sub => sub.total > 0).length;
    if (completed === 0)     return '<span class="status-badge status-pending">Pending</span>';
    if (completed === total) return '<span class="status-badge status-complete">Complete</span>';
    return `<span class="status-badge status-incomplete">${completed}/${total}</span>`;
}
function escapeHtml(t) {
    if (!t && t !== 0) return '';
    const d = document.createElement('div'); d.textContent = t; return d.innerHTML;
}

/* ─── Open student modal ────────────────────────────────────────────────── */
window.openStudentModal = async function (studentId) {
    const student = allStudentsData.find(s => s.student_id === studentId);
    if (!student) { Swal.fire('Error', 'Student data not found', 'error'); return; }
    currentStudentData = student;
    renderStudentModal();
    const modal = new bootstrap.Modal(document.getElementById('studentResultsModal'));
    modal.show();
};

/* ─── Render modal content ──────────────────────────────────────────────── */
function renderStudentModal() {
    if (!currentStudentData) return;
    const s = currentStudentData;

    document.getElementById('modalStudentInfo').innerHTML = `
        <div class="student-info-header">
            <div class="student-avatar-large">
                ${s.photo
                    ? `<img src="${s.photo}" alt="Photo" onerror="this.style.display='none'">`
                    : escapeHtml(getInitials(s.full_name))}
            </div>
            <div>
                <h4 class="mb-1">${escapeHtml(s.full_name)}</h4>
                <p class="mb-1"><i class="ri-id-card-line me-1"></i> Admission: ${escapeHtml(s.admission_no || 'N/A')}</p>
                <div class="summary-stats">
                    <div class="stat-box"><div class="label">Average</div><div class="value">${s.average || 0}</div></div>
                    <div class="stat-box"><div class="label">GPA</div><div class="value">${s.gpa || 0}</div></div>
                    <div class="stat-box"><div class="label">Subjects</div><div class="value">${s.total_subjects || 0}</div></div>
                </div>
            </div>
        </div>`;

    const container = document.getElementById('modalSubjectsContainer');

    if (!s.subjects || s.subjects.length === 0) {
        container.innerHTML = `
            <div class="no-subjects-msg">
                <i class="ri-information-line me-2"></i>
                No subjects registered for this student in the selected term/session.
                Check that subject registrations exist in <strong>subjectRegistrationStatus</strong>
                with Status = 'active' for this class, term, and session.
            </div>`;
        return;
    }

    // FIX: store subjects by index so onclick can reference them without string issues
    container.innerHTML = s.subjects.map((subj, idx) => `
        <div class="subject-score-card" data-idx="${idx}">
            <div class="subject-header">
                <div>
                    <strong><i class="ri-book-open-line me-1"></i>${escapeHtml(subj.subject_name)}</strong>
                    <span class="badge bg-light text-dark ms-2">${escapeHtml(subj.subject_code)}</span>
                </div>
                <div>
                    <span class="me-3">Total: <strong id="total_idx_${idx}">${subj.total || 0}</strong></span>
                    <span class="me-3">Grade: <strong id="grade_idx_${idx}">${subj.grade || 'F'}</strong></span>
                    <span>Remark: <strong id="remark_idx_${idx}">${escapeHtml(subj.remark || 'Not Entered')}</strong></span>
                </div>
            </div>
            <div id="assessments_idx_${idx}">
                ${renderAssessments(subj, idx)}
            </div>
            <div class="subject-total">
                <button class="btn-save-subject" data-idx="${idx}">
                    <i class="ri-save-line me-1"></i> Save Subject
                </button>
            </div>
        </div>
    `).join('');

    // Wire save buttons by index (avoids fragile onclick string matching)
    container.querySelectorAll('.btn-save-subject[data-idx]').forEach(btn => {
        btn.addEventListener('click', function () {
            saveSubjectByIndex(parseInt(this.dataset.idx));
        });
    });

    // Wire live total updates
    container.querySelectorAll('.score-input-modal').forEach(inp => {
        inp.addEventListener('input', function () {
            updateSubjectTotalByIndex(parseInt(this.dataset.idx));
        });
    });
}

/* ─── Render assessment inputs for a subject ────────────────────────────── */
function renderAssessments(subj, idx) {
    if (!currentAssessments || currentAssessments.length === 0) {
        return '<div class="text-muted text-center py-3">No assessments configured for this class</div>';
    }

    // Build a lookup of existing scores
    const scoreMap = {};
    (subj.assessment_scores || []).forEach(a => { scoreMap[a.assessment_id] = a.score; });

    return currentAssessments.map(a => `
        <div class="assessment-row">
            <div class="assessment-label">${escapeHtml(a.name)}</div>
            <div class="assessment-input">
                <input type="number"
                       class="form-control score-input-modal"
                       data-idx="${idx}"
                       data-assessment-id="${a.id}"
                       data-max-score="${a.max_score}"
                       value="${scoreMap[a.id] !== undefined ? scoreMap[a.id] : 0}"
                       step="0.5" min="0" max="${a.max_score}">
            </div>
            <div class="assessment-score">/ ${a.max_score}</div>
        </div>
    `).join('');
}

/* ─── Update total preview for a subject ───────────────────────────────── */
window.updateSubjectTotalByIndex = function (idx) {
    const inputs = document.querySelectorAll(`.score-input-modal[data-idx="${idx}"]`);
    let total = 0;
    inputs.forEach(inp => {
        let v = parseFloat(inp.value) || 0;
        const max = parseFloat(inp.dataset.maxScore) || 100;
        if (v > max) { v = max; inp.value = max; }
        if (v < 0)   { v = 0;   inp.value = 0; }
        total += v;
    });
    total = Math.round(total * 100) / 100;
    const totalEl = document.getElementById(`total_idx_${idx}`);
    if (totalEl) totalEl.textContent = total;
    updateGradeFromTotal(idx, total);
};

function updateGradeFromTotal(idx, total) {
    let grade = 'F', remark = 'Fail';
    if      (total >= 70) { grade = 'A'; remark = 'Excellent'; }
    else if (total >= 60) { grade = 'B'; remark = 'Very Good'; }
    else if (total >= 50) { grade = 'C'; remark = 'Good'; }
    else if (total >= 40) { grade = 'D'; remark = 'Pass'; }
    const gEl = document.getElementById(`grade_idx_${idx}`);
    const rEl = document.getElementById(`remark_idx_${idx}`);
    if (gEl) gEl.textContent = grade;
    if (rEl) rEl.textContent = remark;
}

/* ─── Save a single subject by its array index ──────────────────────────── */
async function saveSubjectByIndex(idx) {
    const subj = currentStudentData.subjects[idx];
    if (!subj) { console.error('No subject at index', idx); return; }

    const scores = [];
    document.querySelectorAll(`.score-input-modal[data-idx="${idx}"]`).forEach(inp => {
        scores.push({
            assessment_id: parseInt(inp.dataset.assessmentId),
            score        : parseFloat(inp.value) || 0,
        });
    });

    // Find and disable the save button
    const btn = document.querySelector(`.btn-save-subject[data-idx="${idx}"]`);
    const origHtml = btn ? btn.innerHTML : '';
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="loading-spinner"></span> Saving...'; }

    try {
        const response = await fetch(ROUTES.updateSubject, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body   : JSON.stringify({
                student_id      : currentStudentData.student_id,
                subject_id      : subj.subject_id,
                subjectclass_id : subj.subjectclass_id,
                term_id         : currentFilters.term_id,
                session_id      : currentFilters.session_id,
                class_id        : currentFilters.class_id,
                scores,
            }),
        });

        const result = await response.json();

        if (result.success) {
            Swal.fire({ icon: 'success', title: 'Saved', text: 'Subject scores saved!', timer: 1200, showConfirmButton: false });
            // Update local data
            const d = result.data;
            subj.total   = d.total;
            subj.bf      = d.bf;
            subj.cum     = d.cum;
            subj.grade   = d.grade;
            subj.remark  = d.remark;
        } else {
            Swal.fire('Error', result.message || 'Failed to save scores', 'error');
        }
    } catch (err) {
        console.error('saveSubjectByIndex error:', err);
        Swal.fire('Error', 'Network error. Please try again.', 'error');
    } finally {
        if (btn) { btn.disabled = false; btn.innerHTML = origHtml || '<i class="ri-save-line me-1"></i> Save Subject'; }
    }
}

/* ─── Save ALL subjects ─────────────────────────────────────────────────── */
async function saveAllSubjects() {
    const confirm = await Swal.fire({
        title              : 'Save All Subjects?',
        text               : 'This will save scores for all subjects at once.',
        icon               : 'question',
        showCancelButton   : true,
        confirmButtonColor : '#10b981',
        confirmButtonText  : 'Yes, save all',
    });
    if (!confirm.isConfirmed) return;

    const subjects = (currentStudentData.subjects || []).map((subj, idx) => {
        const scores = [];
        document.querySelectorAll(`.score-input-modal[data-idx="${idx}"]`).forEach(inp => {
            scores.push({
                assessment_id: parseInt(inp.dataset.assessmentId),
                score        : parseFloat(inp.value) || 0,
            });
        });
        return {
            subject_id      : subj.subject_id,
            subjectclass_id : subj.subjectclass_id,
            scores,
        };
    });

    const saveBtn    = document.getElementById('saveAllSubjectsBtn');
    const origHtml   = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="loading-spinner"></span> Saving All...';

    try {
        const response = await fetch(ROUTES.bulkUpdate, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body   : JSON.stringify({
                student_id : currentStudentData.student_id,
                class_id   : currentFilters.class_id,
                term_id    : currentFilters.term_id,
                session_id : currentFilters.session_id,
                subjects,
            }),
        });

        const result = await response.json();

        if (result.success) {
            Swal.fire({ icon: 'success', title: 'All Saved!', text: result.message, timer: 1500, showConfirmButton: false });
            bootstrap.Modal.getInstance(document.getElementById('studentResultsModal'))?.hide();
            loadStudents();   // refresh student list
        } else {
            Swal.fire('Error', result.message || 'Failed to save scores', 'error');
        }
    } catch (err) {
        console.error('saveAllSubjects error:', err);
        Swal.fire('Error', 'Network error. Please try again.', 'error');
    } finally {
        saveBtn.disabled  = false;
        saveBtn.innerHTML = origHtml;
    }
}
</script>
@endsection
