<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Broadsheets;
use App\Models\Schoolclass;
use App\Models\SchoolInformation;
use App\Models\Subjectclass;
use Illuminate\Http\Request;
use App\Models\SubAssessment;
use App\Models\BroadsheetsMock;
use App\Models\PromotionStatus;
use App\Models\BroadsheetRecord;
use App\Exports\MarksSheetExport;
use App\Imports\ScoresheetImport;
use App\Exports\RecordsheetExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\BroadsheetRecordMock;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MockMarksSheetExport;
use App\Exports\MockRecordsheetExport;
use Illuminate\Support\Facades\Storage;
use App\Models\BroadsheetAssessmentScore;
use App\Models\BroadsheetSubAssessmentScore;
use Barryvdh\DomPDF\Facade\Pdf;

class MyScoreSheetController extends Controller
{
    public function index(Request $request)
    {
        $pagetitle   = 'My Scoresheets';
        $broadsheets = collect();

        if (!$request->ajax()) {
            $termId    = $request->query('termid', 'ALL');
            $sessionId = $request->query('sessionid', 'ALL');
            if ($termId !== 'ALL' && $sessionId !== 'ALL') {
                $broadsheets = $this->getBroadsheets($request->user()->id, $termId, $sessionId);
            }
        }

        if ($request->ajax()) {
            $termId    = $request->input('termid', 'ALL');
            $sessionId = $request->input('sessionid', 'ALL');
            if ($termId === 'ALL' || $sessionId === 'ALL') {
                return response()->json(['success' => false, 'message' => 'Please select both term and session.'], 422);
            }
            $broadsheets = $this->getBroadsheets($request->user()->id, $termId, $sessionId);
            return response()->json(['success' => true, 'data' => ['broadsheets' => $broadsheets]]);
        }

        return view('subjectscoresheet.index', compact('pagetitle', 'broadsheets'));
    }

    public function subjectscoresheet($schoolclassid, $subjectclassid, $staffid, $termid, $sessionid)
    {
        session([
            'schoolclass_id'  => $schoolclassid,
            'subjectclass_id' => $subjectclassid,
            'staff_id'        => $staffid,
            'term_id'         => $termid,
            'session_id'      => $sessionid,
        ]);

        $broadsheets = $this->getBroadsheets($staffid, $termid, $sessionid, $schoolclassid, $subjectclassid);
        $schoolclass = Schoolclass::with('classcategories')->find($schoolclassid);
        $assessments = collect();

        if ($broadsheets->isNotEmpty() && $schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            $categoryIds = $schoolclass->classcategories->pluck('id');
            $assessments = Assessment::whereIn('classcategory_id', $categoryIds)
                ->with('subAssessments')
                ->orderBy('id')
                ->get();

            $this->updateClassMetrics($subjectclassid, $staffid, $termid, $sessionid);
            $this->computeDynamicTotals($broadsheets, $assessments, $schoolclass, $termid, $sessionid);
            $this->updateSubjectPositions($subjectclassid, $staffid, $termid, $sessionid);
            $this->updateClassPositions($schoolclassid, $termid, $sessionid);

            $broadsheets = $this->getBroadsheets($staffid, $termid, $sessionid, $schoolclassid, $subjectclassid);
            $this->computeOverallGPAAndCGPA($broadsheets, $schoolclass, $termid, $sessionid);

            $pagetitle = sprintf(
                'Scoresheet for %s (%s) - %s %s - %s %s',
                $broadsheets->first()->subject,
                $broadsheets->first()->subject_code,
                $broadsheets->first()->schoolclass,
                $broadsheets->first()->arm,
                $broadsheets->first()->term,
                $broadsheets->first()->session
            );
        } else {
            $pagetitle = 'Subject Scoresheet';
        }

        $is_senior = $schoolclass && $schoolclass->classcategories->isNotEmpty()
            ? $schoolclass->classcategories->first()->is_senior ?? false
            : false;

        return view('subjectscoresheet.index', compact('broadsheets', 'pagetitle', 'is_senior', 'assessments'));
    }

    public function subassessmentScoresheet($schoolclassid, $subjectclassid, $staffid, $termid, $sessionid, $subassessmentid)
    {
        session([
            'schoolclass_id'    => $schoolclassid,
            'subjectclass_id'   => $subjectclassid,
            'staff_id'          => $staffid,
            'term_id'           => $termid,
            'session_id'        => $sessionid,
            'subassessment_id'  => $subassessmentid,
        ]);

        $subassessment  = SubAssessment::findOrFail($subassessmentid);
        $assessment     = $subassessment->assessment;
        $broadsheets    = $this->getSubassessmentBroadsheets($staffid, $termid, $sessionid, $schoolclassid, $subjectclassid, $subassessmentid);
        $schoolclass    = Schoolclass::with('classcategories')->find($schoolclassid);
        $allAssessments = collect();

        if ($broadsheets->isNotEmpty() && $schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            $categoryIds    = $schoolclass->classcategories->pluck('id');
            $allAssessments = Assessment::whereIn('classcategory_id', $categoryIds)->with('subAssessments')->orderBy('id')->get();

            $this->updateClassMetrics($subjectclassid, $staffid, $termid, $sessionid);
            $this->computeDynamicTotals($broadsheets, $allAssessments, $schoolclass, $termid, $sessionid);
            $this->updateSubjectPositions($subjectclassid, $staffid, $termid, $sessionid);
            $this->updateClassPositions($schoolclassid, $termid, $sessionid);

            $broadsheets = $this->getSubassessmentBroadsheets($staffid, $termid, $sessionid, $schoolclassid, $subjectclassid, $subassessmentid);
            $this->computeOverallGPAAndCGPA($broadsheets, $schoolclass, $termid, $sessionid);
        }

        $assessments = $allAssessments->flatMap(fn($a) => $a->subAssessments)->where('id', $subassessmentid);

        $pagetitle = sprintf(
            'Scoresheet for %s (%s) - %s %s - %s %s',
            $subassessment->name,
            $subassessment->max_score ?? 'N/A',
            $broadsheets->first()?->schoolclass ?? 'Class',
            $broadsheets->first()?->arm ?? '',
            $broadsheets->first()?->term ?? '',
            $broadsheets->first()?->session ?? ''
        );

        $is_senior = $schoolclass && $schoolclass->classcategories->isNotEmpty()
            ? $schoolclass->classcategories->first()->is_senior ?? false : false;

        return view('subjectscoresheet.subassessment-index', compact('broadsheets', 'pagetitle', 'is_senior', 'assessments', 'subassessment'));
    }

