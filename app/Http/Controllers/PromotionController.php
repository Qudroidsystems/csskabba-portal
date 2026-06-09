<?php
// app/Http/Controllers/PromotionController.php

namespace App\Http\Controllers;

use Exception;
use Illuminate\View\View;
use App\Models\Schoolclass;
use App\Models\Studentclass;
use Illuminate\Http\Request;
use App\Models\Schoolsession;
use App\Models\PromotionStatus;
use App\Models\CompulsorySubjectClass;
use App\Models\Broadsheets;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\PromotionEvaluator;
use Illuminate\Pagination\LengthAwarePaginator;

class PromotionController extends Controller
{
    protected $promotionEvaluator;

    public function __construct(PromotionEvaluator $promotionEvaluator)
    {
        $this->middleware('permission:View promotion',   ['only' => ['index']]);
        $this->middleware('permission:Update promotion', ['only' => ['update', 'destroy']]);
        $this->promotionEvaluator = $promotionEvaluator;
    }

    public function index(Request $request): View|JsonResponse
    {
        $pagetitle   = "Student Promotion Management";
        $allstudents = new LengthAwarePaginator([], 0, 10);

        $hasFilters = $request->filled('schoolclassid')
            && $request->filled('sessionid')
            && $request->input('schoolclassid') !== 'ALL'
            && $request->input('sessionid')     !== 'ALL';

        if ($hasFilters) {
            $schoolclassId = $request->input('schoolclassid');
            $sessionId = $request->input('sessionid');
            $termId = $request->input('termid', 3);

            $query = Studentclass::query()
                ->where('studentclass.schoolclassid', $schoolclassId)
                ->where('studentclass.sessionid', $sessionId)
                ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'studentclass.studentId')
                ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
                ->leftJoin('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid');

            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('studentRegistration.admissionNo', 'like', "%{$search}%")
                      ->orWhere('studentRegistration.firstname',  'like', "%{$search}%")
                      ->orWhere('studentRegistration.lastname',   'like', "%{$search}%")
                      ->orWhere('studentRegistration.othername',  'like', "%{$search}%");
                });
            }

            try {
                $allstudents = $query->select([
                    'studentRegistration.id as stid',
                    'studentRegistration.admissionNo as admissionno',
                    'studentRegistration.firstname as firstname',
                    'studentRegistration.lastname as lastname',
                    'studentRegistration.othername as othername',
                    'studentRegistration.gender as gender',
                    'studentpicture.picture as picture',
                    'studentclass.schoolclassid as schoolclassID',
                    'studentclass.sessionid as sessionid',
                    'studentclass.termid as termid',
                    'schoolclass.schoolclass as schoolclass',
                    'schoolarm.arm as schoolarm',
                    'schoolsession.session as session',
                ])->latest('studentclass.created_at')->paginate(100);

                $allstudents->getCollection()->transform(function ($student) use ($schoolclassId, $sessionId, $termId) {
                    $scores = $this->getStudentScores($student->stid, $schoolclassId, $sessionId, $termId);
                    $overallAverage = $this->calculateOverallAverage($scores);

                    $promotionResult = $this->promotionEvaluator->evaluate(
                        studentId: $student->stid,
                        schoolclassid: $schoolclassId,
                        termid: $termId,
                        sessionid: $sessionId,
                        scores: $scores,
                        overallAverage: $overallAverage
                    );

                    $student->promotion_recommendation = $promotionResult;
                    $student->overall_average = $overallAverage;

                    $existingStatus = PromotionStatus::where('studentId', $student->stid)
                        ->where('schoolclassid', $schoolclassId)
                        ->where('sessionid', $sessionId)
                        ->where('termid', $termId)
                        ->first();

                    if ($existingStatus) {
                        $student->promotion_status = $existingStatus->promotionStatus;
                        $student->promotion_id = $existingStatus->id;
                    } else {
                        $student->promotion_status = null;
                        $student->promotion_id = null;
                    }

                    return $student;
                });

            } catch (Exception $e) {
                Log::error('Promotion query failed', ['request'=>$request->all(),'error'=>$e->getMessage()]);
                $allstudents = new LengthAwarePaginator([], 0, 10);
            }
        }

        $schoolsessions = Schoolsession::get();
        $schoolclasses  = Schoolclass::leftJoin('schoolarm','schoolarm.id','=','schoolclass.arm')
            ->get(['schoolclass.id','schoolclass.schoolclass','schoolarm.arm']);
        $terms = \App\Models\Schoolterm::orderBy('term')->get();

        if ($request->ajax()) {
            return response()->json([
                'tableBody'    => view('promotions.partials.student_rows', compact('allstudents'))->render(),
                'pagination'   => $allstudents->links('pagination::bootstrap-5')->render(),
                'studentCount' => $allstudents->total(),
            ]);
        }

        return view('promotions.index', compact('allstudents', 'schoolsessions', 'schoolclasses', 'terms', 'pagetitle'));
    }

    /**
     * Get complete student details including ALL subjects with pass/fail status against rules
     */
    public function getStudentDetails($studentId, $schoolclassId, $sessionId, $termId)
    {
        try {
            $student = Student::where('studentRegistration.id', $studentId)
                ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
                ->select([
                    'studentRegistration.id as stid',
                    'studentRegistration.admissionNo as admissionno',
                    'studentRegistration.firstname as firstname',
                    'studentRegistration.lastname as lastname',
                    'studentRegistration.othername as othername',
                    'studentRegistration.gender as gender',
                    'studentpicture.picture as picture',
                ])
                ->first();

            if (!$student) {
                return response()->json(['success' => false, 'message' => 'Student not found'], 404);
            }

            // Get ALL scores for this student
            $scores = $this->getStudentScores($studentId, $schoolclassId, $sessionId, $termId);
            $overallAverage = $this->calculateOverallAverage($scores);

            // Get promotion evaluation with rules
            $promotionResult = $this->promotionEvaluator->evaluate(
                studentId: $studentId,
                schoolclassid: $schoolclassId,
                termid: $termId,
                sessionid: $sessionId,
                scores: $scores,
                overallAverage: $overallAverage
            );

            // Get compulsory subjects with their min grades
            $compulsoryQuery = CompulsorySubjectClass::where('schoolclassid', $schoolclassId)
                ->where(function ($q) use ($termId, $sessionId) {
                    $q->where(function ($q2) use ($termId, $sessionId) {
                        $q2->where('termid', $termId)->where('sessionid', $sessionId);
                    })->orWhere(function ($q2) use ($sessionId) {
                        $q2->whereNull('termid')->where('sessionid', $sessionId);
                    })->orWhere(function ($q2) {
                        $q2->whereNull('termid')->whereNull('sessionid');
                    });
                })
                ->with('subject')
                ->get();

            $compulsorySubjectIds = $compulsoryQuery->pluck('subjectId')->toArray();

            // Get the applied rule's subject requirements if any
            $appliedRule = $promotionResult['applied_rule'] ?? null;
            $ruleSubjects = [];
            if ($appliedRule && isset($promotionResult['settings_id'])) {
                $settings = \App\Models\PromotionSetting::find($promotionResult['settings_id']);
                if ($settings && $settings->promotion_rules) {
                    foreach ($settings->promotion_rules as $rule) {
                        if ($rule['rule_name'] === $appliedRule['name']) {
                            foreach ($rule['compulsory_section']['subjects'] ?? [] as $subject) {
                                $ruleSubjects[$subject['subject_id']] = $subject['min_grade'] ?? null;
                            }
                            break;
                        }
                    }
                }
            }

            // Build ALL subjects list with their scores and pass/fail status against the rule
            $allSubjects = [];

            // First, get all subjects the student has scores for
            foreach ($scores as $score) {
                $isCompulsory = in_array($score->subject_id, $compulsorySubjectIds);
                $minGradeFromRule = $ruleSubjects[$score->subject_id] ?? null;
                $minGradeFromCompulsory = $compulsoryQuery->firstWhere('subjectId', $score->subject_id)?->min_grade;
                $requiredMinGrade = $minGradeFromRule ?? $minGradeFromCompulsory ?? null;

                // Determine pass/fail against the promotion rule
                $passStatus = $this->determinePassStatus($score->grade, $requiredMinGrade, $isCompulsory);

                $allSubjects[] = [
                    'subject_id' => $score->subject_id,
                    'subject_name' => $score->subject_name,
                    'subject_code' => $score->subject_code ?? '',
                    'total' => $score->total,
                    'grade' => $score->grade,
                    'is_compulsory' => $isCompulsory,
                    'required_min_grade' => $requiredMinGrade,
                    'pass_status' => $passStatus,
                    'pass_status_label' => $this->getPassStatusLabel($passStatus),
                    'pass_status_class' => $this->getPassStatusClass($passStatus),
                ];
            }

            // Add compulsory subjects that don't have scores (Not Sat)
            foreach ($compulsoryQuery as $compulsory) {
                $existingSubject = collect($allSubjects)->firstWhere('subject_id', $compulsory->subjectId);
                if (!$existingSubject && $compulsory->subject) {
                    $minGradeFromRule = $ruleSubjects[$compulsory->subjectId] ?? null;
                    $requiredMinGrade = $minGradeFromRule ?? $compulsory->min_grade;

                    $allSubjects[] = [
                        'subject_id' => $compulsory->subjectId,
                        'subject_name' => $compulsory->subject->subject ?? 'Unknown',
                        'subject_code' => $compulsory->subject->subject_code ?? '',
                        'total' => null,
                        'grade' => null,
                        'is_compulsory' => true,
                        'required_min_grade' => $requiredMinGrade,
                        'pass_status' => 'not_sat',
                        'pass_status_label' => 'Not Attempted',
                        'pass_status_class' => 'secondary',
                    ];
                }
            }

            // Sort subjects: compulsory first, then alphabetically
            usort($allSubjects, function($a, $b) {
                if ($a['is_compulsory'] != $b['is_compulsory']) {
                    return $b['is_compulsory'] - $a['is_compulsory'];
                }
                return strcmp($a['subject_name'], $b['subject_name']);
            });

            // Build compulsory subjects summary with pass/fail against the rule
            $compulsorySubjectsWithStatus = $compulsoryQuery->map(function ($cs) use ($scores, $ruleSubjects) {
                $scoreEntry = $scores->firstWhere('subject_id', $cs->subjectId);
                $studentGrade = $scoreEntry?->grade ?? null;
                $studentTotal = $scoreEntry?->total ?? null;
                $minGradeFromRule = $ruleSubjects[$cs->subjectId] ?? null;
                $requiredMinGrade = $minGradeFromRule ?? $cs->min_grade;

                $passStatus = $scoreEntry === null ? 'not_sat' : ($this->gradePassFail($studentGrade, $requiredMinGrade) ? 'pass' : 'fail');
                $ruleRequirement = $minGradeFromRule ? "Rule requires: ≥ {$minGradeFromRule}" : ($cs->min_grade ? "Default: ≥ {$cs->min_grade}" : "No requirement");

                return [
                    'csc_id' => $cs->id,
                    'subject_id' => $cs->subjectId,
                    'subject' => $cs->subject?->subject ?? 'N/A',
                    'subject_code' => $cs->subject?->subject_code ?? '',
                    'required_min_grade' => $requiredMinGrade,
                    'rule_requirement' => $ruleRequirement,
                    'student_grade' => $studentGrade,
                    'student_total' => $studentTotal,
                    'pass_status' => $passStatus,
                    'pass_status_label' => $this->getPassStatusLabel($passStatus),
                    'pass_status_class' => $this->getPassStatusClass($passStatus),
                ];
            });

            // Calculate statistics
            $totalSubjects = count($allSubjects);
            $compulsoryCount = $compulsoryQuery->count();
            $passedCompulsory = $compulsorySubjectsWithStatus->where('pass_status', 'pass')->count();
            $failedCompulsory = $compulsorySubjectsWithStatus->where('pass_status', 'fail')->count();
            $notSatCompulsory = $compulsorySubjectsWithStatus->where('pass_status', 'not_sat')->count();

            // Calculate credit counts (C and above)
            $creditGrades = $this->getCreditGrades($schoolclassId);
            $creditCount = 0;
            foreach ($scores as $score) {
                if (in_array($score->grade, $creditGrades)) {
                    $creditCount++;
                }
            }

            return response()->json([
                'success' => true,
                'student' => $student,
                'promotion_result' => $promotionResult,
                'overall_average' => $overallAverage,
                'all_subjects' => $allSubjects,
                'compulsory_subjects' => $compulsorySubjectsWithStatus,
                'statistics' => [
                    'total_subjects' => $totalSubjects,
                    'compulsory_count' => $compulsoryCount,
                    'passed_compulsory' => $passedCompulsory,
                    'failed_compulsory' => $failedCompulsory,
                    'not_sat_compulsory' => $notSatCompulsory,
                    'credit_count' => $creditCount,
                ],
                'scores_count' => $scores->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting student details', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Failed to get student details: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get all student scores with complete subject information
     */
    private function getStudentScores($studentId, $schoolclassId, $sessionId, $termId)
    {
        try {
            $scores = Broadsheets::where('broadsheet_records.student_id', $studentId)
                ->where('broadsheets.term_id', $termId)
                ->where('broadsheet_records.session_id', $sessionId)
                ->where('broadsheet_records.schoolclass_id', $schoolclassId)
                ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
                ->join('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
                ->select([
                    'subject.id as subject_id',
                    'subject.subject as subject_name',
                    'subject.subject_code',
                    'broadsheets.total',
                    'broadsheets.grade',
                ])
                ->get();

            Log::info('Student scores retrieved', [
                'student_id' => $studentId,
                'scores_count' => $scores->count(),
                'subjects' => $scores->pluck('subject_name')->toArray()
            ]);

            return $scores;
        } catch (Exception $e) {
            Log::error('Error getting student scores', [
                'student_id' => $studentId,
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }

    /**
     * Calculate overall average from scores
     */
    private function calculateOverallAverage($scores)
    {
        if ($scores->isEmpty()) {
            return null;
        }

        $totalObtained = 0;
        $totalObtainable = 0;

        foreach ($scores as $score) {
            if ($score->total !== null && is_numeric($score->total)) {
                $totalObtained += (float) $score->total;
            }
            $totalObtainable += 100;
        }

        return $totalObtainable > 0 ? round(($totalObtained / $totalObtainable) * 100, 1) : 0;
    }

    /**
     * Determine pass/fail status for a subject based on grade and requirement.
     *
     * Optional subjects are evaluated against a general passing threshold (F/F9/E8 = fail)
     * but are NOT rule-bound — no min_grade from promotion rules is applied to them.
     *
     * Returns one of: 'pass', 'fail', 'not_sat' (compulsory)
     *              or 'optional_pass', 'optional_fail', 'optional_not_sat' (optional)
     */
    private function determinePassStatus($grade, $requiredMinGrade, $isCompulsory)
    {
        if (!$isCompulsory) {
            if ($grade === null || $grade === '') {
                return 'optional_not_sat';
            }
            $failGrades = ['F', 'F9', 'E8'];
            return in_array(strtoupper(trim($grade)), $failGrades, true)
                ? 'optional_fail'
                : 'optional_pass';
        }

        if (!$grade) return 'not_sat';
        return $this->gradePassFail($grade, $requiredMinGrade) ? 'pass' : 'fail';
    }

    /**
     * Get human-readable pass status label
     */
    private function getPassStatusLabel($status)
    {
        return match($status) {
            'pass'             => 'Passed',
            'fail'             => 'Failed',
            'not_sat'          => 'Not Attempted',
            'optional_pass'    => 'Passed',
            'optional_fail'    => 'Failed',
            'optional_not_sat' => 'Not Attempted',
            'optional'         => 'Optional',
            default            => 'Unknown'
        };
    }

    /**
     * Get CSS class for pass status
     */
    private function getPassStatusClass($status)
    {
        return match($status) {
            'pass', 'optional_pass'       => 'success',
            'fail', 'optional_fail'       => 'danger',
            'not_sat', 'optional_not_sat' => 'warning',
            'optional'                    => 'info',
            default                       => 'secondary'
        };
    }

    /**
     * Check if a grade passes the minimum requirement
     */
    private function gradePassFail(?string $studentGrade, ?string $minGrade): bool
    {
        if ($studentGrade === null) return false;

        $gradeOrder = [
            'F9' => 0, 'E8' => 1, 'D7' => 2,
            'C6' => 3, 'C5' => 4, 'C4' => 5,
            'B3' => 6, 'B2' => 7, 'A1' => 8,
            'F' => 0, 'D' => 2, 'C' => 5,
            'B' => 7, 'A' => 8,
        ];

        $sg = strtoupper(trim($studentGrade));

        if ($minGrade) {
            $mg = strtoupper(trim($minGrade));
            $studentRank = $gradeOrder[$sg] ?? -1;
            $minRank = $gradeOrder[$mg] ?? 0;
            return $studentRank >= $minRank;
        }

        return !in_array($sg, ['F', 'F9'], true);
    }

    /**
     * Get credit grades based on class type (senior/junior)
     */
    private function getCreditGrades($schoolclassId)
    {
        $classCategory = DB::table('schoolclass_classcategory')
            ->join('classcategories', 'classcategories.id', '=', 'schoolclass_classcategory.classcategory_id')
            ->where('schoolclass_classcategory.schoolclass_id', $schoolclassId)
            ->select('classcategories.is_senior')
            ->first();

        $isSenior = $classCategory ? (bool)$classCategory->is_senior : false;

        return $isSenior
            ? ['A1', 'B2', 'B3', 'C4', 'C5', 'C6']
            : ['A', 'B', 'C'];
    }

    /**
     * Update student promotion
     */
    public function update(Request $request, $studentId): JsonResponse
    {
        $request->validate([
            'new_schoolclassid' => 'required|exists:schoolclass,id',
            'new_sessionid' => 'required|exists:schoolsession,id',
            'new_termid' => 'required|integer|min:1|max:3',
            'promotion' => 'boolean',
            'repeat' => 'boolean',
            'trial' => 'boolean',
            'see_principal' => 'boolean',
        ]);

        if ($request->boolean('promotion') && $request->boolean('repeat')) {
            return response()->json(['success' => false, 'message' => 'Cannot select both promotion and repeat.'], 422);
        }

        if ($request->boolean('promotion')) {
            $promotionStatus = 'PROMOTED';
        } elseif ($request->boolean('trial')) {
            $promotionStatus = 'TRIAL';
        } elseif ($request->boolean('see_principal')) {
            $promotionStatus = 'SEE_PRINCIPAL';
        } elseif ($request->boolean('repeat')) {
            $promotionStatus = 'REPEAT';
        } else {
            $promotionStatus = 'PARENTS_TO_SEE_PRINCIPAL';
        }

        try {
            DB::transaction(function () use ($studentId, $request, $promotionStatus) {
                $newClassId = $request->new_schoolclassid;
                $newSessionId = $request->new_sessionid;
                $newTermId = $request->new_termid;

                $existingClass = Studentclass::where('studentId', $studentId)
                    ->where('sessionid', $newSessionId)
                    ->where('termid', $newTermId)
                    ->first();

                if ($existingClass) {
                    $existingClass->update(['schoolclassid' => $newClassId]);
                } else {
                    Studentclass::create([
                        'studentId' => $studentId,
                        'schoolclassid' => $newClassId,
                        'sessionid' => $newSessionId,
                        'termid' => $newTermId,
                    ]);
                }

                PromotionStatus::updateOrCreate(
                    [
                        'studentId' => $studentId,
                        'schoolclassid' => $newClassId,
                        'sessionid' => $newSessionId,
                        'termid' => $newTermId,
                    ],
                    [
                        'promotionStatus' => $promotionStatus,
                        'classstatus' => 'CURRENT',
                        'position' => null,
                    ]
                );

                DB::table('student_current_term')
                    ->where('studentId', $studentId)
                    ->update(['is_current' => false]);

                \App\Models\StudentCurrentTerm::updateOrCreate(
                    [
                        'studentId' => $studentId,
                        'schoolclassId' => $newClassId,
                        'termId' => $newTermId,
                        'sessionId' => $newSessionId,
                    ],
                    ['is_current' => true]
                );
            });

            return response()->json(['success' => true, 'message' => 'Promotion updated successfully.']);

        } catch (Exception $e) {
            Log::error('Promotion update failed', ['studentId' => $studentId, 'request' => $request->all(), 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to update promotion.'], 500);
        }
    }

    /**
     * Remove student from class
     */
    public function destroy(Request $request, $studentId): JsonResponse
    {
        $request->validate([
            'schoolclassid' => 'required|exists:schoolclass,id',
            'sessionid' => 'required|exists:schoolsession,id',
            'termid' => 'required|integer|min:1|max:3',
        ]);

        try {
            DB::transaction(function () use ($studentId, $request) {
                Studentclass::where('studentId', $studentId)
                    ->where('schoolclassid', $request->input('schoolclassid'))
                    ->where('sessionid', $request->input('sessionid'))
                    ->where('termid', $request->input('termid'))
                    ->delete();

                PromotionStatus::where('studentId', $studentId)
                    ->where('schoolclassid', $request->input('schoolclassid'))
                    ->where('sessionid', $request->input('sessionid'))
                    ->where('termid', $request->input('termid'))
                    ->delete();
            });

            return response()->json(['success' => true, 'message' => 'Student removed successfully from class.']);

        } catch (Exception $e) {
            Log::error('Student removal failed', ['studentId' => $studentId, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to remove student.'], 500);
        }
    }

    /**
     * Bulk promote students
     */
    public function bulkPromote(Request $request): JsonResponse
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:studentRegistration,id',
            'new_schoolclassid' => 'required|exists:schoolclass,id',
            'new_sessionid' => 'required|exists:schoolsession,id',
            'new_termid' => 'required|integer|min:1|max:3',
            'promotion_type' => 'required|in:promoted,trial,see_principal,repeat',
        ]);

        try {
            $successCount = 0;
            $failCount = 0;

            foreach ($request->student_ids as $studentId) {
                try {
                    DB::transaction(function () use ($studentId, $request, &$successCount) {
                        $newClassId = $request->new_schoolclassid;
                        $newSessionId = $request->new_sessionid;
                        $newTermId = $request->new_termid;

                        $promotionStatus = match($request->promotion_type) {
                            'promoted' => 'PROMOTED',
                            'trial' => 'TRIAL',
                            'see_principal' => 'SEE_PRINCIPAL',
                            'repeat' => 'REPEAT',
                            default => 'PARENTS_TO_SEE_PRINCIPAL',
                        };

                        $existingClass = Studentclass::where('studentId', $studentId)
                            ->where('sessionid', $newSessionId)
                            ->where('termid', $newTermId)
                            ->first();

                        if ($existingClass) {
                            $existingClass->update(['schoolclassid' => $newClassId]);
                        } else {
                            Studentclass::create([
                                'studentId' => $studentId,
                                'schoolclassid' => $newClassId,
                                'sessionid' => $newSessionId,
                                'termid' => $newTermId,
                            ]);
                        }

                        PromotionStatus::updateOrCreate(
                            [
                                'studentId' => $studentId,
                                'schoolclassid' => $newClassId,
                                'sessionid' => $newSessionId,
                                'termid' => $newTermId,
                            ],
                            [
                                'promotionStatus' => $promotionStatus,
                                'classstatus' => 'CURRENT',
                                'position' => null,
                            ]
                        );

                        $successCount++;
                    });
                } catch (Exception $e) {
                    $failCount++;
                    Log::error('Bulk promotion failed for student', ['studentId' => $studentId, 'error' => $e->getMessage()]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "{$successCount} students promoted successfully. {$failCount} failed.",
                'success_count' => $successCount,
                'fail_count' => $failCount,
            ]);
        } catch (Exception $e) {
            Log::error('Bulk promotion failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to process bulk promotion.'], 500);
        }
    }
}
