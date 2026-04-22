@extends('layouts.master')
@section('content')
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--s:#0f172a;--sf:#1e293b;--sc:#263348;--b:rgba(148,163,184,.15);--t:#e2e8f0;--m:#94a3b8;--ac:#38bdf8;--pr:#22c55e;--ab:#ef4444;--sk:#f59e0b;--ex:#a78bfa;--lt:#fb923c;--r:12px;}
.cs{background:var(--s);min-height:100vh;padding:24px;color:var(--t);font-family:'DM Sans',sans-serif;}
.cs-header{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:28px;}
.cs-header h1{font-size:1.5rem;font-weight:800;color:#fff;margin:0;}
.cs-header h1 span{color:var(--ac);}
.back-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;background:rgba(148,163,184,.1);border:1px solid var(--b);color:var(--t);text-decoration:none;font-size:13px;font-weight:600;}
.stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:12px;margin-bottom:24px;}
.stat-c{background:var(--sf);border:1px solid var(--b);border-radius:var(--r);padding:16px;text-align:center;}
.stat-c .v{font-size:2rem;font-weight:800;line-height:1;}
.stat-c .l{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--m);margin-top:4px;}
.tbl-wrap{background:var(--sf);border:1px solid var(--b);border-radius:var(--r);overflow:hidden;}
.cs-table{width:100%;border-collapse:collapse;}
.cs-table th{background:rgba(56,189,248,.07);padding:12px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--m);border-bottom:1px solid var(--b);white-space:nowrap;text-align:left;}
.cs-table td{padding:11px 14px;font-size:13px;border-bottom:1px solid var(--b);vertical-align:middle;}
.cs-table tr:last-child td{border-bottom:none;}
.cs-table tr:hover td{background:rgba(255,255,255,.02);}
.stu-cell{display:flex;align-items:center;gap:10px;}
.stu-cell img{width:34px;height:34px;border-radius:50%;object-fit:cover;border:2px solid var(--b);}
.stu-name{font-weight:600;font-size:13px;color:#fff;}
.stu-adm{font-size:11px;color:var(--m);}
.bar-wrap{display:flex;align-items:center;gap:8px;min-width:160px;}
.pct-bar{flex:1;height:6px;background:var(--sc);border-radius:99px;overflow:hidden;}
.pct-fill{height:100%;border-radius:99px;}
.pct-fill.high{background:var(--pr);}
.pct-fill.mid{background:var(--sk);}
.pct-fill.low{background:var(--ab);}
.pct-text{font-size:12px;font-weight:700;min-width:40px;text-align:right;}
.pct-text.high{color:var(--pr);}
.pct-text.mid{color:var(--sk);}
.pct-text.low{color:var(--ab);}
.mini-stat{display:inline-flex;align-items:center;gap:3px;padding:2px 7px;border-radius:5px;font-size:11px;font-weight:700;}
.mp{background:rgba(34,197,94,.12);color:var(--pr);}
.ma{background:rgba(239,68,68,.12);color:var(--ab);}
.ms{background:rgba(245,158,11,.12);color:var(--sk);}
.me{background:rgba(167,139,250,.12);color:var(--ex);}
.ml{background:rgba(251,146,60,.12);color:var(--lt);}
.view-btn{font-size:11px;color:var(--ac);text-decoration:none;font-weight:600;}
.view-btn:hover{text-decoration:underline;}
.srch{background:var(--sc);border:1px solid var(--b);color:var(--t);border-radius:8px;padding:9px 14px;font-size:13px;outline:none;width:260px;}
.srch:focus{border-color:var(--ac);}
</style>

<div class="cs">
    <div class="cs-header">
        <div>
            <h1>Class <span>Summary</span></h1>
            <div style="font-size:13px;color:var(--m);margin-top:2px;">
                {{ $class?->schoolclass }} {{ $class?->arms?->arm }} · {{ $term?->term }} · {{ $session?->session }}
            </div>
        </div>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <input type="text" class="srch" id="srch" placeholder="Search students…">
            <a href="{{ route('attendance.register', [$classId, $termId, $sessionId]) }}" class="back-btn">← Mark Attendance</a>
        </div>
    </div>

    @php
        $totP  = $summaries->sum('days_present');
        $totA  = $summaries->sum('days_absent');
        $totS  = $summaries->sum('days_sick_leave');
        $avgPct= $summaries->count() > 0 ? round($summaries->avg('attendance_percentage'), 1) : 0;
        $totalDays = $setting?->totalSchoolDays() ?? 0;
    @endphp

    <div class="stats-row">
        <div class="stat-c"><div class="v" style="color:var(--ac);">{{ $summaries->count() }}</div><div class="l">Students</div></div>
        <div class="stat-c"><div class="v" style="color:var(--ac);">{{ $totalDays }}</div><div class="l">School Days</div></div>
        <div class="stat-c"><div class="v" style="color:var(--pr);">{{ $totP }}</div><div class="l">Total Present</div></div>
        <div class="stat-c"><div class="v" style="color:var(--ab);">{{ $totA }}</div><div class="l">Total Absent</div></div>
        <div class="stat-c"><div class="v" style="color:var(--sk);">{{ $totS }}</div><div class="l">Sick Leave</div></div>
        <div class="stat-c"><div class="v {{ $avgPct >= 80 ? 'text-success' : ($avgPct >= 60 ? '' : 'text-danger') }}" style="color:{{ $avgPct >= 80 ? 'var(--pr)' : ($avgPct >= 60 ? 'var(--sk)' : 'var(--ab)') }};">{{ $avgPct }}%</div><div class="l">Class Avg</div></div>
    </div>

    <div class="tbl-wrap">
        <table class="cs-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Sick</th>
                    <th>Excused</th>
                    <th>Late</th>
                    <th>Attendance</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="summaryRows">
            @forelse($summaries as $i => $s)
                @php
                    $pct = (float)$s->attendance_percentage;
                    $cls = $pct >= 80 ? 'high' : ($pct >= 60 ? 'mid' : 'low');
                    $img = $s->picture ? asset('storage/student_avatars/'.basename($s->picture)) : asset('storage/student_avatars/unnamed.jpg');
                @endphp
                <tr data-name="{{ strtolower($s->lname.' '.$s->fname.' '.$s->admissionno) }}">
                    <td style="color:var(--m);">{{ $i+1 }}</td>
                    <td>
                        <div class="stu-cell">
                            <img src="{{ $img }}" onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'">
                            <div><div class="stu-name">{{ $s->lname }} {{ $s->fname }}</div><div class="stu-adm">{{ $s->admissionno }}</div></div>
                        </div>
                    </td>
                    <td><span class="mini-stat mp">{{ $s->days_present }}</span></td>
                    <td><span class="mini-stat ma">{{ $s->days_absent }}</span></td>
                    <td><span class="mini-stat ms">{{ $s->days_sick_leave }}</span></td>
                    <td><span class="mini-stat me">{{ $s->days_excused }}</span></td>
                    <td><span class="mini-stat ml">{{ $s->days_late }}</span></td>
                    <td>
                        <div class="bar-wrap">
                            <div class="pct-bar"><div class="pct-fill {{ $cls }}" style="width:{{ $pct }}%"></div></div>
                            <span class="pct-text {{ $cls }}">{{ $pct }}%</span>
                        </div>
                    </td>
                    <td>
                        <a href="{{ route('attendance.student-report', [$s->student_id, $classId, $termId, $sessionId]) }}" class="view-btn">Details →</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" style="text-align:center;color:var(--m);padding:28px;">No attendance data recorded yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('srch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#summaryRows tr[data-name]').forEach(r => {
        r.style.display = (!q || r.dataset.name.includes(q)) ? '' : 'none';
    });
});
</script>
@endsection


