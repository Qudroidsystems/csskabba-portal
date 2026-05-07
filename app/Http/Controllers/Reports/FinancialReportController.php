<?php
// app/Http/Controllers/Reports/FinancialReportController.php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reporting\FinancialReportService;
use App\Services\Accounting\AccountingService;
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
        $this->middleware('permission:View financial reports');
        $this->middleware('permission:Export financial reports', ['only' => ['exportBalanceSheet', 'exportIncomeStatement', 'exportTrialBalance', 'exportCashFlow']]);
    }

    /**
     * Balance Sheet Report
     */
    public function balanceSheet(Request $request)
    {
        $asAtDate = $request->get('as_at_date', now()->format('Y-m-d'));

        if ($request->ajax()) {
            $data = $this->financialService->generateBalanceSheet($asAtDate);
            return response()->json(['success' => true, 'data' => $data]);
        }

        if ($request->get('format') === 'pdf') {
            $data = $this->financialService->generateBalanceSheet($asAtDate);
            $pdf = PDF::loadView('reports.financial.balance-sheet-pdf', compact('data', 'asAtDate'));
            return $pdf->download("balance_sheet_{$asAtDate}.pdf");
        }

        $pagetitle = 'Balance Sheet';
        return view('reports.financial.balance-sheet', compact('pagetitle', 'asAtDate'));
    }

    /**
     * Income Statement
     */
    public function incomeStatement(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        if ($request->ajax()) {
            $data = $this->financialService->generateIncomeStatement($startDate, $endDate);
            return response()->json(['success' => true, 'data' => $data]);
        }

        if ($request->get('format') === 'pdf') {
            $data = $this->financialService->generateIncomeStatement($startDate, $endDate);
            $pdf = PDF::loadView('reports.financial.income-statement-pdf', compact('data', 'startDate', 'endDate'));
            return $pdf->download("income_statement_{$startDate}_to_{$endDate}.pdf");
        }

        $pagetitle = 'Income Statement';
        return view('reports.financial.income-statement', compact('pagetitle', 'startDate', 'endDate'));
    }

    /**
     * Trial Balance
     */
    public function trialBalance(Request $request)
    {
        $asAtDate = $request->get('as_at_date', now()->format('Y-m-d'));

        if ($request->ajax()) {
            $trialBalance = $this->accountingService->getTrialBalance($asAtDate);
            $totalDebit = array_sum(array_column($trialBalance, 'debit'));
            $totalCredit = array_sum(array_column($trialBalance, 'credit'));
            return response()->json([
                'success' => true,
                'data' => compact('trialBalance', 'totalDebit', 'totalCredit')
            ]);
        }

        if ($request->get('format') === 'excel') {
            return $this->exportTrialBalanceExcel($asAtDate);
        }

        $pagetitle = 'Trial Balance';
        return view('reports.financial.trial-balance', compact('pagetitle', 'asAtDate'));
    }

    /**
     * Cash Flow Statement
     */
    public function cashFlow(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        if ($request->ajax()) {
            $data = $this->financialService->generateCashFlow($startDate, $endDate);
            return response()->json(['success' => true, 'data' => $data]);
        }

        if ($request->get('format') === 'pdf') {
            $data = $this->financialService->generateCashFlow($startDate, $endDate);
            $pdf = PDF::loadView('reports.financial.cash-flow-pdf', compact('data', 'startDate', 'endDate'));
            return $pdf->download("cash_flow_{$startDate}_to_{$endDate}.pdf");
        }

        $pagetitle = 'Cash Flow Statement';
        return view('reports.financial.cash-flow', compact('pagetitle', 'startDate', 'endDate'));
    }

    /**
     * Debtors List
     */
    public function debtorsList(Request $request)
    {
        $pagetitle = 'Student Debtors List';

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

            // Apply filters
            if ($request->filled('class_id')) {
                $query->where('sbpb.class_id', $request->class_id);
            }
            if ($request->filled('term_id')) {
                $query->where('sbpb.term_id', $request->term_id);
            }
            if ($request->filled('session_id')) {
                $query->where('sbpb.session_id', $request->session_id);
            }
            if ($request->filled('min_outstanding')) {
                $query->where('sbpb.amount_owed', '>=', $request->min_outstanding);
            }
            if ($request->filled('search_value')) {
                $search = $request->search_value;
                $query->where(function($q) use ($search) {
                    $q->where('s.firstname', 'like', "%{$search}%")
                      ->orWhere('s.lastname', 'like', "%{$search}%")
                      ->orWhere('s.admissionNo', 'like', "%{$search}%");
                });
            }

            $debtors = $query->orderBy('sbpb.amount_owed', 'desc')->get();

            // Add collection rate
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
                        return '<img src="' . asset('storage/images/student_avatars/' . $picture) . '" class="student-avatar-img" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;">';
                    }
                    return '<div class="student-avatar-placeholder" style="width: 35px; height: 35px; border-radius: 50%; background: #2563eb; color: white; display: flex; align-items: center; justify-content: center;">' . substr($row->student_id, -2) . '</div>';
                })
                ->addColumn('action', function($row) {
                    return '<a href="' . route('reports.analysis.class-student-details', [
                        'studentId' => $row->student_id,
                        'classId' => $row->class_id,
                        'termId' => $row->term_id,
                        'sessionId' => $row->session_id
                    ]) . '" class="btn btn-sm btn-outline-primary" target="_blank"><i class="ri-eye-line"></i></a>';
                })
                ->rawColumns(['student_avatar', 'action'])
                ->make(true);
        }

        // Get filter data
        $classes = DB::table('schoolclass')
            ->leftJoin('schoolarm', 'schoolclass.arm', '=', 'schoolarm.id')
            ->select(
                'schoolclass.id',
                DB::raw("CONCAT(schoolclass.schoolclass, ' ', COALESCE(schoolarm.arm, '')) as display_name")
            )
            ->orderBy('schoolclass.schoolclass')
            ->get();

        $terms = DB::table('schoolterm')->orderBy('id')->get();
        $sessions = DB::table('schoolsession')->orderBy('session', 'desc')->get();

        return view('reports.financial.debtors-list', compact('pagetitle', 'classes', 'terms', 'sessions'));
    }

    /**
     * Collection Summary
     */
    public function collectionSummary(Request $request)
    {
        $pagetitle = 'School Fee Collection Summary';

        if ($request->ajax()) {
            $query = DB::table('student_bill_payment_book')
                ->select(
                    'class_id',
                    'term_id',
                    'session_id',
                    DB::raw('COUNT(DISTINCT student_id) as student_count'),
                    DB::raw('SUM(amount_paid) as total_collected'),
                    DB::raw('SUM(amount_owed) as total_outstanding'),
                    DB::raw('SUM(adjusted_amount) as total_adjusted')
                )
                ->groupBy('class_id', 'term_id', 'session_id');

            if ($request->filled('class_id')) {
                $query->where('class_id', $request->class_id);
            }
            if ($request->filled('term_id')) {
                $query->where('term_id', $request->term_id);
            }
            if ($request->filled('session_id')) {
                $query->where('session_id', $request->session_id);
            }

            $results = $query->get();

            $data = $results->map(function($row) {
                $class = DB::table('schoolclass')
                    ->leftJoin('schoolarm', 'schoolclass.arm', '=', 'schoolarm.id')
                    ->where('schoolclass.id', $row->class_id)
                    ->select(DB::raw("CONCAT(schoolclass.schoolclass, ' ', COALESCE(schoolarm.arm, '')) as class_name"))
                    ->first();

                $term = DB::table('schoolterm')->where('id', $row->term_id)->value('term');
                $session = DB::table('schoolsession')->where('id', $row->session_id)->value('session');

                $collectionRate = $row->total_adjusted > 0 ? round(($row->total_collected / $row->total_adjusted) * 100, 2) : 0;

                return [
                    'class' => $class->class_name ?? 'N/A',
                    'term' => $term ?? 'N/A',
                    'session' => $session ?? 'N/A',
                    'student_count' => $row->student_count,
                    'total_expected' => number_format($row->total_adjusted, 2),
                    'total_collected' => number_format($row->total_collected, 2),
                    'total_outstanding' => number_format($row->total_outstanding, 2),
                    'collection_rate' => $collectionRate . '%',
                ];
            });

            return DataTables::of($data)->make(true);
        }

        $classes = DB::table('schoolclass')
            ->leftJoin('schoolarm', 'schoolclass.arm', '=', 'schoolarm.id')
            ->select(
                'schoolclass.id',
                DB::raw("CONCAT(schoolclass.schoolclass, ' ', COALESCE(schoolarm.arm, '')) as display_name")
            )
            ->orderBy('schoolclass.schoolclass')
            ->get();

        $terms = DB::table('schoolterm')->orderBy('id')->get();
        $sessions = DB::table('schoolsession')->orderBy('session', 'desc')->get();

        return view('reports.financial.collection-summary', compact('pagetitle', 'classes', 'terms', 'sessions'));
    }

    /**
     * Scholarship Impact Report
     */
    public function scholarshipImpact(Request $request)
    {
        $pagetitle = 'Scholarship & Discount Impact Report';

        if ($request->ajax()) {
            // Get active scholarships
            $scholarships = DB::table('scholarship_assignments as sa')
                ->join('scholarships as s', 's.id', '=', 'sa.scholarship_id')
                ->where('sa.status', 'active')
                ->where('sa.effective_from', '<=', now())
                ->where(function($q) {
                    $q->whereNull('sa.effective_to')->orWhere('sa.effective_to', '>=', now());
                })
                ->get();

            // Get active discounts
            $discounts = DB::table('discount_assignments as da')
                ->join('discounts as d', 'd.id', '=', 'da.discount_id')
                ->where('da.status', 'active')
                ->where('da.effective_from', '<=', now())
                ->where(function($q) {
                    $q->whereNull('da.effective_to')->orWhere('da.effective_to', '>=', now());
                })
                ->get();

            // Calculate total savings from payments
            $totalSavings = DB::table('student_bill_payment_book')
                ->sum(DB::raw('scholarship_deduction + discount_deduction'));

            $totalBeneficiaries = DB::table('scholarship_assignments')
                ->distinct('student_id')
                ->count('student_id');

            // Group by type
            $scholarshipByType = $scholarships->groupBy('title')->map(fn($items) => $items->sum('value'));
            $discountByType = $discounts->groupBy('title')->map(fn($items) => $items->sum('value'));

            // Impact by class
            $impactByClass = DB::table('student_bill_payment_book as sbpb')
                ->join('schoolclass as sc', 'sc.id', '=', 'sbpb.class_id')
                ->leftJoin('schoolarm as sa', 'sa.id', '=', 'sc.arm')
                ->select(
                    DB::raw("CONCAT(sc.schoolclass, ' ', COALESCE(sa.arm, '')) as class_name"),
                    DB::raw('SUM(sbpb.scholarship_deduction) as scholarship_value'),
                    DB::raw('SUM(sbpb.discount_deduction) as discount_value'),
                    DB::raw('COUNT(DISTINCT CASE WHEN sbpb.scholarship_deduction > 0 THEN sbpb.student_id END) as scholarship_students'),
                    DB::raw('COUNT(DISTINCT CASE WHEN sbpb.discount_deduction > 0 THEN sbpb.student_id END) as discount_students')
                )
                ->groupBy('sbpb.class_id', 'sc.schoolclass', 'sa.arm')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_scholarships' => $scholarships->count(),
                    'total_discounts' => $discounts->count(),
                    'total_beneficiaries' => $totalBeneficiaries,
                    'total_savings' => $totalSavings,
                    'scholarship_by_type' => $scholarshipByType,
                    'discount_by_type' => $discountByType,
                    'impact_by_class' => $impactByClass,
                ]
            ]);
        }

        return view('reports.financial.scholarship-impact', compact('pagetitle'));
    }

    /**
     * Export Balance Sheet
     */
    public function exportBalanceSheet(Request $request)
    {
        $asAtDate = $request->get('as_at_date', now()->format('Y-m-d'));
        $data = $this->financialService->generateBalanceSheet($asAtDate);
        $pdf = PDF::loadView('reports.financial.balance-sheet-pdf', compact('data', 'asAtDate'));
        return $pdf->download("balance_sheet_{$asAtDate}.pdf");
    }

    /**
     * Export Income Statement
     */
    public function exportIncomeStatement(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $data = $this->financialService->generateIncomeStatement($startDate, $endDate);
        $pdf = PDF::loadView('reports.financial.income-statement-pdf', compact('data', 'startDate', 'endDate'));
        return $pdf->download("income_statement_{$startDate}_to_{$endDate}.pdf");
    }

    /**
     * Export Trial Balance to Excel
     */
    public function exportTrialBalanceExcel($asAtDate)
    {
        $trialBalance = $this->accountingService->getTrialBalance($asAtDate);
        $totalDebit = array_sum(array_column($trialBalance, 'debit'));
        $totalCredit = array_sum(array_column($trialBalance, 'credit'));

        $filename = "trial_balance_{$asAtDate}.csv";
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        fputcsv($handle, ['Account Code', 'Account Name', 'Account Type', 'Debit (₦)', 'Credit (₦)', 'Balance (₦)']);

        foreach ($trialBalance as $row) {
            fputcsv($handle, [
                $row['account_code'],
                $row['account_name'],
                $row['account_type'],
                number_format($row['debit'], 2),
                number_format($row['credit'], 2),
                number_format($row['balance'], 2),
            ]);
        }

        fputcsv($handle, []);
        fputcsv($handle, ['TOTALS', '', '', number_format($totalDebit, 2), number_format($totalCredit, 2), '']);

        fclose($handle);
        exit;
    }

    /**
     * Export Cash Flow
     */
    public function exportCashFlow(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $data = $this->financialService->generateCashFlow($startDate, $endDate);
        $pdf = PDF::loadView('reports.financial.cash-flow-pdf', compact('data', 'startDate', 'endDate'));
        return $pdf->download("cash_flow_{$startDate}_to_{$endDate}.pdf");
    }
}
