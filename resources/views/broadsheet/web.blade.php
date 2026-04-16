{{--
    broadsheet/web.blade.php
    Full broadsheet web view — extends master layout properly
    Features: sticky toolbar, smart locate/filter, zoom, animations, grade colours
--}}
@extends('layouts.master')

@push('styles')
<style>
/* ══════════════════════════════════════════════════════════════
   BROADSHEET WEB VIEW — DESIGN SYSTEM
   Uses deep navy + electric accents on white canvas
══════════════════════════════════════════════════════════════ */
:root {
    --bsw-navy:       #0f2744;
    --bsw-navy2:      #1e3a5f;
    --bsw-blue:       #1d4ed8;
    --bsw-sky:        #3b82f6;
    --bsw-green:      #16a34a;
    --bsw-emerald:    #059669;
    --bsw-red:        #dc2626;
    --bsw-amber:      #d97706;
    --bsw-orange:     #ea580c;
    --bsw-purple:     #7c3aed;
    --bsw-teal:       #0891b2;
    --bsw-rose:       #e11d48;
    --bsw-muted:      #6b7280;
    --bsw-border:     #e2e8f0;
    --bsw-bg:         #f1f5f9;
    --bsw-white:      #ffffff;
    --bsw-text:       #0f172a;
    --bsw-subtext:    #475569;

    /* Table */
    --sn-w:    38px;
    --adm-w:   88px;
    --name-w:  185px;
    --cell-h:  32px;
    --th-h:    38px;

    /* Animation timing */
    --ease-out: cubic-bezier(.22,.68,0,1.2);
    --ease-in:  cubic-bezier(.4,0,1,1);
}

/* ── Page wrapper: pushes past sidebar ────────────────────── */
.bsw-page {
    min-height: 100vh;
    background: var(--bsw-bg);
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    color: var(--bsw-text);
    display: flex;
    flex-direction: column;
}

/* ══════════════════════════════════════════════════════════════
   TOOLBAR
══════════════════════════════════════════════════════════════ */
.bsw-toolbar {
    background: linear-gradient(135deg, var(--bsw-navy) 0%, #162d52 50%, #1a3460 100%);
    padding: 0 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    min-height: 56px;
    box-shadow: 0 4px 20px rgba(0,0,0,.4);
    position: relative;
    z-index: 100;
    border-bottom: 1px solid rgba(255,255,255,.08);
    animation: toolbarSlide .4s var(--ease-out) both;
}
@keyframes toolbarSlide {
    from { transform: translateY(-100%); opacity: 0; }
    to   { transform: translateY(0);     opacity: 1; }
}

.bsw-toolbar-brand {
    display: flex;
    flex-direction: column;
    margin-right: 6px;
}
.bsw-toolbar-brand .tb-title {
    font-size: 13.5px;
    font-weight: 800;
    color: #fff;
    letter-spacing: .2px;
    white-space: nowrap;
    line-height: 1.1;
}
.bsw-toolbar-brand .tb-sub {
    font-size: 10px;
    color: rgba(255,255,255,.5);
    letter-spacing: .3px;
    white-space: nowrap;
}

.tb-divider {
    width: 1px;
    height: 28px;
    background: rgba(255,255,255,.15);
    flex-shrink: 0;
    margin: 0 4px;
}

/* Locate section */
.tb-locate-label {
    font-size: 10.5px;
    font-weight: 600;
    color: rgba(255,255,255,.6);
    text-transform: uppercase;
    letter-spacing: .6px;
    white-space: nowrap;
    flex-shrink: 0;
}

.tb-select {
    background: rgba(255,255,255,.1);
    border: 1px solid rgba(255,255,255,.22);
    border-radius: 7px;
    color: #fff;
    font-size: 12px;
    font-weight: 500;
    padding: 6px 30px 6px 10px;
    appearance: none;
    cursor: pointer;
    transition: background .15s, border-color .15s;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='rgba(255,255,255,.7)' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 8px center;
    background-size: 12px;
    flex-shrink: 0;
}
.tb-select:focus {
    outline: none;
    border-color: var(--bsw-sky);
    background-color: rgba(59,130,246,.2);
}
.tb-select option { background: #1a2a4a; color: #fff; }
#locateSelect   { min-width: 210px; }
#subjectSelect  { min-width: 160px; display: none; }

.tb-score-wrap {
    display: none;
    align-items: center;
    gap: 6px;
}
.tb-score-input {
    width: 72px;
    background: rgba(255,255,255,.1);
    border: 1px solid rgba(255,255,255,.22);
    border-radius: 7px;
    color: #fff;
    font-size: 12px;
    padding: 6px 10px;
    text-align: center;
}
.tb-score-input::placeholder { color: rgba(255,255,255,.35); }
.tb-score-input:focus { outline: none; border-color: var(--bsw-sky); }

/* Toolbar buttons */
.tb-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(255,255,255,.1);
    border: 1px solid rgba(255,255,255,.18);
    border-radius: 7px;
    color: rgba(255,255,255,.9);
    font-size: 11.5px;
    font-weight: 600;
    padding: 6px 12px;
    cursor: pointer;
    transition: background .15s, transform .1s;
    white-space: nowrap;
    flex-shrink: 0;
    text-decoration: none;
}
.tb-btn:hover {
    background: rgba(255,255,255,.2);
    transform: translateY(-1px);
    color: #fff;
}
.tb-btn:active { transform: translateY(0); }
.tb-btn i { font-size: 14px; }

/* Result badge */
.tb-badge {
    display: none;
    background: var(--bsw-sky);
    color: white;
    font-size: 10.5px;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 20px;
    animation: badgePop .25s var(--ease-out);
    white-space: nowrap;
}
@keyframes badgePop {
    from { transform: scale(.6); opacity: 0; }
    to   { transform: scale(1);  opacity: 1; }
}

/* Next match btn */
.tb-next-btn {
    display: none;
    align-items: center;
    gap: 5px;
    background: var(--bsw-amber);
    border: none;
    border-radius: 7px;
    color: white;
    font-size: 11px;
    font-weight: 700;
    padding: 6px 12px;
    cursor: pointer;
    animation: pulse 1.8s ease-in-out infinite;
}
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.7} }

/* Zoom controls */
.tb-zoom {
    display: flex;
    align-items: center;
    gap: 3px;
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 8px;
    padding: 2px 6px;
}
.tb-zoom-btn {
    width: 24px; height: 24px;
    background: transparent;
    border: none;
    border-radius: 4px;
    color: rgba(255,255,255,.8);
    font-size: 15px;
    line-height: 1;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background .1s;
}
.tb-zoom-btn:hover { background: rgba(255,255,255,.15); color: #fff; }
.tb-zoom-label {
    font-size: 10.5px;
    font-weight: 700;
    color: rgba(255,255,255,.75);
    min-width: 36px;
    text-align: center;
    letter-spacing: .3px;
}

/* spacer */
.tb-spacer { flex: 1; }

/* ══════════════════════════════════════════════════════════════
   SCHOOL HEADER
══════════════════════════════════════════════════════════════ */
.bsw-header {
    background: var(--bsw-white);
    border-bottom: 3px solid var(--bsw-navy2);
    padding: 18px 28px;
    display: flex;
    align-items: center;
    gap: 20px;
    animation: fadeDown .5s var(--ease-out) .1s both;
    position: relative;
    overflow: hidden;
}
.bsw-header::before {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 300px; height: 100%;
    background: linear-gradient(135deg, transparent 40%, rgba(30,58,95,.04));
    pointer-events: none;
}
@keyframes fadeDown {
    from { opacity: 0; transform: translateY(-16px); }
    to   { opacity: 1; transform: translateY(0); }
}

.bsw-header-logo {
    width: 72px; height: 72px;
    object-fit: contain;
    border-radius: 8px;
    border: 2px solid var(--bsw-border);
    flex-shrink: 0;
    box-shadow: 0 2px 12px rgba(30,58,95,.12);
}

.bsw-header-meta { flex: 1; min-width: 0; }
.bsw-school-name {
    font-size: 18px;
    font-weight: 800;
    color: var(--bsw-navy);
    text-transform: uppercase;
    letter-spacing: .5px;
    line-height: 1.1;
}
.bsw-school-address {
    font-size: 11.5px;
    color: var(--bsw-subtext);
    margin-top: 3px;
}
.bsw-school-motto {
    font-size: 11px;
    font-style: italic;
    color: var(--bsw-blue);
    margin-top: 2px;
    font-weight: 600;
}

.bsw-meta-pills {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    flex-shrink: 0;
}
.bsw-meta-pill {
    background: var(--bsw-bg);
    border: 1px solid var(--bsw-border);
    border-radius: 8px;
    padding: 8px 14px;
    text-align: center;
    min-width: 86px;
    transition: transform .2s, box-shadow .2s;
}
.bsw-meta-pill:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,.08);
}
.bsw-meta-pill .mpv {
    font-size: 14px; font-weight: 800; color: var(--bsw-navy);
    display: block; line-height: 1.1;
}
.bsw-meta-pill .mpl {
    font-size: 9.5px; color: var(--bsw-muted);
    text-transform: uppercase; letter-spacing: .5px;
}

