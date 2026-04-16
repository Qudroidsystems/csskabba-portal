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
            'success'     => true,
            'columns'     => $columns,
            'is_senior'   => $schoolclass && $schoolclass->classcategories->isNotEmpty()
                ? ($schoolclass->classcategories->first()->is_senior ?? false) : false,
            'subject_count' => count($columns['scores']),
            'assessment_count' => count($columns['assessments']),
        ]);
    }

    // =========================================================================
    // GET STUDENT PREVIEW (AJAX)
    // =========================================================================

 // =========================================================================
// PATCH 1: Update getStudentPreview to return subject_count & assessment_count
// Replace the existing getStudentPreview method in BroadsheetController
// =========================================================================

public function getStudentPreview(Request $request): JsonResponse
{
    $schoolclassid = $request->input('schoolclassid');
    $sessionid     = $request->input('sessionid');
    $termid        = $request->input('termid');

    if (!$schoolclassid || !$sessionid) {
        return response()->json(['success' => false, 'message' => 'Missing parameters'], 400);
    }

    // Student count
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

    // Subject count for this class
    $subjectCount = DB::table('subjectclass as sc')
        ->join('subjectteacher as st', 'st.id', '=', 'sc.subjectteacherid')
        ->where('sc.schoolclassid', $schoolclassid)
        ->distinct()
        ->count('sc.subjectid');

    // Assessment count from class categories
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
    // ── School & class meta ───────────────────────────────────────────────
    $schoolInfo  = SchoolInformation::getActiveSchool() ?? new \stdClass();

    $schoolclass = Schoolclass::with('classcategories')
        ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
        ->select(['schoolclass.*', 'schoolarm.arm as arm_name'])
        ->where('schoolclass.id', $schoolclassid)
        ->first();

    $schoolsession = Schoolsession::find($sessionid);
    $schoolterm    = Schoolterm::find($termid);

    // ── Dynamic assessments ────────────────────────────────────────────────
    $assessments = collect();
    if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
        $categoryIds = $schoolclass->classcategories->pluck('id');
        $assessments = Assessment::whereIn('classcategory_id', $categoryIds)
            ->orderBy('id')
            ->get();
    }

    // ── Get subjects using RAW QUERY (Fixed - removed st.userid) ───────────
    $subjectsMap = [];

    $subjectClasses = DB::table('subjectclass as sc')
        ->join('subjectteacher as st', 'st.id', '=', 'sc.subjectteacherid')
        ->join('subject', 'subject.id', '=', 'sc.subjectid')
        ->where('sc.schoolclassid', $schoolclassid)
        // You can uncomment these if subjectteacher has sessionid/termid and you want to filter
        // ->where('st.sessionid', $sessionid)
        // ->where('st.termid', $termid)
        ->select([
            'sc.subjectid',
            'subject.subject as subject_name',
            'subject.subject_code',
            'sc.subjectteacherid',
            'st.staffid'                    // Only staffid exists
            // Removed st.userid because it doesn't exist
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

    // ── All students in this class/session ────────────────────────────────
    $studentIds = Studentclass::where('schoolclassid', $schoolclassid)
        ->where('sessionid', $sessionid)
        ->pluck('studentId')
        ->toArray();

    if (empty($studentIds)) {
        // Return empty data if no students
        return [
            'schoolInfo'       => $schoolInfo,
            'schoolclass'      => $schoolclass,
            'schoolsession'    => $schoolsession,
            'schoolterm'       => $schoolterm,
            'assessments'      => $assessments,
            'subjects'         => $subjectsMap,
            'studentRows'      => [],
            'subjectStats'     => [],
            'selectedColumns'  => $selectedColumns,
            'totalStudents'    => 0,
            'generatedAt'      => now()->format('d M Y, H:i'),
        ];
    }

    // ── All broadsheet rows ───────────────────────────────────────────────
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

    // ── Load assessment scores ─────────────────────────────────────────────
    $broadsheetIds = $broadsheets->pluck('broadsheet_id')->unique()->toArray();
    $assessmentScoresAll = BroadsheetAssessmentScore::whereIn('broadsheet_id', $broadsheetIds)
        ->get()
        ->groupBy('broadsheet_id');

    // ── Pivot data: student → subject ──────────────────────────────────────
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
        $assessmentData = [];
        foreach ($assessments as $a) {
            $score = $assessmentScoreRow->firstWhere('assessment_id', $a->id);
            $assessmentData[$a->id] = $score ? (float)$score->score : 0;
        }

        $studentSubjectMap[$sid][$sub] = [
            'total'         => (float)($row->total ?? 0),
            'bf'            => (float)($row->bf ?? 0),
            'cum'           => (float)($row->cum ?? 0),
            'grade'         => $row->grade ?? '-',
            'remark'        => $row->remark ?? '-',
            'position'      => $row->position ?? '-',
            'class_average' => (float)($row->class_average ?? 0),
            'assessments'   => $assessmentData,
        ];
    }

    // ── Build final student rows with GPA ──────────────────────────────────
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

        $cumValues   = collect($subScores)->pluck('cum')->filter(fn($v) => $v > 0);
        $totalCum    = $cumValues->sum();
        $numSubjects = $cumValues->count();

        $totalValues = collect($subScores)->pluck('total')->filter(fn($v) => $v > 0);
        $gradePoints = $totalValues->map(fn($s) => $this->getGradePoint($s));

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

    // ── Subject statistics ────────────────────────────────────────────────
    $subjectStats = [];
    foreach ($subjectsMap as $sub => $subInfo) {
        $allTotals = collect($studentRows)
            ->pluck("subjects.{$sub}.total")
            ->filter(fn($v) => $v !== null && $v > 0);

        $subjectStats[$sub] = [
            'avg'     => $allTotals->count() > 0 ? round($allTotals->avg(), 1) : 0,
            'highest' => $allTotals->count() > 0 ? $allTotals->max() : 0,
            'lowest'  => $allTotals->count() > 0 ? $allTotals->min() : 0,
            'passed'  => $allTotals->filter(fn($v) => $v >= 40)->count(),
            'failed'  => $allTotals->filter(fn($v) => $v < 40)->count(),
        ];
    }

    // Sort subjects by name
    uasort($subjectsMap, fn($a, $b) => strcmp($a['subject_name'], $b['subject_name']));

    return [
        'schoolInfo'       => $schoolInfo,
        'schoolclass'      => $schoolclass,
        'schoolsession'    => $schoolsession,
        'schoolterm'       => $schoolterm,
        'assessments'      => $assessments,
        'subjects'         => $subjectsMap,
        'studentRows'      => array_values($studentRows),
        'subjectStats'     => $subjectStats,
        'selectedColumns'  => $selectedColumns,
        'totalStudents'    => count($studentRows),
        'generatedAt'      => now()->format('d M Y, H:i'),
    ];
}


