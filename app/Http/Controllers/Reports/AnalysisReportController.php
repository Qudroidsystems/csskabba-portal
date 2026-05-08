<?php
// app/Http/Controllers/Reports/AnalysisReportController.php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Analysis\SchoolAnalysisService;
use App\Models\SchoolInformation;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class AnalysisReportController extends Controller
{
    protected $analysisService;

    public function __construct(SchoolAnalysisService $analysisService)
    {
        $this->analysisService = $analysisService;

        // Apply permissions
        // $this->middleware('permission:View analysis reports', ['only' => [
        //     'index',
        //     'classAnalysis',
        //     'analysisClassTermSession',
        //     'getClassAnalysisData',
        //     'studentPaymentDetails',
        //     'schoolWideAnalysis',
        //     'scholarshipImpactAnalysis'
        // ]]);

        // $this->middleware('permission:Export analysis reports', ['only' => [
        //     'exportClassAnalysis',
        //     'exportSchoolWideAnalysis',
        //     'exportPDF'
        // ]]);
    }

    /**
     * Display the analysis index page with filters
     */
   /**
 * Display the analysis index page with filters
 */
public function index(Request $request)
{
    // If this is an AJAX request, return JSON data
    if ($request->ajax()) {
        return response()->json([
            'data' => [],
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        ]);
    }

    // Otherwise, return the view
    $pagetitle = 'School Bill Analysis';

    $classes = DB::table('schoolclass')
        ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
        ->select('schoolclass.id as id', 'schoolclass.schoolclass as schoolclass', 'schoolarm.arm as schoolarm')
        ->orderBy('schoolclass')
        ->get();

    $terms = DB::table('schoolterm')->orderBy('id')->get();
    $sessions = DB::table('schoolsession')->orderBy('session', 'desc')->get();

    return view('reports.analysis.index', compact('pagetitle', 'classes', 'terms', 'sessions'));
}

    /**
     * Class Analysis Report - Main AJAX endpoint for DataTable
     */
    public function classAnalysis(Request $request)
    {
        return $this->getClassAnalysisData($request);
    }

    /**
     * Display analysis for a specific class, term, and session (Detailed View)
     */
    public function analysisClassTermSession(Request $request)
    {
        $pagetitle = 'School Bill Analysis';

        $request->validate([
            'class_id' => 'required|exists:schoolclass,id',
            'termid_id' => 'required|exists:schoolterm,id',
            'session_id' => 'required|exists:schoolsession,id',
        ]);

        $schoolInfo = SchoolInformation::getActiveSchool();

        // Fetch students
        $students = DB::table('studentclass')
            ->where('schoolclassid', $request->class_id)
            ->where('termid', $request->termid_id)
            ->where('sessionid', $request->session_id)
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'studentclass.studentId')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->select([
                'studentRegistration.admissionNo as admissionno',
                'studentRegistration.firstname as firstname',
                'studentRegistration.lastname as lastname',
                'studentRegistration.id as stid',
                'studentRegistration.othername as othername',
                'studentRegistration.gender as gender',
                'studentpicture.picture as picture'
            ])
            ->get();

        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'No students found for the selected class, term, and session.');
        }

        // Fetch bill information
        $studentBillInfo = DB::table('school_bill_class_term_session')
            ->where('school_bill_class_term_session.class_id', $request->class_id)
            ->where('school_bill_class_term_session.termid_id', $request->termid_id)
            ->where('school_bill_class_term_session.session_id', $request->session_id)
            ->leftJoin('school_bill', 'school_bill.id', '=', 'school_bill_class_term_session.bill_id')
            ->select([
                'school_bill.id as schoolbillid',
                'school_bill.title as title',
                'school_bill.description as description',
                'school_bill.bill_amount as amount'
            ])
            ->get();

        // Fetch payment records
        $studentPayments = DB::table('student_bill_payment')
            ->where('student_bill_payment.class_id', $request->class_id)
            ->where('student_bill_payment.termid_id', $request->termid_id)
            ->where('student_bill_payment.session_id', $request->session_id)
            ->leftJoin('student_bill_payment_record', 'student_bill_payment_record.student_bill_payment_id', '=', 'student_bill_payment.id')
            ->leftJoin('school_bill', 'school_bill.id', '=', 'student_bill_payment.school_bill_id')
            ->select([
                'student_bill_payment.student_id as stid',
                'student_bill_payment.school_bill_id as schoolbillid',
                'student_bill_payment_record.amount_paid as totalAmountPaid',
                'student_bill_payment_record.amount_owed as balance'
            ])
            ->get();

        // Fetch class, term, and session details
        $schoolClass = DB::table('schoolclass')
            ->where('schoolclass.id', $request->class_id)
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select('schoolclass.schoolclass as schoolclass', 'schoolarm.arm as schoolarm')
            ->first();

        $schoolTerm = DB::table('schoolterm')->where('id', $request->termid_id)->value('term');
        $schoolSession = DB::table('schoolsession')->where('id', $request->session_id)->value('session');

        // Calculate student totals
        $studentTotals = [];
        foreach ($students as $student) {
            $totalPaid = 0;
            $totalBalance = 0;

            foreach ($studentBillInfo as $bill) {
                $payment = $studentPayments
                    ->where('stid', $student->stid)
                    ->where('schoolbillid', $bill->schoolbillid)
                    ->first();

                if ($payment) {
                    $totalPaid += $payment->totalAmountPaid ?? 0;
                    $totalBalance += $payment->balance ?? 0;
                } else {
                    $totalBalance += $bill->amount ?? 0;
                }
            }

            $totalBilled = $studentBillInfo->sum('amount');
            $studentTotals[$student->stid] = [
                'totalBilled' => $totalBilled,
                'totalPaid' => $totalPaid,
                'totalBalance' => $totalBalance,
                'status' => $totalPaid > 0 ? ($totalBalance > 0 ? 'partial' : 'paid') : 'unpaid'
            ];
        }

        return view('reports.analysis.class-analysis-details', compact(
            'pagetitle',
            'students',
            'studentBillInfo',
            'studentPayments',
            'studentTotals',
            'schoolClass',
            'schoolTerm',
            'schoolSession',
            'schoolInfo'
        ));
    }

    /**
     * Get class analysis data for AJAX DataTable
     */
    public function getClassAnalysisData(Request $request)
    {
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
                    'completion' => $completion,
                    'avatar' => $student->avatar,
                    'class_name' => $className,
                ];
            }

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
            return response()->json(['error' => true, 'message' => $e->getMessage(), 'data' => []], 500);
        }
    }

    /**
     * Export Class Analysis as PDF
     */
    public function exportPDF($class_id, $termid_id, $session_id, $action = 'view')
    {
        $validator = validator(
            ['class_id' => $class_id, 'termid_id' => $termid_id, 'session_id' => $session_id],
            ['class_id' => 'required|exists:schoolclass,id', 'termid_id' => 'required|exists:schoolterm,id', 'session_id' => 'required|exists:schoolsession,id']
        );

        if ($validator->fails()) {
            return redirect()->route('reports.analysis.index')->withErrors($validator);
        }

        $schoolInfo = SchoolInformation::getActiveSchool();

        // Fetch students
        $students = DB::table('studentclass')
            ->where('schoolclassid', $class_id)
            ->where('termid', $termid_id)
            ->where('sessionid', $session_id)
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'studentclass.studentId')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->select([
                'studentRegistration.admissionNo as admissionno',
                'studentRegistration.firstname as firstname',
                'studentRegistration.lastname as lastname',
                'studentRegistration.id as stid',
                'studentRegistration.othername as othername',
                'studentRegistration.gender as gender',
                'studentpicture.picture as picture'
            ])
            ->get();

        if ($students->isEmpty()) {
            return redirect()->route('reports.analysis.index')->with('error', 'No students found.');
        }

        // Fetch bill information
        $studentBillInfo = DB::table('school_bill_class_term_session')
            ->where('school_bill_class_term_session.class_id', $class_id)
            ->where('school_bill_class_term_session.termid_id', $termid_id)
            ->where('school_bill_class_term_session.session_id', $session_id)
            ->leftJoin('school_bill', 'school_bill.id', '=', 'school_bill_class_term_session.bill_id')
            ->select([
                'school_bill.id as schoolbillid',
                'school_bill.title as title',
                'school_bill.description as description',
                'school_bill.bill_amount as amount'
            ])
            ->get();

        // Fetch payment records
        $studentPayments = DB::table('student_bill_payment')
            ->where('student_bill_payment.class_id', $class_id)
            ->where('student_bill_payment.termid_id', $termid_id)
            ->where('student_bill_payment.session_id', $session_id)
            ->leftJoin('student_bill_payment_record', 'student_bill_payment_record.student_bill_payment_id', '=', 'student_bill_payment.id')
            ->leftJoin('school_bill', 'school_bill.id', '=', 'student_bill_payment.school_bill_id')
            ->select([
                'student_bill_payment.student_id as stid',
                'student_bill_payment.school_bill_id as schoolbillid',
                'student_bill_payment_record.amount_paid as totalAmountPaid',
                'student_bill_payment_record.amount_owed as balance'
            ])
            ->get();

        // Fetch class, term, and session details
        $schoolClass = DB::table('schoolclass')
            ->where('schoolclass.id', $class_id)
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->first();

        $schoolTerm = DB::table('schoolterm')->where('id', $termid_id)->value('term');
        $schoolSession = DB::table('schoolsession')->where('id', $session_id)->value('session');

        // Calculate student totals
        $studentTotals = [];
        foreach ($students as $student) {
            $totalPaid = 0;
            $totalBalance = 0;

            foreach ($studentBillInfo as $bill) {
                $payment = $studentPayments
                    ->where('stid', $student->stid)
                    ->where('schoolbillid', $bill->schoolbillid)
                    ->first();

                if ($payment) {
                    $totalPaid += $payment->totalAmountPaid ?? 0;
                    $totalBalance += $payment->balance ?? 0;
                } else {
                    $totalBalance += $bill->amount ?? 0;
                }
            }

            $totalBilled = $studentBillInfo->sum('amount');
            $studentTotals[$student->stid] = [
                'totalBilled' => $totalBilled,
                'totalPaid' => $totalPaid,
                'totalBalance' => $totalBalance,
                'status' => $totalPaid > 0 ? ($totalBalance > 0 ? 'partial' : 'paid') : 'unpaid'
            ];
        }

        $data = [
            'schoolInfo' => $schoolInfo,
            'students' => $students,
            'studentBillInfo' => $studentBillInfo,
            'studentPayments' => $studentPayments,
            'studentTotals' => $studentTotals,
            'schoolClass' => $schoolClass,
            'schoolTerm' => $schoolTerm,
            'schoolSession' => $schoolSession,
            'totalBillsAmount' => $studentBillInfo->sum('amount'),
        ];

        $pdf = PDF::loadView('reports.analysis.pdf.class-analysis', $data);
        $pdf->setPaper('a3', 'landscape');

        $className = str_replace(['/', '\\'], '_', ($schoolClass->schoolclass ?? '') . ' ' . ($schoolClass->arm ?? ''));
        $termName = str_replace(['/', '\\'], '_', $schoolTerm);
        $sessionName = str_replace(['/', '\\'], '_', $schoolSession);

        $filename = "Payment_Analysis_{$className}_{$termName}_{$sessionName}.pdf";

        if ($action === 'download') {
            return $pdf->download($filename);
        }
        return $pdf->stream($filename);
    }

    /**
     * Export Class Analysis (simple format)
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

        if ($format === 'pdf') {
            return $this->exportPDF($classId, $termId, $sessionId, 'download');
        }

        // CSV Export
        $classInfo = DB::table('schoolclass')
            ->leftJoin('schoolarm', 'schoolclass.arm', '=', 'schoolarm.id')
            ->where('schoolclass.id', $classId)
            ->first();
        $className = ($classInfo->schoolclass ?? '') . ' ' . ($classInfo->arm ?? '');
        $termName = DB::table('schoolterm')->where('id', $termId)->value('term');
        $sessionName = DB::table('schoolsession')->where('id', $sessionId)->value('session');

        $students = DB::table('studentclass')
            ->where('schoolclassid', $classId)
            ->where('termid', $termId)
            ->where('sessionid', $sessionId)
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'studentclass.studentId')
            ->select('studentRegistration.admissionNo', 'studentRegistration.firstname', 'studentRegistration.lastname', 'studentRegistration.othername', 'studentRegistration.id as stid')
            ->get();

        $reportData = [];
        foreach ($students as $student) {
            $paymentBook = DB::table('student_bill_payment_book')
                ->where('student_id', $student->stid)
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
        }

        $filename = "class_analysis_" . str_replace(['/', '\\'], '_', $className) . "_" . date('Y-m-d') . ".csv";
        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        fputcsv($handle, ['Student Name', 'Admission No', 'Total Billed (₦)', 'Total Paid (₦)', 'Outstanding (₦)']);
        foreach ($reportData as $row) {
            fputcsv($handle, [
                $row['student_name'],
                $row['admission_no'],
                number_format($row['total_billed'], 2),
                number_format($row['total_paid'], 2),
                number_format($row['outstanding'], 2),
            ]);
        }
        fclose($handle);
        exit;
    }

    /**
     * School Wide Analysis
     */
    public function schoolWideAnalysis(Request $request)
    {
        $pagetitle = 'School Wide Payment Analysis';
        $schoolInfo = SchoolInformation::getActiveSchool();

        if ($request->ajax()) {
            $termId = $request->input('term_id');
            $sessionId = $request->input('session_id');
            $analysis = $this->analysisService->getSchoolFinancialSummary($sessionId, $termId);
            return response()->json(['success' => true, 'data' => $analysis]);
        }

        $terms = $this->analysisService->getAllTerms();
        $sessions = $this->analysisService->getAllSessions();

        return view('reports.analysis.school-wide-analysis', compact('pagetitle', 'terms', 'sessions', 'schoolInfo'));
    }

    /**
     * Export School Wide Analysis
     */
    public function exportSchoolWideAnalysis(Request $request)
    {
        $termId = $request->input('term_id');
        $sessionId = $request->input('session_id');
        $schoolInfo = SchoolInformation::getActiveSchool();

        $analysis = $this->analysisService->getSchoolFinancialSummary($sessionId, $termId);
        $termName = DB::table('schoolterm')->where('id', $termId)->value('term') ?? 'All Terms';
        $sessionName = DB::table('schoolsession')->where('id', $sessionId)->value('session') ?? 'All Sessions';

        $data = [
            'schoolInfo' => $schoolInfo,
            'analysis' => $analysis,
            'termName' => $termName,
            'sessionName' => $sessionName,
            'generatedAt' => now()->format('d F, Y H:i:s'),
        ];

        $pdf = PDF::loadView('reports.analysis.pdf.school-wide-analysis', $data);
        $pdf->setPaper('a3', 'landscape');

        $filename = "School_Wide_Payment_Analysis_" . str_replace(['/', '\\'], '_', $termName) . "_" . str_replace(['/', '\\'], '_', $sessionName) . ".pdf";
        return $pdf->download($filename);
    }

    /**
     * Scholarship Impact Analysis
     */
    public function scholarshipImpactAnalysis(Request $request)
    {
        $pagetitle = 'Scholarship & Discount Impact Analysis';
        $schoolInfo = SchoolInformation::getActiveSchool();

        if ($request->ajax()) {
            $termId = $request->input('term_id');
            $sessionId = $request->input('session_id');
            $data = $this->analysisService->getScholarshipImpactAnalysis($termId, $sessionId);
            return response()->json(['success' => true, 'data' => $data]);
        }

        $terms = $this->analysisService->getAllTerms();
        $sessions = $this->analysisService->getAllSessions();

        return view('reports.analysis.scholarship-impact', compact('pagetitle', 'terms', 'sessions', 'schoolInfo'));
    }

    /**
     * Student Payment Details
     */
    public function studentPaymentDetails($studentId, $classId, $termId, $sessionId)
    {
        $pagetitle = 'Student Payment Details';
        $schoolInfo = SchoolInformation::getActiveSchool();

        $student = DB::table('studentRegistration as s')
            ->leftJoin('studentpicture as sp', 'sp.studentid', '=', 's.id')
            ->where('s.id', $studentId)
            ->select('s.*', 'sp.picture as avatar')
            ->first();

        if (!$student) {
            abort(404, 'Student not found');
        }

        $studentName = trim($student->firstname . ' ' . $student->lastname);
        if (!empty($student->othername)) {
            $studentName .= ' (' . $student->othername . ')';
        }

        $classInfo = DB::table('schoolclass')
            ->leftJoin('schoolarm', 'schoolclass.arm', '=', 'schoolarm.id')
            ->where('schoolclass.id', $classId)
            ->first();

        $termInfo = DB::table('schoolterm')->where('id', $termId)->first();
        $sessionInfo = DB::table('schoolsession')->where('id', $sessionId)->first();

        $paymentBook = DB::table('student_bill_payment_book')
            ->where('student_id', $studentId)
            ->where('class_id', $classId)
            ->where('term_id', $termId)
            ->where('session_id', $sessionId)
            ->first();

        $bills = DB::table('school_bill_class_term_session as sbcts')
            ->where('sbcts.class_id', $classId)
            ->where('sbcts.termid_id', $termId)
            ->where('sbcts.session_id', $sessionId)
            ->join('school_bill', 'school_bill.id', '=', 'sbcts.bill_id')
            ->select('sb.*')
            ->get();

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
            'schoolInfo',
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
}
