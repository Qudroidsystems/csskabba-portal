@extends('layouts.master')

@section('content')

{{-- ============================================================
     STUDENT ASSESSMENT PORTAL — Premium redesign
     Font: Playfair Display (headings) + DM Sans (body)
     Theme: Deep navy + gold accent + paper-white cards
     ============================================================ --}}

<style>
/* ── Google Fonts ─────────────────────────────────────────── */
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;900&family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap');

/* ── CSS Variables ────────────────────────────────────────── */
:root {
    --navy:        #0f1c35;
    --navy-mid:    #1a2f55;
    --navy-soft:   #223565;
    --gold:        #c9a84c;
    --gold-light:  #f0d080;
    --gold-pale:   #fdf6e3;
    --cream:       #f9f7f2;
    --paper:       #ffffff;
    --ink:         #1a1a2e;
    --ink-soft:    #3d4463;
    --ink-muted:   #7b85a3;
    --border:      #e3e7f0;
    --success:     #1a7f5a;
    --danger:      #c0392b;
    --warning:     #d4870a;
    --info:        #1565c0;
    --grade-a:     #0e6b46;
    --grade-b:     #1565c0;
    --grade-c:     #b8860b;
    --grade-d:     #c17600;
    --grade-f:     #c0392b;
    --shadow-sm:   0 1px 4px rgba(15,28,53,.07);
    --shadow-md:   0 4px 20px rgba(15,28,53,.10);
    --shadow-lg:   0 12px 40px rgba(15,28,53,.15);
    --radius:      12px;
    --radius-sm:   8px;
}

/* ── Reset scoped to main content ─────────────────────────── */
.assessment-portal * { box-sizing: border-box; }

.assessment-portal {
    font-family: 'DM Sans', sans-serif;
    background: var(--cream);
    min-height: 100vh;
    padding: 0 0 60px 0;
    color: var(--ink);
}

/* ── Hero Banner ──────────────────────────────────────────── */
.ap-hero {
    background: var(--navy);
    padding: 36px 32px 28px;
    position: relative;
    overflow: hidden;
}

.ap-hero::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 300px; height: 300px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(201,168,76,.18) 0%, transparent 70%);
}

.ap-hero::after {
    content: '';
    position: absolute;
    bottom: -40px; left: 200px;
    width: 200px; height: 200px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(201,168,76,.10) 0%, transparent 70%);
}

.ap-hero-inner { position: relative; z-index: 1; }

.ap-hero-eyebrow {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--gold);
    margin-bottom: 8px;
}

.ap-hero-title {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    font-weight: 900;
    color: #fff;
    margin: 0 0 6px 0;
    line-height: 1.15;
}

.ap-hero-sub {
    color: rgba(255,255,255,.55);
    font-size: 13.5px;
    font-weight: 400;
    margin: 0;
}

.ap-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(201,168,76,.15);
    border: 1px solid rgba(201,168,76,.35);
    color: var(--gold-light);
    font-size: 11px;
    font-weight: 600;
    padding: 4px 12px;
    border-radius: 20px;
    margin-top: 14px;
}

/* ── Filter Bar ───────────────────────────────────────────── */
.ap-filter-bar {
    background: var(--paper);
    border-bottom: 1px solid var(--border);
    padding: 16px 32px;
    display: flex;
    align-items: flex-end;
    gap: 16px;
    flex-wrap: wrap;
}

.ap-filter-group { display: flex; flex-direction: column; gap: 5px; }

.ap-filter-label {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--ink-muted);
}

.ap-filter-select {
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 500;
    color: var(--ink);
    background: var(--cream);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 8px 12px;
    min-width: 160px;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%237b85a3' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    padding-right: 30px;
    cursor: pointer;
    transition: border-color .2s, box-shadow .2s;
}

.ap-filter-select:focus {
    outline: none;
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(201,168,76,.15);
}

.ap-filter-btn {
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: var(--navy);
    background: var(--gold);
    border: none;
    border-radius: var(--radius-sm);
    padding: 9px 22px;
    cursor: pointer;
    transition: background .2s, transform .1s;
}
.ap-filter-btn:hover { background: var(--gold-light); transform: translateY(-1px); }

