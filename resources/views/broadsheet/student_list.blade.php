{{--
    resources/views/broadsheet/student_list.blade.php
    Standalone printable student list grouped by promotion recommendation.
    Opened in a new tab via POST from the broadsheet web view.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Promotion List — {{ ($schoolclass->schoolclass ?? '') . ' ' . ($schoolclass->arm_name ?? '') }}</title>
<style>
/* ═══════════════════════════════════════════════════════════
   BASE
═══════════════════════════════════════════════════════════ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Segoe UI', 'Arial', sans-serif;
    font-size: 13px;
    background: #f1f5f9;
    color: #0f2342;
    line-height: 1.5;
}

/* ── Print resets ── */
@media print {
    body { background: #fff !important; font-size: 11px; }
    .no-print { display: none !important; }
    .page-wrap { max-width: none !important; padding: 0 !important; background: #fff !important; }
    .school-header { page-break-after: avoid; }
    .group-section {
        page-break-after: always;
        page-break-inside: avoid;
    }
    .group-section:last-child {
        page-break-after: auto;
    }
    @page { margin: 1.4cm 1.2cm; }
}

/* Paper size overrides for print */
@media print and (size: A4) { @page { size: A4; } }
@media print and (size: A3) { @page { size: A3; } }
@media print and (size: A2) { @page { size: A2; } }
@media print and (size: A1) { @page { size: A1; } }
@media print and (size: Legal) { @page { size: Legal; } }
@media print and (size: Letter) { @page { size: Letter; } }

/* Portrait orientation */
@media print and (orientation: portrait) {
    @page { orientation: portrait; }
}

/* Landscape orientation */
@media print and (orientation: landscape) {
    @page { orientation: landscape; }
}

/* ── Layout ── */
.page-wrap {
    max-width: 1400px;
    margin: 0 auto;
    padding: 24px 20px;
}

/* ═══════════════════════════════════════════════════════════
   SCHOOL HEADER
═══════════════════════════════════════════════════════════ */
.school-header {
    background: linear-gradient(135deg, #0f2342 0%, #1e3a5f 55%, #0d9488 100%);
    border-radius: 12px;
    padding: 24px 28px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 20px;
    color: white;
}

.school-logo {
    width: 80px; height: 80px;
    border-radius: 50%;
    object-fit: contain;
    border: 3px solid rgba(255,255,255,.35);
    background: white;
    flex-shrink: 0;
}

.school-logo-placeholder {
    width: 80px; height: 80px;
    border-radius: 50%;
    background: rgba(255,255,255,.15);
    border: 3px solid rgba(255,255,255,.35);
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; font-weight: 800; color: white; flex-shrink: 0;
}

.school-info { flex: 1; text-align: center; }
.school-name    { font-size: 20px; font-weight: 800; text-transform: uppercase; letter-spacing: .6px; line-height: 1.2; }
.school-address { font-size: 11.5px; opacity: .8; margin-top: 4px; }
.school-motto   { font-size: 11px; font-style: italic; opacity: .7; margin-top: 3px; }

.list-title-bar {
    background: #0f2342;
    color: white;
    text-align: center;
    padding: 10px 20px;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: 1.5px;
    border-radius: 8px;
    margin-bottom: 16px;
}

/* ── Meta strip ── */
.meta-strip {
    display: flex;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #f8fafc;
    margin-bottom: 18px;
    overflow: hidden;
    flex-wrap: wrap;
}

.meta-cell {
    flex: 1;
    padding: 10px 14px;
    border-right: 1px solid #e2e8f0;
    text-align: center;
    min-width: 100px;
}
.meta-cell:last-child { border-right: none; }
.meta-label { font-size: 9.5px; color: #64748b; text-transform: uppercase; letter-spacing: .4px; display: block; }
.meta-value { font-size: 13px; font-weight: 700; color: #0f2342; display: block; margin-top: 2px; }

/* ═══════════════════════════════════════════════════════════
   PROMOTION GROUP HEADER
═══════════════════════════════════════════════════════════ */
.group-section {
    margin-bottom: 28px;
    page-break-after: always;
    page-break-inside: avoid;
}
.group-section:last-child {
    page-break-after: auto;
}

.group-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 16px;
    border-radius: 8px 8px 0 0;
    font-weight: 700;
    font-size: 14px;
    border-bottom: 2px solid rgba(0,0,0,.08);
}

.group-header .count-badge {
    margin-left: auto;
    padding: 3px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    background: rgba(255,255,255,.3);
}

/* Status colours */
.status-promoted      { background: linear-gradient(90deg, #d1fae5, #ecfdf5); color: #065f46; border-left: 5px solid #10b981; }
.status-trial         { background: linear-gradient(90deg, #fef3c7, #fffbeb); color: #92400e; border-left: 5px solid #f59e0b; }
.status-see_principal { background: linear-gradient(90deg, #dbeafe, #eff6ff); color: #1e40af; border-left: 5px solid #3b82f6; }
.status-repeated      { background: linear-gradient(90deg, #fee2e2, #fff1f2); color: #991b1b; border-left: 5px solid #ef4444; }
.status-awaiting      { background: linear-gradient(90deg, #f1f5f9, #f8fafc);  color: #475569; border-left: 5px solid #94a3b8; }
.status-other         { background: linear-gradient(90deg, #f5f3ff, #ede9fe);  color: #5b21b6; border-left: 5px solid #7c3aed; }

/* ═══════════════════════════════════════════════════════════
   STUDENT TABLE - Responsive with horizontal scroll
═══════════════════════════════════════════════════════════ */
.table-responsive-wrapper {
    overflow-x: auto;
    border-radius: 0 0 8px 8px;
}

.student-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border: 1px solid #e2e8f0;
    border-top: none;
    font-size: 12px;
    min-width: 600px;
}

.student-table thead th {
    background: #1e3a5f;
    color: #a8d4ef;
    padding: 10px 12px;
    text-align: left;
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .4px;
    border-right: 1px solid rgba(255,255,255,.07);
    white-space: nowrap;
}
.student-table thead th:last-child { border-right: none; }

.student-table tbody tr:nth-child(odd)  { background: #ffffff; }
.student-table tbody tr:nth-child(even) { background: #f8fafc; }
.student-table tbody tr:hover           { background: #f0f9ff !important; }

.student-table tbody td {
    padding: 10px 12px;
    border-bottom: 1px solid #e2e8f0;
    border-right: 1px solid #f1f5f9;
    vertical-align: middle;
    white-space: nowrap;
}
.student-table tbody td:last-child          { border-right: none; }
.student-table tbody tr:last-child td       { border-bottom: none; }

td.sn-cell { width: 36px; text-align: center; font-size: 11px; color: #64748b; font-weight: 600; }

/* Avatar */
.student-avatar {
    width: 34px; height: 34px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e2e8f0;
    flex-shrink: 0;
}
.avatar-initials {
    width: 34px; height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0d9488, #0ea5e9);
    color: white;
    font-size: 12px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.name-cell   { font-weight: 600; color: #0f2342; }
.adm-cell    { font-family: 'Courier New', monospace; font-size: 11px; color: #475569; }
.gender-cell { text-align: center; font-size: 11px; }
.arm-cell    { font-size: 11px; color: #0f2342; }

/* ── Summary footer ── */
.summary-footer {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px 20px;
    margin-top: 24px;
}
.summary-footer h4 { font-size: 14px; font-weight: 700; color: #0f2342; margin-bottom: 14px; }
.summary-grid { display: flex; gap: 12px; flex-wrap: wrap; }
.summary-item { flex: 1; min-width: 120px; text-align: center; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0; }
.summary-count { font-size: 26px; font-weight: 800; display: block; }
.summary-lbl   { font-size: 11px; font-weight: 600; display: block; margin-top: 4px; }

/* ── No-print toolbar ── */
.toolbar {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 14px 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    box-shadow: 0 2px 8px rgba(0,0,0,.07);
}

.toolbar-title   { font-size: 15px; font-weight: 700; color: #0f2342; display: flex; align-items: center; gap: 8px; }
.toolbar-actions { display: flex; gap: 8px; flex-wrap: wrap; }

.btn-print, .btn-pdf {
    background: linear-gradient(135deg, #0f2342, #1e3a5f);
    color: white; border: none; border-radius: 8px;
    padding: 9px 20px; font-size: 13px; font-weight: 600;
    cursor: pointer; display: flex; align-items: center; gap: 8px;
    transition: all .2s ease;
}
.btn-pdf {
    background: linear-gradient(135deg, #dc2626, #ef4444);
}
.btn-print:hover, .btn-pdf:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(15,35,66,.3); }

.btn-close-tab {
    background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;
    border-radius: 8px; padding: 9px 18px; font-size: 13px; font-weight: 600;
    cursor: pointer; display: flex; align-items: center; gap: 7px;
    text-decoration: none; transition: all .2s ease;
}
.btn-close-tab:hover { background: #e2e8f0; }

/* Settings panel */
.settings-panel {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 20px;
    display: none;
}
.settings-panel.open { display: block; }
.settings-panel h4 { font-size: 13px; font-weight: 700; color: #0f2342; margin-bottom: 12px; }
.settings-group { display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 15px; align-items: center; }
.settings-group label { display: flex; align-items: center; gap: 8px; font-size: 12px; }
.settings-group select, .settings-group input { padding: 6px 10px; border-radius: 6px; border: 1px solid #e2e8f0; }
.btn-settings {
    background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;
    border-radius: 8px; padding: 9px 18px; font-size: 13px; font-weight: 600;
    cursor: pointer; display: flex; align-items: center; gap: 7px;
}
.btn-settings.active { background: #7c3aed; color: white; border-color: #7c3aed; }

/* Column checkboxes grid */
.columns-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 8px;
    margin-top: 10px;
}
.columns-grid label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    padding: 6px 10px;
    background: white;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    cursor: pointer;
}
.columns-grid label:hover { background: #f0fdf9; border-color: #0d9488; }

/* ── Generated at line ── */
.generated-line {
    text-align: center;
    font-size: 10.5px;
    color: #94a3b8;
    margin-top: 20px;
    padding-top: 14px;
    border-top: 1px dashed #e2e8f0;
}

/* Loading overlay for PDF generation */
.pdf-loading {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,.8);
    z-index: 99999;
    display: none;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    color: white;
}
.pdf-loading.active { display: flex; }
.pdf-loading .spinner {
    width: 50px;
    height: 50px;
    border: 4px solid rgba(255,255,255,.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 15px;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>
</head>
<body>
<div class="page-wrap">

    {{-- PDF Loading Overlay --}}
    <div id="pdfLoading" class="pdf-loading">
        <div class="spinner"></div>
        <div>Generating PDF, please wait...</div>
    </div>

    {{-- ── TOOLBAR (no-print) ── --}}
    <div class="toolbar no-print">
        <div class="toolbar-title">
            <span style="font-size:20px;">📋</span>
            Promotion Student List
            <span style="font-size:12px;font-weight:400;color:#64748b;">
                — {{ ($schoolclass->schoolclass ?? '') . ' ' . ($schoolclass->arm_name ?? '') }}
                &nbsp;·&nbsp; {{ $schoolsession->session ?? '' }}
                &nbsp;·&nbsp; {{ $schoolterm->term ?? '' }}
            </span>
        </div>
        <div class="toolbar-actions">
            <button class="btn-settings" id="toggleSettingsBtn" onclick="toggleSettings()">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 15a3 3 0 100-6 3 3 0 000 6z"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/>
                </svg>
                Settings
            </button>
            <button class="btn-print" onclick="printStudentList()">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 9V2h12v7"/>
                    <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
                    <rect x="6" y="14" width="12" height="8"/>
                </svg>
                Print
            </button>
            <button class="btn-pdf" onclick="exportToPDF()">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                    <path d="M14 2v6h6"/>
                    <path d="M12 18v-4"/>
                    <path d="M9 14h6"/>
                </svg>
                Export PDF
            </button>
            <a href="javascript:window.close()" class="btn-close-tab">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
                Close
            </a>
        </div>
    </div>

    {{-- ── SETTINGS PANEL ── --}}
    <div id="settingsPanel" class="settings-panel no-print">
        <h4>⚙️ Print & Display Settings</h4>

        <div class="settings-group">
            <label>
                <span>📄 Page Orientation:</span>
                <select id="printOrientation">
                    <option value="portrait">Portrait</option>
                    <option value="landscape" selected>Landscape</option>
                </select>
            </label>
            <label>
                <span>📏 Paper Size:</span>
                <select id="paperSize">
                    <option value="A4">A4</option>
                    <option value="A3">A3</option>
                    <option value="A2">A2</option>
                    <option value="A1">A1</option>
                    <option value="Legal">Legal</option>
                    <option value="Letter">Letter</option>
                </select>
            </label>
            <label>
                <span>📄 New Page per Group:</span>
                <select id="newPagePerGroup">
                    <option value="yes" selected>Yes (Start each group on new page)</option>
                    <option value="no">No (Continue on same page)</option>
                </select>
            </label>
        </div>

        <div class="settings-group">
            <label>
                <input type="checkbox" id="showPhotosCheckbox" {{ $show_photos ? 'checked' : '' }}>
                📷 Show Student Photos
            </label>
            <label>
                <input type="checkbox" id="showSnCheckbox" {{ $show_sn ? 'checked' : '' }}>
                🔢 Show Serial Numbers
            </label>
        </div>

        <div>
            <h4 style="font-size:12px; margin-bottom:8px;">📋 Columns to Display:</h4>
            <div class="columns-grid" id="columnsGrid">
                @php
                $columnOptions = [
                    'admissionno' => 'Admission Number',
                    'firstname' => 'First Name',
                    'lastname' => 'Last Name',
                    'gender' => 'Gender',
                    'dateofbirth' => 'Date of Birth',
                    'arm' => 'Arm/Class',
                    'total_cum' => 'Cumulative Total',
                    'total_term' => 'Term Total',
                    'position_cum' => 'Overall Position (Cum)',
                    'position_term' => 'Overall Position (Term)',
                    'gpa' => 'GPA',
                ];
                @endphp
                @foreach($columnOptions as $key => $label)
                    <label>
                        <input type="checkbox" class="column-checkbox" value="{{ $key }}"
                            {{ in_array($key, $list_fields) ? 'checked' : '' }}>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="settings-group" style="margin-top: 15px;">
            <button class="btn-print" onclick="applySettingsAndRefresh()" style="padding: 6px 16px; background: #7c3aed;">
                Apply Settings & Refresh
            </button>
        </div>
    </div>

    {{-- ── SCHOOL HEADER ── --}}
    <div class="school-header" id="schoolHeader">
        @if(!empty($school_logo_base64))
            <img src="{{ $school_logo_base64 }}" class="school-logo" alt="Logo">
        @else
            <div class="school-logo-placeholder">
                {{ strtoupper(substr($schoolInfo->school_name ?? 'S', 0, 2)) }}
            </div>
        @endif
        <div class="school-info">
            <div class="school-name">{{ $schoolInfo->school_name ?? 'SCHOOL NAME' }}</div>
            @if(!empty($schoolInfo->school_address))
                <div class="school-address">{{ $schoolInfo->school_address }}</div>
            @endif
            @if(!empty($schoolInfo->school_motto))
                <div class="school-motto">"{{ $schoolInfo->school_motto }}"</div>
            @endif
        </div>
        <div style="width:80px;flex-shrink:0;"></div>
    </div>

    {{-- ── TITLE BAR ── --}}
    <div class="list-title-bar">STUDENT PROMOTION RECOMMENDATION LIST</div>

    {{-- ── META STRIP ── --}}
    <div class="meta-strip">
        <div class="meta-cell">
            <span class="meta-label">Class</span>
            <span class="meta-value">{{ ($schoolclass->schoolclass ?? '-') . ' ' . ($schoolclass->arm_name ?? '') }}</span>
        </div>
        <div class="meta-cell">
            <span class="meta-label">Session</span>
            <span class="meta-value">{{ $schoolsession->session ?? '-' }}</span>
        </div>
        <div class="meta-cell">
            <span class="meta-label">Term</span>
            <span class="meta-value">{{ $schoolterm->term ?? '-' }}</span>
        </div>
        <div class="meta-cell">
            <span class="meta-label">Total Students</span>
            <span class="meta-value">{{ $totalStudents }}</span>
        </div>
        <div class="meta-cell">
            <span class="meta-label">Generated</span>
            <span class="meta-value" style="font-size:11px;">{{ $generatedAt }}</span>
        </div>
    </div>

    @php
    /* ── Field display label map ── */
    $allFields = [
        'admissionno'   => 'Admission No',
        'firstname'     => 'First Name',
        'lastname'      => 'Last Name',
        'gender'        => 'Gender',
        'dateofbirth'   => 'Date of Birth',
        'arm'           => 'Arm',
        'total_cum'     => 'Cum Total',
        'total_term'    => 'Term Total',
        'position_cum'  => 'Overall Pos (Cum)',
        'position_term' => 'Overall Pos (Term)',
        'gpa'           => 'GPA',
    ];

    /* ── Status meta (fallback labels / icons / CSS classes) ── */
    $statusMeta = [
        'promoted'      => ['label' => 'Promoted',            'icon' => '✅', 'class' => 'status-promoted'],
        'trial'         => ['label' => 'Promoted on Trial',   'icon' => '⚠️', 'class' => 'status-trial'],
        'see_principal' => ['label' => 'See Principal',       'icon' => '👤', 'class' => 'status-see_principal'],
        'repeated'      => ['label' => 'Repeat',              'icon' => '🔁', 'class' => 'status-repeated'],
        'awaiting'      => ['label' => 'Awaiting Decision',   'icon' => '⏳', 'class' => 'status-awaiting'],
        '__other'       => ['label' => 'Other',               'icon' => '📌', 'class' => 'status-other'],
    ];

    /* ── Ordinal helper ── */
    function listOrdinal($n) {
        if (!$n) return '—';
        $n = (int)$n;
        $s = ['th','st','nd','rd'];
        $v = $n % 100;
        return $n . ($s[($v-20)%10] ?? $s[$v] ?? $s[0]);
    }

    $globalSn = 0;
    @endphp

    {{-- ══════════════════════════════════════════════════════════
         GROUPED STUDENT SECTIONS
    ══════════════════════════════════════════════════════════ --}}
    <div id="studentListContent">
        @foreach($grouped_students as $statusKey => $students)
            @php
                $meta = $statusMeta[$statusKey] ?? $statusMeta['__other'];
                $groupLabel = $students[0]['promotion_label'] ?? $meta['label'];
                $groupCount = count($students);
                $hasArm = !empty($students[0]['arm']);
            @endphp
            <div class="group-section" data-status="{{ $statusKey }}">
                <div class="group-header {{ $meta['class'] }}">
                    <span style="font-size:18px;">{{ $meta['icon'] }}</span>
                    <span>{{ $groupLabel }}</span>
                    @if($hasArm)
                        <span style="font-size:12px; font-weight:normal; margin-left:8px;">
                            📍 Arm: {{ $students[0]['arm'] }}
                        </span>
                    @endif
                    <span class="count-badge">{{ $groupCount }} Student{{ $groupCount === 1 ? '' : 's' }}</span>
                </div>

                <div class="table-responsive-wrapper">
                    <table class="student-table">
                        <thead>
                            <tr>
                                @if($show_sn)
                                    <th style="width:40px;text-align:center;">#</th>
                                @endif
                                @if($show_photos)
                                    <th style="width:44px;"></th>
                                @endif

                                {{-- Name columns header ── handle combined vs separate --}}
                                @if(in_array('firstname', $list_fields) && in_array('lastname', $list_fields))
                                    <th>Student Name</th>
                                @elseif(in_array('firstname', $list_fields))
                                    <th>First Name</th>
                                @elseif(in_array('lastname', $list_fields))
                                    <th>Last Name</th>
                                @endif

                                {{-- All other fields --}}
                                @foreach($list_fields as $field)
                                    @if(in_array($field, ['firstname','lastname','name'])) @continue @endif
                                    @if(isset($allFields[$field]))
                                        <th>{{ $allFields[$field] }}</th>
                                    @endif
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $idx => $stu)
                                @php
                                    $globalSn++;
                                    $hasPic = !empty($stu['picture']) && $stu['picture'] !== 'unnamed.jpg';
                                    $imgSrc = $hasPic ? asset('storage/student_avatars/' . basename($stu['picture'])) : null;
                                    $initials = strtoupper(substr($stu['lastname']??'',0,1) . substr($stu['firstname']??'',0,1)) ?: 'ST';
                                @endphp
                                <tr>
                                    @if($show_sn)
                                        <td class="sn-cell">{{ $globalSn }}</td>
                                    @endif

                                    @if($show_photos)
                                        <td style="padding:6px 10px;width:44px;">
                                            @if($imgSrc)
                                                <img src="{{ $imgSrc }}" class="student-avatar" alt="{{ $stu['firstname'] }}"
                                                     onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex'">
                                                <span class="avatar-initials" style="display:none;">{{ $initials }}</span>
                                            @else
                                                <span class="avatar-initials">{{ $initials }}</span>
                                            @endif
                                        </td>
                                    @endif

                                    {{-- Name column(s) ── always render name first --}}
                                    @if(in_array('firstname', $list_fields) && in_array('lastname', $list_fields))
                                        <td class="name-cell">
                                            {{ strtoupper($stu['lastname'] ?? '') }}, {{ $stu['firstname'] ?? '' }}
                                            @if(!empty($stu['arm']) && !in_array('arm', $list_fields))
                                                <span style="font-size:10px; color:#64748b; margin-left:8px;">[{{ $stu['arm'] }}]</span>
                                            @endif
                                        </td>
                                    @elseif(in_array('firstname', $list_fields))
                                        <td class="name-cell">
                                            {{ $stu['firstname'] ?? '' }}
                                            @if(!empty($stu['arm']) && !in_array('arm', $list_fields))
                                                <span style="font-size:10px; color:#64748b; margin-left:8px;">[{{ $stu['arm'] }}]</span>
                                            @endif
                                        </td>
                                    @elseif(in_array('lastname', $list_fields))
                                        <td class="name-cell">
                                            {{ strtoupper($stu['lastname'] ?? '') }}
                                            @if(!empty($stu['arm']) && !in_array('arm', $list_fields))
                                                <span style="font-size:10px; color:#64748b; margin-left:8px;">[{{ $stu['arm'] }}]</span>
                                            @endif
                                        </td>
                                    @endif

                                    {{-- All other selected fields --}}
                                    @foreach($list_fields as $field)
                                        @if(in_array($field, ['firstname','lastname','name'])) @continue @endif

                                        @if($field === 'admissionno')
                                            <td class="adm-cell">{{ $stu['admissionno'] ?? '—' }}</td>

                                        @elseif($field === 'gender')
                                            <td class="gender-cell">{{ $stu['gender'] ?? '—' }}</td>

                                        @elseif($field === 'dateofbirth')
                                            <td>{{ !empty($stu['dateofbirth']) ? \Carbon\Carbon::parse($stu['dateofbirth'])->format('d M Y') : '—' }}</td>

                                        @elseif($field === 'arm')
                                            <td class="arm-cell">{{ $stu['arm'] ?: '—' }}</td>

                                        @elseif($field === 'total_cum')
                                            <td style="text-align:center;font-weight:700;">{{ $stu['total_cum'] ?? '—' }}</td>

                                        @elseif($field === 'total_term')
                                            <td style="text-align:center;font-weight:700;">{{ $stu['total_term'] ?? '—' }}</td>

                                        @elseif($field === 'position_cum')
                                            <td style="text-align:center;font-weight:700;color:#1e40af;">
                                                {{ listOrdinal($stu['position_cum'] ?? null) }}
                                            </td>

                                        @elseif($field === 'position_term')
                                            <td style="text-align:center;font-weight:700;color:#92400e;">
                                                {{ listOrdinal($stu['position_term'] ?? null) }}
                                            </td>

                                        @elseif($field === 'gpa')
                                            <td style="text-align:center;">{{ number_format($stu['gpa'] ?? 0, 2) }}</td>

                                        @endif
                                    @endforeach
                                </td>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ══════════════════════════════════════════════════════════
         SUMMARY FOOTER
    ══════════════════════════════════════════════════════════ --}}
    @php
        $summaryGroups = [];
        foreach($grouped_students as $statusKey => $students) {
            $meta = $statusMeta[$statusKey] ?? $statusMeta['__other'];
            $label = !empty($students[0]['promotion_label']) ? $students[0]['promotion_label'] : $meta['label'];
            $summaryGroups[] = [
                'label' => $label,
                'count' => count($students),
                'bgColor' => match($statusKey) {
                    'promoted' => '#d1fae5',
                    'trial' => '#fef3c7',
                    'see_principal' => '#dbeafe',
                    'repeated' => '#fee2e2',
                    default => '#f1f5f9',
                },
                'textColor' => match($statusKey) {
                    'promoted' => '#065f46',
                    'trial' => '#92400e',
                    'see_principal' => '#1e40af',
                    'repeated' => '#991b1b',
                    default => '#475569',
                },
            ];
        }
    @endphp
    <div class="summary-footer">
        <h4>📊 Summary by Recommendation</h4>
        <div class="summary-grid">
            @foreach($summaryGroups as $sg)
                <div class="summary-item" style="background:{{ $sg['bgColor'] }};border-color:{{ $sg['bgColor'] }};">
                    <span class="summary-count" style="color:{{ $sg['textColor'] }};">{{ $sg['count'] }}</span>
                    <span class="summary-lbl" style="color:{{ $sg['textColor'] }};">{{ $sg['label'] }}</span>
                </div>
            @endforeach
            <div class="summary-item" style="background:#0f2342;border-color:#0f2342;">
                <span class="summary-count" style="color:white;">{{ $totalStudents }}</span>
                <span class="summary-lbl" style="color:rgba(255,255,255,.75);">Total Students</span>
            </div>
        </div>
    </div>

    <div class="generated-line">
        Generated: {{ $generatedAt }} &nbsp;·&nbsp;
        {{ ($schoolclass->schoolclass ?? '') . ' ' . ($schoolclass->arm_name ?? '') }}
        &nbsp;·&nbsp; {{ $schoolsession->session ?? '' }}
        &nbsp;·&nbsp; {{ $schoolterm->term ?? '' }}
    </div>

</div>

<script>
// Settings panel toggle
function toggleSettings() {
    var panel = document.getElementById('settingsPanel');
    var btn = document.getElementById('toggleSettingsBtn');
    panel.classList.toggle('open');
    btn.classList.toggle('active');
}

// Apply page break based on setting
function applyPageBreaks() {
    var newPagePerGroup = localStorage.getItem('new_page_per_group') || 'yes';
    var groups = document.querySelectorAll('.group-section');

    groups.forEach(function(group, index) {
        if (newPagePerGroup === 'yes') {
            if (index < groups.length - 1) {
                group.style.pageBreakAfter = 'always';
            } else {
                group.style.pageBreakAfter = 'auto';
            }
        } else {
            group.style.pageBreakAfter = 'auto';
        }
    });
}

// Print with orientation and page breaks
function printStudentList() {
    var orientation = document.getElementById('printOrientation').value;
    var paperSize = document.getElementById('paperSize').value;
    var newPagePerGroup = document.getElementById('newPagePerGroup').value;

    // Apply page breaks
    var groups = document.querySelectorAll('.group-section');
    groups.forEach(function(group, index) {
        if (newPagePerGroup === 'yes') {
            if (index < groups.length - 1) {
                group.style.pageBreakAfter = 'always';
            } else {
                group.style.pageBreakAfter = 'auto';
            }
        } else {
            group.style.pageBreakAfter = 'auto';
        }
    });

    var style = document.createElement('style');
    style.textContent = '@page { size: ' + paperSize + ' ' + orientation + '; margin: 1.2cm; }';
    document.head.appendChild(style);

    window.print();

    setTimeout(function() {
        document.head.removeChild(style);
        // Reset page breaks
        groups.forEach(function(group) {
            group.style.pageBreakAfter = '';
        });
    }, 100);
}

// Export to PDF using browser print with PDF target
function exportToPDF() {
    var orientation = document.getElementById('printOrientation').value;
    var paperSize = document.getElementById('paperSize').value;
    var newPagePerGroup = document.getElementById('newPagePerGroup').value;

    // Apply page breaks
    var groups = document.querySelectorAll('.group-section');
    groups.forEach(function(group, index) {
        if (newPagePerGroup === 'yes') {
            if (index < groups.length - 1) {
                group.style.pageBreakAfter = 'always';
            } else {
                group.style.pageBreakAfter = 'auto';
            }
        } else {
            group.style.pageBreakAfter = 'auto';
        }
    });

    var loading = document.getElementById('pdfLoading');
    loading.classList.add('active');

    var style = document.createElement('style');
    style.textContent = '@page { size: ' + paperSize + ' ' + orientation + '; margin: 1.2cm; }';
    document.head.appendChild(style);

    setTimeout(function() {
        window.print();
        loading.classList.remove('active');
        setTimeout(function() {
            if (style.parentNode) document.head.removeChild(style);
            // Reset page breaks
            groups.forEach(function(group) {
                group.style.pageBreakAfter = '';
            });
        }, 500);
    }, 500);
}

// Apply settings and refresh page with new column selection
function applySettingsAndRefresh() {
    var selectedColumns = [];
    document.querySelectorAll('.column-checkbox:checked').forEach(function(cb) {
        selectedColumns.push(cb.value);
    });

    var showPhotos = document.getElementById('showPhotosCheckbox').checked;
    var showSn = document.getElementById('showSnCheckbox').checked;
    var orientation = document.getElementById('printOrientation').value;
    var paperSize = document.getElementById('paperSize').value;
    var newPagePerGroup = document.getElementById('newPagePerGroup').value;

    // Store preferences
    localStorage.setItem('student_list_columns', JSON.stringify(selectedColumns));
    localStorage.setItem('student_list_show_photos', showPhotos);
    localStorage.setItem('student_list_show_sn', showSn);
    localStorage.setItem('student_list_orientation', orientation);
    localStorage.setItem('student_list_paper_size', paperSize);
    localStorage.setItem('new_page_per_group', newPagePerGroup);

    // Build URL with parameters
    var url = window.location.pathname;
    var params = new URLSearchParams();
    params.set('list_fields', selectedColumns.join(','));
    params.set('show_photos', showPhotos ? '1' : '0');
    params.set('show_sn', showSn ? '1' : '0');

    // Also preserve existing parameters
    var existingParams = new URLSearchParams(window.location.search);
    for (var pair of existingParams.entries()) {
        if (!params.has(pair[0]) && pair[0] !== 'list_fields' && pair[0] !== 'show_photos' && pair[0] !== 'show_sn') {
            params.set(pair[0], pair[1]);
        }
    }

    window.location.href = url + '?' + params.toString();
}

// Load saved preferences on page load
document.addEventListener('DOMContentLoaded', function() {
    var savedColumns = localStorage.getItem('student_list_columns');
    if (savedColumns) {
        var columns = JSON.parse(savedColumns);
        document.querySelectorAll('.column-checkbox').forEach(function(cb) {
            cb.checked = columns.includes(cb.value);
        });
    }

    var savedShowPhotos = localStorage.getItem('student_list_show_photos');
    if (savedShowPhotos !== null) {
        document.getElementById('showPhotosCheckbox').checked = savedShowPhotos === 'true';
    }

    var savedShowSn = localStorage.getItem('student_list_show_sn');
    if (savedShowSn !== null) {
        document.getElementById('showSnCheckbox').checked = savedShowSn === 'true';
    }

    var savedOrientation = localStorage.getItem('student_list_orientation');
    if (savedOrientation) {
        document.getElementById('printOrientation').value = savedOrientation;
    }

    var savedPaperSize = localStorage.getItem('student_list_paper_size');
    if (savedPaperSize) {
        document.getElementById('paperSize').value = savedPaperSize;
    }

    var savedNewPage = localStorage.getItem('new_page_per_group');
    if (savedNewPage) {
        document.getElementById('newPagePerGroup').value = savedNewPage;
    }

    // Apply page breaks based on saved setting
    applyPageBreaks();
});

// Prevent Settings panel from printing
window.onbeforeprint = function() {
    var panel = document.getElementById('settingsPanel');
    if (panel && panel.classList.contains('open')) {
        panel.style.display = 'none';
    }
};

window.onafterprint = function() {
    var panel = document.getElementById('settingsPanel');
    if (panel && panel.classList.contains('open')) {
        panel.style.display = 'block';
    }
};
</script>
</body>
</html>
