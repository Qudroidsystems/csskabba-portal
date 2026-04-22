@extends('layouts.master')
@section('content')
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--s:#0f172a;--sf:#1e293b;--sc:#263348;--b:rgba(148,163,184,.15);--t:#e2e8f0;--m:#94a3b8;--ac:#38bdf8;--pr:#22c55e;--ab:#ef4444;--sk:#f59e0b;--ex:#a78bfa;--lt:#fb923c;--r:12px;}
.rpt{background:var(--s);min-height:100vh;padding:24px;color:var(--t);font-family:'DM Sans',sans-serif;}
.rpt-header{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:28px;}
.rpt-header h1{font-size:1.5rem;font-weight:800;color:#fff;margin:0;}
.rpt-header h1 span{color:var(--ac);}
.back-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;background:rgba(148,163,184,.1);border:1px solid var(--b);color:var(--t);text-decoration:none;font-size:13px;font-weight:600;transition:.18s;}
.back-btn:hover{border-color:var(--ac);color:var(--ac);}

/* Filter panel */
.filter-panel{background:var(--sf);border:1px solid var(--b);border-radius:var(--r);padding:20px;margin-bottom:24px;display:flex;gap:14px;align-items:flex-end;flex-wrap:wrap;}
.fg{display:flex;flex-direction:column;gap:5px;}
.fg label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--m);}
.fg select{background:var(--sc);border:1px solid var(--b);color:var(--t);border-radius:8px;padding:9px 12px;font-size:14px;outline:none;min-width:180px;}
.fg select:focus{border-color:var(--ac);}
.btn-filter{background:var(--ac);color:#0f172a;border:none;border-radius:8px;padding:10px 22px;font-weight:700;font-size:13px;cursor:pointer;transition:.18s;}
.btn-filter:hover{background:#7dd3fc;}

/* Top stats */
.stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:12px;margin-bottom:24px;}
.stat-c{background:var(--sf);border:1px solid var(--b);border-radius:var(--r);padding:16px;text-align:center;}
.stat-c .v{font-size:2rem;font-weight:800;line-height:1;}
.stat-c .l{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--m);margin-top:4px;}

/* Alert box */
.empty-box{background:var(--sf);border:1px solid var(--b);border-radius:var(--r);padding:48px 24px;text-align:center;color:var(--m);}
.empty-box .ei{font-size:48px;margin-bottom:12px;}
.empty-box h3{color:var(--t);margin-bottom:6px;}