.ap-print-btn {
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: #fff;
    background: var(--navy-mid);
    border: 1.5px solid var(--navy-soft);
    border-radius: var(--radius-sm);
    padding: 9px 22px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    transition: background .2s, transform .1s;
}
.ap-print-btn:hover { background: var(--navy-soft); color: #fff; transform: translateY(-1px); text-decoration: none; }

/* ── Main Layout ──────────────────────────────────────────── */
.ap-body {
    max-width: 1200px;
    margin: 0 auto;
    padding: 32px 24px;
}

/* ── Student Identity Card ────────────────────────────────── */
.ap-identity-card {
    background: var(--paper);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    box-shadow: var(--shadow-md);
    padding: 24px 28px;
    display: flex;
    align-items: center;
    gap: 24px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.ap-identity-card::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 4px;
    background: linear-gradient(to bottom, var(--gold), var(--navy));
}

.ap-avatar {
    width: 64px; height: 64px;
    border-radius: 50%;
    background: var(--navy);
    display: flex; align-items: center; justify-content: center;
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    font-weight: 700;
    color: var(--gold);
    flex-shrink: 0;
    border: 3px solid var(--gold-pale);
    overflow: hidden;
}

.ap-avatar img { width: 100%; height: 100%; object-fit: cover; }

.ap-identity-name {
    font-family: 'Playfair Display', serif;
    font-size: 19px;
    font-weight: 700;
    color: var(--navy);
    margin: 0 0 4px 0;
}

.ap-identity-meta {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

.ap-identity-chip {
    font-size: 11.5px;
    font-weight: 500;
    color: var(--ink-soft);
    background: var(--cream);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 3px 11px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.ap-identity-chip svg { opacity: .6; }

/* ── Stats Strip ──────────────────────────────────────────── */
.ap-stats-strip {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 14px;
    margin-bottom: 24px;
}

@media (max-width: 900px) { .ap-stats-strip { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 560px) { .ap-stats-strip { grid-template-columns: repeat(2, 1fr); } }

.ap-stat-card {
    background: var(--paper);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 18px 16px;
    text-align: center;
    box-shadow: var(--shadow-sm);
    transition: transform .2s, box-shadow .2s;
    position: relative;
    overflow: hidden;
}

.ap-stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }

.ap-stat-card::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
    background: var(--gold);
    border-radius: 0 0 var(--radius) var(--radius);
    opacity: 0;
    transition: opacity .2s;
}
.ap-stat-card:hover::after { opacity: 1; }

.ap-stat-value {
    font-family: 'Playfair Display', serif;
    font-size: 26px;
    font-weight: 700;
    color: var(--navy);
    line-height: 1;
    margin-bottom: 6px;
}

.ap-stat-label {
    font-size: 10.5px;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--ink-muted);
}

/* ── GPA Trend Chart Card ─────────────────────────────────── */
.ap-trend-card {
    background: var(--paper);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow-sm);
    padding: 22px 24px;
    margin-bottom: 24px;
}

.ap-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
}

.ap-card-title {
    font-family: 'Playfair Display', serif;
    font-size: 16px;
    font-weight: 700;
    color: var(--navy);
    margin: 0;
}

.ap-card-subtitle {
    font-size: 12px;
    color: var(--ink-muted);
    margin-top: 2px;
}

/* ── Subject Accordion ────────────────────────────────────── */
.ap-accordion { display: flex; flex-direction: column; gap: 12px; }

.ap-accordion-item {
    background: var(--paper);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    transition: box-shadow .2s;
}

.ap-accordion-item.is-open { box-shadow: var(--shadow-md); }

.ap-accordion-trigger {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 22px;
    background: none;
    border: none;
    cursor: pointer;
    text-align: left;
    gap: 16px;
    transition: background .15s;
}
.ap-accordion-trigger:hover { background: var(--cream); }

.ap-accordion-left { display: flex; align-items: center; gap: 14px; flex: 1; min-width: 0; }

.ap-subject-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    background: var(--navy);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    font-size: 16px;
}

