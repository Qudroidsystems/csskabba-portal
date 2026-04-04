<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Schoolterm;
use App\Models\Broadsheets;
use App\Models\Schoolclass;
use App\Models\Subjectclass;
use Illuminate\Http\Request;
use App\Models\Schoolsession;
use App\Models\Studentpicture;
use App\Models\SubjectTeacher;
use App\Models\BroadsheetsMock;
use App\Models\BroadsheetRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\BroadsheetRecordMock;
use App\Models\StudentSubjectRecord;
use App\Models\SubjectRegistrationStatus;
use App\Models\BroadsheetAssessmentScore;
use App\Models\SubjectUnregistrationArchive;
use App\Models\BroadsheetSubAssessmentScore;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class SubjectOperationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View subject-operation|Create subject-operation|Update subject-operation|Delete subject-operation', ['only' => ['index', 'subjectinfo', 'getRegisteredClasses', 'getArchivedRegistrations']]);
        $this->middleware('permission:Create subject-operation', ['only' => ['store', 'restoreRegistration']]);
        $this->middleware('permission:Delete subject-operation', ['only' => ['destroy', 'permanentlyDeleteArchive', 'permanentlyDeleteArchiveBatch']]);
    }

    /**
     * Display a list of students for subject registration with filters.
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $pagetitle = "Subject Operation Management";

        $schoolclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.id as id', 'schoolarm.arm as schoolarm', 'schoolclass.schoolclass as schoolclass'])
            ->orderBy('schoolclass.schoolclass')
            ->get();
        $schoolterms    = Schoolterm::all();
        $schoolsessions = Schoolsession::all();

        $students        = null;
        $subjectTeachers = null;

        if ($request->filled(['class_id', 'session_id']) &&
            $request->input('class_id') !== 'ALL' &&
            $request->input('session_id') !== 'ALL') {

            $subjectTeachers = SubjectTeacher::leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
                ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
                ->leftJoin('subjectclass', 'subjectclass.subjectteacherid', '=', 'subjectteacher.id')
                ->leftJoin('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->where('subjectteacher.sessionid', $request->input('session_id'))
                ->where('subjectclass.schoolclassid', $request->input('class_id'))
                ->select([
                    'subjectteacher.id as id',
                    'subjectclass.id as subjectclassid',
                    'users.id as userid',
                    'users.name as staffname',
                    'users.avatar as avatar',
                    'subject.id as subjectid',
                    'subject.subject as subjectname',
                    'subject.subject_code as subjectcode',
                    'schoolterm.id as termid',
                    'schoolterm.term as termname',
                    'schoolsession.id as sessionid',
                    'schoolsession.session as sessionname',
                    'schoolclass.schoolclass as class_name',
                    'schoolarm.arm as arm_name',
                    'subjectteacher.updated_at',
                ])
                ->get();

            $query = Student::leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
                ->leftJoin('studentclass', 'studentclass.studentid', '=', 'studentRegistration.id')
                ->leftJoin('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm');

            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('studentRegistration.admissionno', 'like', "%{$search}%")
                        ->orWhere('studentRegistration.firstname', 'like', "%{$search}%")
                        ->orWhere('studentRegistration.lastname', 'like', "%{$search}%");
                });
            }

            if ($gender = $request->input('gender')) {
                if ($gender !== 'ALL') {
                    $query->where('studentRegistration.gender', $gender);
                }
            }

            if ($admissionNo = $request->input('admissionno')) {
                if ($admissionNo !== 'ALL') {
                    $query->where('studentRegistration.admissionno', $admissionNo);
                }
            }

            $query->where('studentclass.schoolclassid', $request->input('class_id'))
                ->where('studentclass.sessionid', $request->input('session_id'));

            $students = $query->select([
                'studentRegistration.id as id',
                'studentRegistration.admissionno as admissionno',
                'studentRegistration.firstname',
                'studentRegistration.lastname',
                'studentRegistration.othername',
                'studentRegistration.gender',
                'studentRegistration.updated_at',
                'studentpicture.picture',
                'studentclass.studentid as studentid',
                'studentclass.schoolclassid as schoolclassid',
                'studentclass.sessionid',
                'schoolclass.schoolclass as class_name',
                'schoolarm.arm as arm_name',
            ])->paginate(100)->appends($request->query());
        }

        return view('subjectoperation.index', compact(
            'students', 'subjectTeachers', 'pagetitle', 'schoolclass', 'schoolterms', 'schoolsessions'
        ));
    }

    /**
     * Display subject information for a specific student.
     */
    public function subjectinfo(Request $request, $id, $schoolclassid, $termid, $sessionid): \Illuminate\View\View|\Illuminate\Http\JsonResponse
    {
        $current = "Current";

        try {
            $pagetitle   = "Subject Operation Management";
            $studentdata = Student::where('id', $id)->get();
            if ($studentdata->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Student not found'], 404);
            }

            $studentpic = Studentpicture::where('studentid', $id)->select(['studentid', 'picture as avatar'])->get();

            $subjectclass = Subjectclass::query()
                ->where('subjectclass.schoolclassid', $schoolclassid)
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
                ->where('schoolterm.id', 2)
                ->where('schoolsession.id', $sessionid)
                ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
                ->leftJoin('staffbioinfo', 'staffbioinfo.userid', '=', 'users.id')
                ->leftJoin('staffpicture', 'staffpicture.staffid', '=', 'users.id')
                ->groupBy([
                    'subject.id', 'users.id', 'staffbioinfo.title', 'users.name',
                    'staffpicture.picture', 'subject.subject', 'subject.subject_code',
                    'subjectclass.id', 'schoolterm.term', 'schoolterm.id',
                    'schoolsession.session', 'schoolsession.id',
                ])
                ->select([
                    'subject.id as subjectid', 'staffbioinfo.title', 'users.name',
                    'staffpicture.picture as picture', 'subject.subject',
                    'users.id as staffid', 'subject.subject_code as subjectcode',
                    'subjectclass.id as subjectclassid', 'schoolterm.term',
                    'schoolterm.id as termid', 'schoolsession.session',
                    'schoolsession.id as sessionid',
                ])
                ->get();

            $subjectRegistrations = [];
            foreach ($subjectclass as $sc) {
                $subjectRegistrations[$sc->subjectid][$sc->staffid] = [
                    'subjectclassid' => $sc->subjectclassid,
                    'status' => StudentSubjectRecord::where([
                        'studentId'      => $id,
                        'subjectclassid' => $sc->subjectclassid,
                        'staffid'        => $sc->staffid,
                        'session'        => $sessionid,
                    ])->exists()
                        ? ['status' => 'Registered', 'broadsheetid' => SubjectRegistrationStatus::where([
                            'studentid'      => $id,
                            'subjectclassid' => $sc->subjectclassid,
                            'staffid'        => $sc->staffid,
                        ])->value('broadsheetid')]
                        : ['status' => 'Not Registered', 'broadsheetid' => null],
                ];
            }

            $totalreg = Subjectclass::where('subjectclass.schoolclassid', $schoolclassid)
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
                ->where('schoolterm.id', 2)
                ->where('schoolsession.id', $sessionid)
                ->distinct('subjectteacher.subjectid')
                ->count('subjectteacher.subjectid');

            $regcount = StudentSubjectRecord::where('student_subject_register_record.studentId', $id)
                ->leftJoin('subjectclass', 'subjectclass.id', '=', 'student_subject_register_record.subjectclassid')
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'student_subject_register_record.session')
                ->where('schoolterm.id', 2)
                ->where('schoolsession.status', $current)
                ->count();

            $noregcount = $totalreg - $regcount;

            $classname = Schoolclass::where('schoolclass.id', $schoolclassid)
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->select(['schoolclass.id', 'schoolclass.schoolclass as schoolclass', 'schoolarm.arm as arm'])
                ->get();

            $terms = Schoolterm::all();

            return view('subjectoperation.subjectinfo', compact(
                'studentpic', 'classname', 'subjectclass', 'subjectRegistrations',
                'studentdata', 'id', 'termid', 'sessionid', 'totalreg',
                'regcount', 'noregcount', 'pagetitle', 'terms'
            ));

        } catch (\Exception $error) {
            Log::error('Error fetching subject info', [
                'student_id'   => $id,
                'schoolclassid'=> $schoolclassid,
                'error'        => $error->getMessage(),
                'trace'        => $error->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subject information: ' . $error->getMessage(),
            ], 500);
        }
    }

    /**
     * Get subject teachers via AJAX.
     */
    public function getSubjectTeachers(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $classId   = $request->input('class_id');
        $termId    = $request->input('term_id');
        $sessionId = $request->input('session_id');

        if (!$classId || !$termId || !$sessionId ||
            $classId === 'ALL' || $termId === 'ALL' || $sessionId === 'ALL') {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }

        $subjectTeachers = SubjectTeacher::leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
            ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
            ->leftJoin('subjectclass', 'subjectclass.subjectteacherid', '=', 'subjectteacher.id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->where('subjectteacher.termid', $termId)
            ->where('subjectteacher.sessionid', $sessionId)
            ->where('subjectclass.schoolclassid', $classId)
            ->select([
                'subjectteacher.id as id',
                'subjectclass.id as subjectclassid',
                'users.id as userid',
                'users.name as staffname',
                'users.avatar as avatar',
                'subject.id as subjectid',
                'subject.subject as subjectname',
                'subject.subject_code as subjectcode',
                'schoolterm.id as termid',
                'schoolterm.term as termname',
                'schoolsession.id as sessionid',
                'schoolsession.session as sessionname',
                'schoolclass.schoolclass as class_name',
                'schoolarm.arm as arm_name',
            ])
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $subjectTeachers,
            'count'   => $subjectTeachers->count(),
        ]);
    }

    /**
     * Store a newly created subject registration for one or multiple students.
     */
    public function store(Request $request): array
    {
        $validated = $request->validate([
            'studentid'      => ['required', 'array'],
            'studentid.*'    => ['required', 'exists:studentRegistration,id'],
            'subjectclassid' => ['required', 'exists:subjectclass,id'],
            'staffid'        => ['required', 'exists:users,id'],
            'termid'         => ['required', 'exists:schoolterm,id'],
            'sessionid'      => ['required', 'exists:schoolsession,id'],
        ]);

        $studentCount       = count($validated['studentid']);
        $batchThreshold     = 50;
        $largeDatasetThreshold = 500;

        if ($studentCount <= $batchThreshold) {
            return $this->processIndividually($validated);
        } elseif ($studentCount <= $largeDatasetThreshold) {
            return $this->processBatch($validated);
        } else {
            return $this->processLargeDataset($validated);
        }
    }

    /**
     * Batch register students for multiple subjects.
     */
    public function batchRegister(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'studentids'                      => ['required', 'array'],
            'studentids.*'                    => ['required', 'exists:studentRegistration,id'],
            'subjectclasses'                  => ['required', 'array'],
            'subjectclasses.*.subjectclassid' => ['required', 'exists:subjectclass,id'],
            'subjectclasses.*.staffid'        => ['required', 'exists:users,id'],
            'subjectclasses.*.termid'         => ['required', 'exists:schoolterm,id'],
            'sessionid'                       => ['required', 'exists:schoolsession,id'],
        ]);

        $results      = [];
        $errors       = [];
        $successCount = 0;

        try {
            DB::beginTransaction();

            foreach ($validated['subjectclasses'] as $subject) {
                $response = $this->processIndividually([
                    'studentid'      => $validated['studentids'],
                    'subjectclassid' => $subject['subjectclassid'],
                    'staffid'        => $subject['staffid'],
                    'termid'         => $subject['termid'],
                    'sessionid'      => $validated['sessionid'],
                ]);

                if ($response['success']) {
                    $successCount += $response['success_count'];
                } else {
                    $errors[] = [
                        'subjectclassid' => $subject['subjectclassid'],
                        'termid'         => $subject['termid'],
                        'message'        => $response['message'] ?? 'Error',
                        'details'        => $response['errors'] ?? [],
                    ];
                }
                $results[] = $response;
            }

            DB::commit();

            return response()->json([
                'success'       => empty($errors),
                'message'       => "Successfully registered {$successCount} student(s).",
                'results'       => $results,
                'error_details' => $errors,
                'success_count' => $successCount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch registration failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Batch registration failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unregister students (soft-archives before hard delete).
     */
    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'studentids'                      => ['required', 'array'],
            'studentids.*'                    => ['required', 'exists:studentRegistration,id'],
            'subjectclasses'                  => ['required', 'array'],
            'subjectclasses.*.subjectclassid' => ['required', 'exists:subjectclass,id'],
            'subjectclasses.*.staffid'        => ['required', 'exists:users,id'],
            'subjectclasses.*.termid'         => ['required', 'exists:schoolterm,id'],
            'sessionid'                       => ['required', 'exists:schoolsession,id'],
        ]);

        $results             = [];
        $errors              = [];
        $unregisteredStudents = [];
        $skippedCount        = 0;
        $unregisteredById    = Auth::id();

        try {
            DB::beginTransaction();

            foreach ($validated['subjectclasses'] as $subject) {
                $subjectclassid = $subject['subjectclassid'];
                $staffid        = $subject['staffid'];
                $termid         = $subject['termid'];
                $sessionid      = $validated['sessionid'];

                $subjectclass  = Subjectclass::findOrFail($subjectclassid);
                $subjectId     = $subjectclass->subjectid;
                $schoolclassId = $subjectclass->schoolclassid;

                $existingRegistrations = SubjectRegistrationStatus::where([
                    'subjectclassid' => $subjectclassid,
                    'termid'         => $termid,
                    'sessionid'      => $sessionid,
                    'staffid'        => $staffid,
                ])->whereIn('studentid', $validated['studentids'])
                    ->get()
                    ->keyBy('studentid');

                $studentsToProcess = array_intersect(
                    $validated['studentids'],
                    $existingRegistrations->keys()->toArray()
                );
                $skippedCount += count(array_diff($validated['studentids'], $studentsToProcess));

                if (empty($studentsToProcess)) {
                    $errors[] = [
                        'subjectclassid' => $subjectclassid,
                        'termid'         => $termid,
                        'message'        => 'No students are registered for this subject.',
                    ];
                    continue;
                }

                $unregisteredStudents = array_unique(array_merge($unregisteredStudents, $studentsToProcess));

                $broadsheetRecordIds = $existingRegistrations->pluck('broadsheetid')->filter()->toArray();

                $archiveRows = [];
                $now         = now();
                foreach ($studentsToProcess as $studentId) {
                    $reg = $existingRegistrations->get($studentId);
                    $archiveRows[] = [
                        'studentid'           => $studentId,
                        'subjectclassid'      => $subjectclassid,
                        'staffid'             => $staffid,
                        'termid'              => $termid,
                        'sessionid'           => $sessionid,
                        'subjectid'           => $subjectId,
                        'schoolclassid'       => $schoolclassId,
                        'broadsheet_record_id'=> $reg?->broadsheetid,
                        'unregistered_by'     => $unregisteredById,
                        'status'              => SubjectUnregistrationArchive::STATUS_ARCHIVED,
                        'unregistered_at'     => $now,
                        'created_at'          => $now,
                        'updated_at'          => $now,
                    ];
                }
                SubjectUnregistrationArchive::insertOrIgnore($archiveRows);

                $broadsheetSheetIds = Broadsheets::whereIn('broadsheet_record_id', $broadsheetRecordIds)
                    ->where('term_id', $termid)
                    ->where('subjectclass_id', $subjectclassid)
                    ->pluck('id');

                if ($broadsheetSheetIds->isNotEmpty()) {
                    BroadsheetAssessmentScore::whereIn('broadsheet_id', $broadsheetSheetIds)->delete();
                    BroadsheetSubAssessmentScore::whereIn('broadsheet_id', $broadsheetSheetIds)->delete();
                }

                $mockRecordIds = BroadsheetRecordMock::whereIn('student_id', $studentsToProcess)
                    ->where('subject_id', $subjectId)
                    ->where('schoolclass_id', $schoolclassId)
                    ->where('session_id', $sessionid)
                    ->pluck('id');

                if ($mockRecordIds->isNotEmpty()) {
                    BroadsheetsMock::whereIn('broadsheet_records_mock_id', $mockRecordIds)
                        ->where('subjectclass_id', $subjectclassid)
                        ->where('term_id', $termid)
                        ->where('staff_id', $staffid)
                        ->delete();
                }

                Broadsheets::whereIn('broadsheet_record_id', $broadsheetRecordIds)
                    ->where('term_id', $termid)
                    ->where('subjectclass_id', $subjectclassid)
                    ->delete();

                $orphanedRecordIds = collect($broadsheetRecordIds)->filter(function ($recordId) {
                    return Broadsheets::where('broadsheet_record_id', $recordId)->doesntExist();
                })->toArray();

                if (!empty($orphanedRecordIds)) {
                    BroadsheetRecord::whereIn('id', $orphanedRecordIds)->delete();
                }

                if ($mockRecordIds->isNotEmpty()) {
                    $orphanedMockIds = BroadsheetRecordMock::whereIn('id', $mockRecordIds)
                        ->get()
                        ->filter(function ($mock) {
                            return BroadsheetsMock::where('broadsheet_records_mock_id', $mock->id)->doesntExist();
                        })
                        ->pluck('id')
                        ->toArray();

                    if (!empty($orphanedMockIds)) {
                        BroadsheetRecordMock::whereIn('id', $orphanedMockIds)->delete();
                    }
                }

                StudentSubjectRecord::whereIn('studentId', $studentsToProcess)
                    ->where('subjectclassid', $subjectclassid)
                    ->where('staffid', $staffid)
                    ->where('session', $sessionid)
                    ->delete();

                SubjectRegistrationStatus::whereIn('studentid', $studentsToProcess)
                    ->where('subjectclassid', $subjectclassid)
                    ->where('termid', $termid)
                    ->where('sessionid', $sessionid)
                    ->where('staffid', $staffid)
                    ->delete();

                Log::info('Unregistered subjects for students', [
                    'subjectclassid'  => $subjectclassid,
                    'termid'          => $termid,
                    'sessionid'       => $sessionid,
                    'student_count'   => count($studentsToProcess),
                    'archived_count'  => count($archiveRows),
                ]);

                $results[] = [
                    'subjectclassid'       => $subjectclassid,
                    'termid'               => $termid,
                    'message'              => 'Successfully unregistered ' . count($studentsToProcess) . ' students',
                    'students_unregistered'=> $studentsToProcess,
                ];
            }

            $successCount = count($unregisteredStudents);

            if ($successCount === 0 && !empty($errors)) {
                DB::rollBack();
                return response()->json([
                    'success'       => false,
                    'message'       => 'No students were unregistered.',
                    'error_details' => $errors,
                    'success_count' => 0,
                    'skipped_count' => $skippedCount,
                ], 422);
            }

            DB::commit();

            return response()->json([
                'success'       => empty($errors),
                'message'       => "Successfully unregistered {$successCount} student(s) from " . count($validated['subjectclasses']) . " subject(s).",
                'results'       => $results,
                'error_details' => $errors,
                'success_count' => $successCount,
                'skipped_count' => $skippedCount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch unregistration failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Batch unregistration failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get archived registrations for the Unregistered History modal.
     */
    public function getArchivedRegistrations(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'class_id'   => ['required', 'integer', 'exists:schoolclass,id'],
            'session_id' => ['required', 'integer', 'exists:schoolsession,id'],
            'term_id'    => ['nullable', 'integer', 'exists:schoolterm,id'],
            'search'     => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $query = SubjectUnregistrationArchive::query()
                ->where('subject_unregistration_archive.status', SubjectUnregistrationArchive::STATUS_ARCHIVED)
                ->where('subject_unregistration_archive.sessionid', $validated['session_id'])
                ->where('subject_unregistration_archive.schoolclassid', $validated['class_id'])
                ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'subject_unregistration_archive.studentid')
                ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
                ->leftJoin('subjectclass', 'subjectclass.id', '=', 'subject_unregistration_archive.subjectclassid')
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->leftJoin('subject', 'subject.id', '=', 'subject_unregistration_archive.subjectid')
                ->leftJoin('users as staff', 'staff.id', '=', 'subject_unregistration_archive.staffid')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subject_unregistration_archive.termid')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subject_unregistration_archive.sessionid')
                ->leftJoin('schoolclass', 'schoolclass.id', '=', 'subject_unregistration_archive.schoolclassid')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->leftJoin('users as actor', 'actor.id', '=', 'subject_unregistration_archive.unregistered_by')
                ->select([
                    'subject_unregistration_archive.id as archive_id',
                    'subject_unregistration_archive.studentid',
                    'subject_unregistration_archive.subjectclassid',
                    'subject_unregistration_archive.staffid',
                    'subject_unregistration_archive.termid',
                    'subject_unregistration_archive.sessionid',
                    'subject_unregistration_archive.subjectid',
                    'subject_unregistration_archive.schoolclassid',
                    'subject_unregistration_archive.broadsheet_record_id',
                    'subject_unregistration_archive.unregistered_at',
                    'studentRegistration.admissionno',
                    'studentRegistration.firstname',
                    'studentRegistration.lastname',
                    'studentRegistration.othername',
                    'studentRegistration.gender',
                    'studentpicture.picture',
                    'subject.subject as subjectname',
                    'subject.subject_code as subjectcode',
                    'staff.name as staffname',
                    'schoolterm.term as termname',
                    'schoolsession.session as sessionname',
                    'schoolclass.schoolclass as class_name',
                    'schoolarm.arm as arm_name',
                    'actor.name as unregistered_by_name',
                ]);

            if (!empty($validated['term_id'])) {
                $query->where('subject_unregistration_archive.termid', $validated['term_id']);
            }

            if (!empty($validated['search'])) {
                $searchTerm = '%' . $validated['search'] . '%';
                $query->where(function($q) use ($searchTerm) {
                    $q->where('studentRegistration.firstname', 'like', $searchTerm)
                      ->orWhere('studentRegistration.lastname', 'like', $searchTerm)
                      ->orWhere('studentRegistration.admissionno', 'like', $searchTerm)
                      ->orWhere('subject.subject', 'like', $searchTerm);
                });
            }

            $query->orderBy('subject_unregistration_archive.unregistered_at', 'desc');

            $perPage  = (int) $request->input('per_page', 50);
            $archived = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data'    => $archived->items(),
                'meta'    => [
                    'current_page' => $archived->currentPage(),
                    'last_page'    => $archived->lastPage(),
                    'total'        => $archived->total(),
                    'per_page'     => $archived->perPage(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching archived registrations', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get registered classes with teacher names.
     */
    public function registeredClasses(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'class_id'   => ['required', 'integer', 'exists:schoolclass,id'],
                'session_id' => ['required', 'integer', 'exists:schoolsession,id'],
                'term_id'    => ['nullable', 'integer', 'exists:schoolterm,id'],
            ]);

            DB::statement('SET SESSION group_concat_max_len = 1000000');

            $query = SubjectRegistrationStatus::query()
                ->join('subjectclass', 'subjectclass.id', '=', 'subject_registration_status.subjectclassid')
                ->join('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->join('schoolsession', 'schoolsession.id', '=', 'subject_registration_status.sessionid')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subject_registration_status.termid')
                ->leftJoin('broadsheet', 'broadsheet.id', '=', 'subject_registration_status.broadsheetid')
                ->leftJoin('subject', 'subject.id', '=', 'broadsheet.subjectid')
                ->leftJoin('users as teachers', 'teachers.id', '=', 'subject_registration_status.staffid')
                ->where('subjectclass.schoolclassid', $validated['class_id'])
                ->where('subject_registration_status.sessionid', $validated['session_id'])
                ->when($validated['term_id'], fn($q, $t) => $q->where('subject_registration_status.termid', $t))
                ->groupBy([
                    'schoolclass.id', 'schoolarm.id', 'schoolsession.id', 'schoolterm.id',
                    'schoolclass.schoolclass', 'schoolarm.arm', 'schoolsession.session', 'schoolterm.term',
                ])
                ->select([
                    'schoolclass.id as class_id',
                    'schoolclass.schoolclass as class_name',
                    DB::raw('COALESCE(schoolarm.arm, "None") as arm_name'),
                    DB::raw('COALESCE(schoolsession.session, "Unknown") as session_name'),
                    DB::raw('COALESCE(schoolterm.term, "Unknown") as term_name'),
                    DB::raw('COUNT(DISTINCT subject_registration_status.studentid) as student_count'),
                    DB::raw('COUNT(DISTINCT subject_registration_status.subjectclassid) as subject_count'),
                    DB::raw('COALESCE(GROUP_CONCAT(DISTINCT subject.subject ORDER BY subject.subject SEPARATOR ", "), "None") as subjects'),
                    DB::raw('COALESCE(GROUP_CONCAT(DISTINCT teachers.name ORDER BY teachers.name SEPARATOR ", "), "None") as teachers'),
                ]);

            $classes = $query->get();

            return response()->json(['success' => true, 'data' => $classes]);

        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Invalid parameters.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error fetching registered classes', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Restore archived registrations.
     */
    public function restoreRegistration(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'archive_ids'   => ['required', 'array'],
            'archive_ids.*' => ['required', 'integer', 'exists:subject_unregistration_archive,id'],
        ]);

        try {
            DB::beginTransaction();

            $archives = SubjectUnregistrationArchive::whereIn('id', $validated['archive_ids'])
                ->where('status', SubjectUnregistrationArchive::STATUS_ARCHIVED)
                ->get();

            if ($archives->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid archived records found.',
                ], 422);
            }

            $groups = $archives->groupBy(function ($row) {
                return $row->subjectclassid . '_' . $row->termid . '_' . $row->sessionid . '_' . $row->staffid;
            });

            $totalRestored = 0;
            $errors        = [];

            foreach ($groups as $groupKey => $groupArchives) {
                $first      = $groupArchives->first();
                $studentIds = $groupArchives->pluck('studentid')->unique()->toArray();

                $result = $this->processIndividually([
                    'studentid'      => $studentIds,
                    'subjectclassid' => $first->subjectclassid,
                    'staffid'        => $first->staffid,
                    'termid'         => $first->termid,
                    'sessionid'      => $first->sessionid,
                ]);

                if ($result['success'] || ($result['skipped_count'] ?? 0) > 0) {
                    SubjectUnregistrationArchive::whereIn('id', $groupArchives->pluck('id')->toArray())
                        ->update([
                            'status'      => SubjectUnregistrationArchive::STATUS_RESTORED,
                            'actioned_at' => now(),
                            'updated_at'  => now(),
                        ]);

                    $totalRestored += $result['success_count'] ?? 0;
                } else {
                    $errors[] = [
                        'subjectclassid' => $first->subjectclassid,
                        'termid'         => $first->termid,
                        'message'        => $result['message'] ?? 'Unknown error',
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success'       => empty($errors),
                'message'       => "Successfully restored {$totalRestored} registration(s).",
                'total_restored'=> $totalRestored,
                'errors'        => $errors,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Restore registration failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Restore failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Permanently delete a single archive record.
     */
    public function permanentlyDeleteArchive(Request $request, int $archiveId): JsonResponse
    {
        try {
            $archive = SubjectUnregistrationArchive::where('id', $archiveId)
                ->where('status', SubjectUnregistrationArchive::STATUS_ARCHIVED)
                ->firstOrFail();

            $archive->update([
                'status'      => SubjectUnregistrationArchive::STATUS_PERMANENTLY_DELETED,
                'actioned_at' => now(),
            ]);

            $archive->delete();

            return response()->json([
                'success' => true,
                'message' => 'Archive record permanently deleted.',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Record not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Permanent delete failed', ['archive_id' => $archiveId, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Permanently delete multiple archive records.
     */
    public function permanentlyDeleteArchiveBatch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'archive_ids'   => ['required', 'array'],
            'archive_ids.*' => ['required', 'integer'],
        ]);

        try {
            $deleted = SubjectUnregistrationArchive::whereIn('id', $validated['archive_ids'])
                ->where('status', SubjectUnregistrationArchive::STATUS_ARCHIVED)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => "{$deleted} archive record(s) permanently deleted.",
                'deleted' => $deleted,
            ]);

        } catch (\Exception $e) {
            Log::error('Batch permanent delete failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // PRIVATE PROCESSING HELPERS
    // =========================================================================

    private function processIndividually(array $validated): array
    {
        $results      = [];
        $successCount = 0;
        $errors       = [];
        $skippedCount = 0;

        try {
            DB::beginTransaction();

            $subjectclass  = Subjectclass::findOrFail($validated['subjectclassid']);
            $subjectId     = $subjectclass->subjectid;
            $schoolclassId = $subjectclass->schoolclassid;

            $existingRegistrations = SubjectRegistrationStatus::where([
                'subjectclassid' => $validated['subjectclassid'],
                'termid'         => $validated['termid'],
                'sessionid'      => $validated['sessionid'],
            ])->whereIn('studentid', $validated['studentid'])
                ->pluck('studentid')
                ->toArray();

            $studentsToProcess = array_diff($validated['studentid'], $existingRegistrations);
            $skippedCount      = count($existingRegistrations);

            foreach ($existingRegistrations as $existingStudentId) {
                $errors[] = "Student ID {$existingStudentId} is already registered";
            }

            if (empty($studentsToProcess)) {
                DB::rollBack();
                return [
                    'success'       => false,
                    'message'       => 'All students are already registered for this subject.',
                    'errors'        => $errors,
                    'skipped_count' => $skippedCount,
                    'success_count' => 0,
                ];
            }

            foreach ($studentsToProcess as $studentId) {
                try {
                    $record = BroadsheetRecord::firstOrCreate([
                        'student_id'     => $studentId,
                        'subject_id'     => $subjectId,
                        'schoolclass_id' => $schoolclassId,
                        'session_id'     => $validated['sessionid'],
                    ]);

                    $recordmock = BroadsheetRecordMock::firstOrCreate([
                        'student_id'     => $studentId,
                        'subject_id'     => $subjectId,
                        'schoolclass_id' => $schoolclassId,
                        'session_id'     => $validated['sessionid'],
                    ]);

                    $this->createDependentRecords($record->id, $recordmock->id, $studentId, $validated);
                    $successCount++;
                    $results[] = "Successfully registered student ID {$studentId}";

                } catch (\Exception $e) {
                    Log::error("Error processing student {$studentId}", ['error' => $e->getMessage()]);
                    $errors[] = "Failed to register student ID {$studentId}: " . $e->getMessage();
                }
            }

            if ($successCount > 0) {
                DB::commit();
                return [
                    'success'       => true,
                    'message'       => "{$successCount} students registered successfully",
                    'method'        => 'individual',
                    'results'       => $results,
                    'errors'        => $errors,
                    'success_count' => $successCount,
                    'skipped_count' => $skippedCount,
                ];
            }

            DB::rollBack();
            return [
                'success'       => false,
                'message'       => 'No students were registered.',
                'errors'        => $errors,
                'skipped_count' => $skippedCount,
                'success_count' => 0,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Individual processing error', ['error' => $e->getMessage()]);
            return [
                'success'       => false,
                'message'       => 'Processing failed: ' . $e->getMessage(),
                'errors'        => [$e->getMessage()],
                'success_count' => 0,
            ];
        }
    }

    private function processBatch(array $validated): array
    {
        try {
            DB::beginTransaction();

            $subjectclass  = Subjectclass::findOrFail($validated['subjectclassid']);
            $subjectId     = $subjectclass->subjectid;
            $schoolclassId = $subjectclass->schoolclassid;
            $now           = now();

            $existingRegistrations = SubjectRegistrationStatus::where([
                'subjectclassid' => $validated['subjectclassid'],
                'termid'         => $validated['termid'],
                'sessionid'      => $validated['sessionid'],
            ])->whereIn('studentid', $validated['studentid'])
                ->pluck('studentid')
                ->toArray();

            $studentsToProcess = array_diff($validated['studentid'], $existingRegistrations);
            $skippedCount      = count($existingRegistrations);

            if (empty($studentsToProcess)) {
                DB::rollBack();
                return ['success' => false, 'message' => 'All students are already registered.', 'skipped_count' => $skippedCount, 'success_count' => 0];
            }

            $broadsheetRecords     = [];
            $broadsheetRecordsMock = [];

            foreach ($studentsToProcess as $studentId) {
                $broadsheetRecords[]     = ['student_id' => $studentId, 'subject_id' => $subjectId, 'schoolclass_id' => $schoolclassId, 'session_id' => $validated['sessionid'], 'created_at' => $now, 'updated_at' => $now];
                $broadsheetRecordsMock[] = ['student_id' => $studentId, 'subject_id' => $subjectId, 'schoolclass_id' => $schoolclassId, 'session_id' => $validated['sessionid'], 'created_at' => $now, 'updated_at' => $now];
            }

            BroadsheetRecord::insertOrIgnore($broadsheetRecords);
            BroadsheetRecordMock::insertOrIgnore($broadsheetRecordsMock);

            $createdRecords     = BroadsheetRecord::where(['subject_id' => $subjectId, 'schoolclass_id' => $schoolclassId, 'session_id' => $validated['sessionid']])->whereIn('student_id', $studentsToProcess)->get()->keyBy('student_id');
            $createdRecordsMock = BroadsheetRecordMock::where(['subject_id' => $subjectId, 'schoolclass_id' => $schoolclassId, 'session_id' => $validated['sessionid']])->whereIn('student_id', $studentsToProcess)->get()->keyBy('student_id');

            $this->bulkCreateDependentRecords($createdRecords, $createdRecordsMock, $studentsToProcess, $validated, $now);

            DB::commit();

            return ['success' => true, 'message' => count($studentsToProcess) . ' students registered', 'method' => 'batch', 'success_count' => count($studentsToProcess), 'skipped_count' => $skippedCount];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Batch processing failed: ' . $e->getMessage(), 'errors' => [$e->getMessage()], 'success_count' => 0];
        }
    }

    private function processLargeDataset(array $validated): array
    {
        try {
            DB::beginTransaction();

            $subjectclass  = Subjectclass::findOrFail($validated['subjectclassid']);
            $subjectId     = $subjectclass->subjectid;
            $schoolclassId = $subjectclass->schoolclassid;
            $chunkSize     = 200;
            $totalProcessed = 0;
            $totalSkipped   = 0;
            $chunks         = array_chunk($validated['studentid'], $chunkSize);

            foreach ($chunks as $studentChunk) {
                $existingInChunk = SubjectRegistrationStatus::where([
                    'subjectclassid' => $validated['subjectclassid'],
                    'termid'         => $validated['termid'],
                    'sessionid'      => $validated['sessionid'],
                ])->whereIn('studentid', $studentChunk)->pluck('studentid')->toArray();

                $studentsToProcess = array_diff($studentChunk, $existingInChunk);
                $totalSkipped     += count($existingInChunk);

                if (!empty($studentsToProcess)) {
                    $this->processChunk($studentsToProcess, $validated, $subjectId, $schoolclassId);
                    $totalProcessed += count($studentsToProcess);
                }
            }

            DB::commit();
            return ['success' => true, 'message' => "{$totalProcessed} students registered", 'method' => 'large_dataset', 'success_count' => $totalProcessed, 'skipped_count' => $totalSkipped];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Large dataset processing failed: ' . $e->getMessage(), 'errors' => [$e->getMessage()], 'success_count' => 0];
        }
    }

    private function processChunk(array $students, array $validated, int $subjectId, int $schoolclassId): void
    {
        $now = now();

        $broadsheetRecords     = [];
        $broadsheetRecordsMock = [];
        foreach ($students as $studentId) {
            $broadsheetRecords[]     = ['student_id' => $studentId, 'subject_id' => $subjectId, 'schoolclass_id' => $schoolclassId, 'session_id' => $validated['sessionid'], 'created_at' => $now, 'updated_at' => $now];
            $broadsheetRecordsMock[] = ['student_id' => $studentId, 'subject_id' => $subjectId, 'schoolclass_id' => $schoolclassId, 'session_id' => $validated['sessionid'], 'created_at' => $now, 'updated_at' => $now];
        }

        BroadsheetRecord::insertOrIgnore($broadsheetRecords);
        BroadsheetRecordMock::insertOrIgnore($broadsheetRecordsMock);

        $createdRecords     = BroadsheetRecord::where(['subject_id' => $subjectId, 'schoolclass_id' => $schoolclassId, 'session_id' => $validated['sessionid']])->whereIn('student_id', $students)->get()->keyBy('student_id');
        $createdRecordsMock = BroadsheetRecordMock::where(['subject_id' => $subjectId, 'schoolclass_id' => $schoolclassId, 'session_id' => $validated['sessionid']])->whereIn('student_id', $students)->get()->keyBy('student_id');

        $this->bulkCreateDependentRecords($createdRecords, $createdRecordsMock, $students, $validated, $now);
    }

    private function createDependentRecords(int $recordId, int $recordMockId, int $studentId, array $validated): void
    {
        $broadsheet = Broadsheets::firstOrCreate([
            'broadsheet_record_id' => $recordId,
            'term_id'              => $validated['termid'],
            'subjectclass_id'      => $validated['subjectclassid'],
        ], ['staff_id' => $validated['staffid']]);

        BroadsheetsMock::firstOrCreate([
            'broadsheet_records_mock_id' => $recordMockId,
            'term_id'                    => $validated['termid'],
            'subjectclass_id'            => $validated['subjectclassid'],
        ], ['staff_id' => $validated['staffid']]);

        SubjectRegistrationStatus::firstOrCreate([
            'studentid'      => $studentId,
            'subjectclassid' => $validated['subjectclassid'],
            'termid'         => $validated['termid'],
            'sessionid'      => $validated['sessionid'],
            'staffid'        => $validated['staffid'],
        ], ['broadsheetid' => $recordId, 'Status' => 1]);

        StudentSubjectRecord::firstOrCreate([
            'studentId'      => $studentId,
            'subjectclassid' => $validated['subjectclassid'],
            'staffid'        => $validated['staffid'],
            'session'        => $validated['sessionid'],
        ]);

        $this->createAssessmentScores($broadsheet->id, $validated['subjectclassid']);
    }

    private function createAssessmentScores(int $broadsheetId, int $subjectclassId): void
    {
        try {
            $subjectclass = Subjectclass::with(['schoolClass.classcategories'])->find($subjectclassId);
            if (!$subjectclass || !$subjectclass->schoolClass) return;

            $categoryIds = $subjectclass->schoolClass->classcategories->pluck('id');
            if ($categoryIds->isEmpty()) return;

            $assessments = DB::table('assessments')->whereIn('classcategory_id', $categoryIds)->distinct()->get(['id', 'name', 'classcategory_id']);
            if ($assessments->isEmpty()) return;

            foreach ($assessments as $assessment) {
                BroadsheetAssessmentScore::firstOrCreate(['broadsheet_id' => $broadsheetId, 'assessment_id' => $assessment->id], ['score' => 0.00]);

                $subAssessments = DB::table('sub_assessments')->where('assessment_id', $assessment->id)->pluck('id');
                foreach ($subAssessments as $subAssessmentId) {
                    BroadsheetSubAssessmentScore::firstOrCreate(['broadsheet_id' => $broadsheetId, 'sub_assessment_id' => $subAssessmentId, 'assessment_id' => $assessment->id], ['score' => 0.00]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to create assessment scores', ['broadsheet_id' => $broadsheetId, 'error' => $e->getMessage()]);
        }
    }

    private function bulkCreateDependentRecords($createdRecords, $createdRecordsMock, array $students, array $validated, $now): void
    {
        $broadsheets         = [];
        $broadsheetsMock     = [];
        $subjectRegistrations = [];
        $studentSubjectRecords = [];

        foreach ($students as $studentId) {
            $record     = $createdRecords->get($studentId);
            $recordMock = $createdRecordsMock->get($studentId);
            if (!$record || !$recordMock) continue;

            $broadsheets[]          = ['broadsheet_record_id' => $record->id, 'term_id' => $validated['termid'], 'subjectclass_id' => $validated['subjectclassid'], 'staff_id' => $validated['staffid'], 'created_at' => $now, 'updated_at' => $now];
            $broadsheetsMock[]      = ['broadsheet_records_mock_id' => $recordMock->id, 'term_id' => $validated['termid'], 'subjectclass_id' => $validated['subjectclassid'], 'staff_id' => $validated['staffid'], 'created_at' => $now, 'updated_at' => $now];
            $subjectRegistrations[] = ['studentid' => $studentId, 'subjectclassid' => $validated['subjectclassid'], 'staffid' => $validated['staffid'], 'termid' => $validated['termid'], 'sessionid' => $validated['sessionid'], 'broadsheetid' => $record->id, 'Status' => 1, 'created_at' => $now, 'updated_at' => $now];
            $studentSubjectRecords[]= ['studentId' => $studentId, 'subjectclassid' => $validated['subjectclassid'], 'staffid' => $validated['staffid'], 'session' => $validated['sessionid'], 'created_at' => $now, 'updated_at' => $now];
        }

        if (!empty($broadsheets))          Broadsheets::insertOrIgnore($broadsheets);
        if (!empty($broadsheetsMock))      BroadsheetsMock::insertOrIgnore($broadsheetsMock);
        if (!empty($subjectRegistrations)) SubjectRegistrationStatus::insertOrIgnore($subjectRegistrations);
        if (!empty($studentSubjectRecords)) StudentSubjectRecord::insertOrIgnore($studentSubjectRecords);

        $recordIds = collect($students)->map(fn($sid) => $createdRecords->get($sid)?->id)->filter()->toArray();
        if (empty($recordIds)) return;

        $createdBroadsheets = Broadsheets::whereIn('broadsheet_record_id', $recordIds)
            ->where('term_id', $validated['termid'])
            ->where('subjectclass_id', $validated['subjectclassid'])
            ->get();

        $this->bulkCreateAssessmentScoresForBroadsheets($createdBroadsheets, $validated['subjectclassid'], $now);
    }

    private function bulkCreateAssessmentScoresForBroadsheets($broadsheets, int $subjectclassId, $now): void
    {
        try {
            if ($broadsheets->isEmpty()) return;

            $subjectclass = Subjectclass::with(['schoolClass.classcategories'])->find($subjectclassId);
            if (!$subjectclass || !$subjectclass->schoolClass) return;

            $categoryIds = $subjectclass->schoolClass->classcategories->pluck('id');
            if ($categoryIds->isEmpty()) return;

            $assessments = DB::table('assessments')->whereIn('classcategory_id', $categoryIds)->distinct()->get(['id']);
            if ($assessments->isEmpty()) return;

            $assessmentScores    = [];
            $subAssessmentScores = [];

            foreach ($broadsheets as $broadsheet) {
                foreach ($assessments as $assessment) {
                    $assessmentScores[] = ['broadsheet_id' => $broadsheet->id, 'assessment_id' => $assessment->id, 'score' => 0.00, 'created_at' => $now, 'updated_at' => $now];
                }
            }
            if (!empty($assessmentScores)) BroadsheetAssessmentScore::insertOrIgnore($assessmentScores);

            $assessmentIds  = $assessments->pluck('id')->toArray();
            $subAssessments = DB::table('sub_assessments')->whereIn('assessment_id', $assessmentIds)->get(['id', 'assessment_id']);

            foreach ($broadsheets as $broadsheet) {
                foreach ($subAssessments as $subAssessment) {
                    $subAssessmentScores[] = ['broadsheet_id' => $broadsheet->id, 'sub_assessment_id' => $subAssessment->id, 'assessment_id' => $subAssessment->assessment_id, 'score' => 0.00, 'created_at' => $now, 'updated_at' => $now];
                }
            }
            if (!empty($subAssessmentScores)) BroadsheetSubAssessmentScore::insertOrIgnore($subAssessmentScores);

        } catch (\Exception $e) {
            Log::error('Failed to bulk create assessment scores', ['error' => $e->getMessage()]);
        }
    }
}
