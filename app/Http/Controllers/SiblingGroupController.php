<?php
// app/Http/Controllers/Admin/SiblingGroupController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiblingGroup;
use App\Models\Student;
use App\Models\DiscountAssignment;
use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

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
     * Display listing with DataTable AJAX.
     */
    public function index(Request $request)
    {
        $pagetitle = 'Sibling Groups Management';

        if ($request->ajax()) {
            $groups = SiblingGroup::withCount('students')
                ->select('sibling_groups.*');

            return DataTables::of($groups)
                ->addIndexColumn()
                ->addColumn('students_list', function($group) {
                    return $group->students->take(3)->map(function($student) {
                        return $student->firstname . ' ' . $student->lastname;
                    })->implode(', ') . ($group->students_count > 3 ? ' +' . ($group->students_count - 3) : '');
                })
                ->addColumn('action', function($group) {
                    $buttons = '<button class="btn btn-sm btn-info view-group me-1" data-id="'.$group->id.'" data-family="'.$group->family_name.'"><i class="ri-eye-line"></i></button>';
                    $buttons .= '<button class="btn btn-sm btn-primary edit-group me-1" data-id="'.$group->id.'"><i class="ri-pencil-line"></i></button>';
                    $buttons .= '<button class="btn btn-sm btn-success apply-discount" data-id="'.$group->id.'"><i class="ri-discount-line"></i></button>';
                    $buttons .= '<button class="btn btn-sm btn-danger delete-group" data-id="'.$group->id.'"><i class="ri-delete-bin-line"></i></button>';
                    return $buttons;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('sibling.groups', compact('pagetitle'));
    }

    /**
     * Store new sibling group (AJAX).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'family_name' => 'required|string|max:255',
            'parent_phone' => 'nullable|string|max:20',
            'parent_email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'student_ids' => 'required|array|min:2',
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
                'message' => 'Sibling group created successfully!',
                'data' => $group
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create sibling group: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show sibling group details (AJAX).
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
     * Update sibling group (AJAX).
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
            'student_ids' => 'required|array|min:2',
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
                'message' => 'Sibling group updated successfully!',
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
     * Delete sibling group (AJAX).
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
            'message' => 'Sibling group deleted successfully!'
        ]);
    }

    /**
     * Apply sibling discount to group (AJAX).
     */
    public function applyDiscount(Request $request, $groupId = null)
    {
        $groupId = $groupId ?? $request->input('group_id');

        $group = SiblingGroup::find($groupId);
        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'discount_type' => 'required|in:percentage,fixed_per_child',
            'discount_value' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
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
}