.ap-subject-name {
    font-family: 'Playfair Display', serif;
    font-size: 15px;
    font-weight: 700;
    color: var(--navy);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ap-subject-code {
    font-family: 'DM Mono', monospace;
    font-size: 11px;
    color: var(--ink-muted);
    margin-top: 2px;
}

.ap-accordion-right {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
}

.ap-grade-pill {
    font-weight: 700;
    font-size: 12px;
    padding: 4px 12px;
    border-radius: 20px;
    letter-spacing: .5px;
}

.ap-score-display {
    font-family: 'DM Mono', monospace;
    font-size: 18px;
    font-weight: 500;
    color: var(--navy);
    min-width: 48px;
    text-align: right;
}

.ap-chevron {
    transition: transform .25s;
    color: var(--ink-muted);
    flex-shrink: 0;
}

.ap-accordion-item.is-open .ap-chevron { transform: rotate(180deg); }

/* ── Accordion Panel ──────────────────────────────────────── */
.ap-panel {
    display: none;
    border-top: 1px solid var(--border);
    padding: 22px 22px 26px;
    background: var(--gold-pale);
}

.ap-accordion-item.is-open .ap-panel { display: block; }

/* ── Mini Stats Row inside panel ──────────────────────────── */
.ap-mini-stats {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.ap-mini-stat {
    background: var(--paper);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 10px 16px;
    text-align: center;
    flex: 1;
    min-width: 80px;
}

.ap-mini-stat-val {
    font-family: 'DM Mono', monospace;
    font-size: 18px;
    font-weight: 500;
    color: var(--navy);
}

.ap-mini-stat-lbl {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--ink-muted);
    margin-top: 3px;
}

/* ── Assessment bars ──────────────────────────────────────── */
.ap-assessments-grid { display: flex; flex-direction: column; gap: 10px; }

.ap-assessment-row {
    background: var(--paper);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 12px 16px;
}

.ap-assessment-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
    gap: 8px;
}

.ap-assessment-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--ink);
    flex: 1;
}

.ap-assessment-score {
    font-family: 'DM Mono', monospace;
    font-size: 13px;
    font-weight: 500;
    color: var(--navy);
    white-space: nowrap;
}

.ap-assessment-pct {
    font-size: 11px;
    font-weight: 600;
    color: var(--ink-muted);
    min-width: 38px;
    text-align: right;
}

.ap-bar-track {
    height: 6px;
    background: var(--cream);
    border-radius: 3px;
    overflow: hidden;
}

.ap-bar-fill {
    height: 100%;
    border-radius: 3px;
    transition: width .7s cubic-bezier(.4,0,.2,1);
}

/* ── Sub-assessments ──────────────────────────────────────── */
.ap-sub-list {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px dashed var(--border);
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.ap-sub-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    font-size: 12px;
}

.ap-sub-name { color: var(--ink-soft); flex: 1; }

.ap-sub-score {
    font-family: 'DM Mono', monospace;
    font-size: 11.5px;
    color: var(--navy);
}

.ap-sub-pct-bar {
    width: 80px; height: 4px;
    background: var(--cream);
    border-radius: 2px;
    overflow: hidden;
}

.ap-sub-pct-fill { height: 100%; border-radius: 2px; }

