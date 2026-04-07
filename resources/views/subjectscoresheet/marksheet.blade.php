<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Marks Sheet</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            background: #fff;
            color: #222;
            margin: 0;
            padding: 0;
        }

        /* First Page - Header and Instructions */
        .first-page {
            page-break-after: avoid;
        }

        .header {
            text-align: center;
            padding: 20px 20px 14px 20px;
            border-bottom: 2px solid #1a3c6e;
            margin-bottom: 14px;
        }

        .school-logo {
            width: 80px;
            height: auto;
            margin-bottom: 6px;
        }

        .school-name {
            font-size: 22px;
            font-weight: 700;
            color: #1a3c6e;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .school-motto {
            font-size: 12px;
            font-style: italic;
            color: #b45309;
            margin: 5px 0;
            font-weight: 500;
        }

        .school-details {
            font-size: 10px;
            color: #555;
            margin: 2px 0;
            line-height: 1.4;
        }

        .doc-title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            color: #1a3c6e;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 15px 0;
            border: 2px solid #1a3c6e;
            display: inline-block;
            padding: 6px 25px;
            border-radius: 4px;
            background: #f0f4fa;
        }

        .doc-title-wrap {
            text-align: center;
            margin: 10px 0;
        }

        .class-info {
            width: 100%;
            background: #f0f4fa;
            border: 1px solid #c5d3e8;
            padding: 10px 15px;
            margin: 15px 0;
            border-radius: 6px;
            font-size: 11px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .info-item {
            padding: 3px 0;
        }

        .info-label {
            font-weight: 700;
            color: #1a3c6e;
            display: inline-block;
            min-width: 70px;
        }

        .info-value {
            color: #333;
        }

        .instructions {
            background: #fffbe6;
            border-left: 4px solid #f59e0b;
            padding: 12px 15px;
            margin: 15px 0 20px 0;
            border-radius: 0 6px 6px 0;
            font-size: 10.5px;
        }

        .instructions strong {
            color: #b45309;
            display: block;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .instructions ul {
            margin-left: 20px;
            list-style-type: disc;
        }

        .instructions li {
            margin-bottom: 4px;
            line-height: 1.4;
        }

        .assessment-max {
            display: inline-block;
            background: #e8f0fe;
            padding: 2px 8px;
            border-radius: 4px;
            margin: 5px 0;
        }

        /* Table Styles - Starts on new page */
        .table-container {
            page-break-before: always;
            margin-top: 10px;
        }

        table.marks {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
            margin-top: 10px;
        }

        .marks th, .marks td {
            border: 1px solid #aab8cc;
            padding: 6px 4px;
            text-align: center;
            vertical-align: middle;
        }

        .marks thead tr {
            background: #1a3c6e;
            color: #fff;
        }

        .marks thead th {
            font-weight: 600;
            font-size: 10px;
            letter-spacing: 0.3px;
            padding: 8px 4px;
        }

        .marks tbody tr:nth-child(even) {
            background: #f5f8fd;
        }

        .marks tbody tr:hover {
            background: #e8f0fe;
        }

        .marks td.name-col {
            text-align: left;
            padding-left: 8px;
            min-width: 160px;
        }

        .marks td.score-col {
            min-width: 50px;
        }

        .marks tfoot td {
            background: #e8f0fe;
            font-weight: 700;
            font-size: 10px;
            padding: 6px;
        }

        .footer {
            display: flex;
            justify-content: space-around;
            margin-top: 50px;
            margin-bottom: 30px;
        }

        .sig {
            text-align: center;
            width: 160px;
        }

        .sig-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 6px;
            font-size: 10px;
        }

        .page-break {
            page-break-before: always;
        }

        /* Page number */
        .page-number {
            text-align: center;
            font-size: 9px;
            color: #999;
            margin-top: 20px;
        }

        @media print {
            body {
                font-size: 10px;
                margin: 0;
                padding: 0;
            }

            .first-page {
                page-break-after: avoid;
            }

            .table-container {
                page-break-before: always;
            }

            .footer {
                position: fixed;
                bottom: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>

{{-- FIRST PAGE: Header and Instructions --}}
<div class="first-page">

    {{-- School Header --}}
    <div class="header">
        @if($school && $school->school_logo)
            <img src="{{ public_path('storage/' . $school->school_logo) }}" alt="Logo" class="school-logo">
        @else
            <div style="width:80px; height:80px; margin:0 auto 6px auto; background:#f0f0f0; border-radius:50%;"></div>
        @endif
        <div class="school-name">{{ $school->school_name ?? 'CLARET SECONDARY SCHOOL KABBA' }}</div>
        <div class="school-details">{{ $school->school_address ?? 'No. 1, Claret Avenue, Iludun Quarters, Olle Road, Kabba, Kogi State, Nigeria' }}</div>
        @if($school && $school->school_phone)
            <div class="school-details">Tel: {{ $school->school_phone }}</div>
        @else
            <div class="school-details">Tel: 08136663185</div>
        @endif
        @if($school && $school->school_email)
            <div class="school-details">Email: {{ $school->school_email }}</div>
        @else
            <div class="school-details">Email: claretsecschools@yahoo.com</div>
        @endif
        <div class="school-motto">"{{ $school->school_motto ?? 'KNOWLEDGE AND VIRTUE' }}"</div>
    </div>

    <div class="doc-title-wrap">
        <span class="doc-title">STUDENT MARKS SHEET</span>
    </div>

    {{-- Class Information --}}
    @if($classInfo)
    <div class="class-info">
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Subject:</span>
                <span class="info-value">{{ $classInfo->subject }} ({{ $classInfo->subject_code }})</span>
            </div>
            <div class="info-item">
                <span class="info-label">Class:</span>
                <span class="info-value">{{ $classInfo->schoolclass }} {{ $classInfo->arm }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Teacher:</span>
                <span class="info-value">Staff ID: {{ $classInfo->staff_id ?? '1' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Term:</span>
                <span class="info-value">{{ $classInfo->term ?? 'First Term' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Session:</span>
                <span class="info-value">{{ $classInfo->session ?? '2025/2026' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Date:</span>
                <span class="info-value">{{ date('d M Y') }}</span>
            </div>
        </div>
    </div>
    @endif

    {{-- Instructions --}}
    <div class="instructions">
        <strong>📋 Instructions for Teachers:</strong>
        <ul>
            <li>Fill in all scores clearly. Use only blue or black ink.</li>
            <li>Assessment columns and maximum scores are listed in the table header.</li>
            @if($assessments->isNotEmpty())
                <li>
                    Max scores:
                    @foreach($assessments as $a)
                        <strong>{{ $a->name }}</strong> ({{ number_format($a->max_score, 2) }}){{ !$loop->last ? ', ' : '' }}
                    @endforeach
                    &mdash; <strong>Total</strong> ({{ number_format($assessments->sum('max_score'), 2) }})
                </li>
            @endif
            <li>Sign and submit to the Academic Office after completion.</li>
        </ul>
    </div>

</div>

{{-- SECOND PAGE AND BEYOND: Table and Footer --}}
<div class="table-container">

    <table class="marks">
        <thead>
            <tr>
                <th style="width:35px;">S/N</th>
                <th style="min-width:100px;">Adm. No</th>
                <th style="min-width:160px;">Student Name</th>
                @foreach($assessments as $assessment)
                    <th class="score-col">
                        {{ $assessment->name }}<br>
                        <small style="font-weight:normal;font-size:9px;">({{ number_format($assessment->max_score, 2) }})</small>
                    </th>
                @endforeach
                <th class="score-col" style="background:#163275;">
                    Total<br>
                    <small style="font-weight:normal;font-size:9px;">({{ number_format($assessments->sum('max_score'), 2) }})</small>
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse($broadsheets as $index => $student)
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
                    <td colspan="{{ 3 + $assessments->count() + 1 }}" style="text-align:center;padding:15px;">
                        No students found.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($broadsheets->isNotEmpty())
        <tfoot>
            <tr>
                <td colspan="3" style="text-align:right; font-style:italic;">
                    Total Students: {{ $broadsheets->count() }}
                </td>
                @foreach($assessments as $assessment)
                    <td></td>
                @endforeach
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>

    {{-- Footer Signatures --}}
    <div class="footer">
        <div class="sig">
            <div class="sig-line">Subject Teacher</div>
            <div style="font-size:9px; margin-top:5px;">Name: _________________</div>
        </div>
        <div class="sig">
            <div class="sig-line">H.O.D</div>
            <div style="font-size:9px; margin-top:5px;">Name: _________________</div>
        </div>
        <div class="sig">
            <div class="sig-line">Principal</div>
            <div style="font-size:9px; margin-top:5px;">Name: _________________</div>
        </div>
        <div class="sig">
            <div class="sig-line">Date</div>
            <div style="font-size:9px; margin-top:5px;">____/____/________</div>
        </div>
    </div>

    <div class="page-number">
        Page {{-- Auto-generated by browser --}}
    </div>
</div>

</body>
</html>
