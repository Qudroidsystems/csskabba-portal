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

    /**
     * Display listing of sibling groups.
     */
    public function index(Request $request)
    {
        $pagetitle = 'Sibling Groups Management';

        if ($request->ajax() || $request->wantsJson()) {
            try {
                $groups = SiblingGroup::with(['students', 'discountAssignments'])
                    ->orderBy('created_at', 'desc')
                    ->get();

                $totalSavings = DiscountAssignment::whereNotNull('sibling_group_id')
                    ->where('status', 'active')
                    ->sum('value');

                $stats = [
                    'total_groups' => $groups->count(),
                    'total_students' => $groups->sum('total_children'),
                    'total_savings' => $totalSavings,
                    'active_discounts' => DiscountAssignment::whereNotNull('sibling_group_id')->where('status', 'active')->count(),
                ];

                $formattedGroups = $groups->map(function($group) {
                    return [
                        'id' => $group->id,
                        'group_no' => $group->group_no,
                        'family_name' => $group->family_name,
                        'parent_phone' => $group->parent_phone,
                        'parent_email' => $group->parent_email,
                        'address' => $group->address,
                        'total_children' => $group->students->count(),
                        'discount_type' => $group->discount_type,
                        'discount_value' => $group->discount_value,
                        'has_discount' => !is_null($group->discount_value),
                        'students' => $group->students->map(function($student) {
                            return [
                                'id' => $student->id,
                                'name' => $student->firstname . ' ' . $student->lastname,
                                'admission_no' => $student->admissionNo,
                            ];
                        }),
                    ];
                });

                return response()->json([
                    'success' => true,
                    'data' => $formattedGroups,
                    'stats' => $stats
                ]);
            } catch (\Exception $e) {
                Log::error('Index error: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load groups: ' . $e->getMessage()
                ], 500);
            }
        }

        return view('sibling.index', compact('pagetitle'));
    }

    /**
     * Show form for creating a new sibling group.
     */
    public function create()
    {
        $pagetitle = 'Create Family Group';
        return view('sibling.create', compact('pagetitle'));
    }

    /**
     * Store a new sibling group.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'family_name' => 'required|string|max:255',
            'parent_phone' => 'nullable|string|max:20',
            'parent_email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:studentRegistration,id',
            'discount_type' => 'nullable|in:percentage,fixed_per_child',
            'discount_value' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $groupCount = SiblingGroup::count();
            $groupNo = 'SG-' . date('Y') . '-' . str_pad($groupCount + 1, 4, '0', STR_PAD_LEFT);

            $group = SiblingGroup::create([
                'group_no' => $groupNo,
                'family_name' => $request->family_name,
                'parent_phone' => $request->parent_phone,
                'parent_email' => $request->parent_email,
                'address' => $request->address,
                'total_children' => count($request->student_ids),
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
            ]);

            // Attach students to the group using the pivot table
            foreach ($request->student_ids as $studentId) {
                DB::table('sibling_group_students')->insert([
                    'sibling_group_id' => $group->id,
                    'student_id' => $studentId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($request->discount_type && $request->discount_value) {
                $this->applySiblingDiscountToGroup($group, $request->student_ids);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Family group created successfully!',
                'data' => $group
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create group: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display sibling group details.
     *
     * Helper function to get student class info with fallback to studentclass table
     */
    private function getStudentClassInfo($student)
    {
        // First try to get from currentTerm (StudentCurrentTerm model)
        if ($student->currentTerm && $student->currentTerm->schoolClass) {
            $className = $student->currentTerm->schoolClass->schoolclass ?? 'N/A';
            $armName = $student->currentTerm->schoolClass->armRelation->arm ?? '';
            return [
                'class' => $className . ($armName ? ' ' . $armName : ''),
                'term' => $student->currentTerm->term->term ?? 'N/A',
                'session' => $student->currentTerm->session->session ?? 'N/A',
                'source' => 'currentTerm'
            ];
        }

        // Fallback to studentclass table (Studentclass model)
        if ($student->schoolClass && $student->schoolClass->schoolclass) {
            $className = $student->schoolClass->schoolclass->schoolclass ?? 'N/A';
            $armName = $student->schoolClass->armRelation->arm ?? '';
            return [
                'class' => $className . ($armName ? ' ' . $armName : ''),
                'term' => $student->term->term ?? 'N/A',
                'session' => $student->session->session ?? 'N/A',
                'source' => 'studentclass'
            ];
        }

        // No class info found
        return [
            'class' => 'Not Assigned',
            'term' => 'Not Assigned',
            'session' => 'Not Assigned',
            'source' => 'none'
        ];
    }

    public function show($id)
    {
        try {
            $group = SiblingGroup::with(['students.picture'])->find($id);

            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Group not found'
                ], 404);
            }

            $students = $group->students->map(function($student) {
                // Load additional relationships for class info
                $student->load(['currentTerm.schoolClass.armRelation', 'schoolClass.schoolclass', 'schoolClass.armRelation', 'term', 'session']);

                $classInfo = $this->getStudentClassInfo($student);

                // Get student picture
                $pictureUrl = null;
                if ($student->picture && $student->picture->picture && $student->picture->picture != 'unnamed.jpg') {
                    $pictureUrl = asset('storage/images/student_avatars/' . $student->picture->picture);
                }

                return [
                    'id' => $student->id,
                    'name' => $student->firstname . ' ' . $student->lastname,
                    'admission_no' => $student->admissionNo,
                    'class' => $classInfo['class'],
                    'term' => $classInfo['term'],
                    'session' => $classInfo['session'],
                    'class_source' => $classInfo['source'],
                    'picture' => $pictureUrl,
                    'initials' => strtoupper(substr($student->firstname, 0, 1) . substr($student->lastname, 0, 1)),
                ];
            });

            // Get discount info
            $discountInfo = null;
            if ($group->discount_value) {
                $discountInfo = [
                    'type' => $group->discount_type,
                    'value' => $group->discount_value,
                    'display' => $group->discount_type === 'percentage'
                        ? $group->discount_value . '%'
                        : '₦' . number_format($group->discount_value, 2) . ' per child'
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'group' => $group,
                    'students' => $students,
                    'total_children' => $students->count(),
                    'discount' => $discountInfo
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Show error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load group details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show form for editing a sibling group.
     */
    public function edit($id)
    {
        $group = SiblingGroup::with('students')->findOrFail($id);
        $pagetitle = 'Edit Family Group - ' . $group->family_name;
        return view('sibling.edit', compact('group', 'pagetitle'));
    }

    /**
     * Update sibling group.
     */
    public function update(Request $request, $id)
    {
        $group = SiblingGroup::find($id);
        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'family_name' => 'required|string|max:255',
            'parent_phone' => 'nullable|string|max:20',
            'parent_email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:studentRegistration,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $group->update([
                'family_name' => $request->family_name,
                'parent_phone' => $request->parent_phone,
                'parent_email' => $request->parent_email,
                'address' => $request->address,
                'total_children' => count($request->student_ids),
            ]);

            // Sync students - remove old and add new
            DB::table('sibling_group_students')->where('sibling_group_id', $id)->delete();
            foreach ($request->student_ids as $studentId) {
                DB::table('sibling_group_students')->insert([
                    'sibling_group_id' => $id,
                    'student_id' => $studentId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Family group updated successfully!',
                'data' => $group
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update group: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete sibling group.
     */
    public function destroy($id)
    {
        $group = SiblingGroup::find($id);
        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found'
            ], 404);
        }

        DB::beginTransaction();
        try {
            // Delete related discount assignments
            DiscountAssignment::where('sibling_group_id', $id)->delete();
            // Detach students
            DB::table('sibling_group_students')->where('sibling_group_id', $id)->delete();
            // Delete group
            $group->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Family group deleted successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Destroy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete group: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply sibling discount to group.
     */
    public function applyDiscount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:sibling_groups,id',
            'discount_type' => 'required|in:percentage,fixed_per_child',
            'discount_value' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $group = SiblingGroup::with('students')->find($request->group_id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found'
            ], 404);
        }

        if ($request->discount_type === 'percentage' && $request->discount_value > 100) {
            return response()->json([
                'success' => false,
                'message' => 'Percentage discount cannot exceed 100%'
            ], 422);
        }

        $group->update([
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
        ]);

        $result = $this->applySiblingDiscountToGroup($group, $group->students->pluck('id')->toArray());

        return response()->json([
            'success' => true,
            'message' => "Sibling discount applied to {$result['count']} student(s)!",
            'data' => $result
        ]);
    }

    /**
     * Apply sibling discount to students in group.
     */
    private function applySiblingDiscountToGroup($group, $studentIds)
    {
        $applied = 0;

        foreach ($studentIds as $index => $studentId) {
            $discountValue = $group->discount_value;
            if ($group->discount_type === 'percentage' && $index > 0) {
                $discountValue = min($group->discount_value + ($index * 5), 50);
            }

            $assignment = DiscountAssignment::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'sibling_group_id' => $group->id,
                ],
                [
                    'discount_id' => null,
                    'value_type' => $group->discount_type === 'percentage' ? 'percentage' : 'fixed_amount',
                    'value' => $discountValue,
                    'status' => 'active',
                    'effective_from' => now(),
                    'effective_to' => now()->addYear(),
                    'assigned_by' => Auth::id(),
                ]
            );

            if ($assignment->wasRecentlyCreated || $assignment->wasChanged()) {
                $applied++;
            }
        }

        return ['count' => $applied];
    }

    /**
     * Search students for adding to group (AJAX).
     */
    public function searchStudents(Request $request)
    {
        \Log::info('=== SEARCH STUDENTS METHOD CALLED ===');
    \Log::info('Request URL: ' . $request->fullUrl());
    \Log::info('Request query: ' . $request->get('q'));
    
        try {
            $search = $request->input('q', '');

            \Log::info('Search students called with query: ' . $search);

            if (strlen($search) < 2) {
                return response()->json([
                    'success' => true,
                    'students' => [],
                    'results' => []
                ]);
            }

            $students = Student::with(['picture', 'currentTerm.schoolClass.armRelation', 'schoolClass.schoolclass'])
                ->where('firstname', 'like', "%{$search}%")
                ->orWhere('lastname', 'like', "%{$search}%")
                ->orWhere('admissionNo', 'like', "%{$search}%")
                ->limit(20)
                ->get();

            $formattedStudents = $students->map(function($student) {
                // Get class info with fallback
                $className = 'N/A';
                $armName = '';

                // Try currentTerm first
                if ($student->currentTerm && $student->currentTerm->schoolClass) {
                    $className = $student->currentTerm->schoolClass->schoolclass ?? 'N/A';
                    if ($student->currentTerm->schoolClass->armRelation) {
                        $armName = $student->currentTerm->schoolClass->armRelation->arm;
                    }
                }
                // Fallback to studentclass
                elseif ($student->schoolClass && $student->schoolClass->schoolclass) {
                    $className = $student->schoolClass->schoolclass->schoolclass ?? 'N/A';
                    if ($student->schoolClass->armRelation) {
                        $armName = $student->schoolClass->armRelation->arm;
                    }
                }

                // Get picture
                $pictureUrl = null;
                if ($student->picture && $student->picture->picture && $student->picture->picture != 'unnamed.jpg') {
                    $pictureUrl = asset('storage/images/student_avatars/' . $student->picture->picture);
                }

                return [
                    'id' => $student->id,
                    'text' => $student->firstname . ' ' . $student->lastname . ' (' . $student->admissionNo . ')',
                    'firstname' => $student->firstname,
                    'lastname' => $student->lastname,
                    'admission_no' => $student->admissionNo,
                    'class' => $className . ($armName ? ' ' . $armName : ''),
                    'picture' => $pictureUrl,
                    'initials' => strtoupper(substr($student->firstname, 0, 1) . substr($student->lastname, 0, 1)),
                ];
            });

            return response()->json([
                'success' => true,
                'students' => $formattedStudents,
                'results' => $formattedStudents->map(function($s) {
                    return [
                        'id' => $s['id'],
                        'text' => $s['text']
                    ];
                })
            ]);

        } catch (\Exception $e) {
            \Log::error('Search students error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage(),
                'students' => []
            ], 500);
        }
    }

    /**
     * Get student siblings (AJAX).
     */
    public function getStudentSiblings($studentId)
    {
        try {
            $siblings = SiblingGroup::getSiblings($studentId);

            return response()->json([
                'success' => true,
                'siblings' => $siblings->map(function($sibling) {
                    return [
                        'id' => $sibling->id,
                        'name' => $sibling->firstname . ' ' . $sibling->lastname,
                        'admission_no' => $sibling->admissionNo,
                    ];
                }),
                'count' => $siblings->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Get siblings error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get siblings: ' . $e->getMessage()
            ], 500);
        }
    }
}
