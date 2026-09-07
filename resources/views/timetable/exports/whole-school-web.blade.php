@extends('layouts.master')

@section('content')
<style>
:root {
    --tt-navy: #0f2342; --tt-teal: #0d9488; --tt-sky: #0ea5e9;
    --tt-muted: #64748b; --tt-border: #e2e8f0; --tt-radius: 14px;
    --tt-shadow: 0 4px 16px rgba(15,35,66,.10);
}
.ttw-hero {
    background: linear-gradient(135deg, var(--tt-navy) 0%, #1e4a7e 55%, #0d9488 100%);
    border-radius: var(--tt-radius); padding: 28px 32px; margin-bottom: 24px; color:#fff;
    display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px;
}
.ttw-hero h1 { font-size:22px; font-weight:700; margin:0 0 6px; }
.ttw-hero p  { font-size:13px; opacity:.75; margin:0; }
.ttw-hero .pills { display:flex; gap:8px; flex-wrap:wrap; margin-top:12px; }
.ttw-pill { background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2); border-radius:20px; padding:4px 12px; font-size:12px; font-weight:600; }
.ttw-print-btn { background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.3); color:#fff; border-radius:10px; padding:9px 18px; font-size:13px; font-weight:600; cursor:pointer; }
.ttw-print-btn:hover { background:rgba(255,255,255,.28); }

.ttw-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:22px; }
.ttw-stat { background:#fff; border:1px solid var(--tt-border); border-radius:var(--tt-radius); box-shadow:var(--tt-shadow); padding:16px 18px; }
.ttw-stat .v { font-size:26px; font-weight:700; color:var(--tt-navy); }
.ttw-stat .l { font-size:11px; color:var(--tt-muted); text-transform:uppercase; margin-top:4px; }

.ttw-toolbar { display:flex; gap:12px; align-items:center; margin-bottom:16px; flex-wrap:wrap; }
.ttw-search { position:relative; flex:1; min-width:220px; max-width:340px; }
.ttw-search input { width:100%; padding:9px 14px 9px 36px; border:1.5px solid var(--tt-border); border-radius:10px; font-size:13px; }
.ttw-search i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--tt-muted); }