// =========================================================================
// PATCH 2: webView — keep as POST (the form posts to it), but also accept GET
// Add this route to web.php alongside the existing post route:
//   Route::get('/web-view', [BroadsheetController::class, 'webView'])->name('broadsheet.web-view.get');
// Or simply change the route to accept both methods:
//   Route::match(['GET','POST'], '/web-view', [BroadsheetController::class, 'webView'])->name('broadsheet.web-view');
// =========================================================================

// =========================================================================
// ROUTE FILE CHANGE  (routes/web.php)
// Replace:
//   Route::post('/web-view', [BroadsheetController::class, 'webView'])->name('web-view');
// With:
//   Route::match(['GET','POST'], '/web-view', [BroadsheetController::class, 'webView'])->name('web-view');
// =========================================================================


// =========================================================================
// PATCH 3: Complete updated webView method for BroadsheetController
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
// PATCH 4: Updated exportPdf — uses getLogoBase64, full page width options
// Replaces existing exportPdf in BroadsheetController
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
            'paper_size'      => 'nullable|in:A1,A2,A3,A4',
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

        // Fix logo for PDF embedding
        $data['school_logo_base64'] = $this->getLogoBase64($data['schoolInfo']);

        // ── Dynamic paper sizing ──────────────────────────────────────────
        $subjectCount    = count($data['subjects'] ?? []);
        $assessmentCount = isset($data['assessments']) ? $data['assessments']->count() : 0;

        if (!$paperSize) {
            $colsPerSubject = $assessmentCount;
            $colsPerSubject += (in_array('total',         $selectedColumns) || empty($selectedColumns)) ? 1 : 0;
            $colsPerSubject += (in_array('cum',           $selectedColumns) || empty($selectedColumns)) ? 1 : 0;
            $colsPerSubject += (in_array('grade',         $selectedColumns) || empty($selectedColumns)) ? 1 : 0;
            $colsPerSubject += (in_array('position',      $selectedColumns) || empty($selectedColumns)) ? 1 : 0;
            $colsPerSubject += (in_array('bf',            $selectedColumns))                            ? 1 : 0;
            $colsPerSubject += (in_array('class_average', $selectedColumns))                            ? 1 : 0;
            $totalSubCols    = $subjectCount * max(1, $colsPerSubject);

            if      ($totalSubCols <= 40)  $paperSize = 'A3';
            elseif  ($totalSubCols <= 70)  $paperSize = 'A2';
            elseif  ($totalSubCols <= 110) $paperSize = 'A1';
            else                           $paperSize = 'A0';
        }

        // ── DomPDF paper dimensions (mm → points conversion) ─────────────
        // A0: 841×1189mm  A1: 594×841mm  A2: 420×594mm  A3: 297×420mm
        $paperDimensions = [
            'A0' => [2384, 3370],
            'A1' => [1684, 2384],
            'A2' => [1190, 1684],
            'A3' => [842,  1190],
            'A4' => [595,  842],
        ];
        [$shortPt, $longPt] = $paperDimensions[$paperSize] ?? [842, 1190];

        $widthPt  = $orientation === 'landscape' ? $longPt  : $shortPt;
        $heightPt = $orientation === 'landscape' ? $shortPt : $longPt;

        $paperString = strtolower($paperSize) . ($orientation === 'landscape' ? '-landscape' : '');

        $pdf = Pdf::loadView('broadsheet.pdf', $data)
            ->setPaper($paperString)
            ->setOption([
                'isHtml5ParserEnabled'    => true,
                'isRemoteEnabled'         => true,
                'isFontSubsettingEnabled' => true,
                'defaultFont'             => 'DejaVu Sans',
                'defaultPaperWidth'       => $widthPt,
                'defaultPaperHeight'      => $heightPt,
                'dpi'                     => 96,
                'enable_css_float'        => false,
                'enable_javascript'       => false,
                'debugPng'                => false,
                'debugKeepTemp'           => false,
                'debugCss'                => false,
                'debugLayout'             => false,
                'debugLayoutLines'        => false,
                'debugLayoutBlocks'       => false,
                'debugLayoutInline'       => false,
                'debugLayoutPaddingBox'   => false,
            ]);

        // ── Filename ─────────────────────────────────────────────────────
        $className   = ($data['schoolclass']->schoolclass ?? 'Class') . ' ' . ($data['schoolclass']->arm_name ?? '');
        $sessionName = $data['schoolsession']->session ?? '';
        $termName    = $data['schoolterm']->term ?? 'Term';

        $filename = 'Broadsheet_'
            . preg_replace('/[^A-Za-z0-9_\- ]/', '_', trim($className))   . '_'
            . preg_replace('/[^A-Za-z0-9_\- ]/', '_', trim($sessionName)) . '_'
            . preg_replace('/[^A-Za-z0-9_\- ]/', '_', trim($termName))    . '.pdf';

        // Stream (opens in browser — user can scroll/zoom/print)
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
    // EXPORT PDF
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
            'paper_size'      => 'nullable|in:A1,A2,A3,A4',
            'orientation'     => 'nullable|in:portrait,landscape',
        ]);

        // $paperSize      = $request->input('paper_size', 'A3');
        // $orientation    = $request->input('orientation', 'landscape');
        $selectedColumns= $request->input('selectedColumns', []);

        $data = $this->buildBroadsheetData(
            $validated['schoolclassid'],
            $validated['sessionid'],
            $validated['termid'],
            $selectedColumns
        );

        // // Fix logo for PDF
        // $data['school_logo_base64'] = $this->getLogoBase64($data['schoolInfo']);

        // // Determine paper size and orientation
        // $paperString = $paperSize;
        // if ($orientation === 'landscape') {
        //     $paperString .= '-landscape';
        // }

        // $pdf = Pdf::loadView('broadsheet.pdf', $data)
        //     ->setPaper($paperString)
        //     ->setOption('isHtml5ParserEnabled', true)
        //     ->setOption('isRemoteEnabled', true);


        // =========================================================================
