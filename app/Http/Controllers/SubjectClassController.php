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
    // UPDATE
    // =========================================================================

    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'schoolclassid'    => 'required|exists:schoolclass,id',
            'subjectteacherid' => 'required|exists:subjectteacher,id',
        ], [
            'schoolclassid.required'    => 'Please select a class!',
            'schoolclassid.exists'      => 'Selected class does not exist!',
            'subjectteacherid.required' => 'Please select a subject teacher!',
            'subjectteacherid.exists'   => 'Selected subject teacher does not exist!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $schoolClassId    = $request->input('schoolclassid');
        $subjectTeacherId = $request->input('subjectteacherid');

        // Find the new subject teacher
        $newSubjectTeacher = SubjectTeacher::find($subjectTeacherId);
        if (!$newSubjectTeacher) {
            return response()->json([
                'success' => false,
                'message' => 'Subject teacher not found.',
            ], 404);
        }

        // Find the subject class being updated
        $subjectclass = Subjectclass::find($id);
        if (!$subjectclass) {
            return response()->json([
                'success' => false,
                'message' => 'Subject Class not found.',
            ], 404);
        }

        // Check for duplicate assignment (excluding current record)
        $duplicate = Subjectclass::where('schoolclassid', $schoolClassId)
            ->where('subjectteacherid', $subjectTeacherId)
            ->where('id', '!=', $id)
            ->exists();

        if ($duplicate) {
            return response()->json([
                'success' => false,
                'message' => 'This subject teacher is already assigned to this class.',
            ], 422);
        }

        // Determine old staff for cascade update
        $oldSubjectTeacher = SubjectTeacher::find($subjectclass->subjectteacherid);
        $oldStaffId        = $oldSubjectTeacher?->staffid;
        $newStaffId        = $newSubjectTeacher->staffid;
        $teacherChanged    = $oldStaffId && $newStaffId && ($oldStaffId !== $newStaffId);

        try {
            DB::beginTransaction();

            // 1. Update the subject class record itself
            $subjectclass->update([
                'schoolclassid'    => $schoolClassId,
                'subjectteacherid' => $subjectTeacherId,
                'subjectid'        => $newSubjectTeacher->subjectid,
            ]);

            // 2. If the assigned staff member changed, cascade the update
            //    to all related records — scores are NEVER touched, only
            //    the staff_id / staffid assignment column is updated.
            if ($teacherChanged) {

                // Broadsheet score records for this subject class
                Broadsheets::where('subjectclass_id', $id)
                    ->update(['staff_id' => $newStaffId]);

                // Mock broadsheet records for this subject class
                BroadsheetsMock::where('subjectclass_id', $id)
                    ->update(['staff_id' => $newStaffId]);

                // Subject registration status records
                SubjectRegistrationStatus::where('subjectclassid', $id)
                    ->update(['staffid' => $newStaffId]);

                // Student subject register records
                DB::table('student_subject_register_record')
                    ->where('subjectclassid', $id)
                    ->update(['staffid' => $newStaffId]);

                Log::info('SubjectClass teacher changed — cascaded staff_id update', [
                    'subjectclass_id' => $id,
                    'old_staff_id'    => $oldStaffId,
                    'new_staff_id'    => $newStaffId,
                ]);
            }

            DB::commit();

            $message = 'Subject Class updated successfully.';
            if ($teacherChanged) {
                $message .= ' Staff assignment updated across all related records.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data'    => $subjectclass->fresh(),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('SubjectClass update failed', [
                'subjectclass_id' => $id,
                'error'           => $e->getMessage(),
                'trace'           => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update Subject Class: ' . $e->getMessage(),
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
