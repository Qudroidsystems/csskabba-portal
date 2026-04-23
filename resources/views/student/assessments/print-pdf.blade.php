<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Assessment Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10px;
            line-height: 1.3;
            color: #000;
            background: #fff;
            padding: 10mm;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-25deg);
            font-size: 60px;
            font-weight: 900;
            color: rgba(220, 38, 38, 0.1);
            white-space: nowrap;
            pointer-events: none;
            z-index: 1000;
            text-transform: uppercase;
        }
        .report-container {
            max-width: 190mm;
            margin: 0 auto;
            border: 2px solid #000;
            background: #fff;
            page-break-after: avoid;
        }
        .school-header {
            background: #111827;
            color: white;
            text-align: center;
            padding: 10px;
        }
        .school-name { font-size: 18px; font-weight: 900; text-transform: uppercase; }
        .school-motto { font-size: 10px; margin-top: 3px; }
        .student-info {
            background: #f0f7ff;
            border: 1px solid #2aa886;
            margin: 10px;
            padding: 10px;
            border-radius: 6px;
        }
        .info-row { display: flex; flex-wrap: wrap; margin-bottom: 5px; }
        .info-label { font-weight: 700; width: 100px; }
        .info-value { font-weight: 600; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px;
        }
        th {
            background: #0d1a3d;
            color: white;
            padding: 6px 4px;
            border: 1px solid #000;
            font-size: 9px;
        }
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            font-size: 8.5px;
        }
        .subject-name { text-align: left; font-weight: 700; }
        .grade-A { color: #16a34a; font-weight: 900; }
        .grade-B { color: #2563eb; font-weight: 900; }
        .grade-C { color: #ca8a04; font-weight: 900; }
        .grade-D { color: #ea580c; font-weight: 900; }
        .grade-F { color: #dc2626; font-weight: 900; }
        .position-1 { background: gold; font-weight: 900; }
        .position-2 { background: silver; font-weight: 900; }
        .position-3 { background: #cd7f32; color: white; font-weight: 900; }
        .summary-bar {
            background: #0d1a3d;
            color: white;
            text-align: center;
            padding: 6px;
            margin: 10px;
            font-weight: 700;
        }
        .gpa-box {
            display: flex;
            justify-content: space-around;
            margin: 10px;
            padding: 8px;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }
        .gpa-item { text-align: center; }
        .gpa-value { font-size: 16px; font-weight: 900; color: #1e40af; }
        .remarks {
            margin: 10px;
            border: 1px solid #000;
        }
        .remarks td { padding: 8px; }
        .footer {
            text-align: center;
            padding: 10px;
            background: #f1f5f9;
            margin-top: 10px;
            font-size: 8px;
        }
        @media print {
            .watermark { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            th { background: #0d1a3d !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="watermark">STUDENT COPY - NOT FOR OFFICIAL USE</div>

    @foreach($studentsData as $data)
    <div class="report-container">
        <div class="school-header">
            <div class="school-name">{{ $schoolInfo->school_name ?? 'PREMIUM ACADEMY' }}</div>
            <div class="school-motto">{{ $schoolInfo->school_motto ?? 'EXCELLENCE IN EDUCATION' }}</div>
        </div>

        <div class="student-info">
            <div class="info-row">
                <span class="info-label">NAME:</span>
                <span class="info-value">{{ $data['student']->lastname ?? '' }}, {{ $data['student']->firstname ?? '' }}</span>
                <span class="info-label" style="margin-left: 30px;">CLASS:</span>
                <span class="info-value">{{ $data['class']->schoolclass ?? '' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">ADM NO:</span>
                <span class="info-value">{{ $data['student']->admissionNo ?? '' }}</span>
                <span class="info-label" style="margin-left: 30px;">TERM:</span>
                <span class="info-value">{{ $data['term']->term ?? '' }}</span>
                <span class="info-label" style="margin-left: 30px;">SESSION:</span>
                <span class="info-value">{{ $data['session']->session ?? '' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">GPA:</span>
                <span class="info-value">{{ number_format($data['overallProgress']['gpa'] ?? 0, 2) }}</span>
                <span class="info-label" style="margin-left: 30px;">CGPA:</span>
                <span class="info-value">{{ number_format($data['overallProgress']['cgpa'] ?? 0, 2) }}</span>
                <span class="info-label" style="margin-left: 30px;">GRADE:</span>
                <span class="info-value">{{ $data['overallProgress']['gpa_grade'] ?? 'F' }}</span>
            </div>
        </div>

        @php $selected = $data['selectedColumns']; @endphp
        <table>
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>SUBJECT</th>
                    @foreach($data['subjects']->first()['assessments'] ?? [] as $a)
                        @if(in_array($a['id'], $selected))
                            <th>{{ $a['name'] }}<br>({{ $a['max_score'] }})</th>
                        @endif
                    @endforeach
                    @if(in_array('total', $selected)) <th>TOTAL</th> @endif
                    @if(in_array('cum', $selected)) <th>CUM</th> @endif
                    @if(in_array('grade', $selected)) <th>GRADE</th> @endif
                    @if(in_array('subject_gpa', $selected)) <th>GPA</th> @endif
                    @if(in_array('position', $selected)) <th>POS</th> @endif
                </tr>
            </thead>
            <tbody>
                @foreach($data['subjects'] as $idx => $subj)
                @php
                    $grade = $subj['grade'] ?? '-';
                    $gradeClass = match(true) {
                        str_starts_with($grade, 'A') => 'grade-A',
                        str_starts_with($grade, 'B') => 'grade-B',
                        str_starts_with($grade, 'C') => 'grade-C',
                        str_starts_with($grade, 'D') => 'grade-D',
                        default => 'grade-F'
                    };
                    $position = $subj['position'] ?? '-';
                    $posNum = (int) filter_var($position, FILTER_SANITIZE_NUMBER_INT);
                    $posClass = match($posNum) { 1 => 'position-1', 2 => 'position-2', 3 => 'position-3', default => '' };
                @endphp
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td class="subject-name">{{ $subj['subject_name'] }}</td>
                    @foreach($subj['assessments'] as $a)
                        @if(in_array($a['id'], $selected))
                            <td>{{ $a['score'] ? number_format($a['score'], 0) : '-' }}</td>
                        @endif
                    @endforeach
                    @if(in_array('total', $selected)) <td>{{ number_format($subj['total'], 1) }}</td> @endif
                    @if(in_array('cum', $selected)) <td>{{ number_format($subj['cum'], 1) }}</td> @endif
                    @if(in_array('grade', $selected)) <td class="{{ $gradeClass }}">{{ $grade }}</td> @endif
                    @if(in_array('subject_gpa', $selected)) <td>{{ number_format($subj['subject_gpa'], 1) }}</td> @endif
                    @if(in_array('position', $selected)) <td class="{{ $posClass }}">{{ $position }}</td> @endif
                </tr>
                @endforeach
            </tbody>
        </table>

        @if(in_array('totals_summary', $selected))
        <div class="summary-bar">
            TOTAL OBTAINED: {{ number_format($data['totalObtained'], 1) }} |
            TOTAL OBTAINABLE: {{ $data['totalObtainable'] }} |
            PERCENTAGE: {{ number_format($data['percentage'], 1) }}%
        </div>
        @endif

        @if(in_array('gpa_summary', $selected) || in_array('cgpa_summary', $selected))
        <div class="gpa-box">
            @if(in_array('gpa_summary', $selected))
            <div class="gpa-item">
                <div>TERM GPA</div>
                <div class="gpa-value">{{ number_format($data['overallProgress']['gpa'] ?? 0, 2) }}</div>
            </div>
            @endif
            @if(in_array('cgpa_summary', $selected))
            <div class="gpa-item">
                <div>CGPA</div>
                <div class="gpa-value">{{ number_format($data['overallProgress']['cgpa'] ?? 0, 2) }}</div>
            </div>
            @endif
            <div class="gpa-item">
                <div>AVERAGE</div>
                <div class="gpa-value">{{ number_format($data['overallProgress']['average_cum'] ?? 0, 1) }}%</div>
            </div>
            <div class="gpa-item">
                <div>SUBJECTS</div>
                <div class="gpa-value">{{ $data['subjects']->count() }}</div>
            </div>
        </div>
        @endif

        @if(in_array('remarks', $selected))
        <table class="remarks">
            <tr>
                <td width="50%">
                    <strong>Teacher's Remark:</strong><br>
                    {{ $data['subjects']->first()['remark'] ?? 'Performed satisfactorily.' }}
                </td>
                <td width="50%">
                    <strong>Principal's Remark:</strong><br>
                    {{ $data['subjects']->first()['remark'] ?? 'Approved.' }}
                </td>
            </tr>
        </table>
        @endif

        <div class="footer">
            <div>Issued: {{ date('jS F, Y') }} | Received by: _________________________</div>
            <div>Next Term Begins: {{ \Carbon\Carbon::now()->addMonths(2)->format('jS F, Y') }}</div>
            <div style="margin-top: 5px;">Powered by School Management System</div>
        </div>
    </div>
    @endforeach
</body>
</html>
