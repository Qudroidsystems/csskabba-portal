@extends('layouts.master')

@section('content')
<style>
:root {
    --bs-pri:    #1e3a5f;
    --bs-acc:    #2563eb;
    --bs-success:#16a34a;
    --bs-warn:   #d97706;
    --bs-danger: #dc2626;
    --bs-muted:  #6b7280;
    --bs-border: #e2e8f0;
    --bs-bg:     #f8fafc;
    --bs-card:   #ffffff;
    --bs-radius: 12px;
    --bs-shadow: 0 2px 8px rgba(0,0,0,.09);
}

/* ── Page header ── */
.bsg-hero {
    background: linear-gradient(135deg, var(--bs-pri) 0%, #2563eb 60%, #4f46e5 100%);
    border-radius: var(--bs-radius);
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.bsg-hero::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 220px; height: 220px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
}
.bsg-hero h1 {
    font-size: 22px;
    font-weight: 700;
    color: white;
    margin: 0 0 6px;
}
.bsg-hero p {
    font-size: 13px;
    color: rgba(255,255,255,.75);
    margin: 0;
}

/* ── Step cards ── */
.step-card {
    background: var(--bs-card);
    border: 1px solid var(--bs-border);
    border-radius: var(--bs-radius);
    box-shadow: var(--bs-shadow);
    padding: 22px 24px;
    margin-bottom: 20px;
    position: relative;
}
.step-card .step-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px; height: 32px;
    background: var(--bs-pri);
    color: white;
    border-radius: 50%;
    font-weight: 700;
    font-size: 14px;
    margin-right: 10px;
    flex-shrink: 0;
}
.step-card .step-title {
    font-size: 15px;
    font-weight: 600;
    color: var(--bs-pri);
    display: flex;
    align-items: center;
    margin-bottom: 16px;
}
.step-card .step-title .step-subtitle {
    font-size: 12px;
    font-weight: 400;
    color: var(--bs-muted);
    margin-left: 8px;
}
.step-card.active {
    border-color: var(--bs-acc);
    box-shadow: 0 0 0 3px rgba(37,99,235,.1), var(--bs-shadow);
}

/* ── Custom select ── */
.bsg-select {
    border: 1.5px solid var(--bs-border);
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 13px;
    width: 100%;
    background: #fff;
    color: #374151;
    transition: border-color .15s;
    appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23374151' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 14px;
    padding-right: 36px;
}
.bsg-select:focus { outline: none; border-color: var(--bs-acc); box-shadow: 0 0 0 3px rgba(37,99,235,.12); }

/* ── Student preview card ── */
.student-preview-card {
    background: linear-gradient(135deg, #eff6ff 0%, #f0fdf4 100%);
    border: 1px solid #bfdbfe;
    border-radius: 10px;
    padding: 16px 20px;
    display: none;
}
.student-preview-card.visible { display: block; }
.preview-stat {
    text-align: center;
    padding: 10px;
}
.preview-stat .val {
    font-size: 28px;
    font-weight: 700;
    color: var(--bs-pri);
    display: block;
}
.preview-stat .lbl {
    font-size: 11px;
    color: var(--bs-muted);
    text-transform: uppercase;
    letter-spacing: .5px;
}

/* ── Column selector modal ── */
.col-group {
    border: 1px solid var(--bs-border);
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 12px;
}
.col-group-header {
    font-size: 12px;
    font-weight: 600;
    color: var(--bs-pri);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.col-item {
    display: flex;
    align-items: center;
    padding: 4px 0;
    font-size: 12.5px;
    color: #374151;
}
.col-item input[type=checkbox] {
    width: 15px; height: 15px;
    margin-right: 8px;
    accent-color: var(--bs-acc);
    flex-shrink: 0;
    cursor: pointer;
}

/* ── Export buttons ── */
.export-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 13px 24px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: transform .15s, box-shadow .15s;
    border: none;
    width: 100%;
}
.export-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.15); }
.export-btn.pdf   { background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%); color: white; }
.export-btn.excel { background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%); color: white; }
.export-btn i     { font-size: 20px; }

