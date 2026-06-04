<?php

namespace App\Http\Controllers;

use App\Models\Schoolarm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SchoolArmController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View school-arm|Create school-arm|Update school-arm|Delete school-arm', ['only' => ['index']]);
        $this->middleware('permission:Create school-arm', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update school-arm', ['only' => ['edit', 'update', 'updatearm']]);
        $this->middleware('permission:Delete school-arm', ['only' => ['destroy', 'deletearm']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Log::channel('schoolarm')->info('School Arm Index Request');

        try {
            $query = Schoolarm::query();

            if ($request->has('search')) {
                $search = $request->query('search');
                $query->where(function ($q) use ($search) {
                    $q->where('arm', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%');
                });
            }

            $all_arms = $query->orderBy('arm', 'asc')->get();
            $data = $query->orderBy('arm', 'asc')->paginate(10);

            return view('arm.index', compact('all_arms', 'data'));

        } catch (\Exception $e) {
            Log::channel('schoolarm')->error('Index Error:', ['error' => $e->getMessage()]);
            return back()->with('danger', 'Error loading school arms: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('arm.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            Log::channel('schoolarm')->info('Store request received', [
                'arm' => $request->input('arm'),
                'remark' => $request->input('remark')
            ]);

            $validator = Validator::make($request->all(), [
                'arm' => 'required|string|max:255|unique:schoolarm,arm',
                'remark' => 'nullable|string|max:500'
            ], [
                'arm.required' => 'Please enter an arm name.',
                'arm.unique' => 'This arm name already exists. Please use a different name.',
                'arm.max' => 'Arm name cannot exceed 255 characters.',
                'remark.max' => 'Description cannot exceed 500 characters.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $arm = Schoolarm::create([
                'arm' => $request->arm,
                'description' => $request->remark ?? null
            ]);

            DB::commit();

            Log::channel('schoolarm')->info('Arm created successfully', ['id' => $arm->id]);

            return response()->json([
                'success' => true,
                'message' => 'Arm created successfully!',
                'data' => $arm
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('schoolarm')->error('Store failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create arm: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $arm = Schoolarm::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $arm
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Arm not found'
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $arm = Schoolarm::findOrFail($id);
            return view('arm.edit', compact('arm'));

        } catch (\Exception $e) {
            Log::channel('schoolarm')->error('Edit error:', ['error' => $e->getMessage()]);
            return back()->with('danger', 'Arm not found');
        }
    }

    /**
     * Update the specified resource in storage (RESTful).
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'arm' => 'required|string|max:255|unique:schoolarm,arm,' . $id,
                'description' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $arm = Schoolarm::findOrFail($id);
            $arm->arm = $request->arm;
            $arm->description = $request->description ?? null;
            $arm->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Arm updated successfully!',
                'data' => $arm
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('schoolarm')->error('Update failed:', [
                'error' => $e->getMessage(),
                'id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update arm: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update arm via AJAX (custom route).
     */
    public function updatearm(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:schoolarm,id',
                'arm' => 'required|string|max:255|unique:schoolarm,arm,' . $request->id,
                'remark' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $arm = Schoolarm::findOrFail($request->id);
            $arm->arm = $request->arm;
            $arm->description = $request->remark ?? null;
            $arm->save();

            DB::commit();

            Log::channel('schoolarm')->info('Arm updated successfully', ['id' => $arm->id]);

            return response()->json([
                'success' => true,
                'message' => 'Arm updated successfully!',
                'data' => $arm
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('schoolarm')->error('Updatearm failed:', [
                'error' => $e->getMessage(),
                'id' => $request->id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update arm: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage (RESTful).
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $arm = Schoolarm::findOrFail($id);

            // Check if arm is being used in school classes
            $classCount = DB::table('schoolclass')->where('arm', $id)->count();
            if ($classCount > 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete this arm because it is currently being used by ' . $classCount . ' class(es).'
                ], 400);
            }

            $arm->delete();

            DB::commit();

            Log::channel('schoolarm')->info('Arm deleted successfully', ['id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Arm deleted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('schoolarm')->error('Destroy failed:', [
                'error' => $e->getMessage(),
                'id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete arm: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete arm via AJAX (custom route).
     */
    public function deletearm(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'armid' => 'required|exists:schoolarm,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $arm = Schoolarm::findOrFail($request->armid);

            // Check if arm is being used in school classes
            $classCount = DB::table('schoolclass')->where('arm', $request->armid)->count();
            if ($classCount > 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete this arm because it is currently being used by ' . $classCount . ' class(es).'
                ], 400);
            }

            $arm->delete();

            DB::commit();

            Log::channel('schoolarm')->info('Arm deleted successfully via AJAX', ['id' => $request->armid]);

            return response()->json([
                'success' => true,
                'message' => 'Arm deleted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('schoolarm')->error('Deletearm failed:', [
                'error' => $e->getMessage(),
                'armid' => $request->armid ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete arm: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all arms for API/Select dropdown.
     */
    public function getArms()
    {
        try {
            $arms = Schoolarm::orderBy('arm', 'asc')->get(['id', 'arm', 'description']);

            return response()->json([
                'success' => true,
                'data' => $arms
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch arms: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete arms.
     */
    public function bulkDelete(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'exists:schoolarm,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Check if any arms are being used
            $usedArms = DB::table('schoolclass')
                ->whereIn('arm', $request->ids)
                ->distinct()
                ->pluck('arm');

            if ($usedArms->count() > 0) {
                DB::rollBack();
                $usedArmNames = Schoolarm::whereIn('id', $usedArms)->pluck('arm')->implode(', ');
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete arms that are being used: ' . $usedArmNames
                ], 400);
            }

            $deleted = Schoolarm::whereIn('id', $request->ids)->delete();

            DB::commit();

            Log::channel('schoolarm')->info('Bulk delete completed', ['count' => $deleted]);

            return response()->json([
                'success' => true,
                'message' => $deleted . ' arm(s) deleted successfully!',
                'count' => $deleted
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('schoolarm')->error('Bulk delete failed:', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete arms: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export arms to CSV/Excel.
     */
    public function export()
    {
        try {
            $arms = Schoolarm::orderBy('arm', 'asc')->get();

            $filename = 'school_arms_' . date('Y-m-d_His') . '.csv';
            $handle = fopen('php://temp', 'w+');

            // Add CSV headers
            fputcsv($handle, ['ID', 'Arm Name', 'Description', 'Created At', 'Updated At']);

            // Add data rows
            foreach ($arms as $arm) {
                fputcsv($handle, [
                    $arm->id,
                    $arm->arm,
                    $arm->description,
                    $arm->created_at,
                    $arm->updated_at
                ]);
            }

            rewind($handle);
            $csvContent = stream_get_contents($handle);
            fclose($handle);

            return response($csvContent, 200)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            Log::channel('schoolarm')->error('Export failed:', ['error' => $e->getMessage()]);
            return back()->with('danger', 'Failed to export arms: ' . $e->getMessage());
        }
    }
}
