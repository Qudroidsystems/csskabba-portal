<?php
// app/Http/Controllers/Reports/FinancialReportController.php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Accounting\AccountingService;
use App\Services\Reporting\FinancialReportService;
use App\Models\StudentBillPaymentBook;
use App\Models\Schoolclass;
use App\Models\Schoolterm;
use App\Models\Schoolsession;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

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

        // Get sample data for demonstration - replace with actual data from your accounting system
        $assets = [
            ['account_name' => 'Cash in Hand', 'balance' => 1500000],
            ['account_name' => 'Bank Account', 'balance' => 5000000],
            ['account_name' => 'Accounts Receivable', 'balance' => 2500000],
            ['account_name' => 'Prepaid Expenses', 'balance' => 500000],
            ['account_name' => 'Fixed Assets', 'balance' => 10000000],
        ];

        $liabilities = [
            ['account_name' => 'Accounts Payable', 'balance' => 800000],
            ['account_name' => 'Staff Payables', 'balance' => 600000],
            ['account_name' => 'Unearned Fees', 'balance' => 1200000],
            ['account_name' => 'PAYE Payable', 'balance' => 300000],
            ['account_name' => 'Pension Payable', 'balance' => 200000],
        ];

        $equity = [
            ['account_name' => 'Capital Introduced', 'balance' => 15000000],
            ['account_name' => 'Retained Earnings', 'balance' => 2500000],
        ];

        $totalAssets = array_sum(array_column($assets, 'balance'));
        $totalLiabilities = array_sum(array_column($liabilities, 'balance'));
        $totalEquity = array_sum(array_column($equity, 'balance'));

        $pagetitle = 'Balance Sheet';

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => compact('assets', 'liabilities', 'equity', 'totalAssets', 'totalLiabilities', 'totalEquity')
            ]);
        }

        if ($request->get('format') === 'pdf') {
            $pdf = PDF::loadView('reports.balance-sheet-pdf', compact('assets', 'liabilities', 'equity', 'totalAssets', 'totalLiabilities', 'totalEquity', 'asAtDate'));
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
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Sample data
        $income = [
            ['account_name' => 'School Fees Income', 'amount' => 8500000],
            ['account_name' => 'Development Levy', 'amount' => 1200000],
            ['account_name' => 'ICT Fees', 'amount' => 500000],
            ['account_name' => 'Other Income', 'amount' => 150000],
        ];

        $expenses = [
            ['account_name' => 'Staff Salaries', 'amount' => 3500000],
            ['account_name' => 'Utilities', 'amount' => 300000],
            ['account_name' => 'Maintenance', 'amount' => 200000],
            ['account_name' => 'Teaching Materials', 'amount' => 150000],
            ['account_name' => 'Bank Charges', 'amount' => 50000],
        ];

        $totalIncome = array_sum(array_column($income, 'amount'));
        $totalExpenses = array_sum(array_column($expenses, 'amount'));
        $netProfit = $totalIncome - $totalExpenses;

        $pagetitle = 'Income Statement';

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => compact('income', 'expenses', 'totalIncome', 'totalExpenses', 'netProfit')
            ]);
        }

        if ($request->get('format') === 'pdf') {
            $pdf = PDF::loadView('reports.income-statement-pdf', compact('income', 'expenses', 'totalIncome', 'totalExpenses', 'netProfit', 'startDate', 'endDate'));
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

        // Sample trial balance data
        $trialBalance = [
            ['account_code' => '1010', 'account_name' => 'Cash in Hand', 'account_type' => 'asset', 'debit' => 1500000, 'credit' => 0, 'balance' => 1500000],
            ['account_code' => '1020', 'account_name' => 'Bank Account', 'account_type' => 'asset', 'debit' => 5000000, 'credit' => 0, 'balance' => 5000000],
            ['account_code' => '1030', 'account_name' => 'Accounts Receivable', 'account_type' => 'asset', 'debit' => 2500000, 'credit' => 0, 'balance' => 2500000],
            ['account_code' => '2010', 'account_name' => 'Accounts Payable', 'account_type' => 'liability', 'debit' => 0, 'credit' => 800000, 'balance' => -800000],
            ['account_code' => '4000', 'account_name' => 'School Fees Income', 'account_type' => 'income', 'debit' => 0, 'credit' => 8500000, 'balance' => -8500000],
            ['account_code' => '5000', 'account_name' => 'Staff Salaries', 'account_type' => 'expense', 'debit' => 3500000, 'credit' => 0, 'balance' => 3500000],
        ];

        $totalDebit = array_sum(array_column($trialBalance, 'debit'));
        $totalCredit = array_sum(array_column($trialBalance, 'credit'));

        $pagetitle = 'Trial Balance';

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => compact('trialBalance', 'totalDebit', 'totalCredit')
            ]);
        }

        if ($request->get('format') === 'excel') {
            // Excel export logic
            return response()->json(['message' => 'Excel export will be available soon']);
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
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $cashFlow = [
            'operating_activities' => [
                ['description' => 'Cash received from customers', 'amount' => 8500000],
                ['description' => 'Cash paid to suppliers', 'amount' => -1200000],
                ['description' => 'Cash paid to employees', 'amount' => -3500000],
                ['description' => 'Other operating expenses', 'amount' => -700000],
            ],
            'investing_activities' => [
                ['description' => 'Purchase of equipment', 'amount' => -500000],
            ],
            'financing_activities' => [
                ['description' => 'Capital introduced', 'amount' => 2000000],
            ],
        ];

        $netCashFlow = 8500000 - 1200000 - 3500000 - 700000 - 500000 + 2000000;

        $pagetitle = 'Cash Flow Statement';

        return view('reports.cash-flow', compact('pagetitle', 'cashFlow', 'netCashFlow', 'startDate', 'endDate'));
    }

    /**
     * Debtors List Report with DataTable AJAX
     */
    public function debtorsList(Request $request)
    {
        $pagetitle = 'Student Debtors List';

        if ($request->ajax()) {
            $debtors = StudentBillPaymentBook::where('amount_owed', '>', 0)
                ->with(['student', 'schoolBill', 'class'])
                ->orderBy('amount_owed', 'desc')
                ->get();

            return datatables()->of($debtors)
                ->addIndexColumn()
                ->addColumn('student_name', function($row) {
                    return $row->student->firstname . ' ' . $row->student->lastname;
                })
                ->addColumn('admission_no', function($row) {
                    return $row->student->admissionNo;
                })
                ->addColumn('bill_title', function($row) {
                    return $row->schoolBill->title ?? 'N/A';
                })
                ->addColumn('class_name', function($row) {
                    return optional($row->class)->schoolclass . ' ' . optional($row->class->armRelation)->arm;
                })
                ->addColumn('original_amount', function($row) {
                    return number_format($row->adjusted_amount + $row->amount_paid, 2);
                })
                ->addColumn('amount_paid', function($row) {
                    return number_format($row->amount_paid, 2);
                })
                ->addColumn('outstanding', function($row) {
                    return number_format($row->amount_owed, 2);
                })
                ->addColumn('savings', function($row) {
                    return number_format(($row->scholarship_deduction ?? 0) + ($row->discount_deduction ?? 0), 2);
                })
                ->addColumn('action', function($row) {
                    return '<a href="' . route('payment.details', $row->student_id) . '" class="btn btn-sm btn-info">View</a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $classes = Schoolclass::with('armRelation')->get();
        $terms = Schoolterm::all();
        $sessions = Schoolsession::all();

        return view('reports.debtors-list', compact('pagetitle', 'classes', 'terms', 'sessions'));
    }

    /**
     * Collection Summary Report
     */
    public function collectionSummary(Request $request)
    {
        $pagetitle = 'School Fee Collection Summary';

        $classes = Schoolclass::with('armRelation')->get();
        $terms = Schoolterm::all();
        $sessions = Schoolsession::all();

        return view('reports.collection-summary', compact('pagetitle', 'classes', 'terms', 'sessions'));
    }

    /**
     * Scholarship Impact Report
     */
    public function scholarshipImpact(Request $request)
    {
        $pagetitle = 'Scholarship & Discount Impact Report';

        return view('reports.scholarship-impact', compact('pagetitle'));
    }

    /**
     * Export report
     */
    public function export($report, $format, Request $request)
    {
        // Implement export logic
        return response()->json(['message' => "Exporting $report in $format format"]);
    }

    /**
     * Get debtors data for chart
     */
    public function getDebtorsData(Request $request)
    {
        $data = StudentBillPaymentBook::where('amount_owed', '>', 0)
            ->selectRaw('
                CASE
                    WHEN amount_owed <= 10000 THEN "₦0 - ₦10,000"
                    WHEN amount_owed <= 50000 THEN "₦10,001 - ₦50,000"
                    WHEN amount_owed <= 100000 THEN "₦50,001 - ₦100,000"
                    ELSE "Above ₦100,000"
                END as range,
                COUNT(*) as count
            ')
            ->groupBy('range')
            ->get();

        return response()->json([
            'success' => true,
            'labels' => $data->pluck('range'),
            'values' => $data->pluck('count')
        ]);
    }

    /**
     * Get collection data for chart
     */
    public function getCollectionData(Request $request)
    {
        $year = $request->input('year', date('Y'));

        $data = [];
        for ($month = 1; $month <= 12; $month++) {
            $data[] = [
                'month' => Carbon::create($year, $month, 1)->format('F'),
                'total' => rand(100000, 500000) // Replace with actual data
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'year' => $year
        ]);
    }
}
