@extends('layouts.master')
@section('content')
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--s:#0f172a;--sf:#1e293b;--sc:#263348;--b:rgba(148,163,184,.15);--t:#e2e8f0;--m:#94a3b8;--ac:#38bdf8;--pr:#22c55e;--ab:#ef4444;--sk:#f59e0b;--ex:#a78bfa;--lt:#fb923c;--r:12px;}
.sr{background:var(--s);min-height:100vh;padding:24px;color:var(--t);font-family:'DM Sans',sans-serif;}

/* Header */
.sr-header{display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:28px;}
.sr-header h1{font-size:1.45rem;font-weight:800;color:#fff;margin:0;}
.sr-header h1 span{color:var(--ac);}
.back-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;background:rgba(148,163,184,.1);border:1px solid var(--b);color:var(--t);text-decoration:none;font-size:13px;font-weight:600;transition:.18s;}
.back-btn:hover{border-color:var(--ac);color:var(--ac);}

/* Student card */
.stu-card{background:var(--sf);border:1px solid var(--b);border-radius:var(--r);padding:22px;display:flex;align-items:center;gap:20px;margin-bottom:24px;flex-wrap:wrap;}
.stu-card img{width:72px;height:72px;border-radius:50%;object-fit:cover;border:3px solid rgba(56,189,248,.3);}
.stu-info h2{font-size:1.2rem;font-weight:800;color:#fff;margin:0 0 4px;}
.stu-info .meta{font-size:13px;color:var(--m);}
.stu-info .badges{display:flex;gap:8px;margin-top:8px;flex-wrap:wrap;}
.badge-pill{padding:3px 12px;border-radius:99px;font-size:11px;font-weight:700;}
.bp-term{background:rgba(56,189,248,.12);color:var(--ac);border:1px solid rgba(56,189,248,.25);}
.bp-sess{background:rgba(167,139,250,.12);color:var(--ex);border:1px solid rgba(167,139,250,.25);}
.bp-cls{background:rgba(34,197,94,.12);color:var(--pr);border:1px solid rgba(34,197,94,.25);}

/* Summary cards */
.sum-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:12px;margin-bottom:24px;}
.sum-card{background:var(--sf);border:1px solid var(--b);border-radius:var(--r);padding:16px;text-align:center;}
.sum-card .v{font-size:2rem;font-weight:800;line-height:1;}
.sum-card .l{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--m);margin-top:4px;}

/* Attendance bar */
.att-bar-wrap{background:var(--sf);border:1px solid var(--b);border-radius:var(--r);padding:20px;margin-bottom:24px;}
.att-bar-wrap h3{font-size:14px;font-weight:700;color:#fff;margin-bottom:14px;}
.stacked-bar{height:12px;border-radius:99px;overflow:hidden;display:flex;background:var(--sc);}
.stacked-bar div{height:100%;transition:width .5s ease;}
.bar-legend{display:flex;gap:14px;flex-wrap:wrap;margin-top:10px;}
.bl-item{display:flex;align-items:center;gap:5px;font-size:12px;color:var(--m);}
.bl-dot{width:10px;height:10px;border-radius:3px;}

/* Timeline table */
.tl-wrap{background:var(--sf);border:1px solid var(--b);border-radius:var(--r);overflow:hidden;}
.tl-wrap h3{font-size:14px;font-weight:700;color:#fff;padding:16px 20px;border-bottom:1px solid var(--b);margin:0;display:flex;align-items:center;justify-content:space-between;}
.tl-table{width:100%;border-collapse:collapse;}
.tl-table th{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--m);padding:10px 16px;text-align:left;border-bottom:1px solid var(--b);}
.tl-table td{padding:10px 16px;font-size:13px;border-bottom:1px solid var(--b);}
.tl-table tr:last-child td{border-bottom:none;}
.tl-table tr:hover td{background:rgba(255,255,255,.02);}
.status-badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:99px;font-size:12px;font-weight:700;}
.sb-present {background:rgba(34,197,94,.12); color:var(--pr);}
.sb-absent  {background:rgba(239,68,68,.12); color:var(--ab);}
.sb-sick_leave{background:rgba(245,158,11,.12);color:var(--sk);}
.sb-excused {background:rgba(167,139,250,.12);color:var(--ex);}
.sb-late    {background:rgba(251,146,60,.12); color:var(--lt);}
.period-pill{padding:2px 8px;border-radius:5px;font-size:11px;font-weight:600;}
.pp-morning{background:rgba(56,189,248,.1);color:var(--ac);}
.pp-afternoon{background:rgba(167,139,250,.1);color:var(--ex);}
.empty-state{text-align:center;padding:40px 20px;color:var(--m);}
.empty-state .ei{font-size:40px;margin-bottom:10px;}

