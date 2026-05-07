<?php
// app/Http/Controllers/Reports/AnalysisReportController.php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Analysis\SchoolAnalysisService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AnalysisReportController extends Controller
{
    protected $analysisService;

    public function __construct(SchoolAnalysisService $analysisService)
    {
        $this->analysisService = $analysisService;
        // $this->middleware('permission:View analysis reports');
        // $this->middleware('permission:Export analysis reports', ['only' => ['exportClassAnalysis', 'exportSchoolWideAnalysis']]);
    }


    public function classAnalysis(Request $request)
{
    $pagetitle = 'Class Financial Analysis';

    if (!$request->ajax()) {
        $classes = $this->analysisService->getAllClasses();
        $terms = $this->analysisService->getAllTerms();
        $sessions = $this->analysisService->getAllSessions();
        return view('reports.analysis.class-analysis', compact('pagetitle', 'classes', 'terms', 'sessions'));
    }

    try {
        $classId = $request->input('class_id');
        $termId = $request->input('term_id');
        $sessionId = $request->input('session_id');

        if (!$classId || !$termId || !$sessionId) {
            return response()->json(['data' => [], 'message' => 'Missing parameters']);
        }

        // Get students
        $students = DB::table('studentRegistration as s')
            ->join('studentclass as sc', 'sc.studentId', '=', 's.id')
            ->leftJoin('studentpicture as sp', 'sp.studentid', '=', 's.id')
            ->where('sc.schoolclassid', $classId)
            ->where('sc.termid', $termId)
            ->where('sc.sessionid', $sessionId)
            ->select('s.id as student_id', 's.firstname', 's.lastname', 's.othername', 's.admissionNo')
            ->get();

        $data = [];
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
                ->join('school_bill as sb', 'sb.id', '=', 'sbcts.bill_id')
                ->sum('sb.bill_amount');

            $totalSavings = $paymentBook ? (($paymentBook->scholarship_deduction ?? 0) + ($paymentBook->discount_deduction ?? 0)) : 0;
            $adjustedBilled = max(0, $totalBilled - $totalSavings);
            $totalPaid = $paymentBook ? (float)$paymentBook->amount_paid : 0;
            $outstanding = max(0, $adjustedBilled - $totalPaid);
            $percentage = $adjustedBilled > 0 ? round(($totalPaid / $adjustedBilled) * 100, 1) : 0;

            $studentName = trim($student->firstname . ' ' . $student->lastname);
            if (!empty($student->othername)) {
                $studentName .= ' (' . $student->othername . ')';
            }

            $data[] = [
                'student_id' => $student->student_id,
                'student_name' => $studentName,
                'admission_no' => $student->admissionNo ?? 'N/A',
                'total_billed' => '₦' . number_format($adjustedBilled, 2),
                'total_paid' => '₦' . number_format($totalPaid, 2),
                'outstanding' => '₦' . number_format($outstanding, 2),
                'completion' => $percentage . '%'
            ];
        }

        return response()->json(['data' => $data]);

    } catch (\Exception $e) {
        Log::error('Class Analysis Error: ' . $e->getMessage());
        return response()->json(['data' => [], 'error' => $e->getMessage()], 500);
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

        $analysis = $this->analysisService->getClassAnalysis($classId, $termId, $sessionId);

        if ($format === 'excel') {
            return $this->exportClassAnalysisExcel($analysis);
        }

        $pdf = PDF::loadView('reports.analysis.pdf.class-analysis', compact('analysis'));
        $filename = "class_analysis_{$classId}_{$termId}_{$sessionId}.pdf";
        return $pdf->download($filename);
    }

    /**
     * Export Class Analysis to Excel
     */
    private function exportClassAnalysisExcel($analysis)
    {
        $filename = "class_analysis_{$analysis['class_name']}.csv";
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        fputcsv($handle, ['Student Name', 'Admission No', 'Total Billed (₦)', 'Total Paid (₦)', 'Outstanding (₦)', 'Savings (₦)']);

        foreach ($analysis['students'] as $student) {
            fputcsv($handle, [
                $student['name'],
                $student['admission_no'],
                number_format($student['total_billed'], 2),
                number_format($student['total_paid'], 2),
                number_format($student['total_outstanding'], 2),
                number_format($student['savings'], 2),
            ]);
        }

        fputcsv($handle, []);
        fputcsv($handle, ['SUMMARY', '', '', '', '', '']);
        fputcsv($handle, ['Total Students:', $analysis['totals']['total_students']]);
        fputcsv($handle, ['Total Billed:', '₦' . number_format($analysis['totals']['total_billed_amount'], 2)]);
        fputcsv($handle, ['Total Paid:', '₦' . number_format($analysis['totals']['total_paid_amount'], 2)]);
        fputcsv($handle, ['Total Outstanding:', '₦' . number_format($analysis['totals']['total_outstanding'], 2)]);
        fputcsv($handle, ['Collection Rate:', $analysis['totals']['collection_rate'] . '%']);

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
            return $this->exportSchoolWideExcel($analysis);
        }

        $pdf = PDF::loadView('reports.analysis.pdf.school-wide-analysis', compact('analysis'));
        $filename = "school_wide_analysis_{$termId}_{$sessionId}.pdf";
        return $pdf->download($filename);
    }

    /**
     * Export School Wide to Excel
     */
    private function exportSchoolWideExcel($analysis)
    {
        $filename = "school_wide_analysis.csv";
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        fputcsv($handle, ['SCHOOL WIDE FINANCIAL SUMMARY']);
        fputcsv($handle, ['Total Revenue:', '₦' . number_format($analysis['total_revenue'], 2)]);
        fputcsv($handle, ['Total Collected:', '₦' . number_format($analysis['total_collected'], 2)]);
        fputcsv($handle, ['Total Outstanding:', '₦' . number_format($analysis['total_outstanding'], 2)]);
        fputcsv($handle, ['Collection Rate:', $analysis['collection_rate'] . '%']);
        fputcsv($handle, []);

        fputcsv($handle, ['CLASS PERFORMANCE']);
        fputcsv($handle, ['Class', 'Students', 'Total Billed', 'Total Collected', 'Collection Rate']);

        foreach ($analysis['class_performance'] as $class) {
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
