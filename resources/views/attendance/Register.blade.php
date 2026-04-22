@extends('layouts.master')

@section('content')
<style>
/* ── Design system ──────────────────────────────────────── */
:root {
    --att-bg:        #0f172a;
    --att-surface:   #1e293b;
    --att-card:      #263348;
    --att-border:    rgba(148,163,184,.15);
    --att-text:      #e2e8f0;
    --att-muted:     #94a3b8;
    --att-accent:    #38bdf8;
    --att-present:   #22c55e;
    --att-absent:    #ef4444;
    --att-sick:      #f59e0b;
    --att-excused:   #a78bfa;
    --att-late:      #fb923c;
    --att-radius:    12px;
    --att-shadow:    0 4px 24px rgba(0,0,0,.35);
}

.att-wrap { background: var(--att-bg); min-height: 100vh; padding: 24px; color: var(--att-text); font-family: 'DM Sans', sans-serif; }

/* ── Header ─────────────────────────────────────────────── */
.att-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 28px; }
.att-header h1 { font-size: 1.6rem; font-weight: 700; color: #fff; margin: 0; }
.att-header h1 span { color: var(--att-accent); }
.att-pills { display: flex; gap: 8px; flex-wrap: wrap; }
.att-pill { padding: 6px 14px; border-radius: 99px; font-size: 12px; font-weight: 600; letter-spacing: .4px; }
.att-pill.term    { background: rgba(56,189,248,.12); color: var(--att-accent); border: 1px solid rgba(56,189,248,.25); }
.att-pill.session { background: rgba(167,139,250,.12); color: #a78bfa; border: 1px solid rgba(167,139,250,.25); }
.att-pill.holiday { background: rgba(239,68,68,.12); color: #f87171; border: 1px solid rgba(239,68,68,.25); }

/* ── Controls bar ───────────────────────────────────────── */
.att-controls { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin-bottom: 24px; background: var(--att-surface); border: 1px solid var(--att-border); border-radius: var(--att-radius); padding: 14px 18px; }
.att-controls label { font-size: 12px; font-weight: 600; color: var(--att-muted); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; display: block; }
.att-controls select, .att-controls input[type="date"] {
    background: var(--att-card); border: 1px solid var(--att-border); color: var(--att-text);
    border-radius: 8px; padding: 8px 12px; font-size: 14px; outline: none;
    transition: border-color .2s;
}
.att-controls select:focus, .att-controls input[type="date"]:focus { border-color: var(--att-accent); }
.ctrl-group { display: flex; flex-direction: column; }
.ctrl-sep { width: 1px; height: 40px; background: var(--att-border); margin: auto 4px; }

/* ── Stats row ──────────────────────────────────────────── */
.att-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 12px; margin-bottom: 24px; }
.stat-card { background: var(--att-surface); border: 1px solid var(--att-border); border-radius: var(--att-radius); padding: 14px 16px; text-align: center; }
.stat-card .stat-val { font-size: 2rem; font-weight: 800; line-height: 1; }
.stat-card .stat-lbl { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; color: var(--att-muted); margin-top: 4px; }
.stat-present .stat-val { color: var(--att-present); }
.stat-absent .stat-val  { color: var(--att-absent); }
.stat-sick .stat-val    { color: var(--att-sick); }
.stat-excused .stat-val { color: var(--att-excused); }
.stat-late .stat-val    { color: var(--att-late); }
.stat-total .stat-val   { color: var(--att-accent); }

/* ── Quick actions ──────────────────────────────────────── */
.att-quick { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; align-items: center; }
.btn-att { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; transition: all .18s; }
.btn-att-primary { background: var(--att-accent); color: #0f172a; }
.btn-att-primary:hover { background: #7dd3fc; }
.btn-att-outline { background: transparent; border: 1px solid var(--att-border); color: var(--att-text); }
.btn-att-outline:hover { border-color: var(--att-accent); color: var(--att-accent); }
.btn-att-success { background: rgba(34,197,94,.12); color: var(--att-present); border: 1px solid rgba(34,197,94,.25); }
.btn-att-success:hover { background: rgba(34,197,94,.22); }
.btn-att-danger { background: rgba(239,68,68,.1); color: var(--att-absent); border: 1px solid rgba(239,68,68,.2); }
.btn-att-danger:hover { background: rgba(239,68,68,.2); }

/* ── Search ─────────────────────────────────────────────── */
.att-search { position: relative; flex: 1; min-width: 180px; max-width: 320px; }
.att-search input { width: 100%; background: var(--att-card); border: 1px solid var(--att-border); color: var(--att-text); border-radius: 8px; padding: 9px 12px 9px 38px; font-size: 14px; outline: none; }
.att-search input:focus { border-color: var(--att-accent); }
.att-search .search-icon { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--att-muted); }

/* ── Register table ─────────────────────────────────────── */
.att-table-wrap { background: var(--att-surface); border: 1px solid var(--att-border); border-radius: var(--att-radius); overflow: hidden; }
.att-table { width: 100%; border-collapse: collapse; }
.att-table thead th { background: rgba(56,189,248,.07); padding: 13px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: var(--att-muted); border-bottom: 1px solid var(--att-border); white-space: nowrap; }
.att-table tbody tr { border-bottom: 1px solid var(--att-border); transition: background .15s; }
.att-table tbody tr:last-child { border-bottom: none; }
.att-table tbody tr:hover { background: rgba(255,255,255,.03); }
.att-table tbody tr.hidden-row { display: none; }
.att-table td { padding: 11px 16px; vertical-align: middle; }

/* ── Student cell ───────────────────────────────────────── */
.student-cell { display: flex; align-items: center; gap: 12px; }
.student-avatar { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid var(--att-border); flex-shrink: 0; }
.student-name { font-weight: 600; font-size: 14px; color: #fff; }
.student-adm  { font-size: 12px; color: var(--att-muted); }

/* ── Status buttons ─────────────────────────────────────── */
.status-group { display: flex; gap: 6px; flex-wrap: wrap; }
.status-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 11px; border-radius: 99px; font-size: 12px; font-weight: 600;
    border: 2px solid transparent; cursor: pointer; transition: all .15s;
    background: transparent; white-space: nowrap;
}
.status-btn svg { width: 14px; height: 14px; flex-shrink: 0; }
.status-btn.present  { border-color: rgba(34,197,94,.3);  color: rgba(34,197,94,.6);  }
.status-btn.absent   { border-color: rgba(239,68,68,.3);  color: rgba(239,68,68,.6);  }
.status-btn.sick     { border-color: rgba(245,158,11,.3); color: rgba(245,158,11,.6); }
.status-btn.excused  { border-color: rgba(167,139,250,.3);color: rgba(167,139,250,.6);}
.status-btn.late     { border-color: rgba(251,146,60,.3); color: rgba(251,146,60,.6); }

.status-btn.present.active  { background: rgba(34,197,94,.15);  border-color: var(--att-present); color: var(--att-present); box-shadow: 0 0 10px rgba(34,197,94,.2); }
.status-btn.absent.active   { background: rgba(239,68,68,.15);  border-color: var(--att-absent);  color: var(--att-absent);  box-shadow: 0 0 10px rgba(239,68,68,.2); }
.status-btn.sick.active     { background: rgba(245,158,11,.15); border-color: var(--att-sick);    color: var(--att-sick);    box-shadow: 0 0 10px rgba(245,158,11,.2); }
.status-btn.excused.active  { background: rgba(167,139,250,.15);border-color: var(--att-excused); color: var(--att-excused); box-shadow: 0 0 10px rgba(167,139,250,.2); }
.status-btn.late.active     { background: rgba(251,146,60,.15); border-color: var(--att-late);    color: var(--att-late);    box-shadow: 0 0 10px rgba(251,146,60,.2); }

/* ── Notes input ────────────────────────────────────────── */
.notes-inp { background: var(--att-card); border: 1px solid var(--att-border); color: var(--att-text); border-radius: 6px; padding: 5px 9px; font-size: 12px; width: 140px; outline: none; }
.notes-inp:focus { border-color: var(--att-accent); }

/* ── Summary badge ──────────────────────────────────────── */
.summary-bar { display: flex; gap: 4px; flex-wrap: wrap; }
.s-dot { width: 20px; height: 20px; border-radius: 4px; font-size: 10px; font-weight: 700; display: flex; align-items: center; justify-content: center; }
.s-dot.p { background: rgba(34,197,94,.2);  color: var(--att-present); }
.s-dot.a { background: rgba(239,68,68,.2);  color: var(--att-absent); }
.s-dot.s { background: rgba(245,158,11,.2); color: var(--att-sick); }
.s-dot.e { background: rgba(167,139,250,.2);color: var(--att-excused); }
.s-dot.l { background: rgba(251,146,60,.2); color: var(--att-late); }
.pct-badge { font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 99px; }
.pct-high { background: rgba(34,197,94,.15); color: var(--att-present); }
.pct-mid  { background: rgba(245,158,11,.15); color: var(--att-sick); }
.pct-low  { background: rgba(239,68,68,.15); color: var(--att-absent); }

/* ── Save bar ───────────────────────────────────────────── */
.att-save-bar {
    position: sticky; bottom: 0; background: var(--att-surface);
    border-top: 1px solid var(--att-border); padding: 14px 20px;
    display: flex; align-items: center; justify-content: space-between;
    gap: 12px; flex-wrap: wrap; z-index: 20;
    box-shadow: 0 -4px 20px rgba(0,0,0,.3);
}
.save-info { font-size: 13px; color: var(--att-muted); }
.save-info strong { color: var(--att-text); }

/* ── Toast ──────────────────────────────────────────────── */
#att-toast { position: fixed; bottom: 90px; right: 24px; z-index: 999; display: flex; flex-direction: column; gap: 8px; }
.toast-item { padding: 12px 18px; border-radius: 10px; font-size: 13px; font-weight: 600; animation: slideUp .3s ease; max-width: 320px; }
.toast-success { background: rgba(34,197,94,.9); color: #fff; }
.toast-error   { background: rgba(239,68,68,.9);  color: #fff; }
@keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

/* ── Holiday banner ─────────────────────────────────────── */
.holiday-banner { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3); border-radius: 10px; padding: 14px 20px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; }
.holiday-banner svg { color: #f87171; flex-shrink: 0; }

/* ── Progress bar ───────────────────────────────────────── */
.save-progress { height: 3px; background: var(--att-border); border-radius: 99px; overflow: hidden; width: 200px; }
.save-progress-fill { height: 100%; background: var(--att-accent); border-radius: 99px; width: 0; transition: width .3s; }

/* ── Responsive ─────────────────────────────────────────── */
@media (max-width: 768px) {
    .att-wrap { padding: 12px; }
    .status-group { gap: 4px; }
    .status-btn { padding: 5px 8px; font-size: 11px; }
    .notes-inp { width: 100px; }
    .att-table th:nth-child(n+5):not(:last-child) { display: none; }
    .att-table td:nth-child(n+5):not(:last-child) { display: none; }
}
</style>

{{-- Load DM Sans from Google Fonts --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<div class="att-wrap">

    {{-- ── Header ─────────────────────────────────────────── --}}
    <div class="att-header">
        <div>
            <h1>Attendance <span>Register</span></h1>
            <div style="font-size:13px;color:var(--att-muted);margin-top:4px;">
                {{ $schoolclass->schoolclass }} {{ $schoolclass->arms?->arm ?? '' }}
            </div>
        </div>
        <div class="att-pills">
            <span class="att-pill term">{{ $term->term }}</span>
            <span class="att-pill session">{{ $session->session }}</span>
            @if($isHoliday)
                <span class="att-pill holiday">⛔ Holiday</span>
            @endif
        </div>
    </div>

    @if($isHoliday)
    <div class="holiday-banner">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
        <div>
            <strong style="color:#f87171;">This date is a holiday or school break.</strong>
            <div style="font-size:12px;color:var(--att-muted);margin-top:2px;">You can still record attendance if needed, but this day may not count toward school totals.</div>
        </div>
    </div>
    @endif

    {{-- ── Controls ────────────────────────────────────────── --}}
    <div class="att-controls">
        <div class="ctrl-group">
            <label>Date</label>
            <input type="date" id="dateInput" value="{{ $date }}"
                   min="{{ $setting->resumption_date->toDateString() }}"
                   max="{{ $setting->vacation_date->toDateString() }}">
        </div>
        @if($setting->track_morning && $setting->track_afternoon)
        <div class="ctrl-sep"></div>
        <div class="ctrl-group">
            <label>Period</label>
            <select id="periodSelect">
                <option value="morning"   {{ $period === 'morning'   ? 'selected' : '' }}>🌅 Morning</option>
                <option value="afternoon" {{ $period === 'afternoon' ? 'selected' : '' }}>🌇 Afternoon</option>
            </select>
        </div>
        @else
        <input type="hidden" id="periodSelect" value="{{ $period }}">
        <div style="font-size:13px;color:var(--att-muted);">
            Period: <strong style="color:var(--att-text);">{{ ucfirst($period) }}</strong>
        </div>
        @endif
        <div class="ctrl-sep"></div>
        <div class="att-search">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="searchStudents" placeholder="Search students…">
        </div>
        <a href="{{ route('attendance.class-summary', [$classId, $termId, $sessionId]) }}" class="btn-att btn-att-outline" style="margin-left:auto;">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
            Class Summary
        </a>
        <a href="{{ route('attendance.my-classes') }}" class="btn-att btn-att-outline">← Back</a>
    </div>

    {{-- ── Live stats ──────────────────────────────────────── --}}
    <div class="att-stats" id="liveStats">
        <div class="stat-card stat-total">
            <div class="stat-val" id="stat-total">{{ $students->count() }}</div>
            <div class="stat-lbl">Total</div>
        </div>
        <div class="stat-card stat-present">
            <div class="stat-val" id="stat-present">0</div>
            <div class="stat-lbl">Present</div>
        </div>
        <div class="stat-card stat-absent">
            <div class="stat-val" id="stat-absent">0</div>
            <div class="stat-lbl">Absent</div>
        </div>
        <div class="stat-card stat-sick">
            <div class="stat-val" id="stat-sick">0</div>
            <div class="stat-lbl">Sick Leave</div>
        </div>
        <div class="stat-card stat-excused">
            <div class="stat-val" id="stat-excused">0</div>
            <div class="stat-lbl">Excused</div>
        </div>
        <div class="stat-card stat-late">
            <div class="stat-val" id="stat-late">0</div>
            <div class="stat-lbl">Late</div>
        </div>
    </div>

    {{-- ── Quick actions ───────────────────────────────────── --}}
    <div class="att-quick">
        <button class="btn-att btn-att-success" id="markAllPresentBtn">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Mark All Present
        </button>
        <button class="btn-att btn-att-danger" id="markAllAbsentBtn">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            Mark All Absent
        </button>
        <span style="font-size:13px;color:var(--att-muted);">
            Unmarked: <strong id="unmarkedCount" style="color:var(--att-text);">{{ $students->count() }}</strong>
        </span>
    </div>

    {{-- ── Table ───────────────────────────────────────────── --}}
    <div class="att-table-wrap">
        <table class="att-table" id="attendanceTable">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th>Student</th>
                    <th>Status</th>
                    <th>Notes</th>
                    <th>Term Attendance</th>
                </tr>
            </thead>
            <tbody id="studentRows">
            @php $idx = 0; @endphp
            @foreach($students as $student)
                @php
                    $idx++;
                    $currentStatus = $existing[$student->id] ?? null;
                    $sum = $summaries[$student->id] ?? null;
                    $pct = $sum ? (float) $sum->attendance_percentage : 0;
                    $pctClass = $pct >= 80 ? 'pct-high' : ($pct >= 60 ? 'pct-mid' : 'pct-low');
                    $img = $student->picture
                        ? asset('storage/student_avatars/' . basename($student->picture))
                        : asset('storage/student_avatars/unnamed.jpg');
                @endphp
                <tr data-student-id="{{ $student->id }}"
                    data-name="{{ strtolower($student->lname . ' ' . $student->fname . ' ' . ($student->mname ?? '') . ' ' . $student->admissionno) }}">
                    <td style="color:var(--att-muted);font-size:13px;">{{ $idx }}</td>
                    <td>
                        <div class="student-cell">
                            <img src="{{ $img }}" class="student-avatar"
                                 onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'">
                            <div>
                                <div class="student-name">{{ $student->lname }} {{ $student->fname }} {{ $student->mname }}</div>
                                <div class="student-adm">{{ $student->admissionno }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="status-group" data-student="{{ $student->id }}">
                            @foreach(['present' => ['✔', 'Present'], 'absent' => ['✖', 'Absent'], 'sick_leave' => ['🤒', 'Sick'], 'excused' => ['📝', 'Excused'], 'late' => ['⏰', 'Late']] as $val => [$icon, $lbl])
                            <button type="button"
                                    class="status-btn {{ str_replace('_', '', $val) === 'sickleave' ? 'sick' : str_replace('_','',$val) === 'sickleave' ? 'sick' : (str_replace('_','',$val)) }} {{ $currentStatus === $val ? 'active' : '' }}"
                                    data-status="{{ $val }}"
                                    data-student="{{ $student->id }}"
                                    onclick="toggleStatus(this, {{ $student->id }})">
                                {{ $icon }} {{ $lbl }}
                            </button>
                            @endforeach
                        </div>
                    </td>
                    <td>
                        <input type="text" class="notes-inp" data-student="{{ $student->id }}"
                               placeholder="Optional note…" maxlength="200">
                    </td>
                    <td>
                        @if($sum)
                        <div class="summary-bar">
                            <div class="s-dot p" title="Present">{{ $sum->days_present }}</div>
                            <div class="s-dot a" title="Absent">{{ $sum->days_absent }}</div>
                            <div class="s-dot s" title="Sick">{{ $sum->days_sick_leave }}</div>
                            <div class="s-dot e" title="Excused">{{ $sum->days_excused }}</div>
                            <div class="s-dot l" title="Late">{{ $sum->days_late }}</div>
                            <span class="pct-badge {{ $pctClass }}" title="Attendance %">{{ $pct }}%</span>
                        </div>
                        @else
                        <span style="font-size:12px;color:var(--att-muted);">No data yet</span>
                        @endif
                        <div style="margin-top:4px;">
                            <a href="{{ route('attendance.student-report', [$student->id, $classId, $termId, $sessionId]) }}"
                               style="font-size:11px;color:var(--att-accent);">View report →</a>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- ── Save bar ─────────────────────────────────────────── --}}
    <div class="att-save-bar">
        <div class="save-info">
            <strong id="markedCount">0</strong> / {{ $students->count() }} marked
            &nbsp;·&nbsp; <span id="currentDateLabel">{{ \Carbon\Carbon::parse($date)->format('D, d M Y') }}</span>
            &nbsp;·&nbsp; <span style="text-transform:capitalize;">{{ $period }}</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
            <div class="save-progress"><div class="save-progress-fill" id="saveProgressFill"></div></div>
            <button class="btn-att btn-att-primary" id="saveAllBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Attendance
            </button>
        </div>
    </div>

</div>

{{-- Toast container --}}
<div id="att-toast"></div>

<script>
// ── State ────────────────────────────────────────────────
const STATE = {
    classId   : {{ $classId }},
    termId    : {{ $termId }},
    sessionId : {{ $sessionId }},
    records   : {}, // student_id → { status, notes }
    total     : {{ $students->count() }},
};

// Pre-load existing attendance
@foreach($existing as $sid => $st)
STATE.records[{{ $sid }}] = { status: '{{ $st }}', notes: '' };
@endforeach

// ── DOM ready ────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    refreshStats();

    // Date / period change → reload page
    document.getElementById('dateInput').addEventListener('change', reloadPage);
    const periodSel = document.getElementById('periodSelect');
    if (periodSel.tagName === 'SELECT') periodSel.addEventListener('change', reloadPage);

    // Search
    document.getElementById('searchStudents').addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#studentRows tr').forEach(row => {
            row.classList.toggle('hidden-row', q && !row.dataset.name.includes(q));
        });
    });

    // Mark all present
    document.getElementById('markAllPresentBtn').addEventListener('click', () => {
        document.querySelectorAll('#studentRows tr').forEach(row => {
            const sid = parseInt(row.dataset.studentId);
            applyStatus(sid, 'present');
        });
        refreshStats();
    });

    // Mark all absent
    document.getElementById('markAllAbsentBtn').addEventListener('click', () => {
        document.querySelectorAll('#studentRows tr').forEach(row => {
            const sid = parseInt(row.dataset.studentId);
            applyStatus(sid, 'absent');
        });
        refreshStats();
    });

    // Save all
    document.getElementById('saveAllBtn').addEventListener('click', saveAll);

    // Ctrl+S
    document.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); saveAll(); }
    });
});