/* ══════════════════════════════════════════════════════════════
   GRADE KEY BAR
══════════════════════════════════════════════════════════════ */
.bsw-grade-key {
    background: #fefce8;
    border-top: 1px solid #fde68a;
    border-bottom: 1px solid #fde68a;
    padding: 7px 28px;
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
    font-size: 10.5px;
    animation: fadeIn .4s ease .3s both;
}
@keyframes fadeIn { from{opacity:0} to{opacity:1} }
.bsw-grade-key strong { color: var(--bsw-navy); font-weight: 700; margin-right: 4px; }
.bsw-gk-item {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 5px;
    padding: 2px 8px;
    font-size: 10.5px;
    white-space: nowrap;
    transition: transform .15s;
}
.bsw-gk-item:hover { transform: scale(1.08); }
.bsw-gk-generated {
    margin-left: auto;
    font-size: 10px;
    color: var(--bsw-muted);
}

/* ══════════════════════════════════════════════════════════════
   TABLE SECTION
══════════════════════════════════════════════════════════════ */
.bsw-table-section {
    flex: 1;
    overflow: hidden;
    position: relative;
    background: var(--bsw-white);
    animation: fadeIn .5s ease .35s both;
}

.bsw-table-scroll {
    width: 100%;
    overflow-x: auto;
    overflow-y: auto;
    /* Height: viewport minus toolbar(56) + header(≈110) + gradekey(≈42) + footer(≈140) */
    max-height: calc(100vh - 360px);
    min-height: 400px;
    position: relative;
}

/* Zoom wrapper */
.bsw-zoom-wrap {
    display: inline-block;
    transform-origin: top left;
    min-width: 100%;
}

/* ══════════════════════════════════════════════════════════════
   THE BROADSHEET TABLE
══════════════════════════════════════════════════════════════ */
#bsTable {
    border-collapse: collapse;
    font-size: 11px;
    white-space: nowrap;
    width: max-content;
    min-width: 100%;
}

/* ── THEAD ── */
#bsTable thead th {
    position: sticky;
    top: 0;
    z-index: 20;
    background: var(--bsw-navy);
    color: #fff;
    font-weight: 700;
    font-size: 10px;
    text-align: center;
    padding: 0 5px;
    height: var(--th-h);
    border: 1px solid rgba(255,255,255,.1);
    vertical-align: middle;
    letter-spacing: .2px;
}
#bsTable thead tr.subj-hdr th {
    background: #1e3a5f;
}
#bsTable thead tr.asm-hdr th {
    background: #263f6a;
    color: rgba(255,255,255,.82);
    font-size: 9px;
    font-weight: 600;
}

/* Sticky left cols in thead */
#bsTable thead th.c-sn   { position: sticky; left: 0; z-index: 31; min-width: var(--sn-w); max-width: var(--sn-w); }
#bsTable thead th.c-adm  { position: sticky; left: var(--sn-w); z-index: 31; min-width: var(--adm-w); max-width: var(--adm-w); }
#bsTable thead th.c-name {
    position: sticky;
    left: calc(var(--sn-w) + var(--adm-w));
    z-index: 31;
    min-width: var(--name-w);
    max-width: var(--name-w);
    text-align: left;
    padding-left: 8px;
}

/* ── TBODY ── */
#bsTable tbody tr td {
    height: var(--cell-h);
    padding: 0 5px;
    border: 1px solid #e5e7eb;
    text-align: center;
    vertical-align: middle;
    font-size: 10.5px;
    color: var(--bsw-text);
    transition: background .08s;
}
#bsTable tbody tr:nth-child(even) td { background: #f8fafc; }
#bsTable tbody tr:hover td { background: #eff6ff !important; cursor: pointer; }

/* Sticky body cols */
#bsTable tbody td.c-sn {
    position: sticky; left: 0; z-index: 10;
    background: #f1f5f9;
    font-weight: 700;
    color: var(--bsw-navy);
    font-size: 10px;
    border-right: 2px solid #cbd5e1;
}
#bsTable tbody td.c-adm {
    position: sticky; left: var(--sn-w); z-index: 10;
    background: #f8fafc;
    font-size: 9.5px;
    color: var(--bsw-subtext);
    border-right: 1px solid #e2e8f0;
}
#bsTable tbody td.c-name {
    position: sticky;
    left: calc(var(--sn-w) + var(--adm-w));
    z-index: 10;
    background: white;
    font-weight: 700;
    font-size: 11px;
    color: var(--bsw-navy);
    text-align: left;
    padding-left: 8px;
    border-right: 2px solid #cbd5e1;
    min-width: var(--name-w);
    max-width: var(--name-w);
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Even row sticky overrides */
#bsTable tbody tr:nth-child(even) td.c-sn   { background: #e8edf3; }
#bsTable tbody tr:nth-child(even) td.c-adm  { background: #f0f4f8; }
#bsTable tbody tr:nth-child(even) td.c-name { background: #f9fafb; }
#bsTable tbody tr:hover td.c-sn,
#bsTable tbody tr:hover td.c-adm,
#bsTable tbody tr:hover td.c-name { background: #dbeafe !important; }

