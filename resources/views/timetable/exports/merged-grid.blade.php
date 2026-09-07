<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 14px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color:#1E293B; }

    .school-header { border:2px solid #0f2342; border-radius:6px; overflow:hidden; margin-bottom:8px; }
    .school-header table { width:100%; border-collapse:collapse; }
    .school-header .logo-cell { width:56px; text-align:center; vertical-align:middle; padding:5px; }
    .school-header .logo-cell img { width:46px; height:46px; border-radius:50%; object-fit:contain; }
    .school-header-top { background:#0f2342; color:#fff; }
    .school-header-top .name { font-size:15px; font-weight:700; text-transform:uppercase; text-align:center; }
    .school-header-top .addr { font-size:8px; opacity:.8; text-align:center; }
    .school-header-bottom { background:#1565C0; color:#fff; text-align:center; padding:5px; font-size:11px; font-weight:700; letter-spacing:1px; }

    .legend { display:table; width:100%; margin-bottom:8px; border:1px solid #E2E8F0; border-radius:6px; padding:5px 8px; background:#F8FAFC; }
    .legend-chip { display:inline-block; padding:2px 8px; border-radius:10px; color:#fff; font-size:7.5px; font-weight:700; margin:1px 3px; }

    table.mgrid { width:100%; border-collapse:collapse; table-layout:fixed; }
    table.mgrid th { color:#fff; font-size:9px; text-transform:uppercase; padding:6px 4px; text-align:center; }
    table.mgrid th.period-th { background:#0f2342; width:80px; }
    table.mgrid td { border:1px solid #CBD5E1; padding:3px; vertical-align:top; }
    table.mgrid td.period-col { background:#F8FAFC; text-align:left; padding:5px; }
    .p-name { font-size:8.5px; font-weight:700; }
    .p-time { font-size:7px; color:#94A3B8; }
    .break-cell { background:#FFFBEB; color:#D97706; font-weight:700; font-size:8px; text-align:center; vertical-align:middle; }
    .free-cell { color:#CBD5E1; font-size:8px; text-align:center; vertical-align:middle; }
    .na-cell { color:#E2E8F0; text-align:center; }

    .chip { border-radius:4px; padding:2px 4px; margin-bottom:2px; font-size:7.5px; line-height:1.25; }
    .chip .cls { font-weight:700; color:#fff; }
    .chip .subj { font-weight:700; }
    .chip .tch { opacity:.85; }
</style>
</head>
<body>

<div class="school-header">
    <table>
        <tr class="school-header-top">
            <td class="logo-cell">
                @if(!empty($schoolInfo?->logo_base64))
                    <img src="{{ $schoolInfo->logo_base64 }}" alt="Logo">
                @endif
            </td>
            <td>
                <div class="name">{{ $schoolInfo->school_name ?? 'School' }}</div>
                @if(!empty($schoolInfo?->school_address))<div class="addr">{{ $schoolInfo->school_address }}</div>@endif
            </td>
            <td style="width:56px;"></td>
        </tr>
    </table>
    <div class="school-header-bottom">Master Timetable (All Classes) — {{ $sessionName }} · {{ $termName }}</div>
</div>

<div class="legend">
    <strong style="font-size:8px;">CLASSES:</strong>
    @foreach($classColors as $cls => $color)
        <span class="legend-chip" style="background:{{ $color }}">{{ $cls }}</span>
    @endforeach
</div>

<table class="mgrid">
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
                <div class="p-name">{{ $row['label'] }}</div>
                <div class="p-time">{{ $row['time'] }}</div>
            </td>
            @foreach($days as $day)
                @php $cell = $row['days'][$day] ?? ['entries'=>[],'is_break'=>false,'applicable'=>false]; @endphp
                @if(!$cell['applicable'])
                    <td class="na-cell">—</td>
                @elseif($cell['is_break'])
                    <td class="break-cell">☕ Break</td>
                @elseif(empty($cell['entries']))
                    <td class="free-cell">Free</td>
                @else
                    <td>
                        @foreach($cell['entries'] as $e)
                            <div class="chip" style="background:{{ $e['color'] }}18;border-left:3px solid {{ $e['color'] }};">
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

</body>
</html>