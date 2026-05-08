<?php
// app/Http/Controllers/Reports/FinancialReportController.php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reporting\FinancialReportService;
use App\Services\Accounting\AccountingService;
use App\Models\SchoolInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;

class FinancialReportController extends Controller
{
    protected $financialService;
    protected $accountingService;

    public function __construct(
        FinancialReportService $financialService,
        AccountingService $accountingService
    ) {
        $this->financialService = $financialService;
        $this->accountingService = $accountingService;

        // Apply permissions
        $this->middleware('permission:View financial reports', ['only' => [
            'debtorsList',
            'balanceSheet',
            'incomeStatement',
            'trialBalance',
            'cashFlow',
            'collectionSummary',
            'scholarshipImpact'
        ]]);

        $this->middleware('permission:Export financial reports', ['only' => [
            'export'
        ]]);
    }

    /**
     * Debtors List Report
     * Permission: View financial reports
     */
    public function debtorsList(Request $request)
    {
        $pagetitle = 'Student Debtors List';
        $schoolInfo = SchoolInformation::getActiveSchool();

        if ($request->ajax()) {
            $query = DB::table('student_bill_payment_book as sbpb')
                ->join('studentRegistration as s', 's.id', '=', 'sbpb.student_id')
                ->leftJoin('school_bill as sb', 'sb.id', '=', 'sbpb.school_bill_id')
                ->leftJoin('schoolclass as sc', 'sc.id', '=', 'sbpb.class_id')
                ->leftJoin('schoolarm as sa', 'sa.id', '=', 'sc.arm')
                ->leftJoin('schoolterm as st', 'st.id', '=', 'sbpb.term_id')
                ->leftJoin('schoolsession as ss', 'ss.id', '=', 'sbpb.session_id')
                ->where('sbpb.amount_owed', '>', 0)
                ->select(
                    's.id as student_id',
                    DB::raw("CONCAT(s.firstname, ' ', s.lastname) as student_name"),
                    's.admissionNo as admission_no',
                    'sb.title as bill_title',
                    DB::raw("CONCAT(sc.schoolclass, ' ', COALESCE(sa.arm, '')) as class_name"),
                    'st.term as term_name',
                    'ss.session as session_name',
                    'sbpb.original_amount',
                    'sbpb.amount_paid',
                    'sbpb.amount_owed as outstanding',
                    DB::raw("(sbpb.scholarship_deduction + sbpb.discount_deduction) as savings"),
                    'sbpb.class_id',
                    'sbpb.term_id',
                    'sbpb.session_id'
                );

            if ($request->filled('class_id')) $query->where('sbpb.class_id', $request->class_id);
            if ($request->filled('term_id')) $query->where('sbpb.term_id', $request->term_id);
            if ($request->filled('session_id')) $query->where('sbpb.session_id', $request->session_id);
            if ($request->filled('min_outstanding')) $query->where('sbpb.amount_owed', '>=', $request->min_outstanding);

            $debtors = $query->orderBy('sbpb.amount_owed', 'desc')->get();
            foreach ($debtors as $debtor) {
                $total = $debtor->original_amount;
                $paid = $debtor->amount_paid;
                $debtor->collection_rate = $total > 0 ? round(($paid / $total) * 100, 1) : 0;
            }

            return DataTables::of($debtors)
                ->addIndexColumn()
                ->addColumn('student_avatar', function($row) {
                    $picture = DB::table('studentpicture')->where('studentid', $row->student_id)->value('picture');
                    if ($picture && $picture !== 'unnamed.jpg' && $picture !== '') {
                        return '<img src="' . asset('storage/images/student_avatars/' . $picture) . '" class="student-avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; cursor: pointer;">';
                    }
                    $initials = $this->getInitials($row->student_name);
                    return '<div class="student-avatar-placeholder" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #2563eb, #4f46e5); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; cursor: pointer;">' . $initials . '</div>';
                })
                ->addColumn('action', function($row) {
                    return '<a href="' . route('reports.analysis.student-details', [
                        'studentId' => $row->student_id,
                        'classId' => $row->class_id,
                        'termId' => $row->term_id,
                        'sessionId' => $row->session_id
                    ]) . '" class="btn btn-sm btn-outline-primary" target="_blank"><i class="ri-eye-line"></i></a>';
                })
                ->rawColumns(['student_avatar', 'action'])
                ->make(true);
        }

        $classes = DB::table('schoolclass')
            ->leftJoin('schoolarm', 'schoolclass.arm', '=', 'schoolarm.id')
            ->select('schoolclass.id', DB::raw("CONCAT(schoolclass.schoolclass, ' ', COALESCE(schoolarm.arm, '')) as display_name"))
            ->orderBy('schoolclass.schoolclass')->get();

        $terms = DB::table('schoolterm')->orderBy('id')->get();
        $sessions = DB::table('schoolsession')->orderBy('session', 'desc')->get();

        return view('reports.financial.debtors-list', compact('pagetitle', 'classes', 'terms', 'sessions', 'schoolInfo'));
    }

