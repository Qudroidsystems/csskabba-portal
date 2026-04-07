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
            padding: 18px;
            font-size: 11px;
            background: #fff;
            color: #222;
            margin: 0;
        }

        /* First page content */
        .first-page {
            page-break-after: avoid;
        }

        /* Table container */
        .table-container {
            margin-top: 0;
            position: relative;
            min-height: 100vh;
        }

        /* Wrapper for table content */
        .table-content {
            width: 100%;
        }

        /* Footer wrapper - ensures signatures stay at bottom */
        .footer-wrapper {
            margin-top: 50px;
            width: 100%;
            position: relative;
            bottom: 0;
        }

        .header {
            text-align: center;
            padding-bottom: 14px;
            border-bottom: 2px solid #1a3c6e;
            margin-bottom: 14px;
        }

        .school-logo {
            width: 70px;
            height: auto;
            margin-bottom: 6px;
        }

        .school-name {
            font-size: 20px;
            font-weight: 700;
            color: #1a3c6e;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .school-details {
            font-size: 10px;
            color: #555;
            margin: 2px 0;
        }

        .doc-title {
            font-size: 15px;
            font-weight: bold;
            text-align: center;
            color: #1a3c6e;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 12px 0 10px;
            border: 2px solid #1a3c6e;
            display: inline-block;
            padding: 4px 20px;
            border-radius: 4px;
        }

        .doc-title-wrap {
            text-align: center;
        }

        .class-info {
            display: flex;
            flex-wrap: wrap;
            width: 100%;
            background: #f0f4fa;
            border: 1px solid #c5d3e8;
            padding: 10px 15px;
            margin: 10px 0;
            border-radius: 6px;
            font-size: 10.5px;
            justify-content: space-between;
        }

        .info-item {
            flex: 1;
            white-space: nowrap;
            padding: 0 5px;
        }

        .info-label {
            font-weight: 700;
            color: #1a3c6e;
            margin-right: 5px;
        }

        .info-value {
            color: #333;
        }

        .instructions {
            background: #fffbe6;
            border-left: 4px solid #f59e0b;
            padding: 8px 12px;
            margin-bottom: 14px;
            border-radius: 0 4px 4px 0;
            font-size: 10px;
        }

        .instructions strong {
            color: #b45309;
            display: block;
            margin-bottom: 4px;
            font-size: 11px;
        }

        .instructions ul {
            margin-left: 20px;
        }

        .instructions li {
            margin-bottom: 2px;
        }

        /* Table Styles */
        table.marks {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
            margin-bottom: 0;
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
        }

        .marks tbody tr:nth-child(even) {
            background: #f5f8fd;
        }

        .marks td.name-col {
            text-align: left;
            padding-left: 7px;
            min-width: 140px;
        }

        .marks td.score-col {
            min-width: 45px;
        }

        .marks tfoot td {
            background: #e8f0fe;
            font-weight: 700;
            font-size: 10px;
            padding: 8px;
        }

        /* Footer Signatures - Horizontal Line */
        .signatures {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            width: 100%;
            margin: 60px 0 20px 0;
            page-break-inside: avoid;
        }

        .signature-item {
            text-align: center;
            flex: 1;
            margin: 0 8px;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 30px;
            padding-top: 5px;
            font-size: 10px;
            font-weight: 600;
        }

        .signature-details {
            margin-top: 8px;
            font-size: 9px;
            color: #555;
        }

        .signature-name {
            margin-top: 5px;
            font-size: 9px;
        }

        .badge-vetted {
            background: #dcfce7;
            color: #166534;
            border-radius: 3px;
            padding: 1px 4px;
            font-size: 9px;
        }

        /* Page break control */
        .page-break {
            page-break-before: always;
        }

        /* Print styles */
        @media print {
            body {
                font-size: 10px;
                margin: 0;
                padding: 10px;
            }

            .first-page {
                page-break-after: avoid;
            }

            .signatures {
                position: relative;
                bottom: 0;
                margin-top: 80px;
                page-break-inside: avoid;
            }

            .footer-wrapper {
                position: relative;
                bottom: 0;
            }
        }
    </style>