/* ── Paper size selector ── */
.paper-btn {
    border: 2px solid var(--bs-border);
    border-radius: 8px;
    padding: 8px 14px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all .15s;
    background: white;
    color: var(--bs-muted);
}
.paper-btn.active {
    border-color: var(--bs-acc);
    background: #eff6ff;
    color: var(--bs-acc);
}

/* ── Spinner overlay ── */
#loadingOverlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
#loadingOverlay.active { display: flex; }
.loading-box {
    background: white;
    border-radius: 16px;
    padding: 36px 48px;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0,0,0,.2);
}
.loading-box .spinner-grow { width: 3rem; height: 3rem; }

/* ── Responsive ── */
@media (max-width: 768px) {
    .bsg-hero { padding: 20px; }
    .bsg-hero h1 { font-size: 18px; }
}
</style>

<div class="main-content">
<div class="page-content">
<div class="container-fluid">

    @foreach(['success','error','warning'] as $bag)
        @if(session($bag))
            <div class="alert alert-{{ $bag === 'error' ? 'danger' : $bag }} alert-dismissible fade show">
                {{ session($bag) }}<button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    @endforeach

    {{-- ── Hero header ── --}}
    <div class="bsg-hero">
        <h1><i class="ri-file-chart-line me-2"></i>{{ $pagetitle }}</h1>
        <p>Select a class, session and term to generate a professional academic broadsheet in PDF or Excel format.</p>
    </div>

    <div class="row g-4">

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- LEFT COLUMN — filter + export                                  --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        <div class="col-lg-4">

            {{-- Step 1: Select class & session ── --}}
            <div class="step-card" id="step1Card">
                <div class="step-title">
                    <span class="step-badge">1</span>
                    Select Class & Session
                    <span class="step-subtitle">Required</span>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:12.5px;color:#374151;">
                        Class / Arm <span class="text-danger">*</span>
                    </label>
                    <select class="bsg-select" id="classSelect">
                        <option value="">— Select class —</option>
                        @foreach($schoolclasses as $cls)
                            <option value="{{ $cls->id }}">
                                {{ $cls->schoolclass }}{{ $cls->arm ? ' ' . $cls->arm : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:12.5px;color:#374151;">
                        Session <span class="text-danger">*</span>
                    </label>
                    <select class="bsg-select" id="sessionSelect">
                        <option value="">— Select session —</option>
                        @foreach($schoolsessions as $session)
                            <option value="{{ $session->id }}">{{ $session->session }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:12.5px;color:#374151;">
                        Term <span class="text-danger">*</span>
                    </label>
                    <select class="bsg-select" id="termSelect">
                        <option value="">— Select term —</option>
                        @foreach($schoolterms as $term)
                            <option value="{{ $term->id }}">{{ $term->term }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Student preview ── --}}
                <div class="student-preview-card" id="studentPreview">
                    <div class="row g-0">
                        <div class="col-4 preview-stat">
                            <span class="val" id="previewCount">0</span>
                            <span class="lbl">Students</span>
                        </div>
                        <div class="col-4 preview-stat" style="border-left:1px solid #bfdbfe;border-right:1px solid #bfdbfe;">
                            <span class="val text-success" id="previewSubjects">-</span>
                            <span class="lbl">Subjects</span>
                        </div>
                        <div class="col-4 preview-stat">
                            <span class="val text-warning" id="previewAssessments">-</span>
                            <span class="lbl">Assessments</span>
                        </div>
                    </div>
                </div>

                <button class="btn btn-primary w-100 mt-3" id="loadColumnsBtn" disabled
                    onclick="openColumnModal();">
                    <i class="ri-settings-3-line me-2"></i>Configure Columns & Export
                </button>
            </div>

            {{-- Step 2: Paper size & orientation ── --}}
            <div class="step-card" id="step2Card">
                <div class="step-title">
                    <span class="step-badge">2</span>
                    PDF Settings
                    <span class="step-subtitle">For PDF export only</span>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:12.5px;color:#374151;">Paper Size</label>
                    <div class="d-flex gap-2 flex-wrap" id="paperSizeGroup">
                        @foreach(['A4','A3','A2','A1'] as $size)
                            <button class="paper-btn {{ $size === 'A3' ? 'active' : '' }}"
                                data-size="{{ $size }}" onclick="selectPaperSize(this)">
                                {{ $size }}
                            </button>
                        @endforeach
                    </div>
                    <input type="hidden" id="paperSize" value="A3">
                </div>

                <div>
                    <label class="form-label fw-semibold" style="font-size:12.5px;color:#374151;">Orientation</label>
                    <div class="d-flex gap-2">
                        <button class="paper-btn" data-orient="portrait" onclick="selectOrientation(this)">
                            <i class="ri-file-line me-1"></i>Portrait
                        </button>
                        <button class="paper-btn active" data-orient="landscape" onclick="selectOrientation(this)">
                            <i class="ri-file-4-line me-1"></i>Landscape
                        </button>
                    </div>
                    <input type="hidden" id="orientation" value="landscape">
                </div>
            </div>

        </div>{{-- /col-lg-4 --}}

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- RIGHT COLUMN — info / guide                                    --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        <div class="col-lg-8">

            {{-- What's in the broadsheet ── --}}
            <div class="step-card">
                <div class="step-title">
                    <span class="step-badge" style="background:#16a34a;"><i class="ri-information-line" style="font-size:15px;"></i></span>
                    What's in the Broadsheet
                </div>
                <div class="row g-3">
                    @php
                    $features = [
                        ['ri-school-line',      '#2563eb', 'School Header',      'School name, address, logo, motto and contact details at the top.'],
                        ['ri-user-line',        '#16a34a', 'Student Details',    'Admission number, full name and optional gender column.'],
                        ['ri-clipboard-line',   '#d97706', 'Dynamic Assessments','All assessments (CA1, CA2, Exam…) pulled automatically from your class configuration.'],
                        ['ri-bar-chart-line',   '#7c3aed', 'Subject Scores',     'Total, BF, Cumulative score, Grade, Position and Class Average per subject.'],
                        ['ri-trophy-line',      '#dc2626', 'Class Statistics',   'Per-subject class average, highest, lowest, pass rate and fail count.'],
                        ['ri-calculator-line',  '#0891b2', 'GPA / CGPA',         'Optional GPA and CGPA columns computed from current term grade points.'],
                        ['ri-pie-chart-line',   '#16a34a', 'Pass/Fail Summary',  'Subject-by-subject pass/fail analysis table after the main broadsheet.'],
                        ['ri-pen-line',         '#374151', 'Signatures',         'Class teacher, HOD, Vice Principal and Principal signature rows.'],
                    ];
                    @endphp
                    @foreach($features as $f)
                    <div class="col-md-6 col-xl-3">
                        <div style="padding:12px;background:#f8fafc;border-radius:10px;border:1px solid var(--bs-border);height:100%;">
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                                <i class="{{ $f[0] }}" style="font-size:18px;color:{{ $f[1] }};"></i>
                                <strong style="font-size:12px;color:#1e3a5f;">{{ $f[2] }}</strong>
                            </div>
                            <p style="font-size:11px;color:#6b7280;margin:0;line-height:1.4;">{{ $f[3] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Quick tips ── --}}
            <div class="step-card">
                <div class="step-title">
                    <span class="step-badge" style="background:#d97706;"><i class="ri-lightbulb-line" style="font-size:15px;"></i></span>
                    Quick Tips
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex gap-3 align-items-start">
                            <span style="font-size:20px;">📐</span>
                            <div>
                                <strong style="font-size:12.5px;color:#1e3a5f;">Paper Size Guide</strong>
                                <p style="font-size:11.5px;color:#6b7280;margin-top:3px;">
                                    Use <strong>A3 Landscape</strong> for up to ~8 subjects, <strong>A2</strong> for 8–15 subjects, and <strong>A1</strong> for very large classes with many subjects.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex gap-3 align-items-start">
                            <span style="font-size:20px;">📊</span>
                            <div>
                                <strong style="font-size:12.5px;color:#1e3a5f;">Excel Export</strong>
                                <p style="font-size:11.5px;color:#6b7280;margin-top:3px;">
                                    Excel output includes all selected columns with colour-coded headers and freeze panes. Great for further analysis or printing.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex gap-3 align-items-start">
                            <span style="font-size:20px;">🎯</span>
                            <div>
                                <strong style="font-size:12.5px;color:#1e3a5f;">Column Selection</strong>
                                <p style="font-size:11.5px;color:#6b7280;margin-top:3px;">
                                    Choose exactly which columns appear. Hiding assessment columns makes the sheet narrower and easier to print on smaller paper.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex gap-3 align-items-start">
                            <span style="font-size:20px;">🔒</span>
                            <div>
                                <strong style="font-size:12.5px;color:#1e3a5f;">WAEC Standard</strong>
                                <p style="font-size:11.5px;color:#6b7280;margin-top:3px;">
                                    Grades follow the WAEC/NECO 9-point scale (A1–F9) with grade key printed at the top of the broadsheet.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /col-lg-8 --}}
    </div>{{-- /row --}}

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- COLUMN SELECTION MODAL                                             --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="columnModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">

                <div class="modal-header border-0" style="background:linear-gradient(135deg,#1e3a5f 0%,#2563eb 100%);">
                    <div>
                        <h5 class="modal-title text-white fw-bold">
                            <i class="ri-layout-column-line me-2"></i>Configure Broadsheet Columns
                        </h5>
                        <p class="text-white-50 small mb-0">Choose which columns appear in your broadsheet, then select export format.</p>
                    </div>
                    <button class="btn-close btn-close-white ms-3" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    {{-- Loading state --}}
                    <div id="colModalLoader" class="text-center py-5">
                        <div class="spinner-border text-primary mb-3" style="width:3rem;height:3rem;"></div>
                        <p class="text-muted">Loading column options…</p>
                    </div>

                    {{-- Column form --}}
                    <div id="colModalForm" style="display:none;">

                        {{-- Summary pills --}}
                        <div class="d-flex gap-2 flex-wrap mb-4" id="colSummaryPills">
                            <span class="badge bg-primary-subtle text-primary px-3 py-2" id="pillClass">Class: —</span>
                            <span class="badge bg-success-subtle text-success px-3 py-2" id="pillSession">Session: —</span>
                            <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-2" id="pillTerm">Term: —</span>
                            <span class="badge bg-info-subtle text-info px-3 py-2" id="pillStudents">0 students</span>
                        </div>

                        <div class="row g-3">
                            {{-- Student info --}}
                            <div class="col-md-3">
                                <div class="col-group">
                                    <div class="col-group-header">
                                        <span>Student Info</span>
                                        <a href="#" class="text-primary text-decoration-none" style="font-size:11px;" onclick="toggleGroup('studentInfoCols');return false;">Toggle</a>
                                    </div>
                                    <div id="studentInfoCols"></div>
                                </div>
                            </div>

                            {{-- Assessments --}}
                            <div class="col-md-4">
                                <div class="col-group">
                                    <div class="col-group-header">
                                        <span>Assessments</span>
                                        <a href="#" class="text-primary text-decoration-none" style="font-size:11px;" onclick="toggleGroup('assessmentCols');return false;">Toggle</a>
                                    </div>
                                    <div id="assessmentCols">
                                        <p class="text-muted small">Loading…</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Scores --}}
                            <div class="col-md-3">
                                <div class="col-group">
                                    <div class="col-group-header">
                                        <span>Scores & Metrics</span>
                                        <a href="#" class="text-primary text-decoration-none" style="font-size:11px;" onclick="toggleGroup('scoreCols');return false;">Toggle</a>
                                    </div>
                                    <div id="scoreCols"></div>
                                </div>
                            </div>

                            {{-- GPA --}}
                            <div class="col-md-2">
                                <div class="col-group">
                                    <div class="col-group-header">
                                        <span>GPA</span>
                                        <a href="#" class="text-primary text-decoration-none" style="font-size:11px;" onclick="toggleGroup('gpaCols');return false;">Toggle</a>
                                    </div>
                                    <div id="gpaCols"></div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Export format & buttons ── --}}
                        <div class="mt-4 p-3 rounded-3" style="background:#f8fafc;border:1px solid var(--bs-border);">
                            <h6 class="fw-bold mb-3" style="color:#1e3a5f;"><i class="ri-download-line me-2"></i>Export Format</h6>


                            <div class="row g-3">
                                 <div class="col-md-6">
                                     <button class="export-btn" style="background:linear-gradient(135deg,#1d6fa4,#2a8fc9);color:white;" onclick="doExport('web');">
                                        <i class="ri-global-line"></i>
                                       <div class="text-start">
                                         <div>Open Web View</div>
                                         <div style="font-size:11px;opacity:.8;font-weight:400;">Interactive · Scroll · Zoom · Navigate</div>
                                       </div>
                                     </button>
                                 </div>
                                <div class="col-md-6">
                                    <button class="export-btn pdf" onclick="doExport('pdf');">
                                        <i class="ri-file-pdf-line"></i>
                                        <div class="text-start">
                                            <div style="font-size:14px;">Download PDF</div>
                                            <div style="font-size:11px;opacity:.8;font-weight:400;">
                                                <span id="pdfSizeLabel">A3 · Landscape</span>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button class="export-btn excel" onclick="doExport('excel');">
                                        <i class="ri-file-excel-line"></i>
                                        <div class="text-start">
                                            <div style="font-size:14px;">Download Excel</div>
                                            <div style="font-size:11px;opacity:.8;font-weight:400;">
                                                .xlsx · All selected columns
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>{{-- /colModalForm --}}
                </div>

                <div class="modal-footer bg-light border-0">
                    <small class="text-muted me-auto">
                        <i class="ri-information-line me-1"></i>
                        The broadsheet includes ALL students in the selected class and session.
                    </small>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden export form --}}
    <form id="exportForm" method="POST" target="_blank" style="display:none;">
        @csrf
        <input type="hidden" name="schoolclassid" id="ef_class">
        <input type="hidden" name="sessionid"     id="ef_session">
        <input type="hidden" name="termid"        id="ef_term">
        <input type="hidden" name="paper_size"    id="ef_paper">
        <input type="hidden" name="orientation"   id="ef_orient">
        <div id="ef_columns"></div>
    </form>

