<?php
// app/Http/Controllers/Reports/FinancialReportController.php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Accounting\AccountingService;
use App\Services\Reporting\FinancialReportService;
use App\Models\StudentBillPayment;
use App\Models\StudentBillPaymentBook;
use App\Models\OnlinePayment;
use App\Models\ScholarshipAssignment;
use App\Models\DiscountAssignment;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinancialReportExport;

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
     * Balance Sheet Report with AJAX data.
     */
    public function balanceSheet(Request $request)
    {
        $asAtDate = $request->get('as_at_date', now()->format('Y-m-d'));

        if ($request->ajax()) {
            $balanceSheet = $this->reportService->generateBalanceSheet($asAtDate);
            return response()->json([
                'success' => true,
                'data' => $balanceSheet
            ]);
        }

        $balanceSheet = $this->reportService->generateBalanceSheet($asAtDate);

        if ($request->get('format') === 'pdf') {
            $pdf = PDF::loadView('reports.balance-sheet-pdf', compact('balanceSheet'));
            return $pdf->download("balance_sheet_{$asAtDate}.pdf");
        }

        $pagetitle = 'Balance Sheet';
        return view('reports.balance-sheet', compact('pagetitle', 'balanceSheet', 'asAtDate'));
    }

    /**
     * Income Statement (P&L) with AJAX data.
     */
    public function incomeStatement(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        if ($request->ajax()) {
            $incomeStatement = $this->reportService->generateIncomeStatement($startDate, $endDate);
            return response()->json([
                'success' => true,
                'data' => $incomeStatement
            ]);
        }

        $incomeStatement = $this->reportService->generateIncomeStatement($startDate, $endDate);

        if ($request->get('format') === 'pdf') {
            $pdf = PDF::loadView('reports.income-statement-pdf', compact('incomeStatement'));
            return $pdf->download("income_statement_{$startDate}_to_{$endDate}.pdf");
        }

        $pagetitle = 'Income Statement';
        return view('reports.income-statement', compact('pagetitle', 'incomeStatement', 'startDate', 'endDate'));
    }

    /**
     * Trial Balance with AJAX data.
     */
    public function trialBalance(Request $request)
    {
        $asAtDate = $request->get('as_at_date', now()->format('Y-m-d'));

        if ($request->ajax()) {
            $trialBalance = $this->accountingService->getTrialBalance($asAtDate);
            return response()->json([
                'success' => true,
                'data' => $trialBalance
            ]);
        }

        $trialBalance = $this->accountingService->getTrialBalance($asAtDate);
        $totalDebit = array_sum(array_column($trialBalance, 'debit'));
        $totalCredit = array_sum(array_column($trialBalance, 'credit'));

        if ($request->get('format') === 'excel') {
            return Excel::download(new FinancialReportExport($trialBalance, 'trial_balance'), 'trial_balance.xlsx');
        }

        $pagetitle = 'Trial Balance';
        return view('reports.trial-balance', compact('pagetitle', 'trialBalance', 'totalDebit', 'totalCredit', 'asAtDate'));
    }

    /**
     * Cash Flow Statement with AJAX data.
     */
    public function cashFlow(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        if ($request->ajax()) {
            $cashFlow = $this->reportService->generateCashFlow($startDate, $endDate);
            return response()->json([
                'success' => true,
                'data' => $cashFlow
            ]);
        }

        $cashFlow = $this->reportService->generateCashFlow($startDate, $endDate);

        $pagetitle = 'Cash Flow Statement';
        return view('reports.cash-flow', compact('pagetitle', 'cashFlow', 'startDate', 'endDate'));
    }

    /**
     * Debtors List Report with AJAX DataTable.
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
                    return $row->schoolBill->title;
                })
                ->addColumn('class_name', function($row) {
                    return optional($row->class)->schoolclass . ' ' . optional($row->class->armRelation)->arm;
                })
                ->addColumn('original_amount', function($row) {
                    return '₦' . number_format($row->original_amount ?? $row->adjusted_amount + $row->amount_paid, 2);
                })
                ->addColumn('amount_paid', function($row) {
                    return '₦' . number_format($row->amount_paid, 2);
                })
                ->addColumn('outstanding', function($row) {
                    return '₦' . number_format($row->amount_owed, 2);
                })
                ->addColumn('savings', function($row) {
                    return '₦' . number_format(($row->scholarship_deduction ?? 0) + ($row->discount_deduction ?? 0), 2);
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
     * Collection Summary Report with AJAX.
     */
    public function collectionSummary(Request $request)
    {
        $pagetitle = 'School Fee Collection Summary';

        if ($request->ajax()) {
            $query = StudentBillPayment::select(
                'class_id',
                'termid_id',
                'session_id',
                DB::raw('SUM(total_paid) as total_collected'),
                DB::raw('COUNT(DISTINCT student_id) as student_count'),
                DB::raw('SUM(total_balance) as total_outstanding')
            )
            ->groupBy('class_id', 'termid_id', 'session_id')
            ->with(['class', 'term', 'session']);

            $classes = Schoolclass::with('armRelation')->get();
            $terms = Schoolterm::all();
            $sessions = Schoolsession::all();

            $data = $query->get()->map(function($row) {
                return [
                    'class' => optional($row->class)->schoolclass . ' ' . optional($row->class->armRelation)->arm,
                    'term' => optional($row->term)->term,
                    'session' => optional($row->session)->session,
                    'student_count' => $row->student_count,
                    'total_collected' => '₦' . number_format($row->total_collected, 2),
                    'total_outstanding' => '₦' . number_format($row->total_outstanding, 2),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        }

        return view('reports.collection-summary', compact('pagetitle'));
    }

    /**
     * Scholarship Impact Report with AJAX.
     */
    public function scholarshipImpact(Request $request)
    {
        $pagetitle = 'Scholarship & Discount Impact Report';

        if ($request->ajax()) {
            $scholarshipData = ScholarshipAssignment::where('status', 'active')
                ->select(
                    DB::raw('COUNT(*) as total_students'),
                    DB::raw('SUM(value) as total_value'),
                    DB::raw('value_type'),
                    DB::raw('YEAR(created_at) as year')
                )
                ->groupBy('value_type', 'year')
                ->get();

            $discountData = DiscountAssignment::where('status', 'active')
                ->select(
                    DB::raw('COUNT(*) as total_students'),
                    DB::raw('SUM(value) as total_value'),
                    DB::raw('value_type'),
                    DB::raw('YEAR(created_at) as year')
                )
                ->groupBy('value_type', 'year')
                ->get();

            return response()->json([
                'success' => true,
                'scholarship' => $scholarshipData,
                'discount' => $discountData
            ]);
        }

        $totalScholarshipValue = ScholarshipAssignment::where('status', 'active')->sum('value');
        $totalDiscountValue = DiscountAssignment::where('status', 'active')->sum('value');
        $totalBeneficiaries = ScholarshipAssignment::where('status', 'active')->distinct('student_id')->count('student_id');

        return view('reports.scholarship-impact', compact(
            'pagetitle', 'totalScholarshipValue', 'totalDiscountValue', 'totalBeneficiaries'
        ));
    }

    /**
     * Export report to various formats.
     */
    public function export($report, $format, Request $request)
    {
        $reportData = [];

        switch ($report) {
            case 'balance-sheet':
                $asAtDate = $request->get('as_at_date', now()->format('Y-m-d'));
                $reportData = $this->reportService->generateBalanceSheet($asAtDate);
                $filename = "balance_sheet_{$asAtDate}";
                break;
            case 'income-statement':
                $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
                $endDate = $request->get('end_date', now()->format('Y-m-d'));
                $reportData = $this->reportService->generateIncomeStatement($startDate, $endDate);
                $filename = "income_statement_{$startDate}_to_{$endDate}";
                break;
            case 'debtors':
                $reportData = StudentBillPaymentBook::where('amount_owed', '>', 0)
                    ->with(['student', 'schoolBill'])
                    ->get();
                $filename = "debtors_list_" . date('Y-m-d');
                break;
            default:
                return response()->json(['error' => 'Report not found'], 404);
        }

        if ($format === 'pdf') {
            $pdf = PDF::loadView("reports.{$report}-pdf", ['data' => $reportData]);
            return $pdf->download("{$filename}.pdf");
        }

        if ($format === 'excel') {
            return Excel::download(new FinancialReportExport($reportData, $report), "{$filename}.xlsx");
        }

        return response()->json(['success' => true, 'data' => $reportData]);
    }

    /**
     * Get debtors data for chart (AJAX).
     */
    public function getDebtorsData(Request $request)
    {
        $classId = $request->input('class_id');

        $query = StudentBillPaymentBook::where('amount_owed', '> 0')
            ->with('student');

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
     * Get collection data for chart (AJAX).
     */
    public function getCollectionData(Request $request)
    {
        $year = $request->input('year', date('Y'));

        $data = [];
        for ($month = 1; $month <= 12; $month++) {
            $total = OnlinePayment::whereYear('payment_date', $year)
                ->whereMonth('payment_date', $month)
                ->where('status', 'success')
                ->sum('amount');

            $data[] = [
                'month' => date('F', mktime(0, 0, 0, $month, 1)),
                'total' => $total
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'year' => $year
        ]);
    }
}
