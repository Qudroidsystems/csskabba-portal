<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SchoolInformation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SchoolInformationController extends Controller
{
    // Uncomment when you have permission middleware set up
    // public function __construct()
    // {
    //     $this->middleware('permission:View schoolinformation|Create schoolinformation|Update schoolinformation|Delete schoolinformation', ['only' => ['index', 'show']]);
    //     $this->middleware('permission:Create schoolinformation', ['only' => ['create', 'store']]);
    //     $this->middleware('permission:Update schoolinformation', ['only' => ['edit', 'update']]);
    //     $this->middleware('permission:Delete schoolinformation', ['only' => ['destroy']]);
    // }

    /**
     * Display a listing of school information.
     */
    public function index(Request $request): View
    {
        $pagetitle = "School Information Management";
        $data = SchoolInformation::latest()->paginate(10);
        $status_counts = [
            'Active' => SchoolInformation::where('is_active', true)->count(),
            'Inactive' => SchoolInformation::where('is_active', false)->count(),
        ];

        return view('schoolinformation.index', compact('data', 'pagetitle', 'status_counts'))
            ->with('i', ($request->input('page', 1) - 1) * 10);
    }

    /**
     * Show the form for creating a new school information.
     */
    public function create(): View
    {
        $title = "Create School Information";
        return view('schoolinformation.create', compact('title'));
    }

    /**
     * Store a newly created school information in storage.
     */
    public function store(Request $request): JsonResponse
    {
        Log::debug("Creating school information", $request->all());

        try {
            $validated = $request->validate([
                'school_name' => 'required|string|max:255',
                'school_address' => 'required|string|max:500',
                'school_phones' => 'required|array|min:1',
                'school_phones.*' => 'required|string|max:20',
                'school_email' => 'required|email:rfc,dns|unique:school_information,school_email',
                'school_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'school_stamp' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'school_motto' => 'nullable|string|max:255',
                'school_website' => 'nullable|url|max:255',
                'no_of_times_school_opened' => 'required|integer|min:0',
                'date_school_opened' => 'nullable|date',
                'date_school_closed' => 'nullable|date|after_or_equal:date_school_opened',
                'date_next_term_begins' => 'nullable|date',
                'is_active' => 'sometimes|boolean',
            ], [
                'school_phones.required' => 'At least one phone number is required.',
                'school_phones.*.required' => 'Each phone number must not be empty.',
                'date_school_closed.after_or_equal' => 'School closed date must be after or equal to the opened date.',
            ]);

            // Handle file uploads
            if ($request->hasFile('school_logo')) {
                $path = $request->file('school_logo')->store('school_logos', 'public');
                $validated['school_logo'] = $path;
            }

            if ($request->hasFile('app_logo')) {
                $path = $request->file('app_logo')->store('app_logos', 'public');
                $validated['app_logo'] = $path;
            }

            if ($request->hasFile('school_stamp')) {
                $path = $request->file('school_stamp')->store('school_stamps', 'public');
                $validated['school_stamp'] = $path;
            }

            // Handle active status - only one school can be active
            $isActive = $request->input('is_active', false);
            if ($isActive) {
                SchoolInformation::where('is_active', true)->update(['is_active' => false]);
                $validated['is_active'] = true;
            } else {
                $validated['is_active'] = false;
            }

            $school = SchoolInformation::create($validated);

            Log::debug("School information created successfully: ID {$school->id}");

            return response()->json([
                'success' => true,
                'message' => 'School information created successfully',
                'school' => [
                    'id' => $school->id,
                    'school_name' => $school->school_name,
                    'school_email' => $school->school_email,
                    'is_active' => $school->is_active,
                ],
            ], 201);

        } catch (ValidationException $e) {
            Log::error("Validation error creating school information: " . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error("Create school information error: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to create school information: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified school information.
     */
    public function show($id): View
    {
        $pagetitle = "School Information Overview";
        $school = SchoolInformation::findOrFail($id);
        return view('schoolinformation.show', compact('school', 'pagetitle'));
    }

    /**
     * Show the form for editing the specified school information.
     */
    public function edit($id): View
    {
        $school = SchoolInformation::findOrFail($id);
        return view('schoolinformation.edit', compact('school'));
    }

    /**
     * Update the specified school information in storage.
     * Supports PUT, PATCH, and POST methods with _method=PUT
     */
    public function update(Request $request, $id): JsonResponse
    {
        Log::debug("Updating school information ID: {$id}", $request->all());

        try {
            $school = SchoolInformation::findOrFail($id);

            $validated = $request->validate([
                'school_name' => 'required|string|max:255',
                'school_address' => 'required|string|max:500',
                'school_phones' => 'required|array|min:1',
                'school_phones.*' => 'required|string|max:20',
                'school_email' => 'required|email|unique:school_information,school_email,' . $id,
                'school_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'school_stamp' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'school_motto' => 'nullable|string|max:255',
                'school_website' => 'nullable|url|max:255',
                'no_of_times_school_opened' => 'required|integer|min:0',
                'date_school_opened' => 'nullable|date',
                'date_school_closed' => 'nullable|date|after_or_equal:date_school_opened',
                'date_next_term_begins' => 'nullable|date',
                'is_active' => 'sometimes|boolean',
            ]);

            // Handle school logo upload
            if ($request->hasFile('school_logo')) {
                if ($school->school_logo && Storage::disk('public')->exists($school->school_logo)) {
                    Storage::disk('public')->delete($school->school_logo);
                }
                $path = $request->file('school_logo')->store('school_logos', 'public');
                $validated['school_logo'] = $path;
            } else {
                $validated['school_logo'] = $school->school_logo;
            }

            // Handle app logo upload
            if ($request->hasFile('app_logo')) {
                if ($school->app_logo && Storage::disk('public')->exists($school->app_logo)) {
                    Storage::disk('public')->delete($school->app_logo);
                }
                $path = $request->file('app_logo')->store('app_logos', 'public');
                $validated['app_logo'] = $path;
            } else {
                $validated['app_logo'] = $school->app_logo;
            }

            // Handle school stamp upload
            if ($request->hasFile('school_stamp')) {
                if ($school->school_stamp && Storage::disk('public')->exists($school->school_stamp)) {
                    Storage::disk('public')->delete($school->school_stamp);
                }
                $path = $request->file('school_stamp')->store('school_stamps', 'public');
                $validated['school_stamp'] = $path;
            } else {
                $validated['school_stamp'] = $school->school_stamp;
            }

            // Handle active status
            $isActive = $request->input('is_active', false);
            if ($isActive && !$school->is_active) {
                SchoolInformation::where('is_active', true)->where('id', '!=', $id)->update(['is_active' => false]);
                $validated['is_active'] = true;
            } elseif (!$isActive) {
                $validated['is_active'] = false;
            } else {
                $validated['is_active'] = $school->is_active;
            }

            $school->update($validated);

            Log::debug("School information ID: {$id} updated successfully");

            return response()->json([
                'success' => true,
                'message' => 'School information updated successfully',
                'school' => [
                    'id' => $school->id,
                    'school_name' => $school->school_name,
                    'school_email' => $school->school_email,
                    'is_active' => $school->is_active,
                ],
            ], 200);

        } catch (ValidationException $e) {
            Log::error("Validation error updating school information ID {$id}: " . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error("Update school information error for ID {$id}: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to update school information: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified school information from storage.
     */
    public function destroy($id): JsonResponse
    {
        Log::debug("Attempting to delete school information ID: {$id}");

        try {
            $school = SchoolInformation::findOrFail($id);

            // Delete associated files
            if ($school->school_logo && Storage::disk('public')->exists($school->school_logo)) {
                Storage::disk('public')->delete($school->school_logo);
            }

            if ($school->app_logo && Storage::disk('public')->exists($school->app_logo)) {
                Storage::disk('public')->delete($school->app_logo);
            }

            if ($school->school_stamp && Storage::disk('public')->exists($school->school_stamp)) {
                Storage::disk('public')->delete($school->school_stamp);
            }

            $school->delete();

            Log::debug("School information ID: {$id} deleted successfully");

            return response()->json([
                'success' => true,
                'message' => 'School information deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            Log::error("Delete school information error for ID {$id}: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete school information: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get school information as JSON for editing.
     */
    public function editJson($id): JsonResponse
    {
        try {
            $school = SchoolInformation::findOrFail($id);

            return response()->json([
                'success' => true,
                'school' => [
                    'id' => $school->id,
                    'school_name' => $school->school_name,
                    'school_address' => $school->school_address,
                    'school_phones' => $school->school_phones ?? [],
                    'school_email' => $school->school_email,
                    'school_motto' => $school->school_motto,
                    'school_website' => $school->school_website,
                    'no_of_times_school_opened' => $school->no_of_times_school_opened,
                    'date_school_opened' => $school->date_school_opened ? $school->date_school_opened->format('Y-m-d') : null,
                    'date_school_closed' => $school->date_school_closed ? $school->date_school_closed->format('Y-m-d') : null,
                    'date_next_term_begins' => $school->date_next_term_begins ? $school->date_next_term_begins->format('Y-m-d') : null,
                    'is_active' => (bool) $school->is_active,
                    'logo_url' => $school->getLogoUrlAttribute(),
                    'app_logo_url' => $school->getAppLogoUrlAttribute(),
                    'stamp_url' => $school->getStampUrlAttribute(),
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error("Edit JSON error for ID {$id}: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to load school data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete multiple school records.
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No schools selected for deletion'
                ], 400);
            }

            $schools = SchoolInformation::whereIn('id', $ids)->get();
            $deletedCount = 0;

            foreach ($schools as $school) {
                // Delete associated files
                if ($school->school_logo && Storage::disk('public')->exists($school->school_logo)) {
                    Storage::disk('public')->delete($school->school_logo);
                }
                if ($school->app_logo && Storage::disk('public')->exists($school->app_logo)) {
                    Storage::disk('public')->delete($school->app_logo);
                }
                if ($school->school_stamp && Storage::disk('public')->exists($school->school_stamp)) {
                    Storage::disk('public')->delete($school->school_stamp);
                }

                $school->delete();
                $deletedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} school(s) deleted successfully"
            ]);

        } catch (\Exception $e) {
            Log::error("Bulk delete error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete schools: ' . $e->getMessage()
            ], 500);
        }
    }
}