// UPDATE exportPdf — replace the paper-size block inside the try{} with:
// =========================================================================

        // Dynamic paper sizing based on subject count
        $subjectCount    = count($data['subjects'] ?? []);
        $assessmentCount = isset($data['assessments']) ? $data['assessments']->count() : 0;

        // Choose paper size automatically if not forced
        $paperSize   = $request->input('paper_size', null);
        $orientation = $request->input('orientation', 'landscape');

        if (!$paperSize) {
            // Auto-select based on column density
            $totalSubCols = $subjectCount * (
                $assessmentCount
                + (in_array('total',         $selectedColumns) || empty($selectedColumns) ? 1 : 0)
                + (in_array('cum',           $selectedColumns) || empty($selectedColumns) ? 1 : 0)
                + (in_array('grade',         $selectedColumns) || empty($selectedColumns) ? 1 : 0)
                + (in_array('position',      $selectedColumns) || empty($selectedColumns) ? 1 : 0)
                + (in_array('bf',            $selectedColumns)  ? 1 : 0)
                + (in_array('class_average', $selectedColumns)  ? 1 : 0)
            );

            if ($totalSubCols <= 40)       $paperSize = 'A3';
            elseif ($totalSubCols <= 70)   $paperSize = 'A2';
            elseif ($totalSubCols <= 110)  $paperSize = 'A1';
            else                           $paperSize = 'A0';
        }

        // DomPDF paper string
        $paperString = strtolower($paperSize) . ($orientation === 'landscape' ? '-landscape' : '');

        $pdf = Pdf::loadView('broadsheet.pdf', $data)
            ->setPaper($paperString)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('isFontSubsettingEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans')
            // Allow very wide tables — critical fix
            ->setOption('defaultPaperWidth',  $orientation === 'landscape' ? 1587 : 1122)
            ->setOption('defaultPaperHeight', $orientation === 'landscape' ? 1122 : 1587);

        // Build clean filename
        $className   = ($data['schoolclass']->schoolclass ?? 'Class') . ' ' . ($data['schoolclass']->arm_name ?? '');
        $sessionName = $data['schoolsession']->session ?? '';
        $termName    = $data['schoolterm']->term ?? 'Term';

        $cleanClassName = preg_replace('/[^A-Za-z0-9_\- ]/', '_', trim($className));
        $cleanSession   = preg_replace('/[^A-Za-z0-9_\- ]/', '_', trim($sessionName));
        $cleanTerm      = preg_replace('/[^A-Za-z0-9_\- ]/', '_', trim($termName));

        $filename = 'Broadsheet_' . $cleanClassName . '_' . $cleanSession . '_' . $cleanTerm . '.pdf';

        // IMPORTANT: This makes it open in the browser instead of downloading
        return $pdf->stream($filename);

    } catch (\Exception $e) {
        Log::error('Broadsheet PDF export error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
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

            // Clean filename for Excel as well
            $cleanClassName = preg_replace('/[\/\\\\:*?"<>|]/', '_', trim($className));
            $cleanSession = preg_replace('/[\/\\\\:*?"<>|]/', '_', trim($sessionName));
            $cleanTerm = preg_replace('/[\/\\\\:*?"<>|]/', '_', trim($termName));
            $cleanClassName = preg_replace('/[^A-Za-z0-9_\- ]/', '', $cleanClassName);
            $cleanSession = preg_replace('/[^A-Za-z0-9_\- ]/', '', $cleanSession);
            $cleanTerm = preg_replace('/[^A-Za-z0-9_\- ]/', '', $cleanTerm);

            $filename = 'Broadsheet_' . $cleanClassName . '_' . $cleanSession . '_' . $cleanTerm . '.xlsx';

            if (strlen($filename) < 15) {
                $filename = 'Broadsheet_' . date('Y-m-d_H-i-s') . '.xlsx';
            }

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
