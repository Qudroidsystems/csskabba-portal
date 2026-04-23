{{-- resources/views/student/assessments/print-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Assessment Report - {{ $fullName ?? 'Student' }}</title>
    <style>
        /* ============================================================
            MATCHING ORIGINAL STUDENT RESULT STYLES
           ============================================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            background: #fff;
            color: #1a1a2e;
            font-size: 11px;
            line-height: 1.4;
            padding: 15px;
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
            font-size: 48px;
            font-weight: 900;
            font-family: 'DejaVu Sans', 'Segoe UI', sans-serif;
            letter-spacing: 6px;
            color: #c0392b;
            text-transform: uppercase;
            width: 100%;
            text-align: center;
        }

        /* Header Section */
        .school-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #1e3a5f;
        }
        .school-name {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #0f1c35;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .school-motto {
            font-size: 9px;
            color: #5a6e8a;
            font-style: italic;
        }
        .school-address {
            font-size: 8px;
            color: #6c757d;
        }

        /* Report Title */
        .report-title {
            text-align: center;
            margin: 15px 0 10px;
        }
        .report-title h2 {
            font-size: 16px;
            font-weight: 700;
            color: #1e3a5f;
            letter-spacing: 1px;
        }
        .term-badge {
            display: inline-block;
            background: #eef2f7;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
            margin-top: 5px;
        }

        /* Student Info Card - Horizontal layout like original */
        .student-info {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 18px;
            margin: 15px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .info-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            flex: 1;
        }
        .info-item {
            display: flex;
            align-items: baseline;
            gap: 5px;
        }
        .info-label {
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.5px;
            min-width: 70px;
        }
        .info-value {
            font-weight: 600;
            color: #0f1c35;
            font-size: 10px;
        }
        .student-photo {
            width: 55px;
            height: 65px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            background: #f1f5f9;
            margin-left: 15px;
        }

        /* Subjects Table - Matching original structure */
        .subjects-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 9.5px;
        }
        .subjects-table th {
            background: #1e3a5f;
            color: white;
            padding: 8px 5px;
            font-weight: 600;
            text-align: center;
            border: 1px solid #2d4a7a;
        }
        .subjects-table td {
            border: 1px solid #d1d9e8;
            padding: 6px 4px;
            text-align: center;
            vertical-align: middle;
        }
        .subjects-table tbody tr:nth-child(even) {
            background-color: #f9fbfd;
        }
        .subject-name-cell {
            font-weight: 600;
            text-align: left !important;
            color: #1e293b;
        }
        .assessment-breakdown-cell {
            font-size: 8px;
            color: #3b4b6e;
            line-height: 1.3;
            text-align: left;
        }
        .grade-box {
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 10px;
            display: inline-block;
            min-width: 30px;
            font-size: 9px;
        }
        .grade-A1, .grade-A { background: #d4edda; color: #0e6b46; }
        .grade-B2, .grade-B3, .grade-B { background: #cce5ff; color: #1565c0; }
        .grade-C4, .grade-C5, .grade-C6, .grade-C { background: #fff3cd; color: #8a6000; }
        .grade-D7, .grade-D { background: #ffe5cc; color: #7a4200; }
        .grade-E8, .grade-F9, .grade-F { background: #f8d7da; color: #c0392b; }

        /* Summary Section - Matching original stats strip */
        .summary-section {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .stats-box {
            background: #f1f5f9;
            border-radius: 10px;
            padding: 10px 15px;
            flex: 1;
            text-align: center;
            border: 1px solid #e2e8f0;
        }
        .stats-box h4 {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            color: #475569;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .stats-number {
            font-size: 18px;
            font-weight: 800;
            color: #0f1c35;
        }
        .stats-label {
            font-size: 8px;
            color: #64748b;
            margin-top: 3px;
        }

        /* Footer */
        .remarks-section {
            margin-top: 20px;
            border-top: 1px solid #cbd5e1;
            padding-top: 12px;
            display: flex;
            justify-content: space-between;
        }
        .sign-line {
            margin-top: 6px;
            width: 160px;
            border-top: 1px dotted #94a3b8;
            padding-top: 4px;
            font-size: 8px;
            color: #5b6e8c;
            text-align: center;
        }
        .disclaimer {
            font-size: 7px;
            text-align: center;
            margin-top: 15px;
            color: #7c8ba0;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }

        /* Print optimization */
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            .watermark {
                opacity: 0.15;
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

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .fw-bold { font-weight: 700; }
        .mt-1 { margin-top: 4px; }
        .mb-1 { margin-bottom: 4px; }
    </style>
</head>
<body>

    {{-- WATERMARK --}}
    <div class="watermark">STUDENT COPY - NOT FOR OFFICIAL USE</div>

    <div class="report-container">

        {{-- SCHOOL HEADER --}}
        <div class="school-header">
            @if(!empty($logoBase64))
                <img src="{{ $logoBase64 }}" alt="School Logo" style="height: 55px; margin-bottom: 5px;">
            @endif
            <div class="school-name">{{ $schoolInfo->school_name ?? 'PREMIUM ACADEMY' }}</div>
            @if(!empty($schoolInfo->motto))
                <div class="school-motto">{{ $schoolInfo->motto }}</div>
            @endif
            <div class="school-address">
                {{ $schoolInfo->address ?? 'P.O. Box 123, Education City' }}
                @if(!empty($schoolInfo->email)) | {{ $schoolInfo->email }} @endif
                @if(!empty($schoolInfo->phone)) | {{ $schoolInfo->phone }} @endif
            </div>
        </div>

        {{-- REPORT TITLE --}}
        <div class="report-title">
            <h2>TERM ASSESSMENT REPORT</h2>
            <div class="term-badge">{{ strtoupper($termName ?? '') }} TERM · {{ $sessionName ?? 'N/A' }} SESSION</div>
        </div>

        {{-- STUDENT INFO CARD - HORIZONTAL LAYOUT --}}
        <div class="student-info">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Student Name:</span>
                    <span class="info-value">{{ $fullName ?? $student->firstname . ' ' . $student->lastname }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Admission No:</span>
                    <span class="info-value">{{ $student->admissionNo ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Class:</span>
                    <span class="info-value">{{ $className ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Term:</span>
                    <span class="info-value">{{ $termName ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Session:</span>
                    <span class="info-value">{{ $sessionName ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Report Date:</span>
                    <span class="info-value">{{ date('d M, Y') }}</span>
                </div>
            </div>
            @if(!empty($pictureBase64))
                <img src="{{ $pictureBase64 }}" class="student-photo" alt="Student Photo">
            @endif
        </div>

        {{-- SUBJECTS TABLE - MATCHING ORIGINAL STRUCTURE --}}
        <table class="subjects-table" cellspacing="0">
            <thead>
                <tr>
                    <th width="20%">Subject</th>
                    <th width="25%">Assessment Breakdown (CA / Exam)</th>
                    <th width="9%">Total (100)</th>
                    <th width="9%">Cumulative</th>
                    <th width="9%">Grade</th>
                    <th width="9%">GPA</th>
                    <th width="19%">Remark / Position</th>
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
                        // Build assessment text like original
                        $assessments = $subject['assessments'] ?? collect();
                        $assessText = '';
                        foreach($assessments as $a) {
                            $assessText .= $a['name'] . ': ' . number_format($a['score'],0) . '/' . $a['max_score'] . ($loop->last ? '' : ', ');
                        }
                        $position = $subject['position'] ?? '-';
                        $remark = $subject['remark'] ?? '-';
                    @endphp
                    <tr>
                        <td class="subject-name-cell">
                            {{ $subject['subject_name'] }}
                            @if(!empty($subject['subject_code']))
                                <br><span style="font-size:7px; color:#5b6e8c;">{{ $subject['subject_code'] }}</span>
                            @endif
                        </td>
                        <td class="assessment-breakdown-cell">{{ $assessText ?: '—' }}</td>
                        <td><strong>{{ number_format($subject['total'] ?? 0, 1) }}</strong></td>
                        <td><strong>{{ number_format($subject['cum'] ?? 0, 1) }}</strong></td>
                        <td><span class="grade-box {{ $gradeClass }}">{{ $grade }}</span></td>
                        <td>{{ number_format($subject['subject_gpa'] ?? 0, 1) }}</td>
                        <td style="font-size: 8px;">
                            @if($remark !== '-') {{ $remark }} @endif
                            @if($position !== '-') <br><strong>Position:</strong> {{ $position }} @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 30px;">No subject records found for this term.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- PERFORMANCE SUMMARY - MATCHING ORIGINAL STATS STRIP --}}
        <div class="summary-section">
            <div class="stats-box">
                <h4>Subjects</h4>
                <div class="stats-number">{{ $subjectsWithAssessments->count() ?? 0 }}</div>
                <div class="stats-label">Total Subjects</div>
            </div>
            <div class="stats-box">
                <h4>Average Score</h4>
                <div class="stats-number">{{ number_format($overallProgress['average_cum'] ?? 0, 1) }}%</div>
                <div class="stats-label">Class Average</div>
            </div>
            <div class="stats-box">
                <h4>GPA</h4>
                <div class="stats-number">{{ number_format($overallProgress['gpa'] ?? 0, 2) }}</div>
                <div class="stats-label">Term GPA</div>
            </div>
            <div class="stats-box">
                <h4>CGPA</h4>
                <div class="stats-number">{{ number_format($overallProgress['cgpa'] ?? 0, 2) }}</div>
                <div class="stats-label">Cumulative GPA</div>
            </div>
            <div class="stats-box">
                <h4>GPA Grade</h4>
                @php
                    $gpaGrade = $overallProgress['gpa_grade'] ?? 'F';
                    $gpaGradeClass = match(true) {
                        str_starts_with($gpaGrade,'A') => 'grade-A1',
                        str_starts_with($gpaGrade,'B') => 'grade-B2',
                        str_starts_with($gpaGrade,'C') => 'grade-C4',
                        str_starts_with($gpaGrade,'D') => 'grade-D7',
                        default => 'grade-F9',
                    };
                @endphp
                <div><span class="grade-box {{ $gpaGradeClass }}" style="font-size: 14px; padding: 3px 12px;">{{ $gpaGrade }}</span></div>
                <div class="stats-label">Letter Grade</div>
            </div>
            <div class="stats-box">
                <h4>Performance</h4>
                <div class="stats-number">{{ number_format($percentage ?? 0, 1) }}%</div>
                <div class="stats-label">Overall Score</div>
            </div>
        </div>

        {{-- TOTAL SUMMARY ROW --}}
        <div style="background: #eef2f7; border-radius: 8px; padding: 8px 15px; margin: 12px 0; text-align: center;">
            <span style="font-weight: 700;">Total Obtainable: {{ $totalObtainable ?? 0 }}</span>
            &nbsp;|&nbsp;
            <span style="font-weight: 700;">Total Obtained: {{ number_format($totalObtained ?? 0, 1) }}</span>
            &nbsp;|&nbsp;
            <span style="font-weight: 700;">Percentage: {{ number_format($percentage ?? 0, 1) }}%</span>
        </div>

        {{-- REMARKS AND SIGNATURES --}}
        <div class="remarks-section">
            <div style="flex: 2;">
                <strong>Teacher's Remark:</strong><br>
                {{ $subjectsWithAssessments->first()['remark'] ?? 'Performed satisfactorily. Keep improving.' }}
            </div>
            <div style="flex: 1; text-align: center;">
                <div class="sign-line">Form Teacher's Signature</div>
            </div>
            <div style="flex: 1; text-align: center;">
                <div class="sign-line">Principal's Signature / Stamp</div>
            </div>
        </div>

        {{-- DISCLAIMER --}}
        <div class="disclaimer">
            <strong>DISCLAIMER:</strong> This is a student copy and is not valid for official transactions.
            Issued on {{ date('jS F, Y') }}.
        </div>
    </div>
</body>
</html>
