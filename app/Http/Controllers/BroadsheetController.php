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

        // Actual subject count in this class
        $subjectCount = DB::table('subjectclass as sc')
            ->join('subjectteacher as st', 'st.id', '=', 'sc.subjectteacherid')
            ->where('sc.schoolclassid', $schoolclassid)
            ->distinct()
            ->count('sc.subjectid');

        $columns = [
            'student_info' => [
                'sn'           => ['label' => 'SN',           'default' => true],
                'admission_no' => ['label' => 'Admission No', 'default' => true],
                'name'         => ['label' => 'Student Name', 'default' => true],
                'gender'       => ['label' => 'Gender',       'default' => false],
            ],
            'assessments' => [],
            'scores' => [
                'total'                    => ['label' => 'Total',                         'default' => true],
                'bf'                       => ['label' => 'BF (Prev Cum)',                 'default' => true],
                'cum'                      => ['label' => 'Cum (BF+T)÷2',                 'default' => true],
                'grade'                    => ['label' => 'Grade',                         'default' => true],
                // 4 per-subject positions (from DB)
                'subj_pos_class_cum'       => ['label' => 'Class Pos (Cum)',               'default' => true],
                'subj_pos_class_total'     => ['label' => 'Class Pos (Term)',              'default' => true],
                'subj_pos_arm_cum'         => ['label' => 'Arm Pos (Cum)',                 'default' => false],
                'subj_pos_arm_total'       => ['label' => 'Arm Pos (Term)',                'default' => false],
                'class_average'            => ['label' => 'Class Avg',                    'default' => true],
                'remark'                   => ['label' => 'Remark',                        'default' => false],
            ],
            // 4 overall student positions (computed from aggregate scores)
            'overall_positions' => [
                'position_class_cum'  => ['label' => 'Overall: Class Pos (Cum)',  'default' => true],
                'position_class_term' => ['label' => 'Overall: Class Pos (Term)', 'default' => true],
                'position_arm_cum'    => ['label' => 'Overall: Arm Pos (Cum)',    'default' => false],
                'position_arm_term'   => ['label' => 'Overall: Arm Pos (Term)',   'default' => false],
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
            'subject_count'    => $subjectCount,
            'assessment_count' => count($columns['assessments']),
        ]);
    }

    // =========================================================================
    // GET STUDENT PREVIEW (AJAX)
    // =========================================================================

    public function getStudentPreview(Request $request): JsonResponse
    {
        $schoolclassid = $request->input('schoolclassid');
        $classgroup    = $request->input('classgroup');
        $sessionid     = $request->input('sessionid');
        $termid        = $request->input('termid');

        if ($classgroup && $sessionid) {
            $matchingClasses = Schoolclass::where('schoolclass', $classgroup)->get();
            $classIds        = $matchingClasses->pluck('id')->toArray();
            $count = Studentclass::whereIn('schoolclassid', $classIds)
                ->where('sessionid', $sessionid)->count();
            return response()->json([
                'success'    => true,
                'count'      => $count,
                'arms_count' => $matchingClasses->count(),
            ]);
        }

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
            ->distinct()->count('sc.subjectid');

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
    //
    // BF   = previous term's cumulative score (stored in broadsheets.bf)
    // CUM  = (BF + Total) / 2  — stored in broadsheets.cum
    //         When BF = 0 (first term), cum = total (correct pass-through).
    //
    // PER-SUBJECT POSITIONS (4 types — pulled directly from DB):
    //   subject_position_class       → class-wide rank by CUM
    //   subject_position_class_total → class-wide rank by TERM total
    //   arm_position                 → arm-only rank by TERM total
    //   arm_position_cum             → arm-only rank by CUM
    //
    // OVERALL STUDENT POSITIONS (4 types — computed here by summing across subjects):
    //   position_class_cum   → class-wide rank by sum of CUM scores
    //   position_class_term  → class-wide rank by sum of TERM scores
    //   position_arm_cum     → arm-only rank by sum of CUM scores
    //   position_arm_term    → arm-only rank by sum of TERM scores
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
                ->orderBy('id')->get();
        }

        // ── Subjects map ──────────────────────────────────────────────────────
        $subjectsMap    = [];
        $subjectClasses = DB::table('subjectclass as sc')
            ->join('subjectteacher as st', 'st.id', '=', 'sc.subjectteacherid')
            ->join('subject', 'subject.id', '=', 'sc.subjectid')
            ->where('sc.schoolclassid', $schoolclassid)
            ->select(['sc.subjectid', 'subject.subject as subject_name', 'subject.subject_code',
                      'sc.subjectteacherid', 'st.staffid'])
            ->distinct()->get();

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
            ->where('sessionid', $sessionid)->pluck('studentId')->toArray();

        if (empty($studentIds)) {
            return $this->emptyBroadsheetResult(
                $schoolInfo, $schoolclass, $schoolsession, $schoolterm,
                $assessments, $subjectsMap, $selectedColumns
            );
        }

        // ── Raw broadsheet rows — pull ALL 4 per-subject position columns ─────
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
                // ── 4 per-subject positions from DB ──
                'broadsheets.subject_position_class as subj_pos_class_cum',        // class-wide, by CUM
                'broadsheets.subject_position_class_total as subj_pos_class_total', // class-wide, by TERM
                'broadsheets.arm_position as subj_pos_arm_total',                   // arm-only,   by TERM
                'broadsheets.arm_position_cum as subj_pos_arm_cum',                 // arm-only,   by CUM
                'broadsheets.avg as class_average',
                'broadsheets.vettedstatus',
            ])
            ->orderBy('studentRegistration.lastname')
            ->orderBy('studentRegistration.firstname')
            ->orderBy('subject.subject')
            ->get();

        $broadsheetIds       = $broadsheets->pluck('broadsheet_id')->unique()->toArray();
        $assessmentScoresAll = BroadsheetAssessmentScore::whereIn('broadsheet_id', $broadsheetIds)
            ->get()->groupBy('broadsheet_id');

        // ── Build per-student subject map ─────────────────────────────────────
        $studentSubjectMap = [];
        foreach ($broadsheets as $row) {
            $sid   = $row->student_id;
            $sub   = $row->subject_id;

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

            $total     = (float) ($row->total ?? 0);
            $bf        = (float) ($row->bf    ?? 0);
            $cumStored = (float) ($row->cum   ?? 0);

            // CUM logic:
            //   - Use the stored value when it is non-zero (DB is authoritative).
            //   - If stored cum is 0 but BF > 0, recompute as (BF + total) / 2.
            //   - If both are 0 (no BF — first term), cum = total.
            // NOTE: when BF = 0, cum correctly equals total. This is NOT a bug.
            //       It looks odd in the UI so we show "—" for BF and a tooltip on CUM.
            if ($cumStored > 0) {
                $cum = $cumStored;
            } elseif ($bf > 0) {
                $cum = round(($bf + $total) / 2, 1);
            } else {
                $cum = $total; // first term — no BF
            }

            $studentSubjectMap[$sid][$sub] = [
                'total'              => $total,
                'bf'                 => $bf,
                'cum'                => $cum,
                'bf_is_zero'         => ($bf == 0), // flag for view — show "—" instead of "0"
                'grade'              => $row->grade ?? '-',
                'remark'             => $row->remark ?? '-',
                // 4 per-subject positions from DB
                'subj_pos_class_cum'   => $row->subj_pos_class_cum   ?? null,
                'subj_pos_class_total' => $row->subj_pos_class_total ?? null,
                'subj_pos_arm_total'   => $row->subj_pos_arm_total   ?? null,
                'subj_pos_arm_cum'     => $row->subj_pos_arm_cum     ?? null,
                'class_average'        => (float) ($row->class_average ?? 0),
                'assessments'          => $assessmentData,
            ];
        }

        // ── Determine arm id for this class ──────────────────────────────────
        $thisArmId = $schoolclass->arm ?? null;

        // ── Student info + arm mapping ────────────────────────────────────────
        $studentInfo = Studentclass::where('schoolclassid', $schoolclassid)
            ->where('sessionid', $sessionid)
            ->join('studentRegistration', 'studentRegistration.id', '=', 'studentclass.studentId')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
            ->select([
                'studentRegistration.id as id',
                'studentRegistration.admissionNo as admissionno',
                'studentRegistration.firstname',
                'studentRegistration.lastname',
                'studentRegistration.gender',
                'studentRegistration.dateofbirth',
                'studentpicture.picture',
                'schoolclass.arm as arm_id',
            ])
            ->orderBy('studentRegistration.lastname')
            ->orderBy('studentRegistration.firstname')
            ->get();

        // ── Build student rows (positions filled in next step) ────────────────
        $studentRows = [];
        foreach ($studentInfo as $stu) {
            $sid       = $stu->id;
            $subScores = $studentSubjectMap[$sid] ?? [];

            $termValues  = collect(array_values($subScores))->pluck('total')->filter(fn ($v) => $v > 0);
            $totalTerm   = round($termValues->sum(), 1);

            $cumValues   = collect(array_values($subScores))->pluck('cum')->filter(fn ($v) => $v > 0);
            $totalCum    = round($cumValues->sum(), 1);
            $numSubjects = $cumValues->count();

            $gradePoints = $termValues->map(fn ($s) => $this->getGradePoint($s));
            $gpa         = $gradePoints->count() > 0 ? round($gradePoints->avg(), 2) : 0.0;
            $gpaGrade    = $this->getGpaGrade($gpa);

            $studentRows[$sid] = [
                'id'                 => $sid,
                'admissionno'        => $stu->admissionno,
                'firstname'          => $stu->firstname,
                'lastname'           => $stu->lastname,
                'gender'             => $stu->gender,
                'dateofbirth'        => $stu->dateofbirth,
                'picture'            => $stu->picture,
                'arm_id'             => $stu->arm_id,
                'subjects'           => $subScores,
                'total_term'         => $totalTerm,
                'total_cum'          => $totalCum,
                'num_subjects'       => $numSubjects,
                'class_average'      => $numSubjects > 0 ? round($totalCum / $numSubjects, 1) : 0,
                'gpa'                => $gpa,
                'cgpa'               => $gpa,
                'gpa_grade'          => $gpaGrade,
                'total_grade_points' => round($gradePoints->sum(), 1),
                // 4 overall positions — computed below
                'position_class_cum'  => 0,
                'position_class_term' => 0,
                'position_arm_cum'    => 0,
                'position_arm_term'   => 0,
            ];
        }

        // ── Assign 4 overall positions ────────────────────────────────────────
        // Class-wide (all students in this class):
        $studentRows = $this->assignPositions($studentRows, 'total_cum',  'position_class_cum');
        $studentRows = $this->assignPositions($studentRows, 'total_term', 'position_class_term');

        // Arm-only (students whose arm_id matches this class's arm):
        $studentRows = $this->assignPositionsFiltered(
            $studentRows, 'total_cum',  'position_arm_cum',  'arm_id', $thisArmId
        );
        $studentRows = $this->assignPositionsFiltered(
            $studentRows, 'total_term', 'position_arm_term', 'arm_id', $thisArmId
        );

        // ── Subject stats ─────────────────────────────────────────────────────
        $subjectStats = [];
        foreach ($subjectsMap as $sub => $subInfo) {
            $allTotals = collect(array_values($studentRows))
                ->map(fn ($r) => $r['subjects'][$sub]['total'] ?? 0)
                ->filter(fn ($v) => $v > 0);

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
    // POSITION HELPERS
    // Standard competition ranking (1, 1, 3 for ties). Works on all students.
    // =========================================================================

    private function assignPositions(array $studentRows, string $scoreKey, string $posKey): array
    {
        $sorted    = collect(array_values($studentRows))->sortByDesc(fn ($r) => $r[$scoreKey])->values();
        $rank      = 0;
        $prevScore = null;

        foreach ($sorted as $i => $row) {
            $score = $row[$scoreKey];
            if ($score !== $prevScore) {
                $rank = $i + 1;
            }
            $prevScore = $score;
            $studentRows[$row['id']][$posKey] = $rank;
        }

        return $studentRows;
    }

    /**
     * Like assignPositions but only ranks students where $filterKey === $filterValue.
     * Students who don't match get position 0.
     */
    private function assignPositionsFiltered(
        array $studentRows, string $scoreKey, string $posKey,
        string $filterKey, $filterValue
    ): array {
        $subset = collect(array_values($studentRows))
            ->filter(fn ($r) => $r[$filterKey] == $filterValue)
            ->sortByDesc(fn ($r) => $r[$scoreKey])
            ->values();

        $rank      = 0;
        $prevScore = null;

        foreach ($subset as $i => $row) {
            $score = $row[$scoreKey];
            if ($score !== $prevScore) {
                $rank = $i + 1;
            }
            $prevScore = $score;
            $studentRows[$row['id']][$posKey] = $rank;
        }

        return $studentRows;
    }

    // =========================================================================
    // EMPTY RESULT HELPER
    // =========================================================================

    private function emptyBroadsheetResult(
        $schoolInfo, $schoolclass, $schoolsession, $schoolterm,
        $assessments, $subjectsMap, array $selectedColumns
    ): array {
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
            Log::error('Broadsheet web view error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Failed to generate broadsheet: ' . $e->getMessage());
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

            [$customPaper, $widthPt, $heightPt, $paperSize] =
                $this->computePdfDimensions($data, $selectedColumns, $paperSize, $orientation);

            $data['pdf_width_pt']   = $widthPt;
            $data['pdf_height_pt']  = $heightPt;
            $data['pdf_paper_size'] = $paperSize;

            $pdf = Pdf::loadView('broadsheet.pdf', $data)
                ->setPaper($customPaper, $orientation)
                ->setOptions($this->pdfOptions());

            return $pdf->stream($this->buildFilename($data));

        } catch (\Exception $e) {
            Log::error('Broadsheet PDF export error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
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

            return Excel::download(
                new \App\Exports\BroadsheetExport($data),
                $this->buildFilename($data, 'xlsx')
            );

        } catch (\Exception $e) {
            Log::error('Broadsheet Excel export error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to generate Excel: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // ALL CLASSES BROADSHEET (all arms of e.g. JSS 1 combined)
    // =========================================================================

    public function allClassesWebView(Request $request): View|JsonResponse
    {
        try {
            $validated = $request->validate([
                'classgroup'      => 'required|string',
                'sessionid'       => 'required|integer|exists:schoolsession,id',
                'termid'          => 'required|integer',
                'selectedColumns' => 'nullable|array',
            ]);

            $data = $this->buildAllClassesBroadsheetData(
                $validated['classgroup'],
                (int) $validated['sessionid'],
                (int) $validated['termid'],
                $request->input('selectedColumns', [])
            );

            $data['school_logo_base64'] = $this->getLogoBase64($data['schoolInfo']);
            $data['pagetitle']          = 'All Classes Broadsheet – ' . $validated['classgroup'];
            $data['is_combined']        = true;

            return view('broadsheet.web', $data);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'Invalid input.');
        } catch (\Exception $e) {
            Log::error('All-classes broadsheet error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to generate broadsheet: ' . $e->getMessage());
        }
    }

    public function allClassesExportPdf(Request $request)
    {
        try {
            ini_set('max_execution_time', 600);
            ini_set('memory_limit', '1024M');

            $validated = $request->validate([
                'classgroup'      => 'required|string',
                'sessionid'       => 'required|integer|exists:schoolsession,id',
                'termid'          => 'required|integer',
                'selectedColumns' => 'nullable|array',
                'paper_size'      => 'nullable|in:A0,A1,A2,A3,A4',
                'orientation'     => 'nullable|in:portrait,landscape',
            ]);

            $selectedColumns = $request->input('selectedColumns', []);
            $orientation     = $request->input('orientation', 'landscape');
            $paperSize       = $request->input('paper_size', 'A2');

            $data = $this->buildAllClassesBroadsheetData(
                $validated['classgroup'],
                (int) $validated['sessionid'],
                (int) $validated['termid'],
                $selectedColumns
            );

            $data['school_logo_base64'] = $this->getLogoBase64($data['schoolInfo']);
            $data['is_combined']        = true;

            [$customPaper, $widthPt, $heightPt, $paperSize] =
                $this->computePdfDimensions($data, $selectedColumns, $paperSize, $orientation);

            $data['pdf_width_pt']   = $widthPt;
            $data['pdf_height_pt']  = $heightPt;
            $data['pdf_paper_size'] = $paperSize;

            $pdf = Pdf::loadView('broadsheet.pdf', $data)
                ->setPaper($customPaper, $orientation)
                ->setOptions($this->pdfOptions());

            $filename = 'Broadsheet_'
                . preg_replace('/[^A-Za-z0-9_\-]/', '_', trim($validated['classgroup'])) . '_'
                . preg_replace('/[^A-Za-z0-9_\-]/', '_', $data['schoolsession']->session ?? '') . '_'
                . preg_replace('/[^A-Za-z0-9_\-]/', '_', $data['schoolterm']->term ?? '') . '.pdf';

            return $pdf->stream($filename);

        } catch (\Exception $e) {
            Log::error('All-classes PDF error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // BUILD ALL-CLASSES BROADSHEET DATA
    // =========================================================================

    private function buildAllClassesBroadsheetData(
        string $classgroup,
        int $sessionid,
        int $termid,
        array $selectedColumns = []
    ): array {
        $schoolInfo    = SchoolInformation::getActiveSchool() ?? new \stdClass();
        $schoolsession = Schoolsession::find($sessionid);
        $schoolterm    = Schoolterm::find($termid);

        $matchingClasses = Schoolclass::with('classcategories')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.*', 'schoolarm.arm as arm_name'])
            ->where('schoolclass.schoolclass', $classgroup)
            ->orderBy('schoolarm.arm')
            ->get();

        $emptyClass = (object)['schoolclass' => $classgroup, 'arm_name' => '(All Arms)', 'id' => null, 'arm' => null];

        if ($matchingClasses->isEmpty()) {
            return array_merge(
                $this->emptyBroadsheetResult($schoolInfo, $emptyClass, $schoolsession, $schoolterm,
                    collect(), [], $selectedColumns),
                ['classgroup' => $classgroup, 'arm_labels' => [], 'is_combined' => true]
            );
        }

        $assessments = collect();
        foreach ($matchingClasses as $cls) {
            if ($cls->classcategories->isNotEmpty()) {
                $categoryIds = $cls->classcategories->pluck('id');
                $assessments = Assessment::whereIn('classcategory_id', $categoryIds)->orderBy('id')->get();
                break;
            }
        }

        $classIds   = $matchingClasses->pluck('id')->toArray();
        $armLabels  = $matchingClasses->pluck('arm_name', 'id')->toArray();
        // Map class_id → arm_id
        $classArmMap = $matchingClasses->pluck('arm', 'id')->toArray();

        $subjectsMap    = [];
        $subjectClasses = DB::table('subjectclass as sc')
            ->join('subjectteacher as st', 'st.id', '=', 'sc.subjectteacherid')
            ->join('subject', 'subject.id', '=', 'sc.subjectid')
            ->whereIn('sc.schoolclassid', $classIds)
            ->select(['sc.subjectid', 'subject.subject as subject_name', 'subject.subject_code'])
            ->distinct()->get();

        foreach ($subjectClasses as $sc) {
            $subjectsMap[$sc->subjectid] = [
                'subject_id'   => $sc->subjectid,
                'subject_name' => $sc->subject_name,
                'subject_code' => $sc->subject_code ?? '',
            ];
        }

        $studentClassRecords = Studentclass::whereIn('schoolclassid', $classIds)
            ->where('sessionid', $sessionid)->get(['studentId', 'schoolclassid']);

        $studentClassMap = $studentClassRecords->pluck('schoolclassid', 'studentId')->toArray();
        $allStudentIds   = $studentClassRecords->pluck('studentId')->toArray();

        $combinedClass = (object)[
            'schoolclass' => $classgroup,
            'arm_name'    => '(' . $matchingClasses->pluck('arm_name')->filter()->implode(', ') . ')',
            'id'          => null,
            'arm'         => null,
        ];

        if (empty($allStudentIds)) {
            return array_merge(
                $this->emptyBroadsheetResult($schoolInfo, $combinedClass, $schoolsession, $schoolterm,
                    $assessments, $subjectsMap, $selectedColumns),
                ['classgroup' => $classgroup, 'arm_labels' => $armLabels, 'is_combined' => true]
            );
        }

        $broadsheets = Broadsheets::whereIn('broadsheet_records.student_id', $allStudentIds)
            ->where('broadsheets.term_id', $termid)
            ->where('broadsheet_records.session_id', $sessionid)
            ->whereIn('broadsheet_records.schoolclass_id', $classIds)
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->join('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->join('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->select([
                'broadsheets.id as broadsheet_id',
                'broadsheet_records.student_id',
                'broadsheet_records.subject_id',
                'broadsheet_records.schoolclass_id',
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
                'broadsheets.subject_position_class as subj_pos_class_cum',
                'broadsheets.subject_position_class_total as subj_pos_class_total',
                'broadsheets.arm_position as subj_pos_arm_total',
                'broadsheets.arm_position_cum as subj_pos_arm_cum',
                'broadsheets.avg as class_average',
            ])
            ->orderBy('studentRegistration.lastname')
            ->orderBy('studentRegistration.firstname')
            ->get();

        $broadsheetIds       = $broadsheets->pluck('broadsheet_id')->unique()->toArray();
        $assessmentScoresAll = BroadsheetAssessmentScore::whereIn('broadsheet_id', $broadsheetIds)
            ->get()->groupBy('broadsheet_id');

        $studentSubjectMap = [];
        foreach ($broadsheets as $row) {
            $sid   = $row->student_id;
            $sub   = $row->subject_id;

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

            $total     = (float) ($row->total ?? 0);
            $bf        = (float) ($row->bf    ?? 0);
            $cumStored = (float) ($row->cum   ?? 0);

            if ($cumStored > 0) {
                $cum = $cumStored;
            } elseif ($bf > 0) {
                $cum = round(($bf + $total) / 2, 1);
            } else {
                $cum = $total;
            }

            $studentSubjectMap[$sid][$sub] = [
                'total'              => $total,
                'bf'                 => $bf,
                'cum'                => $cum,
                'bf_is_zero'         => ($bf == 0),
                'grade'              => $row->grade ?? '-',
                'remark'             => $row->remark ?? '-',
                'subj_pos_class_cum'   => $row->subj_pos_class_cum   ?? null,
                'subj_pos_class_total' => $row->subj_pos_class_total ?? null,
                'subj_pos_arm_total'   => $row->subj_pos_arm_total   ?? null,
                'subj_pos_arm_cum'     => $row->subj_pos_arm_cum     ?? null,
                'class_average'        => (float) ($row->class_average ?? 0),
                'assessments'          => $assessmentData,
            ];
        }

        $studentInfo = Studentclass::whereIn('schoolclassid', $classIds)
            ->where('sessionid', $sessionid)
            ->join('studentRegistration', 'studentRegistration.id', '=', 'studentclass.studentId')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
            ->select([
                'studentRegistration.id as id',
                'studentRegistration.admissionNo as admissionno',
                'studentRegistration.firstname',
                'studentRegistration.lastname',
                'studentRegistration.gender',
                'studentRegistration.dateofbirth',
                'studentpicture.picture',
                'studentclass.schoolclassid',
                'schoolclass.arm as arm_id',
            ])
            ->orderBy('studentRegistration.lastname')
            ->orderBy('studentRegistration.firstname')
            ->get();

        $studentRows = [];
        foreach ($studentInfo as $stu) {
            $sid       = $stu->id;
            $subScores = $studentSubjectMap[$sid] ?? [];

            $termValues  = collect(array_values($subScores))->pluck('total')->filter(fn ($v) => $v > 0);
            $totalTerm   = round($termValues->sum(), 1);
            $cumValues   = collect(array_values($subScores))->pluck('cum')->filter(fn ($v) => $v > 0);
            $totalCum    = round($cumValues->sum(), 1);
            $numSubjects = $cumValues->count();

            $gradePoints = $termValues->map(fn ($s) => $this->getGradePoint($s));
            $gpa         = $gradePoints->count() > 0 ? round($gradePoints->avg(), 2) : 0.0;
            $gpaGrade    = $this->getGpaGrade($gpa);

            $studentClassId = $studentClassMap[$sid] ?? null;
            $armLabel       = $studentClassId ? ($armLabels[$studentClassId] ?? '') : '';
            $armId          = $studentClassId ? ($classArmMap[$studentClassId] ?? null) : null;

            $studentRows[$sid] = [
                'id'                 => $sid,
                'admissionno'        => $stu->admissionno,
                'firstname'          => $stu->firstname,
                'lastname'           => $stu->lastname,
                'gender'             => $stu->gender,
                'dateofbirth'        => $stu->dateofbirth,
                'picture'            => $stu->picture,
                'arm'                => $armLabel,
                'arm_id'             => $armId,
                'schoolclassid'      => $studentClassId,
                'subjects'           => $subScores,
                'total_term'         => $totalTerm,
                'total_cum'          => $totalCum,
                'num_subjects'       => $numSubjects,
                'class_average'      => $numSubjects > 0 ? round($totalCum / $numSubjects, 1) : 0,
                'gpa'                => $gpa,
                'cgpa'               => $gpa,
                'gpa_grade'          => $gpaGrade,
                'total_grade_points' => round($gradePoints->sum(), 1),
                'position_class_cum'  => 0,
                'position_class_term' => 0,
                'position_arm_cum'    => 0,
                'position_arm_term'   => 0,
            ];
        }

        // Assign positions — class-wide across all arms combined, arm-wise per arm_id
        $studentRows = $this->assignPositions($studentRows, 'total_cum',  'position_class_cum');
        $studentRows = $this->assignPositions($studentRows, 'total_term', 'position_class_term');

        // For combined broadsheet, arm positions are per-arm
        $uniqueArmIds = collect(array_values($studentRows))->pluck('arm_id')->unique()->filter();
        foreach ($uniqueArmIds as $armId) {
            $studentRows = $this->assignPositionsFiltered($studentRows, 'total_cum',  'position_arm_cum',  'arm_id', $armId);
            $studentRows = $this->assignPositionsFiltered($studentRows, 'total_term', 'position_arm_term', 'arm_id', $armId);
        }

        $subjectStats = [];
        foreach ($subjectsMap as $sub => $subInfo) {
            $allTotals = collect(array_values($studentRows))
                ->map(fn ($r) => $r['subjects'][$sub]['total'] ?? 0)
                ->filter(fn ($v) => $v > 0);

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
            'schoolclass'     => $combinedClass,
            'schoolsession'   => $schoolsession,
            'schoolterm'      => $schoolterm,
            'assessments'     => $assessments,
            'subjects'        => $subjectsMap,
            'studentRows'     => array_values($studentRows),
            'subjectStats'    => $subjectStats,
            'selectedColumns' => $selectedColumns,
            'totalStudents'   => count($studentRows),
            'generatedAt'     => now()->format('d M Y, H:i'),
            'classgroup'      => $classgroup,
            'arm_labels'      => $armLabels,
            'is_combined'     => true,
        ];
    }

    public function getClassGroups(): JsonResponse
    {
        $groups = Schoolclass::select('schoolclass')->distinct()->orderBy('schoolclass')->pluck('schoolclass');
        return response()->json(['success' => true, 'groups' => $groups]);
    }

    // =========================================================================
    // SHARED PDF HELPERS
    // =========================================================================

    private function computePdfDimensions(
        array $data, array $selectedColumns, ?string $paperSize, string $orientation
    ): array {
        $subjectCount = count($data['subjects'] ?? []);

        $perSubjCols = 0;
        foreach ($data['assessments'] ?? [] as $a) {
            if (empty($selectedColumns) || in_array('assessment_' . $a->id, $selectedColumns)) $perSubjCols++;
        }
        // 8 possible per-subject columns
        foreach (['total','bf','cum','grade','subj_pos_class_cum','subj_pos_class_total',
                  'subj_pos_arm_cum','subj_pos_arm_total','class_average','remark'] as $col) {
            if (empty($selectedColumns) || in_array($col, $selectedColumns)) $perSubjCols++;
        }
        foreach (['gpa','cgpa','gpa_grade','num_subjects','total_grade_points',
                  'position_class_cum','position_class_term','position_arm_cum','position_arm_term'] as $col) {
            if (empty($selectedColumns) || in_array($col, $selectedColumns)) $perSubjCols += 0.5;
        }

        $frozenWidth = 200;
        $marginPt    = 57;
        $neededWidth = $frozenWidth + ($subjectCount * max(1, ceil($perSubjCols)) * 22) + $marginPt;

        if (!$paperSize) {
            $totalCols = $subjectCount * max(1, (int) $perSubjCols);
            if      ($totalCols <= 30)  $paperSize = 'A3';
            elseif  ($totalCols <= 60)  $paperSize = 'A2';
            elseif  ($totalCols <= 100) $paperSize = 'A1';
            else                        $paperSize = 'A0';
        }

        $paperHeights   = ['A0'=>2384,'A1'=>1684,'A2'=>1190,'A3'=>842,'A4'=>595];
        $standardWidths = ['A0'=>3370,'A1'=>2384,'A2'=>1684,'A3'=>1190,'A4'=>842];

        $heightPt    = $paperHeights[$paperSize]    ?? 842;
        $widthPt     = max($standardWidths[$paperSize] ?? 1190, $neededWidth + 100);
        $customPaper = [0, 0, $widthPt, $heightPt];

        return [$customPaper, $widthPt, $heightPt, $paperSize];
    }

    private function pdfOptions(): array
    {
        return [
            'isHtml5ParserEnabled'    => true,
            'isRemoteEnabled'         => true,
            'isFontSubsettingEnabled' => true,
            'defaultFont'             => 'DejaVu Sans',
            'dpi'                     => 96,
            'enable_css_float'        => false,
            'enable_javascript'       => false,
        ];
    }

    private function buildFilename(array $data, string $ext = 'pdf'): string
    {
        $className   = ($data['schoolclass']->schoolclass ?? 'Class') . ' ' . ($data['schoolclass']->arm_name ?? '');
        $sessionName = $data['schoolsession']->session ?? '';
        $termName    = $data['schoolterm']->term ?? 'Term';
        return 'Broadsheet_'
            . preg_replace('/[^A-Za-z0-9_\-]/', '_', trim($className))   . '_'
            . preg_replace('/[^A-Za-z0-9_\-]/', '_', trim($sessionName)) . '_'
            . preg_replace('/[^A-Za-z0-9_\-]/', '_', trim($termName))    . '.' . $ext;
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
}

