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
            $termId = $request->input('termid', 3); // Default to third term (promotional term)

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

                // Calculate promotion recommendations for each student
                $allstudents->getCollection()->transform(function ($student) use ($schoolclassId, $sessionId, $termId) {
                    // Get student's scores for evaluation
                    $scores = $this->getStudentScores($student->stid, $schoolclassId, $sessionId, $termId);

                    // Calculate overall average
                    $overallAverage = $this->calculateOverallAverage($scores);

                    // Evaluate promotion
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

                    // Get existing promotion status if any
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
     * Get student scores for a specific term
     */
    private function getStudentScores($studentId, $schoolclassId, $sessionId, $termId)
    {
        try {
            $scores = Broadsheets::where('broadsheet_records.student_id', $studentId)
                ->where('broadsheets.term_id', $termId)
                ->where('broadsheet_records.session_id', $sessionId)
                ->where('broadsheet_records.schoolclass_id', $schoolclassId)
                ->whereExists(function ($query) use ($studentId, $termId, $sessionId, $schoolclassId) {
                    $query->select(DB::raw(1))
                        ->from('subjectRegistrationStatus')
                        ->join('subjectclass as sjc_reg', 'sjc_reg.id', '=', 'subjectRegistrationStatus.subjectclassid')
                        ->join('subjectteacher as st_reg', 'st_reg.id', '=', 'sjc_reg.subjectteacherid')
                        ->whereColumn('st_reg.subjectid', 'broadsheet_records.subject_id')
                        ->where('subjectRegistrationStatus.studentid', $studentId)
                        ->where('subjectRegistrationStatus.termid', $termId)
                        ->where('subjectRegistrationStatus.sessionid', $sessionId)
                        ->where('sjc_reg.schoolclassid', $schoolclassId);
                })
                ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
                ->join('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
                ->select([
                    'subject.id as subject_id',
                    'subject.subject as subject_name',
                    'broadsheets.total',
                    'broadsheets.grade',
                ])
                ->get();

            return $scores;
        } catch (Exception $e) {
            Log::error('Error getting student scores', ['student_id' => $studentId, 'error' => $e->getMessage()]);
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
     * Get student details for modal
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

            // Get scores for evaluation
            $scores = $this->getStudentScores($studentId, $schoolclassId, $sessionId, $termId);
            $overallAverage = $this->calculateOverallAverage($scores);

            // Evaluate promotion
            $promotionResult = $this->promotionEvaluator->evaluate(
                studentId: $studentId,
                schoolclassid: $schoolclassId,
                termid: $termId,
                sessionid: $sessionId,
                scores: $scores,
                overallAverage: $overallAverage
            );

            // Get compulsory subjects for this class
            $compulsorySubjects = CompulsorySubjectClass::where('schoolclassid', $schoolclassId)
                ->with('subject')
                ->get();

            return response()->json([
                'success' => true,
                'student' => $student,
                'promotion_result' => $promotionResult,
                'overall_average' => $overallAverage,
                'compulsory_subjects' => $compulsorySubjects,
                'scores_count' => $scores->count(),
            ]);
        } catch (Exception $e) {
            Log::error('Error getting student details', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to get student details'], 500);
        }
    }

    /**
     * Promote or repeat a student.
     */
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
            return response()->json(['success'=>false,'message'=>'Cannot select both promotion and repeat.'], 422);
        }

        // Determine promotion status
        if ($request->boolean('promotion')) {
            $promotionStatus = 'PROMOTED';
        } elseif ($request->boolean('trial')) {
            $promotionStatus = 'TRIAL';
        } elseif ($request->boolean('see_principal')) {
            $promotionStatus = 'SEE_PRINCIPAL';
        } elseif ($request->boolean('repeat')) {
            $promotionStatus = 'REPEAT';
        } else {
            $promotionStatus = 'PARENTS TO SEE PRINCIPAL';
        }

        try {
            DB::transaction(function () use ($studentId, $request, $promotionStatus) {
                $newClassId   = $request->new_schoolclassid;
                $newSessionId = $request->new_sessionid;
                $newTermId    = $request->new_termid;

                // 1. Studentclass — find-then-update (avoids duplication)
                $existingClass = Studentclass::where('studentId', $studentId)
                    ->where('sessionid', $newSessionId)
                    ->where('termid',    $newTermId)
                    ->first();

                if ($existingClass) {
                    $existingClass->update(['schoolclassid' => $newClassId]);
                } else {
                    Studentclass::create([
                        'studentId'     => $studentId,
                        'schoolclassid' => $newClassId,
                        'sessionid'     => $newSessionId,
                        'termid'        => $newTermId,
                    ]);
                }

                // 2. PromotionStatus — four-field key
                PromotionStatus::updateOrCreate(
                    [
                        'studentId'     => $studentId,
                        'schoolclassid' => $newClassId,
                        'sessionid'     => $newSessionId,
                        'termid'        => $newTermId,
                    ],
                    [
                        'promotionStatus' => $promotionStatus,
                        'classstatus'     => 'CURRENT',
                        'position'        => null,
                    ]
                );

                // 3. StudentCurrentTerm — mark as current
                DB::table('student_current_term')
                    ->where('studentId', $studentId)
                    ->update(['is_current' => false]);

                \App\Models\StudentCurrentTerm::updateOrCreate(
                    [
                        'studentId'     => $studentId,
                        'schoolclassId' => $newClassId,
                        'termId'        => $newTermId,
                        'sessionId'     => $newSessionId,
                    ],
                    ['is_current' => true]
                );
            });

            return response()->json(['success'=>true,'message'=>'Promotion updated successfully.']);

        } catch (Exception $e) {
            Log::error('Promotion update failed', ['studentId'=>$studentId,'request'=>$request->all(),'error'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>'Failed to update promotion.'], 500);
        }
    }

    public function destroy(Request $request, $studentId): JsonResponse
    {
        $request->validate([
            'schoolclassid' => 'required|exists:schoolclass,id',
            'sessionid'     => 'required|exists:schoolsession,id',
            'termid'        => 'required|integer|min:1|max:3',
        ]);

        try {
            DB::transaction(function () use ($studentId, $request) {
                Studentclass::where('studentId',     $studentId)
                    ->where('schoolclassid', $request->input('schoolclassid'))
                    ->where('sessionid',     $request->input('sessionid'))
                    ->where('termid',        $request->input('termid'))
                    ->delete();

                PromotionStatus::where('studentId',     $studentId)
                    ->where('schoolclassid', $request->input('schoolclassid'))
                    ->where('sessionid',     $request->input('sessionid'))
                    ->where('termid',        $request->input('termid'))
                    ->delete();
            });

            return response()->json(['success'=>true,'message'=>'Student removed successfully from class.']);

        } catch (Exception $e) {
            Log::error('Student removal failed', ['studentId'=>$studentId,'error'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>'Failed to remove student.'], 500);
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
                            default => 'PARENTS TO SEE PRINCIPAL',
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
