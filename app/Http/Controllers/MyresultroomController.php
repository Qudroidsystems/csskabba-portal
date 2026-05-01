<?php

namespace App\Http\Controllers;

use App\Models\Schoolterm;
use App\Models\Broadsheets;
use App\Models\Schoolclass;
use Illuminate\Http\Request;
use App\Models\Schoolsession;
use App\Models\SubjectTeacher;
use App\Models\BroadsheetsMock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Validation\ValidationException;

class MyresultroomController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View myresult-room|Create myresult-room|Update myresult-room|Delete myresult-room', ['only' => ['index']]);
        $this->middleware('permission:Create myresult-room', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update myresult-room', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete myresult-room', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $pagetitle = "My Result Room";
        $user = auth()->user();

        if (!$user) {
            Log::warning('Unauthenticated access attempt', ['request' => $request->all()]);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'User not authenticated'], 403);
            }
            return redirect()->route('login');
        }

        $terms = Schoolterm::orderBy('id')->get();
        $sessions = Schoolsession::orderBy('id', 'desc')->get();
        $mysubjects = collect();
        $subjectTeachers = collect();

        // If no filters are selected, just show the empty view
        if (!$request->isMethod('post') && !$request->has(['termid', 'sessionid'])) {
            return view('myresultroom.index', compact('pagetitle', 'terms', 'sessions', 'mysubjects', 'subjectTeachers'));
        }

        // Process filters
        try {
            $validated = $request->validate([
                'termid' => ['required', 'integer', 'exists:schoolterm,id'],
                'sessionid' => ['required', 'integer', 'exists:schoolsession,id'],
            ]);

            Log::info('Filter request received', [
                'user_id' => $user->id,
                'termid' => $validated['termid'],
                'sessionid' => $validated['sessionid']
            ]);

            // Simplified query - break it down to avoid complex joins
            $subjectTeachersList = SubjectTeacher::where('staffid', $user->id)
                ->where('sessionid', $validated['sessionid'])
                ->where('termid', $validated['termid'])
                ->get();

            if ($subjectTeachersList->isEmpty()) {
                Log::info('No subject teachers found for user');
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'No subjects found',
                        'data' => ['mysubjects' => [], 'subjectTeachers' => []]
                    ], 200);
                }
                return view('myresultroom.index', compact('pagetitle', 'terms', 'sessions', 'mysubjects', 'subjectTeachers'))
                    ->with('error', 'No subjects found for the selected term and session.');
            }

            // Process each subject teacher to get details
            $mysubjects = collect();
            $subjectTeachers = collect();

            foreach ($subjectTeachersList as $teacher) {
                // Get subject details
                $subject = $teacher->subject;
                if (!$subject) continue;

                // Get subjectclass
                $subjectClass = $teacher->subjectClass;
                if (!$subjectClass) continue;

                // Get school class
                $schoolClass = $subjectClass->schoolClass;
                if (!$schoolClass) continue;

                // Get term and session
                $term = $teacher->term;
                $session = $teacher->session;

                // Get arm if exists
                $arm = $schoolClass->arm ? $schoolClass->arm->arm : '';

                // Get class categories
                $categories = $schoolClass->categories->pluck('category')->implode(', ');

                $className = trim($schoolClass->schoolclass . ' ' . $arm);

                // Check if broadsheet exists for terminal results
                $broadsheetExists = Broadsheets::where('staff_id', $user->id)
                    ->where('subjectclass_id', $subjectClass->id)
                    ->where('term_id', $validated['termid'])
                    ->where('session_id', $validated['sessionid'])
                    ->exists();

                // Check if broadsheet mock exists
                $broadsheetMockExists = BroadsheetsMock::where('staff_id', $user->id)
                    ->where('subjectclass_id', $subjectClass->id)
                    ->where('term_id', $validated['termid'])
                    ->where('session_id', $validated['sessionid'])
                    ->exists();

                // Add to mysubjects collection
                $mysubjects->push((object)[
                    'id' => $teacher->id,
                    'subject' => $subject->subject ?? 'N/A',
                    'subjectcode' => $subject->subject_code ?? 'N/A',
                    'schoolclass' => $className,
                    'classcategories' => $categories ?: 'N/A',
                    'term' => $term->term ?? 'N/A',
                    'session' => $session->session ?? 'N/A',
                    'userid' => $user->id,
                    'subjectclassid' => $subjectClass->id,
                    'schoolclassid' => $schoolClass->id,
                    'session_id' => $validated['sessionid'],
                    'termid' => $validated['termid'],
                    'broadsheet_exists' => $broadsheetExists,
                    'broadsheet_mock_exists' => $broadsheetMockExists,
                ]);

                // Add to subjectTeachers collection
                $subjectTeachers->push((object)[
                    'subjectclassid' => $subjectClass->id,
                    'userid' => $user->id,
                    'staffname' => $user->name,
                    'subjectname' => $subject->subject ?? 'N/A',
                    'termid' => $validated['termid'],
                    'term' => $term->term ?? 'N/A',
                    'schoolclass' => $className . ($categories ? ' (' . $categories . ')' : ''),
                ]);
            }

            Log::info('Processed data', [
                'mysubjects_count' => $mysubjects->count(),
                'subjectTeachers_count' => $subjectTeachers->count()
            ]);

            // Return JSON for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data loaded successfully',
                    'data' => [
                        'mysubjects' => $mysubjects->values(),
                        'subjectTeachers' => $subjectTeachers->values(),
                    ],
                ], 200);
            }

            // Return view with data
            return view('myresultroom.index', compact('pagetitle', 'terms', 'sessions', 'mysubjects', 'subjectTeachers'));

        } catch (ValidationException $e) {
            Log::warning('Validation failed', ['errors' => $e->errors()]);
            $errorMessage = 'Invalid input: ' . implode(', ', array_merge(...array_values($e->errors())));

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => $e->errors(),
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error('Error loading subjects', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'request_data' => $request->all()
            ]);

            $errorMessage = 'Database error: ' . $e->getMessage();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], 500);
            }
            return back()->with('error', $errorMessage);
        }
    }
}