.ttw-class-card { background:#fff; border:1px solid var(--tt-border); border-radius:var(--tt-radius); box-shadow:var(--tt-shadow); margin-bottom:22px; overflow:hidden; }
.ttw-class-card .hdr { background:linear-gradient(135deg,#1565C0,#0d9488); color:#fff; padding:14px 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; }
.ttw-class-card .hdr h5 { margin:0; font-size:15px; font-weight:700; }
.ttw-class-card .hdr .badges { display:flex; gap:6px; flex-wrap:wrap; }
.ttw-badge { background:rgba(255,255,255,.18); border-radius:14px; padding:3px 10px; font-size:11px; font-weight:600; }

table.ttw-grid { width:100%; border-collapse:collapse; font-size:12px; }
table.ttw-grid th { background:#1E293B; color:#fff; padding:8px 6px; text-align:center; font-size:11px; text-transform:uppercase; }
table.ttw-grid td { border:1px solid var(--tt-border); padding:6px; text-align:center; vertical-align:middle; }
table.ttw-grid td.period-col { background:#F8FAFC; text-align:left; font-weight:700; white-space:nowrap; }
.ttw-subject { font-weight:700; font-size:12px; color:var(--tt-navy); }
.ttw-teacher { font-size:10.5px; color:#475569; }
.ttw-room { font-size:10px; color:#94a3b8; }
.ttw-free { color:#cbd5e1; font-size:11px; }
.ttw-break { background:#FFFBEB; color:#d97706; font-weight:700; font-size:11px; }

@media print {
    .no-print { display:none !important; }
    .ttw-class-card { box-shadow:none; page-break-inside: avoid; margin-bottom: 14px; }
}
</style>

<div class="main-content"><div class="page-content"><div class="container-fluid">

<div class="ttw-hero">
    <div>
        <h1><i class="ri-school-line me-2"></i>{{ $schoolInfo->school_name ?? 'School' }} — Whole School Timetable</h1>
        <p>{{ $sessionName }} · {{ $termName }} · Generated {{ $generatedAt }}</p>
        <div class="pills">
            <span class="ttw-pill"><i class="ri-building-line me-1"></i>{{ $overallStats['total_classes'] ?? 0 }} classes</span>
            <span class="ttw-pill"><i class="ri-user-line me-1"></i>{{ $overallStats['total_teachers'] ?? 0 }} teachers involved</span>
        </div>
    </div>
    <button class="ttw-print-btn no-print" onclick="window.print()"><i class="ri-printer-line me-1"></i>Print / Save PDF</button>
</div>

<div class="ttw-stats">
    <div class="ttw-stat"><div class="v">{{ $overallStats['total_classes'] ?? 0 }}</div><div class="l">Classes</div></div>
    <div class="ttw-stat"><div class="v">{{ $overallStats['total_teachers'] ?? 0 }}</div><div class="l">Teachers Involved</div></div>
    <div class="ttw-stat"><div class="v">{{ $overallStats['avg_fill_rate'] ?? 0 }}%</div><div class="l">Avg Fill Rate</div></div>
    <div class="ttw-stat">
        <div class="v" style="color:{{ ($overallStats['total_conflicts'] ?? 0) > 0 ? '#dc2626' : '#16a34a' }}">
            {{ $overallStats['total_conflicts'] ?? 0 }}
        </div>
        <div class="l">Conflicts Detected</div>
    </div>
</div>

<div class="ttw-toolbar no-print">
    <div class="ttw-search">
        <i class="ri-search-line"></i>
        <input type="text" id="ttwSearch" placeholder="Filter by class name…">
    </div>
</div>

<div id="ttwClassList">
@foreach ($allTimetables as $tt)
    <div class="ttw-class-card" data-class-name="{{ strtolower($tt['class_name']) }}">
        <div class="hdr">
            <h5><i class="ri-team-line me-1"></i>{{ $tt['class_name'] }}</h5>
            <div class="badges">
                <span class="ttw-badge">{{ $tt['stats']['filled_slots'] }}/{{ $tt['stats']['total_slots'] }} filled ({{ $tt['stats']['fill_rate'] }}%)</span>
                <span class="ttw-badge">{{ $tt['stats']['subject_count'] }} subjects</span>
                <span class="ttw-badge">{{ $tt['stats']['teacher_count'] }} teachers</span>
                @if($tt['stats']['room_count'] > 0)
                    <span class="ttw-badge">{{ $tt['stats']['room_count'] }} rooms</span>
                @endif
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="ttw-grid">
                <thead>
                    <tr>
                        <th style="background:#0f2342;">Period</th>
                        @foreach ($tt['days'] as $day)
                            <th style="background:{{ $dayColors[$day] ?? '#1565C0' }}">{{ $day }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tt['periods'] as $period)
                    <tr>
                        <td class="period-col">
                            {{ $period->name }}<br>
                            <span style="font-weight:400;color:#94a3b8;font-size:10px;">{{ substr($period->start_time,0,5) }}–{{ substr($period->end_time,0,5) }}</span>
                        </td>
                        @foreach ($tt['days'] as $day)
                            @php
                                $meta    = $tt['day_meta'][$day][$period->id] ?? null;
                                $isBreak = in_array($period->type, ['short_break','long_break','assembly'])
                                           && ($meta['effective_type'] ?? $period->type) !== 'lesson';
                                $slot    = $tt['grid'][$period->id][$day] ?? null;
                            @endphp
                            @if ($isBreak)
                                <td class="ttw-break">{{ ucfirst(str_replace('_',' ',$period->type)) }}</td>
                            @elseif (!$meta || !($meta['applicable'] ?? true))
                                <td>—</td>
                            @elseif (!$slot || $slot['is_free'])
                                <td class="ttw-free">Free</td>
                            @else
                                <td>
                                    <div class="ttw-subject">{{ $slot['subject'] }}</div>
                                    @if($slot['teacher'])<div class="ttw-teacher">{{ $slot['teacher'] }}</div>@endif
                                    @if($slot['room'])<div class="ttw-room">{{ $slot['room'] }}</div>@endif
                                </td>
                            @endif
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach
</div>

</div></div></div>

<script>
document.getElementById('ttwSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('#ttwClassList .ttw-class-card').forEach(card => {
        card.style.display = !q || card.dataset.className.includes(q) ? '' : 'none';
    });
});
</script>
@endsection