{{-- ============================================================
     File: resources/views/attendance/teacher/my-classes.blade.php
     ============================================================ --}}
@extends('layouts.master')
@section('content')
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--s:#0f172a;--sf:#1e293b;--sc:#263348;--b:rgba(148,163,184,.15);--t:#e2e8f0;--m:#94a3b8;--ac:#38bdf8;--r:12px;}
.mc{background:var(--s);min-height:100vh;padding:28px;color:var(--t);font-family:'DM Sans',sans-serif;}
.mc h1{font-size:1.6rem;font-weight:800;color:#fff;margin-bottom:6px;}
.mc h1 span{color:var(--ac);}
.cards-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px;margin-top:28px;}
.cls-card{background:var(--sf);border:1px solid var(--b);border-radius:var(--r);padding:22px;transition:all .2s;cursor:pointer;}
.cls-card:hover{border-color:var(--ac);transform:translateY(-2px);box-shadow:0 8px 30px rgba(0,0,0,.3);}
.cls-icon{width:48px;height:48px;border-radius:10px;background:rgba(56,189,248,.1);border:1px solid rgba(56,189,248,.2);display:flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:14px;}
.cls-name{font-size:1.15rem;font-weight:800;color:#fff;margin-bottom:4px;}
.cls-meta{font-size:12px;color:var(--m);}
.cls-actions{display:flex;gap:8px;margin-top:16px;}
.cls-btn{flex:1;padding:8px;border-radius:8px;font-size:12px;font-weight:700;text-align:center;text-decoration:none;transition:.18s;}
.cls-btn-primary{background:var(--ac);color:#0f172a;}
.cls-btn-primary:hover{background:#7dd3fc;}
.cls-btn-outline{background:transparent;border:1px solid var(--b);color:var(--t);}
.cls-btn-outline:hover{border-color:var(--ac);color:var(--ac);}
.empty{text-align:center;padding:60px 20px;color:var(--m);}
.empty h3{color:var(--t);margin-bottom:8px;}
</style>
<div class="mc">
    <h1>My Classes – <span>Attendance</span></h1>
    <p style="color:var(--m);font-size:14px;">Select a class to mark or review attendance.</p>
    @if($classes->isNotEmpty())
    <div class="cards-grid">
        @foreach($classes as $cls)
        <div class="cls-card">
            <div class="cls-icon">🏫</div>
            <div class="cls-name">{{ $cls->schoolclass }} {{ $cls->arm }}</div>
            <div class="cls-meta">{{ $cls->term }} · {{ $cls->session }}</div>
            <div class="cls-actions">
                <a href="{{ route('attendance.register', [$cls->schoolclassid, $cls->termid, $cls->sessionid]) }}"
                   class="cls-btn cls-btn-primary">✔ Mark Attendance</a>
                <a href="{{ route('attendance.class-summary', [$cls->schoolclassid, $cls->termid, $cls->sessionid]) }}"
                   class="cls-btn cls-btn-outline">📊 Summary</a>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty">
        <div style="font-size:48px;margin-bottom:12px;">📚</div>
        <h3>No classes assigned</h3>
        <p>You have no classes assigned for the current session.</p>
    </div>
    @endif
</div>
@endsection