function reloadPage() {
    const d = document.getElementById('dateInput').value;
    const p = document.getElementById('periodSelect').value || '{{ $period }}';
    window.location.href = `{{ route('attendance.register', [$classId, $termId, $sessionId]) }}?date=${d}&period=${p}`;
}

// ── Toggle status (button click) ─────────────────────────
function toggleStatus(btn, studentId) {
    const status = btn.dataset.status;
    applyStatus(studentId, status);
    refreshStats();
    // Auto-save single
    const notes = document.querySelector(`.notes-inp[data-student="${studentId}"]`)?.value || '';
    saveSingle(studentId, status, notes);
}

function applyStatus(studentId, status) {
    const group = document.querySelector(`.status-group[data-student="${studentId}"]`);
    if (!group) return;
    group.querySelectorAll('.status-btn').forEach(b => {
        const s = b.dataset.status;
        const cls = s === 'sick_leave' ? 'sick' : s;
        b.classList.toggle('active', s === status);
    });
    const notes = document.querySelector(`.notes-inp[data-student="${studentId}"]`)?.value || '';
    STATE.records[studentId] = { status, notes };
}

// ── Stats ────────────────────────────────────────────────
function refreshStats() {
    const counts = { present:0, absent:0, sick_leave:0, excused:0, late:0 };
    let marked = 0;
    Object.values(STATE.records).forEach(r => {
        if (r.status) { counts[r.status] = (counts[r.status]||0)+1; marked++; }
    });
    document.getElementById('stat-present').textContent = counts.present;
    document.getElementById('stat-absent').textContent  = counts.absent;
    document.getElementById('stat-sick').textContent    = counts.sick_leave;
    document.getElementById('stat-excused').textContent = counts.excused;
    document.getElementById('stat-late').textContent    = counts.late;
    document.getElementById('markedCount').textContent  = marked;
    document.getElementById('unmarkedCount').textContent= STATE.total - marked;
}