</div>{{-- container --}}
</div>{{-- page-content --}}
</div>{{-- main-content --}}

{{-- Loading overlay --}}
<div id="loadingOverlay">
    <div class="loading-box">
        <div class="spinner-grow text-primary mb-3"></div>
        <div class="fw-bold" style="color:#1e3a5f;font-size:15px;" id="loadingMsg">Generating Broadsheet…</div>
        <p class="text-muted small mt-1 mb-0">This may take a few seconds. Please wait.</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const ROUTES = {
    columnOptions  : '{{ route("broadsheet.column-options") }}',
    studentPreview : '{{ route("broadsheet.student-preview") }}',
    exportPdf      : '{{ route("broadsheet.export.pdf") }}',
    exportExcel    : '{{ route("broadsheet.export.excel") }}',
    webView        : '{{ route("broadsheet.web-view") }}',     // ← ADD THIS
};

let columnData      = null;   // raw column data from server
let studentCount    = 0;
let debounceTimer   = null;

// ── Paper size ────────────────────────────────────────────────────────────────
function selectPaperSize(btn) {
    document.querySelectorAll('[data-size]').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('paperSize').value = btn.dataset.size;
    updatePdfLabel();
}
function selectOrientation(btn) {
    document.querySelectorAll('[data-orient]').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('orientation').value = btn.dataset.orient;
    updatePdfLabel();
}
function updatePdfLabel() {
    const size   = document.getElementById('paperSize').value;
    const orient = document.getElementById('orientation').value;
    const lbl    = document.getElementById('pdfSizeLabel');
    if (lbl) lbl.textContent = size + ' · ' + (orient === 'landscape' ? 'Landscape' : 'Portrait');
}

