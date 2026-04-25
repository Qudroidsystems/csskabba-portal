<?php
// app/Http/Controllers/Admin/SiblingGroupController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiblingGroup;
use App\Models\Student;
use App\Models\DiscountAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SiblingGroupController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View sibling groups', ['only' => ['index', 'show']]);
        $this->middleware('permission:Create sibling group', ['only' => ['store']]);
        $this->middleware('permission:Update sibling group', ['only' => ['update']]);
        $this->middleware('permission:Delete sibling group', ['only' => ['destroy']]);
        $this->middleware('permission:Apply sibling discount', ['only' => ['applyDiscount']]);
    }

    /**
     * Display listing of sibling groups.
     */
    public function index(Request $request)
    {
        $pagetitle = 'Sibling Groups Management';

        if ($request->ajax()) {
            $groups = SiblingGroup::with(['students', 'discountAssignments'])
                ->orderBy('created_at', 'desc')
                ->get();

            $stats = [
                'total_groups' => $groups->count(),
                'total_students' => $groups->sum('total_children'),
                'total_savings' => DiscountAssignment::whereNotNull('sibling_group_id')->sum('value'),
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
        }

        return view('sibling.groups', compact('pagetitle'));
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
            // Generate unique group number
            $groupNo = 'SG-' . date('Y') . '-' . str_pad(SiblingGroup::count() + 1, 4, '0', STR_PAD_LEFT);

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

            // Attach students
            $group->students()->attach($request->student_ids);

            // Apply discount if configured
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
            return response()->json([
                'success' => false,
                'message' => 'Failed to create group: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display sibling group details.
     */
    public function show($id)
    {
        $group = SiblingGroup::with('students')->find($id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found'
            ], 404);
        }

        $students = $group->students->map(function($student) {
            return [
                'id' => $student->id,
                'name' => $student->firstname . ' ' . $student->lastname,
                'admission_no' => $student->admissionNo,
                'class' => optional($student->currentClass())->schoolclass->schoolclass ?? 'N/A',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'group' => $group,
                'students' => $students,
                'total_children' => $students->count()
            ]
        ]);
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

            // Sync students
            $group->students()->sync($request->student_ids);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Family group updated successfully!',
                'data' => $group
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
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

        // Remove discount assignments first
        DiscountAssignment::where('sibling_group_id', $id)->delete();

        // Detach students and delete group
        $group->students()->detach();
        $group->delete();

        return response()->json([
            'success' => true,
            'message' => 'Family group deleted successfully!'
        ]);
    }

    /**
     * Apply sibling discount to group.
     */
    public function applyDiscount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:sibling_groups,id',
            'discount_type' => 'required|in:percentage,fixed_per_child',
            'discount_value' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $group = SiblingGroup::find($request->group_id);

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
            // Calculate discount based on birth order
            $discountValue = $group->discount_value;
            if ($group->discount_type === 'percentage' && $index > 0) {
                // Additional 5% for each subsequent child
                $discountValue = min($group->discount_value + ($index * 5), 50);
            }

            $assignment = DiscountAssignment::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'sibling_group_id' => $group->id,
                ],
                [
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
        $search = $request->input('q', '');

        $students = Student::where('firstname', 'like', "%{$search}%")
            ->orWhere('lastname', 'like', "%{$search}%")
            ->orWhere('admissionNo', 'like', "%{$search}%")
            ->limit(20)
            ->get(['id', 'firstname', 'lastname', 'admissionNo']);

        return response()->json([
            'success' => true,
            'students' => $students->map(function($student) {
                return [
                    'id' => $student->id,
                    'text' => $student->firstname . ' ' . $student->lastname . ' (' . $student->admissionNo . ')'
                ];
            })
        ]);
    }

    /**
     * Get student siblings (AJAX).
     */
    public function getStudentSiblings($studentId)
    {
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
    }
}
