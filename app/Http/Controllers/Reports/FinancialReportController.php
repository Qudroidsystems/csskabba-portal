<?php
// app/Http/Controllers/Reports/FinancialReportController.php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reporting\FinancialReportService;
use App\Services\Accounting\AccountingService;
use App\Models\SchoolInformation;
use App\Models\ScholarshipAssignment;
use App\Models\DiscountAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

        // Permissions
        $this->middleware('permission:View financial reports')->only([
            'debtorsList',
            'balanceSheet',
            'incomeStatement',
            'trialBalance',
            'cashFlow',
            'collectionSummary',
            'scholarshipImpact'
        ]);

        $this->middleware('permission:Export financial reports')->only([
            'export'
        ]);
    }

    /**
     * Debtors List Report
     *
     * One row per student (per class/term/session enrolment), aggregated
     * across every bill they owe on. Scholarship/discount assignments are
     * attached per student so the UI can badge them without N+1 queries.
     */
    public function debtorsList(Request $request)
    {
        $pagetitle = 'Student Debtors List';
        $schoolInfo = SchoolInformation::getActiveSchool();

        if ($request->ajax()) {
            return response()->json([
                'data' => $this->buildDebtorsDataset($request)->values(),
            ]);
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
     * Build the grouped debtors dataset shared by the AJAX endpoint and the
     * PDF/CSV export, so both always show identical numbers for the same
     * filters.
     *
     * Grouping key is student + class + term + session (not just student),
     * because a student can legitimately have separate outstanding balances
     * across different enrolments/terms and those should not be merged.
     */
    private function buildDebtorsDataset(Request $request): Collection
    {
        $query = DB::table('student_bill_payment_book as sbpb')
            ->join('studentRegistration as s', 's.id', '=', 'sbpb.student_id')
            ->leftJoin('school_bill as sb', 'sb.id', '=', 'sbpb.school_bill_id')
            ->leftJoin('schoolclass as sc', 'sc.id', '=', 'sbpb.class_id')
            ->leftJoin('schoolarm as sa', 'sa.id', '=', 'sc.arm')
            ->leftJoin('schoolterm as st', 'st.id', '=', 'sbpb.term_id')
            ->leftJoin('schoolsession as ss', 'ss.id', '=', 'sbpb.session_id')
            ->leftJoin('studentpicture as sp', 'sp.studentid', '=', 's.id')
            ->where('sbpb.amount_owed', '>', 0)
            ->select(
                's.id as student_id',
                DB::raw("CONCAT(s.firstname, ' ', s.lastname) as student_name"),
                's.admissionNo as admission_no',
                'sp.picture as avatar',
                'sb.id as bill_id',
                'sb.title as bill_title',
                'sc.id as class_id',
                DB::raw("TRIM(CONCAT(sc.schoolclass, ' ', COALESCE(sa.arm, ''))) as class_name"),
                'st.id as term_id',
                'st.term as term_name',
                'ss.id as session_id',
                'ss.session as session_name',
                'sbpb.original_amount',
                'sbpb.amount_paid',
                'sbpb.amount_owed as outstanding',
                DB::raw('(sbpb.scholarship_deduction + sbpb.discount_deduction) as savings')
            );

        if ($request->filled('class_id')) $query->where('sbpb.class_id', $request->class_id);
        if ($request->filled('term_id')) $query->where('sbpb.term_id', $request->term_id);
        if ($request->filled('session_id')) $query->where('sbpb.session_id', $request->session_id);
        if ($request->filled('min_outstanding')) $query->where('sbpb.amount_owed', '>=', $request->min_outstanding);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('s.firstname', 'like', "%{$search}%")
                  ->orWhere('s.lastname', 'like', "%{$search}%")
                  ->orWhere('s.admissionNo', 'like', "%{$search}%")
                  ->orWhereRaw("CONCAT(s.firstname, ' ', s.lastname) LIKE ?", ["%{$search}%"]);
            });
        }

        $rows = $query->orderBy('sbpb.amount_owed', 'desc')->get();

        if ($rows->isEmpty()) {
            return collect();
        }

        $studentIds = $rows->pluck('student_id')->unique()->values();
        $now = now();

        $scholarships = ScholarshipAssignment::whereIn('student_id', $studentIds)
            ->where('status', 'active')
            ->where('effective_from', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $now);
            })
            ->with('scholarship')
            ->get()
            ->keyBy('student_id');

        $discounts = DiscountAssignment::whereIn('student_id', $studentIds)
            ->where('status', 'active')
            ->where('effective_from', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $now);
            })
            ->with('discount')
            ->get()
            ->groupBy('student_id');

        return $rows
            ->groupBy(fn ($r) => $r->student_id . '_' . $r->class_id . '_' . $r->term_id . '_' . $r->session_id)
            ->map(function ($bills) use ($scholarships, $discounts) {
                $first            = $bills->first();
                $totalOriginal    = (float) $bills->sum('original_amount');
                $totalPaid        = (float) $bills->sum('amount_paid');
                $totalOutstanding = (float) $bills->sum('outstanding');
                $totalSavings     = (float) $bills->sum('savings');
                $rate             = $totalOriginal > 0 ? round(($totalPaid / $totalOriginal) * 100, 1) : 0;

                $sch  = $scholarships->get($first->student_id);
                $disc = $discounts->get($first->student_id, collect());

                return [
                    'student_id'      => $first->student_id,
                    'student_name'    => $first->student_name,
                    'admission_no'    => $first->admission_no,
                    'avatar'          => $first->avatar,
                    'class_id'        => $first->class_id,
                    'class_name'      => $first->class_name,
                    'term_id'         => $first->term_id,
                    'term_name'       => $first->term_name,
                    'session_id'      => $first->session_id,
                    'session_name'    => $first->session_name,
                    'original_amount' => $totalOriginal,
                    'amount_paid'     => $totalPaid,
                    'outstanding'     => $totalOutstanding,
                    'savings'         => $totalSavings,
                    'collection_rate' => $rate,
                    'bill_count'      => $bills->count(),
                    'scholarship'     => $sch ? [
                        'title'      => $sch->scholarship->title ?? 'Scholarship',
                        'value'      => $sch->value,
                        'value_type' => $sch->value_type,
                    ] : null,
                    'discounts' => $disc->map(fn ($d) => [
                        'title'      => $d->discount->title ?? 'Discount',
                        'value'      => $d->value,
                        'value_type' => $d->value_type,
                    ])->values(),
                    'bills' => $bills->map(fn ($b) => [
                        'bill_id'         => $b->bill_id,
                        'title'           => $b->bill_title,
                        'original_amount' => (float) $b->original_amount,
                        'amount_paid'     => (float) $b->amount_paid,
                        'outstanding'     => (float) $b->outstanding,
                        'savings'         => (float) $b->savings,
                    ])->values(),
                ];
            })
            ->sortByDesc('outstanding');
    }

    /**
     * Balance Sheet
     */
    public function balanceSheet(Request $request)
    {
        $pagetitle = 'Balance Sheet';
        $asAtDate = $request->get('as_at_date', now()->format('Y-m-d'));
        $schoolInfo = SchoolInformation::getActiveSchool();

        if ($request->ajax()) {
            $data = $this->financialService->generateBalanceSheet($asAtDate);
            return response()->json(['success' => true, 'data' => $data]);
        }

        if ($request->get('format') === 'pdf') {
            $data = $this->financialService->generateBalanceSheet($asAtDate);
            $pdf = PDF::loadView('reports.financial.pdf.balance-sheet', compact('data', 'asAtDate', 'schoolInfo'));
            $pdf->setPaper('a4', 'portrait');
            return $pdf->download("balance_sheet_{$asAtDate}.pdf");
        }

        return view('reports.financial.balance-sheet', compact('pagetitle', 'asAtDate', 'schoolInfo'));
    }

    /**
     * Income Statement
     */
    public function incomeStatement(Request $request)
    {
        $pagetitle = 'Income Statement';
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $schoolInfo = SchoolInformation::getActiveSchool();

        if ($request->ajax()) {
            $data = $this->financialService->generateIncomeStatement($startDate, $endDate);
            return response()->json(['success' => true, 'data' => $data]);
        }

        if ($request->get('format') === 'pdf') {
            $data = $this->financialService->generateIncomeStatement($startDate, $endDate);
            $pdf = PDF::loadView('reports.financial.pdf.income-statement', compact('data', 'startDate', 'endDate', 'schoolInfo'));
            $pdf->setPaper('a4', 'portrait');
            return $pdf->download("income_statement_{$startDate}_to_{$endDate}.pdf");
        }

        return view('reports.financial.income-statement', compact('pagetitle', 'startDate', 'endDate', 'schoolInfo'));
    }

    /**
     * Trial Balance
     */
    public function trialBalance(Request $request)
    {
        $pagetitle = 'Trial Balance';
        $asAtDate = $request->get('as_at_date', now()->format('Y-m-d'));
        $schoolInfo = SchoolInformation::getActiveSchool();

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

        return view('reports.financial.trial-balance', compact('pagetitle', 'asAtDate', 'schoolInfo'));
    }

    /**
     * Cash Flow Statement
     */
    public function cashFlow(Request $request)
    {
        $pagetitle = 'Cash Flow Statement';
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $schoolInfo = SchoolInformation::getActiveSchool();

        if ($request->ajax()) {
            $data = $this->financialService->generateCashFlow($startDate, $endDate);
            return response()->json(['success' => true, 'data' => $data]);
        }

        if ($request->get('format') === 'pdf') {
            $data = $this->financialService->generateCashFlow($startDate, $endDate);
            $pdf = PDF::loadView('reports.financial.pdf.cash-flow', compact('data', 'startDate', 'endDate', 'schoolInfo'));
            $pdf->setPaper('a4', 'portrait');
            return $pdf->download("cash_flow_{$startDate}_to_{$endDate}.pdf");
        }

        return view('reports.financial.cash-flow', compact('pagetitle', 'startDate', 'endDate', 'schoolInfo'));
    }

    /**
     * Collection Summary
     */
    public function collectionSummary(Request $request)
    {
        $pagetitle = 'School Fee Collection Summary';
        $schoolInfo = SchoolInformation::getActiveSchool();

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

        return view('reports.financial.collection-summary', compact('pagetitle', 'classes', 'terms', 'sessions', 'schoolInfo'));
    }

    /**
     * Scholarship Impact Report
     */
    public function scholarshipImpact(Request $request)
    {
        $pagetitle = 'Scholarship & Discount Impact Report';
        $schoolInfo = SchoolInformation::getActiveSchool();

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
                    'scholarship_by_type' => $scholarships->groupBy('title')->map(function($items) { return $items->sum('value'); }),
                    'discount_by_type' => $discounts->groupBy('title')->map(function($items) { return $items->sum('value'); }),
                    'impact_by_class' => $impactByClass,
                ]
            ]);
        }

        return view('reports.financial.scholarship-impact', compact('pagetitle', 'schoolInfo'));
    }

    /**
     * Export Reports
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
     * Export Debtors Report (CSV or PDF), using the exact same grouped
     * dataset and filters as the on-screen table so numbers always match
     * what the admin was looking at when they clicked Export/Print.
     */
    private function exportDebtors($format, Request $request)
    {
        $dataset = $this->buildDebtorsDataset($request)->values();

        $filename = 'debtors_list_' . date('Y-m-d_H-i-s');

        if ($format === 'excel' || $format === 'csv') {
            $fullFilename = $filename . '.csv';
            $handle = fopen('php://output', 'w');
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $fullFilename . '"');

            fputcsv($handle, [
                'Student Name', 'Admission No', 'Class', 'Term', 'Session',
                'Scholarship', 'Discounts', 'Bills Owing',
                'Original (₦)', 'Paid (₦)', 'Outstanding (₦)', 'Savings (₦)', 'Collection Rate',
            ]);

            foreach ($dataset as $row) {
                fputcsv($handle, [
                    $row['student_name'],
                    $row['admission_no'],
                    $row['class_name'],
                    $row['term_name'],
                    $row['session_name'],
                    $row['scholarship']['title'] ?? '',
                    collect($row['discounts'])->pluck('title')->implode('; '),
                    collect($row['bills'])->pluck('title')->implode('; '),
                    number_format($row['original_amount'], 2),
                    number_format($row['amount_paid'], 2),
                    number_format($row['outstanding'], 2),
                    number_format($row['savings'], 2),
                    $row['collection_rate'] . '%',
                ]);
            }
            fclose($handle);
            exit;
        }

        // Embed avatars as base64 for reliable DomPDF rendering (no remote
        // HTTP fetch required — same reasoning as school logo/stamp below).
        $dataset = $dataset->map(function ($row) {
            $row['avatar_base64'] = $this->avatarBase64($row['avatar']);
            return $row;
        });

        $schoolInfo = $this->prepareSchoolInfoForPdf(SchoolInformation::getActiveSchool());

        $filters = [
            'class' => $request->filled('class_id')
                ? DB::table('schoolclass')
                    ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                    ->where('schoolclass.id', $request->class_id)
                    ->selectRaw("TRIM(CONCAT(schoolclass.schoolclass, ' ', COALESCE(schoolarm.arm, ''))) as n")
                    ->value('n')
                : null,
            'term' => $request->filled('term_id')
                ? DB::table('schoolterm')->where('id', $request->term_id)->value('term')
                : null,
            'session' => $request->filled('session_id')
                ? DB::table('schoolsession')->where('id', $request->session_id)->value('session')
                : null,
            'search' => $request->get('search'),
        ];

        $totals = [
            'debtors'     => $dataset->count(),
            'original'    => $dataset->sum('original_amount'),
            'paid'        => $dataset->sum('amount_paid'),
            'outstanding' => $dataset->sum('outstanding'),
            'savings'     => $dataset->sum('savings'),
        ];

        $pdf = PDF::loadView('reports.financial.pdf.debtors', compact('dataset', 'schoolInfo', 'filters', 'totals'))
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => false,
            ]);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download($filename . '.pdf');
    }

    /**
     * Export Trial Balance to Excel
     */
    private function exportTrialBalanceExcel($asAtDate)
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
     * Convert a student's avatar filename into a base64 data URI, safe for
     * embedding directly in a DomPDF-rendered PDF (mirrors the pattern used
     * by SchoolInformation::getLogoBase64Attribute()/getStampBase64Attribute()).
     */
    private function avatarBase64(?string $picture): ?string
    {
        if (!$picture || $picture === 'unnamed.jpg' || $picture === '') {
            return null;
        }

        $relativePath = 'images/student_avatars/' . $picture;

        if (!Storage::disk('public')->exists($relativePath)) {
            return null;
        }

        $path = Storage::disk('public')->path($relativePath);
        $mime = mime_content_type($path) ?: 'image/jpeg';

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }

    /**
     * Helper method to prepare school info with base64 images for PDF
     */
    private function prepareSchoolInfoForPdf($schoolInfo)
    {
        if (!$schoolInfo) {
            return null;
        }

        if ($schoolInfo->school_logo && Storage::disk('public')->exists($schoolInfo->school_logo)) {
            $path = Storage::disk('public')->path($schoolInfo->school_logo);
            $mime = mime_content_type($path) ?: 'image/png';
            $data = base64_encode(file_get_contents($path));
            $schoolInfo->logo_base64 = "data:{$mime};base64,{$data}";
        } else {
            $schoolInfo->logo_base64 = null;
        }

        if ($schoolInfo->school_stamp && Storage::disk('public')->exists($schoolInfo->school_stamp)) {
            $path = Storage::disk('public')->path($schoolInfo->school_stamp);
            $mime = mime_content_type($path) ?: 'image/png';
            $data = base64_encode(file_get_contents($path));
            $schoolInfo->stamp_base64 = "data:{$mime};base64,{$data}";
        } else {
            $schoolInfo->stamp_base64 = null;
        }

        if (empty($schoolInfo->formatted_phones) && !empty($schoolInfo->school_phones)) {
            $schoolInfo->formatted_phones = implode(', ', $schoolInfo->school_phones);
        }

        return $schoolInfo;
    }
}