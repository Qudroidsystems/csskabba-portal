<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 18px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color:#1E293B; }
    .header { text-align:center; margin-bottom: 12px; }
    .header h1 { font-size: 16px; margin: 0 0 2px; }
    .header p { margin: 0; color:#64748B; font-size:11px; }
    .class-page { page-break-after: always; }
    .class-page:last-child { page-break-after: auto; }
    .class-title { background:#1565C0; color:#fff; padding:6px 10px; font-size:13px; font-weight:bold; border-radius:4px; margin-bottom:8px; }
    table.grid { width:100%; border-collapse: collapse; }
    table.grid th, table.grid td { border:1px solid #CBD5E1; padding:4px; text-align:center; vertical-align:middle; }
    table.grid th { color:#fff; font-size:9px; text-transform:uppercase; }
    table.grid td.period-col { background:#F8FAFC; text-align:left; font-weight:bold; white-space:nowrap; }
    .subject { font-weight:bold; font-size:9.5px; }
    .teacher { font-size:8.5px; color:#475569; }
    .room { font-size:8px; color:#94A3B8; }
    .free { color:#CBD5E1; font-size:9px; }
    .break-cell { background:#FFFBEB; color:#D97706; font-weight:bold; font-size:9px; }
</style>
</head>
<body>

<div class="header">
    <h1>{{ $schoolInfo->school_name ?? 'School' }} — Whole School Timetable</h1>
    <p>{{ $sessionName }} · {{ $termName }} · Generated {{ $generatedAt }}</p>
</div>

@foreach ($allTimetables as $tt)
<div class="class-page">
    <div class="class-title">{{ $tt['class_name'] }}</div>

    @if ($orientation === 'vertical')
        {{-- Days as rows, periods as columns --}}
        <table class="grid">
            <thead>
                <tr>
                    <th style="background:#1E293B">Day</th>
                    @foreach ($tt['periods'] as $period)
                        <th style="background:#1565C0">
                            {{ $period->name }}<br>
                            <span style="font-weight:normal">{{ substr($period->start_time,0,5) }}–{{ substr($period->end_time,0,5) }}</span>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($tt['days'] as $day)
                <tr>
                    <td class="period-col" style="background:{{ $dayColors[$day] ?? '#1E293B' }};color:#fff">{{ $day }}</td>
                    @foreach ($tt['periods'] as $period)
                        @php
                            $meta    = $tt['day_meta'][$day][$period->id] ?? null;
                            $isBreak = in_array($period->type, ['short_break','long_break','assembly'])
                                       && ($meta['effective_type'] ?? $period->type) !== 'lesson';
                            $slot    = $tt['grid'][$period->id][$day] ?? null;
                        @endphp
                        @if ($isBreak)
                            <td class="break-cell">{{ ucfirst(str_replace('_',' ',$period->type)) }}</td>
                        @elseif (!$meta || !($meta['applicable'] ?? true))
                            <td>—</td>
                        @elseif (!$slot || $slot['is_free'])
                            <td class="free">Free</td>
                        @else
                            <td>
                                <div class="subject">{{ $slot['subject'] }}</div>
                                @if($slot['teacher'])<div class="teacher">{{ $slot['teacher'] }}</div>@endif
                                @if($slot['room'])<div class="room">{{ $slot['room'] }}</div>@endif
                            </td>
                        @endif
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        {{-- Horizontal (default): periods as rows, days as columns --}}
        <table class="grid">
            <thead>
                <tr>
                    <th style="background:#1E293B">Period</th>
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
                        <span style="font-weight:normal;color:#94A3B8">{{ substr($period->start_time,0,5) }}–{{ substr($period->end_time,0,5) }}</span>
                    </td>
                    @foreach ($tt['days'] as $day)
                        @php
                            $meta    = $tt['day_meta'][$day][$period->id] ?? null;
                            $isBreak = in_array($period->type, ['short_break','long_break','assembly'])
                                       && ($meta['effective_type'] ?? $period->type) !== 'lesson';
                            $slot    = $tt['grid'][$period->id][$day] ?? null;
                        @endphp
                        @if ($isBreak)
                            <td class="break-cell">{{ ucfirst(str_replace('_',' ',$period->type)) }}</td>
                        @elseif (!$meta || !($meta['applicable'] ?? true))
                            <td>—</td>
                        @elseif (!$slot || $slot['is_free'])
                            <td class="free">Free</td>
                        @else
                            <td>
                                <div class="subject">{{ $slot['subject'] }}</div>
                                @if($slot['teacher'])<div class="teacher">{{ $slot['teacher'] }}</div>@endif
                                @if($slot['room'])<div class="room">{{ $slot['room'] }}</div>@endif
                            </td>
                        @endif
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endforeach

</body>
</html>