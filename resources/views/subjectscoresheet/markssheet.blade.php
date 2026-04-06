<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Marks Sheet</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; padding: 18px; font-size: 11px; background: #fff; color: #222; }
        .header { text-align: center; padding-bottom: 14px; border-bottom: 2px solid #1a3c6e; margin-bottom: 14px; }
        .school-logo { width: 70px; height: auto; margin-bottom: 6px; }
        .school-name { font-size: 20px; font-weight: 700; color: #1a3c6e; text-transform: uppercase; letter-spacing: 1px; }
        .school-details { font-size: 10px; color: #555; margin: 2px 0; }
        .doc-title { font-size: 15px; font-weight: bold; text-align: center; color: #1a3c6e; text-transform: uppercase;
            letter-spacing: 2px; margin: 12px 0 10px; border: 2px solid #1a3c6e; display: inline-block;
            padding: 4px 20px; border-radius: 4px; }
        .doc-title-wrap { text-align: center; }
        .class-info { display: table; width: 100%; background: #f0f4fa; border: 1px solid #c5d3e8;
            padding: 8px 12px; margin: 10px 0; border-radius: 6px; font-size: 10.5px; }
        .info-row { display: table-row; }
        .info-cell { display: table-cell; padding: 2px 12px 2px 0; }
        .info-label { font-weight: 700; color: #1a3c6e; }
        .instructions { background: #fffbe6; border-left: 4px solid #f59e0b; padding: 8px 12px;
            margin-bottom: 14px; border-radius: 0 4px 4px 0; font-size: 10px; }
        .instructions strong { color: #b45309; display: block; margin-bottom: 4px; font-size: 11px; }
        .instructions li { margin-bottom: 2px; }
        table.marks { width: 100%; border-collapse: collapse; font-size: 10.5px; }
        .marks th, .marks td { border: 1px solid #aab8cc; padding: 5px 4px; text-align: center; vertical-align: middle; }
        .marks thead tr { background: #1a3c6e; color: #fff; }
        .marks thead th { font-weight: 600; font-size: 10px; letter-spacing: 0.3px; }
        .marks tbody tr:nth-child(even) { background: #f5f8fd; }
        .marks td.name-col { text-align: left; padding-left: 7px; min-width: 140px; }
        .marks td.score-col { min-width: 45px; }
        .marks tfoot td { background: #e8f0fe; font-weight: 700; font-size: 10px; }
        .footer { display: flex; justify-content: space-around; margin-top: 36px; }
        .sig { text-align: center; width: 150px; }
        .sig-line { border-top: 1px solid #333; margin-top: 36px; padding-top: 4px; font-size: 10px; }
        .badge-vetted { background: #dcfce7; color: #166534; border-radius: 3px; padding: 1px 4px; font-size: 9px; }
        .page-break { page-break-before: always; }
        @media print { body { font-size: 10px; } }
    </style>
</head>
<body>

{{-- ── Header ── --}}
<div class="header">
    @if($school && $school->school_logo)
        <img src="{{ $school->logo_url }}" alt="Logo" class="school-logo">
    @endif
    <div class="school-name">{{ $school->school_name ?? 'School Name' }}</div>
    @if($school)
        @if($school->school_address)<div class="school-details">{{ $school->school_address }}</div>@endif
        @if($school->school_phone)<div class="school-details">Tel: {{ $school->school_phone }}</div>@endif
        @if($school->school_email)<div class="school-details">Email: {{ $school->school_email }}</div>@endif
        @if($school->school_motto)<div class="school-details"><em>"{{ $school->school_motto }}"</em></div>@endif
    @endif
</div>

<div class="doc-title-wrap"><span class="doc-title">Student Marks Sheet</span></div>

@if($classInfo)
<div class="class-info">
    <div class="info-row">
        <div class="info-cell"><span class="info-label">Subject:</span> {{ $classInfo->subject }} ({{ $classInfo->subject_code }})</div>
        <div class="info-cell"><span class="info-label">Class:</span> {{ $classInfo->schoolclass }} {{ $classInfo->arm }}</div>
        <div class="info-cell"><span class="info-label">Teacher:</span> {{ $classInfo->teacher_name }}</div>
        <div class="info-cell"><span class="info-label">Term:</span> {{ $classInfo->term }}</div>
        <div class="info-cell"><span class="info-label">Session:</span> {{ $classInfo->session }}</div>
        <div class="info-cell"><span class="info-label">Date:</span> {{ date('d M Y') }}</div>
    </div>
</div>

<div class="instructions">
    <strong>Instructions for Teachers:</strong>
    <ul>
        <li>Fill in all scores clearly. Use only blue or black ink.</li>
        <li>Assessment columns and maximum scores are listed in the table header.</li>
        @if($assessments->isNotEmpty())
            <li>
                Max scores:
                @foreach($assessments as $a)
                    <strong>{{ $a->name }}</strong> ({{ $a->max_score }}){{ !$loop->last ? ', ' : '' }}
                @endforeach
                &mdash; <strong>Total</strong> ({{ $assessments->sum('max_score') }})
            </li>
        @endif
        <li>Sign and submit to the Academic Office after completion.</li>
    </ul>
</div>
@endif

<table class="marks">
    <thead>
        <tr>
            <th style="width:30px;">#</th>
            <th style="min-width:90px;">Adm. No</th>
            <th style="min-width:150px;">Student Name</th>
            @foreach($assessments as $assessment)
                <th class="score-col">
                    {{ $assessment->name }}<br>
                    <small style="font-weight:normal;font-size:9px;">({{ $assessment->max_score }})</small>
                </th>
            @endforeach
            <th class="score-col" style="background:#163275;">
                Total<br>
                <small style="font-weight:normal;font-size:9px;">({{ $assessments->sum('max_score') }})</small>
            </th>
        </tr>
    </thead>
    <tbody>
        @forelse($broadsheets as $index => $student)
            @php
                $rowTotal = 0;
                foreach ($assessments as $a) {
                    $scoreObj = $student->assessmentScores->where('assessment_id', $a->id)->first();
                    $rowTotal += $scoreObj ? $scoreObj->score : 0;
                }
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $student->admissionno ?? '-' }}</td>
                <td class="name-col">
                    <strong>{{ $student->lname ?? '' }}</strong>
                    {{ $student->fname ?? '' }}
                    {{ $student->mname ?? '' }}
                </td>
                @foreach($assessments as $assessment)
                    {{-- Blank cell — teacher fills in score on paper --}}
                    <td class="score-col"></td>
                @endforeach
                {{-- Blank total --}}
                <td class="score-col"></td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ 3 + $assessments->count() + 1 }}" style="text-align:center;padding:12px;">No students found.</td>
            </tr>
        @endforelse
    </tbody>
    @if($broadsheets->isNotEmpty())
    <tfoot>
        <tr>
            <td colspan="3" style="text-align:right;font-style:italic;">Total Students: {{ $broadsheets->count() }}</td>
            @foreach($assessments as $assessment)
                <td></td>
            @endforeach
            <td></td>
        </tr>
    </tfoot>
    @endif
</table>

<div class="footer">
    <div class="sig"><div class="sig-line">Subject Teacher</div></div>
    <div class="sig"><div class="sig-line">H.O.D</div></div>
    <div class="sig"><div class="sig-line">Principal</div></div>
    <div class="sig"><div class="sig-line">Date</div></div>
</div>

</body>
</html>
