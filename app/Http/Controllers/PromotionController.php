<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\View\View;
use App\Models\Schoolclass;
use App\Models\Studentclass;
use Illuminate\Http\Request;
use App\Models\Schoolsession;
use App\Models\PromotionStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;

class PromotionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View promotion',   ['only' => ['index']]);
        $this->middleware('permission:Update promotion', ['only' => ['update', 'destroy']]);
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
            $query = Studentclass::query()
                ->where('studentclass.schoolclassid', $request->input('schoolclassid'))
                ->where('studentclass.sessionid',     $request->input('sessionid'))
                ->leftJoin('studentRegistration', 'studentRegistration.id',   '=', 'studentclass.studentId')
                ->leftJoin('studentpicture',      'studentpicture.studentid', '=', 'studentRegistration.id')
                ->leftJoin('schoolclass',         'schoolclass.id',           '=', 'studentclass.schoolclassid')
                ->leftJoin('schoolarm',           'schoolarm.id',             '=', 'schoolclass.arm')
                ->leftJoin('schoolsession',       'schoolsession.id',         '=', 'studentclass.sessionid');

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

                // Attach promotion statuses in one query
                $studentKeys = $allstudents->map(fn($s) =>
                    $s->stid.'_'.$s->schoolclassID.'_'.$s->sessionid
                )->toArray();

                $promotionStatuses = PromotionStatus::whereIn(
                    DB::raw("CONCAT(studentId,'_',schoolclassid,'_',sessionid)"),
                    $studentKeys
                )->select(['id','studentId','schoolclassid','sessionid','promotionStatus'])
                 ->get()
                 ->keyBy(fn($item) => $item->studentId.'_'.$item->schoolclassid.'_'.$item->sessionid);

                $allstudents->getCollection()->transform(function ($student) use ($promotionStatuses) {
                    $key = $student->stid.'_'.$student->schoolclassID.'_'.$student->sessionid;
                    if (isset($promotionStatuses[$key])) {
                        $student->promotion_status = $promotionStatuses[$key]->promotionStatus ?? 'N/A';
                        $student->promotion_id     = $promotionStatuses[$key]->id;
                    } else {
                        $student->promotion_status = 'N/A';
                        $student->promotion_id     = null;
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

        if ($request->ajax()) {
            return response()->json([
                'tableBody'    => view('promotions.partials.student_rows', compact('allstudents'))->render(),
                'pagination'   => $allstudents->links('pagination::bootstrap-5')->render(),
                'studentCount' => $allstudents->total(),
            ]);
        }

        return view('promotions.index', compact('allstudents','schoolsessions','schoolclasses','pagetitle'));
    }

    /**
     * Promote or repeat a student.
     *
     * FIX SUMMARY vs original:
     * ─────────────────────────────────────────────────────────────────────────
     * Studentclass:
     *   OLD (buggy): updateOrCreate([studentId, termid, sessionid], [schoolclassid])
     *     → If class changes, a SECOND row is inserted; the old row survives.
     *       Both rows exist so the student shows in two classes.
     *
     *   NEW (fixed): find-then-update on [studentId, sessionid, termid]
     *     → Always updates the single existing row.  If none exists, inserts one.
     *
     * PromotionStatus:
     *   updateOrCreate([studentId, schoolclassid, sessionid, termid], [...])
     *   → Key includes all four fields, so no duplicate is created.  Correct.
     *
     * StudentCurrentTerm:
     *   Clear is_current on all other rows, then updateOrCreate on full key.
     *   → Correct; unchanged.
     * ─────────────────────────────────────────────────────────────────────────
     */
    public function update(Request $request, $studentId): JsonResponse
    {
        $request->validate([
            'new_schoolclassid' => 'required|exists:schoolclass,id',
            'new_sessionid'     => 'required|exists:schoolsession,id',
            'new_termid'        => 'required|integer|min:1|max:3',
            'promotion'         => 'boolean',
            'repeat'            => 'boolean',
        ]);

        if ($request->boolean('promotion') && $request->boolean('repeat')) {
            return response()->json(['success'=>false,'message'=>'Cannot select both promotion and repeat.'], 422);
        }

        $promotionStatus = $request->boolean('promotion')
            ? 'PROMOTED'
            : ($request->boolean('repeat') ? 'REPEAT' : 'PARENTS TO SEE PRINCIPAL');

        try {
            DB::transaction(function () use ($studentId, $request, $promotionStatus) {
                $newClassId   = $request->new_schoolclassid;
                $newSessionId = $request->new_sessionid;
                $newTermId    = $request->new_termid;

                // -------------------------------------------------------
                // 1. Studentclass — find-then-update (avoids duplication)
                //    DO NOT put schoolclassid in the match key; doing so
                //    would INSERT a new row whenever the class changes.
                // -------------------------------------------------------
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

                // -------------------------------------------------------
                // 2. PromotionStatus — four-field key is correct
                // -------------------------------------------------------
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

                // -------------------------------------------------------
                // 3. StudentCurrentTerm — mark as current
                // -------------------------------------------------------
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
}
