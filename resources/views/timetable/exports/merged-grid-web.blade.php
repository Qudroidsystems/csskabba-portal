@extends('layouts.master')

@section('content')
<style>
:root { --mg-navy:#0f2342; --mg-teal:#0d9488; --mg-border:#e2e8f0; --mg-radius:14px; --mg-shadow:0 4px 16px rgba(15,35,66,.10); }
.mg-hero {
    background: linear-gradient(135deg, var(--mg-navy) 0%, #1e4a7e 55%, #0d9488 100%);
    border-radius: var(--mg-radius); padding: 26px 30px; margin-bottom: 20px; color:#fff;
    display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:14px;
}
.mg-hero h1 { font-size:21px; font-weight:700; margin:0 0 6px; }
.mg-hero p  { font-size:13px; opacity:.75; margin:0; }
.mg-print-btn { background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.3); color:#fff; border-radius:10px; padding:9px 18px; font-size:13px; font-weight:600; cursor:pointer; }
.mg-print-btn:hover { background:rgba(255,255,255,.28); }

.mg-legend { display:flex; flex-wrap:wrap; gap:6px; background:#fff; border:1px solid var(--mg-border); border-radius:10px; padding:12px 16px; margin-bottom:18px; }
.mg-legend-chip { border-radius:14px; padding:4px 12px; font-size:11px; font-weight:700; color:#fff; }

.mg-filter { display:flex; gap:10px; margin-bottom:14px; flex-wrap:wrap; }
.mg-filter select { border:1.5px solid var(--mg-border); border-radius:10px; padding:8px 12px; font-size:13px; min-width:200px; }

.mg-card { background:#fff; border:1px solid var(--mg-border); border-radius:var(--mg-radius); box-shadow:var(--mg-shadow); overflow:hidden; }
table.mg-grid { width:100%; border-collapse:collapse; font-size:12px; }
table.mg-grid th { background:#0f2342; color:#fff; padding:10px 6px; text-align:center; font-size:11px; text-transform:uppercase; }
table.mg-grid th.period-th { width:110px; }
table.mg-grid td { border:1px solid var(--mg-border); padding:6px; vertical-align:top; }
table.mg-grid td.period-col { background:#F8FAFC; text-align:left; font-weight:700; white-space:nowrap; }
.mg-ptime { font-weight:400; font-size:10.5px; color:#94a3b8; }
.mg-break { background:#FFFBEB; color:#d97706; font-weight:700; font-size:11px; text-align:center; }
.mg-free  { color:#cbd5e1; font-size:11px; text-align:center; }
.mg-na    { color:#e2e8f0; text-align:center; }

.mg-chip { border-radius:6px; padding:4px 8px; margin-bottom:4px; font-size:11px; line-height:1.3; transition:transform .15s ease; }
.mg-chip:hover { transform:translateX(2px); }
.mg-chip .cls { font-weight:700; }
.mg-chip .subj { font-weight:700; color:#0f2342; }
.mg-chip .tch { color:#64748b; font-size:10px; }
.mg-chip.dimmed { opacity:.15; }

@media print {
    .no-print { display:none !important; }
    .mg-card { box-shadow:none; }
}
</style>

<div class="main-content"><div class="page-content"><div class="container-fluid">

<div class="mg-hero">
    <div>
        <h1><i class="ri-layout-grid-line me-2"></i>Master Timetable — All Classes Merged</h1>
        <p>{{ $schoolInfo->school_name ?? 'School' }} · {{ $sessionName }} · {{ $termName }} · Generated {{ $generatedAt }}</p>
    </div>
    <button class="mg-print-btn no-print" onclick="window.print()"><i class="ri-printer-line me-1"></i>Print / Save PDF</button>
</div>

<div class="mg-legend no-print">
    <strong style="font-size:12px;color:#0f2342;margin-right:6px;">Classes:</strong>
    @foreach($classColors as $cls => $color)
        <span class="mg-legend-chip" data-cls="{{ $cls }}" style="background:{{ $color }}; cursor:pointer;" onclick="mgFilterClass('{{ $cls }}')">{{ $cls }}</span>
    @endforeach
</div>

<div class="mg-filter no-print">
    <select id="mgClassFilter" onchange="mgFilterClass(this.value)">
        <option value="">Show all classes</option>
        @foreach($classList as $cls)
            <option value="{{ $cls }}">{{ $cls }}</option>
        @endforeach
    </select>
</div>

<div class="mg-card">
    <div style="overflow-x:auto;">
        <table class="mg-grid">
            <thead>
                <tr>
                    <th class="period-th">Period</th>
                    @foreach($days as $day)
                        <th style="background:{{ $dayColors[$day] ?? '#1565C0' }}">{{ $day }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                <tr>
                    <td class="period-col">
                        {{ $row['label'] }}<br><span class="mg-ptime">{{ $row['time'] }}</span>
                    </td>
                    @foreach($days as $day)
                        @php $cell = $row['days'][$day] ?? ['entries'=>[],'is_break'=>false,'applicable'=>false]; @endphp
                        @if(!$cell['applicable'])
                            <td class="mg-na">—</td>
                        @elseif($cell['is_break'])
                            <td class="mg-break">☕ Break</td>
                        @elseif(empty($cell['entries']))
                            <td class="mg-free">Free</td>
                        @else
                            <td>
                                @foreach($cell['entries'] as $e)
                                    <div class="mg-chip" data-cls="{{ $e['class'] }}" style="background:{{ $e['color'] }}18;border-left:3px solid {{ $e['color'] }};">
                                        <span class="cls" style="color:{{ $e['color'] }};">{{ $e['class'] }}</span><br>
                                        <span class="subj">{{ $e['subject'] }}</span>
                                        @if($e['teacher'])<span class="tch"> · {{ $e['teacher'] }}</span>@endif
                                        @if($e['room'])<span class="tch"> · {{ $e['room'] }}</span>@endif
                                    </div>
                                @endforeach
                            </td>
                        @endif
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

</div></div></div>

<script>
function mgFilterClass(cls) {
    document.getElementById('mgClassFilter').value = cls;
    document.querySelectorAll('.mg-chip').forEach(chip => {
        chip.classList.toggle('dimmed', !!cls && chip.dataset.cls !== cls);
    });
}
</script>
@endsection