<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Terminal Report</title>
    <style>
        *{ margin:0; padding:0; box-sizing:border-box; }
        @page{
            size: A5 portrait;   /* Changed to A5 for better fit */
            margin: 5mm;
        }
        body{
            font-family:'Times New Roman', Times, serif;
            font-size:8.5px;
            line-height:1.3;
            color:#000;
        }
        .watermark-text{
            position:fixed; top:50%; left:50%; transform:translate(-50%,-50%) rotate(-30deg);
            font-size:48px; font-weight:900; color:rgba(220,38,38,0.07);
            pointer-events:none; z-index:0; text-transform:uppercase;
        }
        .student-section{
            border:2px double #000; position:relative; overflow:hidden; z-index:1;
            min-height: 190mm; background:white;
        }
        table{ width:100%; border-collapse:collapse; }
        th, td{ border:1px solid #000; padding:3px 2px; text-align:center; font-size:7.8px; }
        th{ background:#0d1a3d; color:white; font-weight:800; font-size:7px; }
        .subject-name{ text-align:left; font-weight:700; padding-left:5px; }
        .highlight-red{ color:#dc2626; font-weight:900; }
        .grade-A { color:#16a34a; font-weight:900; }
        .grade-B { color:#2563eb; font-weight:900; }
        .grade-C { color:#ca8a04; font-weight:900; }
        .grade-D { color:#ea580c; font-weight:900; }
        .grade-F { color:#dc2626; font-weight:900; }
        .pos-1 { background:gold; color:#000; font-weight:900; }
        .pos-2 { background:silver; color:#000; font-weight:900; }
        .pos-3 { background:#cd7f32; color:#fff; font-weight:900; }
    </style>
</head>
<body>
<div class="watermark-text">STUDENT COPY</div>

@php
$selectedColumns = $metadata['selected_columns'] ?? [];
$fullName = strtoupper(trim(
    ($student->lastname ?? '') . ' ' .
    ($student->firstname ?? '') . ' ' .
    ($student->othername ?? '')
));

$classVal = trim(($studentData['schoolclass']->schoolclass ?? '—') . ' ' . ($studentData['schoolclass']->arm_name ?? ''));
@endphp

@foreach ($allStudentData as $studentData)
@php
$schoolInfo = $studentData['schoolInfo'] ?? null;
$student = ($studentData['students'] ?? collect())->first();
$scores = $studentData['scores'] ?? collect();
$assessments = $studentData['assessments'] ?? collect();
$totals = $studentData['totals_summary'] ?? [];
$gpaData = $studentData['gpa_data'] ?? [];
$classNameWithArm = $classVal;
@endphp

<div class="student-section">

    {{-- School Header --}}
    <div style="background:#111827; color:white; padding:6px; text-align:center;">
        <strong style="font-size:11px;">{{ $schoolInfo->school_name ?? 'SCHOOL NAME' }}</strong><br>
        <small style="font-size:8px;">{{ $schoolInfo->school_motto ?? 'KNOWLEDGE AND VIRTUE' }}</small>
    </div>

    {{-- Student Info --}}
    <table style="margin:4px 6px; background:#f0f7ff; border:2px solid #2aa886;">
        <tr>
            <td><strong>NAME:</strong> {{ $fullName }}</td>
            <td><strong>CLASS:</strong> {{ $classNameWithArm }}</td>
            <td><strong>ADM NO:</strong> {{ $student->admissionNo ?? '—' }}</td>
        </tr>
        <tr>
            <td><strong>SESSION:</strong> {{ $metadata['session'] ?? '—' }}</td>
            <td><strong>TERM:</strong> {{ $metadata['term'] ?? '—' }}</td>
            <td><strong>GPA:</strong> {{ $gpaData['gpa'] ?? '-' }} | <strong>CGPA:</strong> {{ $gpaData['cgpa'] ?? '-' }}</td>
        </tr>
    </table>

    {{-- Result Table --}}
    <table style="margin:4px 6px;">
        <thead>
            <tr>
                @if(in_array('sn', $selectedColumns)) <th>S/N</th> @endif
                @if(in_array('name', $selectedColumns)) <th class="subject-name">Subject</th> @endif

                {{-- Assessments --}}
                @foreach ($assessments as $ass)
                    @if(in_array($ass->id, $selectedColumns))
                        <th>{{ $ass->name }}<br><small>({{ $ass->max_score }})</small></th>
                    @endif
                @endforeach

                {{-- TOTAL AFTER ASSESSMENTS --}}
                @if(in_array('total', $selectedColumns))
                    <th>Total</th>
                @endif

                @if(in_array('cum', $selectedColumns)) <th>Cum</th> @endif
                @if(in_array('grade', $selectedColumns)) <th>Grade</th> @endif
                @if(in_array('position', $selectedColumns)) <th>Class Pos (Cum)</th> @endif
                @if(in_array('position_total', $selectedColumns)) <th>Class Pos (Total)</th> @endif
                @if(in_array('arm_position', $selectedColumns)) <th>Arm Pos (Total)</th> @endif
                @if(in_array('arm_position_cum', $selectedColumns)) <th>Arm Pos (Cum)</th> @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($scores as $si => $score)
            @php
                $gradeClass = match(true){
                    str_starts_with(strtoupper($score->grade ?? ''), 'A') => 'grade-A',
                    str_starts_with(strtoupper($score->grade ?? ''), 'B') => 'grade-B',
                    str_starts_with(strtoupper($score->grade ?? ''), 'C') => 'grade-C',
                    str_starts_with(strtoupper($score->grade ?? ''), 'D') => 'grade-D',
                    default => 'grade-F'
                };
            @endphp
            <tr>
                @if(in_array('sn', $selectedColumns)) <td>{{ $si + 1 }}</td> @endif
                @if(in_array('name', $selectedColumns)) <td class="subject-name">{{ $score->subject_name }}</td> @endif

                @foreach ($assessments as $ass)
                    @if(in_array($ass->id, $selectedColumns))
                        @php $aScore = $score->assessment_scores->firstWhere('assessment_id', $ass->id)->score ?? 0; @endphp
                        <td @if($aScore < ($ass->max_score * 0.5)) class="highlight-red" @endif>
                            {{ $aScore ?: '-' }}
                        </td>
                    @endif
                @endforeach

                @if(in_array('total', $selectedColumns))
                    <td @if(($score->total ?? 0) < 50) class="highlight-red" @endif>
                        {{ number_format($score->total ?? 0, 1) }}
                    </td>
                @endif

                @if(in_array('cum', $selectedColumns)) <td>{{ number_format($score->cum ?? 0, 1) }}</td> @endif
                @if(in_array('grade', $selectedColumns)) <td class="{{ $gradeClass }}">{{ $score->grade }}</td> @endif
                @if(in_array('position', $selectedColumns)) <td>{{ $score->position_formatted ?? '-' }}</td> @endif
                @if(in_array('position_total', $selectedColumns)) <td>{{ $score->position_total_formatted ?? '-' }}</td> @endif
                @if(in_array('arm_position', $selectedColumns)) <td>{{ $score->arm_position_formatted ?? '-' }}</td> @endif
                @if(in_array('arm_position_cum', $selectedColumns)) <td>{{ $score->arm_position_cum_formatted ?? '-' }}</td> @endif
            </tr>
            @empty
            <tr><td colspan="20" style="padding:10px;">No data available</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Totals Summary --}}
    <div style="background:#0d1a3d; color:white; padding:6px; margin:4px 6px; text-align:center; font-weight:700;">
        TOTAL OBTAINED: <strong>{{ number_format($totals['obtained'] ?? 0, 1) }}</strong> &nbsp;&nbsp;
        OBTAINABLE: <strong>{{ $totals['obtainable'] ?? 0 }}</strong> &nbsp;&nbsp;
        PERCENTAGE: <strong>{{ number_format($totals['percentage'] ?? 0, 1) }}%</strong> &nbsp;&nbsp;
        | &nbsp;&nbsp; GPA: <strong>{{ $gpaData['gpa'] ?? 0 }}</strong>
    </div>

</div>
@endforeach
</body>
</html>
