<?php

namespace App\Http\Controllers;

use App\Models\ClassTeacher;
use App\Models\Schoolclass;
use App\Models\Schoolterm;
use App\Models\Schoolsession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class ClassTeacherController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View class-teacher|Create class-teacher|Update class-teacher|Delete class-teacher', ['only' => ['index', 'data']]);
        $this->middleware('permission:Create class-teacher', ['only' => ['store']]);
        $this->middleware('permission:Update class-teacher', ['only' => ['update']]);
        $this->middleware('permission:Delete class-teacher', ['only' => ['destroy', 'deleteMultiple']]);
    }

    /**
     * Display the index page.
     */
    public function index(Request $request)
    {
        $pagetitle = "Class Teacher Management";

        $schoolclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.id as id', 'schoolarm.arm as schoolarm', 'schoolclass.schoolclass as schoolclass'])
            ->orderBy('schoolclass.schoolclass')
            ->get();

        $subjectteachers = User::whereHas('roles', function ($q) {
            $q->where('name', '!=', 'Student');
        })->get(['users.id as userid', 'users.name as name', 'users.avatar as avatar']);

        $schoolterms = Schoolterm::all();
        $schoolsessions = Schoolsession::all();

        return view('classteacher.index')
            ->with('schoolclass', $schoolclass)
            ->with('subjectteachers', $subjectteachers)
            ->with('schoolterms', $schoolterms)
            ->with('schoolsessions', $schoolsessions)
            ->with('pagetitle', $pagetitle);
    }

    /**
     * DataTables AJAX data endpoint with UTF-8 handling.
     */
    public function data(Request $request)
    {
        $assignments = ClassTeacher::leftJoin('users', 'users.id', '=', 'classteacher.staffid')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'classteacher.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'classteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'classteacher.sessionid')
            ->select([
                'classteacher.id as id',
                'users.id as staffid',
                'users.name as staffname',
                'users.avatar as avatar',
                'schoolclass.schoolclass as schoolclass',
                'schoolarm.arm as schoolarm',
                'schoolterm.id as termid',
                'schoolterm.term as term',
                'schoolsession.id as sessionid',
                'schoolsession.session as session',
                'classteacher.updated_at as updated_at'
            ]);

        return DataTables::of($assignments)
            ->addIndexColumn()
            ->addColumn('teacher_info', function ($row) {
                // Clean and encode UTF-8 properly
                $staffname = $this->cleanUtf8String($row->staffname ?? 'Unknown');

                // Handle avatar image
                $avatarUrl = null;
                $initials = mb_strtoupper(mb_substr($staffname, 0, 2, 'UTF-8'), 'UTF-8');

                // Check if avatar exists and is not default
                if ($row->avatar && !in_array($row->avatar, ['unnamed.jpg', 'unnamed.png', null, ''])) {
                    // Try multiple possible storage paths
                    $possiblePaths = [
                        'public/staff_avatars/' . $row->avatar,
                        'public/images/staffavatar/' . $row->avatar,
                        'public/staffavatar/' . $row->avatar,
                    ];

                    foreach ($possiblePaths as $path) {
                        if (Storage::exists($path)) {
                            $avatarUrl = Storage::url($path);
                            break;
                        }
                    }

                    // Also check public directory directly
                    if (!$avatarUrl && file_exists(public_path('storage/staff_avatars/' . $row->avatar))) {
                        $avatarUrl = asset('storage/staff_avatars/' . $row->avatar);
                    }
                    if (!$avatarUrl && file_exists(public_path('storage/images/staffavatar/' . $row->avatar))) {
                        $avatarUrl = asset('storage/images/staffavatar/' . $row->avatar);
                    }
                }

                if ($avatarUrl) {
                    $avatarHtml = '<img src="' . e($avatarUrl) . '"
                                   alt="' . e($staffname) . '"
                                   class="teacher-avatar"
                                   style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; cursor: pointer; border: 2px solid #e2e8f0;"
                                   data-teacher-name="' . e($staffname) . '">';
                } else {
                    $avatarHtml = '<div class="avatar-placeholder"
                                   style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600; font-size: 16px; cursor: pointer; border: 2px solid #e2e8f0;"
                                   data-teacher-name="' . e($staffname) . '">' . e($initials) . '</div>';
                }

                return '<div class="d-flex align-items-center gap-2">
                            ' . $avatarHtml . '
                            <div class="fw-semibold text-dark">' . e($staffname) . '</div>
                        </div>';
            })
            ->addColumn('class_info', function ($row) {
                $classText = trim(($this->cleanUtf8String($row->schoolclass ?? '') . ' ' . $this->cleanUtf8String($row->schoolarm ?? '')));
                return '<span class="ts-badge ts-badge-class" style="background: #fef3c7; color: #d97706; display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">'
                    . e($classText) . '</span>';
            })
            ->addColumn('term', function ($row) {
                $termText = $this->cleanUtf8String($row->term ?? '');
                return '<span class="ts-badge ts-badge-term" style="background: #dbeafe; color: #2563eb; display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">'
                    . e($termText) . '</span>';
            })
            ->addColumn('session', function ($row) {
                $sessionText = $this->cleanUtf8String($row->session ?? '');
                return '<span class="ts-badge ts-badge-session" style="background: #ccfbf1; color: #0f766e; display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">'
                    . e($sessionText) . '</span>';
            })
            ->addColumn('formatted_date', function ($row) {
                if (!$row->updated_at) {
                    return 'N/A';
                }
                return '<span class="text-muted small">' . \Carbon\Carbon::parse($row->updated_at)->format('d M Y') . '<br><span style="font-size:10px">' . \Carbon\Carbon::parse($row->updated_at)->format('H:i') . '</span></span>';
            })
            ->addColumn('action', function ($row) {
                $buttons = '<div class="btn-group btn-group-sm">';
                if (auth()->user()->can('Update class-teacher')) {
                    $buttons .= '<button class="btn btn-primary edit-assignment" title="Edit"
                        data-id="' . $row->id . '"
                        data-staffid="' . $row->staffid . '"
                        data-termid="' . $row->termid . '"
                        data-sessionid="' . $row->sessionid . '">
                        <i class="ri-pencil-line"></i>
                    </button>';
                }
                if (auth()->user()->can('Delete class-teacher')) {
                    $title = e($this->cleanUtf8String($row->staffname ?? '') . ' — ' . $this->cleanUtf8String($row->schoolclass ?? '') . ' ' . $this->cleanUtf8String($row->schoolarm ?? ''));
                    $buttons .= '<button class="btn btn-danger delete-assignment" title="Delete"
                        data-id="' . $row->id . '"
                        data-title="' . $title . '">
                        <i class="ri-delete-bin-line"></i>
                    </button>';
                }
                $buttons .= '</div>';
                return $buttons;
            })
            ->rawColumns(['teacher_info', 'class_info', 'term', 'session', 'formatted_date', 'action'])
            ->make(true);
    }

    /**
     * Clean UTF-8 string - removes invalid characters
     */
    private function cleanUtf8String($string)
    {
        if (empty($string)) {
            return '';
        }

        // Remove invalid UTF-8 characters
        $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');

        // Remove control characters except newlines and tabs
        $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $string);

        // Remove emoji and other 4-byte characters if needed (MySQL utf8 doesn't support them)
        // $string = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $string);

        return $string;
    }

    /**
     * Stats endpoint for dashboard cards.
     */
    public function stats()
    {
        try {
            $total = ClassTeacher::count();
            $uniqueTeachers = ClassTeacher::distinct('staffid')->count('staffid');
            $uniqueClasses = ClassTeacher::distinct('schoolclassid')->count('schoolclassid');
            $activeSessions = ClassTeacher::distinct('sessionid')->count('sessionid');

            return response()->json([
                'stats' => [
                    'total' => $total,
                    'unique_teachers' => $uniqueTeachers,
                    'unique_classes' => $uniqueClasses,
                    'active_sessions' => $activeSessions,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Stats error: ' . $e->getMessage());
            return response()->json([
                'stats' => [
                    'total' => 0,
                    'unique_teachers' => 0,
                    'unique_classes' => 0,
                    'active_sessions' => 0,
                ]
            ]);
        }
    }

    /**
     * Store new assignment(s) — supports multi-class assignments.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staffid' => 'required|exists:users,id',
            'schoolclassid' => 'required|array|min:1',
            'schoolclassid.*' => 'exists:schoolclass,id',
            'termid' => 'required|exists:schoolterm,id',
            'sessionid' => 'required|exists:schoolsession,id',
        ], [
            'staffid.required' => 'Please select a teacher!',
            'staffid.exists' => 'Selected teacher does not exist!',
            'schoolclassid.required' => 'Please select at least one class!',
            'schoolclassid.min' => 'Please select at least one class!',
            'schoolclassid.*.exists' => 'Selected class does not exist!',
            'termid.required' => 'Please select a term!',
            'termid.exists' => 'Selected term does not exist!',
            'sessionid.required' => 'Please select a session!',
            'sessionid.exists' => 'Selected session does not exist!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $createdRecords = [];
        $duplicateClasses = [];
        $assignedClasses = [];

        foreach ($request->input('schoolclassid') as $classId) {
            // Check if this teacher is already assigned to this class for the term/session
            $exists = ClassTeacher::where('staffid', $request->input('staffid'))
                ->where('schoolclassid', $classId)
                ->where('termid', $request->input('termid'))
                ->where('sessionid', $request->input('sessionid'))
                ->exists();

            if ($exists) {
                $schoolclass = Schoolclass::find($classId);
                $className = $schoolclass ? ($schoolclass->schoolclass . ' ' . ($schoolclass->arm ?? '')) : $classId;
                $duplicateClasses[] = $this->cleanUtf8String($className);
                continue;
            }

            // Check if this class is already assigned to another teacher for the term/session
            $otherTeacher = ClassTeacher::where('schoolclassid', $classId)
                ->where('termid', $request->input('termid'))
                ->where('sessionid', $request->input('sessionid'))
                ->where('staffid', '!=', $request->input('staffid'))
                ->first();

            if ($otherTeacher) {
                $schoolclass = Schoolclass::find($classId);
                $className = $schoolclass ? ($schoolclass->schoolclass . ' ' . ($schoolclass->arm ?? '')) : $classId;
                $assignedClasses[] = $this->cleanUtf8String($className);
                continue;
            }

            $classteacher = ClassTeacher::create([
                'staffid' => $request->input('staffid'),
                'schoolclassid' => $classId,
                'termid' => $request->input('termid'),
                'sessionid' => $request->input('sessionid'),
            ]);
            $createdRecords[] = $classteacher;
        }

        if (!empty($duplicateClasses) && empty($createdRecords)) {
            return response()->json([
                'success' => false,
                'message' => 'This teacher is already assigned to the following class(es) for the selected term and session: ' . implode(', ', $duplicateClasses)
            ], 422);
        }

        if (!empty($assignedClasses) && empty($createdRecords)) {
            return response()->json([
                'success' => false,
                'message' => 'The following class(es) are already assigned to another teacher for the selected term and session: ' . implode(', ', $assignedClasses)
            ], 422);
        }

        if (empty($createdRecords)) {
            return response()->json([
                'success' => false,
                'message' => 'No new class teachers were added due to duplicates or existing assignments.'
            ], 422);
        }

        Log::info("Class teacher(s) added", ['records' => count($createdRecords)]);
        return response()->json([
            'success' => true,
            'message' => count($createdRecords) . ' class teacher assignment(s) added successfully.',
            'data' => $createdRecords
        ], 201);
    }

    /**
     * Update a teacher's assignments.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'staffid' => 'required|exists:users,id',
            'schoolclassid' => 'required|array|min:1',
            'schoolclassid.*' => 'exists:schoolclass,id',
            'termid' => 'required|exists:schoolterm,id',
            'sessionid' => 'required|exists:schoolsession,id',
        ], [
            'staffid.required' => 'Please select a teacher!',
            'staffid.exists' => 'Selected teacher does not exist!',
            'schoolclassid.required' => 'Please select at least one class!',
            'schoolclassid.min' => 'Please select at least one class!',
            'schoolclassid.*.exists' => 'Selected class does not exist!',
            'termid.required' => 'Please select a term!',
            'termid.exists' => 'Selected term does not exist!',
            'sessionid.required' => 'Please select a session!',
            'sessionid.exists' => 'Selected session does not exist!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $primaryRecord = ClassTeacher::find($id);
        if (!$primaryRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Class Teacher assignment not found.'
            ], 404);
        }

        // Check for duplicate classes
        $duplicateClasses = [];
        $assignedClasses = [];

        foreach ($request->input('schoolclassid') as $classId) {
            $exists = ClassTeacher::where('staffid', $request->input('staffid'))
                ->where('schoolclassid', $classId)
                ->where('termid', $request->input('termid'))
                ->where('sessionid', $request->input('sessionid'))
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                $schoolclass = Schoolclass::find($classId);
                $className = $schoolclass ? ($schoolclass->schoolclass . ' ' . ($schoolclass->arm ?? '')) : $classId;
                $duplicateClasses[] = $this->cleanUtf8String($className);
                continue;
            }

            $otherTeacher = ClassTeacher::where('schoolclassid', $classId)
                ->where('termid', $request->input('termid'))
                ->where('sessionid', $request->input('sessionid'))
                ->where('staffid', '!=', $request->input('staffid'))
                ->first();

            if ($otherTeacher) {
                $schoolclass = Schoolclass::find($classId);
                $className = $schoolclass ? ($schoolclass->schoolclass . ' ' . ($schoolclass->arm ?? '')) : $classId;
                $assignedClasses[] = $this->cleanUtf8String($className);
                continue;
            }
        }

        if (!empty($duplicateClasses)) {
            return response()->json([
                'success' => false,
                'message' => 'This teacher is already assigned to the following class(es) for the selected term and session: ' . implode(', ', $duplicateClasses)
            ], 422);
        }

        if (!empty($assignedClasses)) {
            return response()->json([
                'success' => false,
                'message' => 'The following class(es) are already assigned to another teacher for the selected term and session: ' . implode(', ', $assignedClasses)
            ], 422);
        }

        // Delete all existing assignments for this teacher, term, and session
        ClassTeacher::where('staffid', $primaryRecord->staffid)
            ->where('termid', $primaryRecord->termid)
            ->where('sessionid', $primaryRecord->sessionid)
            ->delete();

        // Create new assignments
        $createdRecords = [];
        foreach ($request->input('schoolclassid') as $classId) {
            $classteacher = ClassTeacher::create([
                'staffid' => $request->input('staffid'),
                'schoolclassid' => $classId,
                'termid' => $request->input('termid'),
                'sessionid' => $request->input('sessionid'),
            ]);
            $createdRecords[] = $classteacher;
        }

        Log::info("Class teacher(s) updated", ['records' => count($createdRecords)]);
        return response()->json([
            'success' => true,
            'message' => count($createdRecords) . ' class teacher assignment(s) updated successfully.',
            'data' => $createdRecords
        ], 200);
    }

    /**
     * Delete a single class teacher assignment.
     */
    public function destroy($id)
    {
        $classteacher = ClassTeacher::find($id);
        if (!$classteacher) {
            return response()->json([
                'success' => false,
                'message' => 'Class Teacher assignment not found.'
            ], 404);
        }

        $classteacher->delete();
        Log::info("Class teacher assignment deleted", ['id' => $id]);
        return response()->json([
            'success' => true,
            'message' => 'Class Teacher assignment deleted successfully.'
        ], 200);
    }

    /**
     * Bulk delete multiple class teacher assignments.
     */
    public function deleteMultiple(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No class teacher assignments selected for deletion.'
            ], 400);
        }

        $deleted = ClassTeacher::whereIn('id', $ids)->delete();
        Log::info("Multiple class teacher assignments deleted", ['count' => $deleted, 'ids' => $ids]);
        return response()->json([
            'success' => true,
            'message' => $deleted . ' class teacher assignment(s) deleted successfully.'
        ], 200);
    }

    /**
     * Get assignments for a specific teacher, term, and session.
     */
    public function assignments($staffId, $termId, $sessionId)
    {
        $classIds = ClassTeacher::where('staffid', $staffId)
            ->where('termid', $termId)
            ->where('sessionid', $sessionId)
            ->pluck('schoolclassid')
            ->toArray();

        return response()->json([
            'success' => true,
            'classIds' => $classIds
        ]);
    }

    /**
     * Show a specific assignment.
     */
    public function show($id)
    {
        $assignment = ClassTeacher::find($id);
        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Assignment not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $assignment
        ]);
    }
}