// ── Debounced auto-refresh when all 3 selects are filled ─────────────────────
['classSelect','sessionSelect','termSelect'].forEach(id => {
    document.getElementById(id).addEventListener('change', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(checkAndLoadPreview, 300);
    });
});

function checkAndLoadPreview() {
    const classId   = document.getElementById('classSelect').value;
    const sessionId = document.getElementById('sessionSelect').value;
    const termId    = document.getElementById('termSelect').value;
    const loadBtn   = document.getElementById('loadColumnsBtn');

    if (!classId || !sessionId || !termId) {
        document.getElementById('studentPreview').classList.remove('visible');
        loadBtn.disabled = true;
        document.getElementById('previewCount').textContent = '0';
        document.getElementById('previewSubjects').textContent = '-';
        document.getElementById('previewAssessments').textContent = '-';
        return;
    }

    // Fetch student preview with subject and assessment counts
    fetch(ROUTES.studentPreview, {
        method : 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body   : JSON.stringify({ schoolclassid: classId, sessionid: sessionId, termid: termId }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            studentCount = data.count;
            document.getElementById('previewCount').textContent = data.count;
            document.getElementById('previewSubjects').textContent = data.subject_count || '-';
            document.getElementById('previewAssessments').textContent = data.assessment_count || '-';
            document.getElementById('studentPreview').classList.add('visible');
            loadBtn.disabled = data.count === 0;
        }
    })
    .catch(err => {
        console.error('Preview error:', err);
        document.getElementById('previewSubjects').textContent = 'Error';
        document.getElementById('previewAssessments').textContent = 'Error';
    });
}

