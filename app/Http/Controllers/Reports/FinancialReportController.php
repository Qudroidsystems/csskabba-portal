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

        // Get data from service
        $balanceSheet = $this->reportService->generateBalanceSheet($asAtDate);

        // Extract data from the service response
        $assets = $balanceSheet['assets'] ?? collect([]);
        $liabilities = $balanceSheet['liabilities'] ?? collect([]);
        $equity = $balanceSheet['equity'] ?? collect([]);
        $totalAssets = $balanceSheet['total_assets'] ?? 0;
        $totalLiabilities = $balanceSheet['total_liabilities'] ?? 0;
        $totalEquity = $balanceSheet['total_equity'] ?? 0;

        if ($request->get('format') === 'pdf') {
            $pdf = PDF::loadView('reports.balance-sheet-pdf', compact('assets', 'liabilities', 'equity', 'totalAssets', 'totalLiabilities', 'totalEquity', 'asAtDate'));
            return $pdf->download("balance_sheet_{$asAtDate}.pdf");
        }

        $pagetitle = 'Balance Sheet';
        return view('reports.balance-sheet', compact(
            'pagetitle',
            'assets',
            'liabilities',
            'equity',
            'totalAssets',
            'totalLiabilities',
            'totalEquity',
            'asAtDate'
        ));
    }

    /**
     * Income Statement (Profit & Loss)
     */
    public function incomeStatement(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Get data from service
        $incomeStatement = $this->reportService->generateIncomeStatement($startDate, $endDate);

        // Extract data
        $income = $incomeStatement['income'] ?? collect([]);
        $expenses = $incomeStatement['expenses'] ?? collect([]);
        $totalIncome = $incomeStatement['total_income'] ?? 0;
        $totalExpenses = $incomeStatement['total_expenses'] ?? 0;
        $netProfit = $incomeStatement['net_profit'] ?? 0;

        if ($request->get('format') === 'pdf') {
            $pdf = PDF::loadView('reports.income-statement-pdf', compact('income', 'expenses', 'totalIncome', 'totalExpenses', 'netProfit', 'startDate', 'endDate'));
            return $pdf->download("income_statement_{$startDate}_to_{$endDate}.pdf");
        }

        $pagetitle = 'Income Statement';
        return view('reports.income-statement', compact(
            'pagetitle',
            'income',
            'expenses',
            'totalIncome',
            'totalExpenses',
            'netProfit',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Trial Balance
     */
    public function trialBalance(Request $request)
    {
        $asAtDate = $request->get('as_at_date', now()->format('Y-m-d'));

        // Get trial balance from service
        $trialBalance = $this->accountingService->getTrialBalance($asAtDate);
        $totalDebit = array_sum(array_column($trialBalance, 'debit'));
        $totalCredit = array_sum(array_column($trialBalance, 'credit'));

        if ($request->get('format') === 'excel') {
            // Excel export logic here
            return redirect()->back()->with('info', 'Excel export coming soon');
        }

        $pagetitle = 'Trial Balance';
        return view('reports.trial-balance', compact('pagetitle', 'trialBalance', 'totalDebit', 'totalCredit', 'asAtDate'));
    }

    /**
     * Cash Flow Statement
     */
    public function cashFlow(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $cashFlow = $this->reportService->generateCashFlow($startDate, $endDate);

        $pagetitle = 'Cash Flow Statement';
        return view('reports.cash-flow', compact('pagetitle', 'cashFlow', 'startDate', 'endDate'));
    }

    /**
     * Debtors List Report with DataTable
     */
    public function debtorsList(Request $request)
    {
        $pagetitle = 'Student Debtors List';

        if ($request->ajax()) {
            $debtors = StudentBillPaymentBook::where('amount_owed', '>', 0)
                ->with(['student', 'schoolBill', 'class', 'term', 'session'])
                ->orderBy('amount_owed', 'desc');

            return DataTables::of($debtors)
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
                    return $row->original_amount ?? $row->adjusted_amount + $row->amount_paid;
                })
                ->addColumn('amount_paid', function($row) {
                    return $row->amount_paid;
                })
                ->addColumn('outstanding', function($row) {
                    return $row->amount_owed;
                })
                ->addColumn('savings', function($row) {
                    return ($row->scholarship_deduction ?? 0) + ($row->discount_deduction ?? 0);
                })
                ->addColumn('action', function($row) {
                    return '<a href="' . route('payment.details', ['studentId' => $row->student_id, 'classId' => $row->class_id, 'termId' => $row->term_id, 'sessionId' => $row->session_id]) . '" class="btn btn-sm btn-info"><i class="ri-eye-line"></i> View</a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('reports.debtors-list', compact('pagetitle'));
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

        $totalScholarshipValue = 0;
        $totalDiscountValue = 0;
        $totalBeneficiaries = 0;

        return view('reports.scholarship-impact', compact('pagetitle', 'totalScholarshipValue', 'totalDiscountValue', 'totalBeneficiaries'));
    }

    /**
     * Export report
     */
    public function export($report, $format, Request $request)
    {
        // Export logic
        return redirect()->back()->with('info', 'Export feature coming soon');
    }

    /**
     * Get debtors data for chart
     */
    public function getDebtorsData(Request $request)
    {
        $classId = $request->input('class_id');

        $query = StudentBillPaymentBook::where('amount_owed', '>', 0);

        if ($classId) {
            $query->where('class_id', $classId);
        }

        $data = $query->get()->groupBy(function($item) {
            $balance = $item->amount_owed;
            if ($balance <= 0) return 'Paid';
            if ($balance <= 10000) return '₦0 - ₦10,000';
            if ($balance <= 50000) return '₦10,001 - ₦50,000';
            if ($balance <= 100000) return '₦50,001 - ₦100,000';
            return 'Above ₦100,000';
        });

        return response()->json([
            'success' => true,
            'labels' => $data->keys(),
            'values' => $data->values()->map->count()
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
                'month' => date('F', mktime(0, 0, 0, $month, 1)),
                'total' => 0
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'year' => $year
        ]);
    }
}
