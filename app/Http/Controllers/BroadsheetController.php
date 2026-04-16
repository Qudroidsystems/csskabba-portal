<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Student;
use App\Models\Assessment;
use App\Models\Broadsheets;
use App\Models\Schoolclass;
use App\Models\Studentclass;
use Illuminate\Http\Request;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\SchoolInformation;
use App\Models\BroadsheetAssessmentScore;
use App\Models\Subjectclass;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class BroadsheetController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View student-report');
    }

    // =========================================================================
    // INDEX
    // =========================================================================

    public function index(Request $request): View|JsonResponse
    {
        $pagetitle      = 'Class Broadsheet Generator';
        $schoolclasses  = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm'])
            ->orderBy('schoolclass.schoolclass')
            ->get();
        $schoolsessions = Schoolsession::orderByDesc('id')->get();
        $schoolterms    = Schoolterm::all();

        return view('broadsheet.index', compact(
            'pagetitle', 'schoolclasses', 'schoolsessions', 'schoolterms'
        ));
    }

    // =========================================================================
    // GET COLUMN OPTIONS (AJAX)
    // =========================================================================

    public function getColumnOptions(Request $request): JsonResponse
    {
        $schoolclassid = $request->input('schoolclassid');
        $sessionid     = $request->input('sessionid');
        $termid        = $request->input('termid');

        if (!$schoolclassid || !$sessionid || !$termid) {
            return response()->json(['success' => false, 'message' => 'Missing parameters'], 400);
        }

        $schoolclass = Schoolclass::with('classcategories')->find($schoolclassid);
        $assessments = collect();

        if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            $categoryIds = $schoolclass->classcategories->pluck('id');
            $assessments = Assessment::whereIn('classcategory_id', $categoryIds)
                ->with('subAssessments')
                ->orderBy('id')
                ->get();
        }

        $columns = [
            'student_info' => [
                'sn'           => ['label' => 'SN',           'default' => true],
                'admission_no' => ['label' => 'Admission No', 'default' => true],
                'name'         => ['label' => 'Student Name', 'default' => true],
                'gender'       => ['label' => 'Gender',       'default' => false],
            ],
            'assessments' => [],
            'scores' => [
                'total'         => ['label' => 'Total',      'default' => true],
                'bf'            => ['label' => 'BF',         'default' => true],
                'cum'           => ['label' => 'Cum',        'default' => true],
                'grade'         => ['label' => 'Grade',      'default' => true],
                'position'      => ['label' => 'Position',   'default' => true],
                'class_average' => ['label' => 'Class Avg',  'default' => true],
                'remark'        => ['label' => 'Remark',     'default' => false],
            ],
            'gpa_metrics' => [
                'gpa'                => ['label' => 'GPA',          'default' => true],
                'cgpa'               => ['label' => 'CGPA',         'default' => false],
                'gpa_grade'          => ['label' => 'GPA Grade',    'default' => false],
                'num_subjects'       => ['label' => 'No. Subjects', 'default' => false],
                'total_grade_points' => ['label' => 'Total GP',     'default' => false],
            ],
        ];

        foreach ($assessments as $a) {
            $columns['assessments']['assessment_' . $a->id] = [
                'label'               => $a->name . ' (' . $a->max_score . ')',
                'default'             => true,
                'assessment_id'       => $a->id,
                'max_score'           => $a->max_score,
                'has_sub_assessments' => $a->subAssessments->isNotEmpty(),
            ];
        }

        return response()->json([
            'success'          => true,
            'columns'          => $columns,
            'is_senior'        => $schoolclass && $schoolclass->classcategories->isNotEmpty()
                ? ($schoolclass->classcategories->first()->is_senior ?? false) : false,
            'subject_count'    => count($columns['scores']),
            'assessment_count' => count($columns['assessments']),
        ]);
    }

    // =========================================================================
    // GET STUDENT PREVIEW (AJAX)
    // =========================================================================

    public function getStudentPreview(Request $request): JsonResponse
    {
        $schoolclassid = $request->input('schoolclassid');
        $sessionid     = $request->input('sessionid');
        $termid        = $request->input('termid');

        if (!$schoolclassid || !$sessionid) {
            return response()->json(['success' => false, 'message' => 'Missing parameters'], 400);
        }

        $students = Studentclass::where('schoolclassid', $schoolclassid)
            ->where('sessionid', $sessionid)
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'studentclass.studentId')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->select([
                'studentRegistration.id as id',
                'studentRegistration.admissionNo as admissionno',
                'studentRegistration.firstname',
                'studentRegistration.lastname',
                'studentRegistration.gender',
                'studentpicture.picture',
            ])
            ->orderBy('studentRegistration.lastname')
            ->orderBy('studentRegistration.firstname')
            ->get();

        $subjectCount = DB::table('subjectclass as sc')
            ->join('subjectteacher as st', 'st.id', '=', 'sc.subjectteacherid')
            ->where('sc.schoolclassid', $schoolclassid)
            ->distinct()
            ->count('sc.subjectid');

        $assessmentCount = 0;
        $schoolclass = Schoolclass::with('classcategories')->find($schoolclassid);
        if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            $categoryIds     = $schoolclass->classcategories->pluck('id');
            $assessmentCount = Assessment::whereIn('classcategory_id', $categoryIds)->count();
        }

        return response()->json([
            'success'          => true,
            'count'            => $students->count(),
            'students'         => $students,
            'subject_count'    => $subjectCount,
            'assessment_count' => $assessmentCount,
        ]);
    }

    // =========================================================================
    // BUILD BROADSHEET DATA
    // =========================================================================

    private function buildBroadsheetData(
        int $schoolclassid,
        int $sessionid,
        int $termid,
        array $selectedColumns = []
    ): array {
        $schoolInfo = SchoolInformation::getActiveSchool() ?? new \stdClass();

        $schoolclass = Schoolclass::with('classcategories')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.*', 'schoolarm.arm as arm_name'])
            ->where('schoolclass.id', $schoolclassid)
            ->first();

        $schoolsession = Schoolsession::find($sessionid);
        $schoolterm    = Schoolterm::find($termid);

        $assessments = collect();
        if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            $categoryIds = $schoolclass->classcategories->pluck('id');
            $assessments = Assessment::whereIn('classcategory_id', $categoryIds)
                ->orderBy('id')
                ->get();
        }

        $subjectsMap   = [];
        $subjectClasses = DB::table('subjectclass as sc')
            ->join('subjectteacher as st', 'st.id', '=', 'sc.subjectteacherid')
            ->join('subject', 'subject.id', '=', 'sc.subjectid')
            ->where('sc.schoolclassid', $schoolclassid)
            ->select([
                'sc.subjectid',
                'subject.subject as subject_name',
                'subject.subject_code',
                'sc.subjectteacherid',
                'st.staffid',
            ])
            ->distinct()
            ->get();

        foreach ($subjectClasses as $sc) {
            $subjectsMap[$sc->subjectid] = [
                'subject_id'       => $sc->subjectid,
                'subject_name'     => $sc->subject_name,
                'subject_code'     => $sc->subject_code ?? '',
                'subjectteacherid' => $sc->subjectteacherid,
                'staffid'          => $sc->staffid,
            ];
        }

        $studentIds = Studentclass::where('schoolclassid', $schoolclassid)
            ->where('sessionid', $sessionid)
            ->pluck('studentId')
            ->toArray();

        if (empty($studentIds)) {
            return [
                'schoolInfo'      => $schoolInfo,
                'schoolclass'     => $schoolclass,
                'schoolsession'   => $schoolsession,
                'schoolterm'      => $schoolterm,
                'assessments'     => $assessments,
                'subjects'        => $subjectsMap,
                'studentRows'     => [],
                'subjectStats'    => [],
                'selectedColumns' => $selectedColumns,
                'totalStudents'   => 0,
                'generatedAt'     => now()->format('d M Y, H:i'),
            ];
        }

        $broadsheets = Broadsheets::whereIn('broadsheet_records.student_id', $studentIds)
            ->where('broadsheets.term_id', $termid)
            ->where('broadsheet_records.session_id', $sessionid)
            ->where('broadsheet_records.schoolclass_id', $schoolclassid)
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->join('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->join('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->select([
                'broadsheets.id as broadsheet_id',
                'broadsheet_records.student_id',
                'broadsheet_records.subject_id',
                'subject.subject as subject_name',
                'subject.subject_code',
                'studentRegistration.admissionNo as admissionno',
                'studentRegistration.firstname',
                'studentRegistration.lastname',
                'studentRegistration.gender',
                'studentpicture.picture',
                'broadsheets.total',
                'broadsheets.bf',
                'broadsheets.cum',
                'broadsheets.grade',
                'broadsheets.remark',
                'broadsheets.subject_position_class as position',
                'broadsheets.avg as class_average',
                'broadsheets.vettedstatus',
            ])
            ->orderBy('studentRegistration.lastname')
            ->orderBy('studentRegistration.firstname')
            ->orderBy('subject.subject')
            ->get();

        $broadsheetIds       = $broadsheets->pluck('broadsheet_id')->unique()->toArray();
        $assessmentScoresAll = BroadsheetAssessmentScore::whereIn('broadsheet_id', $broadsheetIds)
            ->get()
            ->groupBy('broadsheet_id');

        $studentSubjectMap = [];
        foreach ($broadsheets as $row) {
            $sid = $row->student_id;
            $sub = $row->subject_id;

            if (!isset($subjectsMap[$sub])) {
                $subjectsMap[$sub] = [
                    'subject_id'   => $sub,
                    'subject_name' => $row->subject_name,
                    'subject_code' => $row->subject_code ?? '',
                ];
            }

            $assessmentScoreRow = $assessmentScoresAll->get($row->broadsheet_id, collect());
            $assessmentData     = [];
            foreach ($assessments as $a) {
                $score = $assessmentScoreRow->firstWhere('assessment_id', $a->id);
                $assessmentData[$a->id] = $score ? (float) $score->score : 0;
            }

            $studentSubjectMap[$sid][$sub] = [
                'total'         => (float) ($row->total ?? 0),
                'bf'            => (float) ($row->bf ?? 0),
                'cum'           => (float) ($row->cum ?? 0),
                'grade'         => $row->grade ?? '-',
                'remark'        => $row->remark ?? '-',
                'position'      => $row->position ?? '-',
                'class_average' => (float) ($row->class_average ?? 0),
                'assessments'   => $assessmentData,
            ];
        }

        $studentInfo = Studentclass::where('schoolclassid', $schoolclassid)
            ->where('sessionid', $sessionid)
            ->join('studentRegistration', 'studentRegistration.id', '=', 'studentclass.studentId')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->select([
                'studentRegistration.id as id',
                'studentRegistration.admissionNo as admissionno',
                'studentRegistration.firstname',
                'studentRegistration.lastname',
                'studentRegistration.gender',
                'studentRegistration.dateofbirth',
                'studentpicture.picture',
            ])
            ->orderBy('studentRegistration.lastname')
            ->orderBy('studentRegistration.firstname')
            ->get();

        $studentRows = [];
        foreach ($studentInfo as $stu) {
            $sid       = $stu->id;
            $subScores = $studentSubjectMap[$sid] ?? [];

            $cumValues   = collect($subScores)->pluck('cum')->filter(fn ($v) => $v > 0);
            $totalCum    = $cumValues->sum();
            $numSubjects = $cumValues->count();

            $totalValues = collect($subScores)->pluck('total')->filter(fn ($v) => $v > 0);
            $gradePoints = $totalValues->map(fn ($s) => $this->getGradePoint($s));

            $gpa      = $gradePoints->count() > 0 ? round($gradePoints->avg(), 2) : 0.0;
            $cgpa     = $gpa;
            $gpaGrade = $this->getGpaGrade($gpa);

            $studentRows[$sid] = [
                'id'                 => $sid,
                'admissionno'        => $stu->admissionno,
                'firstname'          => $stu->firstname,
                'lastname'           => $stu->lastname,
                'gender'             => $stu->gender,
                'dateofbirth'        => $stu->dateofbirth,
                'picture'            => $stu->picture,
                'subjects'           => $subScores,
                'total_cum'          => round($totalCum, 1),
                'num_subjects'       => $numSubjects,
                'class_average'      => $numSubjects > 0 ? round($totalCum / $numSubjects, 1) : 0,
                'gpa'                => $gpa,
                'cgpa'               => $cgpa,
                'gpa_grade'          => $gpaGrade,
                'total_grade_points' => round($gradePoints->sum(), 1),
            ];
        }

        $subjectStats = [];
        foreach ($subjectsMap as $sub => $subInfo) {
            $allTotals = collect($studentRows)
                ->pluck("subjects.{$sub}.total")
                ->filter(fn ($v) => $v !== null && $v > 0);

            $subjectStats[$sub] = [
                'avg'     => $allTotals->count() > 0 ? round($allTotals->avg(), 1) : 0,
                'highest' => $allTotals->count() > 0 ? $allTotals->max() : 0,
                'lowest'  => $allTotals->count() > 0 ? $allTotals->min() : 0,
                'passed'  => $allTotals->filter(fn ($v) => $v >= 40)->count(),
                'failed'  => $allTotals->filter(fn ($v) => $v < 40)->count(),
            ];
        }

        uasort($subjectsMap, fn ($a, $b) => strcmp($a['subject_name'], $b['subject_name']));

        return [
            'schoolInfo'      => $schoolInfo,
            'schoolclass'     => $schoolclass,
            'schoolsession'   => $schoolsession,
            'schoolterm'      => $schoolterm,
            'assessments'     => $assessments,
            'subjects'        => $subjectsMap,
            'studentRows'     => array_values($studentRows),
            'subjectStats'    => $subjectStats,
            'selectedColumns' => $selectedColumns,
            'totalStudents'   => count($studentRows),
            'generatedAt'     => now()->format('d M Y, H:i'),
        ];
    }

    // =========================================================================
    // WEB VIEW
    // =========================================================================

    public function webView(Request $request): View|JsonResponse
    {
        try {
            $validated = $request->validate([
                'schoolclassid'   => 'required|integer|exists:schoolclass,id',
                'sessionid'       => 'required|integer|exists:schoolsession,id',
                'termid'          => 'required|integer',
                'selectedColumns' => 'nullable|array',
            ]);

            $selectedColumns = $request->input('selectedColumns', []);

            $data = $this->buildBroadsheetData(
                (int) $validated['schoolclassid'],
                (int) $validated['sessionid'],
                (int) $validated['termid'],
                $selectedColumns
            );

            $data['school_logo_base64'] = $this->getLogoBase64($data['schoolInfo']);
            $data['pagetitle']          = 'Class Broadsheet – Web View';

            return view('broadsheet.web', $data);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'Invalid input: ' . implode(', ', $e->errors()));
        } catch (\Exception $e) {
            Log::error('Broadsheet web view error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to generate broadsheet: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // EXPORT PDF  — Full-width, never clips content
    // =========================================================================

   public function exportPdf(Request $request)
{
    try {
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '1024M');

        $validated = $request->validate([
            'schoolclassid'   => 'required|integer|exists:schoolclass,id',
            'sessionid'       => 'required|integer|exists:schoolsession,id',
            'termid'          => 'required|integer',
            'selectedColumns' => 'nullable|array',
            'paper_size'      => 'nullable|in:A0,A1,A2,A3,A4',
            'orientation'     => 'nullable|in:portrait,landscape',
        ]);

        $selectedColumns = $request->input('selectedColumns', []);
        $orientation     = $request->input('orientation', 'landscape');
        $paperSize       = $request->input('paper_size', null);

        $data = $this->buildBroadsheetData(
            (int) $validated['schoolclassid'],
            (int) $validated['sessionid'],
            (int) $validated['termid'],
            $selectedColumns
        );

        $data['school_logo_base64'] = $this->getLogoBase64($data['schoolInfo']);

        // ── Dynamic paper sizing based on column count ─────────────────
        $subjectCount    = count($data['subjects'] ?? []);
        $assessmentCount = isset($data['assessments']) ? $data['assessments']->count() : 0;

        if (!$paperSize) {
            // Count active columns per subject
            $perSubjCols = 0;
            foreach ($data['assessments'] ?? [] as $a) {
                if (empty($selectedColumns) || in_array('assessment_' . $a->id, $selectedColumns)) {
                    $perSubjCols++;
                }
            }
            foreach (['total', 'bf', 'cum', 'grade', 'position', 'class_average', 'remark'] as $col) {
                if (empty($selectedColumns) || in_array($col, $selectedColumns)) {
                    $perSubjCols++;
                }
            }

            $totalCols = $subjectCount * max(1, $perSubjCols);

            if      ($totalCols <= 30)  $paperSize = 'A3';
            elseif  ($totalCols <= 60)  $paperSize = 'A2';
            elseif  ($totalCols <= 100) $paperSize = 'A1';
            else                        $paperSize = 'A0';
        }

        // ── DomPDF custom page dimensions (points) ────────────────────
        // We compute the EXACT width needed so nothing is clipped.
        // Base: frozen cols ~200pt + (subjects × perSubjCols × 22pt) + margins
        $perSubjColWidth = 22; // pt per data column
        $frozenWidth     = 200; // SN + AdmNo + Name
        $marginPt        = 57;  // 10mm each side × 2 × 2.835

        $perSubjCols = 0;
        foreach ($data['assessments'] ?? [] as $a) {
            if (empty($selectedColumns) || in_array('assessment_' . $a->id, $selectedColumns)) {
                $perSubjCols++;
            }
        }
        foreach (['total', 'bf', 'cum', 'grade', 'position', 'class_average', 'remark'] as $col) {
            if (empty($selectedColumns) || in_array($col, $selectedColumns)) {
                $perSubjCols++;
            }
        }
        foreach (['gpa', 'cgpa', 'gpa_grade', 'num_subjects', 'total_grade_points'] as $col) {
            if (empty($selectedColumns) || in_array($col, $selectedColumns)) {
                $perSubjCols += 0.5; // GPA cols are narrower
            }
        }

        $neededWidth = $frozenWidth + ($subjectCount * max(1, ceil($perSubjCols)) * $perSubjColWidth) + $marginPt;

        // Standard paper heights in points (landscape = wide dimension is height)
        $paperHeights = [
            'A0' => 2384,
            'A1' => 1684,
            'A2' => 1190,
            'A3' => 842,
            'A4' => 595,
        ];
        $heightPt = $paperHeights[$paperSize] ?? 842;

        // Width must be at least the needed content width
        $standardWidths = [
            'A0' => 3370,
            'A1' => 2384,
            'A2' => 1684,
            'A3' => 1190,
            'A4' => 842,
        ];
        $widthPt = max($standardWidths[$paperSize] ?? 1190, $neededWidth + 100);

        // Store computed dimensions for the view
        $data['pdf_width_pt']  = $widthPt;
        $data['pdf_height_pt'] = $heightPt;
        $data['pdf_paper_size'] = $paperSize;

        // FIX: Use the correct DomPDF paper size format [0, 0, width, height]
        // The array should have 4 elements: x1, y1, x2, y2
        $customPaper = [0, 0, $widthPt, $heightPt];

        $pdf = Pdf::loadView('broadsheet.pdf', $data)
            ->setPaper($customPaper, $orientation)
            ->setOptions([
                'isHtml5ParserEnabled'    => true,
                'isRemoteEnabled'         => true,
                'isFontSubsettingEnabled' => true,
                'defaultFont'             => 'DejaVu Sans',
                'dpi'                     => 96,
                'enable_css_float'        => false,
                'enable_javascript'       => false,
                'debugPng'                => false,
                'debugKeepTemp'           => false,
                'debugCss'                => false,
                'debugLayout'             => false,
            ]);

        $className   = ($data['schoolclass']->schoolclass ?? 'Class') . ' ' . ($data['schoolclass']->arm_name ?? '');
        $sessionName = $data['schoolsession']->session ?? '';
        $termName    = $data['schoolterm']->term ?? 'Term';

        $filename = 'Broadsheet_'
            . preg_replace('/[^A-Za-z0-9_\-]/', '_', trim($className))   . '_'
            . preg_replace('/[^A-Za-z0-9_\-]/', '_', trim($sessionName)) . '_'
            . preg_replace('/[^A-Za-z0-9_\-]/', '_', trim($termName))    . '.pdf';

        return $pdf->stream($filename);

    } catch (\Exception $e) {
        Log::error('Broadsheet PDF export error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
    }
}
    // =========================================================================
    // EXPORT EXCEL
    // =========================================================================

    public function exportExcel(Request $request)
    {
        try {
            $validated = $request->validate([
                'schoolclassid'   => 'required|integer|exists:schoolclass,id',
                'sessionid'       => 'required|integer|exists:schoolsession,id',
                'termid'          => 'required|integer',
                'selectedColumns' => 'nullable|array',
            ]);

            $selectedColumns = $request->input('selectedColumns', []);

            $data = $this->buildBroadsheetData(
                $validated['schoolclassid'],
                $validated['sessionid'],
                $validated['termid'],
                $selectedColumns
            );

            $className   = ($data['schoolclass']->schoolclass ?? 'Class') . ' ' . ($data['schoolclass']->arm_name ?? '');
            $sessionName = $data['schoolsession']->session ?? '';
            $termName    = $data['schoolterm']->term ?? 'Term';

            $filename = 'Broadsheet_'
                . preg_replace('/[^A-Za-z0-9_\-]/', '_', trim($className))   . '_'
                . preg_replace('/[^A-Za-z0-9_\-]/', '_', trim($sessionName)) . '_'
                . preg_replace('/[^A-Za-z0-9_\-]/', '_', trim($termName))    . '.xlsx';

            return Excel::download(new \App\Exports\BroadsheetExport($data), $filename);

        } catch (\Exception $e) {
            Log::error('Broadsheet Excel export error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to generate Excel: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function getGradePoint(float $score): float
    {
        if ($score >= 75) return 5.0;
        if ($score >= 70) return 4.5;
        if ($score >= 65) return 4.0;
        if ($score >= 60) return 3.5;
        if ($score >= 55) return 3.0;
        if ($score >= 50) return 2.5;
        if ($score >= 45) return 2.0;
        if ($score >= 40) return 1.0;
        return 0.0;
    }

    private function getGpaGrade(float $gpa): string
    {
        if ($gpa >= 4.5) return 'A1';
        if ($gpa >= 4.0) return 'B2';
        if ($gpa >= 3.5) return 'B3';
        if ($gpa >= 3.0) return 'C4';
        if ($gpa >= 2.5) return 'C5';
        if ($gpa >= 2.0) return 'C6';
        if ($gpa >= 1.5) return 'D7';
        if ($gpa >= 1.0) return 'E8';
        return 'F9';
    }

    private function getLogoBase64($schoolInfo): string
    {
        $placeholder = 'data:image/svg+xml;base64,' . base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80">
                <rect width="80" height="80" rx="40" fill="#1e3a5f"/>
                <text x="40" y="45" text-anchor="middle" fill="white" font-family="Arial" font-size="14" font-weight="bold">SCH</text>
            </svg>'
        );

        if (!$schoolInfo || empty($schoolInfo->school_logo)) return $placeholder;

        $paths = [
            storage_path('app/public/' . $schoolInfo->school_logo),
            public_path('storage/' . $schoolInfo->school_logo),
            public_path($schoolInfo->school_logo),
        ];

        foreach ($paths as $path) {
            if (file_exists($path) && filesize($path) > 100) {
                $mime = mime_content_type($path) ?: 'image/jpeg';
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
            }
        }

        return $placeholder;
    }

    private function calculateGrade(float $score): string
    {
        if ($score >= 75) return 'A1';
        if ($score >= 70) return 'B2';
        if ($score >= 65) return 'B3';
        if ($score >= 60) return 'C4';
        if ($score >= 55) return 'C5';
        if ($score >= 50) return 'C6';
        if ($score >= 45) return 'D7';
        if ($score >= 40) return 'E8';
        return 'F9';
    }
}
