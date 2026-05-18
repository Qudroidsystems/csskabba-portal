<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Studentclass;
use App\Models\Studentpersonalityprofile;
use App\Models\Broadsheets;
use App\Models\Schoolclass;
use App\Models\Schoolterm;
use App\Models\Schoolsession;
use App\Models\Subject;
use App\Models\Schoolarm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ClassBroadsheetController extends Controller
{
    // =========================================================================
    // GRADE HELPERS
    // =========================================================================

    public function gradeFromScorePublic(float $score, bool $isSenior): array
    {
        return $this->gradeFromScore($score, $isSenior);
    }

    private function gradeFromScore(float $score, bool $isSenior): array
    {
        if ($score <= 0) return ['-', '-'];

        if ($isSenior) {
            if ($score >= 75) return ['A1', 'A'];
            if ($score >= 70) return ['B2', 'B'];
            if ($score >= 65) return ['B3', 'B'];
            if ($score >= 60) return ['C4', 'C'];
            if ($score >= 55) return ['C5', 'C'];
            if ($score >= 50) return ['C6', 'C'];
            if ($score >= 45) return ['D7', 'D'];
            if ($score >= 40) return ['E8', 'E'];
            return ['F9', 'F'];
        }

        if ($score >= 70) return ['A', 'A'];
        if ($score >= 60) return ['B', 'B'];
        if ($score >= 50) return ['C', 'C'];
        if ($score >= 40) return ['D', 'D'];
        return ['F', 'F'];
    }

    // =========================================================================
    // CLASS BROADSHEET VIEW
    // =========================================================================

    public function classBroadsheet($schoolclassid, $sessionid, $termid)
    {
        $pagetitle = "Class Broadsheet";

        // 1. Students
        $students = Studentclass::where('studentclass.schoolclassid', $schoolclassid)
            ->where('studentclass.sessionid', $sessionid)
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'studentclass.studentId')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->get([
                'studentRegistration.id          as id',
                'studentRegistration.admissionNo as admissionNo',
                'studentRegistration.firstname   as fname',
                'studentRegistration.lastname    as lastname',
                'studentRegistration.othername   as othername',
                'studentRegistration.gender      as gender',
                'studentpicture.picture          as picture',
            ])->sortBy('lastname');

        // 2. Ensure personality profiles exist for every student
        foreach ($students as $student) {
            $profile = Studentpersonalityprofile::firstOrNew([
                'studentid'     => $student->id,
                'schoolclassid' => $schoolclassid,
                'sessionid'     => $sessionid,
                'termid'        => $termid,
            ]);

            if (!$profile->exists) {
                $profile->staffid                    = Auth::id();
                $profile->classteachercomment        = null;
                $profile->guidancescomment           = null;
                $profile->remark_on_other_activities = null;
                $profile->no_of_times_school_absent  = null;
                $profile->signature                  = null;
                $profile->save();
            }
        }

        // 3. Subjects for this class/session
        $subjects = Subject::whereHas('broadsheetRecords', function ($q) use ($schoolclassid, $sessionid) {
            $q->where('schoolclass_id', $schoolclassid)
              ->where('session_id',     $sessionid);
        })->orderBy('subject')->get(['id', 'subject', 'subject_code']);

        // 4. Broadsheet scores — term total and cumulative
        $broadsheetRows = Broadsheets::where('broadsheet_records.schoolclass_id', $schoolclassid)
            ->where('broadsheets.term_id',           $termid)
            ->where('broadsheet_records.session_id', $sessionid)
            ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->leftJoin('subject',             'subject.id',            '=', 'broadsheet_records.subject_id')
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
            ->get([
                'broadsheet_records.student_id',
                'subject.subject as subject_name',
                'subject.subject_code',
                'broadsheets.total',
                'broadsheets.bf',
                'broadsheets.cum',
                'broadsheets.grade',
                'broadsheets.subject_position_class as position',
                'broadsheets.avg as class_average',
            ]);

        // 5. Build O(1) lookup maps
        $termScoreMap = [];
        $cumScoreMap  = [];

        foreach ($broadsheetRows as $row) {
            $sid  = $row->student_id;
            $subj = $row->subject_name;
            $termScoreMap[$sid][$subj] = $row->total ?? 0;
            $cumScoreMap[$sid][$subj]  = $row->cum   ?? 0;
        }

        // 6. Is this a senior class?
        $schoolclassModel = Schoolclass::with(['arms', 'classcategories'])->find($schoolclassid);
        $isSenior = $schoolclassModel && $schoolclassModel->classcategories->isNotEmpty()
            ? ($schoolclassModel->classcategories->first()->is_senior ?? false)
            : false;

        // 7. Per-student analytics
        $studentAnalytics = [];

        foreach ($students as $student) {
            $sid          = $student->id;
            $termTotal    = 0;
            $cumTotal     = 0;
            $subjectCount = 0;
            $grades       = [];

            foreach ($subjects as $subject) {
                $subj      = $subject->subject;
                $termScore = $termScoreMap[$sid][$subj] ?? 0;
                $cumScore  = $cumScoreMap[$sid][$subj]  ?? 0;

                if ($termScore > 0 || $cumScore > 0) {
                    $subjectCount++;
                }

                $termTotal += $termScore;
                $cumTotal  += $cumScore;

                [$termGrade] = $this->gradeFromScore((float) $termScore, $isSenior);
                [$cumGrade]  = $this->gradeFromScore((float) $cumScore,  $isSenior);

                $grades[] = [
                    'subject'    => $subj,
                    'term_score' => $termScore,
                    'cum_score'  => $cumScore,
                    'term_grade' => $termGrade,
                    'cum_grade'  => $cumGrade,
                ];
            }

            $totalObtainable = $subjectCount * 100;
            $termPercentage  = $totalObtainable > 0 ? round(($termTotal / $totalObtainable) * 100, 1) : 0;
            $cumPercentage   = $totalObtainable > 0 ? round(($cumTotal  / $totalObtainable) * 100, 1) : 0;

            $studentAnalytics[$sid] = [
                'term_total'       => $termTotal,
                'cum_total'        => $cumTotal,
                'term_average'     => $subjectCount > 0 ? round($termTotal / $subjectCount, 1) : 0,
                'cum_average'      => $subjectCount > 0 ? round($cumTotal  / $subjectCount, 1) : 0,
                'subject_count'    => $subjectCount,
                'total_obtainable' => $totalObtainable,
                'term_percentage'  => $termPercentage,
                'cum_percentage'   => $cumPercentage,
                'grades'           => $grades,
            ];
        }

        // 8. Personality profiles (no staffid filter — load all for this class/term)
        $personalityProfiles = Studentpersonalityprofile::where('schoolclassid', $schoolclassid)
            ->where('sessionid', $sessionid)
            ->where('termid',    $termid)
            ->get([
                'studentid',
                'classteachercomment',
                'guidancescomment',
                'remark_on_other_activities',
                'no_of_times_school_absent',
                'signature',
            ]);

        // 9. Class meta
        $schoolclass = Schoolclass::where('schoolclass.id', $schoolclassid)
            ->leftJoin('schoolarm', 'schoolclass.arm', '=', 'schoolarm.id')
            ->first(['schoolclass.schoolclass', 'schoolclass.arm', 'schoolarm.arm']);

        $schoolterm    = Schoolterm::where('id',    $termid)->value('term')    ?? 'N/A';
        $schoolsession = Schoolsession::where('id', $sessionid)->value('session') ?? 'N/A';

        return view('classbroadsheet.classbroadsheet', compact(
            'students',
            'subjects',
            'broadsheetRows',
            'termScoreMap',
            'cumScoreMap',
            'personalityProfiles',
            'schoolclass',
            'schoolterm',
            'schoolsession',
            'schoolclassid',
            'sessionid',
            'termid',
            'isSenior',
            'studentAnalytics',
            'pagetitle'
        ));
    }

    // =========================================================================
    // UPDATE COMMENTS
    // Accepts both:
    //   - PATCH  /classbroadsheet/{...}/comments          (native PATCH)
    //   - POST   /classbroadsheet/{...}/comments + _method=PATCH (form spoofing)
    //
    // Works for both per-student autosave (single student ID in payload)
    // and batch Save All (all student IDs in payload).
    // =========================================================================

    public function updateComments(Request $request, $schoolclassid, $sessionid, $termid)
    {
        Log::info('ClassBroadsheet updateComments', [
            'schoolclassid' => $schoolclassid,
            'sessionid'     => $sessionid,
            'termid'        => $termid,
            'method'        => $request->method(),
            'is_ajax'       => $request->ajax(),
            'student_ids'   => array_keys($request->input('teacher_comments', [])),
        ]);

        $request->validate([
            'teacher_comments.*'            => 'nullable|string|max:2000',
            'guidance_comments.*'           => 'nullable|string|max:2000',
            'remarks_on_other_activities.*' => 'nullable|string|max:2000',
            'no_of_times_school_absent.*'   => 'nullable|integer|min:0',
            'signature'                     => 'nullable|mimes:jpg,jpeg,png,pdf|max:5048',
        ]);

        // Handle signature upload (only if provided)
        $signaturePath = null;
        if ($request->hasFile('signature') && $request->file('signature')->isValid()) {
            $file          = $request->file('signature');
            $filename      = 'signature_' . Auth::id() . '_' . time() . '.' . $file->getClientOriginalExtension();
            $stored        = $file->storeAs('public/signatures', $filename);
            $signaturePath = str_replace('public/', '', $stored);
        }

        $teacherComments  = $request->input('teacher_comments',            []);
        $guidanceComments = $request->input('guidance_comments',            []);
        $remarks          = $request->input('remarks_on_other_activities', []);
        $absences         = $request->input('no_of_times_school_absent',   []);

        // Collect every student ID present in ANY field of this payload
        $allStudentIds = array_unique(array_merge(
            array_keys($teacherComments),
            array_keys($guidanceComments),
            array_keys($remarks),
            array_keys($absences)
        ));

        if (empty($allStudentIds) && !$signaturePath) {
            return response()->json([
                'success' => false,
                'message' => 'No data provided to update.',
            ], 400);
        }

        DB::beginTransaction();
        try {
            $updatedCount = 0;
            $createdCount = 0;
            $skippedCount = 0;

            foreach ($allStudentIds as $studentId) {
                $teacherComment  = trim($teacherComments[$studentId]  ?? '');
                $guidanceComment = trim($guidanceComments[$studentId]  ?? '');
                $remark          = trim($remarks[$studentId]           ?? '');
                $absence         = (isset($absences[$studentId]) && $absences[$studentId] !== '')
                    ? (int) $absences[$studentId]
                    : null;

                // Skip if every field is blank and no signature
                if ($teacherComment === '' && $guidanceComment === '' && $remark === '' && $absence === null && !$signaturePath) {
                    $skippedCount++;
                    continue;
                }

                $existing = Studentpersonalityprofile::where('studentid',     $studentId)
                    ->where('schoolclassid', $schoolclassid)
                    ->where('sessionid',     $sessionid)
                    ->where('termid',        $termid)
                    ->first();

                $payload = [
                    'staffid'                    => Auth::id(),
                    'classteachercomment'        => $teacherComment  ?: null,
                    'guidancescomment'           => $guidanceComment ?: null,
                    'remark_on_other_activities' => $remark          ?: null,
                    'no_of_times_school_absent'  => $absence,
                ];

                if ($signaturePath) {
                    $payload['signature'] = $signaturePath;
                }

                if ($existing) {
                    $existing->update($payload);
                    $updatedCount++;
                } else {
                    Studentpersonalityprofile::create(array_merge($payload, [
                        'studentid'     => $studentId,
                        'schoolclassid' => $schoolclassid,
                        'sessionid'     => $sessionid,
                        'termid'        => $termid,
                    ]));
                    $createdCount++;
                }
            }

            DB::commit();

            $total = $updatedCount + $createdCount;

            if ($total === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No comments were entered. Please add at least one comment before saving.',
                ], 400);
            }

            $parts = [];
            if ($updatedCount) $parts[] = "{$updatedCount} updated";
            if ($createdCount) $parts[] = "{$createdCount} new";
            if ($skippedCount) $parts[] = "{$skippedCount} skipped";

            return response()->json([
                'success' => true,
                'message' => 'Saved successfully: ' . implode(', ', $parts) . '.',
                'updated' => $updatedCount,
                'created' => $createdCount,
                'skipped' => $skippedCount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ClassBroadsheet updateComments error', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