</head>
<body>

{{-- FIRST PAGE: Header, School Info, and Instructions --}}
<div class="first-page">

    {{-- Header --}}
    <div class="header">
        @if($school && $school->school_logo && file_exists(public_path('storage/' . $school->school_logo)))
            <img src="{{ public_path('storage/' . $school->school_logo) }}" alt="Logo" class="school-logo">
        @endif
        <div class="school-name">{{ $school->school_name ?? 'CLARET SECONDARY SCHOOL KABBA' }}</div>
        @if($school)
            @if($school->school_address)<div class="school-details">{{ $school->school_address }}</div>@endif
            @if($school->school_phone)<div class="school-details">Tel: {{ $school->school_phone }}</div>@endif
            @if($school->school_email)<div class="school-details">Email: {{ $school->school_email }}</div>@endif
            @if($school->school_motto)<div class="school-details"><em>"{{ $school->school_motto }}"</em></div>@endif
        @else
            <div class="school-details">No. 1, Claret Avenue, Iludun Quarters, Olle Road, Kabba, Kogi State, Nigeria</div>
            <div class="school-details">Tel: 08136663185 | Email: claretsecschools@yahoo.com</div>
            <div class="school-details"><em>"KNOWLEDGE AND VIRTUE"</em></div>
        @endif
    </div>

    <div class="doc-title-wrap">
        <span class="doc-title">STUDENT MARKS SHEET</span>
    </div>

    @if($classInfo)
    <div class="class-info">
        <div class="info-item"><span class="info-label">Subject:</span> <span class="info-value">{{ $classInfo->subject }} ({{ $classInfo->subject_code }})</span></div>
        <div class="info-item"><span class="info-label">Class:</span> <span class="info-value">{{ $classInfo->schoolclass }} {{ $classInfo->arm }}</span></div>
        <div class="info-item"><span class="info-label">Teacher:</span> <span class="info-value">{{ $teacherName ?? 'Staff ID: ' . ($staffId ?? 'N/A') }}</span></div>
        <div class="info-item"><span class="info-label">Term:</span> <span class="info-value">{{ $classInfo->term ?? 'First Term' }}</span></div>
        <div class="info-item"><span class="info-label">Session:</span> <span class="info-value">{{ $classInfo->session ?? '2025/2026' }}</span></div>
        <div class="info-item"><span class="info-label">Date:</span> <span class="info-value">{{ date('d M Y') }}</span></div>
    </div>

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
    @endif

</div>

{{-- TABLE CONTAINER --}}
<div class="table-container">
    <div class="table-content">
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
                            <td class="score-col"></td>
                        @endforeach
                        <td class="score-col"></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 3 + $assessments->count() + 1 }}" style="text-align:center;padding:12px;">
                            No students found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($broadsheets->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align:right;font-style:italic;">
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
    </div>

    {{-- SIGNATURES - HORIZONTAL LINE --}}
    <div class="footer-wrapper">
        <div class="signatures">
            <div class="signature-item">
                <div class="signature-line">Subject Teacher</div>
                <div class="signature-details">
                    <div>Name: _________________</div>
                    <div>Sign: _________________</div>
                    <div>Date: _________________</div>
                </div>
            </div>
            <div class="signature-item">
                <div class="signature-line">H.O.D</div>
                <div class="signature-details">
                    <div>Name: _________________</div>
                    <div>Sign: _________________</div>
                    <div>Date: _________________</div>
                </div>
            </div>
            <div class="signature-item">
                <div class="signature-line">Principal</div>
                <div class="signature-details">
                    <div>Name: _________________</div>
                    <div>Sign: _________________</div>
                    <div>Date: _________________</div>
                </div>
            </div>
            <div class="signature-item">
                <div class="signature-line">Date</div>
                <div class="signature-details">
                    <div>____/____/________</div>
                </div>
            </div>
        </div>
    </div>

</div>

</body>
</html>
