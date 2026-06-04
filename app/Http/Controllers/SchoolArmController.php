<?php

namespace App\Http\Controllers;

use App\Models\SchoolArm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SchoolArmController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $all_arms = SchoolArm::orderBy('arm', 'asc')->get();

        // For pagination example
        $data = SchoolArm::orderBy('arm', 'asc')->paginate(10);

        return view('arm.index', compact('all_arms', 'data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'arm' => 'required|string|max:255|unique:school_arms,arm'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $arm = SchoolArm::create([
                'arm' => $request->arm,
                'description' => $request->remark
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Arm created successfully',
                'data' => $arm
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create arm: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function updatearm(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:school_arms,id',
                'arm' => 'required|string|max:255|unique:school_arms,arm,' . $request->id
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $arm = SchoolArm::findOrFail($request->id);
            $arm->arm = $request->arm;
            $arm->description = $request->remark;
            $arm->save();

            return response()->json([
                'success' => true,
                'message' => 'Arm updated successfully',
                'data' => $arm
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update arm: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deletearm(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'armid' => 'required|exists:school_arms,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $arm = SchoolArm::findOrFail($request->armid);
            $arm->delete();

            return response()->json([
                'success' => true,
                'message' => 'Arm deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete arm: ' . $e->getMessage()
            ], 500);
        }
    }
}
