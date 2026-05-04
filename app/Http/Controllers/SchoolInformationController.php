<?php

namespace App\Http\Controllers;

use App\Models\SchoolInformation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SchoolInformationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $pagetitle = "School Information Management";

        $data = SchoolInformation::latest()->paginate(10);

        $status_counts = [
            'Active'   => SchoolInformation::where('is_active', true)->count(),
            'Inactive' => SchoolInformation::where('is_active', false)->count(),
        ];

        return view('schoolinformation.index', compact('data', 'pagetitle', 'status_counts'))
            ->with('i', ($request->input('page', 1) - 1) * 10);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $title = "Create School Information";
        return view('schoolinformation.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // ==================== TEMPORARY DEBUG (Remove after testing) ====================
        // dd([
        //     'has_school_logo' => $request->hasFile('school_logo'),
        //     'school_logo_name' => $request->file('school_logo')?->getClientOriginalName(),
        //     'all_files' => array_keys($request->allFiles()),
        //     'request_method' => $request->method(),
        // ]);

        try {
            $validated = $request->validate([
                'school_name'           => 'required|string|max:255',
                'school_address'        => 'required|string|max:500',
                'school_phones'         => 'required|array|min:1',
                'school_phones.*'       => 'required|string|max:20',
                'school_email'          => 'required|email:rfc,dns|unique:school_information,school_email',
                'school_logo'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'app_logo'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'school_stamp'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'school_motto'          => 'nullable|string|max:255',
                'school_website'        => 'nullable|url|max:255',
                'no_of_times_school_opened' => 'required|integer|min:0',
                'date_school_opened'    => 'nullable|date',
                'date_school_closed'    => 'nullable|date|after_or_equal:date_school_opened',
                'date_next_term_begins' => 'nullable|date',
                'is_active'             => 'sometimes|boolean',
            ]);

            // ====================== FILE UPLOADS ======================
            if ($request->hasFile('school_logo')) {
                $validated['school_logo'] = $request->file('school_logo')->store('school_logos', 'public');
            }

            if ($request->hasFile('app_logo')) {
                $validated['app_logo'] = $request->file('app_logo')->store('app_logos', 'public');
            }

            if ($request->hasFile('school_stamp')) {
                $validated['school_stamp'] = $request->file('school_stamp')->store('school_stamps', 'public');
            }

            // Only one school can be active
            $isActive = $request->boolean('is_active');
            if ($isActive) {
                SchoolInformation::where('is_active', true)->update(['is_active' => false]);
            }
            $validated['is_active'] = $isActive;

            $school = SchoolInformation::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'School information created successfully.'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error("Store School Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create school information.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $pagetitle = "School Information Overview";
        $school = SchoolInformation::findOrFail($id);
        return view('schoolinformation.show', compact('school', 'pagetitle'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $school = SchoolInformation::findOrFail($id);
        return view('schoolinformation.edit', compact('school'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        // ==================== TEMPORARY DEBUG (Remove after testing) ====================
        // dd([
        //     'has_school_logo' => $request->hasFile('school_logo'),
        //     'school_logo_name' => $request->file('school_logo')?->getClientOriginalName(),
        //     'all_files' => array_keys($request->allFiles()),
        // ]);

        try {
            $school = SchoolInformation::findOrFail($id);

            $validated = $request->validate([
                'school_name'           => 'required|string|max:255',
                'school_address'        => 'required|string|max:500',
                'school_phones'         => 'required|array|min:1',
                'school_phones.*'       => 'required|string|max:20',
                'school_email'          => 'required|email|unique:school_information,school_email,' . $id,
                'school_logo'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'app_logo'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'school_stamp'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'school_motto'          => 'nullable|string|max:255',
                'school_website'        => 'nullable|url|max:255',
                'no_of_times_school_opened' => 'required|integer|min:0',
                'date_school_opened'    => 'nullable|date',
                'date_school_closed'    => 'nullable|date|after_or_equal:date_school_opened',
                'date_next_term_begins' => 'nullable|date',
                'is_active'             => 'sometimes|boolean',
            ]);

            // ====================== FILE UPLOADS (Update) ======================
            if ($request->hasFile('school_logo')) {
                if ($school->school_logo && Storage::disk('public')->exists($school->school_logo)) {
                    Storage::disk('public')->delete($school->school_logo);
                }
                $validated['school_logo'] = $request->file('school_logo')->store('school_logos', 'public');
            }

            if ($request->hasFile('app_logo')) {
                if ($school->app_logo && Storage::disk('public')->exists($school->app_logo)) {
                    Storage::disk('public')->delete($school->app_logo);
                }
                $validated['app_logo'] = $request->file('app_logo')->store('app_logos', 'public');
            }

            if ($request->hasFile('school_stamp')) {
                if ($school->school_stamp && Storage::disk('public')->exists($school->school_stamp)) {
                    Storage::disk('public')->delete($school->school_stamp);
                }
                $validated['school_stamp'] = $request->file('school_stamp')->store('school_stamps', 'public');
            }

            // Active school logic
            $isActive = $request->boolean('is_active');
            if ($isActive && !$school->is_active) {
                SchoolInformation::where('is_active', true)->where('id', '!=', $id)->update(['is_active' => false]);
            }
            $validated['is_active'] = $isActive;

            $school->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'School information updated successfully.'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error("Update School Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update school information.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $school = SchoolInformation::findOrFail($id);

            // Delete files
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

            return response()->json([
                'success' => true,
                'message' => 'School information deleted successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error("Delete School Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete school information.'
            ], 500);
        }
    }

    /**
     * Get school data as JSON for editing (used by modal)
     */
    public function editJson($id): JsonResponse
    {
        try {
            $school = SchoolInformation::findOrFail($id);

            return response()->json([
                'success' => true,
                'school' => [
                    'id'                    => $school->id,
                    'school_name'           => $school->school_name,
                    'school_address'        => $school->school_address,
                    'school_phones'         => $school->school_phones ?? [],
                    'school_email'          => $school->school_email,
                    'school_motto'          => $school->school_motto,
                    'school_website'        => $school->school_website,
                    'no_of_times_school_opened' => $school->no_of_times_school_opened,
                    'date_school_opened'    => $school->date_school_opened?->format('Y-m-d'),
                    'date_school_closed'    => $school->date_school_closed?->format('Y-m-d'),
                    'date_next_term_begins' => $school->date_next_term_begins?->format('Y-m-d'),
                    'is_active'             => (bool)$school->is_active,
                    'logo_url'              => $school->getLogoUrlAttribute(),
                    'app_logo_url'          => $school->getAppLogoUrlAttribute(),
                    'stamp_url'             => $school->getStampUrlAttribute(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load school data.'
            ], 500);
        }
    }

    /**
     * Bulk Delete Schools
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No schools selected.'
                ], 400);
            }

            $schools = SchoolInformation::whereIn('id', $ids)->get();

            foreach ($schools as $school) {
                // Delete associated files
                if ($school->school_logo) Storage::disk('public')->delete($school->school_logo);
                if ($school->app_logo) Storage::disk('public')->delete($school->app_logo);
                if ($school->school_stamp) Storage::disk('public')->delete($school->school_stamp);

                $school->delete();
            }

            return response()->json([
                'success' => true,
                'message' => count($ids) . ' school(s) deleted successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error("Bulk Delete Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete selected schools.'
            ], 500);
        }
    }
}
