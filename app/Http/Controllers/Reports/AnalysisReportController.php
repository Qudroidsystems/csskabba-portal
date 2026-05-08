<?php
// app/Http/Controllers/Reports/AnalysisReportController.php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Analysis\SchoolAnalysisService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class AnalysisReportController extends Controller
{
    protected $analysisService;

    public function __construct(SchoolAnalysisService $analysisService)
    {
        $this->analysisService = $analysisService;
        $this->middleware('permission:View analysis reports');
        $this->middleware('permission:Export analysis reports', ['only' => ['exportClassAnalysis', 'exportSchoolWideAnalysis']]);
    }

    /**
     * Class Analysis Report
     */
    public function classAnalysis(Request $request)
    {
        $pagetitle = 'Class Financial Analysis';

        // Return the view for non-AJAX requests
        if (!$request->ajax()) {
            $classes = $this->analysisService->getAllClasses();
            $terms = $this->analysisService->getAllTerms();
            $sessions = $this->analysisService->getAllSessions();

            return view('reports.analysis.class-analysis', compact('pagetitle', 'classes', 'terms', 'sessions'));
        }

        // Handle AJAX request for DataTables
        try {
            $classId = $request->input('class_id');
            $termId = $request->input('term_id');
            $sessionId = $request->input('session_id');

            if (!$classId || !$termId || !$sessionId) {
                return response()->json([
                    'data' => [],
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0
                ]);
            }

            // Get class name for display
            $classInfo = DB::table('schoolclass')
                ->leftJoin('schoolarm', 'schoolclass.arm', '=', 'schoolarm.id')
                ->where('schoolclass.id', $classId)
                ->select(DB::raw("CONCAT(schoolclass.schoolclass, ' ', COALESCE(schoolarm.arm, '')) as class_name"))
                ->first();
            $className = $classInfo->class_name ?? 'N/A';

            // Get students in the selected class, term, and session
            $students = DB::table('studentRegistration as s')
                ->join('studentclass as sc', 'sc.studentId', '=', 's.id')
                ->leftJoin('studentpicture as sp', 'sp.studentid', '=', 's.id')
                ->where('sc.schoolclassid', $classId)
                ->where('sc.termid', $termId)
                ->where('sc.sessionid', $sessionId)
                ->select(
                    's.id as student_id',
                    's.firstname',
                    's.lastname',
                    's.othername',
                    's.admissionNo',
                    'sp.picture as avatar'
                )
                ->get();

            $data = [];
            foreach ($students as $student) {
                // Get payment book
                $paymentBook = DB::table('student_bill_payment_book')
                    ->where('student_id', $student->student_id)
                    ->where('class_id', $classId)
                    ->where('term_id', $termId)
                    ->where('session_id', $sessionId)
                    ->first();

                // Get total bills for this class/term/session
                $totalBilled = (float) DB::table('school_bill_class_term_session as sbcts')
                    ->where('sbcts.class_id', $classId)
                    ->where('sbcts.termid_id', $termId)
                    ->where('sbcts.session_id', $sessionId)
                    ->join('school_bill as sb', 'sb.id', '=', 'sbcts.bill_id')
                    ->sum('sb.bill_amount');

                $scholarshipDeduction = $paymentBook ? (float)($paymentBook->scholarship_deduction ?? 0) : 0;
                $discountDeduction = $paymentBook ? (float)($paymentBook->discount_deduction ?? 0) : 0;
                $totalSavings = $scholarshipDeduction + $discountDeduction;
                $adjustedBilled = max(0, $totalBilled - $totalSavings);
                $totalPaid = $paymentBook ? (float)$paymentBook->amount_paid : 0;
                $outstanding = max(0, $adjustedBilled - $totalPaid);
                $completionPercentage = $adjustedBilled > 0 ? round(($totalPaid / $adjustedBilled) * 100, 1) : 0;

                $studentName = trim($student->firstname . ' ' . $student->lastname);
                if (!empty($student->othername)) {
                    $studentName .= ' (' . $student->othername . ')';
                }

                $data[] = [
                    'student_id' => $student->student_id,
                    'student_name' => $studentName,
                    'admission_no' => $student->admissionNo ?? 'N/A',
                    'total_billed' => $adjustedBilled,
                    'total_paid' => $totalPaid,
                    'outstanding' => $outstanding,
                    'completion' => $completionPercentage,
                    'avatar' => $student->avatar,
                    'class_name' => $className,
                ];
            }

            // Sort by outstanding amount (highest first)
            usort($data, function($a, $b) {
                return $b['outstanding'] - $a['outstanding'];
            });

            return response()->json([
                'data' => $data,
                'recordsTotal' => count($data),
                'recordsFiltered' => count($data)
            ]);

        } catch (\Exception $e) {
            \Log::error('Class Analysis Error: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0
            ], 500);
        }
    }

    /**
     * School Wide Payment Analysis
     */
    public function schoolWideAnalysis(Request $request)
    {
        $pagetitle = 'School Wide Payment Analysis';

        if ($request->ajax()) {
            $termId = $request->input('term_id');
            $sessionId = $request->input('session_id');
            $analysis = $this->analysisService->getSchoolFinancialSummary($sessionId, $termId);
            return response()->json(['success' => true, 'data' => $analysis]);
        }

        $terms = $this->analysisService->getAllTerms();
        $sessions = $this->analysisService->getAllSessions();

        return view('reports.analysis.school-wide-analysis', compact('pagetitle', 'terms', 'sessions'));
    }

    /**
     * Scholarship Impact Analysis
     */
    public function scholarshipImpactAnalysis(Request $request)
    {
        $pagetitle = 'Scholarship & Discount Impact Analysis';

        if ($request->ajax()) {
            $termId = $request->input('term_id');
            $sessionId = $request->input('session_id');
            $data = $this->analysisService->getScholarshipImpactAnalysis($termId, $sessionId);
            return response()->json(['success' => true, 'data' => $data]);
        }

        $terms = $this->analysisService->getAllTerms();
        $sessions = $this->analysisService->getAllSessions();

        return view('reports.analysis.scholarship-impact', compact('pagetitle', 'terms', 'sessions'));
    }

    /**
     * Student Payment Details (from analysis view)
     */
    public function studentPaymentDetails($studentId, $classId, $termId, $sessionId)
    {
        $pagetitle = 'Student Payment Details';

        // Get student details
        $student = DB::table('studentRegistration as s')
            ->leftJoin('studentpicture as sp', 'sp.studentid', '=', 's.id')
            ->where('s.id', $studentId)
            ->select('s.*', 'sp.picture as avatar')
            ->first();

        if (!$student) {
            abort(404, 'Student not found');
        }

        // Build student name
        $studentName = trim($student->firstname . ' ' . $student->lastname);
        if (!empty($student->othername)) {
            $studentName .= ' (' . $student->othername . ')';
        }

        // Get class name
        $classInfo = DB::table('schoolclass')
            ->leftJoin('schoolarm', 'schoolclass.arm', '=', 'schoolarm.id')
            ->where('schoolclass.id', $classId)
            ->select(DB::raw("CONCAT(schoolclass.schoolclass, ' ', COALESCE(schoolarm.arm, '')) as class_name"))
            ->first();

        // Get term and session names
        $termInfo = DB::table('schoolterm')->where('id', $termId)->first();
        $sessionInfo = DB::table('schoolsession')->where('id', $sessionId)->first();

        // Get payment book
        $paymentBook = DB::table('student_bill_payment_book')
            ->where('student_id', $studentId)
            ->where('class_id', $classId)
            ->where('term_id', $termId)
            ->where('session_id', $sessionId)
            ->first();

        // Get bills
        $bills = DB::table('school_bill_class_term_session as sbcts')
            ->where('sbcts.class_id', $classId)
            ->where('sbcts.termid_id', $termId)
            ->where('sbcts.session_id', $sessionId)
            ->join('school_bill as sb', 'sb.id', '=', 'sbcts.bill_id')
            ->select('sb.*')
            ->get();

        // Get payment records
        $paymentRecords = DB::table('student_bill_payment as sbp')
            ->join('student_bill_payment_record as sbpr', 'sbpr.student_bill_payment_id', '=', 'sbp.id')
            ->leftJoin('users as u', 'u.id', '=', 'sbp.generated_by')
            ->where('sbp.student_id', $studentId)
            ->where('sbp.class_id', $classId)
            ->where('sbp.termid_id', $termId)
            ->where('sbp.session_id', $sessionId)
            ->select(
                'sbp.created_at as payment_date',
                'sbp.payment_method',
                'sbp.status',
                'sbpr.amount_paid',
                'sbpr.amount_owed as balance',
                DB::raw("COALESCE(u.name, 'System') as received_by")
            )
            ->orderBy('sbpr.created_at', 'desc')
            ->get();

        return view('reports.analysis.student-payment-details', compact(
            'pagetitle',
            'student',
            'studentName',
            'classInfo',
            'termInfo',
            'sessionInfo',
            'paymentBook',
            'bills',
            'paymentRecords',
            'studentId',
            'classId',
            'termId',
            'sessionId'
        ));
    }

    /**
     * Export Class Analysis
     */
    public function exportClassAnalysis(Request $request)
    {
        $classId = $request->input('class_id');
        $termId = $request->input('term_id');
        $sessionId = $request->input('session_id');
        $format = $request->input('format', 'pdf');

        if (!$classId || !$termId || !$sessionId) {
            return redirect()->back()->with('error', 'Please select class, term, and session');
        }

        // Get class name
        $classInfo = DB::table('schoolclass')
            ->leftJoin('schoolarm', 'schoolclass.arm', '=', 'schoolarm.id')
            ->where('schoolclass.id', $classId)
            ->select(DB::raw("CONCAT(schoolclass.schoolclass, ' ', COALESCE(schoolarm.arm, '')) as class_name"))
            ->first();
        $className = $classInfo->class_name ?? 'N/A';

        // Get students
        $students = DB::table('studentRegistration as s')
            ->join('studentclass as sc', 'sc.studentId', '=', 's.id')
            ->leftJoin('studentpicture as sp', 'sp.studentid', '=', 's.id')
            ->where('sc.schoolclassid', $classId)
            ->where('sc.termid', $termId)
            ->where('sc.sessionid', $sessionId)
            ->select(
                's.id as student_id',
                's.firstname',
                's.lastname',
                's.othername',
                's.admissionNo',
                'sp.picture as avatar'
            )
            ->get();

        $reportData = [];
        $totalBilledAll = 0;
        $totalPaidAll = 0;
        $totalOutstandingAll = 0;

        foreach ($students as $student) {
            $paymentBook = DB::table('student_bill_payment_book')
                ->where('student_id', $student->student_id)
                ->where('class_id', $classId)
                ->where('term_id', $termId)
                ->where('session_id', $sessionId)
                ->first();

            $totalBilled = (float) DB::table('school_bill_class_term_session as sbcts')
                ->where('sbcts.class_id', $classId)
                ->where('sbcts.termid_id', $termId)
                ->where('sbcts.session_id', $sessionId)
                ->join('school_bill', 'school_bill.id', '=', 'sbcts.bill_id')
                ->sum('school_bill.bill_amount');

            $totalSavings = $paymentBook ? (($paymentBook->scholarship_deduction ?? 0) + ($paymentBook->discount_deduction ?? 0)) : 0;
            $totalPaid = $paymentBook ? $paymentBook->amount_paid : 0;
            $adjustedBilled = max(0, $totalBilled - $totalSavings);
            $outstanding = max(0, $adjustedBilled - $totalPaid);

            $studentName = trim($student->firstname . ' ' . $student->lastname);
            if (!empty($student->othername)) {
                $studentName .= ' (' . $student->othername . ')';
            }

            $reportData[] = [
                'student_name' => $studentName,
                'admission_no' => $student->admissionNo ?? 'N/A',
                'total_billed' => $adjustedBilled,
                'total_paid' => $totalPaid,
                'outstanding' => $outstanding,
            ];

            $totalBilledAll += $adjustedBilled;
            $totalPaidAll += $totalPaid;
            $totalOutstandingAll += $outstanding;
        }

        $termName = DB::table('schoolterm')->where('id', $termId)->value('term') ?? 'N/A';
        $sessionName = DB::table('schoolsession')->where('id', $sessionId)->value('session') ?? 'N/A';
        $collectionRate = $totalBilledAll > 0 ? round(($totalPaidAll / $totalBilledAll) * 100, 1) : 0;

        $data = [
            'reportData' => $reportData,
            'className' => $className,
            'termName' => $termName,
            'sessionName' => $sessionName,
            'totalBilled' => $totalBilledAll,
            'totalPaid' => $totalPaidAll,
            'totalOutstanding' => $totalOutstandingAll,
            'totalStudents' => count($reportData),
            'collectionRate' => $collectionRate,
            'generatedAt' => now()->format('d F, Y H:i:s'),
        ];

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.analysis.pdf.class-analysis', $data);
            $filename = "class_analysis_" . str_replace(' ', '_', $className) . "_" . str_replace(' ', '_', $termName) . "_" . str_replace(' ', '_', $sessionName) . ".pdf";
            return $pdf->download($filename);
        }

        // CSV Export
        $filename = "class_analysis_" . str_replace(' ', '_', $className) . "_" . str_replace(' ', '_', $termName) . "_" . str_replace(' ', '_', $sessionName) . ".csv";
        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        fputcsv($handle, ['Student Name', 'Admission No', 'Total Billed (₦)', 'Total Paid (₦)', 'Outstanding (₦)']);
        foreach ($data['reportData'] as $row) {
            fputcsv($handle, [
                $row['student_name'],
                $row['admission_no'],
                number_format($row['total_billed'], 2),
                number_format($row['total_paid'], 2),
                number_format($row['outstanding'], 2),
            ]);
        }
        fputcsv($handle, []);
        fputcsv($handle, ['SUMMARY', '', '', '', '']);
        fputcsv($handle, ['Class:', $className]);
        fputcsv($handle, ['Term:', $termName]);
        fputcsv($handle, ['Session:', $sessionName]);
        fputcsv($handle, ['Total Students:', $data['totalStudents']]);
        fputcsv($handle, ['Total Billed:', '₦' . number_format($data['totalBilled'], 2)]);
        fputcsv($handle, ['Total Paid:', '₦' . number_format($data['totalPaid'], 2)]);
        fputcsv($handle, ['Total Outstanding:', '₦' . number_format($data['totalOutstanding'], 2)]);
        fputcsv($handle, ['Collection Rate:', $data['collectionRate'] . '%']);
        fputcsv($handle, ['Generated:', $data['generatedAt']]);
        fclose($handle);
        exit;
    }

    /**
     * Export School Wide Analysis
     */
    public function exportSchoolWideAnalysis(Request $request)
    {
        $termId = $request->input('term_id');
        $sessionId = $request->input('session_id');
        $format = $request->input('format', 'pdf');

        $analysis = $this->analysisService->getSchoolFinancialSummary($sessionId, $termId);

        if ($format === 'excel') {
            $filename = "school_wide_analysis_" . date('Y-m-d') . ".csv";
            $handle = fopen('php://output', 'w');
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            fputcsv($handle, ['School Wide Financial Summary']);
            fputcsv($handle, ['Total Revenue:', '₦' . number_format($analysis['total_revenue'] ?? 0, 2)]);
            fputcsv($handle, ['Total Collected:', '₦' . number_format($analysis['total_collected'] ?? 0, 2)]);
            fputcsv($handle, ['Total Outstanding:', '₦' . number_format($analysis['total_outstanding'] ?? 0, 2)]);
            fputcsv($handle, ['Collection Rate:', ($analysis['collection_rate'] ?? 0) . '%']);
            fputcsv($handle, []);
            fputcsv($handle, ['Class Performance']);
            fputcsv($handle, ['Class', 'Students', 'Total Billed', 'Total Collected', 'Collection Rate']);

            foreach ($analysis['class_performance'] ?? [] as $class) {
                fputcsv($handle, [
                    $class->class_name,
                    $class->student_count,
                    number_format($class->total_billed, 2),
                    number_format($class->total_collected, 2),
                    $class->collection_rate . '%',
                ]);
            }
            fclose($handle);
            exit;
        }

        $pdf = Pdf::loadView('reports.analysis.pdf.school-wide-analysis', compact('analysis'));
        $filename = "school_wide_analysis_" . date('Y-m-d') . ".pdf";
        return $pdf->download($filename);
    }

    /**
     * Chart Data for Dashboard
     */
    public function getChartData(Request $request)
    {
        $type = $request->input('type');
        $period = $request->input('period', 'monthly');

        switch ($type) {
            case 'collection-trend':
                $data = $this->analysisService->getCollectionTrendData($period);
                break;
            case 'class-performance':
                $data = $this->analysisService->getClassPerformanceData();
                break;
            case 'payment-distribution':
                $data = $this->analysisService->getPaymentDistributionData();
                break;
            default:
                return response()->json(['success' => false, 'message' => 'Invalid chart type']);
        }

        return response()->json(['success' => true, 'data' => $data]);
    }
}
