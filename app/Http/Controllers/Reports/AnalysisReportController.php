<?php
// app/Http/Controllers/Reports/AnalysisReportController.php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Schoolclass;
use App\Models\Schoolterm;
use App\Models\Schoolsession;
use App\Models\StudentBillPayment;
use App\Models\StudentBillPaymentBook;
use App\Models\ScholarshipAssignment;
use App\Models\DiscountAssignment;
use App\Services\Analysis\SchoolAnalysisService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;

class AnalysisReportController extends Controller
{
    protected $analysisService;

    public function __construct(SchoolAnalysisService $analysisService)
    {
        $this->analysisService = $analysisService;
        $this->middleware('permission:View financial reports');
    }

    /**
     * Class Analysis Report with AJAX DataTable.
     */
    public function classAnalysis(Request $request)
    {
        $pagetitle = 'Class Financial Analysis';

        if ($request->ajax()) {
            $classId = $request->input('class_id');
            $termId = $request->input('term_id');
            $sessionId = $request->input('session_id');

            if (!$classId || !$termId || !$sessionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select class, term, and session'
                ], 400);
            }

            $analysis = $this->analysisService->getClassAnalysis($classId, $termId, $sessionId);

            return DataTables::of(collect($analysis['students']))
                ->addIndexColumn()
                ->addColumn('student_name', function($student) {
                    return $student['name'];
                })
                ->addColumn('admission_no', function($student) {
                    return $student['admission_no'];
                })
                ->addColumn('total_billed', function($student) {
                    return '₦' . number_format($student['total_billed'], 2);
                })
                ->addColumn('total_paid', function($student) {
                    return '₦' . number_format($student['total_paid'], 2);
                })
                ->addColumn('outstanding', function($student) {
                    $color = $student['total_outstanding'] > 0 ? 'text-danger' : 'text-success';
                    return "<span class='{$color}'>₦" . number_format($student['total_outstanding'], 2) . "</span>";
                })
                ->addColumn('completion', function($student) {
                    $percentage = $student['total_billed'] > 0 ? round(($student['total_paid'] / $student['total_billed']) * 100, 1) : 0;
                    $color = $percentage >= 100 ? 'success' : ($percentage >= 70 ? 'warning' : 'danger');
                    return "
                        <div class='d-flex align-items-center gap-2'>
                            <div class='progress flex-grow-1' style='height: 6px; width: 80px;'>
                                <div class='progress-bar bg-{$color}' style='width: {$percentage}%'></div>
                            </div>
                            <span class='small'>{$percentage}%</span>
                        </div>
                    ";
                })
                ->addColumn('action', function($student) {
                    return '<a href="' . route('payment.details', ['studentId' => $student['student_id']]) . '" class="btn btn-sm btn-info"><i class="ri-eye-line"></i> View</a>';
                })
                ->rawColumns(['outstanding', 'completion', 'action'])
                ->make(true);
        }

        $classes = Schoolclass::with('armRelation')->get();
        $terms = Schoolterm::all();
        $sessions = Schoolsession::all();

        return view('reports.class-analysis', compact('pagetitle', 'classes', 'terms', 'sessions'));
    }

    /**
     * School Wide Payment Analysis.
     */
    public function schoolWideAnalysis(Request $request)
    {
        $pagetitle = 'School Wide Payment Analysis';

        if ($request->ajax()) {
            $termId = $request->input('term_id');
            $sessionId = $request->input('session_id');

            $analysis = $this->analysisService->getSchoolFinancialSummary($sessionId, $termId);

            return response()->json([
                'success' => true,
                'data' => $analysis
            ]);
        }

        $terms = Schoolterm::all();
        $sessions = Schoolsession::all();

        return view('reports.school-wide-analysis', compact('pagetitle', 'terms', 'sessions'));
    }

    /**
     * Scholarship Impact Analysis.
     */
    public function scholarshipImpactAnalysis(Request $request)
    {
        $pagetitle = 'Scholarship & Discount Impact Analysis';

        if ($request->ajax()) {
            $termId = $request->input('term_id');
            $sessionId = $request->input('session_id');

            $query = ScholarshipAssignment::with(['scholarship', 'student']);

            if ($termId) {
                // Filter by term logic
            }

            $scholarshipData = $query->get();

            $discountData = DiscountAssignment::with(['discount'])
                ->when($termId, function($q) use ($termId) {
                    // Filter by term logic
                })
                ->get();

            $totalScholarshipValue = $scholarshipData->sum('value');
            $totalDiscountValue = $discountData->sum('value');
            $totalBeneficiaries = $scholarshipData->pluck('student_id')->unique()->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_scholarship_value' => $totalScholarshipValue,
                    'total_discount_value' => $totalDiscountValue,
                    'total_beneficiaries' => $totalBeneficiaries,
                    'scholarship_by_type' => $scholarshipData->groupBy('scholarship.scholarship_type_id')->map(function($items) {
                        return $items->sum('value');
                    }),
                    'discount_by_type' => $discountData->groupBy('discount.discount_type_id')->map(function($items) {
                        return $items->sum('value');
                    }),
                ]
            ]);
        }

        return view('reports.scholarship-impact', compact('pagetitle'));
    }

    /**
     * Export analysis to PDF.
     */
    public function exportAnalysis(Request $request, $type)
    {
        $classId = $request->input('class_id');
        $termId = $request->input('term_id');
        $sessionId = $request->input('session_id');

        switch ($type) {
            case 'class':
                $analysis = $this->analysisService->getClassAnalysis($classId, $termId, $sessionId);
                $pdf = PDF::loadView('reports.pdf.class-analysis', compact('analysis'));
                $filename = "class_analysis_{$classId}_{$termId}_{$sessionId}.pdf";
                break;
            case 'school-wide':
                $analysis = $this->analysisService->getSchoolFinancialSummary($sessionId, $termId);
                $pdf = PDF::loadView('reports.pdf.school-wide-analysis', compact('analysis'));
                $filename = "school_wide_analysis_{$termId}_{$sessionId}.pdf";
                break;
            default:
                abort(404);
        }

        return $pdf->download($filename);
    }
}
