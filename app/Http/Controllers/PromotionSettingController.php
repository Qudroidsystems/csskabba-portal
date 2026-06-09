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
use App\Models\PromotionSetting;
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

    // =========================================================================
    // INDEX
    // =========================================================================

    public function index(Request $request): View|JsonResponse
    {
        $pagetitle   = "Student Promotion Management";
        $allstudents = new LengthAwarePaginator([], 0, 10);

        $hasFilters = $request->filled('schoolclassid')
            && $request->filled('sessionid')
            && $request->input('schoolclassid') !== 'ALL'
            && $request->input('sessionid')     !== 'ALL';

        if ($hasFilters) {
            $schoolclassId = (int) $request->input('schoolclassid');
            $sessionId     = (int) $request->input('sessionid');
            // ── FIX: cast termId to int so it matches DB type in comparisons ──
            $termId        = (int) $request->input('termid', 3);

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
                    $scores         = $this->getStudentScores($student->stid, $schoolclassId, $sessionId, $termId);
                    $overallAverage = $this->calculateOverallAverage($scores);

                    $promotionResult = $this->promotionEvaluator->evaluate(
                        studentId:     (int) $student->stid,
                        schoolclassid: (int) $schoolclassId,
                        termid:        (int) $termId,
                        sessionid:     (int) $sessionId,
                        scores:        $scores,
                        overallAverage: $overallAverage
                    );

                    $student->promotion_recommendation = $promotionResult;
                    $student->overall_average          = $overallAverage;

                    $existingStatus = PromotionStatus::where('studentId',    $student->stid)
                        ->where('schoolclassid', $schoolclassId)
                        ->where('sessionid',     $sessionId)
                        ->where('termid',        $termId)
                        ->first();

                    $student->promotion_status = $existingStatus?->promotionStatus ?? null;
                    $student->promotion_id     = $existingStatus?->id              ?? null;

                    return $student;
                });

            } catch (Exception $e) {
                Log::error('Promotion query failed', ['request' => $request->all(), 'error' => $e->getMessage()]);
                $allstudents = new LengthAwarePaginator([], 0, 10);
            }
        }

        $schoolsessions = Schoolsession::get();
        $schoolclasses  = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm']);
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

    // =========================================================================
    // SCORE FETCHING
    // =========================================================================

    /**
     * FIX: The original query joined subject via broadsheet_records.subject_id,
     * but broadsheet_records stores subject_id only at the record level (not per
     * broadsheet row). The correct path is:
     *   broadsheets → broadsheet_records → subjectteacher → subject
     *
     * This ensures subject_id matches the same ID space used by
     * CompulsorySubjectClass.subjectId and PromotionEvaluator::getCompulsoryIds().
     *
     * Also: we now cast all IDs to int to prevent type-mismatch in strict
     * comparisons inside PromotionEvaluator.
     */
    private function getStudentScores($studentId, $schoolclassId, $sessionId, $termId)
    {
        try {
            $scores = DB::table('broadsheets')
                ->join('broadsheet_records',
                    'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
                ->join('subjectteacher',
                    'subjectteacher.id', '=', 'broadsheet_records.subjectteacher_id')
                ->join('subject',
                    'subject.id', '=', 'subjectteacher.subject_id')
                ->where('broadsheet_records.student_id',    (int) $studentId)
                ->where('broadsheet_records.schoolclass_id', (int) $schoolclassId)
                ->where('broadsheet_records.session_id',    (int) $sessionId)
                ->where('broadsheets.term_id',              (int) $termId)
                ->select([
                    DB::raw('CAST(subject.id AS UNSIGNED) as subject_id'),
                    'subject.subject as subject_name',
                    'subject.subject_code',
                    'broadsheets.total',
                    'broadsheets.grade',
                ])
                ->get();

            return $scores;

        } catch (Exception $e) {
            Log::error('Error getting student scores', [
                'student_id'     => $studentId,
                'schoolclass_id' => $schoolclassId,
                'session_id'     => $sessionId,
                'term_id'        => $termId,
                'error'          => $e->getMessage(),
                'trace'          => $e->getTraceAsString(),
            ]);
            return collect();
        }
    }

    private function calculateOverallAverage($scores)
    {
        if ($scores->isEmpty()) {
            return null;
        }

        $totalObtained   = 0;
        $totalObtainable = 0;

        foreach ($scores as $score) {
            if ($score->total !== null && is_numeric($score->total)) {
                $totalObtained += (float) $score->total;
            }
            $totalObtainable += 100;
        }

        return $totalObtainable > 0 ? round(($totalObtained / $totalObtainable) * 100, 1) : 0;
    }

    // =========================================================================
    // STUDENT DETAILS (modal)
    // =========================================================================

    public function getStudentDetails($studentId, $schoolclassId, $sessionId, $termId): JsonResponse
    {
        try {
            // Cast all IDs to int up front — prevents type-mismatch throughout
            $studentId    = (int) $studentId;
            $schoolclassId = (int) $schoolclassId;
            $sessionId    = (int) $sessionId;
            $termId       = (int) $termId;

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

            $scores         = $this->getStudentScores($studentId, $schoolclassId, $sessionId, $termId);
            $overallAverage = $this->calculateOverallAverage($scores);

            $promotionResult = $this->promotionEvaluator->evaluate(
                studentId:     $studentId,
                schoolclassid: $schoolclassId,
                termid:        $termId,
                sessionid:     $sessionId,
                scores:        $scores,
                overallAverage: $overallAverage
            );

            // applied_rule is already set inside evaluate(); we override only if
            // the controller-level lookup provides richer detail.
            if (empty($promotionResult['applied_rule'])) {
                $settings = PromotionSetting::where('schoolclass_id', $schoolclassId)
                    ->where('is_active', true)
                    ->where(function ($q) use ($sessionId, $termId) {
                        $q->where(function ($q2) use ($sessionId, $termId) {
                            $q2->where('session_id', $sessionId)->where('term_id', $termId);
                        })->orWhere(function ($q2) use ($sessionId) {
                            $q2->where('session_id', $sessionId)->whereNull('term_id');
                        })->orWhere(function ($q2) {
                            $q2->whereNull('session_id')->whereNull('term_id');
                        });
                    })
                    ->orderBy('priority')
                    ->first();

                if ($settings) {
                    $promotionResult['applied_rule'] = $this->getAppliedRule($settings, $promotionResult);
                    $promotionResult['settings_id']  = $settings->id;
                }
            }

            $scoreMap = $scores->keyBy('subject_id');

            // Compulsory subjects with pass/fail status
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

            $compulsorySubjectsWithStatus = $compulsoryQuery->map(function ($cs) use ($scoreMap) {
                $entry        = $scoreMap->get($cs->subjectId);
                $studentGrade = $entry?->grade ?? null;
                $studentTotal = $entry?->total ?? null;
                $minGrade     = $cs->min_grade;
                $passStatus   = $entry === null
                    ? 'not_sat'
                    : ($this->gradePassFail($studentGrade, $minGrade) ? 'pass' : 'fail');

                return [
                    'csc_id'        => $cs->id,
                    'subject_id'    => $cs->subjectId,
                    'subject'       => $cs->subject?->subject ?? 'N/A',
                    'subject_code'  => $cs->subject?->subject_code ?? '',
                    'min_grade'     => $minGrade ?? '—',
                    'student_grade' => $studentGrade,
                    'student_total' => $studentTotal,
                    'pass_status'   => $passStatus,
                ];
            });

            // All subjects with scores
            $allSubjectsWithScores = $scores->map(function ($score) use ($compulsoryQuery) {
                $csMatch    = $compulsoryQuery->firstWhere('subjectId', $score->subject_id);
                $isComp     = $csMatch !== null;
                return [
                    'subject_id'   => $score->subject_id,
                    'subject_name' => $score->subject_name,
                    'subject_code' => $score->subject_code,
                    'grade'        => $score->grade,
                    'total'        => $score->total,
                    'is_compulsory'=> $isComp,
                    'min_grade'    => $csMatch?->min_grade ?? null,
                ];
            });

            return response()->json([
                'success'             => true,
                'student'             => $student,
                'promotion_result'    => $promotionResult,
                'overall_average'     => $overallAverage,
                'compulsory_subjects' => $compulsorySubjectsWithStatus,
                'all_subjects'        => $allSubjectsWithScores,
                'scores_count'        => $scores->count(),
            ]);

        } catch (Exception $e) {
            Log::error('Error getting student details', [
                'student_id' => $studentId,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to get student details'], 500);
        }
    }

    // =========================================================================
    // DEBUG ENDPOINT — REMOVE AFTER DIAGNOSIS
    // Add to routes/web.php:
    //   Route::get('/debug-promotion/{studentId}', [PromotionController::class, 'debugPromotion']);
    // Usage: /debug-promotion/123?classId=5&sessionId=2&termId=2
    // =========================================================================

    public function debugPromotion($studentId): JsonResponse
    {
        $classId   = (int) request('classId',   1);
        $sessionId = (int) request('sessionId', 1);
        $termId    = (int) request('termId',    2);

        $scores = $this->getStudentScores($studentId, $classId, $sessionId, $termId);

        // What does findBestSettings see?
        $settings = PromotionSetting::where('schoolclass_id', $classId)
            ->where('is_active', true)
            ->get(['id', 'session_id', 'term_id', 'rule_logic', 'promotion_rules', 'priority', 'is_active']);

        // What do getCompulsoryIds return?
        $compulsoryIds = CompulsorySubjectClass::where('schoolclassid', $classId)
            ->where(function ($q) use ($termId, $sessionId) {
                $q->where(function ($q2) use ($termId, $sessionId) {
                    $q2->where('termid', $termId)->where('sessionid', $sessionId);
                })->orWhere(function ($q2) use ($sessionId) {
                    $q2->whereNull('termid')->where('sessionid', $sessionId);
                })->orWhere(function ($q2) {
                    $q2->whereNull('termid')->whereNull('sessionid');
                });
            })
            ->pluck('subjectId')
            ->toArray();

        $avg    = $this->calculateOverallAverage($scores);
        $result = $this->promotionEvaluator->evaluate(
            studentId:     (int) $studentId,
            schoolclassid: $classId,
            termid:        $termId,
            sessionid:     $sessionId,
            scores:        $scores,
            overallAverage: $avg
        );

        return response()->json([
            'inputs' => [
                'studentId'    => $studentId,
                'classId'      => $classId,
                'sessionId'    => $sessionId,
                'termId'       => $termId,
            ],
            'scores_count'     => $scores->count(),
            'scores_sample'    => $scores->take(3)->toArray(),
            'score_subject_ids'=> $scores->pluck('subject_id')->toArray(),
            'compulsory_ids'   => $compulsoryIds,
            'id_overlap'       => array_intersect(
                array_map('strval', $scores->pluck('subject_id')->toArray()),
                array_map('strval', $compulsoryIds)
            ),
            'overall_average'  => $avg,
            'active_settings'  => $settings->map(fn($s) => [
                'id'         => $s->id,
                'session_id' => $s->session_id,
                'term_id'    => $s->term_id,
                'rule_logic' => $s->rule_logic,
                'rule_count' => count($s->promotion_rules ?? []),
                'priority'   => $s->priority,
                // Specificity score the evaluator would assign:
                'specificity'=> ($s->session_id == $sessionId && $s->term_id == $termId) ? 3
                              : ($s->session_id == $sessionId ? 2 : 1),
            ]),
            'evaluation_result'=> $result,
        ]);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * FIX: The original matched rules by status_label, which is ambiguous when
     * multiple rules share the same outcome. The evaluator already returns the
     * matched rule index in applied_rule — only fall back to this if needed.
     */
    private function getAppliedRule($settings, $promotionResult): ?array
    {
        $rules = $settings->promotion_rules ?? [];
        if (empty($rules)) return null;

        $matchedStatus = $promotionResult['status'] ?? null;
        if (!$matchedStatus) return null;

        foreach ($rules as $index => $rule) {
            if (($rule['status_label'] ?? '') === $matchedStatus) {
                return [
                    'index'       => $index + 1,
                    'name'        => $rule['rule_name'] ?? "Rule " . ($index + 1),
                    'id'          => $settings->id,
                    'description' => $rule['description'] ?? null,
                ];
            }
        }

        return null;
    }

    private function gradePassFail(?string $studentGrade, ?string $minGrade): bool
    {
        if ($studentGrade === null) return false;

        $gradeOrder = [
            'F9' => 0, 'E8' => 1, 'D7' => 2,
            'C6' => 3, 'C5' => 4, 'C4' => 5,
            'B3' => 6, 'B2' => 7, 'A1' => 8,
            'F'  => 0, 'D'  => 2, 'C'  => 5,
            'B'  => 7, 'A'  => 8,
        ];

        $sg = strtoupper(trim($studentGrade));
        if ($minGrade) {
            $mg = strtoupper(trim($minGrade));
            return ($gradeOrder[$sg] ?? -1) >= ($gradeOrder[$mg] ?? 0);
        }

        return !in_array($sg, ['F', 'F9'], true);
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    public function update(Request $request, $studentId): JsonResponse
    {
        $request->validate([
            'new_schoolclassid' => 'required|exists:schoolclass,id',
            'new_sessionid'     => 'required|exists:schoolsession,id',
            'new_termid'        => 'required|integer|min:1|max:3',
            'promotion'         => 'boolean',
            'repeat'            => 'boolean',
            'trial'             => 'boolean',
            'see_principal'     => 'boolean',
        ]);

        if ($request->boolean('promotion') && $request->boolean('repeat')) {
            return response()->json(['success' => false, 'message' => 'Cannot select both promotion and repeat.'], 422);
        }

        $promotionStatus = match (true) {
            $request->boolean('promotion')     => 'PROMOTED',
            $request->boolean('trial')         => 'TRIAL',
            $request->boolean('see_principal') => 'SEE_PRINCIPAL',
            $request->boolean('repeat')        => 'REPEAT',
            default                            => 'PARENTS_TO_SEE_PRINCIPAL',
        };

        try {
            DB::transaction(function () use ($studentId, $request, $promotionStatus) {
                $newClassId   = $request->new_schoolclassid;
                $newSessionId = $request->new_sessionid;
                $newTermId    = $request->new_termid;

                $existingClass = Studentclass::where('studentId', $studentId)
                    ->where('sessionid', $newSessionId)
                    ->where('termid',    $newTermId)
                    ->first();

                if ($existingClass) {
                    $existingClass->update(['schoolclassid' => $newClassId]);
                } else {
                    Studentclass::create([
                        'studentId'    => $studentId,
                        'schoolclassid'=> $newClassId,
                        'sessionid'    => $newSessionId,
                        'termid'       => $newTermId,
                    ]);
                }

                PromotionStatus::updateOrCreate(
                    [
                        'studentId'    => $studentId,
                        'schoolclassid'=> $newClassId,
                        'sessionid'    => $newSessionId,
                        'termid'       => $newTermId,
                    ],
                    [
                        'promotionStatus' => $promotionStatus,
                        'classstatus'     => 'CURRENT',
                        'position'        => null,
                    ]
                );

                DB::table('student_current_term')
                    ->where('studentId', $studentId)
                    ->update(['is_current' => false]);

                \App\Models\StudentCurrentTerm::updateOrCreate(
                    [
                        'studentId'    => $studentId,
                        'schoolclassId'=> $newClassId,
                        'termId'       => $newTermId,
                        'sessionId'    => $newSessionId,
                    ],
                    ['is_current' => true]
                );
            });

            return response()->json(['success' => true, 'message' => 'Promotion updated successfully.']);

        } catch (Exception $e) {
            Log::error('Promotion update failed', [
                'studentId' => $studentId,
                'request'   => $request->all(),
                'error'     => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to update promotion.'], 500);
        }
    }

    // =========================================================================
    // DESTROY
    // =========================================================================

    public function destroy(Request $request, $studentId): JsonResponse
    {
        $request->validate([
            'schoolclassid' => 'required|exists:schoolclass,id',
            'sessionid'     => 'required|exists:schoolsession,id',
            'termid'        => 'required|integer|min:1|max:3',
        ]);

        try {
            DB::transaction(function () use ($studentId, $request) {
                Studentclass::where('studentId',    $studentId)
                    ->where('schoolclassid', $request->input('schoolclassid'))
                    ->where('sessionid',     $request->input('sessionid'))
                    ->where('termid',        $request->input('termid'))
                    ->delete();

                PromotionStatus::where('studentId',    $studentId)
                    ->where('schoolclassid', $request->input('schoolclassid'))
                    ->where('sessionid',     $request->input('sessionid'))
                    ->where('termid',        $request->input('termid'))
                    ->delete();
            });

            return response()->json(['success' => true, 'message' => 'Student removed successfully from class.']);

        } catch (Exception $e) {
            Log::error('Student removal failed', ['studentId' => $studentId, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to remove student.'], 500);
        }
    }

    // =========================================================================
    // BULK PROMOTE
    // =========================================================================

    public function bulkPromote(Request $request): JsonResponse
    {
        $request->validate([
            'student_ids'        => 'required|array',
            'student_ids.*'      => 'exists:studentRegistration,id',
            'new_schoolclassid'  => 'required|exists:schoolclass,id',
            'new_sessionid'      => 'required|exists:schoolsession,id',
            'new_termid'         => 'required|integer|min:1|max:3',
            'promotion_type'     => 'required|in:promoted,trial,see_principal,repeat',
        ]);

        $successCount = 0;
        $failCount    = 0;

        foreach ($request->student_ids as $studentId) {
            try {
                DB::transaction(function () use ($studentId, $request) {
                    $newClassId   = $request->new_schoolclassid;
                    $newSessionId = $request->new_sessionid;
                    $newTermId    = $request->new_termid;

                    $promotionStatus = match ($request->promotion_type) {
                        'promoted'      => 'PROMOTED',
                        'trial'         => 'TRIAL',
                        'see_principal' => 'SEE_PRINCIPAL',
                        'repeat'        => 'REPEAT',
                        default         => 'PARENTS_TO_SEE_PRINCIPAL',
                    };

                    $existingClass = Studentclass::where('studentId', $studentId)
                        ->where('sessionid', $newSessionId)
                        ->where('termid',    $newTermId)
                        ->first();

                    if ($existingClass) {
                        $existingClass->update(['schoolclassid' => $newClassId]);
                    } else {
                        Studentclass::create([
                            'studentId'    => $studentId,
                            'schoolclassid'=> $newClassId,
                            'sessionid'    => $newSessionId,
                            'termid'       => $newTermId,
                        ]);
                    }

                    PromotionStatus::updateOrCreate(
                        [
                            'studentId'    => $studentId,
                            'schoolclassid'=> $newClassId,
                            'sessionid'    => $newSessionId,
                            'termid'       => $newTermId,
                        ],
                        [
                            'promotionStatus' => $promotionStatus,
                            'classstatus'     => 'CURRENT',
                            'position'        => null,
                        ]
                    );
                });
                $successCount++;
            } catch (Exception $e) {
                $failCount++;
                Log::error('Bulk promotion failed for student', [
                    'studentId' => $studentId,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'success'       => true,
            'message'       => "{$successCount} students promoted successfully. {$failCount} failed.",
            'success_count' => $successCount,
            'fail_count'    => $failCount,
        ]);
    }
}
Done