/* Table */
.tbl-wrap{background:var(--sf);border:1px solid var(--b);border-radius:var(--r);overflow:hidden;}
.tbl-wrap .tbl-head{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--b);gap:12px;flex-wrap:wrap;}
.tbl-wrap .tbl-head h3{font-size:14px;font-weight:700;color:#fff;margin:0;}
.srch{background:var(--sc);border:1px solid var(--b);color:var(--t);border-radius:8px;padding:8px 14px;font-size:13px;outline:none;width:240px;}
.srch:focus{border-color:var(--ac);}
.rpt-table{width:100%;border-collapse:collapse;}
.rpt-table th{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--m);padding:11px 14px;text-align:left;border-bottom:1px solid var(--b);background:rgba(56,189,248,.04);}
.rpt-table td{padding:10px 14px;font-size:13px;border-bottom:1px solid var(--b);vertical-align:middle;}
.rpt-table tr:last-child td{border-bottom:none;}
.rpt-table tr:hover td{background:rgba(255,255,255,.02);}
.stu-cell{display:flex;align-items:center;gap:10px;}
.stu-cell img{width:32px;height:32px;border-radius:50%;object-fit:cover;border:2px solid var(--b);}
.stu-name{font-weight:600;font-size:13px;color:#fff;}
.stu-adm{font-size:11px;color:var(--m);}
.mini{display:inline-flex;align-items:center;gap:3px;padding:2px 7px;border-radius:5px;font-size:11px;font-weight:700;}
.mp{background:rgba(34,197,94,.12);color:var(--pr);}
.ma{background:rgba(239,68,68,.12);color:var(--ab);}
.ms{background:rgba(245,158,11,.12);color:var(--sk);}
.me{background:rgba(167,139,250,.12);color:var(--ex);}
.ml{background:rgba(251,146,60,.12);color:var(--lt);}
.bar-wrap{display:flex;align-items:center;gap:8px;min-width:140px;}
.pct-bar{flex:1;height:5px;background:var(--sc);border-radius:99px;overflow:hidden;}
.pct-fill{height:100%;border-radius:99px;}
.pct-fill.high{background:var(--pr);}
.pct-fill.mid{background:var(--sk);}
.pct-fill.low{background:var(--ab);}
.pct-text{font-size:12px;font-weight:700;min-width:38px;text-align:right;}
.pct-text.high{color:var(--pr);}
.pct-text.mid{color:var(--sk);}
.pct-text.low{color:var(--ab);}
.class-badge{display:inline-block;padding:2px 9px;border-radius:5px;font-size:11px;font-weight:600;background:rgba(56,189,248,.1);color:var(--ac);}
.view-lnk{font-size:11px;color:var(--ac);text-decoration:none;font-weight:600;}
.view-lnk:hover{text-decoration:underline;}
</style>

<div class="rpt">
    <div class="rpt-header">
        <div>
            <h1>School <span>Attendance Report</span></h1>
            <p style="color:var(--m);font-size:13px;margin:2px 0 0;">Overview of student attendance across all classes.</p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button onclick="window.print()" class="back-btn">🖨 Print</button>
            <a href="{{ route('attendance.settings') }}" class="back-btn">← Settings</a>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('attendance.school-report') }}">
        <div class="filter-panel">
            <div class="fg">
                <label>Term</label>
                <select name="term_id">
                    <option value="">All Terms</option>
                    @foreach($terms as $t)
                        <option value="{{ $t->id }}" {{ $termId == $t->id ? 'selected' : '' }}>{{ $t->term }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fg">
                <label>Session</label>
                <select name="session_id">
                    <option value="">All Sessions</option>
                    @foreach($sessions as $s)
                        <option value="{{ $s->id }}" {{ $sessionId == $s->id ? 'selected' : '' }}>{{ $s->session }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-filter">🔍 Generate Report</button>
        </div>
    </form>

    @if($termId && $sessionId && $summaries->isNotEmpty())
    {{-- Stats --}}
    @php
        $avgPct   = round($summaries->avg('attendance_percentage'), 1);
        $totP     = $summaries->sum('days_present');
        $totA     = $summaries->sum('days_absent');
        $totS     = $summaries->sum('days_sick_leave');
        $above80  = $summaries->where('attendance_percentage', '>=', 80)->count();
        $below60  = $summaries->where('attendance_percentage', '<', 60)->count();
    @endphp
    <div class="stats-row">
        <div class="stat-c"><div class="v" style="color:var(--ac);">{{ $summaries->count() }}</div><div class="l">Students</div></div>
        <div class="stat-c"><div class="v" style="color:{{ $avgPct >= 80 ? 'var(--pr)' : ($avgPct >= 60 ? 'var(--sk)' : 'var(--ab)') }};">{{ $avgPct }}%</div><div class="l">School Avg</div></div>
        <div class="stat-c"><div class="v" style="color:var(--pr);">{{ $above80 }}</div><div class="l">Above 80%</div></div>
        <div class="stat-c"><div class="v" style="color:var(--ab);">{{ $below60 }}</div><div class="l">Below 60%</div></div>
        <div class="stat-c"><div class="v" style="color:var(--pr);">{{ $totP }}</div><div class="l">Total Present</div></div>
        <div class="stat-c"><div class="v" style="color:var(--ab);">{{ $totA }}</div><div class="l">Total Absent</div></div>
        <div class="stat-c"><div class="v" style="color:var(--sk);">{{ $totS }}</div><div class="l">Sick Leave</div></div>
    </div>

    {{-- Table --}}
    <div class="tbl-wrap">
        <div class="tbl-head">
            <h3>All Students</h3>
            <input type="text" class="srch" id="srch" placeholder="Search student or class…">
        </div>
        <div style="overflow-x:auto;">
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Sick</th>
                        <th>Excused</th>
                        <th>Late</th>
                        <th>Attendance</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="rptRows">
                @foreach($summaries as $i => $s)
                @php
                    $pct = (float) $s->attendance_percentage;
                    $cls = $pct >= 80 ? 'high' : ($pct >= 60 ? 'mid' : 'low');
                    $img = $s->student?->picture
                        ? asset('storage/student_avatars/'.basename($s->student->picture))
                        : asset('storage/student_avatars/unnamed.jpg');
                    $sName = strtolower(($s->student->lname ?? '').' '.($s->student->fname ?? ''));
                    $cName = strtolower($s->schoolclass?->schoolclass ?? '');
                @endphp
                <tr data-search="{{ $sName }} {{ $cName }} {{ strtolower($s->admissionno ?? '') }}">
                    <td style="color:var(--m);">{{ $i+1 }}</td>
                    <td>
                        <div class="stu-cell">
                            <img src="{{ $img }}" onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'">
                            <div>
                                <div class="stu-name">{{ $s->student?->lname }} {{ $s->student?->fname }}</div>
                                <div class="stu-adm">{{ $s->student?->admissionNo }}</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="class-badge">{{ $s->schoolclass?->schoolclass }}</span></td>
                    <td><span class="mini mp">{{ $s->days_present }}</span></td>
                    <td><span class="mini ma">{{ $s->days_absent }}</span></td>
                    <td><span class="mini ms">{{ $s->days_sick_leave }}</span></td>
                    <td><span class="mini me">{{ $s->days_excused }}</span></td>
                    <td><span class="mini ml">{{ $s->days_late }}</span></td>
                    <td>
                        <div class="bar-wrap">
                            <div class="pct-bar"><div class="pct-fill {{ $cls }}" style="width:{{ $pct }}%"></div></div>
                            <span class="pct-text {{ $cls }}">{{ $pct }}%</span>
                        </div>
                    </td>
                    <td>
                        <a href="{{ route('attendance.student-report', [$s->student_id, $s->schoolclass_id, $termId, $sessionId]) }}"
                           class="view-lnk">Details →</a>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @elseif($termId && $sessionId && $summaries->isEmpty())
    <div class="empty-box">
        <div class="ei">📭</div>
        <h3>No attendance data found</h3>
        <p>No records exist for the selected term and session.</p>
    </div>
    @else
    <div class="empty-box">
        <div class="ei">📊</div>
        <h3>Select Term & Session</h3>
        <p>Choose a term and session above to generate the school-wide attendance report.</p>
    </div>
    @endif
</div>

<script>
document.getElementById('srch')?.addEventListener('input', function(){
    const q = this.value.toLowerCase();
    document.querySelectorAll('#rptRows tr').forEach(r => {
        r.style.display = (!q || r.dataset.search.includes(q)) ? '' : 'none';
    });
});
</script>
@endsection