    public function assessmentScoresheet($schoolclassid, $subjectclassid, $staffid, $termid, $sessionid, $assessmentid)
    {
        session([
            'schoolclass_id'  => $schoolclassid,
            'subjectclass_id' => $subjectclassid,
            'staff_id'        => $staffid,
            'term_id'         => $termid,
            'session_id'      => $sessionid,
            'assessment_id'   => $assessmentid,
        ]);

        $assessment     = Assessment::with('subAssessments')->findOrFail($assessmentid);
        $broadsheets    = $this->getBroadsheets($staffid, $termid, $sessionid, $schoolclassid, $subjectclassid);
        $schoolclass    = Schoolclass::with('classcategories')->find($schoolclassid);
        $realSubs       = $assessment->subAssessments;
        $is_sub_view    = $realSubs->isNotEmpty();
        $subAssessments = $is_sub_view ? $realSubs->each(fn($s) => $s->is_sub_item = true) : collect([$assessment])->each(fn($s) => $s->is_sub_item = false);
        $allAssessments = collect();

        if ($broadsheets->isNotEmpty() && $schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            $categoryIds    = $schoolclass->classcategories->pluck('id');
            $allAssessments = Assessment::whereIn('classcategory_id', $categoryIds)->with('subAssessments')->orderBy('id')->get();

            $this->updateClassMetrics($subjectclassid, $staffid, $termid, $sessionid);
            $this->computeDynamicTotals($broadsheets, $allAssessments, $schoolclass, $termid, $sessionid);
            $this->updateSubjectPositions($subjectclassid, $staffid, $termid, $sessionid);
            $this->updateClassPositions($schoolclassid, $termid, $sessionid);

            $broadsheets = $this->getBroadsheets($staffid, $termid, $sessionid, $schoolclassid, $subjectclassid);
            $this->computeOverallGPAAndCGPA($broadsheets, $schoolclass, $termid, $sessionid);
        }

        $pagetitle = sprintf('Scoresheet for %s (%s) - %s %s - %s %s',
            $assessment->name, $assessment->max_score,
            $broadsheets->first()?->schoolclass ?? 'Class', $broadsheets->first()?->arm ?? '',
            $broadsheets->first()?->term ?? '', $broadsheets->first()?->session ?? '');

        $is_senior = $schoolclass && $schoolclass->classcategories->isNotEmpty()
            ? $schoolclass->classcategories->first()->is_senior ?? false : false;

        return view('subjectscoresheet.assessment-index', compact('broadsheets', 'pagetitle', 'is_senior', 'subAssessments', 'assessment', 'is_sub_view'));
    }

    // =========================================================================
    // DOWNLOAD MARKS SHEET (PDF)
    // =========================================================================

    public function downloadMarksSheet(Request $request)
    {
        try {
            // Get parameters from request or session
            $subjectclassid = $request->input('subjectclass_id', session('subjectclass_id'));
            $staffid        = $request->input('staff_id', session('staff_id'));
            $termid         = $request->input('term_id', session('term_id'));
            $sessionid      = $request->input('session_id', session('session_id'));
            $schoolclassid  = $request->input('schoolclass_id', session('schoolclass_id'));

            // Validate required parameters
            if (!$subjectclassid || !$staffid || !$termid || !$sessionid || !$schoolclassid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing session data. Please open the scoresheet first.'
                ], 400);
            }

            // Get the data needed for the marks sheet
            $broadsheets = $this->getBroadsheets($staffid, $termid, $sessionid, $schoolclassid, $subjectclassid);