// ── Single auto-save ─────────────────────────────────────
function saveSingle(studentId, status, notes) {
    const date   = document.getElementById('dateInput').value;
    const period = document.getElementById('periodSelect').value || '{{ $period }}';
    fetch('{{ route('attendance.save-single') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        body: JSON.stringify({
            student_id: studentId, schoolclass_id: STATE.classId,
            term_id: STATE.termId, session_id: STATE.sessionId,
            attendance_date: date, period, status, notes,
        }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.success && d.summary) updateSummaryRow(studentId, d.summary);
    })
    .catch(() => {});
}

// ── Update summary cell after save ───────────────────────
function updateSummaryRow(studentId, sum) {
    const row = document.querySelector(`tr[data-student-id="${studentId}"]`);
    if (!row) return;
    const cell = row.cells[4];
    const pct  = parseFloat(sum.attendance_percentage || 0);
    const cls  = pct >= 80 ? 'pct-high' : pct >= 60 ? 'pct-mid' : 'pct-low';
    cell.querySelector('.summary-bar') && (cell.querySelector('.summary-bar').innerHTML =
        `<div class="s-dot p" title="Present">${sum.days_present}</div>
         <div class="s-dot a" title="Absent">${sum.days_absent}</div>
         <div class="s-dot s" title="Sick">${sum.days_sick_leave}</div>
         <div class="s-dot e" title="Excused">${sum.days_excused}</div>
         <div class="s-dot l" title="Late">${sum.days_late}</div>
         <span class="pct-badge ${cls}">${pct}%</span>`
    );
}

