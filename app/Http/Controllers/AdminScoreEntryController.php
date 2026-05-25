<?php
// app/Http/Controllers/Admin/AdminScoreEntryController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schoolterm;
use App\Models\Schoolsession;
use App\Models\SubjectTeacher;
use App\Models\Schoolclass;
use App\Models\Subjectclass;
use App\Models\Broadsheets;
use App\Models\BroadsheetRecord;
use App\Models\Assessment;
use App\Models\BroadsheetAssessmentScore;
use App\Models\BroadsheetSubAssessmentScore;
use App\Models\SubAssessment;
use App\Models\PromotionStatus;
use App\Models\User;
use App\Models\Classcategory;
use App\Models\SchoolInformation;
use App\Exports\AdminRecordsheetExport;
use App\Imports\AdminScoresheetImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminScoreEntryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View admin-score-entry|Create admin-score-entr|Update admin-score-entry|Delete admin-score-entry')->only(['index']);
        $this->middleware('permission:Create admin-score-entry')->only(['create', 'store']);
        $this->middleware('permission:Update admin-score-entry')->only(['edit', 'update', 'bulkUpdate', 'singleUpdate']);
        $this->middleware('permission:Delete admin-score-entry')->only(['destroy']);
    }

    /**
     * Display list of subject teachers with their assignments
     */
    public function index(Request $request)
    {
        $pagetitle = "Admin Score Entry - Teacher Subjects";
        $terms = Schoolterm::orderBy('id')->get();
        $sessions = Schoolsession::orderBy('id', 'desc')->get();

        $teacherSubjects = collect();
        $selectedTermId = $request->get('termid');
        $selectedSessionId = $request->get('sessionid');

        if ($selectedTermId && $selectedSessionId) {
            $teacherSubjects = $this->getTeacherSubjects($selectedTermId, $selectedSessionId);
        }

        return view('admin.score-entry.index', compact(
            'pagetitle', 'terms', 'sessions', 'teacherSubjects',
            'selectedTermId', 'selectedSessionId'
        ));
    }

    /**
     * Get teacher subjects with their assignments
     */
    protected function getTeacherSubjects($termId, $sessionId)
    {
        return SubjectTeacher::query()
            ->join('users', 'users.id', '=', 'subjectteacher.staffid')
            ->join('subjectclass', 'subjectclass.subjectteacherid', '=', 'subjectteacher.id')
            ->join('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
            ->join('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->join('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->leftJoin('schoolclass_classcategory', 'schoolclass_classcategory.schoolclass_id', '=', 'schoolclass.id')
            ->leftJoin('classcategories', 'classcategories.id', '=', 'schoolclass_classcategory.classcategory_id')
            ->where('subjectteacher.termid', $termId)
            ->where('subjectteacher.sessionid', $sessionId)
            ->whereNotNull('subjectclass.id')
            ->select(
                'subjectteacher.id as subjectteacher_id',
                'users.id as teacher_id',
                'users.name as teacher_name',
                'subject.subject as subject_name',
                'subject.subject_code',
                'subjectclass.id as subjectclass_id',
                'schoolclass.id as schoolclass_id',
                DB::raw("CONCAT(schoolclass.schoolclass, ' ', COALESCE(schoolarm.arm, '')) as class_name"),
                DB::raw("GROUP_CONCAT(DISTINCT classcategories.category ORDER BY classcategories.category SEPARATOR ', ') as class_categories"),
                'schoolclass.classcategoryid',
                'subjectteacher.termid',
                'subjectteacher.sessionid',
                'schoolterm.term as term_name',
                'schoolsession.session as session_name'
            )
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'subjectteacher.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'subjectteacher.sessionid')
            ->groupBy(
                'subjectteacher.id', 'users.id', 'users.name', 'subject.subject',
                'subject.subject_code', 'subjectclass.id', 'schoolclass.id', 'class_name',
                'schoolclass.classcategoryid', 'subjectteacher.termid', 'subjectteacher.sessionid',
                'schoolterm.term', 'schoolsession.session'
            )
            ->orderBy('users.name')
            ->orderBy('schoolclass.schoolclass')
            ->orderBy('schoolarm.arm')
            ->get()
            ->map(function ($item) {
                // Check if terminal scores exist
                $item->has_terminal_scores = Broadsheets::where('subjectclass_id', $item->subjectclass_id)
                    ->where('staff_id', $item->teacher_id)
                    ->where('term_id', $item->termid)
                    ->exists();

                // Check if mock scores exist
                $item->has_mock_scores = \App\Models\BroadsheetsMock::where('subjectclass_id', $item->subjectclass_id)
                    ->where('staff_id', $item->teacher_id)
                    ->where('term_id', $item->termid)
                    ->exists();

                return $item;
            });
    }

    /**
     * Display scoresheet for a specific teacher subject
     */
    public function showScoresheet($subjectclassId, $teacherId, $termId, $sessionId, $type = 'terminal')
    {
        session([
            'admin_score_entry_subjectclass_id' => $subjectclassId,
            'admin_score_entry_teacher_id' => $teacherId,
            'admin_score_entry_term_id' => $termId,
            'admin_score_entry_session_id' => $sessionId,
            'admin_score_entry_type' => $type,
        ]);

        if ($type === 'mock') {
            return $this->showMockScoresheet($subjectclassId, $teacherId, $termId, $sessionId);
        }

        // Get subjectclass details
        $subjectClass = Subjectclass::with(['subject', 'schoolclass.arm', 'schoolclass.classcategories'])
            ->findOrFail($subjectclassId);

        $teacher = User::findOrFail($teacherId);
        $term = Schoolterm::findOrFail($termId);
        $session = Schoolsession::findOrFail($sessionId);

        // Get broadsheets
        $broadsheets = $this->getBroadsheets($teacherId, $termId, $sessionId, $subjectClass->schoolclass_id, $subjectclassId);

        $schoolclass = $subjectClass->schoolclass;
        $assessments = collect();

        if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            $categoryIds = $schoolclass->classcategories->pluck('id');
            $assessments = Assessment::whereIn('classcategory_id', $categoryIds)
                ->with('subAssessments')
                ->orderBy('id')
                ->get();

            // Update metrics and positions
            $this->updateClassMetrics($subjectclassId, $teacherId, $termId, $sessionId);
            $this->computeDynamicTotals($broadsheets, $assessments, $schoolclass, $termId, $sessionId);
            $this->updateSubjectPositions($subjectclassId, $teacherId, $termId, $sessionId);
            $this->updateClassPositions($schoolclass->id, $termId, $sessionId);

            // Refresh broadsheets
            $broadsheets = $this->getBroadsheets($teacherId, $termId, $sessionId, $schoolclass->id, $subjectclassId);
            $this->computeOverallGPAAndCGPA($broadsheets, $schoolclass, $termId, $sessionId);
        }

        $pagetitle = sprintf(
            'Admin: %s - %s (%s) | %s | %s %s',
            $teacher->name,
            $subjectClass->subject->subject,
            $subjectClass->subject->subject_code,
            $schoolclass->schoolclass . ' ' . ($schoolclass->arm->arm ?? ''),
            $term->term,
            $session->session
        );

        $is_senior = $schoolclass && $schoolclass->classcategories->isNotEmpty()
            ? $schoolclass->classcategories->first()->is_senior ?? false
            : false;

        return view('admin.score-entry.scoresheet', compact(
            'broadsheets', 'pagetitle', 'is_senior', 'assessments',
            'subjectclassId', 'teacherId', 'termId', 'sessionId',
            'teacher', 'subjectClass', 'term', 'session', 'schoolclass'
        ));
    }

    /**
     * Display mock scoresheet
     */
    public function showMockScoresheet($subjectclassId, $teacherId, $termId, $sessionId)
    {
        $subjectClass = Subjectclass::with(['subject', 'schoolclass.arm'])->findOrFail($subjectclassId);
        $teacher = User::findOrFail($teacherId);
        $term = Schoolterm::findOrFail($termId);
        $session = Schoolsession::findOrFail($sessionId);

        $broadsheets = $this->getMockBroadsheets($teacherId, $termId, $sessionId, $subjectClass->schoolclass_id, $subjectclassId);
        $schoolclass = $subjectClass->schoolclass;

        if ($broadsheets->isNotEmpty()) {
            $this->updateMockClassMetrics($subjectclassId, $teacherId, $termId, $sessionId);
            $this->updateMockSubjectPositions($subjectclassId, $teacherId, $termId, $sessionId);
            $broadsheets = $this->getMockBroadsheets($teacherId, $termId, $sessionId, $schoolclass->id, $subjectclassId);
        }

        $pagetitle = sprintf(
            'Admin Mock: %s - %s (%s) | %s | %s %s',
            $teacher->name,
            $subjectClass->subject->subject,
            $subjectClass->subject->subject_code,
            $schoolclass->schoolclass . ' ' . ($schoolclass->arm->arm ?? ''),
            $term->term,
            $session->session
        );

        $is_senior = $schoolclass && $schoolclass->classcategories->isNotEmpty()
            ? $schoolclass->classcategories->first()->is_senior ?? false
            : false;

        return view('admin.score-entry.mock-scoresheet', compact(
            'broadsheets', 'pagetitle', 'is_senior', 'subjectclassId',
            'teacherId', 'termId', 'sessionId', 'teacher', 'subjectClass',
            'term', 'session', 'schoolclass'
        ));
    }

    /**
     * Single score update (terminal)
     */
    public function singleUpdate(Request $request)
    {
        try {
            $validated = $request->validate([
                'broadsheet_id' => 'required|exists:broadsheets,id',
                'assessment_id' => 'required|exists:assessments,id',
                'score' => 'required|numeric|min:0',
                'is_sub' => 'boolean',
                'sub_assessment_id' => 'nullable|exists:sub_assessments,id',
            ]);

            $broadsheetId = $validated['broadsheet_id'];
            $assessmentId = $validated['assessment_id'];
            $score = $validated['score'];
            $isSub = $validated['is_sub'] ?? false;
            $subAssessmentId = $validated['sub_assessment_id'] ?? null;

            if ($isSub && !$subAssessmentId) {
                return response()->json(['success' => false, 'message' => 'Sub-assessment ID required.'], 422);
            }

            $broadsheet = Broadsheets::findOrFail($broadsheetId);
            $model = $isSub ? SubAssessment::findOrFail($subAssessmentId) : Assessment::findOrFail($assessmentId);

            if ($score > $model->max_score) {
                return response()->json(['success' => false, 'message' => "Score cannot exceed maximum of {$model->max_score}."], 422);
            }

            $broadsheetRecord = BroadsheetRecord::find($broadsheet->broadSheet_record_id);
            $schoolclassId = $broadsheetRecord?->schoolclass_id ?? 0;
            $sessionId = $broadsheetRecord?->session_id ?? session('admin_score_entry_session_id');
            $termId = $broadsheet->term_id ?? session('admin_score_entry_term_id');

            $schoolclass = Schoolclass::with('classcategories')->find($schoolclassId);

            DB::transaction(function () use (
                $broadsheetId, $assessmentId, $score, $broadsheet,
                $isSub, $subAssessmentId, $broadsheetRecord, $schoolclass, $sessionId
            ) {
                if ($isSub) {
                    BroadsheetSubAssessmentScore::updateOrCreate(
                        [
                            'broadsheet_id' => $broadsheetId,
                            'sub_assessment_id' => $subAssessmentId,
                            'assessment_id' => $assessmentId,
                        ],
                        ['score' => $score]
                    );

                    $assessment = Assessment::with('subAssessments')->find($assessmentId);
                    if ($assessment) {
                        $subMaxSum = $assessment->subAssessments->sum('max_score');
                        $subTotal = BroadsheetSubAssessmentScore::where('broadsheet_id', $broadsheetId)
                            ->where('assessment_id', $assessmentId)
                            ->sum('score');
                        $normalized = $subMaxSum > 0
                            ? ($subTotal / $subMaxSum) * $assessment->max_score
                            : 0;
                        BroadsheetAssessmentScore::updateOrCreate(
                            ['broadsheet_id' => $broadsheetId, 'assessment_id' => $assessmentId],
                            ['score' => max(0, min($normalized, $assessment->max_score))]
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
                    $assessments = Assessment::whereIn('classcategory_id', $categoryIds)
                        ->with('subAssessments')
                        ->get();
                }

                $broadsheet->load(['assessmentScores', 'subAssessmentScores']);
                $this->computeDynamicTotals(
                    collect([$broadsheet]), $assessments, $schoolclass,
                    $broadsheet->term_id, $sessionId
                );
            });

            $this->updateClassMetrics($broadsheet->subjectclass_id, $broadsheet->staff_id, $termId, $sessionId);
            $this->updateSubjectPositions($broadsheet->subjectclass_id, $broadsheet->staff_id, $termId, $sessionId);
            $this->updateClassPositions($schoolclassId, $termId, $sessionId);

            $isSenior = $schoolclass && $schoolclass->classcategories->isNotEmpty()
                ? $schoolclass->classcategories->first()->is_senior ?? false : false;

            $gpaCgpaData = $this->computeOverallForStudent(
                $broadsheetRecord->student_id ?? 0, $schoolclass, $termId, $sessionId, $isSenior
            );

            $broadsheet->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Score updated successfully!',
                'data' => [
                    'total' => $broadsheet->total,
                    'cum' => $broadsheet->cum,
                    'bf' => $broadsheet->bf,
                    'grade' => $broadsheet->grade,
                    'remark' => $broadsheet->remark,
                    'subject_position_class' => $broadsheet->subject_position_class,
                    'subject_position_class_total' => $broadsheet->subject_position_class_total,
                    'arm_position' => $broadsheet->arm_position,
                    'arm_position_cum' => $broadsheet->arm_position_cum,
                    'gpa' => round($gpaCgpaData['gpa'], 2),
                    'gpa_grade' => $gpaCgpaData['gpa_grade'] ?? 'F',
                    'cgpa' => round($gpaCgpaData['cgpa'], 2),
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Admin singleUpdateScore error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mock single update
     */
    public function mockSingleUpdate(Request $request)
    {
        try {
            $validated = $request->validate([
                'broadsheet_id' => 'required|exists:broadsheetmock,id',
                'exam' => 'required|numeric|min:0|max:100',
            ]);

            $broadsheetId = $validated['broadsheet_id'];
            $examScore = (float) $validated['exam'];

            $broadsheet = \App\Models\BroadsheetsMock::findOrFail($broadsheetId);
            $mockRecord = \App\Models\BroadsheetRecordMock::find($broadsheet->broadsheet_records_mock_id);
            $schoolclassId = $mockRecord?->schoolclass_id ?? 0;
            $sessionId = $mockRecord?->session_id ?? session('admin_score_entry_session_id');
            $schoolclass = Schoolclass::with('classcategories')->find($schoolclassId);

            $examScore = max(0, min($examScore, 100));
            $total = round($examScore, 2);

            $grade = $schoolclass && $schoolclass->classcategories->isNotEmpty()
                ? $schoolclass->classcategories->first()->calculateGrade($total)
                : $this->getDefaultGrade($total);
            $remark = $this->getRemark($grade);

            $broadsheet->exam = $examScore;
            $broadsheet->total = $total;
            $broadsheet->grade = $grade;
            $broadsheet->remark = $remark;
            $broadsheet->save();

            $this->updateMockClassMetrics($broadsheet->subjectclass_id, $broadsheet->staff_id, $broadsheet->term_id, $sessionId);
            $this->updateMockSubjectPositions($broadsheet->subjectclass_id, $broadsheet->staff_id, $broadsheet->term_id, $sessionId);

            $broadsheet->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Score updated successfully!',
                'data' => [
                    'total' => $broadsheet->total,
                    'grade' => $broadsheet->grade,
                    'remark' => $broadsheet->remark,
                    'position' => $broadsheet->subject_position_class,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Admin mockSingleUpdate error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to save: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Bulk update scores
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'scores' => 'required|array',
            'scores.*.id' => 'required|exists:broadsheets,id',
            'scores.*.assessments' => 'sometimes|array',
            'term_id' => 'required|exists:schoolterm,id',
            'session_id' => 'required|exists:schoolsession,id',
            'subjectclass_id' => 'required|exists:subjectclass,id',
            'staff_id' => 'required|exists:users,id',
            'schoolclass_id' => 'required|exists:schoolclass,id',
            'assessment_id' => 'nullable|exists:assessments,id',
            'is_sub' => 'nullable|boolean',
        ]);

        $scores = $validated['scores'];
        $term_id = $validated['term_id'];
        $session_id = $validated['session_id'];
        $subjectclass_id = $validated['subjectclass_id'];
        $staff_id = $validated['staff_id'];
        $schoolclass_id = $validated['schoolclass_id'];
        $assessment_id = $validated['assessment_id'] ?? null;
        $is_sub = (bool) ($validated['is_sub'] ?? false);

        $schoolclass = Schoolclass::with('classcategories')->find($schoolclass_id);
        if (!$schoolclass) {
            return response()->json(['success' => false, 'message' => 'School class not found'], 404);
        }

        $assessments = collect();
        if ($schoolclass->classcategories->isNotEmpty()) {
            $categoryIds = $schoolclass->classcategories->pluck('id');
            $assessments = Assessment::whereIn('classcategory_id', $categoryIds)
                ->with('subAssessments')
                ->get();
        }

        $updatedCount = 0;
        $errors = [];

        DB::transaction(function () use (
            $scores, $term_id, $session_id, $subjectclass_id, $staff_id, $schoolclass_id,
            $schoolclass, $assessments, $is_sub, $assessment_id, &$updatedCount, &$errors
        ) {
            foreach ($scores as $scoreData) {
                $broadsheetId = $scoreData['id'];
                $broadsheet = Broadsheets::find($broadsheetId);
                if (!$broadsheet) {
                    $errors[] = "Broadsheet ID {$broadsheetId} not found.";
                    continue;
                }

                $assessmentsData = $scoreData['assessments'] ?? [];
                if (empty($assessmentsData)) continue;

                if ($is_sub && $assessment_id) {
                    $parentAssessment = $assessments->where('id', $assessment_id)->first()
                        ?? Assessment::with('subAssessments')->find($assessment_id);

                    foreach ($assessmentsData as $subId => $inputScore) {
                        $subId = (int) $subId;
                        $inputScore = max(0, (float) $inputScore);

                        $subModel = SubAssessment::find($subId);
                        if (!$subModel || $subModel->assessment_id != $assessment_id) {
                            continue;
                        }

                        \App\Models\BroadsheetSubAssessmentScore::updateOrCreate(
                            [
                                'broadsheet_id' => $broadsheetId,
                                'sub_assessment_id' => $subId,
                                'assessment_id' => $assessment_id,
                            ],
                            ['score' => min($inputScore, $subModel->max_score)]
                        );
                    }

                    if ($parentAssessment) {
                        $subMaxSum = $parentAssessment->subAssessments->sum('max_score');
                        $subTotal = \App\Models\BroadsheetSubAssessmentScore::where('broadsheet_id', $broadsheetId)
                            ->where('assessment_id', $assessment_id)
                            ->sum('score');
                        $normalized = $subMaxSum > 0
                            ? ($subTotal / $subMaxSum) * $parentAssessment->max_score
                            : 0;

                        BroadsheetAssessmentScore::updateOrCreate(
                            ['broadsheet_id' => $broadsheetId, 'assessment_id' => $assessment_id],
                            ['score' => max(0, min($normalized, $parentAssessment->max_score))]
                        );
                    }
                } else {
                    foreach ($assessmentsData as $componentId => $inputScore) {
                        $componentId = (int) $componentId;
                        $inputScore = max(0, (float) $inputScore);

                        $model = $assessments->where('id', $componentId)->first();
                        if (!$model) continue;

                        BroadsheetAssessmentScore::updateOrCreate(
                            ['broadsheet_id' => $broadsheetId, 'assessment_id' => $componentId],
                            ['score' => min($inputScore, $model->max_score)]
                        );
                    }
                }

                $broadsheet->load(['assessmentScores', 'subAssessmentScores']);
                $this->computeDynamicTotals(
                    collect([$broadsheet]), $assessments, $schoolclass, $term_id, $session_id
                );
                $updatedCount++;
            }

            $this->updateClassMetrics($subjectclass_id, $staff_id, $term_id, $session_id);
        });

        $this->updateSubjectPositions($subjectclass_id, $staff_id, $term_id, $session_id);
        $this->updateClassPositions($schoolclass_id, $term_id, $session_id);

        $updatedBroadsheets = $this->getBroadsheets(
            $staff_id, $term_id, $session_id, $schoolclass_id, $subjectclass_id
        );
        $this->computeOverallGPAAndCGPA($updatedBroadsheets, $schoolclass, $term_id, $session_id);

        $response = [
            'success' => true,
            'message' => "{$updatedCount} score(s) updated!",
            'data' => ['broadsheets' => $updatedBroadsheets, 'assessments' => $assessments],
        ];
        if (!empty($errors)) $response['warnings'] = $errors;

        return response()->json($response, 200);
    }

    /**
     * Mock bulk update
     */
    public function mockBulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'scores' => 'required|array',
            'scores.*.id' => 'required|exists:broadsheetmock,id',
            'scores.*.exam' => 'nullable|numeric|min:0|max:100',
            'term_id' => 'required|exists:schoolterm,id',
            'session_id' => 'required|exists:schoolsession,id',
            'subjectclass_id' => 'required|exists:subjectclass,id',
            'staff_id' => 'required|exists:users,id',
            'schoolclass_id' => 'required|exists:schoolclass,id',
        ]);

        $scores = $validated['scores'];
        $term_id = $validated['term_id'];
        $session_id = $validated['session_id'];
        $subjectclass_id = $validated['subjectclass_id'];
        $staff_id = $validated['staff_id'];
        $schoolclass_id = $validated['schoolclass_id'];

        $schoolclass = Schoolclass::with('classcategories')->find($schoolclass_id);

        $updatedCount = 0;

        DB::transaction(function () use ($scores, $session_id, $schoolclass, &$updatedCount) {
            foreach ($scores as $scoreData) {
                $broadsheetId = $scoreData['id'];
                $examScore = isset($scoreData['exam']) ? (float) $scoreData['exam'] : 0;

                $broadsheet = \App\Models\BroadsheetsMock::find($broadsheetId);
                if (!$broadsheet) continue;

                $examScore = max(0, min($examScore, 100));
                $total = round($examScore, 2);

                $grade = $schoolclass && $schoolclass->classcategories->isNotEmpty()
                    ? $schoolclass->classcategories->first()->calculateGrade($total)
                    : $this->getDefaultGrade($total);
                $remark = $this->getRemark($grade);

                $broadsheet->exam = $examScore;
                $broadsheet->total = $total;
                $broadsheet->grade = $grade;
                $broadsheet->remark = $remark;
                $broadsheet->save();

                $updatedCount++;
            }
        });

        $this->updateMockClassMetrics($subjectclass_id, $staff_id, $term_id, $session_id);
        $this->updateMockSubjectPositions($subjectclass_id, $staff_id, $term_id, $session_id);

        $updatedBroadsheets = $this->getMockBroadsheets($staff_id, $term_id, $session_id, $schoolclass_id, $subjectclass_id);

        return response()->json([
            'success' => true,
            'message' => "{$updatedCount} mock score(s) updated!",
            'data' => ['broadsheets' => $updatedBroadsheets],
        ]);
    }

    /**
     * Delete a score record
     */
    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $type = $request->input('type', 'terminal');

        if ($type === 'mock') {
            $broadsheet = \App\Models\BroadsheetsMock::findOrFail($id);
            $subjectclassid = $broadsheet->subjectclass_id;
            $staffid = $broadsheet->staff_id;
            $termid = $broadsheet->term_id;
            $mockRecord = \App\Models\BroadsheetRecordMock::find($broadsheet->broadsheet_records_mock_id);

            $broadsheet->delete();

            if ($mockRecord) {
                $this->updateMockClassMetrics($subjectclassid, $staffid, $termid, $mockRecord->session_id);
                $this->updateMockSubjectPositions($subjectclassid, $staffid, $termid, $mockRecord->session_id);
            }
        } else {
            $broadsheet = Broadsheets::findOrFail($id);
            $subjectclassid = $broadsheet->subjectclass_id;
            $staffid = $broadsheet->staff_id;
            $termid = $broadsheet->term_id;
            $broadsheetRecord = BroadsheetRecord::find($broadsheet->broadSheet_record_id);

            BroadsheetAssessmentScore::where('broadsheet_id', $id)->delete();
            \App\Models\BroadsheetSubAssessmentScore::where('broadsheet_id', $id)->delete();
            $broadsheet->delete();

            if ($broadsheetRecord) {
                $this->updateClassMetrics($subjectclassid, $staffid, $termid, $broadsheetRecord->session_id);
                $this->updateSubjectPositions($subjectclassid, $staffid, $termid, $broadsheetRecord->session_id);
                $this->updateClassPositions($broadsheetRecord->schoolclass_id, $termid, $broadsheetRecord->session_id);
            }
        }

        return response()->json(['success' => true, 'message' => 'Score deleted successfully!']);
    }

    /**
     * Get results for AJAX refresh
     */
    public function results(Request $request)
    {
        try {
            $subjectclass_id = session('admin_score_entry_subjectclass_id');
            $schoolclass_id = session('admin_score_entry_schoolclass_id') ?? $request->get('schoolclass_id');
            $term_id = session('admin_score_entry_term_id');
            $session_id = session('admin_score_entry_session_id');
            $type = session('admin_score_entry_type', 'terminal');

            if (!$subjectclass_id || !$term_id || !$session_id) {
                return response()->json(['success' => false, 'message' => 'Missing session data', 'scores' => []], 400);
            }

            if ($type === 'mock') {
                $broadsheets = $this->getMockBroadsheets(null, $term_id, $session_id, $schoolclass_id, $subjectclass_id);
                $scoresData = $broadsheets->map(fn($b) => [
                    'id' => $b->id,
                    'admissionno' => $b->admissionno,
                    'fname' => $b->fname,
                    'lname' => $b->lname,
                    'exam' => $b->exam,
                    'total' => $b->total,
                    'grade' => $b->grade,
                    'remark' => $b->remark,
                    'position' => $b->position,
                ]);
                return response()->json(['success' => true, 'scores' => $scoresData, 'type' => 'mock']);
            }

            $schoolclass = Schoolclass::with('classcategories')->find($schoolclass_id);
            $assessments = collect();
            if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
                $categoryIds = $schoolclass->classcategories->pluck('id');
                $assessments = Assessment::whereIn('classcategory_id', $categoryIds)->with('subAssessments')->orderBy('id')->get();
            }

            $broadsheets = Broadsheets::where('subjectclass_id', $subjectclass_id)
                ->where('term_id', $term_id)
                ->with(['assessmentScores', 'subAssessmentScores'])
                ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
                ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
                ->where('broadsheet_records.session_id', $session_id)
                ->orderBy('studentRegistration.lastname')
                ->orderBy('studentRegistration.firstname')
                ->get([
                    'broadsheets.id',
                    'studentRegistration.admissionNO as admissionno',
                    'studentRegistration.firstname as fname',
                    'studentRegistration.lastname as lname',
                    'broadsheets.total',
                    'broadsheets.bf',
                    'broadsheets.cum',
                    'broadsheets.grade',
                    'broadsheets.avg',
                    'broadsheets.subject_position_class as position',
                    'broadsheets.subject_position_class_total as position_total',
                    'broadsheets.arm_position',
                    'broadsheets.arm_position_cum',
                    'broadsheets.term_id',
                ]);

            $this->computeOverallGPAAndCGPA($broadsheets, $schoolclass, $term_id, $session_id);

            $scoresData = $broadsheets->map(function ($b) use ($assessments) {
                $assessmentData = [];
                foreach ($assessments as $a) {
                    $s = $b->assessmentScores->where('assessment_id', $a->id)->first();
                    $assessmentData[$a->id] = [
                        'name' => $a->name,
                        'max_score' => $a->max_score,
                        'score' => $s ? $s->score : 0
                    ];
                }
                return [
                    'id' => $b->id,
                    'admissionno' => $b->admissionno,
                    'fname' => $b->fname,
                    'lname' => $b->lname,
                    'assessments' => $assessmentData,
                    'total' => $b->total,
                    'bf' => $b->bf,
                    'cum' => $b->cum,
                    'avg' => $b->avg ?? 0,
                    'gpa' => $b->gpa ?? 0,
                    'gpa_grade' => $b->gpa_grade ?? 'F',
                    'cgpa' => $b->cgpa ?? 0,
                    'grade' => $b->grade,
                    'position' => $b->position,
                    'position_total' => $b->position_total,
                    'arm_position' => $b->arm_position,
                    'arm_position_cum' => $b->arm_position_cum,
                ];
            });

            return response()->json(['success' => true, 'assessments' => $assessments, 'scores' => $scoresData]);
        } catch (\Exception $e) {
            Log::error('Admin results error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Internal server error.'], 500);
        }
    }

    /**
     * Download marks sheet PDF
     */
    public function downloadMarksSheet(Request $request)
    {
        try {
            $subjectclassid = $request->input('subjectclass_id', session('admin_score_entry_subjectclass_id'));
            $staffid = $request->input('staff_id', session('admin_score_entry_teacher_id'));
            $termid = $request->input('term_id', session('admin_score_entry_term_id'));
            $sessionid = $request->input('session_id', session('admin_score_entry_session_id'));
            $schoolclassid = $request->input('schoolclass_id');
            $type = $request->input('type', session('admin_score_entry_type', 'terminal'));

            if ($type === 'mock') {
                return $this->downloadMockMarksSheet($subjectclassid, $staffid, $termid, $sessionid, $schoolclassid);
            }

            $broadsheets = $this->getBroadsheets($staffid, $termid, $sessionid, $schoolclassid, $subjectclassid);

            if ($broadsheets->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No students found.'], 404);
            }

            $teacher = User::find($staffid);
            $teacherName = $teacher ? ($teacher->name ?? '') : '';

            $schoolclass = Schoolclass::with('classcategories')->find($schoolclassid);
            $assessments = collect();
            if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
                $categoryIds = $schoolclass->classcategories->pluck('id');
                $assessments = Assessment::whereIn('classcategory_id', $categoryIds)->with('subAssessments')->orderBy('id')->get();
            }

            $school = SchoolInformation::first();
            $pdf = Pdf::loadView('admin.score-entry.marksheet-pdf', [
                'broadsheets' => $broadsheets,
                'assessments' => $assessments,
                'classInfo' => $broadsheets->first(),
                'school' => $school,
                'teacherName' => $teacherName,
                'isAdminView' => true,
            ]);
            $pdf->setPaper('a4', 'landscape');

            return $pdf->download('admin-marks-sheet-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Admin marks sheet download error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Download mock marks sheet PDF
     */
    protected function downloadMockMarksSheet($subjectclassid, $staffid, $termid, $sessionid, $schoolclassid)
    {
        $broadsheets = $this->getMockBroadsheets($staffid, $termid, $sessionid, $schoolclassid, $subjectclassid);

        if ($broadsheets->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No mock scores found.'], 404);
        }

        $teacher = User::find($staffid);
        $teacherName = $teacher ? ($teacher->name ?? '') : '';

        $school = SchoolInformation::first();
        $pdf = Pdf::loadView('admin.score-entry.mock-marksheet-pdf', [
            'broadsheets' => $broadsheets,
            'classInfo' => $broadsheets->first(),
            'school' => $school,
            'teacherName' => $teacherName,
            'isAdminView' => true,
        ]);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('admin-mock-marks-sheet-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export to Excel
     */
    public function export(Request $request)
    {
        $schoolclassId = $request->input('schoolclass_id', session('admin_score_entry_schoolclass_id'));
        $subjectclassId = $request->input('subjectclass_id', session('admin_score_entry_subjectclass_id'));
        $termId = $request->input('term_id', session('admin_score_entry_term_id'));
        $sessionId = $request->input('session_id', session('admin_score_entry_session_id'));
        $staffId = $request->input('staff_id', session('admin_score_entry_teacher_id'));

        $subjectClass = Subjectclass::with('subject')->find($subjectclassId);
        $schoolclass = Schoolclass::find($schoolclassId);
        $term = Schoolterm::find($termId);
        $session = Schoolsession::find($sessionId);

        $subjectName = preg_replace('/[^a-zA-Z0-9-]/', '_', $subjectClass?->subject?->subject ?? 'subject');
        $className = preg_replace('/[^a-zA-Z0-9-]/', '_', $schoolclass?->schoolclass ?? 'class');
        $termName = preg_replace('/[^a-zA-Z0-9-]/', '_', $term?->term ?? 'term');
        $sessionName = preg_replace('/[^a-zA-Z0-9-]/', '_', $session?->session ?? 'session');

        $filename = "admin_{$subjectName}_{$className}_{$termName}_{$sessionName}_scoresheet.xlsx";

        $export = new AdminRecordsheetExport($schoolclassId, $subjectclassId, $termId, $sessionId, $staffId);

        return Excel::download($export, $filename);
    }

    /**
     * Import scores from Excel
     */
    public function import(Request $request)
    {
        try {
            $request->validate(['file' => 'required|file|mimes:xlsx,xls']);

            $importData = [
                'subjectclass_id' => $request->input('subjectclass_id', session('admin_score_entry_subjectclass_id')),
                'staff_id' => $request->input('staff_id', session('admin_score_entry_teacher_id')),
                'term_id' => $request->input('term_id', session('admin_score_entry_term_id')),
                'session_id' => $request->input('session_id', session('admin_score_entry_session_id')),
                'schoolclass_id' => $request->input('schoolclass_id', session('admin_score_entry_schoolclass_id')),
            ];

            if (empty($importData['subjectclass_id']) || empty($importData['staff_id'])) {
                return response()->json(['success' => false, 'message' => 'Missing session data.'], 422);
            }

            $importer = new AdminScoresheetImport($importData);
            Excel::import($importer, $request->file('file'));

            $successCount = $importer->getSuccessCount();
            $failures = $importer->getFailures();

            $broadsheets = $this->getBroadsheets(
                $importData['staff_id'],
                $importData['term_id'],
                $importData['session_id'],
                $importData['schoolclass_id'],
                $importData['subjectclass_id']
            );

            if ($successCount === 0 && !empty($failures)) {
                return response()->json(['success' => false, 'message' => 'No records imported.', 'errors' => $failures], 422);
            }

            $responseData = [
                'success' => true,
                'message' => "Successfully imported {$successCount} score(s)!",
                'data' => ['broadsheets' => $broadsheets],
            ];

            if (!empty($failures)) {
                $responseData['warning'] = true;
                $responseData['message'] = "Imported {$successCount} record(s) with " . count($failures) . " warning(s).";
                $responseData['failures'] = $failures;
            }

            return response()->json($responseData);
        } catch (\Exception $e) {
            Log::error('Admin import failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // QUERY HELPERS (copied/adjusted from MyScoreSheetController)
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
            'schoolclass.classcategoryid',
            'schoolarm.arm',
            'schoolarm.id as arm_id',
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
            'broadsheets.subject_position_class_total as position_total',
            'broadsheets.arm_position',
            'broadsheets.arm_position_cum',
            'broadsheets.remark',
            'broadsheets.vettedstatus',
            'broadsheets.avg',
            'broadsheets.cmin',
            'broadsheets.cmax',
        ]);
    }

    protected function getMockBroadsheets($staffId, $termId, $sessionId, $schoolClassId = null, $subjectClassId = null)
    {
        $query = \App\Models\BroadsheetsMock::query()
            ->where('broadsheetmock.staff_id', $staffId)
            ->where('broadsheetmock.term_id', $termId)
            ->join('broadsheet_records_mock', 'broadsheet_records_mock.id', '=', 'broadsheetmock.broadsheet_records_mock_id')
            ->join('subjectclass', function ($join) use ($subjectClassId) {
                $join->on('subjectclass.id', '=', 'broadsheetmock.subjectclass_id')
                     ->on('broadsheet_records_mock.subject_id', '=', 'subjectclass.subjectid')
                     ->on('broadsheet_records_mock.schoolclass_id', '=', 'subjectclass.schoolclassid');
                if ($subjectClassId) $join->where('subjectclass.id', $subjectClassId);
            })
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records_mock.student_id')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->leftJoin('subject', 'subject.id', '=', 'broadsheet_records_mock.subject_id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'broadsheet_records_mock.schoolclass_id')
            ->leftJoin('classcategories', 'classcategories.id', '=', 'schoolclass.classcategoryid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'broadsheetmock.term_id')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'broadsheet_records_mock.session_id')
            ->where('broadsheet_records_mock.session_id', $sessionId)
            ->orderBy('studentRegistration.lastname', 'asc')
            ->orderBy('studentRegistration.firstname', 'asc');

        if ($schoolClassId) $query->where('schoolclass.id', $schoolClassId);

        return $query->get([
            'broadsheetmock.id',
            'studentRegistration.admissionNO as admissionno',
            'broadsheet_records_mock.student_id as student_id',
            'studentRegistration.firstname as fname',
            'studentRegistration.lastname as lname',
            'studentRegistration.othername as mname',
            'subject.subject as subject',
            'subject.subject_code as subject_code',
            'schoolclass.schoolclass',
            'schoolclass.id as schoolclass_id',
            'schoolarm.arm',
            'schoolterm.term',
            'schoolsession.session',
            'subjectclass.id as subjectclid',
            'broadsheetmock.staff_id',
            'broadsheetmock.term_id',
            'broadsheet_records_mock.session_id as sessionid',
            'studentpicture.picture',
            'broadsheetmock.exam',
            'broadsheetmock.total',
            'broadsheetmock.grade',
            'broadsheetmock.subject_position_class as position',
            'broadsheetmock.remark',
            'broadsheetmock.cmin',
            'broadsheetmock.cmax',
            'broadsheetmock.avg',
            'broadsheetmock.vettedstatus',
        ]);
    }

    // =========================================================================
    // POSITION / METRICS HELPERS (simplified versions)
    // =========================================================================

    protected function updateClassMetrics($subjectclassid, $staffid, $termid, $sessionid)
    {
        $subjectClass = DB::table('subjectclass')->where('id', $subjectclassid)->first();
        if (!$subjectClass) return;

        $subjectTeacher = DB::table('subjectteacher')->where('id', $subjectClass->subjectteacherid)->first();
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
        $subjectClass = DB::table('subjectclass')
            ->join('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->where('subjectclass.id', $subjectclass_id)
            ->first(['subjectclass.schoolclassid', 'subjectteacher.subjectid']);

        if (!$subjectClass) return;

        $subjectId = $subjectClass->subjectid;
        $schoolclassId = $subjectClass->schoolclassid;

        $baseClass = DB::table('schoolclass')
            ->where('id', $schoolclassId)
            ->first(['schoolclass', 'classcategoryid']);

        if (!$baseClass) return;

        $allArmIds = DB::table('schoolclass')
            ->where('schoolclass', $baseClass->schoolclass)
            ->where('classcategoryid', $baseClass->classcategoryid)
            ->pluck('id');

        $allSubjectClassIds = DB::table('subjectclass')
            ->join('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->whereIn('subjectclass.schoolclassid', $allArmIds)
            ->where('subjectteacher.subjectid', $subjectId)
            ->pluck('subjectclass.id');

        $allStudents = DB::table('broadsheets')
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
            ->whereIn('broadsheets.subjectclass_id', $allSubjectClassIds)
            ->where('broadsheets.term_id', $term_id)
            ->where('broadsheet_records.session_id', $session_id)
            ->get(['broadsheets.id', 'broadsheets.cum', 'broadsheets.total', 'broadsheet_records.schoolclass_id']);

        if ($allStudents->isEmpty()) return;

        // Class-wide by cum
        $rank = 0;
        $lastVal = null;
        $currentRank = 0;
        foreach ($allStudents->sortByDesc('cum')->values() as $index => $b) {
            $rank = $index + 1;
            if ($lastVal === null || $b->cum != $lastVal) {
                $currentRank = $rank;
                $lastVal = $b->cum;
            }
            DB::table('broadsheets')->where('id', $b->id)->update(['subject_position_class' => $currentRank]);
        }

        // Class-wide by total
        $rank = 0;
        $lastVal = null;
        $currentRank = 0;
        foreach ($allStudents->sortByDesc('total')->values() as $index => $b) {
            $rank = $index + 1;
            if ($lastVal === null || $b->total != $lastVal) {
                $currentRank = $rank;
                $lastVal = $b->total;
            }
            DB::table('broadsheets')->where('id', $b->id)->update(['subject_position_class_total' => $currentRank]);
        }

        // Arm-specific positions
        $byArm = $allStudents->groupBy('schoolclass_id');
        foreach ($byArm as $armClassId => $studentsInArm) {
            $rank = 0;
            $lastVal = null;
            $currentRank = 0;
            foreach ($studentsInArm->sortByDesc('total')->values() as $index => $b) {
                $rank = $index + 1;
                if ($lastVal === null || $b->total != $lastVal) {
                    $currentRank = $rank;
                    $lastVal = $b->total;
                }
                DB::table('broadsheets')->where('id', $b->id)->update(['arm_position' => $currentRank]);
            }

            $rank = 0;
            $lastVal = null;
            $currentRank = 0;
            foreach ($studentsInArm->sortByDesc('cum')->values() as $index => $b) {
                $rank = $index + 1;
                if ($lastVal === null || $b->cum != $lastVal) {
                    $currentRank = $rank;
                    $lastVal = $b->cum;
                }
                DB::table('broadsheets')->where('id', $b->id)->update(['arm_position_cum' => $currentRank]);
            }
        }
    }

    protected function updateClassPositions($schoolclassid, $termid, $sessionid)
    {
        $rank = 0;
        $lastScore = null;
        $rows = 0;
        $pos = PromotionStatus::where('schoolclassid', $schoolclassid)
            ->where('termid', $termid)
            ->where('sessionid', $sessionid)
            ->orderBy('subjectstotalscores', 'DESC')
            ->get();

        foreach ($pos as $row) {
            $rows++;
            if ($lastScore !== $row->subjectstotalscores) {
                $lastScore = $row->subjectstotalscores;
                $rank = $rows;
            }
            $suffix = match ($rank) {
                1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th'
            };
            PromotionStatus::where('id', $row->id)->update(['position' => $rank . $suffix]);
        }
    }

    protected function updateMockClassMetrics($subjectclassid, $staffid, $termid, $sessionid)
    {
        $subjectClass = DB::table('subjectclass')->where('id', $subjectclassid)->first();
        if (!$subjectClass) return;

        $subjectTeacher = DB::table('subjectteacher')->where('id', $subjectClass->subjectteacherid)->first();
        if (!$subjectTeacher) return;

        $subjectId = $subjectTeacher->subjectid;

        $metrics = \App\Models\BroadsheetsMock::query()
            ->where('broadsheetmock.subjectclass_id', $subjectclassid)
            ->where('broadsheetmock.staff_id', $staffid)
            ->where('broadsheetmock.term_id', $termid)
            ->leftJoin('broadsheet_records_mock', 'broadsheet_records_mock.id', '=', 'broadsheetmock.broadsheet_records_mock_id')
            ->where('broadsheet_records_mock.session_id', $sessionid)
            ->where('broadsheet_records_mock.subject_id', $subjectId)
            ->select([
                DB::raw('MIN(broadsheetmock.total) as class_min'),
                DB::raw('MAX(broadsheetmock.total) as class_max'),
                DB::raw('SUM(broadsheetmock.total) as total_sum'),
                DB::raw('COUNT(broadsheetmock.id) as student_count'),
            ])->first();

        $classMin = $metrics->class_min ?? 0;
        $classMax = $metrics->class_max ?? 0;
        $classAvg = $metrics->student_count > 0 ? round($metrics->total_sum / $metrics->student_count, 1) : 0;

        $ids = \App\Models\BroadsheetsMock::query()
            ->where('broadsheetmock.subjectclass_id', $subjectclassid)
            ->where('broadsheetmock.staff_id', $staffid)
            ->where('broadsheetmock.term_id', $termid)
            ->leftJoin('broadsheet_records_mock', 'broadsheet_records_mock.id', '=', 'broadsheetmock.broadsheet_records_mock_id')
            ->where('broadsheet_records_mock.session_id', $sessionid)
            ->where('broadsheet_records_mock.subject_id', $subjectId)
            ->pluck('broadsheetmock.id');

        \App\Models\BroadsheetsMock::whereIn('id', $ids)->update([
            'cmin' => $classMin,
            'cmax' => $classMax,
            'avg' => $classAvg,
        ]);
    }

    protected function updateMockSubjectPositions($subjectclass_id, $staff_id, $term_id, $session_id)
    {
        $broadsheets = \App\Models\BroadsheetsMock::query()
            ->where('broadsheetmock.subjectclass_id', $subjectclass_id)
            ->where('broadsheetmock.staff_id', $staff_id)
            ->where('broadsheetmock.term_id', $term_id)
            ->leftJoin('broadsheet_records_mock', 'broadsheet_records_mock.id', '=', 'broadsheetmock.broadsheet_records_mock_id')
            ->where('broadsheet_records_mock.session_id', $session_id)
            ->orderByDesc('broadsheetmock.total')
            ->orderBy('broadsheetmock.id')
            ->get(['broadsheetmock.id', 'broadsheetmock.total', 'broadsheetmock.subject_position_class']);

        if ($broadsheets->isEmpty()) return;

        $rank = 0;
        $lastTotal = null;
        $lastPosition = 0;
        foreach ($broadsheets as $b) {
            $rank++;
            if ($lastTotal === null || $b->total != $lastTotal) {
                $lastPosition = $rank;
                $lastTotal = $b->total;
            }
            if ($b->subject_position_class != $lastPosition) {
                \App\Models\BroadsheetsMock::where('id', $b->id)->update(['subject_position_class' => $lastPosition]);
            }
        }
    }

    protected function computeDynamicTotals($broadsheets, $assessments, $schoolclass, $termId, $sessionId)
    {
        foreach ($broadsheets as $broadsheet) {
            $assessmentScores = $broadsheet->assessmentScores ?? collect();
            $totalRaw = 0;
            foreach ($assessments as $a) {
                $scoreObj = $assessmentScores->where('assessment_id', $a->id)->first();
                $totalRaw += $scoreObj ? (float) $scoreObj->score : 0;
            }

            $subjectId = DB::table('broadsheet_records')
                ->where('id', $broadsheet->broadSheet_record_id)
                ->value('subject_id');

            $newBf = $this->getPreviousTermCum($broadsheet->student_id, $subjectId, $termId, $sessionId);
            $newCum = ($termId == 1 || $newBf == 0) ? round($totalRaw, 2) : round(($totalRaw + $newBf) / 2, 2);
            $newGrade = $schoolclass && $schoolclass->classcategories->isNotEmpty()
                ? $schoolclass->classcategories->first()->calculateGrade($totalRaw)
                : $this->getDefaultGrade($totalRaw);
            $newRemark = $this->getRemark($newGrade);

            $broadsheet->total = $totalRaw;
            $broadsheet->bf = $newBf;
            $broadsheet->cum = $newCum;
            $broadsheet->grade = $newGrade;
            $broadsheet->remark = $newRemark;
            $broadsheet->save();
        }
    }

    protected function computeOverallGPAAndCGPA($broadsheets, $schoolclass, $termId, $sessionId)
    {
        if (!$schoolclass || $schoolclass->classcategories->isEmpty()) return;
        $isSenior = $schoolclass->classcategories->first()->is_senior ?? false;
        foreach ($broadsheets as $b) {
            $data = $this->computeOverallForStudent($b->student_id, $schoolclass, $termId, $sessionId, $isSenior);
            $b->gpa = round($data['gpa'], 2);
            $b->cgpa = round($data['cgpa'], 2);
            $b->gpa_grade = $data['gpa_grade'] ?? 'F';
        }
    }

    protected function computeOverallForStudent($studentId, $schoolclass, $termId, $sessionId, $isSenior)
    {
        $currentBroadsheets = Broadsheets::where('broadsheets.term_id', $termId)
            ->whereHas('broadsheetRecord', fn($q) =>
                $q->where('student_id', $studentId)->where('session_id', $sessionId)
            )
            ->get(['broadsheets.total']);

        $averageTotal = $currentBroadsheets->avg('total') ?? 0.0;
        $gpaGrade = $schoolclass->classcategories->first()
            ? $schoolclass->classcategories->first()->calculateGrade($averageTotal)
            : $this->getDefaultGrade($averageTotal);

        $termGradePoints = $currentBroadsheets->map(fn($b) => $this->getGradePoint($b->total, $isSenior));
        $gpa = $termGradePoints->avg() ?? 0.0;

        return [
            'gpa' => $gpa,
            'cgpa' => $gpa,
            'gpa_grade' => $gpaGrade,
        ];
    }

    protected function getGradePoint($score, $isSenior = false)
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

    protected function getPreviousTermCum($studentId, $subjectId, $termId, $sessionId)
    {
        if ($termId == 1) return 0;
        $prev = DB::table('broadsheets')
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
            ->where('broadsheet_records.student_id', $studentId)
            ->where('broadsheet_records.subject_id', $subjectId)
            ->where('broadsheet_records.session_id', $sessionId)
            ->where('broadsheets.term_id', $termId - 1)
            ->value('broadsheets.cum');
        return $prev !== null ? round((float) $prev, 2) : 0;
    }

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
        return match ($grade) {
            'A', 'A1' => 'Excellent',
            'B', 'B2', 'B3' => 'Very Good',
            'C', 'C4', 'C5', 'C6' => 'Good',
            'D', 'D7', 'E8' => 'Pass',
            default => 'Fail',
        };
    }
}