            if ($broadsheets->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No students found for this subject.'
                ], 404);
            }

            // Get schoolclass and assessments
            $schoolclass = Schoolclass::with('classcategories')->find($schoolclassid);
            $assessments = collect();

            if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
                $categoryIds = $schoolclass->classcategories->pluck('id');
                $assessments = Assessment::whereIn('classcategory_id', $categoryIds)
                    ->with('subAssessments')
                    ->orderBy('id')
                    ->get();
            }

            // Get class info for the header
            $classInfo = $broadsheets->first();

            // Get school info (adjust the model name as needed)
            $school = SchoolInformation::first();

            // Generate PDF using DomPDF
            $pdf = Pdf::loadView('subjectscoresheet.marksheet', [
                'broadsheets' => $broadsheets,
                'assessments' => $assessments,
                'classInfo' => $classInfo,
                'school' => $school
            ]);

            $pdf->setPaper('a4', 'landscape');

            return $pdf->download('marks-sheet-' . date('Y-m-d') . '.pdf');

        } catch (\Exception $e) {
            Log::error('Marks sheet download error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate marks sheet: ' . $e->getMessage()
            ], 500);
        }
    }

    // =========================================================================
    // EXPORT (Excel, dynamic)
    // =========================================================================

    public function export()
    {
        $schoolclassId  = session('schoolclass_id');
        $subjectclassId = session('subjectclass_id');
        $termId         = session('term_id');
        $sessionId      = session('session_id');
        $staffId        = session('staff_id');

        if (!$schoolclassId || !$subjectclassId || !$termId || !$sessionId || !$staffId) {
            return back()->with('error', 'Missing session data. Please open the scoresheet first.');
        }

        return Excel::download(
            new RecordsheetExport($schoolclassId, $subjectclassId, $termId, $sessionId, $staffId),
            'scoresheet_' . date('Y-m-d_H-i-s') . '.xlsx'
        );
    }

    // =========================================================================
    // IMPORT
    // =========================================================================

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls']);

        $importData = [
            'subjectclass_id' => $request->input('subjectclass_id', session('subjectclass_id')),
            'staff_id'        => $request->input('staff_id',        session('staff_id')),
            'term_id'         => $request->input('term_id',         session('term_id')),
            'session_id'      => $request->input('session_id',      session('session_id')),
            'schoolclass_id'  => $request->input('schoolclass_id',  session('schoolclass_id')),
        ];

        try {
            $importer = new ScoresheetImport($importData);
            $path     = $request->file('file')->store('temp');
            $fullPath = storage_path('app/' . $path);
            $importer->validateExcelMetadata($fullPath);
            Excel::import($importer, $request->file('file'));
            Storage::delete($path);

            $failures = $importer->getFailures();
            if (!empty($failures)) {
                return back()->with('warning', count($failures) . ' row(s) had issues during import.');
            }

            return back()->with('success', 'Scores imported successfully!');
        } catch (\Exception $e) {
            Log::error('Import failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // SINGLE UPDATE SCORE
    // =========================================================================

    public function singleUpdateScore(Request $request)
    {
        try {
            $validated = $request->validate([
                'broadsheet_id'    => 'required|exists:broadsheets,id',
                'assessment_id'    => 'required|exists:assessments,id',
                'score'            => 'required|numeric|min:0',
                'is_sub'           => 'boolean',
                'sub_assessment_id'=> 'nullable|exists:sub_assessments,id',
                'total'            => 'nullable|numeric',
                'raw_total'        => 'nullable|numeric',
            ]);

            $broadsheetId    = $validated['broadsheet_id'];
            $assessmentId    = $validated['assessment_id'];
            $score           = $validated['score'];
            $isSub           = $validated['is_sub'] ?? false;
            $subAssessmentId = $validated['sub_assessment_id'] ?? null;

            if ($isSub && !$subAssessmentId) {
                return response()->json(['success' => false, 'message' => 'Sub-assessment ID required.'], 422);
            }

            $broadsheet = Broadsheets::findOrFail($broadsheetId);
            $model      = $isSub ? SubAssessment::findOrFail($subAssessmentId) : Assessment::findOrFail($assessmentId);

            if ($score > $model->max_score) {
                return response()->json([
                    'success' => false,
                    'message' => "Score cannot exceed maximum of {$model->max_score}.",
                ], 422);
            }

            $fkValue          = $broadsheet->broadSheet_record_id ?? $broadsheet->broadsheet_record_id;
            $broadsheetRecord = BroadsheetRecord::find($fkValue);

            $schoolclassId = $broadsheetRecord?->schoolclass_id
                ?? (int)($request->input('schoolclass_id') ?: session('schoolclass_id'))
                ?: 0;

            $sessionId = $broadsheetRecord?->session_id
                ?? $request->input('session_id')
                ?? session('session_id');

            if (!$sessionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session context missing — please reload the scoresheet and try again.',
                ], 200);
            }

            $termId      = $broadsheet->term_id ?? session('term_id');
            $schoolclass = Schoolclass::with('classcategories')->find($schoolclassId);
            $isSenior    = $schoolclass && $schoolclass->classcategories->isNotEmpty()
                ? $schoolclass->classcategories->first()->is_senior ?? false : false;

            DB::transaction(function () use ($broadsheetId, $assessmentId, $score, $broadsheet, $isSub, $subAssessmentId, $broadsheetRecord, $schoolclass, $sessionId) {
                if ($isSub) {
                    BroadsheetSubAssessmentScore::updateOrCreate(
                        ['broadsheet_id' => $broadsheetId, 'sub_assessment_id' => $subAssessmentId, 'assessment_id' => $assessmentId],
                        ['score' => $score]
                    );
                    $assessment = Assessment::with('subAssessments')->find($assessmentId);
                    if ($assessment) {
                        $subMaxSum    = $assessment->subAssessments->sum('max_score');
                        $subTotal     = BroadsheetSubAssessmentScore::where('broadsheet_id', $broadsheetId)->where('assessment_id', $assessmentId)->sum('score');
                        $normalized   = $subMaxSum > 0 ? ($subTotal / $subMaxSum) * $assessment->max_score : 0;
                        $clamped      = max(0, min($normalized, $assessment->max_score));
                        BroadsheetAssessmentScore::updateOrCreate(
                            ['broadsheet_id' => $broadsheetId, 'assessment_id' => $assessmentId],
                            ['score' => $clamped]
                        );
                    }
                } else {
                    BroadsheetAssessmentScore::updateOrCreate(
                        ['broadsheet_id' => $broadsheetId, 'assessment_id' => $assessmentId],
                        ['score' => $score]
                    );
                }

                $assessments = collect();
                if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
                    $categoryIds = $schoolclass->classcategories->pluck('id');
                    $assessments = Assessment::whereIn('classcategory_id', $categoryIds)->with('subAssessments')->get();
                }
                $broadsheet->load(['assessmentScores', 'subAssessmentScores']);
                $this->computeDynamicTotals(collect([$broadsheet]), $assessments, $schoolclass, $broadsheet->term_id, $sessionId);
            });

            $this->updateClassMetrics($broadsheet->subjectclass_id, $broadsheet->staff_id, $termId, $sessionId);
            $this->updateSubjectPositions($broadsheet->subjectclass_id, $broadsheet->staff_id, $termId, $sessionId);
            $this->updateClassPositions($schoolclassId, $termId, $sessionId);

            $studentId       = $broadsheetRecord?->student_id ?? DB::table('broadsheet_records')->where('id', $fkValue ?? 0)->value('student_id') ?? 0;
            $gpaCgpaData     = $this->computeOverallForStudent($studentId, $schoolclass, $termId, $sessionId, $isSenior);
            $gpa             = round($gpaCgpaData['gpa'], 2);
            $cgpa            = round($gpaCgpaData['cgpa'], 2);
            $gpa_grade       = $gpaCgpaData['gpa_grade'] ?? 'F';
            $num_subjects    = $gpaCgpaData['num_subjects'] ?? 0;
            $total_gp        = $gpaCgpaData['total_grade_points'] ?? 0.0;

            $broadsheet->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Score updated successfully!',
                'data'    => [
                    'total'              => $broadsheet->total,
                    'cum'                => $broadsheet->cum,
                    'bf'                 => $broadsheet->bf,
                    'grade'              => $broadsheet->grade,
                    'remark'             => $broadsheet->remark,
                    'gpa'                => $gpa,
                    'gpa_grade'          => $gpa_grade,
                    'cgpa'               => $cgpa,
                    'num_subjects'       => $num_subjects,
                    'total_grade_points' => $total_gp,
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('singleUpdateScore error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to save score: ' . $e->getMessage(),
            ], 200);
        }
    }

    // =========================================================================
    // BULK UPDATE
    // =========================================================================

    public function bulkUpdateScores(Request $request)
    {
        $validated = $request->validate([
            'scores'            => 'required|array',
            'scores.*.id'       => 'required|exists:broadsheets,id',
            'scores.*.assessments' => 'sometimes|array',
            'scores.*.total'    => 'nullable|numeric',
            'scores.*.raw_total'=> 'nullable|numeric',
            'term_id'           => 'required|exists:schoolterm,id',
            'session_id'        => 'required|exists:schoolsession,id',
            'subjectclass_id'   => 'required|exists:subjectclass,id',
            'staff_id'          => 'required|exists:users,id',
            'schoolclass_id'    => 'required|exists:schoolclass,id',
            'assessment_id'     => 'required_if:is_sub,true|exists:assessments,id',
            'is_sub'            => 'boolean',
        ]);

        $scores         = $validated['scores'];
        $term_id        = $validated['term_id'];
        $session_id     = $validated['session_id'];
        $subjectclass_id= $validated['subjectclass_id'];
        $staff_id       = $validated['staff_id'];
        $schoolclass_id = $validated['schoolclass_id'];
        $assessment_id  = $validated['assessment_id'] ?? null;
        $is_sub         = $validated['is_sub'] ?? false;

        $schoolclass = Schoolclass::with('classcategories')->find($schoolclass_id);
        if (!$schoolclass) {
            return response()->json(['success' => false, 'message' => 'School class not found'], 404);
        }

        $assessments = collect();
        if ($schoolclass->classcategories->isNotEmpty()) {
            $categoryIds = $schoolclass->classcategories->pluck('id');
            $assessments = Assessment::whereIn('classcategory_id', $categoryIds)->with('subAssessments')->get();
        }

        $updatedCount = 0;
        $errors       = [];

        DB::transaction(function () use ($scores, $term_id, $session_id, $subjectclass_id, $staff_id, $schoolclass_id, $schoolclass, $assessments, $is_sub, $assessment_id, &$updatedCount, &$errors) {
            foreach ($scores as $scoreData) {
                $broadsheetId  = $scoreData['id'];
                $broadsheet    = Broadsheets::find($broadsheetId);
                if (!$broadsheet) { $errors[] = "Broadsheet ID {$broadsheetId} not found."; continue; }

                $assessmentsData = $scoreData['assessments'] ?? [];
                if (empty($assessmentsData)) continue;

                $localErrors = [];
                foreach ($assessmentsData as $componentId => $inputScore) {
                    $componentId = (int) $componentId;
                    if ($is_sub) {
                        $model = SubAssessment::find($componentId);
                        if (!$model || $model->assessment_id != $assessment_id) { $localErrors[] = "SubAssessment {$componentId} invalid."; continue; }
                        $clamped = max(0, min($inputScore, $model->max_score));
                        BroadsheetSubAssessmentScore::updateOrCreate(
                            ['broadsheet_id' => $broadsheetId, 'sub_assessment_id' => $componentId, 'assessment_id' => $assessment_id],
                            ['score' => $clamped]
                        );
                    } else {
                        $model = $assessments->where('id', $componentId)->first();
                        if (!$model) { $localErrors[] = "Assessment {$componentId} invalid."; continue; }
                        $clamped = max(0, min($inputScore, $model->max_score));
                        BroadsheetAssessmentScore::updateOrCreate(
                            ['broadsheet_id' => $broadsheetId, 'assessment_id' => $componentId],
                            ['score' => $clamped]
                        );
                    }
                }

                if (!empty($localErrors)) { $errors[] = "Broadsheet {$broadsheetId}: " . implode(', ', $localErrors); continue; }

                if ($is_sub && $assessment_id) {
                    $assessment = $assessments->where('id', $assessment_id)->first();
                    if ($assessment) {
                        $subMaxSum  = $assessment->subAssessments->sum('max_score');
                        $subTotal   = BroadsheetSubAssessmentScore::where('broadsheet_id', $broadsheetId)->where('assessment_id', $assessment_id)->sum('score');
                        $normalized = $subMaxSum > 0 ? ($subTotal / $subMaxSum) * $assessment->max_score : 0;
                        BroadsheetAssessmentScore::updateOrCreate(
                            ['broadsheet_id' => $broadsheetId, 'assessment_id' => $assessment_id],
                            ['score' => max(0, min($normalized, $assessment->max_score))]
                        );
                    }
                }

                $broadsheet->load(['assessmentScores', 'subAssessmentScores']);
                $this->computeDynamicTotals(collect([$broadsheet]), $assessments, $schoolclass, $term_id, $session_id);
                $updatedCount++;
            }

            $this->updateClassMetrics($subjectclass_id, $staff_id, $term_id, $session_id);
            $this->updateSubjectPositions($subjectclass_id, $staff_id, $term_id, $session_id);
            $this->updateClassPositions($schoolclass_id, $term_id, $session_id);
        });

        $updatedBroadsheets = $this->getBroadsheets($staff_id, $term_id, $session_id, $schoolclass_id, $subjectclass_id);
        $this->computeOverallGPAAndCGPA($updatedBroadsheets, $schoolclass, $term_id, $session_id);

        $response = ['success' => true, 'message' => "{$updatedCount} score(s) updated!", 'data' => ['broadsheets' => $updatedBroadsheets, 'assessments' => $assessments]];
        if (!empty($errors)) $response['warnings'] = $errors;

        return response()->json($response, 200);
    }

    // =========================================================================
    // RESULTS (AJAX refresh)
    // =========================================================================

    public function results()
    {
        try {
            $subjectclass_id = session('subjectclass_id');
            $schoolclass_id  = session('schoolclass_id');
            $term_id         = session('term_id');
            $session_id      = session('session_id');

            if (!$subjectclass_id || !$schoolclass_id || !$term_id || !$session_id) {
                return response()->json(['success' => false, 'message' => 'Missing session data', 'scores' => []], 400);
            }

            $schoolclass = Schoolclass::with('classcategories')->find($schoolclass_id);
            $assessments = collect();
            if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
                $categoryIds = $schoolclass->classcategories->pluck('id');
                $assessments = Assessment::whereIn('classcategory_id', $categoryIds)->with('subAssessments')->orderBy('id')->get();
            }

            $broadsheets = Broadsheets::where(['subjectclass_id' => $subjectclass_id, 'term_id' => $term_id])
                ->with(['assessmentScores', 'subAssessmentScores'])
                ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
                ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
                ->leftJoin('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
                ->where('broadsheet_records.session_id', $session_id)
                ->orderBy('studentRegistration.lastname')
                ->orderBy('studentRegistration.firstname')
                ->get([
                    'broadsheets.id',
                    'studentRegistration.admissionNO as admissionno',
                    'studentRegistration.firstname as fname',
                    'studentRegistration.lastname as lname',
                    'broadsheets.total', 'broadsheets.bf', 'broadsheets.cum', 'broadsheets.grade',
                    'broadsheets.subject_position_class as position', 'broadsheets.term_id',
                ]);

            $this->computeOverallGPAAndCGPA($broadsheets, $schoolclass, $term_id, $session_id);

            $scoresData = $broadsheets->map(function ($b) use ($assessments) {
                $assessmentData = [];
                foreach ($assessments as $a) {
                    $s = $b->assessmentScores->where('assessment_id', $a->id)->first();
                    $assessmentData[$a->id] = ['name' => $a->name, 'max_score' => $a->max_score, 'score' => $s ? $s->score : 0];
                }
                return [
                    'id' => $b->id, 'admissionno' => $b->admissionno, 'fname' => $b->fname, 'lname' => $b->lname,
                    'assessments' => $assessmentData, 'total' => $b->total, 'bf' => $b->bf, 'cum' => $b->cum,
                    'gpa' => $b->gpa, 'gpa_grade' => $b->gpa_grade ?? 'F', 'cgpa' => $b->cgpa,
                    'grade' => $b->grade, 'position' => $b->position,
                    'num_subjects' => $b->num_subjects ?? 0, 'total_grade_points' => $b->total_grade_points ?? 0.0,
                ];
            });

            return response()->json(['success' => true, 'assessments' => $assessments, 'scores' => $scoresData]);
        } catch (\Exception $e) {
            Log::error('Error in results: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Internal server error.'], 500);
        }
    }

    // =========================================================================
    // EDIT / UPDATE / DESTROY
    // =========================================================================

    public function edit($id)
    {
        $broadsheet = Broadsheets::where('broadsheets.id', $id)
            ->with('assessmentScores')
            ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->leftJoin('subjectclass', 'subjectclass.id', '=', 'broadsheets.subjectclass_id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'broadsheet_records.schoolclass_id')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('classcategories', 'classcategories.id', '=', 'schoolclass.classcategoryid')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'broadsheets.term_id')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'broadsheet_records.session_id')
            ->first([
                'broadsheets.id as bid', 'studentRegistration.admissionNO as admissionno', 'studentRegistration.title',
                'studentRegistration.firstname as fname', 'studentRegistration.lastname as lname', 'studentpicture.picture',
                'broadsheets.total', 'broadsheets.bf', 'broadsheets.cum', 'broadsheets.grade',
                'schoolterm.term', 'schoolsession.session', 'subject.subject', 'subject.subject_code',
                'schoolclass.schoolclass', 'schoolarm.id', 'broadsheets.subject_position_class as position',
                'broadsheets.remark', 'broadsheet_records.student_id', 'broadsheets.staff_id',
                'broadsheets.term_id', 'broadsheet_records.session_id as sessionid', 'schoolclass.classcategoryid',
            ]);

        if (!$broadsheet) {
            return view('error', ['id' => $id, 'title' => 'Not Found', 'message' => 'Score not found.']);
        }

        $schoolclass = Schoolclass::with('classcategories')->find($broadsheet->schoolclass_id ?? 0);
        $assessments = collect();
        if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            $categoryIds = $schoolclass->classcategories->pluck('id');
            $assessments = Assessment::whereIn('classcategory_id', $categoryIds)->with('subAssessments')->orderBy('id')->get();
        }

        $isSenior = $schoolclass && $schoolclass->classcategories->isNotEmpty()
            ? $schoolclass->classcategories->first()->is_senior ?? false : false;

        if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            $gpaCgpaData = $this->computeOverallForStudent($broadsheet->student_id, $schoolclass, $broadsheet->term_id, $broadsheet->sessionid, $isSenior);
            $broadsheet->gpa               = round($gpaCgpaData['gpa'], 2);
            $broadsheet->cgpa              = round($gpaCgpaData['cgpa'], 2);
            $broadsheet->gpa_grade         = $gpaCgpaData['gpa_grade'] ?? 'F';
            $broadsheet->num_subjects      = $gpaCgpaData['num_subjects'] ?? 0;
            $broadsheet->total_grade_points= $gpaCgpaData['total_grade_points'] ?? 0.0;
        }

        $pagetitle = sprintf('Edit Score for %s %s - %s (%s)', $broadsheet->fname, $broadsheet->lname, $broadsheet->subject, $id);
        return view('scoresheet.edit', compact('broadsheet', 'pagetitle', 'assessments'));
    }

    public function update(Request $request, $id)
    {
        $broadsheet       = Broadsheets::findOrFail($id);
        $termId           = $broadsheet->term_id;
        $broadsheetRecord = BroadsheetRecord::find($broadsheet->broadSheet_record_id);
        $schoolclassId    = $broadsheetRecord->schoolclass_id ?? 0;
        $schoolclass      = Schoolclass::with('classcategories')->find($schoolclassId);
        $assessments      = collect();

        if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            $categoryIds = $schoolclass->classcategories->pluck('id');
            $assessments = Assessment::whereIn('classcategory_id', $categoryIds)->with('subAssessments')->get();
        }

        $rules = [];
        foreach ($assessments as $a) {
            $rules['assessment_' . $a->id] = 'nullable|numeric|min:0|max:' . $a->max_score;
        }
        $request->validate($rules);

        foreach ($assessments as $a) {
            BroadsheetAssessmentScore::updateOrCreate(
                ['broadsheet_id' => $id, 'assessment_id' => $a->id],
                ['score' => $request->input('assessment_' . $a->id, 0)]
            );
        }

        $broadsheet->load('assessmentScores');
        $this->computeDynamicTotals(collect([$broadsheet]), $assessments, $schoolclass, $termId, $broadsheetRecord->session_id);

        $isSenior = $schoolclass && $schoolclass->classcategories->isNotEmpty()
            ? $schoolclass->classcategories->first()->is_senior ?? false : false;

        if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            $gpaCgpaData = $this->computeOverallForStudent($broadsheet->student_id, $schoolclass, $termId, $broadsheetRecord->session_id, $isSenior);
            $broadsheet->gpa               = round($gpaCgpaData['gpa'], 2);
            $broadsheet->cgpa              = round($gpaCgpaData['cgpa'], 2);
            $broadsheet->gpa_grade         = $gpaCgpaData['gpa_grade'] ?? 'F';
            $broadsheet->num_subjects      = $gpaCgpaData['num_subjects'] ?? 0;
            $broadsheet->total_grade_points= $gpaCgpaData['total_grade_points'] ?? 0.0;
        }

        $this->updateClassMetrics($broadsheet->subjectclass_id, $broadsheet->staff_id, $termId, $broadsheetRecord->session_id);
        $this->updateSubjectPositions($broadsheet->subjectclass_id, $broadsheet->staff_id, $termId, $broadsheetRecord->session_id);
        $this->updateClassPositions($schoolclassId, $termId, $broadsheetRecord->session_id);

        return redirect()->action([self::class, 'subjectscoresheet'], [
            'schoolclassid'  => $schoolclassId,
            'subjectclassid' => $broadsheet->subjectclass_id,
            'staffid'        => $broadsheet->staff_id,
            'termid'         => $termId,
            'sessionid'      => $broadsheetRecord->session_id,
        ])->with('success', 'Score updated successfully!');
    }

    public function destroy(Request $request)
    {
        $id               = $request->input('id');
        $broadsheet       = Broadsheets::findOrFail($id);
        $subjectclassid   = $broadsheet->subjectclass_id;
        $staffid          = $broadsheet->staff_id;
        $termid           = $broadsheet->term_id;
        $broadsheetRecord = DB::table('broadsheet_records')->where('id', $broadsheet->broadSheet_record_id)->first();

        BroadsheetAssessmentScore::where('broadsheet_id', $id)->delete();
        BroadsheetSubAssessmentScore::where('broadsheet_id', $id)->delete();
        $broadsheet->delete();

        if ($broadsheetRecord) {
            $this->updateClassMetrics($subjectclassid, $staffid, $termid, $broadsheetRecord->session_id);
            $this->updateSubjectPositions($subjectclassid, $staffid, $termid, $broadsheetRecord->session_id);
            $this->updateClassPositions($broadsheetRecord->schoolclass_id, $termid, $broadsheetRecord->session_id);
        }

        return response()->json(['success' => true, 'message' => 'Score deleted successfully!']);
    }

    // =========================================================================
    // GPA / CGPA helpers
    // =========================================================================

    private function computeOverallGPAAndCGPA($broadsheets, $schoolclass, $termId, $sessionId)
    {
        if ($schoolclass->classcategories->isEmpty()) return;
        $isSenior = $schoolclass->classcategories->first()->is_senior ?? false;
        foreach ($broadsheets as $b) {
            $data = $this->computeOverallForStudent($b->student_id, $schoolclass, $termId, $sessionId, $isSenior);
            $b->gpa               = round($data['gpa'], 2);
            $b->cgpa              = round($data['cgpa'], 2);
            $b->gpa_grade         = $data['gpa_grade'] ?? 'F';
            $b->num_subjects      = $data['num_subjects'] ?? 0;
            $b->total_grade_points= $data['total_grade_points'] ?? 0.0;
        }
    }

    private function computeOverallForStudent($studentId, $schoolclass, $termId, $sessionId, $isSenior)
    {
        $currentBroadsheets = Broadsheets::where('term_id', $termId)
            ->whereHas('broadsheetRecord', fn($q) => $q->where('student_id', $studentId)->where('session_id', $sessionId))
            ->get(['total']);

        $category    = $schoolclass->classcategories->first();
        $averageTotal= $currentBroadsheets->avg('total') ?? 0.0;
        $gpaGrade    = $category ? $category->calculateGrade($averageTotal) : $this->getDefaultGrade($averageTotal);

        $termGradePoints  = $currentBroadsheets->map(fn($b) => $this->getGradePoint($b->total, $isSenior));
        $gpa              = $termGradePoints->avg() ?? 0.0;
        $numSubjects      = $currentBroadsheets->count();
        $totalGradePoints = $termGradePoints->sum();

        $annualGPAs = [];
        $sessions   = DB::table('broadsheet_records')
            ->join('schoolclass', 'schoolclass.id', '=', 'broadsheet_records.schoolclass_id')
            ->join('classcategories', 'classcategories.id', '=', 'schoolclass.classcategoryid')
            ->where('broadsheet_records.student_id', $studentId)
            ->where('classcategories.is_senior', $isSenior)
            ->select('broadsheet_records.session_id')
            ->distinct()->orderByDesc('broadsheet_records.session_id')->limit(3)->pluck('session_id');

        foreach ($sessions as $targetSession) {
            $sessionGPAs = [];
            for ($t = 1; $t <= 3; $t++) {
                $tb = Broadsheets::where('term_id', $t)
                    ->whereHas('broadsheetRecord', fn($q) => $q->where('student_id', $studentId)->where('session_id', $targetSession))
                    ->get(['total']);
                $sessionGPAs[] = $tb->map(fn($b) => $this->getGradePoint($b->total, $isSenior))->avg() ?? 0.0;
            }
            $annualGPAs[] = collect($sessionGPAs)->avg() ?? 0.0;
        }

        return [
            'gpa'                => $gpa,
            'cgpa'               => collect($annualGPAs)->avg() ?? 0.0,
            'gpa_grade'          => $gpaGrade,
            'num_subjects'       => $numSubjects,
            'total_grade_points' => $totalGradePoints,
        ];
    }

    private function getGradePoint($score, $isSenior = false)
    {
        if (!$isSenior) {
            if ($score >= 70) return 5.0;
            if ($score >= 60) return 4.0;
            if ($score >= 50) return 3.0;
            if ($score >= 40) return 2.0;
            return 0.0;
        }
        if ($score >= 75) return 5.0;
        if ($score >= 65) return 4.0;
        if ($score >= 50) return 3.0;
        if ($score >= 45) return 2.0;
        if ($score >= 40) return 1.0;
        return 0.0;
    }

    // =========================================================================
    // COMPUTE DYNAMIC TOTALS
    // =========================================================================

    private function computeDynamicTotals($broadsheets, $assessments, $schoolclass, $termId, $sessionId)
    {
        foreach ($broadsheets as $broadsheet) {
            $assessmentScores = $broadsheet->assessmentScores ?? collect();
            $totalRaw         = 0;
            foreach ($assessments as $a) {
                $scoreObj  = $assessmentScores->where('assessment_id', $a->id)->first();
                $totalRaw += $scoreObj ? $scoreObj->score : 0;
            }

            $newBf    = $this->getPreviousTermCum($broadsheet->student_id, $broadsheet->subject_id, $termId, $sessionId);
            $newCum   = $termId == 1 ? round($totalRaw, 2) : round(($totalRaw + $newBf) / 2, 2);
            $newGrade = $schoolclass && $schoolclass->classcategories->isNotEmpty()
                ? $schoolclass->classcategories->first()->calculateGrade($newCum) : $this->getDefaultGrade($newCum);
            $newRemark = $this->getRemark($newGrade);

            $changed = abs($broadsheet->total - $totalRaw) > 0.01
                || abs($broadsheet->bf - $newBf) > 0.01
                || abs($broadsheet->cum - $newCum) > 0.01
                || $broadsheet->grade !== $newGrade
                || $broadsheet->remark !== $newRemark;

            if ($changed) {
                $broadsheet->total  = $totalRaw;
                $broadsheet->bf     = $newBf;
                $broadsheet->cum    = $newCum;
                $broadsheet->grade  = $newGrade;
                $broadsheet->remark = $newRemark;
                $broadsheet->save();
            }
        }
    }

    // =========================================================================
    // getBroadsheets — sorted ascending by lastname then firstname
    // =========================================================================

    protected function getBroadsheets($staffId, $termId, $sessionId, $schoolClassId = null, $subjectClassId = null)
    {
        $query = Broadsheets::query()
            ->where('broadsheets.staff_id', $staffId)
            ->where('broadsheets.term_id', $termId)
            ->with(['assessmentScores', 'subAssessmentScores'])
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
            ->join('subjectclass', function ($join) use ($subjectClassId) {
                $join->on('subjectclass.id', '=', 'broadsheets.subjectclass_id')
                    ->on('broadsheet_records.subject_id', '=', 'subjectclass.subjectid')
                    ->on('broadsheet_records.schoolclass_id', '=', 'subjectclass.schoolclassid');
                if ($subjectClassId) $join->where('subjectclass.id', $subjectClassId);
            })
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->leftJoin('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'broadsheet_records.schoolclass_id')
            ->leftJoin('classcategories', 'classcategories.id', '=', 'schoolclass.classcategoryid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'broadsheets.term_id')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'broadsheet_records.session_id')
            ->where('broadsheet_records.session_id', $sessionId)
            ->orderBy('studentRegistration.lastname', 'asc')
            ->orderBy('studentRegistration.firstname', 'asc');

        if ($schoolClassId) $query->where('schoolclass.id', $schoolClassId);

        return $query->get([
            'broadsheets.id',
            'studentRegistration.admissionNO as admissionno',
            'broadsheet_records.student_id as student_id',
            'studentRegistration.firstname as fname',
            'studentRegistration.lastname as lname',
            'studentRegistration.othername as mname',
            'subject.subject as subject',
            'subject.subject_code as subject_code',
            'broadsheet_records.subject_id',
            'schoolclass.schoolclass',
            'schoolclass.id as schoolclass_id',
            'schoolarm.arm',
            'schoolterm.term',
            'schoolsession.session',
            'subjectclass.id as subjectclid',
            'broadsheets.staff_id',
            'broadsheets.term_id',
            'broadsheet_records.session_id as sessionid',
            'studentpicture.picture',
            'broadsheets.total',
            'broadsheets.bf',
            'broadsheets.cum',
            'broadsheets.grade',
            'broadsheets.subject_position_class as position',
            'broadsheets.remark',
            'broadsheets.vettedstatus',
        ]);
    }

    protected function getSubassessmentBroadsheets($staffId, $termId, $sessionId, $schoolClassId = null, $subjectClassId = null, $subassessmentId = null)
    {
        $query = Broadsheets::query()
            ->where('broadsheets.staff_id', $staffId)
            ->where('broadsheets.term_id', $termId)
            ->with(['subAssessmentScores'])
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
            ->join('subjectclass', function ($join) use ($subjectClassId) {
                $join->on('subjectclass.id', '=', 'broadsheets.subjectclass_id')
                    ->on('broadsheet_records.subject_id', '=', 'subjectclass.subjectid')
                    ->on('broadsheet_records.schoolclass_id', '=', 'subjectclass.schoolclassid');
                if ($subjectClassId) $join->where('subjectclass.id', $subjectClassId);
            })
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->leftJoin('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'broadsheet_records.schoolclass_id')
            ->leftJoin('classcategories', 'classcategories.id', '=', 'schoolclass.classcategoryid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'broadsheets.term_id')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'broadsheet_records.session_id')
            ->where('broadsheet_records.session_id', $sessionId)
            ->orderBy('studentRegistration.lastname', 'asc')
            ->orderBy('studentRegistration.firstname', 'asc');

        if ($schoolClassId) $query->where('schoolclass.id', $schoolClassId);

        return $query->get([
            'broadsheets.id', 'studentRegistration.admissionNO as admissionno',
            'broadsheet_records.student_id as student_id', 'studentRegistration.firstname as fname',
            'studentRegistration.lastname as lname', 'studentRegistration.othername as mname',
            'subject.subject', 'subject.subject_code', 'broadsheet_records.subject_id',
            'schoolclass.schoolclass', 'schoolclass.id as schoolclass_id', 'schoolarm.arm',
            'schoolterm.term', 'schoolsession.session', 'subjectclass.id as subjectclid',
            'broadsheets.staff_id', 'broadsheets.term_id', 'broadsheet_records.session_id as sessionid',
            'studentpicture.picture', 'broadsheets.total', 'broadsheets.bf', 'broadsheets.cum',
            'broadsheets.grade', 'broadsheets.subject_position_class as position', 'broadsheets.remark', 'broadsheets.vettedstatus',
        ]);
    }

    // =========================================================================
    // POSITION / METRICS HELPERS
    // =========================================================================

    protected function updateClassMetrics($subjectclassid, $staffid, $termid, $sessionid)
    {
        $subjectClass   = DB::table('subjectclass')->where('id', $subjectclassid)->first(['subjectteacherid']);
        if (!$subjectClass) return;
        $subjectTeacher = DB::table('subjectteacher')->where('id', $subjectClass->subjectteacherid)->first(['subjectid']);
        if (!$subjectTeacher) return;
        $subjectId = $subjectTeacher->subjectid;

        $metrics = Broadsheets::where('broadsheets.subjectclass_id', $subjectclassid)
            ->where('broadsheets.staff_id', $staffid)
            ->where('broadsheets.term_id', $termid)
            ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
            ->where('broadsheet_records.session_id', $sessionid)
            ->where('broadsheet_records.subject_id', $subjectId)
            ->select([
                DB::raw('MIN(broadsheets.cum) as class_min'),
                DB::raw('MAX(broadsheets.cum) as class_max'),
                DB::raw('SUM(broadsheets.cum) as cum_sum'),
                DB::raw('COUNT(broadsheets.id) as student_count'),
            ])->first();

        $classMin = $metrics->class_min ?? 0;
        $classMax = $metrics->class_max ?? 0;
        $classAvg = $metrics->student_count > 0 ? round($metrics->cum_sum / $metrics->student_count, 1) : 0;

        Broadsheets::where('subjectclass_id', $subjectclassid)
            ->where('staff_id', $staffid)
            ->where('term_id', $termid)
            ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
            ->where('broadsheet_records.session_id', $sessionid)
            ->where('broadsheet_records.subject_id', $subjectId)
            ->update(['cmin' => $classMin, 'cmax' => $classMax, 'avg' => $classAvg]);
    }

    protected function updateSubjectPositions($subjectclass_id, $staff_id, $term_id, $session_id)
    {
        $broadsheets = Broadsheets::where('subjectclass_id', $subjectclass_id)
            ->where('staff_id', $staff_id)->where('term_id', $term_id)
            ->where('broadsheet_records.session_id', $session_id)
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
            ->orderByDesc('broadsheets.cum')->orderBy('broadsheets.id')->get();

        $rank = 0; $lastCum = null; $lastPosition = 0;
        foreach ($broadsheets as $b) {
            $rank++;
            if ($lastCum === null || $b->cum != $lastCum) { $lastPosition = $rank; $lastCum = $b->cum; }
            if ($b->subject_position_class != $lastPosition) {
                $b->subject_position_class = $lastPosition;
                $b->save();
            }
        }
    }

    protected function updateClassPositions($schoolclassid, $termid, $sessionid)
    {
        $rank = 0; $lastScore = null; $rows = 0;
        $pos = PromotionStatus::where('schoolclassid', $schoolclassid)
            ->where('termid', $termid)->where('sessionid', $sessionid)
            ->orderBy('subjectstotalscores', 'DESC')->get();

        foreach ($pos as $row) {
            $rows++;
            if ($lastScore !== $row->subjectstotalscores) { $lastScore = $row->subjectstotalscores; $rank = $rows; }
            $suffix = match ($rank) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' };
            PromotionStatus::where('id', $row->id)->update(['position' => $rank . $suffix]);
        }
    }

    // =========================================================================
    // GRADING HELPERS
    // =========================================================================

    protected function getDefaultGrade($score)
    {
        if ($score >= 70) return 'A';
        if ($score >= 60) return 'B';
        if ($score >= 50) return 'C';
        if ($score >= 40) return 'D';
        return 'F';
    }

    protected function getRemark($grade)
    {
        return match($grade) {
            'A', 'A1'       => 'Excellent',
            'B', 'B2', 'B3' => 'Very Good',
            'C', 'C4', 'C5', 'C6' => 'Good',
            'D', 'D7', 'E8' => 'Pass',
            default         => 'Fail',
        };
    }

    protected function getPreviousTermCum($studentId, $subjectId, $termId, $sessionId)
    {
        if ($termId == 1) return 0;
        $prev = Broadsheets::where('broadsheet_records.student_id', $studentId)
            ->where('broadsheet_records.subject_id', $subjectId)
            ->where('broadsheets.term_id', $termId - 1)
            ->where('broadsheet_records.session_id', $sessionId)
            ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
            ->value('broadsheets.cum');
        return $prev ? round($prev, 2) : 0;
    }

    public function calculateGradePreview(Request $request)
    {
        $request->validate(['schoolclass_id' => 'required|exists:schoolclass,id', 'cum' => 'required|numeric|min:0|max:100']);
        $schoolclass = Schoolclass::with('classcategories')->findOrFail($request->schoolclass_id);
        $grade = $schoolclass->classcategories->isNotEmpty()
            ? $schoolclass->classcategories->first()->calculateGrade($request->cum)
            : $this->getDefaultGrade($request->cum);
        return response()->json(['grade' => $grade]);
    }
}
