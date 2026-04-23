{{-- resources/views/student/assessments/print-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Assessment Report - {{ $fullName ?? 'Student' }}</title>
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            background: #fff;
            color: #1a1a2e;
            font-size: 12px;
            line-height: 1.45;
            padding: 20px;
        }

        /* Main container */
        .report-container {
            max-width: 1100px;
            margin: 0 auto;
            background: #ffffff;
            position: relative;
        }

        /* WATERMARK */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            opacity: 0.12;
            pointer-events: none;
            z-index: 1000;
            white-space: nowrap;
            font-size: 58px;
            font-weight: 900;
            font-family: 'DejaVu Sans', 'Segoe UI', sans-serif;
            letter-spacing: 8px;
            color: #c0392b;
            text-transform: uppercase;
            width: 100%;
            text-align: center;
        }

        /* Header Section */
        .school-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #1e3a5f;
        }
        .school-name {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #0f1c35;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .school-motto {
            font-size: 10px;
            color: #5a6e8a;
            font-style: italic;
            margin-top: 4px;
        }
        .school-address {
            font-size: 9px;
            color: #6c757d;
        }

        /* Report Title */
        .report-title {
            text-align: center;
            margin: 15px 0 10px;
        }
        .report-title h2 {
            font-size: 18px;
            font-weight: 700;
            color: #1e3a5f;
            letter-spacing: 1px;
        }
        .report-title p {
            font-size: 12px;
            color: #2c3e66;
            font-weight: 500;
        }
        .term-badge {
            display: inline-block;
            background: #eef2f7;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            margin-top: 6px;
        }

        /* Student Info Card */
        .student-info {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 18px;
            margin: 20px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .info-left {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .info-item {
            font-size: 11px;
        }
        .info-label {
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.8px;
            margin-bottom: 2px;
        }
        .info-value {
            font-weight: 600;
            color: #0f1c35;
            font-size: 12px;
        }
        .student-photo {
            width: 60px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #f1f5f9;
        }

        /* Subjects Table */
        .subjects-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 10.5px;
        }
        .subjects-table th {
            background: #1e3a5f;
            color: white;
            padding: 10px 6px;
            font-weight: 600;
            text-align: center;
            border: 1px solid #2d4a7a;
        }
        .subjects-table td {
            border: 1px solid #d1d9e8;
            padding: 8px 5px;
            text-align: center;
            vertical-align: middle;
        }
        .subjects-table tbody tr:nth-child(even) {
            background-color: #f9fbfd;
        }
        .subject-name {
            font-weight: 600;
            text-align: left !important;
            color: #1e293b;
        }
        .assessment-breakdown {
            font-size: 9px;
            color: #3b4b6e;
            line-height: 1.3;
            text-align: left;
            max-width: 180px;
        }
        .grade-box {
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 12px;
            display: inline-block;
            min-width: 36px;
        }
        .grade-A1, .grade-A { background: #d4edda; color: #0e6b46; }
        .grade-B2, .grade-B3, .grade-B { background: #cce5ff; color: #1565c0; }
        .grade-C4, .grade-C5, .grade-C6, .grade-C { background: #fff3cd; color: #8a6000; }
        .grade-D7, .grade-D { background: #ffe5cc; color: #7a4200; }
        .grade-E8, .grade-F9, .grade-F { background: #f8d7da; color: #c0392b; }

        /* Summary Section */
        .summary-section {
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
        }
        .stats-box {
            background: #f1f5f9;
            border-radius: 12px;
            padding: 12px 20px;
            flex: 1;
            text-align: center;
            border: 1px solid #e2e8f0;
        }
        .stats-box h4 {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #475569;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }
        .stats-number {
            font-size: 22px;
            font-weight: 800;
            color: #0f1c35;
        }

        /* Footer Remarks */
        .remarks {
            margin-top: 30px;
            border-top: 1px solid #cbd5e1;
            padding-top: 15px;
            font-size: 10px;
            display: flex;
            justify-content: space-between;
        }
        .sign-line {
            margin-top: 8px;
            width: 180px;
            border-top: 1px dotted #94a3b8;
            padding-top: 5px;
            font-size: 9px;
            color: #5b6e8c;
        }

        /* Print optimization */
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            .watermark {
                opacity: 0.18;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            .subjects-table th {
                background: #1e3a5f !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .grade-box {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .fw-bold { font-weight: 700; }
        .mt-2 { margin-top: 8px; }
        .mb-2 { margin-bottom: 8px; }
    </style>
</head>
<body>

    {{-- WATERMARK --}}
    <div class="watermark">STUDENT COPY - NOT FOR OFFICIAL USE</div>

    <div class="report-container">

        {{-- SCHOOL HEADER --}}
        <div class="school-header">
            @if(!empty($logoBase64))
                <img src="{{ $logoBase64 }}" alt="School Logo" style="height: 65px; margin-bottom: 6px;">
            @endif
            <div class="school-name">{{ $schoolInfo->school_name ?? 'PREMIUM ACADEMY' }}</div>
            @if(!empty($schoolInfo->motto))
                <div class="school-motto">{{ $schoolInfo->motto }}</div>
            @endif
            <div class="school-address">
                {{ $schoolInfo->address ?? 'P.O. Box 123, Education City' }}
                @if(!empty($schoolInfo->email)) | {{ $schoolInfo->email }} @endif
            </div>
        </div>

        {{-- REPORT TITLE --}}
        <div class="report-title">
            <h2>TERM ASSESSMENT REPORT</h2>
            <p>Academic Session: {{ $sessionName ?? 'N/A' }} | Term: {{ $termName ?? 'N/A' }}</p>
            <div class="term-badge">{{ strtoupper($termName ?? '') }} TERM EXAMINATION</div>
        </div>

        {{-- STUDENT INFO CARD --}}
        <div class="student-info">
            <div class="info-left">
                <div class="info-item">
                    <div class="info-label">Student Name</div>
                    <div class="info-value">{{ $fullName ?? $student->firstname . ' ' . $student->lastname }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Admission No.</div>
                    <div class="info-value">{{ $student->admissionNo ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Class</div>
                    <div class="info-value">{{ $className ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Report Date</div>
                    <div class="info-value">{{ date('d M, Y') }}</div>
                </div>
            </div>
            @if(!empty($pictureBase64))
                <img src="{{ $pictureBase64 }}" class="student-photo" alt="Student Photo">
            @else
                <div class="student-photo" style="display:flex; align-items:center; justify-content:center; background:#eef2f7; color:#94a3b8;">📷</div>
            @endif
        </div>

        {{-- SUBJECTS TABLE --}}
        <table class="subjects-table" cellspacing="0">
            <thead>
                <tr>
                    <th width="22%">Subject</th>
                    <th width="28%">Assessment Breakdown</th>
                    <th width="8%">Total</th>
                    <th width="8%">Cum</th>
                    <th width="8%">Grade</th>
                    <th width="10%">GPA</th>
                    <th width="16%">Remark</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subjectsWithAssessments as $subject)
                    @php
                        $grade = $subject['grade'] ?? '-';
                        $gradeUp = strtoupper($grade);
                        $gradeClass = match(true) {
                            str_starts_with($gradeUp,'A') => 'grade-A1',
                            str_starts_with($gradeUp,'B') => 'grade-B2',
                            str_starts_with($gradeUp,'C') => 'grade-C4',
                            str_starts_with($gradeUp,'D') => 'grade-D7',
                            default => 'grade-F9',
                        };
                        $assessments = $subject['assessments'] ?? collect();
                        $assessText = '';
                        foreach($assessments as $a) {
                            $assessText .= $a['name'] . ': ' . number_format($a['score'],0) . '/' . $a['max_score'] . '  ';
                        }
                        $position = $subject['position'] ?? '-';
                    @endphp
                    <tr>
                        <td class="subject-name">{{ $subject['subject_name'] }}<br><span style="font-size:8px; color:#5b6e8c;">{{ $subject['subject_code'] ?? '' }}</span></td>
                        <td class="assessment-breakdown">{{ $assessText ?: '—' }}</td>
                        <td><strong>{{ number_format($subject['total'] ?? 0, 1) }}</strong></td>
                        <td><strong>{{ number_format($subject['cum'] ?? 0, 1) }}</strong></td>
                        <td><span class="grade-box {{ $gradeClass }}">{{ $grade }}</span></td>
                        <td>{{ number_format($subject['subject_gpa'] ?? 0, 1) }}</td>
                        <td style="font-size: 9px;">{{ $subject['remark'] ?? '—' }} @if($position !== '-') <br><strong>Pos:</strong> {{ $position }} @endif</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding:30px;">No subject records found for this term.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- PERFORMANCE SUMMARY --}}
        <div class="summary-section">
            <div class="stats-box">
                <h4>Academic Summary</h4>
                <div><span class="stats-number">{{ $subjectsWithAssessments->count() ?? 0 }}</span> Subjects</div>
                <div class="mt-2">Average Score: <strong>{{ number_format($overallProgress['average_cum'] ?? 0, 1) }}%</strong></div>
                <div>Total Obtainable: <strong>{{ $totalObtainable ?? 0 }}</strong> | Obtained: <strong>{{ number_format($totalObtained ?? 0, 1) }}</strong></div>
                <div>Overall Percentage: <strong>{{ number_format($percentage ?? 0, 1) }}%</strong></div>
            </div>
            <div class="stats-box">
                <h4>GPA & CGPA</h4>
                <div>Term GPA: <span class="stats-number">{{ number_format($overallProgress['gpa'] ?? 0, 2) }}</span></div>
                <div>Cumulative GPA (CGPA): <strong>{{ number_format($overallProgress['cgpa'] ?? 0, 2) }}</strong></div>
                <div class="mt-2">GPA Grade:
                    <span class="grade-box grade-A1">{{ $overallProgress['gpa_grade'] ?? 'F' }}</span>
                </div>
            </div>
            <div class="stats-box">
                <h4>Attendance / Conduct</h4>
                <div>Days Present: <strong>—</strong> / <strong>—</strong></div>
                <div>Conduct: <strong>Good</strong></div>
                <div class="mt-2">Next Term Begins: <strong>{{ \Carbon\Carbon::now()->addMonths(2)->format('d M, Y') }}</strong></div>
            </div>
        </div>

        {{-- REMARKS AND SIGNATURES --}}
        <div class="remarks">
            <div>
                <strong>Teacher's Remark:</strong><br>
                {{ $subjectsWithAssessments->first()['remark'] ?? 'Performed satisfactorily. Keep improving.' }}
            </div>
            <div style="text-align:center;">
                <div class="sign-line">Form Teacher's Signature</div>
            </div>
            <div style="text-align:center;">
                <div class="sign-line">Principal's Signature / Stamp</div>
            </div>
        </div>

        <div style="font-size: 8px; text-align: center; margin-top: 25px; color: #7c8ba0; border-top: 1px solid #e2e8f0; padding-top: 12px;">
            <strong>DISCLAIMER:</strong> This is a student copy and is not valid for official transactions. Issued on {{ date('jS F, Y') }}.
        </div>
    </div>
</body>
</html>