// ── Open column modal ─────────────────────────────────────────────────────────
function openColumnModal() {
    const classId   = document.getElementById('classSelect').value;
    const sessionId = document.getElementById('sessionSelect').value;
    const termId    = document.getElementById('termSelect').value;

    if (!classId || !sessionId || !termId) {
        Swal.fire({ icon: 'warning', title: 'Incomplete Selection', text: 'Please select class, session and term first.' });
        return;
    }

    // Update summary pills
    const classText   = document.getElementById('classSelect').selectedOptions[0]?.text ?? '';
    const sessionText = document.getElementById('sessionSelect').selectedOptions[0]?.text ?? '';
    const termText    = document.getElementById('termSelect').selectedOptions[0]?.text ?? '';

    document.getElementById('pillClass').textContent    = 'Class: ' + classText;
    document.getElementById('pillSession').textContent  = sessionText;
    document.getElementById('pillTerm').textContent     = termText;
    document.getElementById('pillStudents').textContent = studentCount + ' students';

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('columnModal'));
    modal.show();

    // Show loader, hide form
    document.getElementById('colModalLoader').style.display = 'block';
    document.getElementById('colModalForm').style.display   = 'none';

    // Fetch column options
    fetch(ROUTES.columnOptions, {
        method : 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body   : JSON.stringify({ schoolclassid: classId, sessionid: sessionId, termid: termId }),
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) throw new Error(data.message || 'Failed to load columns');
        columnData = data.columns;
        renderColumnForm(data.columns);
        document.getElementById('colModalLoader').style.display = 'none';
        document.getElementById('colModalForm').style.display   = 'block';
        updatePdfLabel();

        // Set assessment and subject counts
        const asmCount = Object.keys(data.columns.assessments || {}).length;
        const subjectCount = Object.keys(data.columns.scores || {}).length;
        document.getElementById('previewAssessments').textContent = asmCount;
        document.getElementById('previewSubjects').textContent = subjectCount;
    })
    .catch(err => {
        Swal.fire({ icon: 'error', title: 'Error', text: err.message });
        bootstrap.Modal.getInstance(document.getElementById('columnModal'))?.hide();
    });
}