// ── Save all ─────────────────────────────────────────────
function saveAll() {
    const date   = document.getElementById('dateInput').value;
    const period = document.getElementById('periodSelect').value || '{{ $period }}';

    // Collect all rows
    const records = [];
    document.querySelectorAll('#studentRows tr').forEach(row => {
        const sid   = parseInt(row.dataset.studentId);
        const notes = row.querySelector('.notes-inp')?.value || '';
        const active= row.querySelector('.status-btn.active');
        if (active) {
            records.push({ student_id: sid, status: active.dataset.status, notes });
            STATE.records[sid] = { status: active.dataset.status, notes };
        }
    });

    if (!records.length) { showToast('No attendance marked yet.', 'error'); return; }

    const btn  = document.getElementById('saveAllBtn');
    const fill = document.getElementById('saveProgressFill');
    btn.disabled = true;
    fill.style.width = '0%';

    const interval = setInterval(() => {
        const w = parseFloat(fill.style.width) || 0;
        if (w < 90) fill.style.width = (w + 12) + '%';
    }, 120);

    fetch('{{ route('attendance.save') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        body: JSON.stringify({
            schoolclass_id: STATE.classId, term_id: STATE.termId,
            session_id: STATE.sessionId, attendance_date: date,
            period, records,
        }),
    })
    .then(r => r.json())
    .then(d => {
        clearInterval(interval);
        fill.style.width = '100%';
        setTimeout(() => fill.style.width = '0%', 800);
        btn.disabled = false;
        showToast(d.message, d.success ? 'success' : 'error');
    })
    .catch(() => {
        clearInterval(interval); fill.style.width = '0%'; btn.disabled = false;
        showToast('Network error.', 'error');
    });
}

// ── Helpers ──────────────────────────────────────────────
function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function showToast(msg, type = 'success') {
    const c = document.getElementById('att-toast');
    const t = document.createElement('div');
    t.className = `toast-item toast-${type}`;
    t.textContent = msg;
    c.appendChild(t);
    setTimeout(() => t.remove(), 3500);
}

refreshStats();
</script>
@endsection
