<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiblingGroup;
use App\Models\Student;
use App\Models\DiscountAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class SiblingGroupController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View sibling groups', ['only' => ['index', 'show']]);
        $this->middleware('permission:Create sibling group', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update sibling group', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete sibling group', ['only' => ['destroy']]);
        $this->middleware('permission:Apply sibling discount', ['only' => ['applyDiscount']]);
    }

    public function index(Request $request)
    {
        $pagetitle = 'Sibling Groups Management';

        if ($request->ajax() || $request->wantsJson()) {
            $groups = SiblingGroup::with(['students', 'discountAssignments'])
                ->orderBy('created_at', 'desc')
                ->get();

            $totalSavings = DiscountAssignment::whereNotNull('sibling_group_id')
                ->where('status', 'active')
                ->sum('value');

            $stats = [
                'total_groups'    => $groups->count(),
                'total_students'  => $groups->sum('total_children'),
                'total_savings'   => $totalSavings,
                'active_discounts' => DiscountAssignment::whereNotNull('sibling_group_id')->where('status', 'active')->count(),
            ];

            $formattedGroups = $groups->map(function ($group) {
                return [
                    'id'             => $group->id,
                    'group_no'       => $group->group_no,
                    'family_name'    => $group->family_name,
                    'parent_phone'   => $group->parent_phone,
                    'parent_email'   => $group->parent_email,
                    'address'        => $group->address,
                    'total_children' => $group->students->count(),
                    'discount_type'  => $group->discount_type,
                    'discount_value' => $group->discount_value,
                    'has_discount'   => !is_null($group->discount_value),
                    'students'       => $group->students->map(function ($student) {
                        return [
                            'id'           => $student->id,
                            'name'         => $student->firstname . ' ' . $student->lastname,
                            'admission_no' => $student->admissionNo,
                        ];
                    }),
                ];
            });

            return response()->json([
                'success' => true,
                'data'    => $formattedGroups,
                'stats'   => $stats,
            ]);
        }

        return view('sibling.index', compact('pagetitle'));
    }

    public function create()
    {
        $pagetitle = 'Create Family Group';
        return view('sibling.create', compact('pagetitle'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'family_name'    => 'required|string|max:255',
            'parent_phone'   => 'nullable|string|max:20',
            'parent_email'   => 'nullable|email|max:100',
            'address'        => 'nullable|string',
            'student_ids'    => 'required|array|min:1',
            'student_ids.*'  => 'exists:studentRegistration,id',
            'discount_type'  => 'nullable|in:percentage,fixed_per_child',
            'discount_value' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $groupCount = SiblingGroup::count();
            $groupNo    = 'SG-' . date('Y') . '-' . str_pad($groupCount + 1, 4, '0', STR_PAD_LEFT);

            $group = SiblingGroup::create([
                'group_no'       => $groupNo,
                'family_name'    => $request->family_name,
                'parent_phone'   => $request->parent_phone,
                'parent_email'   => $request->parent_email,
                'address'        => $request->address,
                'total_children' => count($request->student_ids),
                'discount_type'  => $request->discount_type,
                'discount_value' => $request->discount_value,
            ]);

            foreach ($request->student_ids as $studentId) {
                DB::table('sibling_group_students')->insert([
                    'sibling_group_id' => $group->id,
                    'student_id'       => $studentId,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }

            if ($request->discount_type && $request->discount_value) {
                $this->applySiblingDiscountToGroup($group, $request->student_ids);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Family group created successfully!',
                'data'    => $group,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store sibling group error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create group: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $group = SiblingGroup::find($id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found',
            ], 404);
        }

        $students = DB::table('sibling_group_students')
            ->where('sibling_group_id', $id)
            ->join('studentRegistration', 'sibling_group_students.student_id', '=', 'studentRegistration.id')
            ->select('studentRegistration.id', 'studentRegistration.firstname', 'studentRegistration.lastname', 'studentRegistration.admissionNo')
            ->get();

        $formattedStudents = $students->map(function ($student) {
            $classInfo    = $this->getStudentClassInfo($student->id);
            $classDisplay = $classInfo['class'];
            if ($classInfo['arm']) {
                $classDisplay .= ' ' . $classInfo['arm'];
            }

            return [
                'id'           => $student->id,
                'name'         => $student->firstname . ' ' . $student->lastname,
                'admission_no' => $student->admissionNo,
                'class'        => $classDisplay,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'group'          => $group,
                'students'       => $formattedStudents,
                'total_children' => $formattedStudents->count(),
            ],
        ]);
    }

public function edit($id)
{
    $groupData = SiblingGroup::findOrFail($id);

    // Get students with pivot
    $students = DB::table('sibling_group_students')
        ->where('sibling_group_id', $id)
        ->join('studentRegistration', 'sibling_group_students.student_id', '=', 'studentRegistration.id')
        ->leftJoin('studentpicture', 'studentRegistration.id', '=', 'studentpicture.studentid')
        ->select(
            'studentRegistration.id',
            'studentRegistration.firstname',
            'studentRegistration.lastname',
            'studentRegistration.admissionNo',
            'studentpicture.picture'
        )
        ->get();

    $initialStudents = [];

    foreach ($students as $student) {
        $firstName = !empty($student->firstname) ? $student->firstname : 'X';
        $lastName  = !empty($student->lastname) ? $student->lastname : 'X';
        $initials  = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));

        $pictureUrl = null;
        if (!empty($student->picture) && $student->picture !== 'unnamed.jpg') {
            $pictureUrl = asset('storage/images/student_avatars/' . $student->picture);
        }

        // Get class info safely
        try {
            $classInfo = $this->getStudentClassInfo($student->id);
            $classDisplay = $classInfo['class'] ?? 'N/A';
            if (!empty($classInfo['arm'])) {
                $classDisplay .= ' ' . $classInfo['arm'];
            }
        } catch (\Exception $e) {
            Log::warning("Class info failed for student {$student->id}");
            $classDisplay = 'N/A';
        }

        $initialStudents[] = [
            'id'           => (int) $student->id,
            'firstname'    => $firstName,
            'lastname'     => $lastName,
            'admission_no' => $student->admissionNo ?? 'N/A',
            'class'        => $classDisplay,
            'picture'      => $pictureUrl,
            'initials'     => $initials,
        ];
    }

    $pagetitle = 'Edit Family Group - ' . $groupData->family_name;

    return view('sibling.edit', [
        'group'           => $groupData,
        'pagetitle'       => $pagetitle,
        'initialStudents' => $initialStudents,
    ]);
}
    public function update(Request $request, $id)
    {
        $group = SiblingGroup::find($id);
        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'family_name'   => 'required|string|max:255',
            'parent_phone'  => 'nullable|string|max:20',
            'parent_email'  => 'nullable|email|max:100',
            'address'       => 'nullable|string',
            'student_ids'   => 'required|array|min:1',
            'student_ids.*' => 'exists:studentRegistration,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $group->update([
                'family_name'    => $request->family_name,
                'parent_phone'   => $request->parent_phone,
                'parent_email'   => $request->parent_email,
                'address'        => $request->address,
                'total_children' => count($request->student_ids),
            ]);

            // Sync students
            DB::table('sibling_group_students')->where('sibling_group_id', $id)->delete();

            foreach ($request->student_ids as $studentId) {
                DB::table('sibling_group_students')->insert([
                    'sibling_group_id' => $id,
                    'student_id'       => $studentId,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Family group updated successfully!',
                'data'    => $group,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update sibling group error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update group: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $group = SiblingGroup::find($id);
        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found',
            ], 404);
        }

        DB::beginTransaction();
        try {
            DB::table('sibling_group_students')->where('sibling_group_id', $id)->delete();
            DiscountAssignment::where('sibling_group_id', $id)->delete();
            $group->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Family group deleted successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delete sibling group error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete group: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function applyDiscount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id'       => 'required|exists:sibling_groups,id',
            'discount_type'  => 'required|in:percentage,fixed_per_child',
            'discount_value' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $group = SiblingGroup::find($request->group_id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found',
            ], 404);
        }

        if ($request->discount_type === 'percentage' && $request->discount_value > 100) {
            return response()->json([
                'success' => false,
                'message' => 'Percentage discount cannot exceed 100%',
            ], 422);
        }

        $group->update([
            'discount_type'  => $request->discount_type,
            'discount_value' => $request->discount_value,
        ]);

        $studentIds = DB::table('sibling_group_students')
            ->where('sibling_group_id', $group->id)
            ->pluck('student_id')
            ->toArray();

        $result = $this->applySiblingDiscountToGroup($group, $studentIds);

        return response()->json([
            'success' => true,
            'message' => "Sibling discount applied to {$result['count']} student(s)!",
            'data'    => $result,
        ]);
    }

    private function applySiblingDiscountToGroup($group, $studentIds)
    {
        $applied = 0;

        foreach ($studentIds as $index => $studentId) {
            $discountValue = $group->discount_value;
            if ($group->discount_type === 'percentage' && $index > 0) {
                $discountValue = min($group->discount_value + ($index * 5), 50);
            }

            DiscountAssignment::where('student_id', $studentId)
                ->where('sibling_group_id', $group->id)
                ->delete();

            $assignment                   = new DiscountAssignment();
            $assignment->student_id       = $studentId;
            $assignment->sibling_group_id = $group->id;
            $assignment->value_type       = $group->discount_type === 'percentage' ? 'percentage' : 'fixed_amount';
            $assignment->value            = $discountValue;
            $assignment->status           = 'active';
            $assignment->effective_from   = now();
            $assignment->effective_to     = now()->addYear();
            $assignment->assigned_by      = Auth::id();
            $assignment->save();

            $applied++;
        }

        return ['count' => $applied];
    }

    /**
     * Get student class information with fallback — never throws.
     */
    private function getStudentClassInfo($studentId)
    {
        $result = [
            'class'            => 'N/A',
            'arm'              => '',
            'term'             => 'N/A',
            'session'          => 'N/A',
            'has_current_term' => false,
        ];

        try {
            // Try student_current_term first
            $currentTerm = DB::table('student_current_term')
                ->where('studentId', $studentId)
                ->where('is_current', true)
                ->first();

            if ($currentTerm) {
                $result['has_current_term'] = true;

                $class = DB::table('schoolclass')->where('id', $currentTerm->schoolclassId)->first();
                if ($class) {
                    $result['class'] = $class->schoolclass ?? 'N/A';
                    if (!empty($class->arm)) {
                        $arm = DB::table('schoolarm')->where('id', $class->arm)->first();
                        $result['arm'] = $arm->arm ?? '';
                    }
                }

                $term = DB::table('schoolterm')->where('id', $currentTerm->termId)->first();
                $result['term'] = $term->term ?? 'N/A';

                $session = DB::table('schoolsession')->where('id', $currentTerm->sessionId)->first();
                $result['session'] = $session->session ?? 'N/A';

                return $result;
            }

            // Fallback to studentclass
            $studentClass = DB::table('studentclass')
                ->where('studentId', $studentId)
                ->orderBy('sessionid', 'desc')
                ->orderBy('termid', 'desc')
                ->first();

            if ($studentClass) {
                $class = DB::table('schoolclass')->where('id', $studentClass->schoolclassid)->first();
                if ($class) {
                    $result['class'] = $class->schoolclass ?? 'N/A';
                    if (!empty($class->arm)) {
                        $arm = DB::table('schoolarm')->where('id', $class->arm)->first();
                        $result['arm'] = $arm->arm ?? '';
                    }
                }

                $term = DB::table('schoolterm')->where('id', $studentClass->termid)->first();
                $result['term'] = $term->term ?? 'N/A';

                $session = DB::table('schoolsession')->where('id', $studentClass->sessionid)->first();
                $result['session'] = $session->session ?? 'N/A';
            }

            return $result;

        } catch (\Exception $e) {
            Log::error("getStudentClassInfo error for student {$studentId}: " . $e->getMessage());
            return $result;
        }
    }

    /**
     * Search students for adding to group (AJAX)
     */
    public function searchStudents(Request $request)
    {
        try {
            $search = trim($request->input('q', ''));

            if (strlen($search) < 2) {
                return response()->json([
                    'success'  => true,
                    'students' => [],
                    'results'  => [],
                ]);
            }

            $students = Student::where('firstname', 'like', "%{$search}%")
                ->orWhere('lastname', 'like', "%{$search}%")
                ->orWhere('admissionNo', 'like', "%{$search}%")
                ->limit(20)
                ->get(['id', 'firstname', 'lastname', 'admissionNo']);

            $formattedStudents = $students->map(function ($student) {
                $firstName = !empty($student->firstname) ? $student->firstname : 'X';
                $lastName  = !empty($student->lastname)  ? $student->lastname  : 'X';
                $initials  = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));

                $pictureUrl = null;
                $picture    = DB::table('studentpicture')->where('studentid', $student->id)->first();
                if ($picture && !empty($picture->picture) && $picture->picture !== 'unnamed.jpg') {
                    $pictureUrl = asset('storage/images/student_avatars/' . $picture->picture);
                }

                try {
                    $classInfo = $this->getStudentClassInfo($student->id);
                } catch (\Exception $e) {
                    $classInfo = ['class' => 'N/A', 'arm' => ''];
                }

                $classDisplay = $classInfo['class'] ?? 'N/A';
                if (!empty($classInfo['arm'])) {
                    $classDisplay .= ' ' . $classInfo['arm'];
                }

                return [
                    'id'           => (int) $student->id,
                    'text'         => $firstName . ' ' . $lastName . ' (' . ($student->admissionNo ?? 'N/A') . ')',
                    'firstname'    => $firstName,
                    'lastname'     => $lastName,
                    'admission_no' => $student->admissionNo ?? 'N/A',
                    'class'        => $classDisplay,
                    'picture'      => $pictureUrl,
                    'initials'     => $initials,
                ];
            });

            return response()->json([
                'success'  => true,
                'students' => $formattedStudents,
                'results'  => $formattedStudents->map(fn($s) => ['id' => $s['id'], 'text' => $s['text']]),
            ]);

        } catch (\Exception $e) {
            Log::error('Search students error: ' . $e->getMessage());
            return response()->json([
                'success'  => false,
                'message'  => 'Search failed: ' . $e->getMessage(),
                'students' => [],
            ], 500);
        }
    }

    public function getStudentSiblings($studentId)
    {
        try {
            $group = DB::table('sibling_group_students')
                ->where('student_id', $studentId)
                ->first();

            if (!$group) {
                return response()->json([
                    'success'  => true,
                    'siblings' => [],
                    'count'    => 0,
                ]);
            }

            $siblings = DB::table('sibling_group_students')
                ->where('sibling_group_id', $group->sibling_group_id)
                ->where('student_id', '!=', $studentId)
                ->join('studentRegistration', 'sibling_group_students.student_id', '=', 'studentRegistration.id')
                ->select('studentRegistration.id', 'studentRegistration.firstname', 'studentRegistration.lastname', 'studentRegistration.admissionNo')
                ->get();

            return response()->json([
                'success'  => true,
                'siblings' => $siblings->map(fn($s) => [
                    'id'           => $s->id,
                    'name'         => $s->firstname . ' ' . $s->lastname,
                    'admission_no' => $s->admissionNo,
                ]),
                'count' => $siblings->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Get student siblings error: ' . $e->getMessage());
            return response()->json([
                'success'  => false,
                'message'  => 'Failed to get siblings',
                'siblings' => [],
                'count'    => 0,
            ]);
        }
    }
}
