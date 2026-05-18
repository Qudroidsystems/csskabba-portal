<?php

namespace App\Http\Controllers;

use App\Models\Studentclass;
use App\Models\Studentpersonalityprofile;
use App\Models\Broadsheets;
use App\Models\Schoolclass;
use App\Models\Schoolterm;
use App\Models\Schoolsession;
use App\Models\Subject;
use App\Models\StudentRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        foreach ($students as $student) {
            $profile = Studentpersonalityprofile::firstOrNew([
                'studentid'     => $student->id,
                'schoolclassid' => $schoolclassid,
                'sessionid'     => $sessionid,
                'termid'        => $termid,
            ]);
            if (!$profile->exists) {
                $profile->staffid = Auth::id();
                $profile->save();
            }
        }

        $subjects = Subject::whereHas('broadsheetRecords', function ($q) use ($schoolclassid, $sessionid) {
            $q->where('schoolclass_id', $schoolclassid)->where('session_id', $sessionid);
        })->orderBy('subject')->get(['id', 'subject', 'subject_code']);

        $broadsheetRows = Broadsheets::where('broadsheet_records.schoolclass_id', $schoolclassid)
            ->where('broadsheets.term_id',           $termid)
            ->where('broadsheet_records.session_id', $sessionid)
            ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->leftJoin('subject',             'subject.id',            '=', 'broadsheet_records.subject_id')
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
            ->get([
                'broadsheet_records.student_id',
                'subject.subject as subject_name',
                'broadsheets.total',
                'broadsheets.cum',
                'broadsheets.grade',
                'broadsheets.subject_position_class as position',
                'broadsheets.avg as class_average',
            ]);

        $termScoreMap = [];
        $cumScoreMap  = [];
        foreach ($broadsheetRows as $row) {
            $termScoreMap[$row->student_id][$row->subject_name] = $row->total ?? 0;
            $cumScoreMap[$row->student_id][$row->subject_name]  = $row->cum   ?? 0;
        }

        $schoolclassModel = Schoolclass::with(['arms', 'classcategories'])->find($schoolclassid);
        $isSenior = $schoolclassModel && $schoolclassModel->classcategories->isNotEmpty()
            ? ($schoolclassModel->classcategories->first()->is_senior ?? false)
            : false;

        $studentAnalytics = [];
        foreach ($students as $student) {
            $sid = $student->id;
            $termTotal = $cumTotal = $subjectCount = 0;
            $grades = [];
            foreach ($subjects as $subject) {
                $subj      = $subject->subject;
                $termScore = $termScoreMap[$sid][$subj] ?? 0;
                $cumScore  = $cumScoreMap[$sid][$subj]  ?? 0;
                if ($termScore > 0 || $cumScore > 0) $subjectCount++;
                $termTotal += $termScore;
                $cumTotal  += $cumScore;
                [$termGrade] = $this->gradeFromScore((float) $termScore, $isSenior);
                [$cumGrade]  = $this->gradeFromScore((float) $cumScore,  $isSenior);
                $grades[] = ['subject' => $subj, 'term_score' => $termScore, 'cum_score' => $cumScore, 'term_grade' => $termGrade, 'cum_grade' => $cumGrade];
            }
            $totalObtainable = $subjectCount * 100;
            $studentAnalytics[$sid] = [
                'term_total'       => $termTotal,
                'cum_total'        => $cumTotal,
                'term_average'     => $subjectCount > 0 ? round($termTotal / $subjectCount, 1) : 0,
                'cum_average'      => $subjectCount > 0 ? round($cumTotal  / $subjectCount, 1) : 0,
                'subject_count'    => $subjectCount,
                'total_obtainable' => $totalObtainable,
                'term_percentage'  => $totalObtainable > 0 ? round(($termTotal / $totalObtainable) * 100, 1) : 0,
                'cum_percentage'   => $totalObtainable > 0 ? round(($cumTotal  / $totalObtainable) * 100, 1) : 0,
                'grades'           => $grades,
            ];
        }

        $personalityProfiles = Studentpersonalityprofile::where('schoolclassid', $schoolclassid)
            ->where('sessionid', $sessionid)
            ->where('termid',    $termid)
            ->get();

        $schoolclass = Schoolclass::where('schoolclass.id', $schoolclassid)
            ->leftJoin('schoolarm', 'schoolclass.arm', '=', 'schoolarm.id')
            ->first(['schoolclass.schoolclass', 'schoolclass.arm', 'schoolarm.arm']);

        $schoolterm    = Schoolterm::where('id',    $termid)->value('term')    ?? 'N/A';
        $schoolsession = Schoolsession::where('id', $sessionid)->value('session') ?? 'N/A';

        return view('classbroadsheet.classbroadsheet', compact(
            'students', 'subjects', 'broadsheetRows',
            'termScoreMap', 'cumScoreMap', 'personalityProfiles',
            'schoolclass', 'schoolterm', 'schoolsession',
            'schoolclassid', 'sessionid', 'termid',
            'isSenior', 'studentAnalytics', 'pagetitle'
        ));
    }

    // =========================================================================
    // GET PAST COMMENTS (Enhanced with specific comment types and counts)
    // Route: GET /classbroadsheet/past-comments/{studentId}
    // =========================================================================

    public function getPastComments($studentId)
    {
        try {
            // Get all profiles for this student across all terms/sessions
            $profiles = Studentpersonalityprofile::where('studentid', $studentId)
                ->where(function ($q) {
                    $q->whereNotNull('classteachercomment')
                      ->orWhereNotNull('guidancescomment')
                      ->orWhereNotNull('remark_on_other_activities')
                      ->orWhereNotNull('principalscomment');
                })
                ->orderByDesc('sessionid')
                ->orderByDesc('termid')
                ->get([
                    'id', 'studentid', 'schoolclassid', 'sessionid', 'termid',
                    'classteachercomment', 'guidancescomment',
                    'remark_on_other_activities', 'principalscomment',
                    'no_of_times_school_absent',
                    'created_at', 'updated_at',
                ]);

            // Get student info for modal
            $student = StudentRegistration::find($studentId);

            // Count comments by type
            $commentCounts = [
                'classteacher' => 0,
                'guidance' => 0,
                'activities' => 0,
                'principal' => 0,
                'total' => 0
            ];

            $result = $profiles->map(function ($p) use (&$commentCounts) {
                $term    = Schoolterm::find($p->termid);
                $session = Schoolsession::find($p->sessionid);
                $class   = Schoolclass::where('schoolclass.id', $p->schoolclassid)
                    ->leftJoin('schoolarm', 'schoolclass.arm', '=', 'schoolarm.id')
                    ->first(['schoolclass.schoolclass', 'schoolarm.arm']);

                // Count non-empty comments
                if ($p->classteachercomment && trim($p->classteachercomment) !== '') $commentCounts['classteacher']++;
                if ($p->guidancescomment && trim($p->guidancescomment) !== '') $commentCounts['guidance']++;
                if ($p->remark_on_other_activities && trim($p->remark_on_other_activities) !== '') $commentCounts['activities']++;
                if ($p->principalscomment && trim($p->principalscomment) !== '') $commentCounts['principal']++;

                return [
                    'id'                         => $p->id,
                    'term'                       => $term    ? $term->term       : 'Unknown Term',
                    'term_id'                    => $p->termid,
                    'session'                    => $session ? $session->session : 'Unknown Session',
                    'session_id'                 => $p->sessionid,
                    'class'                      => $class   ? trim($class->schoolclass . ' ' . $class->arm) : 'Unknown Class',
                    'class_id'                   => $p->schoolclassid,
                    'classteachercomment'        => $p->classteachercomment,
                    'guidancescomment'           => $p->guidancescomment,
                    'remark_on_other_activities' => $p->remark_on_other_activities,
                    'principalscomment'          => $p->principalscomment,
                    'no_of_times_school_absent'  => $p->no_of_times_school_absent,
                    'date'                       => optional($p->updated_at ?? $p->created_at)->format('d M Y') ?? '',
                ];
            });

            $commentCounts['total'] = $profiles->count();

            return response()->json([
                'success' => true,
                'data' => $result,
                'student' => $student ? [
                    'id' => $student->id,
                    'name' => trim($student->lastname . ' ' . $student->firstname . ' ' . $student->othername),
                    'admission_no' => $student->admissionNo,
                    'gender' => $student->gender,
                ] : null,
                'counts' => $commentCounts
            ]);
        } catch (\Exception $e) {
            Log::error('getPastComments error', ['error' => $e->getMessage(), 'studentId' => $studentId]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // UPDATE COMMENTS (Enhanced with specific comment types)
    // =========================================================================

    public function updateComments(Request $request, $schoolclassid, $sessionid, $termid)
    {
        $request->validate([
            'teacher_comments.*'            => 'nullable|string|max:5000',
            'guidance_comments.*'           => 'nullable|string|max:5000',
            'remarks_on_other_activities.*' => 'nullable|string|max:5000',
            'principals_comments.*'         => 'nullable|string|max:5000',
            'no_of_times_school_absent.*'   => 'nullable|integer|min:0',
            'signature'                     => 'nullable|mimes:jpg,jpeg,png,pdf|max:5048',
        ]);

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
        $principalComments = $request->input('principals_comments',        []);
        $absences         = $request->input('no_of_times_school_absent',   []);

        $allStudentIds = array_unique(array_merge(
            array_keys($teacherComments),
            array_keys($guidanceComments),
            array_keys($remarks),
            array_keys($principalComments),
            array_keys($absences)
        ));

        if (empty($allStudentIds) && !$signaturePath) {
            return response()->json(['success' => false, 'message' => 'No data provided.'], 400);
        }

        DB::beginTransaction();
        try {
            $updatedCount = $createdCount = $skippedCount = 0;
            $savedComments = [];

            foreach ($allStudentIds as $studentId) {
                $teacherComment  = trim($teacherComments[$studentId]  ?? '');
                $guidanceComment = trim($guidanceComments[$studentId]  ?? '');
                $remark          = trim($remarks[$studentId]           ?? '');
                $principalComment = trim($principalComments[$studentId] ?? '');
                $absence         = (isset($absences[$studentId]) && $absences[$studentId] !== '')
                    ? (int) $absences[$studentId] : null;

                if ($teacherComment === '' && $guidanceComment === '' && $remark === '' && $principalComment === '' && $absence === null && !$signaturePath) {
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
                    'principalscomment'          => $principalComment ?: null,
                    'no_of_times_school_absent'  => $absence,
                ];
                if ($signaturePath) $payload['signature'] = $signaturePath;

                if ($existing) {
                    $existing->update($payload);
                    $updatedCount++;
                    $savedComments[] = [
                        'student_id' => $studentId,
                        'type' => $this->getCommentType($teacherComment, $guidanceComment, $remark, $principalComment)
                    ];
                } else {
                    Studentpersonalityprofile::create(array_merge($payload, [
                        'studentid'     => $studentId,
                        'schoolclassid' => $schoolclassid,
                        'sessionid'     => $sessionid,
                        'termid'        => $termid,
                    ]));
                    $createdCount++;
                    $savedComments[] = [
                        'student_id' => $studentId,
                        'type' => $this->getCommentType($teacherComment, $guidanceComment, $remark, $principalComment)
                    ];
                }
            }

            DB::commit();

            $total = $updatedCount + $createdCount;
            if ($total === 0) {
                return response()->json(['success' => false, 'message' => 'No comments entered.'], 400);
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
                'saved_comments' => $savedComments
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ClassBroadsheet updateComments error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    private function getCommentType($teacher, $guidance, $activities, $principal)
    {
        $types = [];
        if ($teacher) $types[] = 'Teacher';
        if ($guidance) $types[] = 'Guidance';
        if ($activities) $types[] = 'Activities';
        if ($principal) $types[] = 'Principal';
        return implode(', ', $types);
    }
}
