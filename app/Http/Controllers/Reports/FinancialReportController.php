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
        $this->middleware('permission:Export financial reports', ['only' => ['export']]);
    }

    /**
     * MAIN EXPORT METHOD - handles all report exports
     */
    public function export($report, $format, Request $request)
    {
        switch ($report) {
            case 'debtors':
                return $this->exportDebtors($format, $request);
            case 'balance-sheet':
                return $this->exportBalanceSheet($format, $request);
            case 'income-statement':
                return $this->exportIncomeStatement($format, $request);
            case 'trial-balance':
                return $this->exportTrialBalance($format, $request);
            case 'cash-flow':
                return $this->exportCashFlow($format, $request);
            default:
                abort(404, 'Report not found');
        }
    }

    /**
     * Debtors List Report with DataTable AJAX
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

            if ($request->filled('class_id')) $query->where('sbpb.class_id', $request->class_id);
            if ($request->filled('term_id')) $query->where('sbpb.term_id', $request->term_id);
            if ($request->filled('session_id')) $query->where('sbpb.session_id', $request->session_id);
            if ($request->filled('min_outstanding')) $query->where('sbpb.amount_owed', '>=', $request->min_outstanding);
            if ($request->filled('search_value')) {
                $search = $request->search_value;
                $query->where(function($q) use ($search) {
                    $q->where('s.firstname', 'like', "%{$search}%")
                      ->orWhere('s.lastname', 'like', "%{$search}%")
                      ->orWhere('s.admissionNo', 'like', "%{$search}%");
                });
            }

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

        $classes = DB::table('schoolclass')
            ->leftJoin('schoolarm', 'schoolclass.arm', '=', 'schoolarm.id')
            ->select('schoolclass.id', DB::raw("CONCAT(schoolclass.schoolclass, ' ', COALESCE(schoolarm.arm, '')) as display_name"))
            ->orderBy('schoolclass.schoolclass')->get();

        $terms = DB::table('schoolterm')->orderBy('id')->get();
        $sessions = DB::table('schoolsession')->orderBy('session', 'desc')->get();

        return view('reports.financial.debtors-list', compact('pagetitle', 'classes', 'terms', 'sessions'));
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

            if ($request->filled('class_id')) $query->where('class_id', $request->class_id);
            if ($request->filled('term_id')) $query->where('term_id', $request->term_id);
            if ($request->filled('session_id')) $query->where('session_id', $request->session_id);

            $results = $query->get();
            $data = $results->map(function($row) {
                $class = DB::table('schoolclass')
                    ->leftJoin('schoolarm', 'schoolclass.arm', '=', 'schoolarm.id')
                    ->where('schoolclass.id', $row->class_id)
                    ->select(DB::raw("CONCAT(schoolclass.schoolclass, ' ', COALESCE(schoolarm.arm, '')) as class_name"))
                    ->first();
                $term = DB::table('schoolterm')->where('id', $row->term_id)->value('term');
                $session = DB::table('schoolsession')->where('id', $row->session_id)->value('session');
                $rate = $row->total_adjusted > 0 ? round(($row->total_collected / $row->total_adjusted) * 100, 2) : 0;

                return [
                    'class' => $class->class_name ?? 'N/A',
                    'term' => $term ?? 'N/A',
                    'session' => $session ?? 'N/A',
                    'student_count' => $row->student_count,
                    'total_expected' => number_format($row->total_adjusted, 2),
                    'total_collected' => number_format($row->total_collected, 2),
                    'total_outstanding' => number_format($row->total_outstanding, 2),
                    'collection_rate' => $rate . '%',
                ];
            });

            return DataTables::of($data)->make(true);
        }

        $classes = DB::table('schoolclass')
            ->leftJoin('schoolarm', 'schoolclass.arm', '=', 'schoolarm.id')
            ->select('schoolclass.id', DB::raw("CONCAT(schoolclass.schoolclass, ' ', COALESCE(schoolarm.arm, '')) as display_name"))
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
            $scholarships = DB::table('scholarship_assignments as sa')
                ->join('scholarships as s', 's.id', '=', 'sa.scholarship_id')
                ->where('sa.status', 'active')
                ->where('sa.effective_from', '<=', now())
                ->where(function($q) {
                    $q->whereNull('sa.effective_to')->orWhere('sa.effective_to', '>=', now());
                })
                ->get();

            $discounts = DB::table('discount_assignments as da')
                ->join('discounts as d', 'd.id', '=', 'da.discount_id')
                ->where('da.status', 'active')
                ->where('da.effective_from', '<=', now())
                ->where(function($q) {
                    $q->whereNull('da.effective_to')->orWhere('da.effective_to', '>=', now());
                })
                ->get();

            $totalSavings = DB::table('student_bill_payment_book')
                ->sum(DB::raw('scholarship_deduction + discount_deduction'));

            $totalBeneficiaries = DB::table('scholarship_assignments')
                ->distinct('student_id')
                ->count('student_id');

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
                    'scholarship_by_type' => $scholarships->groupBy('title')->map(fn($items) => $items->sum('value')),
                    'discount_by_type' => $discounts->groupBy('title')->map(fn($items) => $items->sum('value')),
                    'impact_by_class' => $impactByClass,
                ]
            ]);
        }

        return view('reports.financial.scholarship-impact', compact('pagetitle'));
    }

    // ============================================
    // EXPORT METHODS
    // ============================================

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

        if ($format === 'excel' || $format === 'csv') {
            $filename = "debtors_list_" . date('Y-m-d') . ".csv";
            $handle = fopen('php://output', 'w');
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

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

        $pdf = PDF::loadView('reports.financial.debtors-pdf', compact('debtors'));
        return $pdf->download("debtors_list_" . date('Y-m-d') . ".pdf");
    }

    private function exportBalanceSheet($format, Request $request)
    {
        $asAtDate = $request->get('as_at_date', now()->format('Y-m-d'));
        $data = $this->financialService->generateBalanceSheet($asAtDate);

        if ($format === 'excel' || $format === 'csv') {
            $filename = "balance_sheet_{$asAtDate}.csv";
            $handle = fopen('php://output', 'w');
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            fputcsv($handle, ['Balance Sheet Report']);
            fputcsv($handle, ['As at:', $asAtDate]);
            fputcsv($handle, []);
            fputcsv($handle, ['ASSETS']);
            fputcsv($handle, ['Account Name', 'Balance (₦)']);
            foreach ($data['assets'] as $asset) {
                fputcsv($handle, [$asset['account_name'], number_format($asset['balance'], 2)]);
            }
            fputcsv($handle, ['TOTAL ASSETS', number_format($data['total_assets'], 2)]);
            fputcsv($handle, []);
            fputcsv($handle, ['LIABILITIES']);
            fputcsv($handle, ['Account Name', 'Balance (₦)']);
            foreach ($data['liabilities'] as $liability) {
                fputcsv($handle, [$liability['account_name'], number_format($liability['balance'], 2)]);
            }
            fputcsv($handle, ['TOTAL LIABILITIES', number_format($data['total_liabilities'], 2)]);
            fputcsv($handle, []);
            fputcsv($handle, ['EQUITY']);
            fputcsv($handle, ['Account Name', 'Balance (₦)']);
            foreach ($data['equity'] as $eq) {
                fputcsv($handle, [$eq['account_name'], number_format($eq['balance'], 2)]);
            }
            fputcsv($handle, ['TOTAL EQUITY', number_format($data['total_equity'], 2)]);
            fclose($handle);
            exit;
        }

        $pdf = PDF::loadView('reports.financial.balance-sheet-pdf', compact('data', 'asAtDate'));
        return $pdf->download("balance_sheet_{$asAtDate}.pdf");
    }

    private function exportIncomeStatement($format, Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $data = $this->financialService->generateIncomeStatement($startDate, $endDate);

        if ($format === 'excel' || $format === 'csv') {
            $filename = "income_statement_{$startDate}_to_{$endDate}.csv";
            $handle = fopen('php://output', 'w');
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            fputcsv($handle, ['Income Statement Report']);
            fputcsv($handle, ['Period:', $startDate . ' to ' . $endDate]);
            fputcsv($handle, []);
            fputcsv($handle, ['INCOME']);
            fputcsv($handle, ['Account Name', 'Amount (₦)']);
            foreach ($data['income'] as $inc) {
                fputcsv($handle, [$inc['account_name'], number_format($inc['amount'], 2)]);
            }
            fputcsv($handle, ['TOTAL INCOME', number_format($data['total_income'], 2)]);
            fputcsv($handle, []);
            fputcsv($handle, ['EXPENSES']);
            fputcsv($handle, ['Account Name', 'Amount (₦)']);
            foreach ($data['expenses'] as $exp) {
                fputcsv($handle, [$exp['account_name'], number_format($exp['amount'], 2)]);
            }
            fputcsv($handle, ['TOTAL EXPENSES', number_format($data['total_expenses'], 2)]);
            fputcsv($handle, []);
            fputcsv($handle, ['NET PROFIT/LOSS', number_format($data['net_profit'], 2)]);
            fclose($handle);
            exit;
        }

        $pdf = PDF::loadView('reports.financial.income-statement-pdf', compact('data', 'startDate', 'endDate'));
        return $pdf->download("income_statement_{$startDate}_to_{$endDate}.pdf");
    }

    private function exportTrialBalance($format, Request $request)
    {
        $asAtDate = $request->get('as_at_date', now()->format('Y-m-d'));
        $trialBalance = $this->accountingService->getTrialBalance($asAtDate);
        $totalDebit = array_sum(array_column($trialBalance, 'debit'));
        $totalCredit = array_sum(array_column($trialBalance, 'credit'));

        if ($format === 'excel' || $format === 'csv') {
            $filename = "trial_balance_{$asAtDate}.csv";
            $handle = fopen('php://output', 'w');
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            fputcsv($handle, ['Trial Balance Report']);
            fputcsv($handle, ['As at:', $asAtDate]);
            fputcsv($handle, []);
            fputcsv($handle, ['Account Code', 'Account Name', 'Account Type', 'Debit (₦)', 'Credit (₦)', 'Balance (₦)']);
            foreach ($trialBalance as $item) {
                fputcsv($handle, [
                    $item['account_code'],
                    $item['account_name'],
                    $item['account_type'],
                    number_format($item['debit'], 2),
                    number_format($item['credit'], 2),
                    number_format($item['balance'], 2),
                ]);
            }
            fputcsv($handle, []);
            fputcsv($handle, ['TOTALS', '', '', number_format($totalDebit, 2), number_format($totalCredit, 2), '']);
            fclose($handle);
            exit;
        }

        return redirect()->back()->with('info', 'Trial balance PDF export coming soon');
    }

    private function exportCashFlow($format, Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $data = $this->financialService->generateCashFlow($startDate, $endDate);

        if ($format === 'excel' || $format === 'csv') {
            $filename = "cash_flow_{$startDate}_to_{$endDate}.csv";
            $handle = fopen('php://output', 'w');
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            fputcsv($handle, ['Cash Flow Statement']);
            fputcsv($handle, ['Period:', $startDate . ' to ' . $endDate]);
            fputcsv($handle, []);
            fputcsv($handle, ['Operating Activities', number_format($data['operating_activities'], 2)]);
            fputcsv($handle, ['Investing Activities', number_format($data['investing_activities'], 2)]);
            fputcsv($handle, ['Financing Activities', number_format($data['financing_activities'], 2)]);
            fputcsv($handle, ['Net Cash Flow', number_format($data['net_cash_flow'], 2)]);
            fclose($handle);
            exit;
        }

        $pdf = PDF::loadView('reports.financial.cash-flow-pdf', compact('data', 'startDate', 'endDate'));
        return $pdf->download("cash_flow_{$startDate}_to_{$endDate}.pdf");
    }

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
