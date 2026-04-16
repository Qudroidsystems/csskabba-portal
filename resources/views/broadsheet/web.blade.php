{{--
  broadsheet/web.blade.php
  Full interactive web view of the broadsheet with:
  • Sticky header + frozen left columns
  • Smart "Navigate To" dropdown
  • Zoom controls
  • Highlight on search/navigate
--}}
@extends('layouts.master')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Broadsheet – Web View</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=Sora:wght@400;600;700;800&display=swap');

/* ──────────────────────────────────────────
   DESIGN TOKENS
   ────────────────────────────────────────── */
:root {
  --navy:       #0d1b2a;
  --navy2:      #1a2f45;
  --blue:       #1d6fa4;
  --blue-lt:    #2a8fc9;
  --accent:     #f59e0b;
  --accent2:    #10b981;
  --danger:     #ef4444;
  --border:     #1e3a5240;
  --row-odd:    #ffffff;
  --row-even:   #f0f6fb;
  --highlight:  #fff3cd;
  --hl-ring:    #f59e0b;
  --frozen-bg:  #e8f0f9;
  --frozen-bdr: #1d6fa4;
  --ctrl-bg:    #111c2a;
  --ctrl-text:  #cfe4f5;
  --ctrl-bdr:   #1d6fa455;

  --font-head:  'Sora', sans-serif;
  --font-mono:  'IBM Plex Mono', monospace;

  --cell-h:     34px;
  --hdr-h:      56px;
  --ctrl-h:     58px;
  --zoom:       1;
}

/* ──────────────────────────────────────────
   LAYOUT
   ────────────────────────────────────────── */
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: var(--font-head);
  background: #f0f5fb;
  color: var(--navy);
  min-height: 100vh;
}

/* control bar */
.ctrl-bar {
  position: sticky;
  top: 0;
  z-index: 200;
  background: var(--ctrl-bg);
  border-bottom: 1px solid var(--ctrl-bdr);
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 0 18px;
  height: var(--ctrl-h);
  flex-wrap: wrap;
  row-gap: 6px;
}

.ctrl-logo {
  font-size: 13px;
  font-weight: 800;
  color: #fff;
  letter-spacing: 1px;
  white-space: nowrap;
  margin-right: 6px;
}
.ctrl-logo span { color: var(--accent); }

