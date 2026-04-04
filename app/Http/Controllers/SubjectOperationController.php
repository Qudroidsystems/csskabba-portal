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

    // =========================================================================
    // INDEX
    // =========================================================================

    public function index(Request $request): \Illuminate\View\View
    {
        $pagetitle = "Subject Operation Management";

        $schoolclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.id as id', 'schoolarm.arm as schoolarm', 'schoolclass.schoolclass as schoolclass'])
            ->orderBy('schoolclass.schoolclass')
            ->get();
        $schoolterms    = Schoolterm::all();
        $schoolsessions = Schoolsession::all();

        $staffs = User::whereHas('roles', function ($q) {
            $q->where('name', '!=', 'Student');
        })->get(['users.id as userid', 'users.name as name', 'users.avatar as avatar']);

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

    // =========================================================================
    // REGISTERED CLASSES (with teacher names and badges)
    // =========================================================================

    public function getRegisteredClasses(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'class_id'   => ['required', 'integer', 'exists:schoolclass,id'],
                'session_id' => ['required', 'integer', 'exists:schoolsession,id'],
                'term_id'    => ['nullable', 'integer', 'exists:schoolterm,id'],
            ]);

            DB::statement('SET SESSION group_concat_max_len = 1000000');

            // Get all terms for this session
            $terms = Schoolterm::all();

            $results = [];

            foreach ($terms as $term) {
                // Get subject teachers for this class, session, and term
                $subjectTeachers = SubjectTeacher::query()
                    ->join('subjectclass', 'subjectclass.subjectteacherid', '=', 'subjectteacher.id')
                    ->join('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                    ->join('users', 'users.id', '=', 'subjectteacher.staffid')
                    ->where('subjectclass.schoolclassid', $validated['class_id'])
                    ->where('subjectteacher.sessionid', $validated['session_id'])
                    ->where('subjectteacher.termid', $term->id)
                    ->select([
                        'subjectteacher.id',
                        'subjectteacher.staffid',
                        'users.name as teacher_name',
                        'subject.id as subject_id',
                        'subject.subject as subject_name',
                        'subjectclass.id as subjectclass_id',
                    ])
                    ->get();

                if ($subjectTeachers->isEmpty()) {
                    continue;
                }

                // Get registered students count for this term
                $studentCount = SubjectRegistrationStatus::query()
                    ->whereIn('subjectclassid', $subjectTeachers->pluck('subjectclass_id'))
                    ->where('sessionid', $validated['session_id'])
                    ->where('termid', $term->id)
                    ->distinct('studentid')
                    ->count('studentid');

                $subjects = $subjectTeachers->pluck('subject_name')->unique()->filter()->implode(', ');
                $teachers = $subjectTeachers->pluck('teacher_name')->unique()->filter()->implode(', ');
                $subjectCount = $subjectTeachers->pluck('subject_id')->unique()->count();

                $results[] = (object)[
                    'class_id' => $validated['class_id'],
                    'class_name' => Schoolclass::find($validated['class_id'])->schoolclass ?? 'Unknown',
                    'arm_name' => Schoolclass::find($validated['class_id'])->armName->arm ?? 'None',
                    'session_name' => Schoolsession::find($validated['session_id'])->session ?? 'Unknown',
                    'term_name' => $term->term,
                    'student_count' => $studentCount,
                    'subject_count' => $subjectCount,
                    'subjects' => $subjects ?: 'None',
                    'teachers' => $teachers ?: 'None',
                    'teacher_count' => $subjectTeachers->pluck('staffid')->unique()->count(),
                ];
            }

            return response()->json(['success' => true, 'data' => $results]);

        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Invalid parameters.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error fetching registered classes', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // GET ARCHIVED REGISTRATIONS
    // =========================================================================

    public function getArchivedRegistrations(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'class_id'   => ['required', 'integer', 'exists:schoolclass,id'],
            'session_id' => ['required', 'integer', 'exists:schoolsession,id'],
            'term_id'    => ['nullable', 'integer', 'exists:schoolterm,id'],
            'search'     => ['nullable', 'string', 'max:255'],
            'per_page'   => ['nullable', 'integer', 'in:20,50,100,150'],
        ]);

        try {
            $perPage = $request->input('per_page', 50);

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

    // =========================================================================
    // BATCH REGISTER
    // =========================================================================

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
                    ];
                }
                $results[] = $response;
            }

            DB::commit();

            return response()->json([
                'success'       => empty($errors),
                'message'       => $successCount . ' student(s) registered successfully.',
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

    // =========================================================================
    // DESTROY (UNREGISTER)
    // =========================================================================

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

                $results[] = [
                    'subjectclassid'       => $subjectclassid,
                    'termid'               => $termid,
                    'message'              => 'Successfully unregistered ' . count($studentsToProcess) . ' students',
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
                'message'       => "Successfully unregistered {$successCount} student(s).",
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

    // =========================================================================
    // RESTORE REGISTRATION
    // =========================================================================

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

    // =========================================================================
    // PERMANENTLY DELETE ARCHIVE (BATCH)
    // =========================================================================

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

            if (empty($studentsToProcess)) {
                DB::rollBack();
                return [
                    'success'       => false,
                    'message'       => 'All students are already registered.',
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
}
