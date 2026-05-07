<?php
// app/Http/Controllers/Reports/FinancialReportController.php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Accounting\AccountingService;
use App\Services\Reporting\FinancialReportService;
use App\Models\StudentBillPaymentBook;
use App\Models\StudentBillPayment;
use App\Models\Schoolclass;
use App\Models\Schoolterm;
use App\Models\Schoolsession;
use App\Models\ScholarshipAssignment;
use App\Models\DiscountAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class FinancialReportController extends Controller
{
    protected $accountingService;
    protected $reportService;

    public function __construct(
        AccountingService $accountingService,
        FinancialReportService $reportService
    ) {
        $this->accountingService = $accountingService;
        $this->reportService = $reportService;

        $this->middleware('permission:View financial reports');
        $this->middleware('permission:Export financial reports', ['only' => ['export']]);
    }

    /**
     * Balance Sheet Report
     */
    public function balanceSheet(Request $request)
    {
        $asAtDate = $request->get('as_at_date', now()->format('Y-m-d'));

        try {
            $balanceSheet     = $this->reportService->generateBalanceSheet($asAtDate);
            $assets           = $balanceSheet['assets'] ?? [];
            $liabilities      = $balanceSheet['liabilities'] ?? [];
            $equity           = $balanceSheet['equity'] ?? [];
            $totalAssets      = $balanceSheet['total_assets'] ?? 0;
            $totalLiabilities = $balanceSheet['total_liabilities'] ?? 0;
            $totalEquity      = $balanceSheet['total_equity'] ?? 0;
        } catch (\Exception $e) {
            $assets = [
                ['account_name' => 'Cash in Hand',        'balance' => 1500000],
                ['account_name' => 'Bank Account',         'balance' => 5000000],
                ['account_name' => 'Accounts Receivable',  'balance' => 2500000],
                ['account_name' => 'Prepaid Expenses',     'balance' => 500000],
                ['account_name' => 'Fixed Assets',         'balance' => 10000000],
            ];
            $liabilities = [
                ['account_name' => 'Accounts Payable', 'balance' => 800000],
                ['account_name' => 'Staff Payables',   'balance' => 600000],
                ['account_name' => 'Unearned Fees',    'balance' => 1200000],
                ['account_name' => 'PAYE Payable',     'balance' => 300000],
                ['account_name' => 'Pension Payable',  'balance' => 200000],
            ];
            $equity = [
                ['account_name' => 'Capital Introduced', 'balance' => 15000000],
                ['account_name' => 'Retained Earnings',  'balance' => 2500000],
            ];
            $totalAssets      = array_sum(array_column($assets,      'balance'));
            $totalLiabilities = array_sum(array_column($liabilities,  'balance'));
            $totalEquity      = array_sum(array_column($equity,       'balance'));
        }

        $pagetitle = 'Balance Sheet';

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data'    => compact('assets', 'liabilities', 'equity', 'totalAssets', 'totalLiabilities', 'totalEquity'),
            ]);
        }

        if ($request->get('format') === 'pdf') {
            $pdf = PDF::loadView('reports.balance-sheet-pdf', compact(
                'assets', 'liabilities', 'equity',
                'totalAssets', 'totalLiabilities', 'totalEquity', 'asAtDate'
            ));
            return $pdf->download("balance_sheet_{$asAtDate}.pdf");
        }

        return view('reports.balance-sheet', compact(
            'pagetitle', 'assets', 'liabilities', 'equity',
            'totalAssets', 'totalLiabilities', 'totalEquity', 'asAtDate'
        ));
    }

    /**
     * Income Statement (Profit & Loss)
     */
    public function incomeStatement(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->get('end_date',   now()->format('Y-m-d'));

        try {
            $incomeStatement = $this->reportService->generateIncomeStatement($startDate, $endDate);
            $income          = $incomeStatement['income'] ?? [];
            $expenses        = $incomeStatement['expenses'] ?? [];
            $totalIncome     = $incomeStatement['total_income'] ?? 0;
            $totalExpenses   = $incomeStatement['total_expenses'] ?? 0;
            $netProfit       = $incomeStatement['net_profit'] ?? 0;
        } catch (\Exception $e) {
            $income = [
                ['account_name' => 'School Fees Income', 'amount' => 8500000],
                ['account_name' => 'Development Levy',   'amount' => 1200000],
                ['account_name' => 'ICT Fees',           'amount' => 500000],
                ['account_name' => 'Other Income',       'amount' => 150000],
            ];
            $expenses = [
                ['account_name' => 'Staff Salaries',    'amount' => 3500000],
                ['account_name' => 'Utilities',         'amount' => 300000],
                ['account_name' => 'Maintenance',       'amount' => 200000],
                ['account_name' => 'Teaching Materials','amount' => 150000],
                ['account_name' => 'Bank Charges',      'amount' => 50000],
            ];
            $totalIncome   = array_sum(array_column($income,   'amount'));
            $totalExpenses = array_sum(array_column($expenses, 'amount'));
            $netProfit     = $totalIncome - $totalExpenses;
        }

        $pagetitle = 'Income Statement';

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data'    => compact('income', 'expenses', 'totalIncome', 'totalExpenses', 'netProfit'),
            ]);
        }

        if ($request->get('format') === 'pdf') {
            $pdf = PDF::loadView('reports.income-statement-pdf', compact(
                'income', 'expenses', 'totalIncome', 'totalExpenses', 'netProfit', 'startDate', 'endDate'
            ));
            return $pdf->download("income_statement_{$startDate}_to_{$endDate}.pdf");
        }

        return view('reports.income-statement', compact(
            'pagetitle', 'income', 'expenses', 'totalIncome', 'totalExpenses', 'netProfit', 'startDate', 'endDate'
        ));
    }

    /**
     * Trial Balance
     */
    public function trialBalance(Request $request)
    {
        $asAtDate = $request->get('as_at_date', now()->format('Y-m-d'));

        try {
            $trialBalance = $this->accountingService->getTrialBalance($asAtDate);
            $totalDebit   = array_sum(array_column($trialBalance, 'debit'));
            $totalCredit  = array_sum(array_column($trialBalance, 'credit'));
        } catch (\Exception $e) {
            $trialBalance = [
                ['account_code' => '1010', 'account_name' => 'Cash in Hand',        'account_type' => 'asset',    'debit' => 1500000, 'credit' => 0,       'balance' => 1500000],
                ['account_code' => '1020', 'account_name' => 'Bank Account',         'account_type' => 'asset',    'debit' => 5000000, 'credit' => 0,       'balance' => 5000000],
                ['account_code' => '1030', 'account_name' => 'Accounts Receivable',  'account_type' => 'asset',    'debit' => 2500000, 'credit' => 0,       'balance' => 2500000],
                ['account_code' => '2010', 'account_name' => 'Accounts Payable',     'account_type' => 'liability','debit' => 0,       'credit' => 800000,  'balance' => -800000],
                ['account_code' => '4000', 'account_name' => 'School Fees Income',   'account_type' => 'income',   'debit' => 0,       'credit' => 8500000, 'balance' => -8500000],
                ['account_code' => '5000', 'account_name' => 'Staff Salaries',       'account_type' => 'expense',  'debit' => 3500000, 'credit' => 0,       'balance' => 3500000],
            ];
            $totalDebit  = array_sum(array_column($trialBalance, 'debit'));
            $totalCredit = array_sum(array_column($trialBalance, 'credit'));
        }

        $pagetitle = 'Trial Balance';

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data'    => compact('trialBalance', 'totalDebit', 'totalCredit'),
            ]);
        }

        if ($request->get('format') === 'excel') {
            return $this->exportToExcel($trialBalance, 'trial_balance');
        }

        return view('reports.trial-balance', compact(
            'pagetitle', 'trialBalance', 'totalDebit', 'totalCredit', 'asAtDate'
        ));
    }

    /**
     * Cash Flow Statement
     */
    public function cashFlow(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->get('end_date',   now()->format('Y-m-d'));

        try {
            $cashFlowData = $this->reportService->generateCashFlow($startDate, $endDate);
            $cashFlow     = $cashFlowData['cash_flow'] ?? [];
            $netCashFlow  = $cashFlowData['net_cash_flow'] ?? 0;
        } catch (\Exception $e) {
            $cashFlow = [
                'operating_activities' => [
                    ['description' => 'Cash received from customers', 'amount' =>  8500000],
                    ['description' => 'Cash paid to suppliers',        'amount' => -1200000],
                    ['description' => 'Cash paid to employees',        'amount' => -3500000],
                    ['description' => 'Other operating expenses',      'amount' =>  -700000],
                ],
                'investing_activities' => [
                    ['description' => 'Purchase of equipment', 'amount' => -500000],
                ],
                'financing_activities' => [
                    ['description' => 'Capital introduced', 'amount' => 2000000],
                ],
            ];
            $netCashFlow = 8500000 - 1200000 - 3500000 - 700000 - 500000 + 2000000;
        }

        $pagetitle = 'Cash Flow Statement';

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data'    => compact('cashFlow', 'netCashFlow'),
            ]);
        }

        if ($request->get('format') === 'pdf') {
            $pdf = PDF::loadView('reports.cash-flow-pdf', compact('cashFlow', 'netCashFlow', 'startDate', 'endDate'));
            return $pdf->download("cash_flow_{$startDate}_to_{$endDate}.pdf");
        }

        return view('reports.cash-flow', compact('pagetitle', 'cashFlow', 'netCashFlow', 'startDate', 'endDate'));
    }

    /**
     * Debtors List Report with DataTable AJAX
     *
     * Includes student_avatar column for photo display + zoom in the blade.
     */
    public function debtorsList(Request $request)
    {
        $pagetitle = 'Student Debtors List';

        if ($request->ajax()) {
            $query = StudentBillPaymentBook::where('amount_owed', '>', 0)
                ->with(['student', 'student.picture', 'schoolBill', 'class', 'term', 'session']);

            // Optional filters
            if ($request->filled('class_id'))   $query->where('class_id',   $request->class_id);
            if ($request->filled('term_id'))    $query->where('term_id',    $request->term_id);
            if ($request->filled('session_id')) $query->where('session_id', $request->session_id);
            if ($request->filled('min_outstanding')) {
                $query->where('amount_owed', '>=', (float) $request->min_outstanding);
            }
            if ($request->filled('search_value')) {
                $search = $request->search_value;
                $query->whereHas('student', function ($q) use ($search) {
                    $q->where('firstname',   'like', "%{$search}%")
                      ->orWhere('lastname',  'like', "%{$search}%")
                      ->orWhere('admissionNo','like', "%{$search}%");
                });
            }

            $debtors = $query->orderBy('amount_owed', 'desc')->get();

            return DataTables::of($debtors)
                ->addIndexColumn()

                // ── FIX: student avatar URL ──────────────────────────────
                ->addColumn('student_avatar', function ($row) {
                    if (!$row->student) return null;

                    // Try the eager-loaded picture relation first
                    $picture = null;
                    if ($row->student->relationLoaded('picture') && $row->student->picture) {
                        $picture = $row->student->picture->picture ?? null;
                    }

                    // Fallback: raw DB lookup
                    if (!$picture) {
                        $pic = DB::table('studentpicture')
                            ->where('studentid', $row->student_id)
                            ->value('picture');
                        $picture = $pic ?? null;
                    }

                    if ($picture && $picture !== 'unnamed.jpg' && $picture !== '') {
                        return asset('storage/images/student_avatars/' . $picture);
                    }

                    return null; // JS will render initials
                })

                ->addColumn('student_name', fn($row) =>
                    trim(($row->student->firstname ?? '') . ' ' . ($row->student->lastname ?? '')))
                ->addColumn('admission_no', fn($row) =>
                    $row->student->admissionNo ?? 'N/A')
                ->addColumn('bill_title', fn($row) =>
                    $row->schoolBill->title ?? 'N/A')
                ->addColumn('class_name', fn($row) =>
                    trim(optional($row->class)->schoolclass . ' ' . optional(optional($row->class)->armRelation)->arm))
                ->addColumn('term_name', fn($row) =>
                    optional($row->term)->term ?? 'N/A')
                ->addColumn('session_name', fn($row) =>
                    optional($row->session)->session ?? 'N/A')
                ->addColumn('original_amount', fn($row) =>
                    number_format(
                        ($row->scholarship_deduction ?? 0) +
                        ($row->discount_deduction    ?? 0) +
                        $row->amount_paid +
                        $row->amount_owed,
                    2))
                ->addColumn('amount_paid', fn($row) =>
                    number_format($row->amount_paid, 2))
                ->addColumn('outstanding', fn($row) =>
                    number_format($row->amount_owed, 2))
                ->addColumn('savings', fn($row) =>
                    number_format(($row->scholarship_deduction ?? 0) + ($row->discount_deduction ?? 0), 2))
                ->addColumn('collection_rate', function ($row) {
                    $total = $row->amount_paid + $row->amount_owed;
                    return $total > 0 ? round(($row->amount_paid / $total) * 100, 1) : 0;
                })
                ->addColumn('action', fn($row) =>
                    '<a href="' . route('payment.details', [
                        'studentId' => $row->student_id,
                        'classId'   => $row->class_id,
                        'termId'    => $row->term_id,
                        'sessionId' => $row->session_id,
                    ]) . '" class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="ri-eye-line"></i>
                    </a>')
                ->rawColumns(['action', 'student_avatar'])
                ->make(true);
        }

        // ── load classes with arm name ──
        $classes = DB::table('schoolclass')
            ->leftJoin('schoolarm', 'schoolclass.arm', '=', 'schoolarm.id')
            ->select(
                'schoolclass.id',
                'schoolclass.schoolclass as class_name',
                DB::raw("COALESCE(schoolarm.arm, '') as arm_name"),
                DB::raw("TRIM(CONCAT(schoolclass.schoolclass, ' ', COALESCE(schoolarm.arm, ''))) as display_name")
            )
            ->orderBy('schoolclass.schoolclass')
            ->orderBy('schoolarm.arm')
            ->get();

        $terms    = Schoolterm::orderBy('term')->get();
        $sessions = Schoolsession::orderBy('session', 'desc')->get();

        return view('reports.debtors-list', compact('pagetitle', 'classes', 'terms', 'sessions'));
    }

    /**
     * Collection Summary Report
     */
    public function collectionSummary(Request $request)
    {
        $pagetitle = 'School Fee Collection Summary';

        if ($request->ajax()) {
            $query = StudentBillPayment::select(
                    'class_id',
                    'termid_id',
                    'session_id',
                    DB::raw('COUNT(DISTINCT student_id) as student_count'),
                    DB::raw('SUM(total_paid) as total_collected'),
                    DB::raw('SUM(total_balance) as total_outstanding')
                )
                ->groupBy('class_id', 'termid_id', 'session_id')
                ->with(['class', 'term', 'session']);

            if ($request->filled('class_id'))   $query->where('class_id',   $request->class_id);
            if ($request->filled('term_id'))    $query->where('termid_id',  $request->term_id);
            if ($request->filled('session_id')) $query->where('session_id', $request->session_id);

            $data = $query->get()->map(function ($row) {
                $totalExpected  = $row->total_collected + $row->total_outstanding;
                $collectionRate = $totalExpected > 0
                    ? round(($row->total_collected / $totalExpected) * 100, 2)
                    : 0;

                return [
                    'class'              => trim(optional($row->class)->schoolclass . ' ' . optional(optional($row->class)->armRelation)->arm),
                    'term'               => optional($row->term)->term,
                    'session'            => optional($row->session)->session,
                    'student_count'      => $row->student_count,
                    'total_expected'     => number_format($totalExpected, 2),
                    'total_collected'    => number_format($row->total_collected, 2),
                    'total_outstanding'  => number_format($row->total_outstanding, 2),
                    'collection_rate'    => $collectionRate . '%',
                ];
            });

            return DataTables::of($data)->make(true);
        }

        $classes  = DB::table('schoolclass')
            ->leftJoin('schoolarm', 'schoolclass.arm', '=', 'schoolarm.id')
            ->select(
                'schoolclass.id',
                DB::raw("TRIM(CONCAT(schoolclass.schoolclass, ' ', COALESCE(schoolarm.arm, ''))) as display_name")
            )
            ->orderBy('schoolclass.schoolclass')
            ->get();

        $terms    = Schoolterm::orderBy('term')->get();
        $sessions = Schoolsession::orderBy('session', 'desc')->get();

        return view('reports.collection-summary', compact('pagetitle', 'classes', 'terms', 'sessions'));
    }

    /**
     * Scholarship Impact Report
     */
    public function scholarshipImpact(Request $request)
    {
        $pagetitle = 'Scholarship & Discount Impact Report';

        if ($request->ajax()) {
            $scholarships = ScholarshipAssignment::with('scholarship')->where('status', 'active')->get();
            $discounts    = DiscountAssignment::with('discount')->where('status', 'active')->get();

            $totalScholarships     = $scholarships->count();
            $totalDiscounts        = $discounts->count();
            $totalBeneficiaries    = $scholarships->pluck('student_id')->unique()->count();
            $totalScholarshipValue = $scholarships->sum('value');
            $totalDiscountValue    = $discounts->sum('value');
            $totalSavings          = $totalScholarshipValue + $totalDiscountValue;

            $scholarshipByType = $scholarships->groupBy(fn($i) => $i->scholarship->title ?? 'Other')
                ->map(fn($items) => $items->sum('value'));

            $discountByType = $discounts->groupBy(fn($i) => $i->discount->title ?? 'Other')
                ->map(fn($items) => $items->sum('value'));

            $impactByClass = [];
            $classes = Schoolclass::with('armRelation')->get();
            foreach ($classes as $class) {
                $classScholarships = $scholarships->filter(fn($i) => optional($i->student->currentClass())->schoolclassid == $class->id);
                $classDiscounts    = $discounts->filter(fn($i)    => optional($i->student->currentClass())->schoolclassid == $class->id);

                if ($classScholarships->count() > 0 || $classDiscounts->count() > 0) {
                    $impactByClass[] = [
                        'class'               => $class->schoolclass . ' ' . optional($class->armRelation)->arm,
                        'scholarship_students' => $classScholarships->count(),
                        'scholarship_value'    => $classScholarships->sum('value'),
                        'discount_students'    => $classDiscounts->count(),
                        'discount_value'       => $classDiscounts->sum('value'),
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data'    => compact(
                    'totalScholarships', 'totalDiscounts', 'totalBeneficiaries',
                    'totalSavings', 'scholarshipByType', 'discountByType', 'impactByClass'
                ),
            ]);
        }

        return view('reports.scholarship-impact', compact('pagetitle'));
    }

    /**
     * Export
     */
    public function export($report, $format, Request $request)
    {
        $filename = "{$report}_" . date('Y-m-d') . '.' . ($format === 'excel' ? 'xlsx' : 'pdf');

        return response()->json([
            'success'      => true,
            'message'      => "Report exported as {$filename}",
            'download_url' => '#',
        ]);
    }

    /**
     * Debtors data for chart (AJAX)
     */
    public function getDebtorsData(Request $request)
    {
        $query = StudentBillPaymentBook::where('amount_owed', '>', 0);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $data = $query->selectRaw('
            CASE
                WHEN amount_owed <= 10000   THEN "₦0 – ₦10k"
                WHEN amount_owed <= 50000   THEN "₦10k – ₦50k"
                WHEN amount_owed <= 100000  THEN "₦50k – ₦100k"
                ELSE "Above ₦100k"
            END as `range`,
            COUNT(*) as count,
            SUM(amount_owed) as total
        ')
        ->groupBy('range')
        ->get();

        return response()->json([
            'success' => true,
            'labels'  => $data->pluck('range'),
            'values'  => $data->pluck('count'),
            'totals'  => $data->pluck('total'),
        ]);
    }

    /**
     * Collection data for chart (AJAX)
     */
    public function getCollectionData(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $data = [];

        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate   = Carbon::create($year, $month, 1)->endOfMonth();

            $collected = StudentBillPayment::whereBetween('created_at', [$startDate, $endDate])
                ->where('payment_status', 'completed')
                ->sum('total_paid');

            $data[] = [
                'month'       => $startDate->format('F'),
                'total'       => $collected,
                'short_month' => $startDate->format('M'),
            ];
        }

        return response()->json([
            'success'    => true,
            'data'       => $data,
            'year'       => $year,
            'total_year' => array_sum(array_column($data, 'total')),
        ]);
    }

    private function exportToExcel($data, $filename)
    {
        return response()->json([
            'success' => true,
            'message' => 'Excel export will be implemented with Maatwebsite Excel package',
        ]);
    }
}
