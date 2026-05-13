<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Broadsheets;
use App\Models\BroadsheetRecord;
use App\Models\Schoolclass;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Studentclass;
use App\Models\Principalscomment;
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
    // INDEX — list assignments
    // =========================================================================

    public function index()
    {
        $pagetitle = "My Principal's Comment Assignments";

        $assignments = Principalscomment::where('staffId', Auth::id())
            ->join('schoolclass', 'principalscomments.schoolclassid', '=', 'schoolclass.id')
            ->leftJoin('schoolarm',     'schoolarm.id',     '=', 'schoolclass.arm')
            ->leftJoin('schoolsession', 'principalscomments.sessionid', '=', 'schoolsession.id')
            ->leftJoin('schoolterm',    'principalscomments.termid',    '=', 'schoolterm.id')
            ->select([
                'principalscomments.id',
                'schoolclass.id as schoolclassid',
                'schoolclass.schoolclass as sclass',
                'schoolarm.arm as schoolarm',
                'schoolsession.session as session_name',
                'schoolterm.term as term_name',
                'principalscomments.updated_at',
            ])
            ->orderBy('schoolclass.schoolclass')
            ->orderBy('schoolarm.arm')
            ->get();

        $currentSession = Schoolsession::where('status', 'Current')->first()
            ?? Schoolsession::latest()->first();
        $currentTerm = Schoolterm::latest()->first();

        return view('myprincipalscomment.index')
            ->with(compact('assignments', 'pagetitle', 'currentSession', 'currentTerm'));
    }

    // =========================================================================
    // CLASS BROADSHEET — main view
    // =========================================================================

    public function classBroadsheet($schoolclassid, $sessionid, $termid)
    {
        $pagetitle = "Principal's Comment & Class Broadsheet";

        // ------------------------------------------------------------------
        // 1. Students enrolled in this class / session
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
        // 2. School meta
        // ------------------------------------------------------------------
        $schoolclass           = Schoolclass::with(['arm', 'classcategories'])->findOrFail($schoolclassid);
        $schoolclass->arm_name = $schoolclass->arm?->arm ?? '';

        $schoolterm    = Schoolterm::find($termid)?->term         ?? 'N/A';
        $schoolsession = Schoolsession::find($sessionid)?->session ?? 'N/A';

        $isSenior = $schoolclass->classcategories->isNotEmpty()
            ? ($schoolclass->classcategories->first()->is_senior ?? false)
            : false;

        // ------------------------------------------------------------------
        // 3. Pull broadsheet rows — same pattern as MyScoreSheetController
        //    We fetch ALL subject rows for this class/session/term at once
        //    using the proper broadsheet_records join.
        // ------------------------------------------------------------------
        $broadsheetRows = Broadsheets::query()
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
            ->join('subjectclass', function ($join) {
                $join->on('subjectclass.id', '=', 'broadsheets.subjectclass_id')
                     ->on('broadsheet_records.subject_id',    '=', 'subjectclass.subjectid')
                     ->on('broadsheet_records.schoolclass_id','=', 'subjectclass.schoolclassid');
            })
            ->leftJoin('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->where('broadsheet_records.schoolclass_id', $schoolclassid)
            ->where('broadsheet_records.session_id',     $sessionid)
            ->where('broadsheets.term_id',               $termid)
            ->orderBy('subject.subject')
            ->get([
                'broadsheets.id',
                'broadsheet_records.student_id',
                'broadsheet_records.subject_id',
                'subject.subject as subject_name',
                'broadsheets.total',   // term score (raw, graded on this)
                'broadsheets.bf',
                'broadsheets.cum',     // cumulative score
                'broadsheets.grade',
                'broadsheets.remark',
                'broadsheets.term_id',
            ]);

        // ------------------------------------------------------------------
        // 4. Derive distinct subject list (ordered)
        // ------------------------------------------------------------------
        $subjects = $broadsheetRows
            ->pluck('subject_name')
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        // ------------------------------------------------------------------
        // 5. Build per-student lookup maps
        //    termScores  → keyed by [student_id][subject_name]  value = total
        //    cumulScores → keyed by [student_id][subject_name]  value = cum
        // ------------------------------------------------------------------
        $termScoreMap  = [];   // student_id => subject_name => term total
        $cumScoreMap   = [];   // student_id => subject_name => cumulative

        foreach ($broadsheetRows as $row) {
            $sid  = $row->student_id;
            $subj = $row->subject_name;

            $termScoreMap[$sid][$subj] = $row->total ?? 0;
            $cumScoreMap[$sid][$subj]  = $row->cum   ?? 0;
        }

        // ------------------------------------------------------------------
        // 6. Existing principal comments (keyed by student id)
        // ------------------------------------------------------------------
        $profiles = Studentpersonalityprofile::where('schoolclassid', $schoolclassid)
            ->where('termid',    $termid)
            ->where('sessionid', $sessionid)
            ->pluck('principalscomment', 'studentid')
            ->toArray();

        // ------------------------------------------------------------------
        // 7. Grade analysis per student (uses cumulative score)
        // ------------------------------------------------------------------
        $studentGrades        = [];   // student_id => [ ['subject','score','term_score','grade','grade_letter'], … ]
        $studentGradeAnalysis = [];   // student_id => ['grades','counts','weak_subjects']

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

                if ($isSenior) {
                    if ($cumTotal >= 75)      { $grade = 'A1'; $gradeLetter = 'A'; }
                    elseif ($cumTotal >= 70)  { $grade = 'B2'; $gradeLetter = 'B'; }
                    elseif ($cumTotal >= 65)  { $grade = 'B3'; $gradeLetter = 'B'; }
                    elseif ($cumTotal >= 60)  { $grade = 'C4'; $gradeLetter = 'C'; }
                    elseif ($cumTotal >= 55)  { $grade = 'C5'; $gradeLetter = 'C'; }
                    elseif ($cumTotal >= 50)  { $grade = 'C6'; $gradeLetter = 'C'; }
                    elseif ($cumTotal >= 45)  { $grade = 'D7'; $gradeLetter = 'D'; }
                    elseif ($cumTotal >= 40)  { $grade = 'E8'; $gradeLetter = 'E'; }
                    else                      { $grade = 'F9'; $gradeLetter = 'F'; }
                } else {
                    if ($cumTotal >= 70)      { $grade = 'A'; $gradeLetter = 'A'; }
                    elseif ($cumTotal >= 60)  { $grade = 'B'; $gradeLetter = 'B'; }
                    elseif ($cumTotal >= 50)  { $grade = 'C'; $gradeLetter = 'C'; }
                    elseif ($cumTotal >= 40)  { $grade = 'D'; $gradeLetter = 'D'; }
                    else                      { $grade = 'F'; $gradeLetter = 'F'; }
                }

                $entry = [
                    'subject'      => $subject,
                    'score'        => $cumTotal,
                    'term_score'   => $termTotal,
                    'grade'        => $grade,
                    'grade_letter' => $gradeLetter,
                ];

                $studentGrades[$sid][]                         = $entry;
                $studentGradeAnalysis[$sid]['grades'][]        = $entry;
                $studentGradeAnalysis[$sid]['counts'][$gradeLetter]++;

                if (in_array($gradeLetter, ['C', 'D', 'E', 'F'])) {
                    $studentGradeAnalysis[$sid]['weak_subjects'][] = [
                        'subject'          => $subject,
                        'grade'            => $grade,
                        'grade_letter'     => $gradeLetter,
                        'cumulative_score' => $cumTotal,
                        'term_score'       => $termTotal,
                    ];
                }
            }
        }

        // ------------------------------------------------------------------
        // 8. Standard personalised comments (second-person, direct to student)
        // ------------------------------------------------------------------
        $standardPersonalizedComments = [];

        $baseTemplates = [
            "Excellent result {NAME}, keep it up!",
            "A very good result {NAME}, keep it up!",
            "Good result {NAME}, keep it up!",
            "Average result {NAME}, there's still room for improvement next term.",
            "{NAME}, you can do better next term.",
            "{NAME}, you need to sit up and be serious.",
            "{NAME}, wake up and be serious.",
        ];

        foreach ($students as $student) {
            $sid       = $student->id;
            $firstName = $student->fname;

            $weakSubjects = $studentGradeAnalysis[$sid]['weak_subjects'] ?? [];
            $advice       = '';

            if (!empty($weakSubjects)) {
                usort($weakSubjects, function ($a, $b) {
                    $order = ['F' => 0, 'E' => 1, 'D' => 2, 'C' => 3];
                    return $order[$a['grade_letter']] <=> $order[$b['grade_letter']];
                });

                $subjectList  = array_map(
                    fn($ws) => strtoupper($ws['subject']) . ' (' . $ws['grade'] . ')',
                    $weakSubjects
                );
                $subjectsText = $this->formatList($subjectList);
                $advice       = "\n\nYou should work harder in $subjectsText to improve your performance.";
            }

            $options = [];
            foreach ($baseTemplates as $template) {
                $options[] = str_replace('{NAME}', $firstName, $template) . $advice;
            }

            $standardPersonalizedComments[$sid] = $options;
        }

        // ------------------------------------------------------------------
        // 9. Intelligent (AI-style) comments — third-person, gender-aware
        // ------------------------------------------------------------------
        $intelligentComments = [];

        foreach ($students as $student) {
            $sid       = $student->id;
            $firstName = $student->fname;
            $analysis  = $studentGradeAnalysis[$sid] ?? ['counts' => [], 'weak_subjects' => []];

            // Grade summary string
            $gradeParts = [];
            foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $g) {
                $count = $analysis['counts'][$g] ?? 0;
                if ($count > 0) {
                    $gradeParts[] = "$count {$g}" . ($count > 1 ? "'s" : '');
                }
            }
            $gradeSummary = !empty($gradeParts)
                ? $this->formatList($gradeParts)
                : 'no grades recorded';

            // Percentage of A/B grades
            $totalGrades    = array_sum($analysis['counts']);
            $goodGrades     = ($analysis['counts']['A'] ?? 0) + ($analysis['counts']['B'] ?? 0);
            $percentageGood = $totalGrades > 0 ? ($goodGrades / $totalGrades) * 100 : 0;

            if ($percentageGood >= 80)      $baseComment = "Excellent result {NAME}, keep it up!";
            elseif ($percentageGood >= 70)  $baseComment = "A very good result {NAME}, keep it up!";
            elseif ($percentageGood >= 60)  $baseComment = "Good result {NAME}, keep it up!";
            elseif ($percentageGood >= 50)  $baseComment = "Average result {NAME}, there's still room for improvement next term.";
            elseif ($percentageGood >= 40)  $baseComment = "{NAME}, you can do better next term.";
            elseif ($percentageGood >= 30)  $baseComment = "{NAME}, you need to sit up and be serious.";
            else                            $baseComment = "{NAME}, wake up and be serious.";

            $termInfo = '';
            if (in_array($schoolterm, ['2nd Term', 'Second Term'])) {
                $termInfo = ' (Cumulative average of 1st and 2nd terms)';
            } elseif (in_array($schoolterm, ['3rd Term', 'Third Term'])) {
                $termInfo = ' (Cumulative average of 1st, 2nd and 3rd terms)';
            }

            $comment    = "$firstName has $gradeSummary$termInfo. "
                        . str_replace('{NAME}', $firstName, $baseComment);
            $pronoun    = strtoupper($student->gender) === 'MALE' ? 'He'  : 'She';
            $possessive = strtoupper($student->gender) === 'MALE' ? 'his' : 'her';

            $weakSubjects = $analysis['weak_subjects'] ?? [];
            if (!empty($weakSubjects)) {
                usort($weakSubjects, function ($a, $b) {
                    $order = ['F' => 0, 'E' => 1, 'D' => 2, 'C' => 3];
                    return $order[$a['grade_letter']] <=> $order[$b['grade_letter']];
                });
                $subjectList  = array_map(
                    fn($ws) => $ws['subject'] . ' (' . $ws['grade'] . ')',
                    $weakSubjects
                );
                $subjectsText = $this->formatList($subjectList);
                $comment     .= "\n\n$pronoun should work harder in $subjectsText to improve $possessive performance.";
            }

            $intelligentComments[$sid] = $comment;
        }

        // ------------------------------------------------------------------
        // 10. Student analytics (totals, averages, positions)
        // ------------------------------------------------------------------
        $studentTotals     = [];   // sid => [total, average, subjects]
        $studentTermTotals = [];   // sid => [total, average]

        foreach ($students as $student) {
            $sid       = $student->id;
            $cumSum    = 0;
            $termSum   = 0;
            $count     = 0;

            foreach ($subjects as $subject) {
                $cum  = $cumScoreMap[$sid][$subject]  ?? null;
                $term = $termScoreMap[$sid][$subject] ?? null;

                if (!is_null($cum)) {
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

        // Class-level cumulative average
        $classCumSum      = array_sum(array_column($studentTotals, 'total'));
        $classCumSubjects = array_sum(array_column($studentTotals, 'subjects'));
        $classAverage     = $classCumSubjects > 0
            ? round($classCumSum / $classCumSubjects, 1)
            : 0;

        $classAnalytics = [
            'average'        => $classAverage,
            'total_students' => $students->count(),
        ];

        // Positions based on cumulative averages
        $sortedStudents = $students
            ->sortByDesc(fn($s) => $studentTotals[$s->id]['average'] ?? 0)
            ->values();

        $positions = [];
        $rank      = 1;
        $prevAvg   = null;

        foreach ($sortedStudents as $index => $student) {
            $avg = $studentTotals[$student->id]['average'];
            if ($index > 0 && $avg < $prevAvg) {
                $rank = $index + 1;
            }
            $positions[$student->id] = $rank;
            $prevAvg = $avg;
        }

        $studentAnalytics = [];
        foreach ($students as $student) {
            $sid      = $student->id;
            $totals   = $studentTotals[$sid];
            $termTot  = $studentTermTotals[$sid];
            $position = $positions[$sid] ?? null;

            $studentAnalytics[$sid] = [
                'total_score'   => $totals['total'],
                'average'       => $totals['average'],
                'term_total'    => $termTot['total'],
                'term_average'  => $termTot['average'],
                'subjects'      => $totals['subjects'],
                'position'      => $position,
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
                'isSenior'
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
     * Format an array of strings into a natural-language list.
     * e.g. ['A','B','C'] → "A, B and C"
     */
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