// ── Render column checkboxes ──────────────────────────────────────────────────
function renderColumnForm(columns) {
    renderGroup('studentInfoCols', columns.student_info ?? {});
    renderGroup('assessmentCols',  columns.assessments  ?? {});
    renderGroup('scoreCols',       columns.scores       ?? {});
    renderGroup('gpaCols',         columns.gpa_metrics  ?? {});
}

function renderGroup(containerId, items) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = '';
    Object.entries(items).forEach(([key, cfg]) => {
        const div = document.createElement('div');
        div.className = 'col-item';
        div.innerHTML = `
            <input type="checkbox" class="col-checkbox" data-key="${key}" id="chk_${key}" ${cfg.default ? 'checked' : ''}>
            <label for="chk_${key}" style="cursor:pointer;">
                ${cfg.label}
                ${cfg.max_score ? '<small style="color:#9ca3af;"> (' + cfg.max_score + ')</small>' : ''}
            </label>`;
        container.appendChild(div);
    });
}

// ── Toggle all in a group ─────────────────────────────────────────────────────
function toggleGroup(containerId) {
    const checkboxes = document.querySelectorAll(`#${containerId} .col-checkbox`);
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => cb.checked = !allChecked);
}

// ── Do export ─────────────────────────────────────────────────────────────────
function doExport(type) {
    const classId   = document.getElementById('classSelect').value;
    const sessionId = document.getElementById('sessionSelect').value;
    const termId    = document.getElementById('termSelect').value;

    if (!classId || !sessionId || !termId) {
        Swal.fire({ icon: 'warning', title: 'Incomplete', text: 'Please select class, session and term.' });
        return;
    }

    // Collect selected columns
    const selectedCols = Array.from(document.querySelectorAll('.col-checkbox:checked')).map(cb => cb.dataset.key);

    if (selectedCols.length === 0) {
        Swal.fire({ icon: 'warning', title: 'No Columns', text: 'Please select at least one column.' });
        return;
    }

    // Build hidden form and submit
    const form   = document.getElementById('exportForm');
    const action = type === 'pdf' ? ROUTES.exportPdf : ROUTES.exportExcel;

    form.action = action;
    document.getElementById('ef_class').value   = classId;
    document.getElementById('ef_session').value = sessionId;
    document.getElementById('ef_term').value    = termId;
    document.getElementById('ef_paper').value   = document.getElementById('paperSize').value;
    document.getElementById('ef_orient').value  = document.getElementById('orientation').value;

    // Clear old column inputs
    const colDiv = document.getElementById('ef_columns');
    colDiv.innerHTML = '';
    selectedCols.forEach((col, i) => {
        const inp = document.createElement('input');
        inp.type  = 'hidden';
        inp.name  = `selectedColumns[${i}]`;
        inp.value = col;
        colDiv.appendChild(inp);
    });

    // Show loading overlay
    const overlay = document.getElementById('loadingOverlay');
    document.getElementById('loadingMsg').textContent =
        type === 'pdf' ? 'Generating PDF Broadsheet…' : 'Generating Excel Broadsheet…';
    overlay.classList.add('active');

    form.submit();

    // Hide overlay after delay (file download doesn't trigger page unload)
    setTimeout(() => overlay.classList.remove('active'), 8000);
    bootstrap.Modal.getInstance(document.getElementById('columnModal'))?.hide();
}
</script>
@endsection