/* navigate dropdown */
.nav-wrapper {
  position: relative;
  flex: 1;
  max-width: 340px;
}
.nav-search {
  width: 100%;
  background: #1a2f45;
  border: 1px solid var(--ctrl-bdr);
  border-radius: 8px;
  color: #fff;
  font-family: var(--font-head);
  font-size: 12.5px;
  padding: 7px 36px 7px 14px;
  outline: none;
  cursor: pointer;
  transition: border-color .2s;
}
.nav-search:focus { border-color: var(--accent); }
.nav-search::placeholder { color: #6b8fa8; }
.nav-arrow {
  position: absolute;
  right: 10px; top: 50%;
  transform: translateY(-50%);
  color: #6b8fa8;
  pointer-events: none;
  font-size: 11px;
}
.nav-dropdown {
  position: absolute;
  top: calc(100% + 6px);
  left: 0;
  width: 320px;
  background: #0d1b2a;
  border: 1px solid var(--ctrl-bdr);
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 12px 40px rgba(0,0,0,.5);
  display: none;
  z-index: 9999;
  max-height: 440px;
  overflow-y: auto;
}
.nav-dropdown.open { display: block; }

.nav-filter-input {
  width: 100%;
  background: #1a2f45;
  border: none;
  border-bottom: 1px solid var(--ctrl-bdr);
  color: #fff;
  font-size: 12px;
  padding: 10px 14px;
  outline: none;
}
.nav-filter-input::placeholder { color: #5a7a92; }

.nav-group-label {
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 1.2px;
  color: var(--accent);
  padding: 8px 14px 4px;
  text-transform: uppercase;
  background: #0b1624;
}
.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 14px;
  cursor: pointer;
  font-size: 12.5px;
  color: var(--ctrl-text);
  transition: background .15s;
  border-bottom: 1px solid #1a2f4555;
}
.nav-item:hover { background: #1a2f45; }
.nav-item.active { background: #1d3d57; }
.nav-icon {
  width: 22px; height: 22px;
  border-radius: 5px;
  display: flex; align-items: center; justify-content: center;
  font-size: 11px;
  flex-shrink: 0;
}
.nav-item-meta { font-size: 10px; color: #5a7a92; margin-top: 1px; }

/* zoom controls */
.zoom-group {
  display: flex;
  align-items: center;
  gap: 5px;
  background: #1a2f45;
  border: 1px solid var(--ctrl-bdr);
  border-radius: 8px;
  padding: 4px 10px;
}
.zoom-btn {
  background: none;
  border: none;
  color: var(--ctrl-text);
  font-size: 16px;
  cursor: pointer;
  padding: 2px 6px;
  border-radius: 4px;
  transition: background .15s;
  line-height: 1;
}
.zoom-btn:hover { background: #243d56; }
.zoom-val {
  font-family: var(--font-mono);
  font-size: 11px;
  color: var(--accent);
  min-width: 36px;
  text-align: center;
}

/* export buttons */
.ctrl-export {
  display: flex;
  align-items: center;
  gap: 7px;
}
.ctrl-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  border-radius: 7px;
  border: none;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity .15s, transform .1s;
  white-space: nowrap;
}
.ctrl-btn:hover { opacity: .88; transform: translateY(-1px); }
.ctrl-btn.pdf   { background: #c0392b; color: #fff; }
.ctrl-btn.excel { background: #16a34a; color: #fff; }
.ctrl-btn.print { background: #1d6fa4; color: #fff; }

/* pill stats */
.meta-pills {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 20px;
  background: var(--navy2);
  border-bottom: 1px solid var(--ctrl-bdr);
  flex-wrap: wrap;
}
.pill {
  font-size: 11.5px;
  font-weight: 600;
  padding: 4px 12px;
  border-radius: 20px;
  white-space: nowrap;
}
.pill-blue   { background: #1d6fa420; color: #5bc0de; border: 1px solid #1d6fa455; }
.pill-gold   { background: #f59e0b20; color: #f59e0b; border: 1px solid #f59e0b55; }
.pill-green  { background: #10b98120; color: #10b981; border: 1px solid #10b98155; }
.pill-red    { background: #ef444420; color: #ef8080; border: 1px solid #ef444455; }
.pill-white  { background: #ffffff15; color: #c0d8ee; border: 1px solid #ffffff25; }

/* highlight pulse */
@keyframes hlPulse {
  0%   { box-shadow: 0 0 0 3px var(--hl-ring); }
  50%  { box-shadow: 0 0 0 8px transparent; }
  100% { box-shadow: 0 0 0 0 transparent; }
}
.hl-cell {
  background: var(--highlight) !important;
  animation: hlPulse .6s ease-out 2;
  position: relative;
  z-index: 10;
}
.hl-row td { background: #fffbe8 !important; }
.hl-col    { background: #e8f5ff !important; }

/* ──────────────────────────────────────────
   SCHOOL HEADER CARD
   ────────────────────────────────────────── */
.school-banner {
  background: linear-gradient(120deg, var(--navy) 0%, var(--navy2) 60%, #1d3d57 100%);
  padding: 22px 28px 18px;
  display: flex;
  align-items: center;
  gap: 20px;
  border-bottom: 3px solid var(--accent);
}
.banner-logo img {
  width: 72px; height: 72px;
  border-radius: 50%;
  border: 2px solid var(--accent);
  object-fit: contain;
  background: #fff;
}
.banner-text { flex: 1; }
.banner-name {
  font-size: 20px;
  font-weight: 800;
  color: #fff;
  letter-spacing: .5px;
}
.banner-sub {
  font-size: 12px;
  color: #7ab3d0;
  margin-top: 3px;
}
.banner-motto {
  font-size: 12px;
  color: var(--accent);
  font-style: italic;
  margin-top: 4px;
}
.banner-badge {
  text-align: right;
  color: #7ab3d0;
  font-size: 11.5px;
  line-height: 1.8;
}
.banner-badge strong { color: #fff; }

/* doc title strip */
.doc-strip {
  background: linear-gradient(90deg, var(--blue) 0%, #1a3a60 100%);
  color: #fff;
  text-align: center;
  padding: 9px;
  font-size: 13px;
  font-weight: 800;
  letter-spacing: 2px;
  text-transform: uppercase;
  border-bottom: 2px solid var(--accent);
}

/* grade key */
.grade-key-bar {
  background: #f8fbff;
  border-bottom: 1px solid #d0e4f0;
  padding: 7px 18px;
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
  font-size: 11px;
}
.gk-lbl { font-weight: 700; color: var(--navy2); margin-right: 4px; }
.gk-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 2px 8px 2px 5px;
  border-radius: 4px;
  font-size: 10.5px;
  font-weight: 600;
}
.gk-badge .gk-g {
  font-weight: 800;
  background: rgba(255,255,255,.25);
  padding: 1px 4px;
  border-radius: 3px;
}

/* ──────────────────────────────────────────
   SCROLL WRAPPER
   ────────────────────────────────────────── */
.table-scroll-wrapper {
  overflow-x: auto;
  overflow-y: auto;
  max-height: calc(100vh - 170px);
  position: relative;
  background: #fff;
  border-bottom: 2px solid var(--border);
}
.table-scroll-wrapper::-webkit-scrollbar { height: 7px; width: 7px; }
.table-scroll-wrapper::-webkit-scrollbar-track { background: #e8f0f9; }
.table-scroll-wrapper::-webkit-scrollbar-thumb { background: #1d6fa4; border-radius: 4px; }

/* ──────────────────────────────────────────
   BROADSHEET TABLE
   ────────────────────────────────────────── */
.bs-table {
  border-collapse: separate;
  border-spacing: 0;
  font-size: 11.5px;
  white-space: nowrap;
  width: max-content;
  transform-origin: top left;
}

/* sticky header */
.bs-table thead tr.hdr-subj th,
.bs-table thead tr.hdr-asm  th {
  position: sticky;
  top: 0;
  z-index: 50;
  border-bottom: 1px solid #2563eb66;
}
.bs-table thead tr.hdr-subj th { top: 0; }
.bs-table thead tr.hdr-asm  th { top: var(--hdr-h); }

/* frozen left columns */
.bs-table td.frozen,
.bs-table th.frozen {
  position: sticky;
  left: 0;
  z-index: 40;
  background: var(--frozen-bg);
  border-right: 2px solid var(--frozen-bdr);
}
.bs-table td.frozen-2,
.bs-table th.frozen-2 {
  position: sticky;
  z-index: 40;
  background: var(--frozen-bg);
}
/* multiple frozen columns stagger */
.bs-table .f0 { left: 0; }
.bs-table .f1 { left: 28px; }
.bs-table .f2 { left: 80px; }
.bs-table .f3 { left: 170px; }

.bs-table thead th.frozen,
.bs-table thead th.frozen-2 {
  z-index: 80;
  background: #0d1b2a;
}

/* header cells */
.bs-table thead tr.hdr-subj th {
  background: var(--navy);
  color: #fff;
  padding: 8px 6px;
  font-size: 11px;
  font-weight: 700;
  text-align: center;
  border: 1px solid #2563eb33;
  height: var(--hdr-h);
  vertical-align: bottom;
}
.bs-table thead tr.hdr-subj th.subj-hdr {
  background: #163562;
  border-left: 2px solid var(--blue-lt);
  font-size: 10.5px;
  max-width: 90px;
  overflow: hidden;
  text-overflow: ellipsis;
  padding-bottom: 6px;
}
.bs-table thead tr.hdr-asm th {
  background: #1a3d6a;
  color: #a8d4ef;
  padding: 5px 4px;
  font-size: 10px;
  text-align: center;
  border: 1px solid #2563eb22;
  height: 38px;
}
.bs-table thead tr.hdr-asm th.sub-boundary { border-left: 2px solid var(--blue-lt); }

/* body cells */
.bs-table tbody td {
  padding: 5px 5px;
  border: 0.5px solid #c8dced;
  text-align: center;
  vertical-align: middle;
  height: var(--cell-h);
  transition: background .12s;
}
.bs-table tbody td.name-cell {
  text-align: left;
  padding-left: 8px;
  font-weight: 600;
  min-width: 130px;
  color: var(--navy);
}
.bs-table tbody td.adm-cell {
  font-family: var(--font-mono);
  font-size: 10.5px;
  color: #4a6e8a;
}
.bs-table tbody td.sn-cell {
  color: #8ca8be;
  font-size: 10px;
  font-family: var(--font-mono);
  width: 28px;
}
/* subject boundary */
.bs-table tbody td.sub-start { border-left: 2px solid #1d6fa466; }

/* row stripes */
.bs-table tbody tr:nth-child(odd) td  { background: var(--row-odd); }
.bs-table tbody tr:nth-child(even) td { background: var(--row-even); }
.bs-table tbody tr:hover td { background: #dbeeff !important; cursor: default; }

/* grade colours */
.g-a1 { background: #d1fae5 !important; color: #065f46; font-weight: 700; }
.g-b2 { background: #dbeafe !important; color: #1e3a8a; }
.g-b3 { background: #e0eeff !important; color: #1e40af; }
.g-c4 { background: #fef9c3 !important; color: #854d0e; }
.g-c5 { background: #fef3c7 !important; color: #92400e; }
.g-c6 { background: #fde68a !important; color: #78350f; }
.g-d7 { background: #ffedd5 !important; color: #9a3412; }
.g-e8 { background: #fed7aa !important; color: #9a3412; }
.g-f9 { background: #fee2e2 !important; color: #991b1b; font-weight: 700; }

/* stats footer rows */
.stats-row td {
  background: #0d1b2a !important;
  color: #a8d4ef !important;
  font-weight: 700 !important;
  font-size: 10.5px;
  padding: 5px 4px;
  border: 0.5px solid #163562;
}
.stats-row td.stats-lbl {
  text-align: left;
  padding-left: 8px;
  color: var(--accent) !important;
  font-family: var(--font-mono);
  font-size: 10px;
}
.stats-hi td  { background: #0a2240 !important; }
.stats-lo td  { background: #111c2a !important; }
.stats-avg td { background: #0d2035 !important; }

/* GPA column */
.gpa-cell {
  background: #eff6ff !important;
  color: #1e3a8a;
  font-weight: 700;
  border-left: 2px solid #3b82f6 !important;
}

/* ──────────────────────────────────────────
   SUMMARY TABLE
   ────────────────────────────────────────── */
.summary-section {
  padding: 22px 20px 32px;
  background: #f0f5fb;
  border-top: 2px solid var(--border);
}
.summary-title {
  font-size: 14px;
  font-weight: 800;
  color: var(--navy2);
  margin-bottom: 14px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.sum-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12px;
  max-width: 800px;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 2px 12px rgba(0,0,0,.08);
}
.sum-table thead tr th {
  background: var(--navy);
  color: #a8d4ef;
  padding: 9px 12px;
  text-align: left;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: .5px;
}
.sum-table tbody tr:nth-child(even) { background: #e8f3fc; }
.sum-table tbody tr:nth-child(odd)  { background: #fff; }
.sum-table tbody td {
  padding: 8px 12px;
  border-bottom: 1px solid #d0e4f0;
  color: var(--navy2);
}
.pass-badge {
  display: inline-block;
  padding: 2px 9px;
  border-radius: 12px;
  font-weight: 700;
  font-size: 11px;
}
.pass-good { background: #d1fae5; color: #065f46; }
.pass-bad  { background: #fee2e2; color: #991b1b; }

/* ──────────────────────────────────────────
   SIGNATURE + FOOTER
   ────────────────────────────────────────── */
.sig-bar {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  padding: 20px 24px 30px;
  background: #fff;
  border-top: 1px solid #d0e4f0;
}
.sig-box { text-align: center; }
.sig-line {
  border-top: 1.5px solid #4a6e8a;
  padding-top: 6px;
  margin-top: 30px;
  font-size: 11.5px;
  font-weight: 600;
  color: var(--navy2);
}
.pg-footer {
  background: var(--navy);
  color: #5a7a92;
  font-size: 10.5px;
  padding: 10px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.pg-footer a { color: var(--accent); text-decoration: none; }

/* ──────────────────────────────────────────
   SCROLL-TO BEACON
   ────────────────────────────────────────── */
.beacon {
  display: inline-block;
  width: 0; height: 0;
  vertical-align: middle;
}

/* ──────────────────────────────────────────
   PRINT
   ────────────────────────────────────────── */
@media print {
  .ctrl-bar, .meta-pills, .grade-key-bar { display: none !important; }
  .table-scroll-wrapper {
    overflow: visible;
    max-height: none;
  }
  .bs-table td.frozen,
  .bs-table th.frozen,
  .bs-table td.frozen-2,
  .bs-table th.frozen-2 {
    position: static;
  }
  body { background: #fff; }
}

@media (max-width: 768px) {
  .banner-badge { display: none; }
  .sig-bar { grid-template-columns: 1fr 1fr; }
}
</style>
</head>
<body>

@php
/* ── Derived flags ── */
$sel = $selectedColumns ?? [];
$showAdmNo    = empty($sel) || in_array('admission_no', $sel);
$showGender   = in_array('gender', $sel);
$showTotal    = empty($sel) || in_array('total', $sel);
$showBF       = in_array('bf', $sel);
$showCum      = empty($sel) || in_array('cum', $sel);
$showGrade    = empty($sel) || in_array('grade', $sel);
$showPosition = empty($sel) || in_array('position', $sel);
$showAvg      = in_array('class_average', $sel);
$showRemark   = in_array('remark', $sel);
$showGPA      = in_array('gpa', $sel);
$showCGPA     = in_array('cgpa', $sel);
$showGPAGrade = in_array('gpa_grade', $sel);
$showNumSub   = in_array('num_subjects', $sel);
$showTotalGP  = in_array('total_grade_points', $sel);

$activeAssessments = $assessments->filter(fn($a) =>
    empty($sel) || in_array('assessment_' . $a->id, $sel)
);

$gradeClass = [
    'A1'=>'g-a1','B2'=>'g-b2','B3'=>'g-b3',
    'C4'=>'g-c4','C5'=>'g-c5','C6'=>'g-c6',
    'D7'=>'g-d7','E8'=>'g-e8','F9'=>'g-f9',
    '-'=>'',
];

/* frozen cols offset helper */
$frozenCount = 1 + ($showAdmNo ? 1 : 0) + 1 + ($showGender ? 1 : 0);
@endphp

{{-- ══════════════════════════════════════════
     CONTROL BAR
     ══════════════════════════════════════════ --}}
<div class="ctrl-bar">
  <div class="ctrl-logo">BROAD<span>SHEET</span></div>

  {{-- ── NAVIGATE TO DROPDOWN ── --}}
  <div class="nav-wrapper" id="navWrapper">
    <input class="nav-search" id="navTrigger" readonly
           placeholder="⌕  Navigate to…  ( students · subjects · stats · GPA … )"
           onclick="toggleNav()">
    <span class="nav-arrow">▾</span>

    <div class="nav-dropdown" id="navDropdown">
      <input class="nav-filter-input" id="navFilter"
             placeholder="Type to filter options…"
             oninput="filterNavItems(this.value)">

      <div id="navList">
        {{-- ── Section: Students ── --}}
        <div class="nav-group-label" data-nav-group>👤 Jump to Student</div>
        @foreach($studentRows as $idx => $stu)
          <div class="nav-item"
               data-nav-label="{{ strtoupper($stu['lastname']) }}, {{ $stu['firstname'] }}"
               data-nav-type="student"
               data-nav-target="row-{{ $stu['id'] }}"
               onclick="navigateTo(this)">
            <span class="nav-icon" style="background:#1d3d57;color:#5bc0de;">
              {{ $idx + 1 }}
            </span>
            <div>
              <div>{{ strtoupper($stu['lastname']) }}, {{ $stu['firstname'] }}</div>
              <div class="nav-item-meta">{{ $stu['admissionno'] }} · GPA {{ number_format($stu['gpa'], 2) }}</div>
            </div>
          </div>
        @endforeach

        {{-- ── Section: Subjects ── --}}
        <div class="nav-group-label" data-nav-group>📚 Jump to Subject Column</div>
        @foreach($subjects as $subId => $subInfo)
          <div class="nav-item"
               data-nav-label="{{ $subInfo['subject_name'] }}"
               data-nav-type="subject"
               data-nav-target="col-sub-{{ $subId }}"
               onclick="navigateTo(this)">
            <span class="nav-icon" style="background:#163562;color:#60a5fa;">📖</span>
            <div>
              <div>{{ $subInfo['subject_name'] }}</div>
              <div class="nav-item-meta">
                @if(!empty($subInfo['subject_code'])) {{ $subInfo['subject_code'] }} · @endif
                Avg: {{ $subjectStats[$subId]['avg'] ?? '—' }}
                &nbsp;|&nbsp; Hi: {{ $subjectStats[$subId]['highest'] ?? '—' }}
                &nbsp;|&nbsp; Lo: {{ $subjectStats[$subId]['lowest'] ?? '—' }}
              </div>
            </div>
          </div>
        @endforeach

        {{-- ── Section: Special Rows ── --}}
        <div class="nav-group-label" data-nav-group>📊 Class Statistics</div>
        <div class="nav-item" data-nav-label="Class Average Row" data-nav-type="stats"
             data-nav-target="stats-avg" onclick="navigateTo(this)">
          <span class="nav-icon" style="background:#0a2240;color:#fbbf24;">≈</span>
          <div>
            <div>Class Average Row</div>
            <div class="nav-item-meta">Subject-wise average scores</div>
          </div>
        </div>
        <div class="nav-item" data-nav-label="Highest Score Row" data-nav-type="stats"
             data-nav-target="stats-hi" onclick="navigateTo(this)">
          <span class="nav-icon" style="background:#0a2240;color:#10b981;">↑</span>
          <div>
            <div>Highest Score Row</div>
            <div class="nav-item-meta">Top score per subject</div>
          </div>
        </div>
        <div class="nav-item" data-nav-label="Lowest Score Row" data-nav-type="stats"
             data-nav-target="stats-lo" onclick="navigateTo(this)">
          <span class="nav-icon" style="background:#0a2240;color:#ef4444;">↓</span>
          <div>
            <div>Lowest Score Row</div>
            <div class="nav-item-meta">Lowest score per subject</div>
          </div>
        </div>

        {{-- ── Section: Column Groups ── --}}
        <div class="nav-group-label" data-nav-group>🔢 Column Groups</div>
        @if($showGPA || $showCGPA)
        <div class="nav-item" data-nav-label="GPA / CGPA Columns" data-nav-type="col-group"
             data-nav-target="col-gpa" onclick="navigateTo(this)">
          <span class="nav-icon" style="background:#1e3a8a;color:#a5b4fc;">★</span>
          <div>
            <div>GPA / CGPA Columns</div>
            <div class="nav-item-meta">Grade point averages</div>
          </div>
        </div>
        @endif
        @foreach($activeAssessments as $a)
        <div class="nav-item" data-nav-label="{{ $a->name }} Assessment Columns" data-nav-type="col-group"
             data-nav-target="col-asm-{{ $a->id }}" onclick="navigateTo(this)">
          <span class="nav-icon" style="background:#163562;color:#93c5fd;">✎</span>
          <div>
            <div>{{ $a->name }} <span style="opacity:.7;">({{ $a->max_score }})</span></div>
            <div class="nav-item-meta">Assessment scores across all subjects</div>
          </div>
        </div>
        @endforeach

        {{-- ── Section: Pass/Fail Summary ── --}}
        <div class="nav-group-label" data-nav-group>✅ Pass / Fail Summary</div>
        <div class="nav-item" data-nav-label="Subject Pass/Fail Summary Table" data-nav-type="section"
             data-nav-target="summary-section" onclick="navigateTo(this)">
          <span class="nav-icon" style="background:#065f46;color:#6ee7b7;">✓</span>
          <div>
            <div>Pass / Fail Summary Table</div>
            <div class="nav-item-meta">{{ count($subjects) }} subjects · pass rates & averages</div>
          </div>
        </div>
        @foreach($subjects as $subId => $subInfo)
          @php $s = $subjectStats[$subId] ?? []; @endphp
          <div class="nav-item"
               data-nav-label="{{ $subInfo['subject_name'] }} – Pass/Fail"
               data-nav-type="summary-row"
               data-nav-target="sum-row-{{ $subId }}"
               onclick="navigateTo(this)">
            <span class="nav-icon" style="background:#0a2240;color:#6ee7b7;">⊞</span>
            <div>
              <div>{{ $subInfo['subject_name'] }}</div>
              <div class="nav-item-meta">
                Passed: {{ $s['passed'] ?? 0 }} · Failed: {{ $s['failed'] ?? 0 }}
                · Avg: {{ $s['avg'] ?? '—' }}
              </div>
            </div>
          </div>
        @endforeach

        {{-- ── Section: Signatures ── --}}
        <div class="nav-group-label" data-nav-group>✍️ Signatures</div>
        <div class="nav-item" data-nav-label="Signature Row" data-nav-type="section"
             data-nav-target="sig-section" onclick="navigateTo(this)">
          <span class="nav-icon" style="background:#2d1b69;color:#c4b5fd;">✍</span>
          <div>
            <div>Signature Row</div>
            <div class="nav-item-meta">Class Teacher · HOD · VP · Principal</div>
          </div>
        </div>

      </div>{{-- #navList --}}
    </div>{{-- .nav-dropdown --}}
  </div>{{-- .nav-wrapper --}}

  {{-- ── Zoom controls ── --}}
  <div class="zoom-group">
    <button class="zoom-btn" onclick="adjustZoom(-0.1)" title="Zoom out">−</button>
    <span class="zoom-val" id="zoomLabel">100%</span>
    <button class="zoom-btn" onclick="adjustZoom(0.1)" title="Zoom in">+</button>
    <button class="zoom-btn" onclick="resetZoom()" title="Reset zoom" style="font-size:11px;color:#f59e0b;">⊙</button>
  </div>

  {{-- ── Export buttons ── --}}
  <div class="ctrl-export">
    <button class="ctrl-btn pdf"   onclick="triggerExport('pdf')">
      <i class="ri-file-pdf-line"></i> PDF
    </button>
    <button class="ctrl-btn excel" onclick="triggerExport('excel')">
      <i class="ri-file-excel-line"></i> Excel
    </button>
    <button class="ctrl-btn print" onclick="window.print()">
      <i class="ri-printer-line"></i> Print
    </button>
  </div>

</div>{{-- .ctrl-bar --}}

{{-- ── Meta pills ── --}}
<div class="meta-pills">
  <span class="pill pill-blue">
    📚 {{ $schoolclass->schoolclass ?? '' }} {{ $schoolclass->arm_name ?? '' }}
  </span>
  <span class="pill pill-gold">
    🗓 {{ $schoolsession->session ?? '' }}
  </span>
  <span class="pill pill-white">
    {{ $schoolterm->term ?? '' }}
  </span>
  <span class="pill pill-green">
    👥 {{ $totalStudents }} Students
  </span>
  <span class="pill pill-blue">
    📖 {{ count($subjects) }} Subjects
  </span>
  <span class="pill pill-white" style="font-size:10.5px;">
    Generated: {{ $generatedAt }}
  </span>
</div>

{{-- ══════════════════════════════════════════
     SCHOOL BANNER
     ══════════════════════════════════════════ --}}
<div class="school-banner">
  <div class="banner-logo">
    @if(!empty($school_logo_base64))
      <img src="{{ $school_logo_base64 }}" alt="Logo">
    @else
      <div style="width:72px;height:72px;border-radius:50%;background:#1a2f45;border:2px solid var(--accent);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;color:var(--accent);">SCH</div>
    @endif
  </div>
  <div class="banner-text">
    <div class="banner-name">{{ $schoolInfo->school_name ?? 'SCHOOL NAME' }}</div>
    @if(!empty($schoolInfo->school_address))
      <div class="banner-sub">{{ $schoolInfo->school_address }}</div>
    @endif
    @if(!empty($schoolInfo->school_phone) || !empty($schoolInfo->school_email))
      <div class="banner-sub">
        @if(!empty($schoolInfo->school_phone)) 📞 {{ $schoolInfo->school_phone }} @endif
        @if(!empty($schoolInfo->school_email)) &nbsp;✉ {{ $schoolInfo->school_email }} @endif
      </div>
    @endif
    @if(!empty($schoolInfo->school_motto))
      <div class="banner-motto">"{{ $schoolInfo->school_motto }}"</div>
    @endif
  </div>
  <div class="banner-badge">
    <div><strong>Class:</strong> {{ $schoolclass->schoolclass ?? '—' }} {{ $schoolclass->arm_name ?? '' }}</div>
    <div><strong>Session:</strong> {{ $schoolsession->session ?? '—' }}</div>
    <div><strong>Term:</strong> {{ $schoolterm->term ?? '—' }}</div>
    <div><strong>Students:</strong> {{ $totalStudents }}</div>
  </div>
</div>

<div class="doc-strip">CLASS ACADEMIC BROADSHEET</div>

{{-- ── Grade key ── --}}
<div class="grade-key-bar">
  <span class="gk-lbl">GRADE KEY:</span>
  @php
  $gk = [
    'A1'=>['75–100','#16a34a'],'B2'=>['70–74','#1d4ed8'],'B3'=>['65–69','#2563eb'],
    'C4'=>['60–64','#d97706'],'C5'=>['55–59','#b45309'],'C6'=>['50–54','#92400e'],
    'D7'=>['45–49','#ea580c'],'E8'=>['40–44','#c2410c'],'F9'=>['0–39','#dc2626'],
  ];
  @endphp
  @foreach($gk as $g => [$range, $col])
    <span class="gk-badge" style="background:{{ $col }}20;border:1px solid {{ $col }}66;color:{{ $col }};">
      <span class="gk-g" style="background:{{ $col }};color:#fff;">{{ $g }}</span>
      {{ $range }}
    </span>
  @endforeach
  <span style="font-size:10.5px;color:#4a6e8a;margin-left:6px;">
    <strong>BF</strong>=Brought Forward &nbsp;
    <strong>CUM</strong>=Cumulative &nbsp;
    <strong>POS</strong>=Position &nbsp;
    <strong>AVG</strong>=Class Average
  </span>
</div>

{{-- ══════════════════════════════════════════
     SCROLLABLE TABLE WRAPPER
     ══════════════════════════════════════════ --}}
<div class="table-scroll-wrapper" id="tableWrapper">
<table class="bs-table" id="bsTable">
<thead>

  {{-- ── Row 1: Subject names ── --}}
  <tr class="hdr-subj">
    {{-- Frozen: SN --}}
    <th class="frozen f0" rowspan="2" style="width:28px;z-index:90;">#</th>
    {{-- Frozen: Adm No --}}
    @if($showAdmNo)
      <th class="frozen f1" rowspan="2" style="min-width:72px;z-index:90;">Adm. No</th>
    @endif
    {{-- Frozen: Name --}}
    <th class="frozen {{ $showAdmNo ? 'f2' : 'f1' }}" rowspan="2"
        style="min-width:130px;text-align:left;padding-left:8px;z-index:90;">Student Name</th>
    {{-- Frozen: Gender --}}
    @if($showGender)
      <th class="frozen {{ $showAdmNo ? 'f3' : 'f2' }}" rowspan="2" style="width:40px;z-index:90;">Sex</th>
    @endif

    @foreach($subjects as $subId => $subInfo)
      @php
        $asmC = $activeAssessments->count();
        $cs   = $asmC;
        if($showTotal)    $cs++;
        if($showBF)       $cs++;
        if($showCum)      $cs++;
        if($showGrade)    $cs++;
        if($showPosition) $cs++;
        if($showAvg)      $cs++;
        if($showRemark)   $cs++;
      @endphp
      <th class="subj-hdr" colspan="{{ max(1,$cs) }}"
          id="col-sub-{{ $subId }}" data-sub-id="{{ $subId }}">
        <span id="beacon-sub-{{ $subId }}" class="beacon"></span>
        {{ $subInfo['subject_name'] }}
        @if(!empty($subInfo['subject_code']))
          <br><small style="opacity:.65;font-size:9px;">({{ $subInfo['subject_code'] }})</small>
        @endif
      </th>
    @endforeach

    @if($showGPA || $showCGPA || $showGPAGrade || $showNumSub || $showTotalGP)
      <th colspan="{{ ($showGPA?1:0)+($showCGPA?1:0)+($showGPAGrade?1:0)+($showNumSub?1:0)+($showTotalGP?1:0) }}"
          style="background:#0a1e38;border-left:3px solid #3b82f6;" id="col-gpa">
        <span id="beacon-gpa" class="beacon"></span>
        GPA METRICS
      </th>
    @endif
  </tr>

  {{-- ── Row 2: Assessment sub-headers ── --}}
  <tr class="hdr-asm">
    @foreach($subjects as $subId => $subInfo)
      @foreach($activeAssessments as $aIdx => $a)
        <th class="{{ $aIdx === 0 ? 'sub-boundary' : '' }}"
            id="col-asm-{{ $a->id }}-{{ $subId }}"
            data-asm-id="{{ $a->id }}">
          <span id="beacon-asm-{{ $a->id }}" class="beacon"></span>
          {{ $a->name }}<br>
          <small style="opacity:.65;font-size:9px;">/{{ $a->max_score }}</small>
        </th>
      @endforeach
      @if($showTotal)    <th>Total</th>       @endif
      @if($showBF)       <th>BF</th>          @endif
      @if($showCum)      <th>Cum</th>         @endif
      @if($showGrade)    <th>Grd</th>         @endif
      @if($showPosition) <th>Pos</th>         @endif
      @if($showAvg)      <th>Avg</th>         @endif
      @if($showRemark)   <th>Rmk</th>         @endif
    @endforeach
    @if($showGPA)       <th class="gpa-cell" style="background:#0a1e38 !important;color:#93c5fd;">GPA</th>       @endif
    @if($showCGPA)      <th class="gpa-cell" style="background:#0a1e38 !important;color:#86efac;">CGPA</th>     @endif
    @if($showGPAGrade)  <th class="gpa-cell" style="background:#0a1e38 !important;color:#fcd34d;">Grd</th>      @endif
    @if($showNumSub)    <th style="background:#0a1e38 !important;color:#a8d4ef;">No.Sub</th>                    @endif
    @if($showTotalGP)   <th style="background:#0a1e38 !important;color:#a8d4ef;">TGP</th>                      @endif
  </tr>

</thead>
<tbody>

  @foreach($studentRows as $idx => $stu)
    <tr id="row-{{ $stu['id'] }}" data-student-id="{{ $stu['id'] }}">

      {{-- SN --}}
      <td class="sn-cell frozen f0">{{ $idx + 1 }}</td>

      {{-- Adm No --}}
      @if($showAdmNo)
        <td class="adm-cell frozen f1">{{ $stu['admissionno'] }}</td>
      @endif

      {{-- Name --}}
      <td class="name-cell frozen {{ $showAdmNo ? 'f2' : 'f1' }}">
        <strong>{{ strtoupper($stu['lastname']) }}</strong>, {{ $stu['firstname'] }}
      </td>

      {{-- Gender --}}
      @if($showGender)
        <td class="frozen {{ $showAdmNo ? 'f3' : 'f2' }}" style="font-size:10px;">
          {{ substr($stu['gender'] ?? '', 0, 1) }}
        </td>
      @endif

      {{-- Subject scores --}}
      @foreach($subjects as $subId => $subInfo)
        @php $sd = $stu['subjects'][$subId] ?? []; $g = $sd['grade'] ?? '-'; $gc = $gradeClass[$g] ?? ''; @endphp

        @foreach($activeAssessments as $aIdx => $a)
          @php $as = $sd['assessments'][$a->id] ?? 0; @endphp
          <td class="{{ $aIdx === 0 ? 'sub-start' : '' }}">
            {{ $as > 0 ? number_format($as,1) : '—' }}
          </td>
        @endforeach

        @if($showTotal)
          <td class="{{ $gc }}">{{ ($sd['total'] ?? 0) > 0 ? number_format($sd['total'],1) : '—' }}</td>
        @endif
        @if($showBF)
          <td>{{ ($sd['bf'] ?? 0) > 0 ? number_format($sd['bf'],1) : '—' }}</td>
        @endif
        @if($showCum)
          <td class="{{ $gc }}" style="font-weight:700;">
            {{ ($sd['cum'] ?? 0) > 0 ? number_format($sd['cum'],1) : '—' }}
          </td>
        @endif
        @if($showGrade)
          <td class="{{ $gc }}" style="font-weight:800;">{{ $g }}</td>
        @endif
        @if($showPosition)
          <td style="font-size:10px;color:#4a6e8a;">{{ $sd['position'] ?? '—' }}</td>
        @endif
        @if($showAvg)
          <td style="font-size:10px;color:#6b8fa8;">{{ $subjectStats[$subId]['avg'] ?? '—' }}</td>
        @endif
        @if($showRemark)
          <td style="font-size:10px;">{{ $sd['remark'] ?? '—' }}</td>
        @endif
      @endforeach

      @if($showGPA)
        <td class="gpa-cell">{{ number_format($stu['gpa'],2) }}</td>
      @endif
      @if($showCGPA)
        <td class="gpa-cell" style="background:#f0fdf4 !important;color:#166534;">{{ number_format($stu['cgpa'],2) }}</td>
      @endif
      @if($showGPAGrade)
        @php $ggc = $gradeClass[$stu['gpa_grade'] ?? '-'] ?? ''; @endphp
        <td class="gpa-cell {{ $ggc }}" style="font-weight:800;">{{ $stu['gpa_grade'] ?? '—' }}</td>
      @endif
      @if($showNumSub)
        <td>{{ $stu['num_subjects'] ?? '—' }}</td>
      @endif
      @if($showTotalGP)
        <td>{{ number_format($stu['total_grade_points'],1) }}</td>
      @endif
    </tr>
  @endforeach

  {{-- ── Average row ── --}}
  <tr class="stats-row stats-avg" id="stats-avg">
    <td class="stats-lbl" colspan="{{ 1 + ($showAdmNo?1:0) + 1 + ($showGender?1:0) }}">AVG</td>
    @foreach($subjects as $subId => $subInfo)
      @php $st = $subjectStats[$subId] ?? []; @endphp
      @foreach($activeAssessments as $a) <td>—</td> @endforeach
      @if($showTotal)    <td>{{ $st['avg'] ?? '—' }}</td>  @endif
      @if($showBF)       <td>—</td>  @endif
      @if($showCum)      <td>—</td>  @endif
      @if($showGrade)    <td>—</td>  @endif
      @if($showPosition) <td>—</td>  @endif
      @if($showAvg)      <td>{{ $st['avg'] ?? '—' }}</td>  @endif
      @if($showRemark)   <td>—</td>  @endif
    @endforeach
    @if($showGPA)      <td>—</td> @endif
    @if($showCGPA)     <td>—</td> @endif
    @if($showGPAGrade) <td>—</td> @endif
    @if($showNumSub)   <td>—</td> @endif
    @if($showTotalGP)  <td>—</td> @endif
  </tr>

  {{-- ── Highest row ── --}}
  <tr class="stats-row stats-hi" id="stats-hi">
    <td class="stats-lbl" colspan="{{ 1 + ($showAdmNo?1:0) + 1 + ($showGender?1:0) }}">HIGHEST</td>
    @foreach($subjects as $subId => $subInfo)
      @php $st = $subjectStats[$subId] ?? []; @endphp
      @foreach($activeAssessments as $a) <td>—</td> @endforeach
      @if($showTotal)    <td>{{ $st['highest'] ?? '—' }}</td> @endif
      @if($showBF)       <td>—</td> @endif
      @if($showCum)      <td>—</td> @endif
      @if($showGrade)    <td>—</td> @endif
      @if($showPosition) <td>—</td> @endif
      @if($showAvg)      <td>—</td> @endif
      @if($showRemark)   <td>—</td> @endif
    @endforeach
    @if($showGPA)      <td>—</td> @endif
    @if($showCGPA)     <td>—</td> @endif
    @if($showGPAGrade) <td>—</td> @endif
    @if($showNumSub)   <td>—</td> @endif
    @if($showTotalGP)  <td>—</td> @endif
  </tr>

  {{-- ── Lowest row ── --}}
  <tr class="stats-row stats-lo" id="stats-lo">
    <td class="stats-lbl" colspan="{{ 1 + ($showAdmNo?1:0) + 1 + ($showGender?1:0) }}">LOWEST</td>
    @foreach($subjects as $subId => $subInfo)
      @php $st = $subjectStats[$subId] ?? []; @endphp
      @foreach($activeAssessments as $a) <td>—</td> @endforeach
      @if($showTotal)    <td>{{ $st['lowest'] ?? '—' }}</td> @endif
      @if($showBF)       <td>—</td> @endif
      @if($showCum)      <td>—</td> @endif
      @if($showGrade)    <td>—</td> @endif
      @if($showPosition) <td>—</td> @endif
      @if($showAvg)      <td>—</td> @endif
      @if($showRemark)   <td>—</td> @endif
    @endforeach
    @if($showGPA)      <td>—</td> @endif
    @if($showCGPA)     <td>—</td> @endif
    @if($showGPAGrade) <td>—</td> @endif
    @if($showNumSub)   <td>—</td> @endif
    @if($showTotalGP)  <td>—</td> @endif
  </tr>

</tbody>
</table>
</div>{{-- .table-scroll-wrapper --}}

{{-- ══════════════════════════════════════════
     PASS / FAIL SUMMARY
     ══════════════════════════════════════════ --}}
<div class="summary-section" id="summary-section">
  <div class="summary-title">
    📊 Subject Pass / Fail Summary
    <span style="font-size:11px;font-weight:400;color:#4a6e8a;">— {{ count($subjects) }} subjects</span>
  </div>
  <table class="sum-table">
    <thead>
      <tr>
        <th>Subject</th>
        <th style="text-align:center;">Avg</th>
        <th style="text-align:center;">Highest</th>
        <th style="text-align:center;">Lowest</th>
        <th style="text-align:center;">Passed</th>
        <th style="text-align:center;">Failed</th>
        <th style="text-align:center;">Pass Rate</th>
      </tr>
    </thead>
    <tbody>
      @foreach($subjects as $subId => $subInfo)
        @php
          $st = $subjectStats[$subId] ?? [];
          $p  = $st['passed'] ?? 0;
          $f  = $st['failed'] ?? 0;
          $t  = $p + $f;
          $pr = $t > 0 ? round($p/$t*100) : 0;
        @endphp
        <tr id="sum-row-{{ $subId }}">
          <td style="font-weight:600;">
            {{ $subInfo['subject_name'] }}
            @if(!empty($subInfo['subject_code']))
              <span style="color:#6b8fa8;font-size:10.5px;">({{ $subInfo['subject_code'] }})</span>
            @endif
          </td>
          <td style="text-align:center;font-weight:700;">{{ $st['avg'] ?? '—' }}</td>
          <td style="text-align:center;color:#16a34a;font-weight:700;">{{ $st['highest'] ?? '—' }}</td>
          <td style="text-align:center;color:#dc2626;font-weight:700;">{{ $st['lowest'] ?? '—' }}</td>
          <td style="text-align:center;color:#16a34a;">{{ $p }}</td>
          <td style="text-align:center;color:#dc2626;">{{ $f }}</td>
          <td style="text-align:center;">
            <span class="pass-badge {{ $pr >= 50 ? 'pass-good' : 'pass-bad' }}">{{ $pr }}%</span>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

{{-- ══════════════════════════════════════════
     SIGNATURES
     ══════════════════════════════════════════ --}}
<div class="sig-bar" id="sig-section">
  @foreach(['Class Teacher','Head of Department','Vice Principal','Principal'] as $role)
    <div class="sig-box">
      <div class="sig-line">{{ $role }}</div>
    </div>
  @endforeach
</div>

<div class="pg-footer">
  <span>{{ $schoolInfo->school_name ?? '' }} — Confidential Academic Record</span>
  <span>Generated: {{ $generatedAt }}</span>
</div>

{{-- ══════════════════════════════════════════
     HIDDEN EXPORT FORM
     ══════════════════════════════════════════ --}}
<form id="exportForm" method="POST" target="_blank" style="display:none;">
  @csrf
  <input type="hidden" name="schoolclassid" value="{{ $schoolclass->id ?? '' }}">
  <input type="hidden" name="sessionid"     value="{{ $schoolsession->id ?? '' }}">
  <input type="hidden" name="termid"        value="{{ $schoolterm->id ?? '' }}">
  <input type="hidden" name="paper_size"    id="ef_paper"  value="A3">
  <input type="hidden" name="orientation"   id="ef_orient" value="landscape">
  @foreach(($selectedColumns ?? []) as $i => $col)
    <input type="hidden" name="selectedColumns[{{ $i }}]" value="{{ $col }}">
  @endforeach
</form>

{{-- ══════════════════════════════════════════
     JAVASCRIPT
     ══════════════════════════════════════════ --}}
<script>
/* ── zoom ──────────────────────────────────────────────────────────── */
let zoomLevel = 1.0;
const table = document.getElementById('bsTable');

function adjustZoom(delta) {
  zoomLevel = Math.min(2.0, Math.max(0.4, parseFloat((zoomLevel + delta).toFixed(2))));
  applyZoom();
}
function resetZoom() { zoomLevel = 1.0; applyZoom(); }
function applyZoom() {
  table.style.transform = `scale(${zoomLevel})`;
  table.style.transformOrigin = 'top left';
  document.getElementById('zoomLabel').textContent = Math.round(zoomLevel * 100) + '%';
}

/* ── nav dropdown ──────────────────────────────────────────────────── */
function toggleNav() {
  const dd = document.getElementById('navDropdown');
  dd.classList.toggle('open');
  if (dd.classList.contains('open')) {
    setTimeout(() => document.getElementById('navFilter').focus(), 50);
  }
}

function filterNavItems(q) {
  q = q.toLowerCase();
  const items  = document.querySelectorAll('.nav-item');
  const groups = document.querySelectorAll('[data-nav-group]');
  const seen   = new Set();

  items.forEach(item => {
    const label = (item.dataset.navLabel || '').toLowerCase();
    const type  = (item.dataset.navType  || '').toLowerCase();
    const show  = !q || label.includes(q) || type.includes(q);
    item.style.display = show ? '' : 'none';
    if (show) seen.add(item.closest('[id]')?.previousElementSibling);
  });
}

document.addEventListener('click', function(e) {
  if (!document.getElementById('navWrapper').contains(e.target)) {
    document.getElementById('navDropdown').classList.remove('open');
  }
});

/* ── navigate to ───────────────────────────────────────────────────── */
function navigateTo(item) {
  const target = item.dataset.navTarget;
  const type   = item.dataset.navType;

  // close dropdown
  document.getElementById('navDropdown').classList.remove('open');
  document.getElementById('navTrigger').value = item.dataset.navLabel || '';

  // clear old highlights
  document.querySelectorAll('.hl-cell,.hl-row,.hl-col').forEach(el => {
    el.classList.remove('hl-cell','hl-row','hl-col');
  });

  const el = document.getElementById(target);
  if (!el) return;

  if (type === 'student') {
    // highlight the whole row
    el.querySelectorAll('td').forEach(td => td.classList.add('hl-cell'));
    // scroll into view (vertical + reset horizontal for frozen cols)
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });

  } else if (type === 'subject') {
    // scroll the header cell into view horizontally
    const wrapper = document.getElementById('tableWrapper');
    const rect    = el.getBoundingClientRect();
    const wRect   = wrapper.getBoundingClientRect();
    wrapper.scrollBy({ left: rect.left - wRect.left - 200, behavior: 'smooth' });
    // highlight all cells in this column
    const subId = el.dataset.subId;
    if (subId) {
      document.querySelectorAll(`[data-sub-id="${subId}"]`).forEach(th => th.classList.add('hl-cell'));
    }
    el.classList.add('hl-cell');

  } else if (type === 'stats') {
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    el.querySelectorAll('td').forEach(td => td.classList.add('hl-cell'));

  } else if (type === 'col-group') {
    const wrapper = document.getElementById('tableWrapper');
    const rect    = el.getBoundingClientRect();
    const wRect   = wrapper.getBoundingClientRect();
    wrapper.scrollBy({ left: rect.left - wRect.left - 200, behavior: 'smooth' });
    el.classList.add('hl-cell');

  } else if (type === 'section' || type === 'summary-row') {
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    el.querySelectorAll('td,th').forEach(c => c.classList.add('hl-cell'));
  }

  // auto-remove highlight after 3s
  setTimeout(() => {
    document.querySelectorAll('.hl-cell').forEach(el => el.classList.remove('hl-cell'));
  }, 3000);
}

/* ── export triggers ───────────────────────────────────────────────── */
const ROUTES = {
  pdf   : '{{ route("broadsheet.export.pdf") }}',
  excel : '{{ route("broadsheet.export.excel") }}',
};

function triggerExport(type) {
  const form = document.getElementById('exportForm');
  form.action = type === 'pdf' ? ROUTES.pdf : ROUTES.excel;
  form.submit();
}
</script>
@endsection
