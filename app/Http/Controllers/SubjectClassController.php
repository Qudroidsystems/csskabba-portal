<?php

namespace App\Http\Controllers;

use App\Models\Broadsheets;
use App\Models\BroadsheetsMock;
use App\Models\Schoolclass;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Subjectclass;
use App\Models\SubjectRegistrationStatus;
use App\Models\SubjectTeacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SubjectClassController extends Controller
{
    public function __construct()
    {
        $this->middleware(
            'permission:View subject-class|Create subject-class|Update subject-class|Delete subject-class',
            ['only' => ['index', 'store']]
        );
        $this->middleware('permission:Create subject-class', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update subject-class', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete subject-class', ['only' => ['destroy', 'deletesubjectclass']]);
    }

    // =========================================================================
    // INDEX
    // =========================================================================

    public function index(Request $request)
    {
        $pagetitle = "Subject Class Management";

        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id as id', 'schoolclass.schoolclass as schoolclass', 'schoolarm.arm as arm'])
            ->sortBy('schoolclass');

        $subjectteacher = SubjectTeacher::leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
            ->get([
                'subjectteacher.id as id',
                'subjectteacher.staffid as subtid',
                'subjectteacher.subjectid as subid',
                'subject.id as subjectid',
                'subject.subject as subject',
                'subject.subject_code as subjectcode',
                'users.name as teachername',
                'schoolterm.id as termid',
                'schoolterm.term as termname',
                'schoolsession.id as sessionid',
                'schoolsession.session as sessionname',
            ])
            ->sortBy('subject');

        $subjectclasses = Subjectclass::leftJoin('schoolclass', 'subjectclass.schoolclassid', '=', 'schoolclass.id')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
            ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select([
                'subjectclass.id as scid',
                'schoolclass.id as schoolclassid',
                'schoolclass.schoolclass as sclass',
                'schoolarm.arm as schoolarm',
                'subjectteacher.id as subteacherid',
                'subjectteacher.staffid as subtid',
                'subjectteacher.subjectid as subid',
                'subject.id as subjectid',
                'subject.subject as subjectname',
                'subject.subject_code as subjectcode',
                'users.name as teachername',
                'users.avatar as picture',
                'schoolterm.id as termid',
                'schoolterm.term as termname',
                'schoolsession.id as sessionid',
                'schoolsession.session as sessionname',
                'subjectclass.updated_at',
            ])
            ->orderBy('sclass')
            ->get();

        return view('subjectclass.index')
            ->with('subjectclasses', $subjectclasses)
            ->with('schoolclasses', $schoolclasses)
            ->with('subjectteacher', $subjectteacher)
            ->with('pagetitle', $pagetitle);
    }

    // =========================================================================
    // CREATE
    // =========================================================================

    public function create()
    {
        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id as id', 'schoolclass.schoolclass as schoolclass', 'schoolarm.arm as arm'])
            ->sortBy('schoolclass');

        $subjectteachers = SubjectTeacher::leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
            ->get([
                'subjectteacher.id as id',
                'subjectteacher.staffid as subtid',
                'subjectteacher.subjectid as subid',
                'subject.id as subjectid',
                'subject.subject as subject',
                'subject.subject_code as subjectcode',
                'users.name as teachername',
                'schoolterm.id as termid',
                'schoolterm.term as termname',
                'schoolsession.id as sessionid',
                'schoolsession.session as sessionname',
            ])
            ->sortBy('subject');

        return view('subjectclass.create')
            ->with('schoolclasses', $schoolclasses)
            ->with('subjectteacher', $subjectteachers);
    }

    // =========================================================================
    // STORE
    // =========================================================================

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'schoolclassid'      => 'required|exists:schoolclass,id',
            'subjectteacherid'   => 'required|array|min:1',
            'subjectteacherid.*' => 'required|exists:subjectteacher,id',
        ], [
            'schoolclassid.required'      => 'Please select a class!',
            'schoolclassid.exists'        => 'Selected class does not exist!',
            'subjectteacherid.required'   => 'Please select at least one subject teacher!',
            'subjectteacherid.*.required' => 'Please select at least one subject teacher!',
            'subjectteacherid.*.exists'   => 'One or more selected subject teachers do not exist!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $schoolClassId    = $request->input('schoolclassid');
        $subjectTeacherIds = $request->input('subjectteacherid', []);

        if (empty($subjectTeacherIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Please select at least one subject teacher.',
            ], 422);
        }

        $createdRecords = [];
        $skippedCount   = 0;

        $subjectTeachers = SubjectTeacher::whereIn('id', $subjectTeacherIds)->get()->keyBy('id');

        foreach ($subjectTeacherIds as $subjectTeacherId) {
            $subjectTeacher = $subjectTeachers->get($subjectTeacherId);
            if (!$subjectTeacher) {
                continue;
            }

            $exists = Subjectclass::where('schoolclassid', $schoolClassId)
                ->where('subjectteacherid', $subjectTeacherId)
                ->exists();

            if ($exists) {
                $skippedCount++;
                continue;
            }

            $subjectclass = Subjectclass::create([
                'schoolclassid'    => $schoolClassId,
                'subjectteacherid' => $subjectTeacherId,
                'subjectid'        => $subjectTeacher->subjectid,
            ]);

            $createdRecords[] = $subjectclass;
        }

        if (empty($createdRecords)) {
            return response()->json([
                'success' => false,
                'message' => 'All selected subject teachers are already assigned to this class.',
            ], 422);
        }

        $message = count($createdRecords) . ' Subject Class(es) added successfully.';
        if ($skippedCount > 0) {
            $message .= " ({$skippedCount} already existed and were skipped.)";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $createdRecords,
        ], 201);
    }

    // =========================================================================
    // EDIT
    // =========================================================================

    public function edit($id)
    {
        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id as id', 'schoolclass.schoolclass as schoolclass', 'schoolarm.arm as arm'])
            ->sortBy('schoolclass');

        $subjectteachers = SubjectTeacher::leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
            ->get([
                'subjectteacher.id as id',
                'subjectteacher.staffid as subtid',
                'subjectteacher.subjectid as subid',
                'subject.id as subjectid',
                'subject.subject as subject',
                'subject.subject_code as subjectcode',
                'users.name as teachername',
                'schoolterm.id as termid',
                'schoolterm.term as termname',
                'schoolsession.id as sessionid',
                'schoolsession.session as sessionname',
            ])
            ->sortBy('subject');

        $subjectclasses = Subjectclass::where('subjectclass.id', $id)
            ->leftJoin('schoolclass', 'subjectclass.schoolclassid', '=', 'schoolclass.id')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
            ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->first([
                'subjectclass.id as scid',
                'schoolclass.id as schoolclassid',
                'schoolclass.schoolclass as sclass',
                'schoolarm.arm as schoolarm',
                'subjectteacher.id as subteacherid',
                'subjectteacher.staffid as subtid',
                'subjectteacher.subjectid as subid',
                'subject.id as subjectid',
                'subject.subject as subjectname',
                'subject.subject_code as subjectcode',
                'users.name as teachername',
                'users.avatar as picture',
                'schoolterm.id as termid',
                'schoolterm.term as termname',
                'schoolsession.id as sessionid',
                'schoolsession.session as sessionname',
                'subjectclass.updated_at',
            ]);

        if (!$subjectclasses) {
            return redirect()->route('subjectclass.index')
                ->with('danger', 'Subject Class not found.');
        }

        return view('subjectclass.edit')
            ->with('subjectclasses', collect([$subjectclasses]))
            ->with('schoolclasses', $schoolclasses)
            ->with('subjectteachers', $subjectteachers);
    }

    // =========================================================================
    // UPDATE — swap the STAFF MEMBER on an existing subject-class assignment.
    //
    // What stays the same:  subject, term, session, class
    // What changes:         the person (staff) teaching that subject
    //
    // Flow:
    //  1. Load the current subjectclass → get its current subjectteacher record
    //     to know subjectid + termid + sessionid (these never change).
    //  2. Accept only `new_staffid` from the request.
    //  3. Find or create a subjectteacher row for
    //     (new_staffid + same subjectid + same termid + same sessionid).
    //  4. Check the new combo isn't already assigned to this class.
    //  5. Point subjectclass.subjectteacherid at the new subjectteacher row.
    //  6. Cascade staff_id across broadsheets, mock, registrations, student records.
    // =========================================================================

    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'new_staffid' => 'required|exists:users,id',
        ], [
            'new_staffid.required' => 'Please select a staff member!',
            'new_staffid.exists'   => 'Selected staff member does not exist!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $newStaffId = (int) $request->input('new_staffid');

        // ── 1. Load the subject class ─────────────────────────────────
        $subjectclass = Subjectclass::find($id);
        if (!$subjectclass) {
            return response()->json([
                'success' => false,
                'message' => 'Subject Class not found.',
            ], 404);
        }

        // ── 2. Load the current subjectteacher to extract fixed fields ─
        $currentSubjectTeacher = SubjectTeacher::find($subjectclass->subjectteacherid);
        if (!$currentSubjectTeacher) {
            return response()->json([
                'success' => false,
                'message' => 'Current subject teacher record not found.',
            ], 404);
        }

        $oldStaffId  = (int) $currentSubjectTeacher->staffid;
        $subjectId   = $currentSubjectTeacher->subjectid;
        $termId      = $currentSubjectTeacher->termid;
        $sessionId   = $currentSubjectTeacher->sessionid;

        // No-op: same teacher
        if ($oldStaffId === $newStaffId) {
            return response()->json([
                'success' => true,
                'message' => 'No change — the selected teacher is already assigned.',
            ], 200);
        }

        // ── 3. Find or create subjectteacher for (newStaff + same subject/term/session) ─
        $newSubjectTeacher = SubjectTeacher::firstOrCreate(
            [
                'staffid'   => $newStaffId,
                'subjectid' => $subjectId,
                'termid'    => $termId,
                'sessionid' => $sessionId,
            ]
        );

        // ── 4. Check the new combo isn't already on this class ────────
        $duplicate = Subjectclass::where('schoolclassid', $subjectclass->schoolclassid)
            ->where('subjectteacherid', $newSubjectTeacher->id)
            ->where('id', '!=', $id)
            ->exists();

        if ($duplicate) {
            return response()->json([
                'success' => false,
                'message' => 'The selected teacher is already assigned to this subject and class for the same term and session.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            // ── 5. Update the subjectclass row ────────────────────────
            $subjectclass->update([
                'subjectteacherid' => $newSubjectTeacher->id,
                // subjectid, schoolclassid stay the same — no change needed
            ]);

            // ── 6. Cascade staff_id to all related records ────────────
            //    Scores (total, grade, etc.) are NEVER touched —
            //    only the staff assignment column is updated.

            Broadsheets::where('subjectclass_id', $id)
                ->update(['staff_id' => $newStaffId]);

            BroadsheetsMock::where('subjectclass_id', $id)
                ->update(['staff_id' => $newStaffId]);

            SubjectRegistrationStatus::where('subjectclassid', $id)
                ->update(['staffid' => $newStaffId]);

            DB::table('student_subject_register_record')
                ->where('subjectclassid', $id)
                ->update(['staffid' => $newStaffId]);

            DB::commit();

            // Fetch new staff name for the response message
            $newStaff = User::find($newStaffId);
            $oldStaff = User::find($oldStaffId);

            Log::info('SubjectClass staff swapped', [
                'subjectclass_id'       => $id,
                'old_staff_id'          => $oldStaffId,
                'new_staff_id'          => $newStaffId,
                'new_subjectteacher_id' => $newSubjectTeacher->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Teacher changed from {$oldStaff?->name} to {$newStaff?->name} successfully. All related records updated.",
                'data'    => $subjectclass->fresh(),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('SubjectClass staff swap failed', [
                'subjectclass_id' => $id,
                'error'           => $e->getMessage(),
                'trace'           => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update teacher: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // ASSIGNMENTS — fetch class + teacher for a given subject class ID
    // =========================================================================

    public function assignments($subjectClassId): JsonResponse
    {
        try {
            $subjectclass = Subjectclass::where('id', $subjectClassId)
                ->select('schoolclassid', 'subjectteacherid')
                ->first();

            if (!$subjectclass) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subject Class not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data'    => [
                    'schoolclassid'    => $subjectclass->schoolclassid,
                    'subjectteacherid' => [$subjectclass->subjectteacherid],
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching assignments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch assignments.',
            ], 500);
        }
    }

    // =========================================================================
    // ASSIGNMENTS BY SUBJECT TEACHER
    // =========================================================================

    public function assignmentsBySubjectTeacher($subjectTeacherId): JsonResponse
    {
        try {
            $assignments = Subjectclass::where('subjectteacherid', $subjectTeacherId)
                ->select('schoolclassid')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $assignments,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching assignments by subject teacher: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch assignments.',
            ], 500);
        }
    }

    // =========================================================================
    // DESTROY — single delete (blocked if records exist)
    // =========================================================================

    public function destroy($id): JsonResponse
    {
        $subjectclass = Subjectclass::find($id);

        if (!$subjectclass) {
            return response()->json([
                'success' => false,
                'message' => 'Subject Class not found.',
            ], 404);
        }

        // ── Guard: block deletion if related records exist ────────────
        $hasBroadsheets    = Broadsheets::where('subjectclass_id', $id)->exists();
        $hasRegistrations  = SubjectRegistrationStatus::where('subjectclassid', $id)->exists();
        $hasMockRecords    = BroadsheetsMock::where('subjectclass_id', $id)->exists();

        if ($hasBroadsheets || $hasRegistrations || $hasMockRecords) {
            $details = [];
            if ($hasBroadsheets)   $details[] = 'broadsheet score records';
            if ($hasRegistrations) $details[] = 'student subject registrations';
            if ($hasMockRecords)   $details[] = 'mock broadsheet records';

            return response()->json([
                'success' => false,
                'message' => 'Cannot delete this Subject Class because it has existing '
                           . implode(', ', $details) . '. '
                           . 'Please unregister all students from this subject class before deleting it.',
            ], 422);
        }

        $subjectclass->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subject Class deleted successfully.',
        ], 200);
    }

    // =========================================================================
    // DELETE SUBJECT CLASS (alternative endpoint)
    // =========================================================================

    public function deletesubjectclass(Request $request): JsonResponse
    {
        $subjectclass = Subjectclass::find($request->subjectclassid);

        if (!$subjectclass) {
            return response()->json([
                'success' => false,
                'message' => 'Subject Class not found.',
            ], 404);
        }

        // ── Guard: block deletion if related records exist ────────────
        $hasBroadsheets   = Broadsheets::where('subjectclass_id', $request->subjectclassid)->exists();
        $hasRegistrations = SubjectRegistrationStatus::where('subjectclassid', $request->subjectclassid)->exists();
        $hasMockRecords   = BroadsheetsMock::where('subjectclass_id', $request->subjectclassid)->exists();

        if ($hasBroadsheets || $hasRegistrations || $hasMockRecords) {
            $details = [];
            if ($hasBroadsheets)   $details[] = 'broadsheet score records';
            if ($hasRegistrations) $details[] = 'student subject registrations';
            if ($hasMockRecords)   $details[] = 'mock broadsheet records';

            return response()->json([
                'success' => false,
                'message' => 'Cannot delete this Subject Class because it has existing '
                           . implode(', ', $details) . '. '
                           . 'Please unregister all students from this subject class before deleting it.',
            ], 422);
        }

        $subjectclass->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subject Class has been removed.',
        ], 200);
    }
}
