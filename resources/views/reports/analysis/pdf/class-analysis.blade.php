<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Class Analysis Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #1e3a5f;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #1e3a5f;
            font-size: 20px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .summary-box {
            background: #f5f5f5;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #ddd;
        }
        .summary-row:last-child {
            border-bottom: none;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #1e3a5f;
            color: white;
            font-weight: bold;
        }
        .text-end {
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .status-paid { color: #16a34a; font-weight: bold; }
        .status-partial { color: #d97706; font-weight: bold; }
        .status-unpaid { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Class Analysis Report</h1>
        <p>Class: {{ $className }} | Term: {{ $termName }} | Session: {{ $sessionName }}</p>
        <p>Generated: {{ $generatedAt }}</p>
    </div>

    <div class="summary-box">
        <div class="summary-row">
            <strong>Total Students:</strong>
            <span>{{ $totalStudents }}</span>
        </div>
        <div class="summary-row">
            <strong>Total Billed:</strong>
            <span>₦{{ number_format($totalBilled, 2) }}</span>
        </div>
        <div class="summary-row">
            <strong>Total Paid:</strong>
            <span>₦{{ number_format($totalPaid, 2) }}</span>
        </div>
        <div class="summary-row">
            <strong>Total Outstanding:</strong>
            <span>₦{{ number_format($totalOutstanding, 2) }}</span>
        </div>
        <div class="summary-row">
            <strong>Collection Rate:</strong>
            <span>{{ $collectionRate }}%</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>S/N</th>
                <th>Student Name</th>
                <th>Admission No</th>
                <th class="text-end">Total Billed (₦)</th>
                <th class="text-end">Total Paid (₦)</th>
                <th class="text-end">Outstanding (₦)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData as $index => $student)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $student['student_name'] }}</td>
                <td>{{ $student['admission_no'] }}</td>
                <td class="text-end">₦{{ number_format($student['total_billed'], 2) }}</td>
                <td class="text-end">₦{{ number_format($student['total_paid'], 2) }}</td>
                <td class="text-end">
                    @if($student['outstanding'] > 0)
                        <span class="status-unpaid">₦{{ number_format($student['outstanding'], 2) }}</span>
                    @else
                        <span class="status-paid">₦0.00</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">No data available</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot style="background-color: #f0f0f0; font-weight: bold;">
            <tr>
                <td colspan="3" class="text-end">TOTAL</td>
                <td class="text-end">₦{{ number_format($totalBilled, 2) }}</td>
                <td class="text-end">₦{{ number_format($totalPaid, 2) }}</td>
                <td class="text-end">₦{{ number_format($totalOutstanding, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        This is a computer-generated document. No signature is required.
    </div>
</body>
</html>