    /**
     * Export Reports
     * Permission: Export financial reports
     */
    public function export($report, $format, Request $request)
    {
        switch ($report) {
            case 'debtors':
                return $this->exportDebtors($format, $request);
            default:
                abort(404, 'Report not found');
        }
    }

    /**
     * Export Debtors Report
     */
    private function exportDebtors($format, Request $request)
    {
        $classId = $request->get('class_id');
        $termId = $request->get('term_id');
        $sessionId = $request->get('session_id');
        $minOutstanding = $request->get('min_outstanding');

        $query = DB::table('student_bill_payment_book as sbpb')
            ->join('studentRegistration as s', 's.id', '=', 'sbpb.student_id')
            ->leftJoin('school_bill as sb', 'sb.id', '=', 'sbpb.school_bill_id')
            ->leftJoin('schoolclass as sc', 'sc.id', '=', 'sbpb.class_id')
            ->leftJoin('schoolarm as sa', 'sa.id', '=', 'sc.arm')
            ->leftJoin('schoolterm as st', 'st.id', '=', 'sbpb.term_id')
            ->leftJoin('schoolsession as ss', 'ss.id', '=', 'sbpb.session_id')
            ->where('sbpb.amount_owed', '>', 0)
            ->select(
                DB::raw("CONCAT(s.firstname, ' ', s.lastname) as student_name"),
                's.admissionNo as admission_no',
                'sb.title as bill_title',
                DB::raw("CONCAT(sc.schoolclass, ' ', COALESCE(sa.arm, '')) as class_name"),
                'st.term as term_name',
                'ss.session as session_name',
                'sbpb.original_amount',
                'sbpb.amount_paid',
                'sbpb.amount_owed as outstanding',
                DB::raw("(sbpb.scholarship_deduction + sbpb.discount_deduction) as savings")
            );

        if ($classId) $query->where('sbpb.class_id', $classId);
        if ($termId) $query->where('sbpb.term_id', $termId);
        if ($sessionId) $query->where('sbpb.session_id', $sessionId);
        if ($minOutstanding) $query->where('sbpb.amount_owed', '>=', $minOutstanding);

        $debtors = $query->orderBy('sbpb.amount_owed', 'desc')->get();

        $filename = "debtors_list_" . date('Y-m-d_H-i-s');

        if ($format === 'excel' || $format === 'csv') {
            $fullFilename = $filename . ".csv";
            $handle = fopen('php://output', 'w');
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $fullFilename . '"');

            fputcsv($handle, ['Student Name', 'Admission No', 'Bill Title', 'Class', 'Term', 'Session', 'Original (₦)', 'Paid (₦)', 'Outstanding (₦)', 'Savings (₦)']);
            foreach ($debtors as $debtor) {
                fputcsv($handle, [
                    $debtor->student_name,
                    $debtor->admission_no,
                    $debtor->bill_title,
                    $debtor->class_name,
                    $debtor->term_name,
                    $debtor->session_name,
                    number_format($debtor->original_amount, 2),
                    number_format($debtor->amount_paid, 2),
                    number_format($debtor->outstanding, 2),
                    number_format($debtor->savings, 2),
                ]);
            }
            fclose($handle);
            exit;
        }

        $fullFilename = $filename . ".pdf";
        $data = compact('debtors');
        $pdf = PDF::loadView('reports.financial.pdf.debtors', $data);
        $pdf->setPaper('a3', 'landscape');
        return $pdf->download($fullFilename);
    }

    // Other methods (balanceSheet, incomeStatement, etc.) with same permission patterns...

    private function getInitials($name)
    {
        if (!$name) return 'ST';
        $parts = explode(' ', trim($name));
        $initials = '';
        for ($i = 0; $i < min(2, count($parts)); $i++) {
            $initials .= substr($parts[$i], 0, 1);
        }
        return strtoupper($initials);
    }
}
