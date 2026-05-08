<?php
// app/Http/Controllers/Reports/AnalysisReportController.php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Analysis\SchoolAnalysisService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
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
                return response()->json(['data' => [], 'recordsTotal' => 0, 'recordsFiltered' => 0]);
            }

            $classInfo = DB::table('schoolclass')
                ->leftJoin('schoolarm', 'schoolclass.arm', '=', 'schoolarm.id')
                ->where('schoolclass.id', $classId)
                ->select(DB::raw("CONCAT(schoolclass.schoolclass, ' ', COALESCE(schoolarm.arm, '')) as class_name"))
                ->first();
            $className = $classInfo->class_name ?? 'N/A';

            $students = DB::table('studentRegistration as s')
                ->join('studentclass as sc', 'sc.studentId', '=', 's.id')
                ->leftJoin('studentpicture as sp', 'sp.studentid', '=', 's.id')
                ->where('sc.schoolclassid', $classId)
                ->where('sc.termid', $termId)
                ->where('sc.sessionid', $sessionId)
                ->select('s.id as student_id', 's.firstname', 's.lastname', 's.othername', 's.admissionNo', 'sp.picture as avatar')
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
                    ->join('school_bill', 'school_bill.id', '=', 'sbcts.bill_id')
                    ->sum('school_bill.bill_amount');

                $totalSavings = $paymentBook ? (($paymentBook->scholarship_deduction ?? 0) + ($paymentBook->discount_deduction ?? 0)) : 0;
                $adjustedBilled = max(0, $totalBilled - $totalSavings);
                $totalPaid = $paymentBook ? (float)$paymentBook->amount_paid : 0;
                $outstanding = max(0, $adjustedBilled - $totalPaid);
                $completion = $adjustedBilled > 0 ? round(($totalPaid / $adjustedBilled) * 100, 1) : 0;

                $studentName = trim($student->firstname . ' ' . $student->lastname);
                if (!empty($student->othername)) $studentName .= ' (' . $student->othername . ')';

                $data[] = [
                    'student_id' => $student->student_id,
                    'student_name' => $studentName,
                    'admission_no' => $student->admissionNo ?? 'N/A',
                    'total_billed' => $adjustedBilled,
                    'total_paid' => $totalPaid,
                    'outstanding' => $outstanding,
                    'completion' => $completion,
                    'avatar' => $student->avatar,
                    'class_name' => $className,
                ];
            }

            usort($data, function($a, $b) { return $b['outstanding'] - $a['outstanding']; });

            return response()->json(['data' => $data, 'recordsTotal' => count($data), 'recordsFiltered' => count($data)]);

        } catch (\Exception $e) {
            \Log::error('Class Analysis Error: ' . $e->getMessage());
            return response()->json(['error' => true, 'message' => $e->getMessage(), 'data' => []], 500);
        }
    }

    /**
     * Export Class Analysis - FIXED with sanitized filename
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
            ->select('s.id as student_id', 's.firstname', 's.lastname', 's.othername', 's.admissionNo', 'sp.picture as avatar')
            ->get();

        $reportData = [];
        $totalBilledAll = 0;
        $totalPaidAll = 0;
        $totalOutstandingAll = 0;
        $fullyPaidCount = 0;
        $partialCount = 0;

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
            if (!empty($student->othername)) $studentName .= ' (' . $student->othername . ')';

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

            if ($outstanding <= 0 && $totalPaid > 0) $fullyPaidCount++;
            elseif ($totalPaid > 0) $partialCount++;
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
            'fullyPaidCount' => $fullyPaidCount,
            'partialCount' => $partialCount,
            'collectionRate' => $collectionRate,
            'generatedAt' => now()->format('d F, Y H:i:s'),
        ];

        // Sanitize filename - remove invalid characters
        $safeClassName = $this->sanitizeFilename($className);
        $safeTermName = $this->sanitizeFilename($termName);
        $safeSessionName = $this->sanitizeFilename($sessionName);

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.analysis.pdf.class-analysis', $data);
            $filename = "class_analysis_{$safeClassName}_{$safeTermName}_{$safeSessionName}.pdf";
            if (strlen($filename) > 200 || $filename == "class_analysis___.pdf") {
                $filename = "class_analysis_" . date('Y-m-d_H-i-s') . ".pdf";
            }
            return $pdf->download($filename);
        }

        // CSV Export
        $filename = "class_analysis_{$safeClassName}_{$safeTermName}_{$safeSessionName}.csv";
        if (strlen($filename) > 200 || $filename == "class_analysis___.csv") {
            $filename = "class_analysis_" . date('Y-m-d_H-i-s') . ".csv";
        }

        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        fputcsv($handle, ['Student Name', 'Admission No', 'Total Billed (₦)', 'Total Paid (₦)', 'Outstanding (₦)']);
        foreach ($reportData as $row) {
            fputcsv($handle, [$row['student_name'], $row['admission_no'], number_format($row['total_billed'], 2), number_format($row['total_paid'], 2), number_format($row['outstanding'], 2)]);
        }

        fputcsv($handle, []);
        fputcsv($handle, ['SUMMARY']);
        fputcsv($handle, ['Class:', $className]);
        fputcsv($handle, ['Term:', $termName]);
        fputcsv($handle, ['Session:', $sessionName]);
        fputcsv($handle, ['Total Students:', $data['totalStudents']]);
        fputcsv($handle, ['Total Billed:', '₦' . number_format($totalBilledAll, 2)]);
        fputcsv($handle, ['Total Paid:', '₦' . number_format($totalPaidAll, 2)]);
        fputcsv($handle, ['Total Outstanding:', '₦' . number_format($totalOutstandingAll, 2)]);
        fputcsv($handle, ['Collection Rate:', $collectionRate . '%']);
        fputcsv($handle, ['Fully Paid Students:', $fullyPaidCount]);
        fputcsv($handle, ['Partial Payment Students:', $partialCount]);
        fputcsv($handle, ['Generated:', $data['generatedAt']]);

        fclose($handle);
        exit;
    }

    /**
     * School Wide Analysis
     */
    public function schoolWideAnalysis(Request $request)
    {
        $pagetitle = 'School Wide Payment Analysis';
        if ($request->ajax()) {
            $analysis = $this->analysisService->getSchoolFinancialSummary($request->input('session_id'), $request->input('term_id'));
            return response()->json(['success' => true, 'data' => $analysis]);
        }
        return view('reports.analysis.school-wide-analysis', compact('pagetitle', 'terms' => $this->analysisService->getAllTerms(), 'sessions' => $this->analysisService->getAllSessions()));
    }

    /**
     * Scholarship Impact Analysis
     */
    public function scholarshipImpactAnalysis(Request $request)
    {
        $pagetitle = 'Scholarship & Discount Impact Analysis';
        if ($request->ajax()) {
            $data = $this->analysisService->getScholarshipImpactAnalysis($request->input('term_id'), $request->input('session_id'));
            return response()->json(['success' => true, 'data' => $data]);
        }
        return view('reports.analysis.scholarship-impact', compact('pagetitle', 'terms' => $this->analysisService->getAllTerms(), 'sessions' => $this->analysisService->getAllSessions()));
    }

    /**
     * Student Payment Details
     */
    public function studentPaymentDetails($studentId, $classId, $termId, $sessionId)
    {
        $student = DB::table('studentRegistration')->where('id', $studentId)->first();
        if (!$student) abort(404);

        $studentName = trim($student->firstname . ' ' . $student->lastname);
        if (!empty($student->othername)) $studentName .= ' (' . $student->othername . ')';

        $paymentBook = DB::table('student_bill_payment_book')->where('student_id', $studentId)->where('class_id', $classId)->where('term_id', $termId)->where('session_id', $sessionId)->first();
        $bills = DB::table('school_bill_class_term_session')->where('class_id', $classId)->where('termid_id', $termId)->where('session_id', $sessionId)->join('school_bill', 'school_bill.id', '=', 'school_bill_class_term_session.bill_id')->select('school_bill.*')->get();
        $paymentRecords = DB::table('student_bill_payment')->join('student_bill_payment_record', 'student_bill_payment_record.student_bill_payment_id', '=', 'student_bill_payment.id')->leftJoin('users', 'users.id', '=', 'student_bill_payment.generated_by')->where('student_bill_payment.student_id', $studentId)->where('student_bill_payment.class_id', $classId)->where('student_bill_payment.termid_id', $termId)->where('student_bill_payment.session_id', $sessionId)->select('student_bill_payment.created_at as payment_date', 'student_bill_payment.payment_method', 'student_bill_payment.status', 'student_bill_payment_record.amount_paid', 'student_bill_payment_record.amount_owed as balance', DB::raw("COALESCE(users.name, 'System') as received_by"))->orderBy('student_bill_payment_record.created_at', 'desc')->get();

        return view('reports.analysis.student-payment-details', compact('student', 'studentName', 'paymentBook', 'bills', 'paymentRecords', 'studentId', 'classId', 'termId', 'sessionId'));
    }

    /**
     * Export School Wide Analysis
     */
    public function exportSchoolWideAnalysis(Request $request)
    {
        $analysis = $this->analysisService->getSchoolFinancialSummary($request->input('session_id'), $request->input('term_id'));
        $filename = "school_wide_analysis_" . date('Y-m-d_H-i-s') . ".csv";
        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        fputcsv($handle, ['School Wide Financial Summary']);
        fputcsv($handle, ['Total Revenue:', '₦' . number_format($analysis['total_revenue'] ?? 0, 2)]);
        fputcsv($handle, ['Total Collected:', '₦' . number_format($analysis['total_collected'] ?? 0, 2)]);
        fputcsv($handle, ['Collection Rate:', ($analysis['collection_rate'] ?? 0) . '%']);
        fputcsv($handle, []);
        fputcsv($handle, ['Class', 'Students', 'Total Billed', 'Total Collected', 'Collection Rate']);
        foreach ($analysis['class_performance'] ?? [] as $class) {
            fputcsv($handle, [$class->class_name, $class->student_count, number_format($class->total_billed, 2), number_format($class->total_collected, 2), $class->collection_rate . '%']);
        }
        fclose($handle);
        exit;
    }

    /**
     * Sanitize filename - remove invalid characters
     */
    private function sanitizeFilename($filename)
    {
        if (empty($filename)) return 'report';
        // Remove any invalid characters for filenames
        $filename = preg_replace('/[\/\\\\:*?"<>|]/', '-', $filename);
        // Remove multiple consecutive dashes
        $filename = preg_replace('/-+/', '-', $filename);
        // Trim dashes from beginning and end
        $filename = trim($filename, '-');
        // Limit length to 100 characters
        $filename = substr($filename, 0, 100);
        return $filename;
    }
}