<?php

namespace App\Http\Controllers;

use App\Models\Studentclass;
use App\Models\Studentpersonalityprofile;
use App\Models\Broadsheets;
use App\Models\Schoolclass;
use App\Models\Schoolterm;
use App\Models\Schoolsession;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClassBroadsheetController extends Controller
{
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
        $topPerformerByCum = null;
        $topPerformerByTerm = null;
        $topPerformerPicture = null;
        $topCumPercentage = -1;
        $topTermPercentage = -1;

        foreach ($students as $student) {
            $sid = $student->id;
            $termTotal = 0;
            $cumTotal = 0;
            $subjectCount = 0;
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
            $termPercentage = $totalObtainable > 0 ? round(($termTotal / $totalObtainable) * 100, 1) : 0;
            $cumPercentage = $totalObtainable > 0 ? round(($cumTotal / $totalObtainable) * 100, 1) : 0;

            $studentAnalytics[$sid] = [
                'term_total'              => $termTotal,
                'cum_total'               => $cumTotal,
                'term_average'            => $subjectCount > 0 ? round($termTotal / $subjectCount, 1) : 0,
                'cum_average'             => $subjectCount > 0 ? round($cumTotal / $subjectCount, 1) : 0,
                'subject_count'           => $subjectCount,
                'total_obtainable'        => $totalObtainable,
                'term_percentage'         => $termPercentage,
                'cum_percentage'          => $cumPercentage,
                'grades'                  => $grades,
            ];

            if ($cumPercentage > $topCumPercentage) {
                $topCumPercentage = $cumPercentage;
                $topPerformerByCum = trim(($student->lastname ?? '') . ' ' . ($student->fname ?? ''));
                $topPerformerPicture = $student->picture ? asset('storage/student_avatars/' . basename($student->picture)) : null;
            }

            if ($termPercentage > $topTermPercentage) {
                $topTermPercentage = $termPercentage;
                $topPerformerByTerm = trim(($student->lastname ?? '') . ' ' . ($student->fname ?? ''));
            }
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

        $avgTermPercentage = 0;
        $avgCumPercentage = 0;
        if (count($studentAnalytics) > 0) {
            $totalTermPct = array_sum(array_column($studentAnalytics, 'term_percentage'));
            $totalCumPct = array_sum(array_column($studentAnalytics, 'cum_percentage'));
            $avgTermPercentage = round($totalTermPct / count($studentAnalytics), 1);
            $avgCumPercentage = round($totalCumPct / count($studentAnalytics), 1);
        }

        return view('classbroadsheet.classbroadsheet', compact(
            'students', 'subjects', 'broadsheetRows',
            'termScoreMap', 'cumScoreMap', 'personalityProfiles',
            'schoolclass', 'schoolterm', 'schoolsession',
            'schoolclassid', 'sessionid', 'termid',
            'isSenior', 'studentAnalytics', 'pagetitle',
            'topPerformerByCum', 'topPerformerByTerm', 'topPerformerPicture',
            'avgTermPercentage', 'avgCumPercentage'
        ));
    }

    public function getPastComments($studentId)
    {
        try {
            Log::info('getPastComments called for student: ' . $studentId);

            $student = Student::find($studentId);
            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found'
                ], 404);
            }

            $profiles = Studentpersonalityprofile::where('studentid', $studentId)
                ->where(function ($q) {
                    $q->whereNotNull('classteachercomment')
                      ->orWhereNotNull('guidancescomment')
                      ->orWhereNotNull('remark_on_other_activities')
                      ->orWhereNotNull('principalscomment');
                })
                ->orderByDesc('sessionid')
                ->orderByDesc('termid')
                ->get();

            $staffUserIds = $profiles->pluck('staffid')->filter()->unique()->values()->toArray();
            $staffMembers = Staff::with('user')->whereIn('userid', $staffUserIds)->get()->keyBy('userid');

            $commentCounts = [
                'classteacher' => 0,
                'guidance' => 0,
                'activities' => 0,
                'principal' => 0,
                'total' => 0
            ];

            $result = collect();

            foreach ($profiles as $profile) {
                $hasComment = false;
                $commentText = '';
                $commentType = '';
                $staffUserId = $profile->staffid;

                if ($profile->classteachercomment && trim($profile->classteachercomment) !== '') {
                    $commentText = $profile->classteachercomment;
                    $commentType = 'Teacher';
                    $commentCounts['classteacher']++;
                    $hasComment = true;
                } elseif ($profile->guidancescomment && trim($profile->guidancescomment) !== '') {
                    $commentText = $profile->guidancescomment;
                    $commentType = 'Guidance';
                    $commentCounts['guidance']++;
                    $hasComment = true;
                } elseif ($profile->remark_on_other_activities && trim($profile->remark_on_other_activities) !== '') {
                    $commentText = $profile->remark_on_other_activities;
                    $commentType = 'Activities';
                    $commentCounts['activities']++;
                    $hasComment = true;
                } elseif ($profile->principalscomment && trim($profile->principalscomment) !== '') {
                    $commentText = $profile->principalscomment;
                    $commentType = 'Principal';
                    $commentCounts['principal']++;
                    $hasComment = true;
                }

                if (!$hasComment) continue;

                $staffName = null;
                $staffPicture = null;

                if ($staffUserId) {
                    $staff = $staffMembers->get($staffUserId);
                    if (!$staff) {
                        $staff = Staff::with('user')->find($staffUserId);
                        if ($staff && $staff->userid) {
                            $staffMembers->put($staff->userid, $staff);
                        }
                    }
                    if ($staff && $staff->user) {
                        $staffName = $staff->user->name;
                    } elseif ($staff && $staff->employmentid) {
                        $staffName = 'Staff: ' . $staff->employmentid;
                    } else {
                        $staffName = 'Staff Member';
                    }
                } else {
                    $staffName = 'System';
                }

                $term = Schoolterm::find($profile->termid);
                $session = Schoolsession::find($profile->sessionid);
                $class = Schoolclass::where('schoolclass.id', $profile->schoolclassid)
                    ->leftJoin('schoolarm', 'schoolclass.arm', '=', 'schoolarm.id')
                    ->first(['schoolclass.schoolclass', 'schoolarm.arm']);

                $result->push([
                    'id' => $profile->id,
                    'term' => $term ? $term->term : 'Unknown Term',
                    'session' => $session ? $session->session : 'Unknown Session',
                    'class' => $class ? trim($class->schoolclass . ' ' . $class->arm) : 'Unknown Class',
                    'comment_text' => $commentText,
                    'comment_type' => $commentType,
                    'date' => optional($profile->updated_at ?? $profile->created_at)->format('d M Y') ?? '',
                    'staff_name' => $staffName,
                    'staff_picture' => $staffPicture,
                    'staff_id' => $staffUserId,
                ]);
            }

            $commentCounts['total'] = $result->count();
            $studentName = trim($student->lastname . ' ' . $student->firstname . ' ' . ($student->othername ?? ''));

            return response()->json([
                'success' => true,
                'data' => $result->values(),
                'student' => [
                    'id' => $student->id,
                    'name' => $studentName,
                    'admission_no' => $student->admissionNo,
                ],
                'counts' => $commentCounts
            ]);

        } catch (\Exception $e) {
            Log::error('getPastComments error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'studentId' => $studentId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

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
            $file = $request->file('signature');
            $filename = 'signature_' . Auth::id() . '_' . time() . '.' . $file->getClientOriginalExtension();
            $stored = $file->storeAs('public/signatures', $filename);
            $signaturePath = str_replace('public/', '', $stored);
        }

        $teacherComments = $request->input('teacher_comments', []);
        $guidanceComments = $request->input('guidance_comments', []);
        $remarks = $request->input('remarks_on_other_activities', []);
        $principalComments = $request->input('principals_comments', []);
        $absences = $request->input('no_of_times_school_absent', []);

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

            foreach ($allStudentIds as $studentId) {
                $teacherComment = trim($teacherComments[$studentId] ?? '');
                $guidanceComment = trim($guidanceComments[$studentId] ?? '');
                $remark = trim($remarks[$studentId] ?? '');
                $principalComment = trim($principalComments[$studentId] ?? '');
                $absence = (isset($absences[$studentId]) && $absences[$studentId] !== '')
                    ? (int) $absences[$studentId] : null;

                if ($teacherComment === '' && $guidanceComment === '' && $remark === '' && $principalComment === '' && $absence === null && !$signaturePath) {
                    $skippedCount++;
                    continue;
                }

                $existing = Studentpersonalityprofile::where('studentid', $studentId)
                    ->where('schoolclassid', $schoolclassid)
                    ->where('sessionid', $sessionid)
                    ->where('termid', $termid)
                    ->first();

                $payload = [
                    'staffid' => Auth::id(),
                    'classteachercomment' => $teacherComment ?: null,
                    'guidancescomment' => $guidanceComment ?: null,
                    'remark_on_other_activities' => $remark ?: null,
                    'principalscomment' => $principalComment ?: null,
                    'no_of_times_school_absent' => $absence,
                ];
                if ($signaturePath) $payload['signature'] = $signaturePath;

                if ($existing) {
                    $existing->update($payload);
                    $updatedCount++;
                } else {
                    Studentpersonalityprofile::create(array_merge($payload, [
                        'studentid' => $studentId,
                        'schoolclassid' => $schoolclassid,
                        'sessionid' => $sessionid,
                        'termid' => $termid,
                    ]));
                    $createdCount++;
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
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ClassBroadsheet updateComments error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }
}