/* ── Grade colours ────────────────────────────────────────── */
.grade-A1, .grade-A  { background:#d4edda; color:#0e6b46; }
.grade-B2, .grade-B3, .grade-B { background:#cce5ff; color:#1565c0; }
.grade-C4, .grade-C5, .grade-C6, .grade-C { background:#fff3cd; color:#8a6000; }
.grade-D7, .grade-D { background:#ffe5cc; color:#7a4200; }
.grade-E8, .grade-F9, .grade-F { background:#f8d7da; color:#c0392b; }

/* bar fill colours by percentage */
.bar-excellent { background: #1a7f5a; }
.bar-good      { background: #2563eb; }
.bar-average   { background: #d4870a; }
.bar-low       { background: #c0392b; }

/* ── Empty State ──────────────────────────────────────────── */
.ap-empty {
    background: var(--paper);
    border: 1.5px dashed var(--border);
    border-radius: var(--radius);
    padding: 60px 24px;
    text-align: center;
    color: var(--ink-muted);
}

.ap-empty-icon {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: .5;
}

.ap-empty-title {
    font-family: 'Playfair Display', serif;
    font-size: 18px;
    color: var(--navy);
    margin-bottom: 8px;
}

/* ── Alert ────────────────────────────────────────────────── */
.ap-alert {
    border-radius: var(--radius-sm);
    padding: 12px 18px;
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 20px;
    border-left: 4px solid;
}

.ap-alert-warning { background:#fff3cd; border-color:#f59e0b; color:#7c4700; }
.ap-alert-info    { background:#eff6ff; border-color:#3b82f6; color:#1e3a8a; }

/* ── Responsive tweaks ────────────────────────────────────── */
@media (max-width: 600px) {
    .ap-filter-bar { flex-direction: column; align-items: stretch; }
    .ap-identity-card { flex-direction: column; align-items: flex-start; gap: 14px; }
    .ap-accordion-trigger { padding: 14px 16px; }
    .ap-score-display { font-size: 15px; }
}
</style>

<div class="assessment-portal">

    {{-- ── Hero ── --}}
    <div class="ap-hero">
        <div class="ap-hero-inner">
            <p class="ap-hero-eyebrow">Academic Portal</p>
            <h1 class="ap-hero-title">My Assessment Report</h1>
            <p class="ap-hero-sub">View your subject scores, assessment breakdowns and GPA progress</p>
            @if(isset($term) && isset($session))
                <span class="ap-hero-badge">
                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none"><circle cx="5" cy="5" r="4" stroke="currentColor" stroke-width="1.2"/><path d="M5 3v2.5l1.5 1" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
                    {{ $term->term ?? '' }} · {{ $session->session ?? '' }}
                </span>
            @endif
        </div>
    </div>

    {{-- ── Filter Bar ── --}}
    <form method="GET" action="{{ route('assessments') }}" id="filterForm">
        <div class="ap-filter-bar">
            <div class="ap-filter-group">
                <label class="ap-filter-label">Term</label>
                <select name="term_id" class="ap-filter-select">
                    <option value="">All Terms</option>
                    @foreach($terms as $t)
                        <option value="{{ $t->id }}" {{ $userSelectedTermId == $t->id ? 'selected' : '' }}>{{ $t->term }}</option>
                    @endforeach
                </select>
            </div>

            <div class="ap-filter-group">
                <label class="ap-filter-label">Session</label>
                <select name="session_id" class="ap-filter-select">
                    <option value="">All Sessions</option>
                    @foreach($sessions as $s)
                        <option value="{{ $s->id }}" {{ $selectedSessionId == $s->id ? 'selected' : '' }}>{{ $s->session }}</option>
                    @endforeach
                </select>
            </div>

            <div class="ap-filter-group" style="justify-content:flex-end;">
                <label class="ap-filter-label" style="visibility:hidden">.</label>
                <button type="submit" class="ap-filter-btn">Apply Filter</button>
            </div>

            @if(isset($subjectsWithAssessments) && $subjectsWithAssessments->isNotEmpty())
            <div class="ap-filter-group" style="justify-content:flex-end; margin-left:auto;">
                <label class="ap-filter-label" style="visibility:hidden">.</label>
                <a href="{{ route('assessments.print', array_filter(['session_id' => $selectedSessionId, 'term_id' => $selectedTermId])) }}"
                   class="ap-print-btn" target="_blank">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>
                    </svg>
                    Print / Save PDF
                </a>
            </div>
            @endif
        </div>
    </form>

    <div class="ap-body">

        {{-- ── Error / Info Alerts ── --}}
        @if(session('error') || isset($error))
            <div class="ap-alert ap-alert-warning">⚠ {{ session('error') ?? $error }}</div>
        @endif

        @if(!isset($subjectsWithAssessments) || $subjectsWithAssessments->isEmpty())
            <div class="ap-empty">
                <div class="ap-empty-icon">📋</div>
                <div class="ap-empty-title">No Assessments Found</div>
                <p style="font-size:13.5px; max-width:360px; margin: 0 auto;">
                    No assessments are available for the selected term and session. Check back later or contact your class teacher.
                </p>
            </div>
        @else

        {{-- ── Student Identity Card ── --}}
        <div class="ap-identity-card">
            <div class="ap-avatar">
                @if(!empty($studentPicture))
                    <img src="{{ asset('storage/student_avatars/' . $studentPicture) }}" alt="{{ $student->firstname }}">
                @else
                    {{ strtoupper(substr($student->firstname, 0, 1)) }}{{ strtoupper(substr($student->lastname, 0, 1)) }}
                @endif
            </div>
            <div style="flex:1; min-width:0;">
                <p class="ap-identity-name">{{ $student->firstname }} {{ $student->lastname }}</p>
                <div class="ap-identity-meta">
                    <span class="ap-identity-chip">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Adm No: {{ $student->admissionNo }}
                    </span>
                    @isset($class)
                    <span class="ap-identity-chip">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                        {{ $class->schoolclass }}
                    </span>
                    @endisset
                    @isset($term)
                    <span class="ap-identity-chip">📅 {{ $term->term }}</span>
                    @endisset
                    @isset($session)
                    <span class="ap-identity-chip">🎓 {{ $session->session }}</span>
                    @endisset
                </div>
            </div>
        </div>

        {{-- ── Stats Strip ── --}}
        <div class="ap-stats-strip">
            <div class="ap-stat-card">
                <div class="ap-stat-value">{{ $overallProgress['total_subjects'] }}</div>
                <div class="ap-stat-label">Subjects</div>
            </div>
            <div class="ap-stat-card">
                <div class="ap-stat-value">{{ number_format($overallProgress['average_cum'], 1) }}</div>
                <div class="ap-stat-label">Avg Score</div>
            </div>
            <div class="ap-stat-card">
                <div class="ap-stat-value">{{ number_format($overallProgress['gpa'], 2) }}</div>
                <div class="ap-stat-label">GPA</div>
            </div>
            <div class="ap-stat-card">
                <div class="ap-stat-value">{{ number_format($overallProgress['cgpa'], 2) }}</div>
                <div class="ap-stat-label">CGPA</div>
            </div>
            <div class="ap-stat-card">
                @php
                    $g = $overallProgress['gpa_grade'] ?? '-';
                    $gc = match(true) {
                        str_starts_with($g,'A') => 'grade-A1',
                        str_starts_with($g,'B') => 'grade-B2',
                        str_starts_with($g,'C') => 'grade-C4',
                        str_starts_with($g,'D') => 'grade-D7',
                        default => 'grade-F9'
                    };
                @endphp
                <div class="ap-stat-value">
                    <span class="ap-grade-pill {{ $gc }}">{{ $g }}</span>
                </div>
                <div class="ap-stat-label">GPA Grade</div>
            </div>
            <div class="ap-stat-card">
                <div class="ap-stat-value">{{ number_format($overallProgress['total_grade_points'], 1) }}</div>
                <div class="ap-stat-label">Total GP</div>
            </div>
        </div>

        {{-- ── GPA Trend Chart ── --}}
        @if(isset($gpaTrend) && count($gpaTrend) > 0)
        <div class="ap-trend-card" style="margin-bottom:24px;">
            <div class="ap-card-header">
                <div>
                    <div class="ap-card-title">GPA Trend</div>
                    <div class="ap-card-subtitle">Performance across terms this session</div>
                </div>
            </div>
            <div style="position:relative; height:200px;">
                <canvas id="gpaTrendChart"></canvas>
            </div>
        </div>
        @endif

        {{-- ── Subjects Section ── --}}
        <div class="ap-card-header" style="margin-bottom:16px;">
            <div>
                <div class="ap-card-title">Subject Assessments</div>
                <div class="ap-card-subtitle">{{ $subjectsWithAssessments->count() }} subject(s) registered</div>
            </div>
            <button onclick="toggleAll()" id="toggleAllBtn"
                style="font-family:'DM Sans',sans-serif;font-size:12px;font-weight:600;color:var(--ink-muted);background:none;border:1px solid var(--border);padding:6px 14px;border-radius:var(--radius-sm);cursor:pointer;">
                Expand All
            </button>
        </div>

        <div class="ap-accordion" id="apAccordion">
            @foreach($subjectsWithAssessments as $idx => $subject)
            @php
                $cum   = $subject['cum']   ?? 0;
                $total = $subject['total'] ?? 0;
                $grade = $subject['grade'] ?? '-';
                $gradeUp = strtoupper($grade);
                $gradeClass = match(true) {
                    str_starts_with($gradeUp,'A') => 'grade-A1',
                    str_starts_with($gradeUp,'B') => 'grade-B2',
                    str_starts_with($gradeUp,'C') => 'grade-C4',
                    str_starts_with($gradeUp,'D') => 'grade-D7',
                    default                        => 'grade-F9',
                };
                $icons = ['📐','📚','🔬','🌍','💻','🎨','⚗️','📖','🧮','🏛️','🎵','✏️'];
                $icon  = $icons[$idx % count($icons)];
            @endphp

            <div class="ap-accordion-item {{ $idx === 0 ? 'is-open' : '' }}" id="item-{{ $idx }}">
                <button class="ap-accordion-trigger" onclick="toggleItem({{ $idx }})">
                    <div class="ap-accordion-left">
                        <div class="ap-subject-icon">{{ $icon }}</div>
                        <div>
                            <p class="ap-subject-name">{{ $subject['subject_name'] }}</p>
                            <p class="ap-subject-code">{{ $subject['subject_code'] ?? '' }}</p>
                        </div>
                    </div>
                    <div class="ap-accordion-right">
                        <span class="ap-grade-pill {{ $gradeClass }}">{{ $grade }}</span>
                        <span class="ap-score-display">{{ number_format($cum, 1) }}</span>
                        <svg class="ap-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                </button>

                <div class="ap-panel">

                    {{-- Mini stats --}}
                    <div class="ap-mini-stats">
                        <div class="ap-mini-stat">
                            <div class="ap-mini-stat-val">{{ number_format($total, 1) }}</div>
                            <div class="ap-mini-stat-lbl">Total</div>
                        </div>
                        <div class="ap-mini-stat">
                            <div class="ap-mini-stat-val">{{ number_format($subject['bf'], 1) }}</div>
                            <div class="ap-mini-stat-lbl">BF</div>
                        </div>
                        <div class="ap-mini-stat">
                            <div class="ap-mini-stat-val">{{ number_format($cum, 1) }}</div>
                            <div class="ap-mini-stat-lbl">Cumulative</div>
                        </div>
                        <div class="ap-mini-stat">
                            <div class="ap-mini-stat-val">{{ number_format($subject['subject_gpa'], 1) }}</div>
                            <div class="ap-mini-stat-lbl">Subject GPA</div>
                        </div>
                        <div class="ap-mini-stat">
                            <div class="ap-mini-stat-val">{{ $subject['position'] }}</div>
                            <div class="ap-mini-stat-lbl">Position</div>
                        </div>
                        <div class="ap-mini-stat">
                            <div class="ap-mini-stat-val {{ $gradeClass }}" style="border-radius:6px; font-size:16px; padding:2px 10px; display:inline-block;">{{ $grade }}</div>
                            <div class="ap-mini-stat-lbl">Grade</div>
                        </div>
                    </div>

                    @if(!empty($subject['remark']) && $subject['remark'] !== '-')
                    <div class="ap-alert ap-alert-info" style="margin-bottom:16px;">
                        <strong>Teacher's Remark:</strong> {{ $subject['remark'] }}
                    </div>
                    @endif

                    {{-- Assessment Breakdown --}}
                    @if(isset($subject['assessments']) && $subject['assessments']->isNotEmpty())
                    <p style="font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--ink-muted);margin-bottom:10px;">Assessment Breakdown</p>
                    <div class="ap-assessments-grid">
                        @foreach($subject['assessments'] as $assessment)
                        @php
                            $pct = $assessment['percentage'] ?? 0;
                            $barClass = $pct >= 75 ? 'bar-excellent'
                                : ($pct >= 55 ? 'bar-good'
                                : ($pct >= 40 ? 'bar-average'
                                : 'bar-low'));
                        @endphp
                        <div class="ap-assessment-row">
                            <div class="ap-assessment-header">
                                <span class="ap-assessment-name">{{ $assessment['name'] }}</span>
                                <span class="ap-assessment-score">{{ number_format($assessment['score'], 1) }} / {{ $assessment['max_score'] }}</span>
                                <span class="ap-assessment-pct">{{ $pct }}%</span>
                            </div>
                            <div class="ap-bar-track">
                                <div class="ap-bar-fill {{ $barClass }}" style="width: {{ min($pct,100) }}%"></div>
                            </div>

                            {{-- Sub-assessments --}}
                            @if(isset($assessment['sub_assessments']) && $assessment['sub_assessments']->isNotEmpty())
                            <div class="ap-sub-list">
                                @foreach($assessment['sub_assessments'] as $sub)
                                @php $sp = $sub['percentage'] ?? 0; @endphp
                                <div class="ap-sub-row">
                                    <span class="ap-sub-name">↳ {{ $sub['name'] }}</span>
                                    <span class="ap-sub-score">{{ number_format($sub['score'],1) }} / {{ $sub['max_score'] }}</span>
                                    <div class="ap-sub-pct-bar">
                                        <div class="ap-sub-pct-fill" style="width:{{ min($sp,100) }}%; background: {{ $sp>=60 ? '#1a7f5a' : ($sp>=40 ? '#d4870a' : '#c0392b') }};"></div>
                                    </div>
                                    <span style="font-size:10px;color:var(--ink-muted);min-width:34px;text-align:right;">{{ $sp }}%</span>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p style="font-size:13px;color:var(--ink-muted);text-align:center;padding:20px 0;">No assessment breakdown available.</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        @endif {{-- end if subjectsWithAssessments --}}
    </div>
</div>

{{-- ── Scripts ── --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
/* Accordion toggle */
function toggleItem(idx) {
    const el = document.getElementById('item-' + idx);
    el.classList.toggle('is-open');
}

let allOpen = false;
function toggleAll() {
    const items = document.querySelectorAll('.ap-accordion-item');
    allOpen = !allOpen;
    items.forEach(el => {
        if (allOpen) el.classList.add('is-open');
        else el.classList.remove('is-open');
    });
    document.getElementById('toggleAllBtn').textContent = allOpen ? 'Collapse All' : 'Expand All';
}

/* Animate bars on scroll */
const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.querySelectorAll('.ap-bar-fill,.ap-sub-pct-fill').forEach(bar => {
                const w = bar.style.width;
                bar.style.width = '0';
                requestAnimationFrame(() => { bar.style.width = w; });
            });
            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll('.ap-panel').forEach(p => observer.observe(p));

@if(isset($gpaTrend) && count($gpaTrend) > 0)
/* GPA Trend Chart */
const gpaCtx = document.getElementById('gpaTrendChart');
if (gpaCtx) {
    new Chart(gpaCtx, {
        type: 'line',
        data: {
            labels: @json(array_keys($gpaTrend)),
            datasets: [{
                label: 'GPA',
                data: @json(array_values($gpaTrend)),
                borderColor: '#c9a84c',
                backgroundColor: 'rgba(201,168,76,.12)',
                borderWidth: 2.5,
                pointBackgroundColor: '#0f1c35',
                pointBorderColor: '#c9a84c',
                pointRadius: 5,
                pointHoverRadius: 7,
                tension: 0.45,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f1c35',
                    titleColor: '#c9a84c',
                    bodyColor: '#fff',
                    padding: 12,
                    cornerRadius: 8,
                }
            },
            scales: {
                y: {
                    beginAtZero: true, max: 5,
                    grid: { color: 'rgba(0,0,0,.05)' },
                    ticks: { font: { family: 'DM Mono, monospace', size: 11 }, color: '#7b85a3' }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'DM Sans, sans-serif', size: 11 }, color: '#7b85a3' }
                }
            }
        }
    });
}
@endif
</script>

@endsection
