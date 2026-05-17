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

    // =========================================================================
    // INDEX
    // =========================================================================

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

        $schoolterms    = Schoolterm::all();
        $schoolsessions = Schoolsession::all();

        return view('classteacher.index')
            ->with('schoolclass',     $schoolclass)
            ->with('subjectteachers', $subjectteachers)
            ->with('schoolterms',     $schoolterms)
            ->with('schoolsessions',  $schoolsessions)
            ->with('pagetitle',       $pagetitle);
    }

    // =========================================================================
    // DATATABLE — AJAX
    // =========================================================================

    public function data(Request $request)
    {
        $assignments = ClassTeacher::leftJoin('users',        'users.id',        '=', 'classteacher.staffid')
            ->leftJoin('schoolclass',   'schoolclass.id',   '=', 'classteacher.schoolclassid')
            ->leftJoin('schoolarm',     'schoolarm.id',     '=', 'schoolclass.arm')
            ->leftJoin('schoolterm',    'schoolterm.id',    '=', 'classteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'classteacher.sessionid')
            ->select([
                'classteacher.id       as id',
                'users.id              as staffid',
                'users.name            as staffname',
                'users.avatar          as avatar',
                'schoolclass.schoolclass as schoolclass',
                'schoolarm.arm         as schoolarm',
                'schoolterm.id         as termid',
                'schoolterm.term       as term',
                'schoolsession.id      as sessionid',
                'schoolsession.session as session',
                'classteacher.updated_at as updated_at',
            ]);

        return DataTables::of($assignments)
            ->addIndexColumn()

            // ── Teacher cell ──────────────────────────────────────────────
            ->addColumn('teacher_info', function ($row) {
                $staffname = $this->cleanUtf8String($row->staffname ?? 'Unknown');

                // Resolve avatar URL — same multi-path logic as original
                $avatarUrl  = null;
                $hasImage   = false;

                $avatar = $row->avatar ?? '';
                $isDefault = in_array($avatar, ['unnamed.jpg', 'unnamed.png', '', null], true);

                if (!$isDefault) {
                    $paths = [
                        'public/staff_avatars/'        . $avatar,
                        'public/images/staffavatar/'   . $avatar,
                        'public/staffavatar/'          . $avatar,
                    ];

                    foreach ($paths as $path) {
                        if (Storage::exists($path)) {
                            $avatarUrl = Storage::url($path);
                            $hasImage  = true;
                            break;
                        }
                    }

                    // Fallback: check public/storage directly
                    if (!$hasImage) {
                        $publicPaths = [
                            public_path('storage/staff_avatars/'      . $avatar),
                            public_path('storage/images/staffavatar/' . $avatar),
                        ];
                        foreach ($publicPaths as $i => $pp) {
                            if (file_exists($pp)) {
                                $avatarUrl = $i === 0
                                    ? asset('storage/staff_avatars/'      . $avatar)
                                    : asset('storage/images/staffavatar/' . $avatar);
                                $hasImage  = true;
                                break;
                            }
                        }
                    }
                }

                // Common data-* attributes used by the blade's JS zoom handler
                $dataAttrs = 'data-staffname="' . e($staffname) . '" '
                           . 'data-image="'     . e($avatarUrl ?? '') . '" '
                           . 'data-has-image="' . ($hasImage ? 'true' : 'false') . '"';

                if ($hasImage) {
                    // Real photo: <img> styled exactly like subjectteacher blade
                    $avatarHtml = '<img src="' . e($avatarUrl) . '" '
                        . 'alt="' . e($staffname) . '" '
                        . 'class="teacher-avatar ct-avatar-trigger" '
                        . $dataAttrs . ' '
                        . 'onerror="this.onerror=null;this.src=\'' . asset('storage/staff_avatars/unnamed.jpg') . '\'">';
                } else {
                    // No photo: initials bubble
                    $words    = preg_split('/\s+/', trim($staffname));
                    $initials = '';
                    foreach (array_slice($words, 0, 2) as $w) {
                        $initials .= mb_strtoupper(mb_substr($w, 0, 1, 'UTF-8'), 'UTF-8');
                    }
                    $avatarHtml = '<div class="avatar-initials ct-avatar-trigger" ' . $dataAttrs . '>'
                        . e($initials)
                        . '</div>';
                }

                return '<div class="d-flex align-items-center gap-2">'
                    . $avatarHtml
                    . '<span class="fw-semibold text-dark">' . e($staffname) . '</span>'
                    . '</div>';
            })

            // ── Class badge ───────────────────────────────────────────────
            ->addColumn('class_info', function ($row) {
                $text = trim(
                    $this->cleanUtf8String($row->schoolclass ?? '')
                    . ' '
                    . $this->cleanUtf8String($row->schoolarm ?? '')
                );
                return '<span class="ct-badge ct-badge-class">' . e($text) . '</span>';
            })

            // ── Term badge ────────────────────────────────────────────────
            ->addColumn('term', function ($row) {
                return '<span class="ct-badge ct-badge-term">'
                    . e($this->cleanUtf8String($row->term ?? ''))
                    . '</span>';
            })

            // ── Session badge ─────────────────────────────────────────────
            ->addColumn('session', function ($row) {
                return '<span class="ct-badge ct-badge-session">'
                    . e($this->cleanUtf8String($row->session ?? ''))
                    . '</span>';
            })

            // ── Date ──────────────────────────────────────────────────────
            ->addColumn('formatted_date', function ($row) {
                if (!$row->updated_at) {
                    return '<span class="text-muted small">—</span>';
                }
                $dt = \Carbon\Carbon::parse($row->updated_at);
                return '<small class="text-muted">'
                    . $dt->format('d M Y')
                    . '</small>';
            })

            // ── Actions ───────────────────────────────────────────────────
            ->addColumn('action', function ($row) {
                $staffname  = $this->cleanUtf8String($row->staffname ?? '');
                $schoolclass = $this->cleanUtf8String($row->schoolclass ?? '');
                $schoolarm   = $this->cleanUtf8String($row->schoolarm ?? '');
                $title = e(trim($staffname . ' — ' . $schoolclass . ' ' . $schoolarm));

                $buttons = '<div class="d-flex gap-1">';

                if (auth()->user()->can('Update class-teacher')) {
                    $buttons .= '<button class="btn btn-sm btn-outline-secondary edit-assignment" title="Edit" '
                        . 'data-id="'        . $row->id        . '" '
                        . 'data-staffid="'   . $row->staffid   . '" '
                        . 'data-termid="'    . $row->termid    . '" '
                        . 'data-sessionid="' . $row->sessionid . '">'
                        . '<i class="ph-pencil"></i>'
                        . '</button>';
                }

                if (auth()->user()->can('Delete class-teacher')) {
                    $buttons .= '<button class="btn btn-sm btn-outline-danger delete-assignment" title="Delete" '
                        . 'data-id="'    . $row->id . '" '
                        . 'data-title="' . $title   . '">'
                        . '<i class="ph-trash"></i>'
                        . '</button>';
                }

                $buttons .= '</div>';
                return $buttons;
            })

            ->rawColumns(['teacher_info', 'class_info', 'term', 'session', 'formatted_date', 'action'])
            ->make(true);
    }

    // =========================================================================
    // STATS
    // =========================================================================

    public function stats()
    {
        try {
            return response()->json([
                'stats' => [
                    'total'           => ClassTeacher::count(),
                    'unique_teachers' => ClassTeacher::distinct('staffid')->count('staffid'),
                    'unique_classes'  => ClassTeacher::distinct('schoolclassid')->count('schoolclassid'),
                    'active_sessions' => ClassTeacher::distinct('sessionid')->count('sessionid'),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('ClassTeacher stats error: ' . $e->getMessage());
            return response()->json([
                'stats' => ['total' => 0, 'unique_teachers' => 0, 'unique_classes' => 0, 'active_sessions' => 0],
            ]);
        }
    }

    // =========================================================================
    // STORE
    // =========================================================================

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staffid'          => 'required|exists:users,id',
            'schoolclassid'    => 'required|array|min:1',
            'schoolclassid.*'  => 'exists:schoolclass,id',
            'termid'           => 'required|exists:schoolterm,id',
            'sessionid'        => 'required|exists:schoolsession,id',
        ], [
            'staffid.required'         => 'Please select a teacher.',
            'staffid.exists'           => 'Selected teacher does not exist.',
            'schoolclassid.required'   => 'Please select at least one class.',
            'schoolclassid.min'        => 'Please select at least one class.',
            'schoolclassid.*.exists'   => 'One or more selected classes do not exist.',
            'termid.required'          => 'Please select a term.',
            'termid.exists'            => 'Selected term does not exist.',
            'sessionid.required'       => 'Please select a session.',
            'sessionid.exists'         => 'Selected session does not exist.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $createdRecords  = [];
        $duplicateNames  = [];
        $conflictNames   = [];

        foreach ($request->input('schoolclassid') as $classId) {

            // Duplicate: same teacher already assigned to this class/term/session
            $dup = ClassTeacher::where('staffid',      $request->input('staffid'))
                ->where('schoolclassid', $classId)
                ->where('termid',        $request->input('termid'))
                ->where('sessionid',     $request->input('sessionid'))
                ->exists();

            if ($dup) {
                $sc = Schoolclass::find($classId);
                $duplicateNames[] = $sc ? $this->cleanUtf8String($sc->schoolclass) : $classId;
                continue;
            }

            // Conflict: class already assigned to a *different* teacher for same term/session
            $conflict = ClassTeacher::where('schoolclassid', $classId)
                ->where('termid',    $request->input('termid'))
                ->where('sessionid', $request->input('sessionid'))
                ->where('staffid',   '!=', $request->input('staffid'))
                ->exists();

            if ($conflict) {
                $sc = Schoolclass::find($classId);
                $conflictNames[] = $sc ? $this->cleanUtf8String($sc->schoolclass) : $classId;
                continue;
            }

            $createdRecords[] = ClassTeacher::create([
                'staffid'      => $request->input('staffid'),
                'schoolclassid'=> $classId,
                'termid'       => $request->input('termid'),
                'sessionid'    => $request->input('sessionid'),
            ]);
        }

        if (empty($createdRecords)) {
            $msg = 'No assignments created.';
            if (!empty($duplicateNames)) {
                $msg = 'Already assigned: ' . implode(', ', $duplicateNames) . '.';
            } elseif (!empty($conflictNames)) {
                $msg = 'These classes already have another teacher: ' . implode(', ', $conflictNames) . '.';
            }
            return response()->json(['success' => false, 'message' => $msg], 422);
        }

        $msg = count($createdRecords) . ' class teacher assignment(s) added successfully.';
        if (!empty($duplicateNames) || !empty($conflictNames)) {
            $skipped = array_merge($duplicateNames, $conflictNames);
            $msg .= ' Skipped: ' . implode(', ', $skipped) . '.';
        }

        Log::info('ClassTeacher store', ['created' => count($createdRecords)]);
        return response()->json(['success' => true, 'message' => $msg, 'data' => $createdRecords], 201);
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'staffid'          => 'required|exists:users,id',
            'schoolclassid'    => 'required|array|min:1',
            'schoolclassid.*'  => 'exists:schoolclass,id',
            'termid'           => 'required|exists:schoolterm,id',
            'sessionid'        => 'required|exists:schoolsession,id',
        ], [
            'staffid.required'         => 'Please select a teacher.',
            'staffid.exists'           => 'Selected teacher does not exist.',
            'schoolclassid.required'   => 'Please select at least one class.',
            'schoolclassid.min'        => 'Please select at least one class.',
            'schoolclassid.*.exists'   => 'One or more selected classes do not exist.',
            'termid.required'          => 'Please select a term.',
            'termid.exists'            => 'Selected term does not exist.',
            'sessionid.required'       => 'Please select a session.',
            'sessionid.exists'         => 'Selected session does not exist.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $primary = ClassTeacher::find($id);
        if (!$primary) {
            return response()->json(['success' => false, 'message' => 'Assignment not found.'], 404);
        }

        // Check each incoming class for conflicts with OTHER teachers
        $conflictNames = [];
        foreach ($request->input('schoolclassid') as $classId) {
            $conflict = ClassTeacher::where('schoolclassid', $classId)
                ->where('termid',    $request->input('termid'))
                ->where('sessionid', $request->input('sessionid'))
                ->where('staffid',   '!=', $request->input('staffid'))
                ->exists();

            if ($conflict) {
                $sc = Schoolclass::find($classId);
                $conflictNames[] = $sc ? $this->cleanUtf8String($sc->schoolclass) : $classId;
            }
        }

        if (!empty($conflictNames)) {
            return response()->json([
                'success' => false,
                'message' => 'The following classes already have another teacher for this term/session: '
                    . implode(', ', $conflictNames) . '.',
            ], 422);
        }

        // Remove ALL existing assignments for the OLD teacher + OLD term + OLD session,
        // so we cleanly replace them with the new selection.
        ClassTeacher::where('staffid',   $primary->staffid)
            ->where('termid',    $primary->termid)
            ->where('sessionid', $primary->sessionid)
            ->delete();

        // Re-create
        $createdRecords = [];
        foreach ($request->input('schoolclassid') as $classId) {
            $createdRecords[] = ClassTeacher::create([
                'staffid'       => $request->input('staffid'),
                'schoolclassid' => $classId,
                'termid'        => $request->input('termid'),
                'sessionid'     => $request->input('sessionid'),
            ]);
        }

        Log::info('ClassTeacher update', ['id' => $id, 'new_records' => count($createdRecords)]);
        return response()->json([
            'success' => true,
            'message' => count($createdRecords) . ' class teacher assignment(s) updated successfully.',
            'data'    => $createdRecords,
        ], 200);
    }

    // =========================================================================
    // DESTROY (single)
    // =========================================================================

    public function destroy($id)
    {
        $ct = ClassTeacher::find($id);
        if (!$ct) {
            return response()->json(['success' => false, 'message' => 'Assignment not found.'], 404);
        }

        $ct->delete();
        Log::info('ClassTeacher deleted', ['id' => $id]);
        return response()->json(['success' => true, 'message' => 'Assignment deleted successfully.'], 200);
    }

    // =========================================================================
    // BULK DESTROY
    // =========================================================================

    public function deleteMultiple(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No assignments selected.'], 400);
        }

        $deleted = ClassTeacher::whereIn('id', $ids)->delete();
        Log::info('ClassTeacher bulk delete', ['count' => $deleted]);
        return response()->json([
            'success' => true,
            'message' => $deleted . ' assignment(s) deleted successfully.',
        ], 200);
    }

    // =========================================================================
    // ASSIGNMENTS (for edit modal pre-load)
    // =========================================================================

    public function assignments($staffId, $termId, $sessionId)
    {
        $classIds = ClassTeacher::where('staffid',   $staffId)
            ->where('termid',    $termId)
            ->where('sessionid', $sessionId)
            ->pluck('schoolclassid')
            ->toArray();

        return response()->json(['success' => true, 'classIds' => $classIds]);
    }

    // =========================================================================
    // SHOW
    // =========================================================================

    public function show($id)
    {
        $ct = ClassTeacher::find($id);
        if (!$ct) {
            return response()->json(['success' => false, 'message' => 'Assignment not found.'], 404);
        }
        return response()->json(['success' => true, 'data' => $ct]);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function cleanUtf8String($string)
    {
        if (empty($string)) {
            return '';
        }
        $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $string);
        return $string;
    }
}