/* Print */
@media print{
    .sr{background:#fff;color:#000;padding:12px;}
    .back-btn,.tl-wrap h3 button{display:none;}
    .stu-card,.tl-wrap,.sum-card,.att-bar-wrap{border:1px solid #ddd;}
}
</style>

<div class="sr">
    {{-- Header --}}
    <div class="sr-header">
        <div>
            <h1>Student <span>Attendance Report</span></h1>
            <div style="font-size:13px;color:var(--m);margin-top:2px;">
                {{ $class?->schoolclass }} {{ $class?->arms?->arm }} · {{ $term?->term }} · {{ $session?->session }}
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button onclick="window.print()" class="back-btn">🖨 Print</button>
            <a href="{{ route('attendance.class-summary', [$classId, $termId, $sessionId]) }}" class="back-btn">← Class Summary</a>
            <a href="{{ route('attendance.register', [$classId, $termId, $sessionId]) }}" class="back-btn">✔ Register</a>
        </div>
    </div>

    {{-- Student card --}}
    @if($student)
    <div class="stu-card">
        @php
            $img = $student->picture
                ? asset('storage/student_avatars/'.basename($student->picture))
                : asset('storage/student_avatars/unnamed.jpg');
        @endphp
        <img src="{{ $img }}" onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'">
        <div class="stu-info">
            <h2>{{ $student->lname }} {{ $student->fname }} {{ $student->mname }}</h2>
            <div class="meta">Admission No: {{ $student->admissionno }}</div>
            <div class="badges">
                <span class="badge-pill bp-cls">{{ $class?->schoolclass }} {{ $class?->arms?->arm }}</span>
                <span class="badge-pill bp-term">{{ $term?->term }}</span>
                <span class="badge-pill bp-sess">{{ $session?->session }}</span>
            </div>
        </div>
    </div>
    @endif

    {{-- Summary stats --}}
    @php
        $pct    = $summary ? (float) $summary->attendance_percentage : 0;
        $pctCls = $pct >= 80 ? 'var(--pr)' : ($pct >= 60 ? 'var(--sk)' : 'var(--ab)');
        $totalDays = $setting?->totalSchoolDays() ?? ($summary?->total_school_days ?? 0);
    @endphp
    <div class="sum-grid">
        <div class="sum-card"><div class="v" style="color:var(--ac);">{{ $totalDays }}</div><div class="l">School Days</div></div>
        <div class="sum-card"><div class="v" style="color:var(--pr);">{{ $summary?->days_present ?? 0 }}</div><div class="l">Present</div></div>
        <div class="sum-card"><div class="v" style="color:var(--ab);">{{ $summary?->days_absent ?? 0 }}</div><div class="l">Absent</div></div>
        <div class="sum-card"><div class="v" style="color:var(--sk);">{{ $summary?->days_sick_leave ?? 0 }}</div><div class="l">Sick Leave</div></div>
        <div class="sum-card"><div class="v" style="color:var(--ex);">{{ $summary?->days_excused ?? 0 }}</div><div class="l">Excused</div></div>
        <div class="sum-card"><div class="v" style="color:var(--lt);">{{ $summary?->days_late ?? 0 }}</div><div class="l">Late</div></div>
        <div class="sum-card"><div class="v" style="color:{{ $pctCls }};">{{ $pct }}%</div><div class="l">Attendance</div></div>
    </div>

    {{-- Visual bar --}}
    @if($summary && $totalDays > 0)
    <div class="att-bar-wrap">
        <h3>Attendance Breakdown</h3>
        @php
            $pr = round(($summary->days_present    / $totalDays) * 100, 1);
            $ab = round(($summary->days_absent      / $totalDays) * 100, 1);
            $sk = round(($summary->days_sick_leave  / $totalDays) * 100, 1);
            $ex = round(($summary->days_excused     / $totalDays) * 100, 1);
            $lt = round(($summary->days_late        / $totalDays) * 100, 1);
        @endphp
        <div class="stacked-bar">
            <div style="width:{{ $pr }}%;background:var(--pr);" title="Present {{ $pr }}%"></div>
            <div style="width:{{ $lt }}%;background:var(--lt);" title="Late {{ $lt }}%"></div>
            <div style="width:{{ $sk }}%;background:var(--sk);" title="Sick {{ $sk }}%"></div>
            <div style="width:{{ $ex }}%;background:var(--ex);" title="Excused {{ $ex }}%"></div>
            <div style="width:{{ $ab }}%;background:var(--ab);" title="Absent {{ $ab }}%"></div>
        </div>
        <div class="bar-legend">
            <div class="bl-item"><div class="bl-dot" style="background:var(--pr);"></div> Present ({{ $pr }}%)</div>
            <div class="bl-item"><div class="bl-dot" style="background:var(--lt);"></div> Late ({{ $lt }}%)</div>
            <div class="bl-item"><div class="bl-dot" style="background:var(--sk);"></div> Sick ({{ $sk }}%)</div>
            <div class="bl-item"><div class="bl-dot" style="background:var(--ex);"></div> Excused ({{ $ex }}%)</div>
            <div class="bl-item"><div class="bl-dot" style="background:var(--ab);"></div> Absent ({{ $ab }}%)</div>
        </div>
    </div>
    @endif

    {{-- Daily timeline --}}
    <div class="tl-wrap">
        <h3>
            Daily Attendance Log
            <span style="font-size:12px;color:var(--m);font-weight:400;">{{ $records->count() }} record(s)</span>
        </h3>
        @if($records->isNotEmpty())
        <div style="overflow-x:auto;">
            <table class="tl-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Period</th>
                        <th>Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($records as $i => $rec)
                <tr>
                    <td style="color:var(--m);">{{ $i + 1 }}</td>
                    <td><strong>{{ $rec->attendance_date->format('d M Y') }}</strong></td>
                    <td style="color:var(--m);">{{ $rec->attendance_date->format('l') }}</td>
                    <td>
                        <span class="period-pill pp-{{ $rec->period }}">{{ ucfirst($rec->period) }}</span>
                    </td>
                    <td>
                        <span class="status-badge sb-{{ $rec->status }}">
                            {{ \App\Models\StudentAttendance::statusIcon($rec->status) }}
                            {{ \App\Models\StudentAttendance::statusLabel($rec->status) }}
                        </span>
                    </td>
                    <td style="color:var(--m);font-size:12px;">{{ $rec->notes ?? '—' }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state">
            <div class="ei">📋</div>
            <p>No attendance records found for this student in the selected term.</p>
        </div>
        @endif
    </div>
</div>
@endsection