/* ── GRADE COLOURS ── */
.g-A1 { color: #15803d; font-weight: 800; }
.g-B2 { color: #1d4ed8; font-weight: 700; }
.g-B3 { color: #2563eb; font-weight: 700; }
.g-C4 { color: #d97706; font-weight: 600; }
.g-C5 { color: #b45309; font-weight: 600; }
.g-C6 { color: #92400e; font-weight: 600; }
.g-D7 { color: #ea580c; font-weight: 600; }
.g-E8 { color: #dc2626; font-weight: 700; }
.g-F9 { color: #991b1b; font-weight: 800; }

/* Score cell background by grade */
.bg-A1 { background: #dcfce7 !important; }
.bg-B2 { background: #dbeafe !important; }
.bg-B3 { background: #e0eeff !important; }
.bg-C4 { background: #fef9c3 !important; }
.bg-C5 { background: #fef3c7 !important; }
.bg-C6 { background: #fde68a !important; }
.bg-D7 { background: #ffedd5 !important; }
.bg-E8 { background: #fed7aa !important; }
.bg-F9 { background: #fee2e2 !important; }

/* ── TFOOT ── */
#bsTable tfoot td {
    height: 26px;
    padding: 0 5px;
    border: 1px solid #d1d5db;
    font-size: 9.5px;
    text-align: center;
    vertical-align: middle;
    font-weight: 700;
}
#bsTable tfoot tr.ft-avg  td { background: #eff6ff; color: #1d4ed8; }
#bsTable tfoot tr.ft-high td { background: #f0fdf4; color: #166534; }
#bsTable tfoot tr.ft-low  td { background: #fff7ed; color: #9a3412; }
#bsTable tfoot tr.ft-pass td { background: #dcfce7; color: #15803d; font-weight: 600; }
#bsTable tfoot tr.ft-fail td { background: #fee2e2; color: #991b1b; font-weight: 600; }
#bsTable tfoot td.c-sn, #bsTable tfoot td.c-adm, #bsTable tfoot td.c-name {
    position: sticky; z-index: 10;
    background: #dde4ef !important;
    color: var(--bsw-navy);
    text-align: left;
    padding-left: 6px;
    font-size: 9px;
    font-weight: 800;
    border-right: 2px solid #94a3b8;
}
#bsTable tfoot td.c-sn   { left: 0; }
#bsTable tfoot td.c-adm  { left: var(--sn-w); }
#bsTable tfoot td.c-name { left: calc(var(--sn-w) + var(--adm-w)); }
.ft-gpa-cell { background: #f5f3ff !important; color: var(--bsw-purple) !important; }

/* ══════════════════════════════════════════════════════════════
   HIGHLIGHT STATES
══════════════════════════════════════════════════════════════ */
#bsTable tbody tr.hl-match   td { background: #fef9c3 !important; }
#bsTable tbody tr.hl-primary td { background: #dbeafe !important; }
#bsTable tbody tr.hl-success td { background: #dcfce7 !important; }
#bsTable tbody tr.hl-danger  td { background: #fee2e2 !important; }
#bsTable tbody tr.hl-warning td { background: #fef3c7 !important; }

#bsTable tbody tr.hl-match td.c-sn,   #bsTable tbody tr.hl-match td.c-adm,   #bsTable tbody tr.hl-match td.c-name   { background: #fde047 !important; }
#bsTable tbody tr.hl-primary td.c-sn, #bsTable tbody tr.hl-primary td.c-adm, #bsTable tbody tr.hl-primary td.c-name { background: #bfdbfe !important; }
#bsTable tbody tr.hl-success td.c-sn, #bsTable tbody tr.hl-success td.c-adm, #bsTable tbody tr.hl-success td.c-name { background: #bbf7d0 !important; }
#bsTable tbody tr.hl-danger td.c-sn,  #bsTable tbody tr.hl-danger td.c-adm,  #bsTable tbody tr.hl-danger td.c-name  { background: #fecaca !important; }
#bsTable tbody tr.hl-warning td.c-sn, #bsTable tbody tr.hl-warning td.c-adm, #bsTable tbody tr.hl-warning td.c-name { background: #fde68a !important; }

#bsTable tbody tr.hl-dim td { opacity: .28; pointer-events: none; }
#bsTable tbody tr.hl-dim td.c-sn, #bsTable tbody tr.hl-dim td.c-adm, #bsTable tbody tr.hl-dim td.c-name { opacity: .4; }

/* Cell-level highlight */
td.cell-hl         { outline: 2.5px solid #f59e0b; background: #fef9c3 !important; z-index: 2; position: relative; }
td.cell-hl-success { outline: 2.5px solid #22c55e; background: #dcfce7 !important; z-index: 2; position: relative; }
td.cell-hl-danger  { outline: 2.5px solid #ef4444; background: #fee2e2 !important; z-index: 2; position: relative; }

/* Flash animation for scroll-to */
@keyframes rowFlash {
    0%,100% { box-shadow: none; }
    25%,75%  { box-shadow: inset 0 0 0 2px #f59e0b; }
}
.row-flash { animation: rowFlash .7s ease 2; }

/* ══════════════════════════════════════════════════════════════
   SUBJECT PASS/FAIL SUMMARY
══════════════════════════════════════════════════════════════ */
.bsw-summary {
    background: var(--bsw-white);
    margin: 20px 28px;
    border-radius: 12px;
    border: 1px solid var(--bsw-border);
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,.05);
    animation: fadeUp .5s var(--ease-out) .5s both;
}
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}
.bsw-summary-head {
    background: var(--bsw-navy);
    color: white;
    padding: 12px 20px;
    font-size: 13px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
}
.bsw-summary table { width: 100%; border-collapse: collapse; font-size: 11.5px; }
.bsw-summary th {
    background: #f1f5f9;
    color: var(--bsw-navy);
    font-weight: 700;
    font-size: 10.5px;
    padding: 8px 14px;
    border-bottom: 1px solid var(--bsw-border);
    text-align: left;
}
.bsw-summary td { padding: 7px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.bsw-summary tr:last-child td { border-bottom: none; }
.bsw-summary tr:hover td { background: #f8fafc; }
.pass-bar {
    display: inline-block;
    width: 80px; height: 8px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
    vertical-align: middle;
    margin-left: 6px;
}
.pass-bar-fill { height: 100%; border-radius: 4px; transition: width .6s ease; }

/* ══════════════════════════════════════════════════════════════
   SIGNATURE BLOCK
══════════════════════════════════════════════════════════════ */
.bsw-sigs {
    display: flex;
    gap: 24px;
    flex-wrap: wrap;
    padding: 24px 28px 36px;
    background: var(--bsw-white);
    margin: 0 28px 28px;
    border-radius: 12px;
    border: 1px solid var(--bsw-border);
    animation: fadeUp .5s var(--ease-out) .6s both;
}
.bsw-sig-item { flex: 1; min-width: 130px; text-align: center; }
.bsw-sig-line { border-top: 1.5px solid #374151; margin: 32px 10px 5px; }
.bsw-sig-label { font-size: 10.5px; color: var(--bsw-subtext); text-transform: uppercase; letter-spacing: .5px; }

/* ══════════════════════════════════════════════════════════════
   TOAST
══════════════════════════════════════════════════════════════ */
#bswToast {
    position: fixed;
    bottom: 24px; right: 24px;
    background: var(--bsw-navy);
    color: white;
    padding: 12px 20px;
    border-radius: 10px;
    font-size: 12.5px;
    font-weight: 600;
    z-index: 9999;
    opacity: 0;
    transform: translateY(16px) scale(.95);
    transition: opacity .25s, transform .25s;
    pointer-events: none;
    max-width: 320px;
    box-shadow: 0 8px 32px rgba(0,0,0,.3);
}
#bswToast.show { opacity: 1; transform: translateY(0) scale(1); }

/* ══════════════════════════════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════════════════════════════ */
@media (max-width: 768px) {
    .bsw-toolbar { padding: 8px 12px; gap: 6px; }
    .bsw-header  { flex-direction: column; align-items: flex-start; padding: 14px 16px; }
    .bsw-meta-pills { width: 100%; }
    .bsw-summary, .bsw-sigs { margin: 12px; }
    #locateSelect { min-width: 150px; }
}

@media print {
    .bsw-toolbar { display: none !important; }
    .bsw-table-scroll { max-height: none !important; overflow: visible !important; }
}
</style>
@endpush

@section('content')
<div class="bsw-page">

    {{-- ══════════════════════════════════════════ TOOLBAR ══ --}}
    <div class="bsw-toolbar">
        <div class="bsw-toolbar-brand">
            <span class="tb-title">
                <i class="ri-file-chart-line" style="margin-right:5px;"></i>
                {{ ($schoolclass->schoolclass ?? 'Class') . ' ' . ($schoolclass->arm_name ?? '') }}
            </span>
            <span class="tb-sub">
                {{ $schoolsession->session ?? '' }} &middot; {{ $schoolterm->term ?? '' }} &middot; {{ $totalStudents }} Students
            </span>
        </div>

        <div class="tb-divider"></div>

        <span class="tb-locate-label"><i class="ri-search-eye-line" style="margin-right:3px;"></i>Locate:</span>

        <select id="locateSelect" class="tb-select" onchange="onLocateChange()">
            <optgroup label="── View Options ──">
                <option value="">— Find / Highlight —</option>
                <option value="all">✕ Clear All Filters</option>
            </optgroup>
            <optgroup label="── By Performance ──">
                <option value="top5">🏆 Top 5 Students (GPA)</option>
                <option value="top10">🥇 Top 10 Students (GPA)</option>
                <option value="top3_subject">🎯 Top 3 per Subject</option>
                <option value="bottom5">⚠️ Bottom 5 Students (GPA)</option>
                <option value="distinction">⭐ Distinction (All A1/B2)</option>
                <option value="above_avg">📈 Above Class Average</option>
                <option value="below_avg">📉 Below Class Average</option>
                <option value="at_risk">🔴 At-Risk (2+ Fails)</option>
                <option value="most_improved">💪 Most Subjects Passed</option>
            </optgroup>
            <optgroup label="── By Score Range ──">
                <option value="score_ge80">✅ Score ≥ 80 (Anywhere)</option>
                <option value="score_ge70">📗 Score ≥ 70 (Anywhere)</option>
                <option value="score_ge60">📘 Score ≥ 60 (Anywhere)</option>
                <option value="score_lt50">🟡 Score < 50 (Anywhere)</option>
                <option value="score_lt40">❌ Score < 40 / Fail (Anywhere)</option>
                <option value="custom_min">🔢 Custom Minimum Score…</option>
                <option value="custom_max">🔢 Custom Maximum Score…</option>
                <option value="custom_range">📊 Score Between Two Values…</option>
            </optgroup>
            <optgroup label="── By Grade ──">
                <option value="grade_A1">🟢 Grade A1 Only</option>
                <option value="grade_B2B3">🔵 Grade B2 or B3</option>
                <option value="grade_C">🟡 Grade C4/C5/C6</option>
                <option value="grade_D7">🟠 Grade D7</option>
                <option value="grade_E8F9">🔴 Grade E8 or F9 (Fail)</option>
                <option value="grade_F9">⛔ Grade F9 Only</option>
            </optgroup>
            <optgroup label="── By Subject ──">
                <option value="subj_top">🥇 Top Scorer in Subject</option>
                <option value="subj_pass">✅ All Passes in Subject</option>
                <option value="subj_fail">❌ All Fails in Subject</option>
                <option value="subj_above_avg">📈 Above Avg in Subject</option>
                <option value="subj_below_avg">📉 Below Avg in Subject</option>
                <option value="subj_ge80">≥ 80 in Subject</option>
                <option value="subj_lt40">< 40 in Subject</option>
            </optgroup>
            <optgroup label="── By Completion ──">
                <option value="missing">⚪ Missing / Zero Scores</option>
                <option value="complete">✔️ All Scores Entered</option>
                <option value="partial">⚠️ Partially Filled</option>
            </optgroup>
            <optgroup label="── By GPA ──">
                <option value="gpa_ge4">GPA ≥ 4.0 (Excellent)</option>
                <option value="gpa_ge3">GPA ≥ 3.0 (Good)</option>
                <option value="gpa_lt2">GPA < 2.0 (Struggling)</option>
                <option value="gpa_lt1">GPA < 1.0 (Critical)</option>
            </optgroup>
        </select>

        <select id="subjectSelect" class="tb-select" onchange="runLocate()">
            <option value="">— Pick subject —</option>
            @foreach($subjects as $subId => $subInfo)
                <option value="{{ $subId }}">{{ $subInfo['subject_name'] }}</option>
            @endforeach
        </select>

        <div class="tb-score-wrap" id="scoreWrap">
            <span class="tb-locate-label" id="scoreLabel">Min:</span>
            <input type="number" id="scoreMin" class="tb-score-input" min="0" max="100" placeholder="0" oninput="runLocate()">
            <span class="tb-locate-label" id="scoreSep" style="display:none;">–</span>
            <input type="number" id="scoreMax" class="tb-score-input" min="0" max="100" placeholder="100" oninput="runLocate()" id="scoreMax" style="display:none;">
        </div>

        <span id="resultBadge" class="tb-badge">0 found</span>

        <button id="nextMatchBtn" class="tb-next-btn" onclick="scrollToNext()">
            <i class="ri-arrow-down-line"></i> Next
        </button>

        <div class="tb-divider"></div>

        <div class="tb-zoom">
            <button class="tb-zoom-btn" onclick="changeZoom(-.1)" title="Zoom out">−</button>
            <span class="tb-zoom-label" id="zoomLabel">100%</span>
            <button class="tb-zoom-btn" onclick="changeZoom(+.1)" title="Zoom in">+</button>
            <button class="tb-zoom-btn" onclick="fitZoom()" title="Fit to width" style="font-size:10px;width:auto;padding:0 5px;">Fit</button>
        </div>

        <div class="tb-divider"></div>

        <button class="tb-btn" onclick="window.print()">
            <i class="ri-printer-line"></i> Print
        </button>
        <a href="{{ route('broadsheet.index') }}" class="tb-btn">
            <i class="ri-arrow-left-line"></i> Back
        </a>
    </div>

    {{-- ══════════════════════════════════════════ SCHOOL HEADER ══ --}}
    <div class="bsw-header">
        <img src="{{ $school_logo_base64 ?? '' }}" alt="School Logo" class="bsw-header-logo"
             onerror="this.style.display='none'">
        <div class="bsw-header-meta">
            <div class="bsw-school-name">{{ $schoolInfo->school_name ?? 'School Name' }}</div>
            @if(!empty($schoolInfo->school_address))
                <div class="bsw-school-address">{{ $schoolInfo->school_address }}</div>
            @endif
            @if(!empty($schoolInfo->school_motto))
                <div class="bsw-school-motto">"{{ $schoolInfo->school_motto }}"</div>
            @endif
        </div>
        <div class="bsw-meta-pills">
            <div class="bsw-meta-pill">
                <span class="mpv">{{ ($schoolclass->schoolclass ?? '—') . ' ' . ($schoolclass->arm_name ?? '') }}</span>
                <span class="mpl">Class</span>
            </div>
            <div class="bsw-meta-pill">
                <span class="mpv">{{ $schoolsession->session ?? '—' }}</span>
                <span class="mpl">Session</span>
            </div>
            <div class="bsw-meta-pill">
                <span class="mpv">{{ $schoolterm->term ?? '—' }}</span>
                <span class="mpl">Term</span>
            </div>
            <div class="bsw-meta-pill">
                <span class="mpv">{{ $totalStudents }}</span>
                <span class="mpl">Students</span>
            </div>
            <div class="bsw-meta-pill">
                <span class="mpv">{{ count($subjects) }}</span>
                <span class="mpl">Subjects</span>
            </div>
            <div class="bsw-meta-pill">
                <span class="mpv">{{ $assessments->count() }}</span>
                <span class="mpl">Assessments</span>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════ GRADE KEY ══ --}}
    <div class="bsw-grade-key">
        <strong>Grade Key:</strong>
        <span class="bsw-gk-item" style="color:#15803d;font-weight:700;">A1: 75–100</span>
        <span class="bsw-gk-item" style="color:#1d4ed8;">B2: 70–74</span>
        <span class="bsw-gk-item" style="color:#2563eb;">B3: 65–69</span>
        <span class="bsw-gk-item" style="color:#d97706;">C4: 60–64</span>
        <span class="bsw-gk-item" style="color:#b45309;">C5: 55–59</span>
        <span class="bsw-gk-item" style="color:#92400e;">C6: 50–54</span>
        <span class="bsw-gk-item" style="color:#ea580c;">D7: 45–49</span>
        <span class="bsw-gk-item" style="color:#dc2626;">E8: 40–44</span>
        <span class="bsw-gk-item" style="color:#991b1b;font-weight:700;">F9: 0–39</span>
        <span class="bsw-gk-generated">Generated: {{ $generatedAt }}</span>
    </div>

    {{-- ══════════════════════════════════════════ TABLE ══ --}}
    <div class="bsw-table-section">
        <div class="bsw-table-scroll" id="tableScroll">
            <div class="bsw-zoom-wrap" id="zoomWrap">
            <table id="bsTable">

@php
    $sel = $selectedColumns ?? [];
    $showSN    = empty($sel) || in_array('sn', $sel);
    $showAdm   = empty($sel) || in_array('admission_no', $sel);
    $showName  = empty($sel) || in_array('name', $sel);
    $showGend  = in_array('gender', $sel);
    $showTotal = empty($sel) || in_array('total', $sel);
    $showBF    = in_array('bf', $sel) || empty($sel);
    $showCum   = empty($sel) || in_array('cum', $sel);
    $showGrade = empty($sel) || in_array('grade', $sel);
    $showPos   = empty($sel) || in_array('position', $sel);
    $showAvg   = empty($sel) || in_array('class_average', $sel);
    $showRmk   = in_array('remark', $sel);
    $showGPA   = empty($sel) || in_array('gpa', $sel);
    $showCGPA  = in_array('cgpa', $sel);
    $showGPAGr = in_array('gpa_grade', $sel);
    $showNS    = in_array('num_subjects', $sel);
    $showTGP   = in_array('total_grade_points', $sel);

    $activeAsm = $assessments->filter(fn($a) => empty($sel) || in_array('assessment_'.$a->id, $sel));

    $perSubjCols = $activeAsm->count()
        + ($showTotal?1:0) + ($showBF?1:0) + ($showCum?1:0)
        + ($showGrade?1:0) + ($showPos?1:0) + ($showAvg?1:0) + ($showRmk?1:0);

    $gpaColCount = ($showGPA?1:0)+($showCGPA?1:0)+($showGPAGr?1:0)+($showNS?1:0)+($showTGP?1:0);

    $subjectColors = [
        '#1d4ed8','#059669','#7c3aed','#dc2626','#0891b2',
        '#d97706','#be185d','#16a34a','#c2410c','#4338ca',
        '#0f766e','#9333ea','#b45309','#1e40af','#065f46',
    ];
@endphp

{{-- ═══ THEAD ═══ --}}
<thead>
{{-- Row 1: Subject name headers --}}
<tr class="subj-hdr">
    @if($showSN)   <th class="c-sn"   rowspan="2">#</th>          @endif
    @if($showAdm)  <th class="c-adm"  rowspan="2">Adm. No</th>    @endif
    @if($showName) <th class="c-name" rowspan="2" style="text-align:left;padding-left:8px;">Student Name</th> @endif
    @if($showGend) <th rowspan="2" style="min-width:36px;">Sex</th> @endif

    @foreach($subjects as $subId => $subInfo)
        @php $color = $subjectColors[array_search($subId, array_keys($subjects)) % count($subjectColors)]; @endphp
        <th colspan="{{ max(1,$perSubjCols) }}"
            style="background:{{ $color }};border-left:2px solid rgba(255,255,255,.3);min-width:{{ max(1,$perSubjCols)*22 }}px;"
            title="{{ $subInfo['subject_name'] }}">
            {{ $subInfo['subject_name'] }}
            @if(!empty($subInfo['subject_code']))
                <span style="opacity:.7;font-size:8.5px;"> ({{ $subInfo['subject_code'] }})</span>
            @endif
        </th>
    @endforeach

    @if($gpaColCount > 0)
        <th colspan="{{ $gpaColCount }}" style="background:#5b21b6;border-left:2px solid rgba(255,255,255,.3);">
            GPA METRICS
        </th>
    @endif
</tr>

{{-- Row 2: Assessment sub-headers --}}
<tr class="asm-hdr">
    @foreach($subjects as $subId => $subInfo)
        @foreach($activeAsm as $aIdx => $a)
            <th style="min-width:30px;max-width:40px;font-size:8.5px;
                       {{ $aIdx===0 ? 'border-left:2px solid rgba(255,255,255,.25);' : '' }}">
                {{ $a->name }}<br>
                <span style="opacity:.65;">({{ $a->max_score }})</span>
            </th>
        @endforeach
        @if($showTotal) <th style="min-width:34px;">Total</th>  @endif
        @if($showBF)    <th style="min-width:30px;">BF</th>     @endif
        @if($showCum)   <th style="min-width:34px;">Cum</th>    @endif
        @if($showGrade) <th style="min-width:28px;">Grd</th>    @endif
        @if($showPos)   <th style="min-width:28px;">Pos</th>    @endif
        @if($showAvg)   <th style="min-width:34px;">Avg</th>    @endif
        @if($showRmk)   <th style="min-width:46px;">Remark</th> @endif
    @endforeach
    @if($showGPA)   <th style="min-width:30px;background:#4c1d95;border-left:2px solid rgba(255,255,255,.2);">GPA</th>   @endif
    @if($showCGPA)  <th style="min-width:30px;background:#4c1d95;">CGPA</th>  @endif
    @if($showGPAGr) <th style="min-width:28px;background:#4c1d95;">GGrd</th>  @endif
    @if($showNS)    <th style="min-width:28px;background:#4c1d95;">NS</th>    @endif
    @if($showTGP)   <th style="min-width:30px;background:#4c1d95;">TGP</th>   @endif
</tr>
</thead>

{{-- ═══ TBODY ═══ --}}
<tbody>
@php
$gradeMap = [
    'A1'=>'A1','B2'=>'B2','B3'=>'B3',
    'C4'=>'C4','C5'=>'C5','C6'=>'C6',
    'D7'=>'D7','E8'=>'E8','F9'=>'F9',
];
@endphp
@forelse($studentRows as $idx => $stu)
@php
    $name = trim($stu['lastname'].' '.$stu['firstname']);
    $subScores = $stu['subjects'] ?? [];
    $cumVals = collect($subScores)->pluck('cum')->filter(fn($v)=>$v>0);
    $f9Count = collect($subScores)->filter(fn($s)=>($s['grade']??'')==='F9')->count();
    $stuAvg  = $cumVals->count()>0 ? round($cumVals->avg(),1) : 0;
@endphp
<tr data-sid="{{ $stu['id'] }}"
    data-gpa="{{ $stu['gpa'] }}"
    data-avg="{{ $stuAvg }}"
    data-f9="{{ $f9Count }}"
    data-ns="{{ $stu['num_subjects'] }}"
    data-idx="{{ $idx }}">

    @if($showSN)   <td class="c-sn">{{ $idx+1 }}</td>                    @endif
    @if($showAdm)  <td class="c-adm">{{ $stu['admissionno'] }}</td>       @endif
    @if($showName) <td class="c-name" title="{{ $name }}">{{ $name }}</td> @endif
    @if($showGend) <td>{{ strtoupper(substr($stu['gender']??'-',0,1)) }}</td> @endif

    @foreach($subjects as $subId => $subInfo)
        @php $ss = $subScores[$subId] ?? null; $g = $ss['grade']??'-'; $gc = $gradeMap[$g]??''; @endphp
        @foreach($activeAsm as $aIdx => $a)
            @php $asScore = $ss['assessments'][$a->id] ?? 0; @endphp
            <td data-sub="{{ $subId }}" data-col="asm{{ $a->id }}" data-score="{{ $asScore }}"
                style="{{ $aIdx===0?'border-left:2px solid #c7d5e8;':'' }}">
                {{ $asScore>0 ? $asScore : '–' }}
            </td>
        @endforeach

        @if($showTotal)
            <td data-sub="{{ $subId }}" data-col="total" data-score="{{ $ss['total']??0 }}"
                class="{{ $gc?'bg-'.$gc:'' }}">
                {{ $ss&&$ss['total']>0 ? $ss['total'] : '–' }}
            </td>
        @endif
        @if($showBF)
            <td data-sub="{{ $subId }}" data-col="bf" data-score="{{ $ss['bf']??0 }}">
                {{ $ss&&$ss['bf']>0 ? $ss['bf'] : '–' }}
            </td>
        @endif
        @if($showCum)
            <td data-sub="{{ $subId }}" data-col="cum" data-score="{{ $ss['cum']??0 }}"
                class="{{ $gc?'bg-'.$gc:'' }}" style="font-weight:700;">
                {{ $ss&&$ss['cum']>0 ? $ss['cum'] : '–' }}
            </td>
        @endif
        @if($showGrade)
            <td data-sub="{{ $subId }}" data-col="grade" data-grade="{{ $g }}"
                class="{{ $gc?'g-'.$gc:'' }}" style="font-weight:800;">
                {{ $ss ? $g : '–' }}
            </td>
        @endif
        @if($showPos)
            <td data-sub="{{ $subId }}" data-col="pos" style="font-size:10px;">
                {{ $ss ? ($ss['position']??'–') : '–' }}
            </td>
        @endif
        @if($showAvg)
            <td data-sub="{{ $subId }}" data-col="avg" style="color:#64748b;font-size:10px;">
                {{ $ss ? number_format($ss['class_average'],1) : '–' }}
            </td>
        @endif
        @if($showRmk)
            <td data-sub="{{ $subId }}" data-col="remark" style="font-size:10px;white-space:nowrap;">
                {{ $ss ? ($ss['remark']??'–') : '–' }}
            </td>
        @endif
    @endforeach

    @if($showGPA)   <td class="ft-gpa-cell" style="font-weight:800;">{{ $stu['gpa'] }}</td>          @endif
    @if($showCGPA)  <td class="ft-gpa-cell" style="color:#059669;">{{ $stu['cgpa'] }}</td>           @endif
    @if($showGPAGr) <td class="g-{{ $gradeMap[$stu['gpa_grade']]??'' }}" style="font-weight:800;">{{ $stu['gpa_grade'] }}</td> @endif
    @if($showNS)    <td>{{ $stu['num_subjects'] }}</td>                                                @endif
    @if($showTGP)   <td>{{ $stu['total_grade_points'] }}</td>                                         @endif
</tr>
@empty
<tr><td colspan="999" style="text-align:center;padding:32px;color:#9ca3af;font-size:13px;">
    <i class="ri-inbox-line" style="font-size:32px;display:block;margin-bottom:8px;"></i>
    No student data found.
</td></tr>
@endforelse
</tbody>

{{-- ═══ TFOOT (statistics) ═══ --}}
<tfoot>
@php
$statsRows = [
    ['ft-avg',  'CLASS AVG',  'avg'],
    ['ft-high', 'HIGHEST',    'highest'],
    ['ft-low',  'LOWEST',     'lowest'],
    ['ft-pass', 'PASS COUNT', 'passed'],
    ['ft-fail', 'FAIL COUNT', 'failed'],
];
@endphp
@foreach($statsRows as [$cls,$lbl,$key])
<tr class="{{ $cls }}">
    @if($showSN)   <td class="c-sn">—</td>                                 @endif
    @if($showAdm)  <td class="c-adm">—</td>                                @endif
    @if($showName) <td class="c-name" style="text-align:left;">{{ $lbl }}</td> @endif
    @if($showGend) <td>—</td>                                               @endif

    @foreach($subjects as $subId => $subInfo)
        @php $st = $subjectStats[$subId] ?? []; @endphp
        @foreach($activeAsm as $a) <td>—</td> @endforeach
        @if($showTotal)
            <td>{{ $st[$key]??'—' }}</td>
        @endif
        @if($showBF)   <td>—</td> @endif
        @if($showCum)  <td>—</td> @endif
        @if($showGrade)<td>—</td> @endif
        @if($showPos)  <td>—</td> @endif
        @if($showAvg)
            <td>{{ $key==='avg'?($st['avg']??'—'):'—' }}</td>
        @endif
        @if($showRmk)  <td>—</td> @endif
    @endforeach

    @if($showGPA)
        @php
            $gpaVal = '—';
            if($key==='avg')     $gpaVal = count($studentRows)>0?round(collect($studentRows)->avg('gpa'),2):'—';
            if($key==='highest') $gpaVal = count($studentRows)>0?collect($studentRows)->max('gpa'):'—';
            if($key==='lowest')  $gpaVal = count($studentRows)>0?collect($studentRows)->min('gpa'):'—';
        @endphp
        <td class="ft-gpa-cell">{{ $gpaVal }}</td>
    @endif
    @if($showCGPA)  <td>—</td> @endif
    @if($showGPAGr) <td>—</td> @endif
    @if($showNS)    <td>—</td> @endif
    @if($showTGP)   <td>—</td> @endif
</tr>
@endforeach
</tfoot>

            </table>
            </div>{{-- /zoom-wrap --}}
        </div>{{-- /table-scroll --}}
    </div>{{-- /table-section --}}

    {{-- ══════════════════════════════════════════ SUMMARY TABLE ══ --}}
    <div class="bsw-summary">
        <div class="bsw-summary-head">
            <i class="ri-bar-chart-2-line"></i>
            Subject Pass / Fail Summary
        </div>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Subject</th>
                        <th style="text-align:center;">Students</th>
                        <th style="text-align:center;">Avg Score</th>
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
                        $st = $subjectStats[$subId] ?? ['avg'=>0,'highest'=>0,'lowest'=>0,'passed'=>0,'failed'=>0];
                        $tot = $st['passed'] + $st['failed'];
                        $pr  = $tot>0 ? round($st['passed']/$tot*100,1) : 0;
                    @endphp
                    <tr>
                        <td style="color:#9ca3af;font-size:10px;">{{ $loop->iteration }}</td>
                        <td style="font-weight:700;">{{ $subInfo['subject_name'] }}
                            @if(!empty($subInfo['subject_code']))
                                <span style="color:#9ca3af;font-size:10px;">({{ $subInfo['subject_code'] }})</span>
                            @endif
                        </td>
                        <td style="text-align:center;">{{ $tot }}</td>
                        <td style="text-align:center;font-weight:700;">{{ $st['avg'] }}</td>
                        <td style="text-align:center;color:#16a34a;font-weight:700;">{{ $st['highest'] }}</td>
                        <td style="text-align:center;color:#d97706;font-weight:700;">{{ $st['lowest'] }}</td>
                        <td style="text-align:center;color:#16a34a;font-weight:700;">{{ $st['passed'] }}</td>
                        <td style="text-align:center;color:#dc2626;font-weight:700;">{{ $st['failed'] }}</td>
                        <td style="text-align:center;">
                            <span style="font-weight:800;color:{{ $pr>=50?'#16a34a':'#dc2626' }};">{{ $pr }}%</span>
                            <span class="pass-bar">
                                <span class="pass-bar-fill"
                                      style="width:{{ $pr }}%;background:{{ $pr>=50?'linear-gradient(90deg,#22c55e,#16a34a)':'linear-gradient(90deg,#f87171,#dc2626)' }};"
                                      data-w="{{ $pr }}"></span>
                            </span>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ══════════════════════════════════════════ SIGNATURES ══ --}}
    <div class="bsw-sigs">
        @foreach(['Class Teacher','Head of Department','Vice Principal (Academics)','Principal'] as $role)
        <div class="bsw-sig-item">
            <div class="bsw-sig-line"></div>
            <div class="bsw-sig-label">{{ $role }}</div>
        </div>
        @endforeach
    </div>

</div>{{-- /bsw-page --}}

{{-- Toast --}}
<div id="bswToast"></div>
@endsection

@push('scripts')
<script>
/* ══════════════════════════════════════════════════════════
   PHP → JS DATA
══════════════════════════════════════════════════════════ */
const SUBJECT_STATS = @json($subjectStats);
const SUBJECT_LIST  = @json(collect($subjects)->map(fn($s)=>['id'=>$s['subject_id'],'name'=>$s['subject_name']])->values());

/* ══════════════════════════════════════════════════════════
   STATE
══════════════════════════════════════════════════════════ */
let zoom        = 1.0;
let locateMode  = '';
let matchedRows = [];
let matchCursor = 0;
let toastTimer  = null;

/* ══════════════════════════════════════════════════════════
   ZOOM
══════════════════════════════════════════════════════════ */
function changeZoom(delta) {
    zoom = Math.min(2.5, Math.max(0.25, zoom + delta));
    applyZoom();
}
function fitZoom() {
    const outer = document.getElementById('tableScroll');
    const table = document.getElementById('bsTable');
    if (!table) return;
    zoom = Math.min(1.0, Math.max(0.25, outer.clientWidth / table.scrollWidth));
    applyZoom();
}
function applyZoom() {
    const wrap = document.getElementById('zoomWrap');
    wrap.style.transform = `scale(${zoom})`;
    wrap.style.transformOrigin = 'top left';
    // Compensate outer scroll height
    const table = document.getElementById('bsTable');
    const outer = document.getElementById('tableScroll');
    outer.style.height = Math.max(400, table.scrollHeight * zoom + 40) + 'px';
    document.getElementById('zoomLabel').textContent = Math.round(zoom * 100) + '%';
}

/* ══════════════════════════════════════════════════════════
   LOCATE ENGINE
══════════════════════════════════════════════════════════ */
const SUBJECT_NEEDS = [
    'subj_top','subj_pass','subj_fail',
    'subj_above_avg','subj_below_avg','subj_ge80','subj_lt40','top3_subject'
];
const SCORE_NEEDS = ['custom_min','custom_max','custom_range'];

function onLocateChange() {
    locateMode = document.getElementById('locateSelect').value;

    const subjSel  = document.getElementById('subjectSelect');
    const scoreWrp = document.getElementById('scoreWrap');
    const scoreMin = document.getElementById('scoreMin');
    const scoreMax = document.getElementById('scoreMax');
    const scoreSep = document.getElementById('scoreSep');
    const scoreLbl = document.getElementById('scoreLabel');

    subjSel.style.display  = SUBJECT_NEEDS.includes(locateMode) ? 'inline-block' : 'none';
    scoreWrp.style.display = SCORE_NEEDS.includes(locateMode)   ? 'flex'         : 'none';

    if (locateMode === 'custom_range') {
        scoreSep.style.display = 'inline';
        scoreMax.style.display = 'inline-block';
        scoreLbl.textContent   = 'Min:';
    } else {
        scoreSep.style.display = 'none';
        scoreMax.style.display = 'none';
        scoreLbl.textContent   = locateMode === 'custom_max' ? 'Max:' : 'Min:';
    }

    if (!SUBJECT_NEEDS.includes(locateMode) && !SCORE_NEEDS.includes(locateMode)) {
        runLocate();
    }
}

function runLocate() {
    const mode      = locateMode;
    const subjId    = document.getElementById('subjectSelect').value;
    const minScore  = parseFloat(document.getElementById('scoreMin').value) || 0;
    const maxScore  = parseFloat(document.getElementById('scoreMax').value) || 100;

    const allRows = Array.from(document.querySelectorAll('#bsTable tbody tr[data-sid]'));

    // Clear
    allRows.forEach(r => {
        r.classList.remove('hl-match','hl-primary','hl-success','hl-danger','hl-warning','hl-dim');
        r.querySelectorAll('td').forEach(td =>
            td.classList.remove('cell-hl','cell-hl-success','cell-hl-danger'));
    });
    matchedRows = [];
    matchCursor = 0;
    document.getElementById('resultBadge').style.display = 'none';
    document.getElementById('nextMatchBtn').style.display = 'none';

    if (!mode || mode === 'all') {
        showToast('Filter cleared — showing all ' + allRows.length + ' students');
        return;
    }

    // Compute class GPA average
    const allGpas = allRows.map(r => parseFloat(r.dataset.gpa) || 0);
    const classGpaAvg = allGpas.length ? allGpas.reduce((a,b)=>a+b,0)/allGpas.length : 0;

    const picked = [];

    allRows.forEach(row => {
        const gpa   = parseFloat(row.dataset.gpa)  || 0;
        const f9    = parseInt(row.dataset.f9)      || 0;
        const ns    = parseInt(row.dataset.ns)      || 0;
        let match   = false;
        let hlClass = 'hl-match';

        const allScoreCells = Array.from(row.querySelectorAll('td[data-sub][data-score]'));
        const allGradeCells = Array.from(row.querySelectorAll('td[data-sub][data-grade]'));

        const subjScoreCells = subjId
            ? Array.from(row.querySelectorAll(`td[data-sub="${subjId}"][data-score]`)) : [];
        const subjGradeCells = subjId
            ? Array.from(row.querySelectorAll(`td[data-sub="${subjId}"][data-grade]`)) : [];

        const subjStat = subjId && SUBJECT_STATS[subjId] ? SUBJECT_STATS[subjId] : {};

        switch (mode) {
            // ── Performance ──
            case 'top5': case 'top10': case 'bottom5': case 'top3_subject': case 'most_improved':
                break; // handled post-loop

            case 'distinction':
                match = allGradeCells.length > 0
                    && allGradeCells.every(td => ['A1','B2'].includes(td.dataset.grade));
                hlClass = 'hl-success';
                break;
            case 'above_avg':
                match = gpa > classGpaAvg;
                hlClass = 'hl-success';
                break;
            case 'below_avg':
                match = gpa < classGpaAvg;
                hlClass = 'hl-warning';
                break;
            case 'at_risk':
                match = f9 >= 2;
                hlClass = 'hl-danger';
                break;

            // ── Score Range ──
            case 'score_ge80':
                match = allScoreCells.some(td => parseFloat(td.dataset.score) >= 80);
                hlClass = 'hl-success';
                if (match) allScoreCells.forEach(td => {
                    if (parseFloat(td.dataset.score) >= 80) td.classList.add('cell-hl-success');
                });
                break;
            case 'score_ge70':
                match = allScoreCells.some(td => parseFloat(td.dataset.score) >= 70);
                hlClass = 'hl-success';
                if (match) allScoreCells.forEach(td => {
                    if (parseFloat(td.dataset.score) >= 70) td.classList.add('cell-hl-success');
                });
                break;
            case 'score_ge60':
                match = allScoreCells.some(td => parseFloat(td.dataset.score) >= 60);
                hlClass = 'hl-primary';
                if (match) allScoreCells.forEach(td => {
                    if (parseFloat(td.dataset.score) >= 60) td.classList.add('cell-hl');
                });
                break;
            case 'score_lt50':
                match = allScoreCells.some(td => {
                    const s = parseFloat(td.dataset.score);
                    return s > 0 && s < 50;
                });
                hlClass = 'hl-warning';
                if (match) allScoreCells.forEach(td => {
                    const s = parseFloat(td.dataset.score);
                    if (s > 0 && s < 50) td.classList.add('cell-hl');
                });
                break;
            case 'score_lt40':
                match = allScoreCells.some(td => {
                    const s = parseFloat(td.dataset.score);
                    return s > 0 && s < 40;
                });
                hlClass = 'hl-danger';
                if (match) allScoreCells.forEach(td => {
                    const s = parseFloat(td.dataset.score);
                    if (s > 0 && s < 40) td.classList.add('cell-hl-danger');
                });
                break;
            case 'custom_min':
                match = minScore > 0 && allScoreCells.some(td => parseFloat(td.dataset.score) >= minScore);
                hlClass = 'hl-primary';
                if (match) allScoreCells.forEach(td => {
                    if (parseFloat(td.dataset.score) >= minScore) td.classList.add('cell-hl');
                });
                break;
            case 'custom_max':
                match = allScoreCells.some(td => {
                    const s = parseFloat(td.dataset.score);
                    return s > 0 && s <= minScore;
                });
                hlClass = 'hl-warning';
                if (match) allScoreCells.forEach(td => {
                    const s = parseFloat(td.dataset.score);
                    if (s > 0 && s <= minScore) td.classList.add('cell-hl');
                });
                break;
            case 'custom_range':
                match = allScoreCells.some(td => {
                    const s = parseFloat(td.dataset.score);
                    return s >= minScore && s <= maxScore;
                });
                hlClass = 'hl-match';
                if (match) allScoreCells.forEach(td => {
                    const s = parseFloat(td.dataset.score);
                    if (s >= minScore && s <= maxScore) td.classList.add('cell-hl');
                });
                break;

            // ── By Grade ──
            case 'grade_A1':
                match = allGradeCells.some(td => td.dataset.grade === 'A1');
                hlClass = 'hl-success';
                if (match) allGradeCells.forEach(td => {
                    if (td.dataset.grade === 'A1') td.classList.add('cell-hl-success');
                });
                break;
            case 'grade_B2B3':
                match = allGradeCells.some(td => ['B2','B3'].includes(td.dataset.grade));
                hlClass = 'hl-primary';
                if (match) allGradeCells.forEach(td => {
                    if (['B2','B3'].includes(td.dataset.grade)) td.classList.add('cell-hl');
                });
                break;
            case 'grade_C':
                match = allGradeCells.some(td => ['C4','C5','C6'].includes(td.dataset.grade));
                hlClass = 'hl-match';
                if (match) allGradeCells.forEach(td => {
                    if (['C4','C5','C6'].includes(td.dataset.grade)) td.classList.add('cell-hl');
                });
                break;
            case 'grade_D7':
                match = allGradeCells.some(td => td.dataset.grade === 'D7');
                hlClass = 'hl-warning';
                if (match) allGradeCells.forEach(td => {
                    if (td.dataset.grade === 'D7') td.classList.add('cell-hl');
                });
                break;
            case 'grade_E8F9':
                match = allGradeCells.some(td => ['E8','F9'].includes(td.dataset.grade));
                hlClass = 'hl-danger';
                if (match) allGradeCells.forEach(td => {
                    if (['E8','F9'].includes(td.dataset.grade)) td.classList.add('cell-hl-danger');
                });
                break;
            case 'grade_F9':
                match = allGradeCells.some(td => td.dataset.grade === 'F9');
                hlClass = 'hl-danger';
                if (match) allGradeCells.forEach(td => {
                    if (td.dataset.grade === 'F9') td.classList.add('cell-hl-danger');
                });
                break;

            // ── By Subject ──
            case 'subj_fail':
                if (!subjId) return;
                match = subjGradeCells.some(td => ['E8','F9'].includes(td.dataset.grade));
                hlClass = 'hl-danger';
                if (match) subjGradeCells.forEach(td => {
                    if (['E8','F9'].includes(td.dataset.grade)) td.classList.add('cell-hl-danger');
                });
                break;
            case 'subj_pass':
                if (!subjId) return;
                match = subjGradeCells.some(td =>
                    td.dataset.grade && !['E8','F9','–','-'].includes(td.dataset.grade));
                hlClass = 'hl-success';
                if (match) subjGradeCells.forEach(td => {
                    if (td.dataset.grade && !['E8','F9','–','-'].includes(td.dataset.grade))
                        td.classList.add('cell-hl-success');
                });
                break;
            case 'subj_above_avg':
                if (!subjId) return;
                match = subjScoreCells.some(td => parseFloat(td.dataset.score) > (subjStat.avg || 0));
                hlClass = 'hl-success';
                if (match) subjScoreCells.forEach(td => {
                    if (parseFloat(td.dataset.score) > (subjStat.avg || 0)) td.classList.add('cell-hl-success');
                });
                break;
            case 'subj_below_avg':
                if (!subjId) return;
                match = subjScoreCells.some(td => {
                    const s = parseFloat(td.dataset.score);
                    return s > 0 && s < (subjStat.avg || 0);
                });
                hlClass = 'hl-warning';
                if (match) subjScoreCells.forEach(td => {
                    const s = parseFloat(td.dataset.score);
                    if (s > 0 && s < (subjStat.avg || 0)) td.classList.add('cell-hl');
                });
                break;
            case 'subj_ge80':
                if (!subjId) return;
                match = subjScoreCells.some(td => parseFloat(td.dataset.score) >= 80);
                hlClass = 'hl-success';
                if (match) subjScoreCells.forEach(td => {
                    if (parseFloat(td.dataset.score) >= 80) td.classList.add('cell-hl-success');
                });
                break;
            case 'subj_lt40':
                if (!subjId) return;
                match = subjScoreCells.some(td => {
                    const s = parseFloat(td.dataset.score);
                    return s > 0 && s < 40;
                });
                hlClass = 'hl-danger';
                if (match) subjScoreCells.forEach(td => {
                    const s = parseFloat(td.dataset.score);
                    if (s > 0 && s < 40) td.classList.add('cell-hl-danger');
                });
                break;

            // ── Completion ──
            case 'missing':
                match = allScoreCells.some(td => parseFloat(td.dataset.score) === 0);
                hlClass = 'hl-warning';
                if (match) allScoreCells.forEach(td => {
                    if (parseFloat(td.dataset.score) === 0) td.classList.add('cell-hl');
                });
                break;
            case 'complete':
                match = allScoreCells.length > 0
                    && allScoreCells.every(td => parseFloat(td.dataset.score) > 0);
                hlClass = 'hl-success';
                break;
            case 'partial':
                const zeros    = allScoreCells.filter(td => parseFloat(td.dataset.score) === 0).length;
                const nonZeros = allScoreCells.filter(td => parseFloat(td.dataset.score) > 0).length;
                match = zeros > 0 && nonZeros > 0;
                hlClass = 'hl-warning';
                break;

            // ── GPA ──
            case 'gpa_ge4':
                match = gpa >= 4.0;
                hlClass = 'hl-success';
                break;
            case 'gpa_ge3':
                match = gpa >= 3.0;
                hlClass = 'hl-primary';
                break;
            case 'gpa_lt2':
                match = gpa < 2.0 && gpa > 0;
                hlClass = 'hl-warning';
                break;
            case 'gpa_lt1':
                match = gpa < 1.0;
                hlClass = 'hl-danger';
                break;
        }

        if (match) {
            row.classList.add(hlClass);
            picked.push({ row, hlClass });
        }
    });

    // ── Post-loop: ranking & subject-top ──
    if (['top5','top10','bottom5','top3_subject','most_improved'].includes(mode)) {
        const withGpa = allRows.map(r => ({
            row: r,
            gpa: parseFloat(r.dataset.gpa) || 0,
            ns:  parseInt(r.dataset.ns)    || 0,
            subjScore: subjId ? (() => {
                const c = r.querySelector(`td[data-sub="${subjId}"][data-col="total"]`)
                       || r.querySelector(`td[data-sub="${subjId}"][data-score]`);
                return c ? parseFloat(c.dataset.score) || 0 : 0;
            })() : 0,
        }));

        if (mode === 'top5' || mode === 'top10') {
            const n = mode === 'top5' ? 5 : 10;
            [...withGpa].sort((a,b) => b.gpa - a.gpa).slice(0, n).forEach(({row}) => {
                row.classList.add('hl-success');
                picked.push({ row, hlClass: 'hl-success' });
            });
        } else if (mode === 'bottom5') {
            [...withGpa].sort((a,b) => a.gpa - b.gpa).slice(0, 5).forEach(({row}) => {
                row.classList.add('hl-danger');
                picked.push({ row, hlClass: 'hl-danger' });
            });
        } else if (mode === 'top3_subject' && subjId) {
            [...withGpa].sort((a,b) => b.subjScore - a.subjScore).slice(0, 3).forEach(({row}) => {
                row.classList.add('hl-success');
                picked.push({ row, hlClass: 'hl-success' });
            });
        } else if (mode === 'most_improved') {
            [...withGpa].sort((a,b) => b.ns - a.ns).slice(0, 5).forEach(({row}) => {
                row.classList.add('hl-primary');
                picked.push({ row, hlClass: 'hl-primary' });
            });
        }
    }

    // ── Dim non-matching ──
    const matchSet = new Set(picked.map(p => p.row));
    if (matchSet.size > 0) {
        allRows.forEach(r => {
            if (!matchSet.has(r)) r.classList.add('hl-dim');
        });
    }

    matchedRows = [...matchSet];

    // Update UI
    const badge = document.getElementById('resultBadge');
    const nextBtn = document.getElementById('nextMatchBtn');
    badge.textContent = matchedRows.length + ' found';
    badge.style.display = matchedRows.length > 0 ? 'inline-block' : 'none';
    nextBtn.style.display = matchedRows.length > 0 ? 'inline-flex' : 'none';
    nextBtn.innerHTML = `<i class="ri-arrow-down-line"></i> Next (1/${matchedRows.length})`;

    showToast(matchedRows.length + ' student(s) matched for: ' + document.getElementById('locateSelect').selectedOptions[0]?.text);
}

function scrollToNext() {
    if (matchedRows.length === 0) return;
    const row = matchedRows[matchCursor % matchedRows.length];
    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
    row.classList.add('row-flash');
    setTimeout(() => row.classList.remove('row-flash'), 1600);
    matchCursor = (matchCursor + 1) % matchedRows.length;
    const nextBtn = document.getElementById('nextMatchBtn');
    nextBtn.innerHTML = `<i class="ri-arrow-down-line"></i> Next (${matchCursor + 1}/${matchedRows.length})`;
}

/* ══════════════════════════════════════════════════════════
   TOAST
══════════════════════════════════════════════════════════ */
function showToast(msg) {
    const t = document.getElementById('bswToast');
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => t.classList.remove('show'), 3000);
}

/* ══════════════════════════════════════════════════════════
   KEYBOARD SHORTCUTS
══════════════════════════════════════════════════════════ */
document.addEventListener('keydown', e => {
    if (e.ctrlKey || e.metaKey) {
        if (e.key === '=' || e.key === '+') { e.preventDefault(); changeZoom(+.1); }
        if (e.key === '-')                  { e.preventDefault(); changeZoom(-.1); }
        if (e.key === '0')                  { e.preventDefault(); fitZoom(); }
    }
    if (e.key === 'Escape') {
        document.getElementById('locateSelect').value = '';
        locateMode = '';
        onLocateChange();
    }
    if (e.key === 'Enter' && matchedRows.length > 0) scrollToNext();
    if (e.key === 'n' && !e.ctrlKey && matchedRows.length > 0) scrollToNext();
});

/* ══════════════════════════════════════════════════════════
   STAGGERED ROW ENTRY ANIMATION
══════════════════════════════════════════════════════════ */
function animateRows() {
    const rows = document.querySelectorAll('#bsTable tbody tr');
    rows.forEach((r, i) => {
        r.style.opacity = '0';
        r.style.transform = 'translateX(-8px)';
        r.style.transition = `opacity .25s ease ${Math.min(i*0.018, 0.5)}s, transform .25s ease ${Math.min(i*0.018, 0.5)}s`;
        requestAnimationFrame(() => {
            r.style.opacity = '1';
            r.style.transform = 'translateX(0)';
        });
    });
}

/* ══════════════════════════════════════════════════════════
   PASS BAR ANIMATION
══════════════════════════════════════════════════════════ */
function animateBars() {
    const fills = document.querySelectorAll('.pass-bar-fill');
    fills.forEach(f => {
        const target = f.dataset.w || '0';
        f.style.width = '0';
        setTimeout(() => { f.style.width = target + '%'; }, 600);
    });
}

/* ══════════════════════════════════════════════════════════
   INIT
══════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(fitZoom, 150);
    animateRows();
    animateBars();
    showToast('Broadsheet loaded  ·  Ctrl+/- to zoom  ·  Esc to clear filter');
});
</script>
@endpush
