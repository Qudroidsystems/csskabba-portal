<?php
// app/Http/Controllers/Admin/ScholarshipController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scholarship;
use App\Models\ScholarshipType;
use App\Models\ScholarshipAssignment;
use App\Models\ScholarshipApplication;
use App\Models\Student;
use App\Models\Schoolclass;
use App\Models\Schoolterm;
use App\Models\Schoolsession;
use App\Services\Scholarship\ScholarshipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class ScholarshipController extends Controller
{
    protected $scholarshipService;

    public function __construct(ScholarshipService $scholarshipService)
    {
        $this->scholarshipService = $scholarshipService;

        $this->middleware('permission:View scholarship', ['only' => ['index', 'show', 'showAssignments', 'showApplications']]);
        $this->middleware('permission:Create scholarship', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update scholarship', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete scholarship', ['only' => ['destroy', 'bulkDestroy']]);
        $this->middleware('permission:Approve scholarship', ['only' => ['approve', 'approveApplication']]);
        $this->middleware('permission:Revoke scholarship', ['only' => ['revoke']]);
    }

    /**
     * Display a listing of scholarships.
     */
    public function index(Request $request)
    {
        $query = Scholarship::with(['type', 'createdBy', 'approvedBy']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('scholarship_no', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Scholarship type filter
        if ($request->filled('type_id')) {
            $query->where('scholarship_type_id', $request->type_id);
        }

        // Sort
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'desc');
        $query->orderBy($sort, $order);

        $scholarships = $query->paginate(15);

        // Statistics
        $totalScholarships = Scholarship::count();
        $activeScholarships = Scholarship::where('status', 'active')->count();
        $totalAwarded = ScholarshipAssignment::where('status', 'active')->sum('value');
        $totalStudents = ScholarshipAssignment::where('status', 'active')->distinct('student_id')->count('student_id');

        $scholarshipTypes = ScholarshipType::where('is_active', true)->get();

        $pagetitle = 'Scholarship Management';

        return view('admin.scholarship.index', compact(
            'pagetitle',
            'scholarships',
            'scholarshipTypes',
            'totalScholarships',
            'activeScholarships',
            'totalAwarded',
            'totalStudents'
        ));
    }

    /**
     * Show the form for creating a new scholarship.
     */
    public function create()
    {
        $scholarshipTypes = ScholarshipType::where('is_active', true)->get();
        $classes = Schoolclass::with('armRelation')->get();
        $terms = Schoolterm::all();
        $sessions = Schoolsession::all();

        $pagetitle = 'Create New Scholarship';

        return view('admin.scholarship.create', compact(
            'pagetitle',
            'scholarshipTypes',
            'classes',
            'terms',
            'sessions'
        ));
    }

    /**
     * Store a newly created scholarship in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'scholarship_type_id' => 'required|exists:scholarship_types,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'value_type' => 'required|in:percentage,fixed_amount',
                'value' => 'required|numeric|min:0.01',
                'cap_amount' => 'nullable|numeric|min:0',
                'requires_application' => 'boolean',
                'eligible_classes' => 'nullable|array',
                'eligible_classes.*' => 'exists:schoolclass,id',
                'eligible_status_ids' => 'nullable|array',
                'eligible_status_ids.*' => 'exists:student_status,id',
                'excluded_bill_categories' => 'nullable|array',
                'effective_from' => 'required|date',
                'effective_to' => 'nullable|date|after:effective_from',
                'max_recipients' => 'nullable|integer|min:1',
                'renewal_frequency' => 'nullable|integer|min:1',
                'budget_amount' => 'nullable|numeric|min:0',
                'status' => 'required|in:draft,active,expired,suspended',
            ]);

            // Generate unique scholarship number
            $validated['scholarship_no'] = $this->generateScholarshipNumber();
            $validated['created_by'] = auth()->id();
            $validated['requires_application'] = $request->has('requires_application');

            // Convert arrays to JSON
            $validated['eligible_classes'] = json_encode($validated['eligible_classes'] ?? []);
            $validated['eligible_status_ids'] = json_encode($validated['eligible_status_ids'] ?? []);
            $validated['excluded_bill_categories'] = json_encode($validated['excluded_bill_categories'] ?? []);

            $scholarship = Scholarship::create($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Scholarship created successfully.',
                    'scholarship' => $scholarship
                ]);
            }

            return redirect()->route('admin.scholarship.index')
                ->with('success', 'Scholarship created successfully.');

        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error.',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error creating scholarship: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create scholarship. Please try again.'
                ], 500);
            }

            return back()->with('error', 'Failed to create scholarship.');
        }
    }

    /**
     * Display the specified scholarship.
     */
    public function show($id)
    {
        $scholarship = Scholarship::with(['type', 'createdBy', 'approvedBy', 'assignments.student'])
            ->findOrFail($id);

        $assignments = $scholarship->assignments()
            ->with('student')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $totalAmount = $scholarship->assignments()->sum('value');
        $activeCount = $scholarship->assignments()->where('status', 'active')->count();

        $pagetitle = 'Scholarship Details: ' . $scholarship->title;

        return view('admin.scholarship.show', compact(
            'pagetitle',
            'scholarship',
            'assignments',
            'totalAmount',
            'activeCount'
        ));
    }

    /**
     * Show the form for editing the specified scholarship.
     */
    public function edit($id)
    {
        $scholarship = Scholarship::findOrFail($id);
        $scholarshipTypes = ScholarshipType::where('is_active', true)->get();
        $classes = Schoolclass::with('armRelation')->get();

        $pagetitle = 'Edit Scholarship: ' . $scholarship->title;

        return view('admin.scholarship.edit', compact(
            'pagetitle',
            'scholarship',
            'scholarshipTypes',
            'classes'
        ));
    }

    /**
     * Update the specified scholarship in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $scholarship = Scholarship::findOrFail($id);

            $validated = $request->validate([
                'scholarship_type_id' => 'required|exists:scholarship_types,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'value_type' => 'required|in:percentage,fixed_amount',
                'value' => 'required|numeric|min:0.01',
                'cap_amount' => 'nullable|numeric|min:0',
                'requires_application' => 'boolean',
                'eligible_classes' => 'nullable|array',
                'eligible_status_ids' => 'nullable|array',
                'excluded_bill_categories' => 'nullable|array',
                'effective_from' => 'required|date',
                'effective_to' => 'nullable|date|after:effective_from',
                'max_recipients' => 'nullable|integer|min:1',
                'renewal_frequency' => 'nullable|integer|min:1',
                'budget_amount' => 'nullable|numeric|min:0',
                'status' => 'required|in:draft,active,expired,suspended',
            ]);

            // Convert arrays to JSON
            $validated['eligible_classes'] = json_encode($validated['eligible_classes'] ?? []);
            $validated['eligible_status_ids'] = json_encode($validated['eligible_status_ids'] ?? []);
            $validated['excluded_bill_categories'] = json_encode($validated['excluded_bill_categories'] ?? []);

            $scholarship->update($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Scholarship updated successfully.'
                ]);
            }

            return redirect()->route('admin.scholarship.index')
                ->with('success', 'Scholarship updated successfully.');

        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error.',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error updating scholarship: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update scholarship.'
                ], 500);
            }

            return back()->with('error', 'Failed to update scholarship.');
        }
    }

    /**
     * Remove the specified scholarship from storage.
     */
    public function destroy($id)
    {
        try {
            $scholarship = Scholarship::findOrFail($id);

            // Check if scholarship has active assignments
            $hasActiveAssignments = $scholarship->assignments()
                ->where('status', 'active')
                ->exists();

            if ($hasActiveAssignments) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete scholarship with active assignments.'
                    ], 400);
                }
                return back()->with('error', 'Cannot delete scholarship with active assignments.');
            }

            $scholarship->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Scholarship deleted successfully.'
                ]);
            }

            return redirect()->route('admin.scholarship.index')
                ->with('success', 'Scholarship deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Error deleting scholarship: ' . $e->getMessage());

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete scholarship.'
                ], 500);
            }

            return back()->with('error', 'Failed to delete scholarship.');
        }
    }

    /**
     * Bulk delete scholarships.
     */
    public function bulkDestroy(Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No scholarships selected.'
                ], 400);
            }

            $scholarshipsWithActive = Scholarship::whereIn('id', $ids)
                ->whereHas('assignments', function($q) {
                    $q->where('status', 'active');
                })
                ->count();

            if ($scholarshipsWithActive > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete {$scholarshipsWithActive} scholarship(s) with active assignments."
                ], 400);
            }

            $deleted = Scholarship::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "{$deleted} scholarship(s) deleted successfully."
            ]);

        } catch (\Exception $e) {
            Log::error('Error bulk deleting scholarships: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete scholarships.'
            ], 500);
        }
    }

    /**
     * Approve a scholarship (set status to active).
     */
    public function approve($id)
    {
        try {
            $scholarship = Scholarship::findOrFail($id);

            $scholarship->update([
                'status' => 'active',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Scholarship approved successfully.'
                ]);
            }

            return redirect()->back()->with('success', 'Scholarship approved successfully.');

        } catch (\Exception $e) {
            Log::error('Error approving scholarship: ' . $e->getMessage());

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to approve scholarship.'
                ], 500);
            }

            return back()->with('error', 'Failed to approve scholarship.');
        }
    }

    /**
     * Revoke a scholarship.
     */
    public function revoke($id)
    {
        try {
            $scholarship = Scholarship::findOrFail($id);

            // Also revoke all active assignments
            $scholarship->assignments()
                ->where('status', 'active')
                ->update([
                    'status' => 'revoked',
                    'revocation_reason' => request()->input('reason', 'Scholarship revoked by administrator')
                ]);

            $scholarship->update([
                'status' => 'suspended',
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Scholarship revoked successfully.'
                ]);
            }

            return redirect()->back()->with('success', 'Scholarship revoked successfully.');

        } catch (\Exception $e) {
            Log::error('Error revoking scholarship: ' . $e->getMessage());

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to revoke scholarship.'
                ], 500);
            }

            return back()->with('error', 'Failed to revoke scholarship.');
        }
    }

    /**
     * Display scholarship assignments.
     */
    public function showAssignments(Request $request, $scholarshipId = null)
    {
        $query = ScholarshipAssignment::with(['scholarship', 'student', 'assignedBy', 'approvedBy']);

        if ($scholarshipId) {
            $query->where('scholarship_id', $scholarshipId);
            $scholarship = Scholarship::findOrFail($scholarshipId);
            $pagetitle = 'Scholarship Assignments: ' . $scholarship->title;
        } else {
            $pagetitle = 'All Scholarship Assignments';
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Student search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('admissionNo', 'like', "%{$search}%");
            });
        }

        $assignments = $query->orderBy('created_at', 'desc')->paginate(20);

        $statusCounts = [
            'pending' => ScholarshipAssignment::where('status', 'pending')->count(),
            'approved' => ScholarshipAssignment::where('status', 'approved')->count(),
            'active' => ScholarshipAssignment::where('status', 'active')->count(),
            'expired' => ScholarshipAssignment::where('status', 'expired')->count(),
            'revoked' => ScholarshipAssignment::where('status', 'revoked')->count(),
        ];

        $scholarships = Scholarship::where('status', 'active')->get();

        return view('admin.scholarship.assignments', compact(
            'pagetitle',
            'assignments',
            'statusCounts',
            'scholarshipId',
            'scholarships'
        ));
    }

    /**
     * Display scholarship applications (for manual applications).
     */
    public function showApplications(Request $request)
    {
        $query = ScholarshipApplication::with(['scholarship', 'student', 'reviewedBy']);

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Student search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('admissionNo', 'like', "%{$search}%");
            });
        }

        $applications = $query->orderBy('submitted_at', 'desc')->paginate(20);

        $statusCounts = [
            'draft' => ScholarshipApplication::where('status', 'draft')->count(),
            'submitted' => ScholarshipApplication::where('status', 'submitted')->count(),
            'under_review' => ScholarshipApplication::where('status', 'under_review')->count(),
            'approved' => ScholarshipApplication::where('status', 'approved')->count(),
            'rejected' => ScholarshipApplication::where('status', 'rejected')->count(),
        ];

        $pagetitle = 'Scholarship Applications';

        return view('admin.scholarship.applications', compact(
            'pagetitle',
            'applications',
            'statusCounts'
        ));
    }

    /**
     * Approve a scholarship application.
     */
    public function approveApplication(Request $request, $applicationId)
    {
        try {
            $result = $this->scholarshipService->processApplication(
                $applicationId,
                'approve',
                $request->input('notes')
            );

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Scholarship application approved and assigned successfully.',
                    'assignment' => $result
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to process application.'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error approving application: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve application: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a scholarship application.
     */
    public function rejectApplication(Request $request, $applicationId)
    {
        try {
            $request->validate([
                'rejection_reason' => 'required|string|min:5'
            ]);

            $result = $this->scholarshipService->processApplication(
                $applicationId,
                'reject',
                $request->input('rejection_reason')
            );

            return response()->json([
                'success' => true,
                'message' => 'Scholarship application rejected.'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error rejecting application: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject application.'
            ], 500);
        }
    }

    /**
     * Assign scholarship to a student directly (without application).
     */
    public function assignToStudent(Request $request)
    {
        try {
            $validated = $request->validate([
                'scholarship_id' => 'required|exists:scholarships,id',
                'student_id' => 'required|exists:studentRegistration,id',
                'effective_from' => 'required|date',
                'effective_to' => 'nullable|date|after:effective_from',
                'reason' => 'nullable|string',
            ]);

            $scholarship = Scholarship::findOrFail($validated['scholarship_id']);

            // Check if student already has active assignment for this scholarship
            $existing = ScholarshipAssignment::where('scholarship_id', $validated['scholarship_id'])
                ->where('student_id', $validated['student_id'])
                ->whereIn('status', ['active', 'pending', 'approved'])
                ->exists();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student already has an active/ pending assignment for this scholarship.'
                ], 400);
            }

            $assignment = ScholarshipAssignment::create([
                'scholarship_id' => $validated['scholarship_id'],
                'student_id' => $validated['student_id'],
                'status' => 'active',
                'approved_at' => now(),
                'effective_from' => $validated['effective_from'],
                'effective_to' => $validated['effective_to'],
                'value_type' => $scholarship->value_type,
                'value' => $scholarship->value,
                'cap_amount' => $scholarship->cap_amount,
                'reason' => $validated['reason'] ?? null,
                'assigned_by' => auth()->id(),
                'approved_by' => auth()->id(),
            ]);

            // Update utilized amount
            $scholarship->increment('utilized_amount', $scholarship->value_type === 'fixed_amount' ? $scholarship->value : 0);

            return response()->json([
                'success' => true,
                'message' => 'Scholarship assigned successfully.',
                'assignment' => $assignment
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error assigning scholarship: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign scholarship.'
            ], 500);
        }
    }

    /**
     * Revoke a scholarship assignment.
     */
    public function revokeAssignment(Request $request, $assignmentId)
    {
        try {
            $assignment = ScholarshipAssignment::findOrFail($assignmentId);

            $assignment->update([
                'status' => 'revoked',
                'revocation_reason' => $request->input('reason', 'Revoked by administrator'),
                'effective_to' => now(),
            ]);

            // Update scholarship utilized amount
            $scholarship = $assignment->scholarship;
            $scholarship->decrement('utilized_amount', $assignment->value_type === 'fixed_amount' ? $assignment->value : 0);

            return response()->json([
                'success' => true,
                'message' => 'Scholarship assignment revoked successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error revoking assignment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke assignment.'
            ], 500);
        }
    }

    /**
     * Get students eligible for scholarship based on criteria (AJAX).
     */
    public function getEligibleStudents(Request $request)
    {
        try {
            $request->validate([
                'scholarship_id' => 'required|exists:scholarships,id',
                'q' => 'nullable|string'
            ]);

            $scholarship = Scholarship::findOrFail($request->scholarship_id);
            $eligibleClasses = json_decode($scholarship->eligible_classes, true) ?? [];
            $eligibleStatusIds = json_decode($scholarship->eligible_status_ids, true) ?? [];

            $query = Student::query();

            if (!empty($eligibleClasses)) {
                $query->whereHas('studentClass', function($q) use ($eligibleClasses) {
                    $q->whereIn('schoolclassid', $eligibleClasses);
                });
            } else {
                // Get all students with current class
                $query->whereHas('studentClass');
            }

            if (!empty($eligibleStatusIds)) {
                $query->whereIn('statusId', $eligibleStatusIds);
            }

            // Search by name or admission number
            if ($request->filled('q')) {
                $search = $request->q;
                $query->where(function($q) use ($search) {
                    $q->where('firstname', 'like', "%{$search}%")
                      ->orWhere('lastname', 'like', "%{$search}%")
                      ->orWhere('admissionNo', 'like', "%{$search}%");
                });
            }

            // Exclude students who already have active assignment
            $query->whereDoesntHave('scholarshipAssignments', function($q) use ($scholarship) {
                $q->where('scholarship_id', $scholarship->id)
                  ->whereIn('status', ['active', 'pending', 'approved']);
            });

            $students = $query->select('id', 'firstname', 'lastname', 'admissionNo')
                ->orderBy('lastname')
                ->limit(50)
                ->get();

            return response()->json([
                'success' => true,
                'students' => $students
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting eligible students: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get eligible students.'
            ], 500);
        }
    }

    /**
     * Generate unique scholarship number.
     */
    private function generateScholarshipNumber()
    {
        $year = date('Y');
        $lastScholarship = Scholarship::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastScholarship ? intval(substr($lastScholarship->scholarship_no, -4)) + 1 : 1;

        return 'SCH-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
