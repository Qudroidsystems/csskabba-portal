<?php

namespace App\Http\Controllers;

use App\Models\Broadsheets;
use App\Models\Principalscomment;
use App\Models\Schoolclass;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Studentclass;
use App\Models\Studentpersonalityprofile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MyPrincipalsCommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View my-principals-comment',   ['only' => ['index']]);
        $this->middleware('permission:Update my-principals-comment', ['only' => ['classBroadsheet', 'updateComments']]);
    }

    // =========================================================================
    // INDEX
    // =========================================================================

    public function index()
    {
        $pagetitle = "My Principal's Comment Assignments";

        $assignments = Principalscomment::where('staffId', Auth::id())
            ->join('schoolclass', 'principalscomments.schoolclassid', '=', 'schoolclass.id')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('schoolsession', 'principalscomments.sessionid', '=', 'schoolsession.id')
            ->leftJoin('schoolterm', 'principalscomments.termid', '=', 'schoolterm.id')
            ->select([
                'principalscomments.id',
                'schoolclass.id as schoolclassid',
                'schoolclass.schoolclass as sclass',
                'schoolarm.arm as schoolarm',
                'schoolsession.id as session_id',
                'schoolsession.session as session_name',
                'schoolterm.id as term_id',
                'schoolterm.term as term_name',
                'principalscomments.updated_at',
            ])
            ->orderBy('schoolclass.schoolclass')
            ->orderBy('schoolarm.arm')
            ->get();

        return view('myprincipalscomment.index')->with(compact('assignments', 'pagetitle'));
    }

    // =========================================================================
    // CLASS BROADSHEET
    // =========================================================================

    public function classBroadsheet(Request $request, $schoolclassid, $sessionid, $termid)
    {
        $pagetitle = "Principal's Comment & Class Broadsheet";

        // Scoring mode: 'cumulative' (default) or 'term'
        $scoringMode = $request->get('scoring_mode', 'cumulative');
        if (!in_array($scoringMode, ['cumulative', 'term'])) {
            $scoringMode = 'cumulative';
        }

        // ------------------------------------------------------------------
        // 1.  Students enrolled in this class / session
        // ------------------------------------------------------------------
        $students = Studentclass::where('schoolclassid', $schoolclassid)
            ->where('sessionid', $sessionid)
            ->join('studentRegistration', 'studentRegistration.id', '=', 'studentclass.studentId')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->orderBy('studentRegistration.lastname')
            ->orderBy('studentRegistration.firstname')
            ->get([
                'studentRegistration.id          as id',
                'studentRegistration.admissionNo as admissionNo',
                'studentRegistration.firstname   as fname',
                'studentRegistration.lastname    as lastname',
                'studentRegistration.othername   as othername',
                'studentRegistration.gender      as gender',
                'studentpicture.picture          as picture',
            ]);

        // ------------------------------------------------------------------
        // 2.  School / class meta
        // ------------------------------------------------------------------
        $schoolclass           = Schoolclass::with(['arm', 'classcategories'])->findOrFail($schoolclassid);
        $schoolclass->arm_name = $schoolclass->arm?->arm ?? '';

        $schoolterm    = Schoolterm::find($termid)?->term         ?? 'N/A';
        $schoolsession = Schoolsession::find($sessionid)?->session ?? 'N/A';

        $isSenior = $schoolclass->classcategories->isNotEmpty()
            ? ($schoolclass->classcategories->first()->is_senior ?? false)
            : false;

        // ------------------------------------------------------------------
        // 3.  Broadsheet rows
        // ------------------------------------------------------------------
        $broadsheetRows = Broadsheets::where('broadsheet_records.schoolclass_id', $schoolclassid)
            ->where('broadsheets.term_id', $termid)
            ->where('broadsheet_records.session_id', $sessionid)
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->join('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->orderBy('subject.subject')
            ->select([
                'broadsheet_records.student_id',
                'subject.subject as subject_name',
                'broadsheets.total',   // term score
                'broadsheets.bf',
                'broadsheets.cum',     // cumulative
                'broadsheets.grade',
                'broadsheets.remark',
            ])
            ->get();

        // ------------------------------------------------------------------
        // 4.  Distinct, ordered subject list
        // ------------------------------------------------------------------
        $subjects = $broadsheetRows
            ->pluck('subject_name')
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        // ------------------------------------------------------------------
        // 5.  O(1) lookup maps  [student_id][subject_name] => score
        // ------------------------------------------------------------------
        $termScoreMap = [];
        $cumScoreMap  = [];

        foreach ($broadsheetRows as $row) {
            $sid  = $row->student_id;
            $subj = $row->subject_name;

            $termScoreMap[$sid][$subj] = $row->total ?? 0;
            $cumScoreMap[$sid][$subj]  = $row->cum   ?? 0;
        }

        // ------------------------------------------------------------------
        // 6.  Saved principal comments keyed by student id
        // ------------------------------------------------------------------
        $profiles = Studentpersonalityprofile::where('schoolclassid', $schoolclassid)
            ->where('termid',    $termid)
            ->where('sessionid', $sessionid)
            ->pluck('principalscomment', 'studentid')
            ->toArray();

        // ------------------------------------------------------------------
        // 7.  Grade analysis per student
        //     Grades computed for BOTH term and cumulative scores always.
        //     $activeScoreMap is determined by $scoringMode for comment/analytics.
        // ------------------------------------------------------------------
        $studentGrades        = [];   // used for tooltip grade table
        $studentGradeAnalysis = [];   // used for comment generation

        foreach ($students as $student) {
            $sid = $student->id;

            $studentGradeAnalysis[$sid] = [
                'grades'        => [],
                'counts'        => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0],
                'weak_subjects' => [],
            ];

            foreach ($subjects as $subject) {
                $cumTotal  = $cumScoreMap[$sid][$subject]  ?? 0;
                $termTotal = $termScoreMap[$sid][$subject] ?? 0;

                // Grade for CUMULATIVE
                [$cumGrade, $cumGradeLetter] = $this->gradeFromScore((float) $cumTotal, $isSenior);

                // Grade for TERM
                [$termGrade, $termGradeLetter] = $this->gradeFromScore((float) $termTotal, $isSenior);

                // Active score/grade depends on chosen mode
                $activeScore       = $scoringMode === 'term' ? $termTotal  : $cumTotal;
                $activeGrade       = $scoringMode === 'term' ? $termGrade  : $cumGrade;
                $activeGradeLetter = $scoringMode === 'term' ? $termGradeLetter : $cumGradeLetter;

                $entry = [
                    'subject'           => $subject,
                    // Cumulative
                    'cum_score'         => $cumTotal,
                    'cum_grade'         => $cumGrade,
                    'cum_grade_letter'  => $cumGradeLetter,
                    // Term
                    'term_score'        => $termTotal,
                    'term_grade'        => $termGrade,
                    'term_grade_letter' => $termGradeLetter,
                    // Legacy fields for tooltip compatibility
                    'score'             => $activeScore,
                    'grade'             => $activeGrade,
                    'grade_letter'      => $activeGradeLetter,
                ];

                $studentGrades[$sid][]                  = $entry;
                $studentGradeAnalysis[$sid]['grades'][] = $entry;
                $studentGradeAnalysis[$sid]['counts'][$activeGradeLetter]++;

                if (in_array($activeGradeLetter, ['C', 'D', 'E', 'F'])) {
                    $studentGradeAnalysis[$sid]['weak_subjects'][] = [
                        'subject'          => $subject,
                        'grade'            => $activeGrade,
                        'grade_letter'     => $activeGradeLetter,
                        'cumulative_score' => $cumTotal,
                        'term_score'       => $termTotal,
                    ];
                }
            }
        }

        // ------------------------------------------------------------------
        // 8.  Standard personalised comments  (second-person)
        // ------------------------------------------------------------------
        $baseTemplates = [
            "Excellent result {NAME}, keep it up!",
            "A very good result {NAME}, keep it up!",
            "Good result {NAME}, keep it up!",
            "Average result {NAME}, there's still room for improvement next term.",
            "{NAME}, you can do better next term.",
            "{NAME}, you need to sit up and be serious.",
            "{NAME}, wake up and be serious.",
        ];

        $standardPersonalizedComments = [];

        foreach ($students as $student) {
            $sid       = $student->id;
            $firstName = $student->fname;

            $weakSubjects = $studentGradeAnalysis[$sid]['weak_subjects'] ?? [];
            $advice       = '';

            if (!empty($weakSubjects)) {
                usort($weakSubjects, fn ($a, $b) =>
                    ['F' => 0, 'E' => 1, 'D' => 2, 'C' => 3][$a['grade_letter']]
                    <=>
                    ['F' => 0, 'E' => 1, 'D' => 2, 'C' => 3][$b['grade_letter']]
                );

                $subjectList = array_map(
                    fn ($ws) => strtoupper($ws['subject']) . ' (' . $ws['grade'] . ')',
                    $weakSubjects
                );
                $advice = "\n\nYou should work harder in "
                        . $this->formatList($subjectList)
                        . " to improve your performance.";
            }

            $options = [];
            foreach ($baseTemplates as $template) {
                $options[] = str_replace('{NAME}', $firstName, $template) . $advice;
            }

            $standardPersonalizedComments[$sid] = $options;
        }

        // ------------------------------------------------------------------
        // 9.  Intelligent comments  (third-person, gender-aware)
        // ------------------------------------------------------------------
        $intelligentComments = [];

        foreach ($students as $student) {
            $sid       = $student->id;
            $firstName = $student->fname;
            $analysis  = $studentGradeAnalysis[$sid];

            $gradeParts = [];
            foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $g) {
                $count = $analysis['counts'][$g] ?? 0;
                if ($count > 0) {
                    $gradeParts[] = "$count {$g}" . ($count > 1 ? "'s" : '');
                }
            }
            $gradeSummary = !empty($gradeParts) ? $this->formatList($gradeParts) : 'no grades recorded';

            $totalGrades    = array_sum($analysis['counts']);
            $goodGrades     = ($analysis['counts']['A'] ?? 0) + ($analysis['counts']['B'] ?? 0);
            $percentageGood = $totalGrades > 0 ? ($goodGrades / $totalGrades) * 100 : 0;

            $baseComment = match (true) {
                $percentageGood >= 80 => "Excellent result {NAME}, keep it up!",
                $percentageGood >= 70 => "A very good result {NAME}, keep it up!",
                $percentageGood >= 60 => "Good result {NAME}, keep it up!",
                $percentageGood >= 50 => "Average result {NAME}, there's still room for improvement next term.",
                $percentageGood >= 40 => "{NAME}, you can do better next term.",
                $percentageGood >= 30 => "{NAME}, you need to sit up and be serious.",
                default               => "{NAME}, wake up and be serious.",
            };

            $termInfo = match (true) {
                in_array($schoolterm, ['2nd Term', 'Second Term']) => ' (Cumulative average of 1st and 2nd terms)',
                in_array($schoolterm, ['3rd Term', 'Third Term'])  => ' (Cumulative average of 1st, 2nd and 3rd terms)',
                default                                            => '',
            };

            // Only include termInfo in comment if using cumulative mode
            $modeTermInfo = $scoringMode === 'cumulative' ? $termInfo : '';

            $comment    = "$firstName has $gradeSummary$modeTermInfo. "
                        . str_replace('{NAME}', $firstName, $baseComment);
            $pronoun    = strtoupper($student->gender) === 'MALE' ? 'He'  : 'She';
            $possessive = strtoupper($student->gender) === 'MALE' ? 'his' : 'her';

            $weakSubjects = $analysis['weak_subjects'] ?? [];
            if (!empty($weakSubjects)) {
                usort($weakSubjects, fn ($a, $b) =>
                    ['F' => 0, 'E' => 1, 'D' => 2, 'C' => 3][$a['grade_letter']]
                    <=>
                    ['F' => 0, 'E' => 1, 'D' => 2, 'C' => 3][$b['grade_letter']]
                );
                $subjectList  = array_map(fn ($ws) => $ws['subject'] . ' (' . $ws['grade'] . ')', $weakSubjects);
                $comment     .= "\n\n$pronoun should work harder in "
                              . $this->formatList($subjectList)
                              . " to improve $possessive performance.";
            }

            $intelligentComments[$sid] = $comment;
        }

        // ------------------------------------------------------------------
        // 10. Student analytics  (totals, averages, positions)
        //     Primary analytics follow scoring mode; both are always computed.
        // ------------------------------------------------------------------
        $studentTotals     = [];
        $studentTermTotals = [];

        foreach ($students as $student) {
            $sid     = $student->id;
            $cumSum  = 0;
            $termSum = 0;
            $count   = 0;

            foreach ($subjects as $subject) {
                $cum  = $cumScoreMap[$sid][$subject]  ?? null;
                $term = $termScoreMap[$sid][$subject] ?? null;

                if (!is_null($cum) && $cum > 0) {
                    $cumSum += $cum;
                    $count++;
                }
                if (!is_null($term)) {
                    $termSum += $term;
                }
            }

            $studentTotals[$sid] = [
                'total'    => $cumSum,
                'average'  => $count > 0 ? round($cumSum  / $count, 1) : 0,
                'subjects' => $count,
            ];
            $studentTermTotals[$sid] = [
                'total'   => $termSum,
                'average' => $count > 0 ? round($termSum / $count, 1) : 0,
            ];
        }

        // Class averages for both modes
        $classCumSum      = array_sum(array_column($studentTotals, 'total'));
        $classCumSubjects = array_sum(array_column($studentTotals, 'subjects'));
        $classCumAverage  = $classCumSubjects > 0 ? round($classCumSum / $classCumSubjects, 1) : 0;

        $classTermSum      = array_sum(array_column($studentTermTotals, 'total'));
        $classTermAverage  = $classCumSubjects > 0 ? round($classTermSum / $classCumSubjects, 1) : 0;

        $activeClassAverage = $scoringMode === 'term' ? $classTermAverage : $classCumAverage;

        $classAnalytics = [
            'average'        => $activeClassAverage,
            'cum_average'    => $classCumAverage,
            'term_average'   => $classTermAverage,
            'total_students' => $students->count(),
        ];

        // Positions by active scoring mode (descending)
        $sortedStudents = $students
            ->sortByDesc(fn ($s) =>
                $scoringMode === 'term'
                    ? ($studentTermTotals[$s->id]['average'] ?? 0)
                    : ($studentTotals[$s->id]['average']     ?? 0)
            )
            ->values();

        $positions = [];
        $rank      = 1;
        $prevAvg   = null;

        foreach ($sortedStudents as $index => $student) {
            $avg = $scoringMode === 'term'
                ? $studentTermTotals[$student->id]['average']
                : $studentTotals[$student->id]['average'];

            if ($index > 0 && $avg < $prevAvg) {
                $rank = $index + 1;
            }
            $positions[$student->id] = $rank;
            $prevAvg = $avg;
        }

        $studentAnalytics = [];
        foreach ($students as $student) {
            $sid      = $student->id;
            $position = $positions[$sid] ?? null;

            $studentAnalytics[$sid] = [
                // Cumulative
                'total_score'  => $studentTotals[$sid]['total'],
                'average'      => $studentTotals[$sid]['average'],
                // Term
                'term_total'   => $studentTermTotals[$sid]['total'],
                'term_average' => $studentTermTotals[$sid]['average'],
                'subjects'     => $studentTotals[$sid]['subjects'],
                'position'     => $position,
                'position_text' => $position ? $this->getPositionSuffix($position) : '-',
                'grade_counts'  => $studentGradeAnalysis[$sid]['counts'] ?? [],
            ];
        }

        // ------------------------------------------------------------------
        // 11. Render view
        // ------------------------------------------------------------------
        return view('myprincipalscomment.classbroadsheet')
            ->with(compact(
                'students',
                'subjects',
                'termScoreMap',
                'cumScoreMap',
                'profiles',
                'schoolclass',
                'schoolterm',
                'schoolsession',
                'schoolclassid',
                'sessionid',
                'termid',
                'pagetitle',
                'studentGrades',
                'studentGradeAnalysis',
                'intelligentComments',
                'standardPersonalizedComments',
                'studentAnalytics',
                'classAnalytics',
                'isSenior',
                'scoringMode'
            ));
    }

    // =========================================================================
    // UPDATE COMMENTS
    // =========================================================================

    public function updateComments(Request $request, $schoolclassid, $sessionid, $termid)
    {
        Log::info('Update Comments Request Received', [
            'schoolclassid'  => $schoolclassid,
            'sessionid'      => $sessionid,
            'termid'         => $termid,
            'auth_id'        => Auth::id(),
            'request_method' => $request->method(),
            'ajax'           => $request->ajax(),
        ]);

        $request->validate(['teacher_comments.*' => 'nullable|string|max:5000']);

        $comments     = $request->input('teacher_comments', []);
        $updatedCount = 0;
        $createdCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($comments as $studentId => $comment) {
                if (is_null($comment) || trim($comment) === '') {
                    $skippedCount++;
                    continue;
                }

                $comment = trim(strip_tags($comment));
                $comment = html_entity_decode($comment, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                $existing = Studentpersonalityprofile::where('studentid',    $studentId)
                    ->where('schoolclassid', $schoolclassid)
                    ->where('sessionid',     $sessionid)
                    ->where('termid',        $termid)
                    ->first();

                if ($existing) {
                    if ($existing->principalscomment !== $comment) {
                        $existing->update([
                            'staffid'           => Auth::id(),
                            'principalscomment' => $comment,
                        ]);
                        $updatedCount++;
                    }
                } else {
                    Studentpersonalityprofile::create([
                        'studentid'         => $studentId,
                        'schoolclassid'     => $schoolclassid,
                        'sessionid'         => $sessionid,
                        'termid'            => $termid,
                        'staffid'           => Auth::id(),
                        'principalscomment' => $comment,
                    ]);
                    $createdCount++;
                }
            }

            DB::commit();

            $totalProcessed = $updatedCount + $createdCount;
            $message = $totalProcessed > 0
                ? "Successfully saved: $updatedCount updated, $createdCount created. Skipped: $skippedCount empty comments."
                : "No changes detected. $skippedCount empty comments skipped.";

            Log::info('Update completed', [
                'updated' => $updatedCount,
                'created' => $createdCount,
                'skipped' => $skippedCount,
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'updated' => $updatedCount,
                'created' => $createdCount,
                'skipped' => $skippedCount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error saving principals comments', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Return [grade, gradeLetter] for a score.
     */
    private function gradeFromScore(float $score, bool $isSenior): array
    {
        if ($isSenior) {
            if ($score >= 75) return ['A1', 'A'];
            if ($score >= 70) return ['B2', 'B'];
            if ($score >= 65) return ['B3', 'B'];
            if ($score >= 60) return ['C4', 'C'];
            if ($score >= 55) return ['C5', 'C'];
            if ($score >= 50) return ['C6', 'C'];
            if ($score >= 45) return ['D7', 'D'];
            if ($score >= 40) return ['E8', 'E'];
            return ['F9', 'F'];
        }

        if ($score >= 70) return ['A', 'A'];
        if ($score >= 60) return ['B', 'B'];
        if ($score >= 50) return ['C', 'C'];
        if ($score >= 40) return ['D', 'D'];
        return ['F', 'F'];
    }

    private function formatList(array $items): string
    {
        $count = count($items);
        if ($count === 0) return '';
        if ($count === 1) return $items[0];
        if ($count === 2) return implode(' and ', $items);
        return implode(', ', array_slice($items, 0, -1)) . ' and ' . end($items);
    }

    private function getPositionSuffix(int $num): string
    {
        if ($num % 100 >= 11 && $num % 100 <= 13) {
            return $num . 'th';
        }
        return match ($num % 10) {
            1       => $num . 'st',
            2       => $num . 'nd',
            3       => $num . 'rd',
            default => $num . 'th',
        };
    }
}
