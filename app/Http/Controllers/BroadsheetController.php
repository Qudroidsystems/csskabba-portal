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

        $actualSubjectCount = DB::table('subjectclass as sc')
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
                'total'                 => ['label' => 'Total',               'default' => true],
                'bf'                    => ['label' => 'BF',                  'default' => true],
                'cum'                   => ['label' => 'Cum',                 'default' => true],
                'grade'                 => ['label' => 'Grade',               'default' => true],
                'position_term_class'   => ['label' => 'Term Pos (Class)',    'default' => true],
                'position_term_arm'     => ['label' => 'Term Pos (Arm)',      'default' => false],
                'position_cum_class'    => ['label' => 'Cum Pos (Class)',     'default' => true],
                'position_cum_arm'      => ['label' => 'Cum Pos (Arm)',       'default' => false],
                'class_average'         => ['label' => 'Class Avg',           'default' => true],
                'remark'                => ['label' => 'Remark',              'default' => false],
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
            'subject_count'    => $actualSubjectCount,
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
            $count           = Studentclass::whereIn('schoolclassid', $classIds)
                ->where('sessionid', $sessionid)->count();
            return response()->json(['success' => true, 'count' => $count, 'arms_count' => $matchingClasses->count()]);
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
        $schoolclass     = Schoolclass::with('classcategories')->find($schoolclassid);
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
    // HELPER: Fetch previous term's cum scores (BF source)
    // =========================================================================

    private function fetchPreviousTermCums(
        array  $studentIds,
        int    $sessionid,
        int    $currentTermId,
        array  $classIds
    ): array {
        if (empty($studentIds)) return [];

        $prevTerm = Schoolterm::where('id', '<', $currentTermId)
            ->orderByDesc('id')
            ->first();

        // Initialize empty map for all students
        $map = [];
        foreach ($studentIds as $sid) {
            $map[$sid] = [];
        }

        if (!$prevTerm) {
            // No previous term exists - return empty map (all BFs = 0)
            return $map;
        }

        $rows = Broadsheets::whereIn('broadsheet_records.student_id', $studentIds)
            ->where('broadsheets.term_id', $prevTerm->id)
            ->where('broadsheet_records.session_id', $sessionid)
            ->whereIn('broadsheet_records.schoolclass_id', $classIds)
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->select([
                'broadsheet_records.student_id',
                'broadsheet_records.subject_id',
                'broadsheets.cum',
            ])
            ->get();

        foreach ($rows as $r) {
            $map[(int)$r->student_id][(int)$r->subject_id] = (float)$r->cum;
        }

        return $map;
    }

    // =========================================================================
    // BUILD BROADSHEET DATA (single class)
    // =========================================================================

    private function buildBroadsheetData(
        int   $schoolclassid,
        int   $sessionid,
        int   $termid,
        array $selectedColumns = []
    ): array {
        $schoolInfo    = SchoolInformation::getActiveSchool() ?? new \stdClass();
        $schoolclass   = Schoolclass::with('classcategories')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.*', 'schoolarm.arm as arm_name'])
            ->where('schoolclass.id', $schoolclassid)
            ->first();
        $schoolsession = Schoolsession::find($sessionid);
        $schoolterm    = Schoolterm::find($termid);

        $assessments = collect();
        if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            $categoryIds = $schoolclass->classcategories->pluck('id');
            $assessments = Assessment::whereIn('classcategory_id', $categoryIds)->orderBy('id')->get();
        }

        // Build subjects map
        $subjectsMap    = [];
        $subjectClasses = DB::table('subjectclass as sc')
            ->join('subjectteacher as st', 'st.id', '=', 'sc.subjectteacherid')
            ->join('subject', 'subject.id', '=', 'sc.subjectid')
            ->where('sc.schoolclassid', $schoolclassid)
            ->select(['sc.subjectid', 'subject.subject as subject_name', 'subject.subject_code',
                      'sc.subjectteacherid', 'st.staffid'])
            ->distinct()->get();

        foreach ($subjectClasses as $sc) {
            $subjectsMap[(int)$sc->subjectid] = [
                'subject_id'       => (int)$sc->subjectid,
                'subject_name'     => $sc->subject_name,
                'subject_code'     => $sc->subject_code ?? '',
                'subjectteacherid' => $sc->subjectteacherid,
                'staffid'          => $sc->staffid,
            ];
        }

        // Get students in this class
        $studentIds = Studentclass::where('schoolclassid', $schoolclassid)
            ->where('sessionid', $sessionid)
            ->pluck('studentId')
            ->map(fn($v) => (int)$v)
            ->toArray();

        if (empty($studentIds)) {
            return $this->emptyBroadsheetResult(
                $schoolInfo, $schoolclass, $schoolsession, $schoolterm,
                $assessments, $subjectsMap, $selectedColumns
            );
        }

        // Fetch previous term cum scores (BF source)
        $prevCumMap = $this->fetchPreviousTermCums(
            $studentIds, $sessionid, $termid, [$schoolclassid]
        );

        // Current term broadsheet rows
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
            ->get()->groupBy('broadsheet_id');

        // Build student-subject score map
        $studentSubjectMap = [];
        foreach ($broadsheets as $row) {
            $sid = (int)$row->student_id;
            $sub = (int)$row->subject_id;

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
                $assessmentData[$a->id] = $score ? (float)$score->score : 0;
            }

            $rawTotal = (float)($row->total ?? 0);

            // BF = previous term's cum score (from DB lookup)
            // If no previous term cum exists, BF = 0
            $bf = isset($prevCumMap[$sid][$sub]) ? $prevCumMap[$sid][$sub] : 0;

            // CUM rule:
            //   • BF > 0  → (BF + Total) ÷ 2
            //   • BF = 0  → Total  (first term or no prior record)
            $cum = $bf > 0 ? round(($bf + $rawTotal) / 2, 1) : $rawTotal;

            $studentSubjectMap[$sid][$sub] = [
                'total'         => $rawTotal,
                'bf'            => $bf,
                'cum'           => $cum,
                'grade'         => $row->grade ?? '-',
                'remark'        => $row->remark ?? '-',
                'position'      => $row->position ?? '-',
                'class_average' => (float)($row->class_average ?? 0),
                'assessments'   => $assessmentData,
            ];
        }

        // Build arm map for the class (all students in same class = same arm)
        $armMap = [];
        foreach ($studentIds as $sid) {
            $armMap[$sid] = $schoolclassid;
        }

        // Get arm label for this class
        $armLabels = [];
        $armLabels[$schoolclassid] = $schoolclass->arm_name ?? '';

        return $this->assembleStudentRows(
            $studentIds, $sessionid, $schoolclassid, null,
            $studentSubjectMap, $subjectsMap, $assessments,
            $schoolInfo, $schoolclass, $schoolsession, $schoolterm,
            $selectedColumns, $armLabels, $armMap, false
        );
    }

    // =========================================================================
    // SHARED: Assemble student rows, compute positions, build result array
    // =========================================================================

    private function assembleStudentRows(
        array  $studentIds,
        int    $sessionid,
        ?int   $schoolclassid,
        ?array $classIds,
        array  $studentSubjectMap,
        array  $subjectsMap,
        $assessments,
        $schoolInfo,
        $schoolclass,
        $schoolsession,
        $schoolterm,
        array  $selectedColumns,
        array  $armLabels       = [],
        ?array $armMap          = null,
        bool   $isCombined      = false
    ): array {
        // Build the student info query
        $query = Studentclass::where('sessionid', $sessionid);
        if ($schoolclassid) {
            $query->where('schoolclassid', $schoolclassid);
        } else {
            $query->whereIn('schoolclassid', $classIds ?? []);
        }

        $studentInfoRows = $query
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
                'studentclass.schoolclassid',
            ])
            ->orderBy('studentRegistration.lastname')
            ->orderBy('studentRegistration.firstname')
            ->get();

        $studentRows = [];
        foreach ($studentInfoRows as $stu) {
            $sid       = (int)$stu->id;
            $subScores = $studentSubjectMap[$sid] ?? [];

            $totalValues  = [];  // Term scores
            $cumValues    = [];  // Cumulative scores
            $gradePointsA = [];
            $subjectCount  = 0;

            // Only count subjects that have actual scores
            foreach ($subScores as $subId => $subData) {
                $hasScore = ($subData['total'] ?? 0) > 0 || ($subData['cum'] ?? 0) > 0;
                if ($hasScore) {
                    $subjectCount++;
                    if (($subData['total'] ?? 0) > 0) {
                        $totalValues[]  = $subData['total'];
                        $gradePointsA[] = $this->getGradePoint($subData['total']);
                    }
                    if (($subData['cum'] ?? 0) > 0) {
                        $cumValues[] = $subData['cum'];
                    }
                }
            }

            // Term total = sum of all term scores
            $totalTerm   = array_sum($totalValues);
            // Cum total = sum of all cumulative scores
            $totalCum    = array_sum($cumValues);
            $numSubjects = $subjectCount;

            $gpa = count($gradePointsA) > 0
                ? round(array_sum($gradePointsA) / count($gradePointsA), 2) : 0.0;

            $armLabel = '';
            if ($isCombined && $armMap && isset($armMap[$sid])) {
                $armLabel = $armLabels[$armMap[$sid]] ?? '';
            } elseif (!$isCombined && $armLabels) {
                $armLabel = $armLabels[$schoolclassid] ?? '';
            }

            $studentRows[$sid] = [
                'id'                 => $sid,
                'admissionno'        => $stu->admissionno,
                'firstname'          => $stu->firstname,
                'lastname'           => $stu->lastname,
                'gender'             => $stu->gender,
                'dateofbirth'        => $stu->dateofbirth,
                'picture'            => $stu->picture,
                'arm'                => $armLabel,
                'schoolclassid'      => (int)$stu->schoolclassid,
                'subjects'           => $subScores,
                'total_cum'          => round($totalCum, 1),
                'total_term'         => round($totalTerm, 1),
                'num_subjects'       => $numSubjects,
                'class_average'      => $numSubjects > 0 ? round($totalCum / $numSubjects, 1) : 0,
                'gpa'                => $gpa,
                'cgpa'               => $gpa,
                'gpa_grade'          => $this->getGpaGrade($gpa),
                'total_grade_points' => round(array_sum($gradePointsA), 1),
            ];
        }

        // Calculate all position types
        $posMapsCum  = $this->buildPositionMaps($studentRows, 'total_cum', $armMap);
        $posMapsTerm = $this->buildPositionMaps($studentRows, 'total_term', $armMap);

        // Debug: Log position maps to verify
        Log::info('Position calculation', [
            'student_count' => count($studentRows),
            'cum_class_positions' => $posMapsCum['class'],
            'term_class_positions' => $posMapsTerm['class'],
            'cum_arm_positions' => $posMapsCum['arm'],
            'term_arm_positions' => $posMapsTerm['arm'],
        ]);

        // Assign positions to each student
        foreach ($studentRows as $sid => &$row) {
            $row['position_cum_class']  = $posMapsCum['class'][$sid]  ?? 0;
            $row['position_cum_arm']    = $posMapsCum['arm'][$sid]    ?? 0;
            $row['position_term_class'] = $posMapsTerm['class'][$sid] ?? 0;
            $row['position_term_arm']   = $posMapsTerm['arm'][$sid]   ?? 0;

            // Backward compatibility
            $row['position_cum']  = $row['position_cum_class'];
            $row['position_term'] = $row['position_term_class'];
        }
        unset($row);

        $subjectStats = $this->buildSubjectStats($subjectsMap, $studentRows);
        uasort($subjectsMap, fn($a, $b) => strcmp($a['subject_name'], $b['subject_name']));

        $result = [
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

        if ($isCombined) {
            $result['arm_labels']  = $armLabels;
            $result['is_combined'] = true;
        }

        return $result;
    }

    // =========================================================================
    // HELPER: Build position maps (class-wide and arm-based)
    // =========================================================================

    private function buildPositionMaps(array $studentRows, string $key, ?array $armMap = null): array
    {
        $positionMaps = [
            'class' => [],
            'arm'   => [],
        ];

        if (empty($studentRows)) {
            return $positionMaps;
        }

        // =====================================================================
        // CLASS-WIDE POSITIONS
        // =====================================================================
        $sorted = $studentRows;
        uasort($sorted, fn($a, $b) => ($b[$key] ?? 0) <=> ($a[$key] ?? 0));

        $prevVal = null;
        $prevPos = 0;
        $counter = 0;

        foreach ($sorted as $sid => $row) {
            $counter++;
            $val = (float)($row[$key] ?? 0);

            if ($val <= 0) {
                // Students with no scores get no position
                $positionMaps['class'][(int)$sid] = 0;
                continue;
            }

            if ($prevVal !== null && $val === $prevVal) {
                // Same score = same position (dense ranking)
                $positionMaps['class'][(int)$sid] = $prevPos;
            } else {
                $positionMaps['class'][(int)$sid] = $counter;
                $prevPos = $counter;
            }
            $prevVal = $val;
        }

        // =====================================================================
        // ARM-BASED POSITIONS
        // =====================================================================
        if ($armMap && !empty($armMap)) {
            // Group students by arm
            $armGroups = [];
            foreach ($studentRows as $sid => $row) {
                $arm = $armMap[$sid] ?? 'default';
                $armGroups[$arm][$sid] = $row;
            }

            foreach ($armGroups as $arm => $armStudents) {
                uasort($armStudents, fn($a, $b) => ($b[$key] ?? 0) <=> ($a[$key] ?? 0));

                $prevVal = null;
                $prevPos = 0;
                $counter = 0;

                foreach ($armStudents as $sid => $row) {
                    $counter++;
                    $val = (float)($row[$key] ?? 0);

                    if ($val <= 0) {
                        $positionMaps['arm'][(int)$sid] = 0;
                        continue;
                    }

                    if ($prevVal !== null && $val === $prevVal) {
                        $positionMaps['arm'][(int)$sid] = $prevPos;
                    } else {
                        $positionMaps['arm'][(int)$sid] = $counter;
                        $prevPos = $counter;
                    }
                    $prevVal = $val;
                }
            }
        }

        return $positionMaps;
    }

    // =========================================================================
    // HELPER: Subject stats
    // =========================================================================

    private function buildSubjectStats(array $subjectsMap, array $studentRows): array
    {
        $subjectStats = [];
        foreach ($subjectsMap as $sub => $subInfo) {
            $totals = [];
            foreach ($studentRows as $row) {
                $val = $row['subjects'][$sub]['total'] ?? 0;
                if ($val > 0) $totals[] = $val;
            }
            $count = count($totals);
            $subjectStats[$sub] = [
                'avg'     => $count > 0 ? round(array_sum($totals) / $count, 1) : 0,
                'highest' => $count > 0 ? max($totals) : 0,
                'lowest'  => $count > 0 ? min($totals) : 0,
                'passed'  => count(array_filter($totals, fn($v) => $v >= 40)),
                'failed'  => count(array_filter($totals, fn($v) => $v < 40)),
            ];
        }
        return $subjectStats;
    }

    // =========================================================================
    // HELPER: Empty result
    // =========================================================================

    private function emptyBroadsheetResult(
        $schoolInfo, $schoolclass, $schoolsession, $schoolterm,
        $assessments, $subjectsMap, array $selectedColumns, array $extra = []
    ): array {
        return array_merge([
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
        ], $extra);
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

            $data = $this->buildBroadsheetData(
                (int)$validated['schoolclassid'],
                (int)$validated['sessionid'],
                (int)$validated['termid'],
                $request->input('selectedColumns', [])
            );

            $data['school_logo_base64'] = $this->getLogoBase64($data['schoolInfo']);
            $data['pagetitle']          = 'Class Broadsheet – Web View';

            return view('broadsheet.web', $data);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'Invalid input.');
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
                (int)$validated['schoolclassid'],
                (int)$validated['sessionid'],
                (int)$validated['termid'],
                $selectedColumns
            );
            $data['school_logo_base64'] = $this->getLogoBase64($data['schoolInfo']);

            $subjectCount = count($data['subjects'] ?? []);
            $perSubjCols  = $this->countActivePerSubjectCols($data['assessments'] ?? collect(), $selectedColumns);

            if (!$paperSize) {
                $total = $subjectCount * max(1, $perSubjCols);
                $paperSize = $total <= 30 ? 'A3' : ($total <= 60 ? 'A2' : ($total <= 100 ? 'A1' : 'A0'));
            }

            [$widthPt, $heightPt] = $this->computePdfDimensions($paperSize, $subjectCount, $perSubjCols);
            $data['pdf_width_pt']   = $widthPt;
            $data['pdf_height_pt']  = $heightPt;
            $data['pdf_paper_size'] = $paperSize;

            $pdf = Pdf::loadView('broadsheet.pdf', $data)
                ->setPaper([0, 0, $widthPt, $heightPt], $orientation)
                ->setOptions([
                    'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true,
                    'isFontSubsettingEnabled' => true, 'defaultFont' => 'DejaVu Sans',
                    'dpi' => 96, 'enable_css_float' => false, 'enable_javascript' => false,
                ]);

            return $pdf->stream($this->buildFilename(
                ($data['schoolclass']->schoolclass ?? 'Class') . ' ' . ($data['schoolclass']->arm_name ?? ''),
                $data['schoolsession']->session ?? '',
                $data['schoolterm']->term ?? 'Term'
            ));
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
                $validated['schoolclassid'], $validated['sessionid'], $validated['termid'], $selectedColumns
            );

            return Excel::download(
                new \App\Exports\BroadsheetExport($data),
                $this->buildFilename(
                    ($data['schoolclass']->schoolclass ?? 'Class') . ' ' . ($data['schoolclass']->arm_name ?? ''),
                    $data['schoolsession']->session ?? '',
                    $data['schoolterm']->term ?? 'Term', 'xlsx'
                )
            );
        } catch (\Exception $e) {
            Log::error('Broadsheet Excel export error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to generate Excel: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // ALL CLASSES WEB VIEW
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
                (int)$validated['sessionid'],
                (int)$validated['termid'],
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

    // =========================================================================
    // ALL CLASSES EXPORT PDF
    // =========================================================================

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
                (int)$validated['sessionid'],
                (int)$validated['termid'],
                $selectedColumns
            );
            $data['school_logo_base64'] = $this->getLogoBase64($data['schoolInfo']);
            $data['is_combined']        = true;

            $subjectCount = count($data['subjects'] ?? []);
            $perSubjCols  = $this->countActivePerSubjectCols($data['assessments'] ?? collect(), $selectedColumns);
            [$widthPt, $heightPt] = $this->computePdfDimensions($paperSize, $subjectCount, $perSubjCols);

            $pdf = Pdf::loadView('broadsheet.pdf', $data)
                ->setPaper([0, 0, $widthPt, $heightPt], $orientation)
                ->setOptions([
                    'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true,
                    'isFontSubsettingEnabled' => true, 'defaultFont' => 'DejaVu Sans',
                    'dpi' => 96, 'enable_css_float' => false, 'enable_javascript' => false,
                ]);

            return $pdf->stream($this->buildFilename(
                $validated['classgroup'],
                $data['schoolsession']->session ?? '',
                $data['schoolterm']->term ?? ''
            ));
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
        int    $sessionid,
        int    $termid,
        array  $selectedColumns = []
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

        $combinedClass = (object)[
            'schoolclass' => $classgroup,
            'arm_name'    => $matchingClasses->isEmpty()
                ? '(All Arms)'
                : '(' . $matchingClasses->pluck('arm_name')->filter()->implode(', ') . ')',
            'id'          => null,
        ];

        if ($matchingClasses->isEmpty()) {
            return $this->emptyBroadsheetResult(
                $schoolInfo, $combinedClass, $schoolsession, $schoolterm,
                collect(), [], $selectedColumns,
                ['classgroup' => $classgroup, 'arm_labels' => [], 'is_combined' => true]
            );
        }

        $assessments = collect();
        foreach ($matchingClasses as $cls) {
            if ($cls->classcategories->isNotEmpty()) {
                $assessments = Assessment::whereIn(
                    'classcategory_id', $cls->classcategories->pluck('id')
                )->orderBy('id')->get();
                break;
            }
        }

        $classIds = $matchingClasses->pluck('id')->map(fn($v) => (int)$v)->toArray();

        $subjectsMap    = [];
        $subjectClasses = DB::table('subjectclass as sc')
            ->join('subjectteacher as st', 'st.id', '=', 'sc.subjectteacherid')
            ->join('subject', 'subject.id', '=', 'sc.subjectid')
            ->whereIn('sc.schoolclassid', $classIds)
            ->select(['sc.subjectid', 'subject.subject as subject_name', 'subject.subject_code'])
            ->distinct()->get();

        foreach ($subjectClasses as $sc) {
            $subjectsMap[(int)$sc->subjectid] = [
                'subject_id'   => (int)$sc->subjectid,
                'subject_name' => $sc->subject_name,
                'subject_code' => $sc->subject_code ?? '',
            ];
        }

        $studentClassRecords = Studentclass::whereIn('schoolclassid', $classIds)
            ->where('sessionid', $sessionid)
            ->get(['studentId', 'schoolclassid']);

        $armMap = [];
        foreach ($studentClassRecords as $r) {
            $armMap[(int)$r->studentId] = (int)$r->schoolclassid;
        }
        $allStudentIds = array_keys($armMap);

        if (empty($allStudentIds)) {
            return $this->emptyBroadsheetResult(
                $schoolInfo, $combinedClass, $schoolsession, $schoolterm,
                $assessments, $subjectsMap, $selectedColumns,
                ['classgroup' => $classgroup,
                 'arm_labels' => $matchingClasses->pluck('arm_name', 'id')->toArray(),
                 'is_combined' => true]
            );
        }

        $prevCumMap = $this->fetchPreviousTermCums($allStudentIds, $sessionid, $termid, $classIds);

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
                'broadsheets.total',
                'broadsheets.bf',
                'broadsheets.cum',
                'broadsheets.grade',
                'broadsheets.remark',
                'broadsheets.subject_position_class as position',
                'broadsheets.avg as class_average',
            ])
            ->get();

        $broadsheetIds       = $broadsheets->pluck('broadsheet_id')->unique()->toArray();
        $assessmentScoresAll = BroadsheetAssessmentScore::whereIn('broadsheet_id', $broadsheetIds)
            ->get()->groupBy('broadsheet_id');

        $studentSubjectMap = [];
        foreach ($broadsheets as $row) {
            $sid = (int)$row->student_id;
            $sub = (int)$row->subject_id;

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
                $assessmentData[$a->id] = $score ? (float)$score->score : 0;
            }

            $rawTotal = (float)($row->total ?? 0);
            $bf       = isset($prevCumMap[$sid][$sub]) ? $prevCumMap[$sid][$sub] : 0;
            $cum      = $bf > 0 ? round(($bf + $rawTotal) / 2, 1) : $rawTotal;

            $studentSubjectMap[$sid][$sub] = [
                'total'         => $rawTotal,
                'bf'            => $bf,
                'cum'           => $cum,
                'grade'         => $row->grade ?? '-',
                'remark'        => $row->remark ?? '-',
                'position'      => $row->position ?? '-',
                'class_average' => (float)($row->class_average ?? 0),
                'assessments'   => $assessmentData,
            ];
        }

        $armLabels = $matchingClasses->pluck('arm_name', 'id')
            ->mapWithKeys(fn($v, $k) => [(int)$k => $v])->toArray();

        return $this->assembleStudentRows(
            $allStudentIds, $sessionid, null, $classIds,
            $studentSubjectMap, $subjectsMap, $assessments,
            $schoolInfo, $combinedClass, $schoolsession, $schoolterm,
            $selectedColumns, $armLabels, $armMap, true
        );
    }

    // =========================================================================
    // AJAX: Get class groups
    // =========================================================================

    public function getClassGroups(): JsonResponse
    {
        $groups = Schoolclass::select('schoolclass')->distinct()->orderBy('schoolclass')->pluck('schoolclass');
        return response()->json(['success' => true, 'groups' => $groups]);
    }

    // =========================================================================
    // PDF HELPERS
    // =========================================================================

    private function countActivePerSubjectCols($assessments, array $selectedColumns): float
    {
        $cols = 0.0;
        foreach ($assessments as $a) {
            if (empty($selectedColumns) || in_array('assessment_' . $a->id, $selectedColumns)) $cols++;
        }
        foreach (['total', 'bf', 'cum', 'grade', 'position_term_class', 'position_term_arm', 'position_cum_class', 'position_cum_arm', 'class_average', 'remark'] as $col) {
            if (empty($selectedColumns) || in_array($col, $selectedColumns)) $cols++;
        }
        foreach (['gpa', 'cgpa', 'gpa_grade', 'num_subjects', 'total_grade_points'] as $col) {
            if (empty($selectedColumns) || in_array($col, $selectedColumns)) $cols += 0.5;
        }
        return $cols;
    }

    private function computePdfDimensions(string $paperSize, int $subjectCount, float $perSubjCols): array
    {
        $heights = ['A0' => 2384, 'A1' => 1684, 'A2' => 1190, 'A3' => 842,  'A4' => 595];
        $widths  = ['A0' => 3370, 'A1' => 2384, 'A2' => 1684, 'A3' => 1190, 'A4' => 842];
        $needed  = 200 + ($subjectCount * max(1, ceil($perSubjCols)) * 22) + 57;
        return [
            max($widths[$paperSize] ?? 1190, $needed + 100),
            $heights[$paperSize] ?? 842,
        ];
    }

    private function buildFilename(string $class, string $session, string $term, string $ext = 'pdf'): string
    {
        $c = fn(string $s) => preg_replace('/[^A-Za-z0-9_\-]/', '_', trim($s));
        return 'Broadsheet_' . $c($class) . '_' . $c($session) . '_' . $c($term) . '.' . $ext;
    }

    // =========================================================================
    // GRADE HELPERS
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

    private function getLogoBase64($schoolInfo): string
    {
        $placeholder = 'data:image/svg+xml;base64,' . base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80">'
            . '<rect width="80" height="80" rx="40" fill="#1e3a5f"/>'
            . '<text x="40" y="45" text-anchor="middle" fill="white" font-family="Arial" font-size="14" font-weight="bold">SCH</text>'
            . '</svg>'
        );

        if (!$schoolInfo || empty($schoolInfo->school_logo)) return $placeholder;

        foreach ([
            storage_path('app/public/' . $schoolInfo->school_logo),
            public_path('storage/' . $schoolInfo->school_logo),
            public_path($schoolInfo->school_logo),
        ] as $path) {
            if (file_exists($path) && filesize($path) > 100) {
                return 'data:' . (mime_content_type($path) ?: 'image/jpeg')
                    . ';base64,' . base64_encode(file_get_contents($path));
            }
        }

        return $placeholder;
    }
}
