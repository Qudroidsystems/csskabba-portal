<?php

namespace App\Http\Controllers;

use App\Models\Schoolclass;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Student;
use App\Models\Subjectclass;
use App\Models\SubjectRegistrationStatus;
use App\Models\SubjectUnregistrationArchive;
use App\Models\SubjectTeacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SubjectOperationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View subject registration', ['only' => ['index', 'getRegisteredClasses', 'subjectinfo', 'getArchivedRegistrations']]);
        $this->middleware('permission:Create subject registration', ['only' => ['store', 'batchRegister']]);
        $this->middleware('permission:Delete subject registration', ['only' => ['destroy', 'permanentlyDeleteArchive', 'permanentlyDeleteArchiveBatch']]);
        $this->middleware('permission:Restore subject registration', ['only' => ['restoreRegistration']]);
    }

    /**
     * Display the subject registration interface
     */
    public function index(Request $request)
    {
        $pagetitle = "Subject Registration";

        // Get all school classes with arms
        $schoolclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->selectRaw("schoolclass.id, CONCAT(schoolclass.schoolclass, ' - ', schoolarm.arm) as class_display, schoolclass.schoolclass, schoolarm.arm")
            ->orderBy('schoolclass.schoolclass')
            ->get();

        // Get all school sessions
        $schoolsessions = Schoolsession::select('id', 'session')->get();

        // Get all school terms
        $schoolterms = Schoolterm::select('id', 'term')->get();

        // Get students with pagination
        $students = Student::leftJoin('studentclass', 'studentclass.studentId', '=', 'studentRegistration.id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->select([
                'studentRegistration.*',
                'studentclass.schoolclassid',
                'studentclass.termid',
                'studentclass.sessionid',
                'schoolclass.schoolclass',
                'schoolarm.arm',
                'studentpicture.picture'
            ])
            ->when($request->class_id && $request->class_id !== 'ALL', function($q) use ($request) {
                $q->where('studentclass.schoolclassid', $request->class_id);
            })
            ->when($request->session_id && $request->session_id !== 'ALL', function($q) use ($request) {
                $q->where('studentclass.sessionid', $request->session_id);
            })
            ->when($request->search, function($q) use ($request) {
                $q->where(function($query) use ($request) {
                    $query->where('studentRegistration.firstname', 'LIKE', "%{$request->search}%")
                          ->orWhere('studentRegistration.lastname', 'LIKE', "%{$request->search}%")
                          ->orWhere('studentRegistration.admissionNo', 'LIKE', "%{$request->search}%");
                });
            })
            ->when($request->gender && $request->gender !== 'ALL', function($q) use ($request) {
                $q->where('studentRegistration.gender', $request->gender);
            })
            ->when($request->admissionno && $request->admissionno !== 'ALL', function($q) use ($request) {
                $q->where('studentRegistration.admissionNo', $request->admissionno);
            })
            ->orderBy('studentRegistration.created_at', 'desc')
            ->paginate(15);

        // Get subject teachers (for display) - Using SubjectTeacher model
        $subjectTeachers = SubjectTeacher::with(['subject', 'user', 'subjectclass'])
            ->whereHas('subjectclass', function($q) {
                $q->where('status', 'Active');
            })
            ->get()
            ->map(function($subjectTeacher) {
                // Get the subjectclass record
                $subjectClass = $subjectTeacher->subjectclass;

                return (object)[
                    'subjectclassid' => $subjectClass ? $subjectClass->id : null,
                    'subjectname' => $subjectTeacher->subject->subjectname ?? '',
                    'staffname' => $subjectTeacher->user->name ?? 'Not Assigned',
                    'userid' => $subjectTeacher->staffid,
                    'termid' => $subjectTeacher->termid,
                ];
            })
            ->filter(); // Remove null entries

        return view('subjectoperation.index', compact(
            'pagetitle',
            'schoolclass',
            'schoolsessions',
            'schoolterms',
            'students',
            'subjectTeachers'
        ));
    }

    /**
     * Show method - required by resource route
     */
    public function show($id = null)
    {
        return redirect()->route('subjects.index');
    }

    /**
     * Create method - required by resource route
     */
    public function create()
    {
        return redirect()->route('subjects.index');
    }

    /**
     * Edit method - required by resource route
     */
    public function edit($id)
    {
        return redirect()->route('subjects.index');
    }

    /**
     * Update method - required by resource route
     */
    public function update(Request $request, $id)
    {
        return redirect()->route('subjects.index');
    }

    /**
     * Store a single subject registration
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'student_id' => 'required|exists:studentRegistration,id',
                'subjectclassid' => 'required|exists:subjectclass,id',
                'termid' => 'required|exists:schoolterm,id',
                'sessionid' => 'required|exists:schoolsession,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if already registered
            $exists = SubjectRegistrationStatus::where([
                'studentId' => $request->student_id,
                'subjectclassid' => $request->subjectclassid,
                'termid' => $request->termid,
                'sessionid' => $request->sessionid,
            ])->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student is already registered for this subject'
                ], 400);
            }

            $registration = SubjectRegistrationStatus::create([
                'studentId' => $request->student_id,
                'subjectclassid' => $request->subjectclassid,
                'termid' => $request->termid,
                'sessionid' => $request->sessionid,
                'status' => 'registered',
                'registered_by' => auth()->id(),
                'registered_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subject registered successfully',
                'data' => $registration
            ]);

        } catch (\Exception $e) {
            Log::error('Error storing subject registration: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to register subject: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batch register students for multiple subjects
     */
    public function batchRegister(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'studentids' => 'required|array',
                'studentids.*' => 'exists:studentRegistration,id',
                'subjectclasses' => 'required|array',
                'subjectclasses.*.subjectclassid' => 'required|exists:subjectclass,id',
                'subjectclasses.*.staffid' => 'nullable|exists:users,id',
                'subjectclasses.*.termid' => 'required|exists:schoolterm,id',
                'sessionid' => 'required|exists:schoolsession,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($request->studentids as $studentId) {
                foreach ($request->subjectclasses as $subjectClass) {
                    try {
                        // Check if already registered
                        $exists = SubjectRegistrationStatus::where([
                            'studentId' => $studentId,
                            'subjectclassid' => $subjectClass['subjectclassid'],
                            'termid' => $subjectClass['termid'],
                            'sessionid' => $request->sessionid,
                        ])->exists();

                        if (!$exists) {
                            SubjectRegistrationStatus::create([
                                'studentId' => $studentId,
                                'subjectclassid' => $subjectClass['subjectclassid'],
                                'termid' => $subjectClass['termid'],
                                'sessionid' => $request->sessionid,
                                'status' => 'registered',
                                'registered_by' => auth()->id(),
                                'registered_at' => now(),
                            ]);
                            $successCount++;
                        } else {
                            $failedCount++;
                            $errors[] = "Student already registered for subject";
                        }
                    } catch (\Exception $e) {
                        $failedCount++;
                        $errors[] = "Error: " . $e->getMessage();
                    }
                }
            }

            DB::commit();

            // Store the last used class id in session
            if ($request->has('class_id')) {
                session(['last_class_id' => $request->class_id]);
            }

            return response()->json([
                'success' => true,
                'message' => "Registered {$successCount} subject(s) successfully. Failed: {$failedCount}",
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in batch registration: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete batch registration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unregister/destroy subject registrations and move to archive
     */
    public function destroy(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'studentids' => 'required|array',
                'studentids.*' => 'exists:studentRegistration,id',
                'subjectclasses' => 'required|array',
                'subjectclasses.*.subjectclassid' => 'required|exists:subjectclass,id',
                'subjectclasses.*.termid' => 'required|exists:schoolterm,id',
                'sessionid' => 'required|exists:schoolsession,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($request->studentids as $studentId) {
                foreach ($request->subjectclasses as $subjectClass) {
                    try {
                        // Find the registration
                        $registration = SubjectRegistrationStatus::where([
                            'studentId' => $studentId,
                            'subjectclassid' => $subjectClass['subjectclassid'],
                            'termid' => $subjectClass['termid'],
                            'sessionid' => $request->sessionid,
                        ])->first();

                        if ($registration) {
                            // Get subject class details for archive
                            $subjectClassModel = Subjectclass::with(['subject', 'schoolClass'])->find($subjectClass['subjectclassid']);
                            $student = Student::find($studentId);

                            // Create archive record
                            SubjectUnregistrationArchive::create([
                                'studentid' => $studentId,
                                'subjectclassid' => $subjectClass['subjectclassid'],
                                'staffid' => $subjectClass['staffid'] ?? null,
                                'termid' => $subjectClass['termid'],
                                'sessionid' => $request->sessionid,
                                'subjectid' => $subjectClassModel->subjectid ?? null,
                                'schoolclassid' => $subjectClassModel->schoolclassid ?? null,
                                'unregistered_by' => auth()->id(),
                                'status' => SubjectUnregistrationArchive::STATUS_ARCHIVED,
                                'unregistered_at' => now(),
                                'actioned_at' => now(),
                            ]);

                            // Delete the registration
                            $registration->delete();
                            $successCount++;
                        } else {
                            $failedCount++;
                            $errors[] = "Registration not found";
                        }
                    } catch (\Exception $e) {
                        $failedCount++;
                        $errors[] = "Error: " . $e->getMessage();
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Unregistered {$successCount} subject(s) successfully. Failed: {$failedCount}",
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in destroy: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete unregistration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subject information
     */
    public function subjectinfo($id, $schoolclassid, $termid, $sessionid)
    {
        try {
            $subjectClass = Subjectclass::with(['subject', 'subjectTeacher.user', 'schoolClass.armRelation'])
                ->where('id', $id)
                ->where('schoolclassid', $schoolclassid)
                ->first();

            if (!$subjectClass) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subject not found'
                ], 404);
            }

            // Get teacher name through subjectTeacher relationship
            $teacherName = 'Not Assigned';
            if ($subjectClass->subjectTeacher && $subjectClass->subjectTeacher->user) {
                $teacherName = $subjectClass->subjectTeacher->user->name;
            }

            // Get registered students count
            $registeredCount = SubjectRegistrationStatus::where([
                'subjectclassid' => $id,
                'termid' => $termid,
                'sessionid' => $sessionid,
            ])->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'subject_name' => $subjectClass->subject->subjectname ?? '',
                    'subject_code' => $subjectClass->subject->subjectcode ?? '',
                    'teacher_name' => $teacherName,
                    'class_name' => $subjectClass->schoolClass->schoolclass ?? '',
                    'arm_name' => $subjectClass->schoolClass->armRelation->arm ?? '',
                    'registered_students' => $registeredCount,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting subject info: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get subject information: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subject teachers
     */
    public function getSubjectTeachers(Request $request)
    {
        try {
            $query = SubjectTeacher::with(['subject', 'user', 'subjectclass'])
                ->whereHas('subjectclass', function($q) {
                    $q->where('status', 'Active');
                });

            if ($request->class_id) {
                $query->whereHas('subjectclass', function($q) use ($request) {
                    $q->where('schoolclassid', $request->class_id);
                });
            }

            if ($request->term_id) {
                $query->where('termid', $request->term_id);
            }

            $subjectTeachers = $query->get()->map(function($subjectTeacher) {
                $subjectClass = $subjectTeacher->subjectclass;

                return [
                    'id' => $subjectClass ? $subjectClass->id : null,
                    'subject_name' => $subjectTeacher->subject->subjectname ?? '',
                    'subject_code' => $subjectTeacher->subject->subjectcode ?? '',
                    'teacher_name' => $subjectTeacher->user->name ?? 'Not Assigned',
                    'teacher_id' => $subjectTeacher->staffid,
                    'class_name' => $subjectClass && $subjectClass->schoolClass ? $subjectClass->schoolClass->schoolclass : '',
                    'arm_name' => $subjectClass && $subjectClass->schoolClass && $subjectClass->schoolClass->armRelation ? $subjectClass->schoolClass->armRelation->arm : '',
                    'term_id' => $subjectTeacher->termid,
                ];
            })->filter();

            return response()->json([
                'success' => true,
                'data' => $subjectTeachers
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting subject teachers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get subject teachers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get registered classes with teacher information
     */
    public function getRegisteredClasses(Request $request)
    {
        try {
            $classId = $request->get('class_id');
            $sessionId = $request->get('session_id');
            $includeTeachers = $request->boolean('include_teachers', true);

            if (!$classId || $classId === 'ALL' || !$sessionId || $sessionId === 'ALL') {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            // Get all registrations grouped by term and subject
            $registrations = SubjectRegistrationStatus::with([
                'subjectClass' => function($q) use ($classId) {
                    $q->where('schoolclassid', $classId);
                },
                'subjectClass.subject',
                'subjectClass.subjectTeacher.user',
                'subjectClass.schoolClass.armRelation',
                'term'
            ])
            ->where('sessionid', $sessionId)
            ->whereHas('subjectClass', function($q) use ($classId) {
                $q->where('schoolclassid', $classId);
            })
            ->get();

            // Group by term and subject
            $grouped = $registrations->groupBy('termid')->map(function($termRegs, $termId) use ($includeTeachers) {
                $term = Schoolterm::find($termId);

                // Group by subject to avoid duplicates
                $subjects = $termRegs->groupBy('subjectclassid')->map(function($subjectRegs, $subjectClassId) use ($includeTeachers) {
                    $firstReg = $subjectRegs->first();
                    $teacherName = 'Not Assigned';

                    if ($includeTeachers && $firstReg->subjectClass && $firstReg->subjectClass->subjectTeacher) {
                        $teacherName = $firstReg->subjectClass->subjectTeacher->user->name ?? 'Not Assigned';
                    }

                    return [
                        'subject_id' => $subjectClassId,
                        'subject_name' => $firstReg->subjectClass->subject->subjectname ?? 'Unknown',
                        'subject_code' => $firstReg->subjectClass->subject->subjectcode ?? '',
                        'student_count' => $subjectRegs->count(),
                        'teacher_name' => $teacherName,
                    ];
                })->values();

                return [
                    'term_id' => $termId,
                    'term_name' => $term->term ?? 'Unknown',
                    'subjects' => $subjects,
                    'subject_count' => $subjects->count(),
                    'total_students' => $termRegs->count(),
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => $grouped
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting registered classes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch registered classes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get archived registrations from SubjectUnregistrationArchive
     */
    public function getArchivedRegistrations(Request $request)
    {
        try {
            $classId = $request->get('class_id');
            $sessionId = $request->get('session_id');
            $termId = $request->get('term_id');
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 50);
            $search = $request->get('search', '');

            if (!$classId || $classId === 'ALL' || !$sessionId || $sessionId === 'ALL') {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'meta' => [
                        'current_page' => 1,
                        'last_page' => 1,
                        'per_page' => $perPage,
                        'total' => 0,
                    ]
                ]);
            }

            $query = SubjectUnregistrationArchive::with([
                'student',
                'subjectclass.subject',
                'subjectclass.subjectTeacher.user',
                'staff',
                'term',
                'session',
                'unregisteredBy'
            ])
            ->where('schoolclassid', $classId)
            ->where('sessionid', $sessionId)
            ->where('status', SubjectUnregistrationArchive::STATUS_ARCHIVED);

            if ($termId && $termId !== '') {
                $query->where('termid', $termId);
            }

            if ($search) {
                $query->whereHas('student', function($q) use ($search) {
                    $q->where('firstname', 'LIKE', "%{$search}%")
                      ->orWhere('lastname', 'LIKE', "%{$search}%")
                      ->orWhere('admissionNo', 'LIKE', "%{$search}%");
                });
            }

            $archives = $query->orderBy('unregistered_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            $formattedData = $archives->map(function($archive) {
                // Get teacher name through the relationship chain
                $teacherName = 'System';
                if ($archive->subjectclass && $archive->subjectclass->subjectTeacher && $archive->subjectclass->subjectTeacher->user) {
                    $teacherName = $archive->subjectclass->subjectTeacher->user->name;
                } elseif ($archive->staff) {
                    $teacherName = $archive->staff->name;
                } elseif ($archive->unregisteredBy) {
                    $teacherName = $archive->unregisteredBy->name;
                }

                return [
                    'archive_id' => $archive->id,
                    'student_id' => $archive->studentid,
                    'firstname' => $archive->student->firstname ?? '',
                    'lastname' => $archive->student->lastname ?? '',
                    'admissionno' => $archive->student->admissionNo ?? '',
                    'subjectname' => $archive->subjectclass->subject->subjectname ?? '',
                    'subjectcode' => $archive->subjectclass->subject->subjectcode ?? '',
                    'staffname' => $teacherName,
                    'termname' => $archive->term->term ?? '',
                    'unregistered_at' => $archive->unregistered_at,
                    'unregistered_by_name' => $archive->unregisteredBy->name ?? 'System',
                    'picture' => $archive->student->picture->picture ?? null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedData,
                'meta' => [
                    'current_page' => $archives->currentPage(),
                    'last_page' => $archives->lastPage(),
                    'per_page' => $archives->perPage(),
                    'total' => $archives->total(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching archived registrations: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch archived registrations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a registration from archive
     */
    public function restoreRegistration(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'archive_ids' => 'required|array',
                'archive_ids.*' => 'exists:subject_unregistration_archive,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $restoredCount = 0;
            $errors = [];

            foreach ($request->archive_ids as $archiveId) {
                try {
                    $archive = SubjectUnregistrationArchive::findOrFail($archiveId);

                    // Check if registration already exists
                    $exists = SubjectRegistrationStatus::where([
                        'studentId' => $archive->studentid,
                        'subjectclassid' => $archive->subjectclassid,
                        'sessionid' => $archive->sessionid,
                        'termid' => $archive->termid,
                    ])->exists();

                    if (!$exists) {
                        // Restore the registration
                        SubjectRegistrationStatus::create([
                            'studentId' => $archive->studentid,
                            'subjectclassid' => $archive->subjectclassid,
                            'sessionid' => $archive->sessionid,
                            'termid' => $archive->termid,
                            'status' => 'registered',
                            'registered_by' => auth()->id(),
                            'registered_at' => now(),
                        ]);

                        $restoredCount++;
                    }

                    // Update archive status
                    $archive->status = SubjectUnregistrationArchive::STATUS_RESTORED;
                    $archive->actioned_at = now();
                    $archive->save();

                } catch (\Exception $e) {
                    $errors[] = "Archive ID {$archiveId}: " . $e->getMessage();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully restored {$restoredCount} registration(s)",
                'total_restored' => $restoredCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error restoring registration: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Permanently delete archive records
     */
    public function permanentlyDeleteArchiveBatch(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'archive_ids' => 'required|array',
                'archive_ids.*' => 'exists:subject_unregistration_archive,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $deletedCount = 0;
            foreach ($request->archive_ids as $archiveId) {
                $archive = SubjectUnregistrationArchive::find($archiveId);
                if ($archive) {
                    $archive->status = SubjectUnregistrationArchive::STATUS_PERMANENTLY_DELETED;
                    $archive->actioned_at = now();
                    $archive->save();
                    $archive->delete(); // Hard delete
                    $deletedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} archive record(s)",
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting archive records: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Permanently delete a single archive record
     */
    public function permanentlyDeleteArchive($archiveId)
    {
        try {
            DB::beginTransaction();

            $archive = SubjectUnregistrationArchive::findOrFail($archiveId);
            $archive->status = SubjectUnregistrationArchive::STATUS_PERMANENTLY_DELETED;
            $archive->actioned_at = now();
            $archive->save();
            $archive->delete(); // Hard delete

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Archive record deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting archive record: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get registered classes for display (alias)
     */
    public function registeredClasses(Request $request)
    {
        return $this->getRegisteredClasses($request);
    }
}
