<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Schoolarm;
use Illuminate\View\View;
use App\Models\Schoolterm;
use App\Models\Broadsheets;
use App\Models\Schoolclass;
use App\Models\Studentclass;
use Illuminate\Http\Request;
use App\Models\Classcategory;
use App\Models\Schoolsession;
use Illuminate\Http\Response;
use App\Models\PromotionStatus;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\SchoolInformation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Models\CompulsorySubjectClass;
use App\Models\AttendanceSummary;
use App\Models\BroadsheetAssessmentScore;
use App\Models\BroadsheetsMock;
use App\Models\Studentpersonalityprofile;
use Illuminate\Pagination\LengthAwarePaginator;

class ViewStudentReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View student-report', ['only' => [
            'index', 'show', 'registeredClasses', 'classBroadsheet',
            'studentresult', 'studentmockresult', 'exportStudentResultPdf', 'exportClassResultsPdf'
        ]]);
        $this->middleware('permission:Create student-report', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update student-report', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete student-report', ['only' => ['destroy']]);

        Log::channel('pdf')->info('ViewStudentReportController initialized', ['timestamp' => now()]);
    }

    /**
     * Format number with ordinal suffix (st, nd, rd, th)
     */
    protected function formatOrdinal($number)
    {
        if (!is_numeric($number) || $number <= 0) {
            return '-';
        }

        $lastDigit     = $number % 10;
        $lastTwoDigits = $number % 100;

        if ($lastTwoDigits >= 11 && $lastTwoDigits <= 13) {
            return $number . 'th';
        }

        return $number . match ($lastDigit) {
            1       => 'st',
            2       => 'nd',
            3       => 'rd',
            default => 'th',
        };
    }

    /**
     * Calculate grade based on total score using WAEC/NECO standard.
     */
    protected function calculateGrade($score)
    {
        Log::debug('Calculating grade', ['score' => $score]);

        if ($score === null || $score == 0) {
            return 'F9';
        }

        if ($score >= 75) {
            return 'A1';
        } elseif ($score >= 70) {
            return 'B2';
        } elseif ($score >= 65) {
            return 'B3';
        } elseif ($score >= 60) {
            return 'C4';
        } elseif ($score >= 55) {
            return 'C5';
        } elseif ($score >= 50) {
            return 'C6';
        } elseif ($score >= 45) {
            return 'D7';
        } elseif ($score >= 40) {
            return 'E8';
        } else {
            return 'F9';
        }
    }

    /**
     * Get grade point based on score using WAEC/NECO standard.
     */
    protected function getGradePoint($score)
    {
        Log::debug('Calculating grade point', ['score' => $score]);

        if ($score === null || $score == 0) {
            return 0.0;
        }

        if ($score >= 75) {
            return 5.0;
        } elseif ($score >= 70) {
            return 4.5;
        } elseif ($score >= 65) {
            return 4.0;
        } elseif ($score >= 60) {
            return 3.5;
        } elseif ($score >= 55) {
            return 3.0;
        } elseif ($score >= 50) {
            return 2.5;
        } elseif ($score >= 45) {
            return 2.0;
        } elseif ($score >= 40) {
            return 1.0;
        } else {
            return 0.0;
        }
    }

    /**
     * Get remark based on grade
     */
    protected function getRemark($grade)
    {
        Log::debug('Getting remark for grade', ['grade' => $grade]);

        $remarks = [
            'A1' => 'Excellent',
            'B2' => 'Very Good',
            'B3' => 'Good',
            'C4' => 'Credit',
            'C5' => 'Credit',
            'C6' => 'Credit',
            'D7' => 'Pass',
            'E8' => 'Pass',
            'F9' => 'Fail',
        ];

        $remark = $remarks[$grade] ?? 'Unknown';
        Log::debug('Remark retrieved', ['grade' => $grade, 'remark' => $remark]);

        return $remark;
    }

    /**
     * Get GPA letter grade based on GPA value.
     */
    protected function getGpaGrade($gpa)
    {
        if ($gpa >= 4.5) {
            return 'A1';
        } elseif ($gpa >= 4.0) {
            return 'B2';
        } elseif ($gpa >= 3.5) {
            return 'B3';
        } elseif ($gpa >= 3.0) {
            return 'C4';
        } elseif ($gpa >= 2.5) {
            return 'C5';
        } elseif ($gpa >= 2.0) {
            return 'C6';
        } elseif ($gpa >= 1.5) {
            return 'D7';
        } elseif ($gpa >= 1.0) {
            return 'E8';
        } else {
            return 'F9';
        }
    }

    /**
     * Compute overall GPA and CGPA for a student.
     */
    protected function computeOverallGPAAndCGPAForStudent($studentId, $schoolclass, $termId, $sessionId)
    {
        Log::info('Computing GPA/CGPA for student', [
            'student_id' => $studentId,
            'term_id'    => $termId,
            'session_id' => $sessionId,
            'class_name' => $schoolclass->schoolclass ?? 'Unknown',
        ]);

        $currentTermBroadsheets = Broadsheets::where('term_id', $termId)
            ->whereHas('broadsheetRecord', function ($q) use ($studentId, $sessionId) {
                $q->where('student_id', $studentId)->where('session_id', $sessionId);
            })
            ->get(['total']);

        Log::debug('Current term broadsheets', [
            'student_id'   => $studentId,
            'count'        => $currentTermBroadsheets->count(),
            'total_scores' => $currentTermBroadsheets->pluck('total')->toArray(),
        ]);

        $termGradePoints    = $currentTermBroadsheets->map(fn ($b) => $this->getGradePoint($b->total));
        $gpa                = $termGradePoints->avg() ?? 0.0;
        $num_subjects       = $currentTermBroadsheets->count();
        $total_grade_points = $termGradePoints->sum();

        // CGPA — average of all completed term GPAs in the current session
        $termGPAs = [];

        for ($t = 1; $t <= $termId; $t++) {
            $termBroadsheets = Broadsheets::where('term_id', $t)
                ->whereHas('broadsheetRecord', function ($q) use ($studentId, $sessionId) {
                    $q->where('student_id', $studentId)->where('session_id', $sessionId);
                })
                ->get(['total']);

            if ($termBroadsheets->isNotEmpty()) {
                $termGradePointsPast = $termBroadsheets->map(fn ($b) => $this->getGradePoint($b->total));
                $termGPA             = $termGradePointsPast->avg() ?? 0.0;

                if ($termGPA > 0) {
                    $termGPAs[] = $termGPA;
                }
            }
        }

        $cgpa     = !empty($termGPAs) ? collect($termGPAs)->avg() : 0.0;
        $gpaGrade = $this->getGpaGrade($gpa);

        $result = [
            'gpa'                => round($gpa, 2),
            'cgpa'               => round($cgpa, 2),
            'gpa_grade'          => $gpaGrade,
            'num_subjects'       => $num_subjects,
            'total_grade_points' => round($total_grade_points, 1),
            'calculated_gpa'     => $num_subjects > 0 ? round($total_grade_points / $num_subjects, 2) : 0.0,
        ];

        Log::info('GPA/CGPA computation completed', $result);

        return $result;
    }

    /**
     * Helper method to calculate positions with tie handling.
     * Returns RAW NUMERIC positions (not formatted with suffixes).
     */
    protected function calculatePositionsRaw($sortedRecords, $field)
    {
        $positionMap  = [];
        $rank         = 0;
        $lastValue    = null;
        $lastPosition = 0;

        foreach ($sortedRecords as $record) {
            $rank++;
            $currentValue = $record->$field;

            if ($lastValue !== null && $currentValue == $lastValue) {
                $positionMap[$record->id] = $lastPosition;
            } else {
                $lastPosition             = $rank;
                $lastValue                = $currentValue;
                $positionMap[$record->id] = $lastPosition;
            }
        }

        return $positionMap;
    }

    /**
     * Calculate class positions, averages, and grades for all subjects.
     * Stores RAW NUMERIC positions in database (1, 2, 3, 89, etc.)
     */
    protected function calculateClassPositionsAndAverages($schoolclassid, $sessionid, $termid)
    {
        $cacheKey = "class_metrics_{$schoolclassid}_{$sessionid}_{$termid}";

        Log::info('Starting class metrics calculation', [
            'schoolclassid' => $schoolclassid,
            'sessionid'     => $sessionid,
            'termid'        => $termid,
            'cache_key'     => $cacheKey,
        ]);

        Cache::forget($cacheKey);

        $schoolclass = Schoolclass::with(['classcategories', 'arms'])->where('id', $schoolclassid)->first(['id', 'schoolclass', 'arm']);
        if (!$schoolclass) {
            Log::error('Schoolclass not found', compact('schoolclassid', 'sessionid', 'termid'));
            return false;
        }

        $className = $schoolclass->schoolclass;
        $classIds  = Schoolclass::where('schoolclass', $className)->pluck('id')->toArray();

        if (empty($classIds)) {
            Log::error('No schoolclass IDs found for class name', compact('className', 'schoolclassid', 'sessionid', 'termid'));
            return false;
        }

        $students = Studentclass::whereIn('schoolclassid', $classIds)
            ->where('sessionid', $sessionid)
            ->pluck('studentId')
            ->toArray();

        if (empty($students)) {
            Log::error('No students found for class', compact('className', 'classIds', 'sessionid', 'termid'));
            return false;
        }

        $armId = $schoolclass->arm;

        $success = DB::transaction(function () use ($schoolclassid, $sessionid, $termid, $className, $classIds, $students, $armId) {

            $broadsheets = Broadsheets::whereIn('broadsheet_records.student_id', $students)
                ->where('broadsheets.term_id', $termid)
                ->where('broadsheet_records.session_id', $sessionid)
                ->whereIn('broadsheet_records.schoolclass_id', $classIds)
                ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
                ->join('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
                ->join('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
                ->join('schoolclass', 'schoolclass.id', '=', 'broadsheet_records.schoolclass_id')
                ->select([
                    'broadsheets.id',
                    'broadsheet_records.student_id',
                    'broadsheet_records.subject_id',
                    'subject.subject as subject_name',
                    'studentRegistration.admissionNo as admission_no',
                    'broadsheets.total',
                    'broadsheets.bf',
                    'broadsheets.cum',
                    'broadsheets.subject_position_class',
                    'broadsheets.subject_position_class_total',
                    'broadsheets.arm_position',
                    'broadsheets.arm_position_cum',
                    'broadsheets.avg',
                    'broadsheets.grade',
                    'broadsheets.remark',
                    'schoolclass.arm as student_arm_id',
                ])
                ->get();

            if ($broadsheets->isEmpty()) {
                Log::error('No broadsheet records found for class', compact('className', 'classIds', 'sessionid', 'termid', 'students'));
                return false;
            }

            $subjectGroups = $broadsheets->groupBy('subject_id');

            foreach ($subjectGroups as $subjectId => $subjectRecords) {
                $subjectName = $subjectRecords->first()->subject_name;

                // 1. CLASS POSITION (CUM) - All arms, ranked by cumulative
                $validRecordsCum = $subjectRecords->filter(fn ($r) => $r->cum != 0 && $r->cum !== null);
                $sortedByCum     = $validRecordsCum->sortByDesc('cum')->values();
                $positionMapCum  = $this->calculatePositionsRaw($sortedByCum, 'cum');

                // 2. CLASS POSITION (TOTAL) - All arms, ranked by raw total
                $validRecordsTotal = $subjectRecords->filter(fn ($r) => $r->total != 0 && $r->total !== null);
                $sortedByTotal     = $validRecordsTotal->sortByDesc('total')->values();
                $positionMapTotal  = $this->calculatePositionsRaw($sortedByTotal, 'total');

                // 3. ARM POSITION (TOTAL) - This arm only, ranked by raw total
                $armOnlyRecords       = $subjectRecords->filter(fn ($r) => $r->student_arm_id == $armId);
                $validArmRecordsTotal = $armOnlyRecords->filter(fn ($r) => $r->total != 0 && $r->total !== null);
                $sortedByArmTotal     = $validArmRecordsTotal->sortByDesc('total')->values();
                $armPositionMapTotal  = $this->calculatePositionsRaw($sortedByArmTotal, 'total');

                // 4. ARM POSITION (CUM) - This arm only, ranked by cumulative
                $validArmRecordsCum = $armOnlyRecords->filter(fn ($r) => $r->cum != 0 && $r->cum !== null);
                $sortedByArmCum     = $validArmRecordsCum->sortByDesc('cum')->values();
                $armPositionMapCum  = $this->calculatePositionsRaw($sortedByArmCum, 'cum');

                // Calculate class average
                $totalScores  = $validRecordsTotal->sum('total');
                $studentCount = $validRecordsTotal->count();
                $classAvg     = $studentCount > 0 ? round($totalScores / $studentCount, 1) : 0;

                $updatesCount = 0;

                foreach ($subjectRecords as $record) {
                    $grade  = $record->total == 0 ? '-' : $this->calculateGrade($record->total);
                    $remark = $this->getRemark($grade);

                    // Store RAW NUMBERS (not formatted strings)
                    $newPositionCum = ($record->cum == 0 || $record->cum === null) ? null :
                        ($positionMapCum[$record->id] ?? null);

                    $newPositionTotal = ($record->total == 0 || $record->total === null) ? null :
                        ($positionMapTotal[$record->id] ?? null);

                    $newArmPositionTotal = ($record->total == 0 || $record->total === null || $record->student_arm_id != $armId) ? null :
                        ($armPositionMapTotal[$record->id] ?? null);

                    $newArmPositionCum = ($record->cum == 0 || $record->cum === null || $record->student_arm_id != $armId) ? null :
                        ($armPositionMapCum[$record->id] ?? null);

                    if (
                        $record->avg != $classAvg ||
                        $record->subject_position_class != $newPositionCum ||
                        $record->subject_position_class_total != $newPositionTotal ||
                        $record->arm_position != $newArmPositionTotal ||
                        $record->arm_position_cum != $newArmPositionCum ||
                        $record->grade != $grade ||
                        $record->remark != $remark
                    ) {
                        Broadsheets::where('id', $record->id)->update([
                            'avg'                          => $classAvg,
                            'subject_position_class'       => $newPositionCum,
                            'subject_position_class_total' => $newPositionTotal,
                            'arm_position'                 => $newArmPositionTotal,
                            'arm_position_cum'             => $newArmPositionCum,
                            'grade'                        => $grade,
                            'remark'                       => $remark,
                        ]);
                        $updatesCount++;
                    }
                }

                Log::info('Subject processing completed', [
                    'subject_name'    => $subjectName,
                    'updates_applied' => $updatesCount,
                    'total_records'   => $subjectRecords->count(),
                ]);
            }

            return true;
        });

        if ($success) {
            Cache::put($cacheKey, true, now()->addHours(1));
        }

        return $success;
    }

    /**
     * Fetch attendance summary for a student in a given class/term/session.
     * Returns a structured array (never null — always has defaults).
     */
    protected function getAttendanceSummary($studentId, $schoolclassId, $termId, $sessionId): array
    {
        try {
            $record = AttendanceSummary::where('student_id', $studentId)
                ->where('schoolclass_id', $schoolclassId)
                ->where('term_id', $termId)
                ->where('session_id', $sessionId)
                ->first();

            if ($record) {
                return [
                    'total_school_days'    => $record->total_school_days    ?? 0,
                    'days_present'         => $record->days_present         ?? 0,
                    'days_absent'          => $record->days_absent          ?? 0,
                    'days_sick_leave'      => $record->days_sick_leave      ?? 0,
                    'days_excused'         => $record->days_excused         ?? 0,
                    'days_late'            => $record->days_late            ?? 0,
                    'attendance_percentage'=> $record->attendance_percentage ?? 0.0,
                    'found'                => true,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error fetching attendance summary', [
                'student_id'     => $studentId,
                'schoolclass_id' => $schoolclassId,
                'term_id'        => $termId,
                'session_id'     => $sessionId,
                'error'          => $e->getMessage(),
            ]);
        }

        // Safe defaults when no record found
        return [
            'total_school_days'     => 0,
            'days_present'          => 0,
            'days_absent'           => 0,
            'days_sick_leave'       => 0,
            'days_excused'          => 0,
            'days_late'             => 0,
            'attendance_percentage' => 0.0,
            'found'                 => false,
        ];
    }

    /**
     * Get complete student result data — includes all 4 subject position columns + attendance.
     */
 /**
 * Get complete student result data — includes all 4 subject position columns + attendance.
 */
private function getStudentResultData($id, $schoolclassid, $sessionid, $termid)
{
    try {
        Log::channel('pdf')->info('========== START getStudentResultData ==========', [
            'student_id'    => $id,
            'schoolclassid' => $schoolclassid,
            'sessionid'     => $sessionid,
            'termid'        => $termid,
            'timestamp'     => now()->toDateTimeString(),
        ]);

        if (!is_numeric($id) || !is_numeric($schoolclassid) || !is_numeric($sessionid) || !is_numeric($termid)) {
            Log::error('Invalid parameters in getStudentResultData', compact('id', 'schoolclassid', 'sessionid', 'termid'));
            return [];
        }

        $students = Student::where('studentRegistration.id', $id)
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->select([
                'studentRegistration.id as id',
                'studentRegistration.admissionNo as admissionNo',
                'studentRegistration.firstname as fname',
                'studentRegistration.lastname as lastname',
                'studentRegistration.othername as othername',
                'studentRegistration.dateofbirth as dateofbirth',
                'studentRegistration.gender as gender',
                'studentRegistration.home_address2 as present_address',
                'studentRegistration.home_address2 as permanent_address',
                'studentRegistration.updated_at as updated_at',
                'studentpicture.picture as picture',
            ])
            ->orderBy('studentRegistration.lastname', 'asc')
            ->get();

        if ($students->isEmpty()) {
            Log::error('No active student found for ID', compact('id', 'schoolclassid', 'sessionid', 'termid'));
            $students = collect([]);
        }

        $schoolclass = Schoolclass::with(['arms', 'classcategories'])->find($schoolclassid);
        $assessments = collect();

        if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            $categoryIds = $schoolclass->classcategories->pluck('id');

            try {
                if (class_exists(\App\Models\Assessment::class)) {
                    $assessments = \App\Models\Assessment::whereIn('classcategory_id', $categoryIds)
                        ->with('subAssessments')
                        ->orderBy('id')
                        ->get();
                }
            } catch (\Exception $e) {
                Log::error('Error loading assessments', ['error' => $e->getMessage()]);
            }
        }

        $scores = Broadsheets::where('broadsheet_records.student_id', $id)
            ->where('broadsheets.term_id', $termid)
            ->where('broadsheet_records.session_id', $sessionid)
            ->where('broadsheet_records.schoolclass_id', $schoolclassid)
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->join('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->orderBy('subject.subject')
            ->select([
                'subject.id as subject_id',
                'subject.subject as subject_name',
                'subject.subject_code',
                'broadsheets.total',
                'broadsheets.bf',
                'broadsheets.cum',
                'broadsheets.grade',
                'broadsheets.remark',
                'broadsheets.subject_position_class as position',
                'broadsheets.subject_position_class_total as position_total',
                'broadsheets.arm_position',
                'broadsheets.arm_position_cum',
                'broadsheets.avg as class_average',
                'broadsheets.id as broadsheet_id',
                'broadsheets.vettedstatus',
            ])->get();

        // Add formatted versions and assessment scores
        foreach ($scores as $score) {
            $score->position_formatted         = $score->position         ? $this->formatOrdinal($score->position)         : '-';
            $score->position_total_formatted   = $score->position_total   ? $this->formatOrdinal($score->position_total)   : '-';
            $score->arm_position_formatted     = $score->arm_position     ? $this->formatOrdinal($score->arm_position)     : '-';
            $score->arm_position_cum_formatted = $score->arm_position_cum ? $this->formatOrdinal($score->arm_position_cum) : '-';

            try {
                if (class_exists(\App\Models\BroadsheetAssessmentScore::class)) {
                    $assessmentScores = \App\Models\BroadsheetAssessmentScore::where('broadsheet_id', $score->broadsheet_id)
                        ->with('assessment')
                        ->orderBy('assessment_id')
                        ->get();

                    $assessmentArray = $assessmentScores->values();

                    $score->ca1  = 0;
                    $score->ca2  = 0;
                    $score->ca3  = 0;
                    $score->exam = 0;

                    if ($assessmentArray->count() > 0) $score->ca1  = $assessmentArray->get(0)->score ?? 0;
                    if ($assessmentArray->count() > 1) $score->ca2  = $assessmentArray->get(1)->score ?? 0;
                    if ($assessmentArray->count() > 2) $score->ca3  = $assessmentArray->get(2)->score ?? 0;
                    if ($assessmentArray->count() > 3) $score->exam = $assessmentArray->get(3)->score ?? 0;

                    $score->assessment_scores = $assessmentScores;
                    $score->assessments       = $assessments;
                }
            } catch (\Exception $e) {
                Log::error('Error loading assessment scores', [
                    'error'         => $e->getMessage(),
                    'broadsheet_id' => $score->broadsheet_id,
                ]);
            }
        }

        // Totals summary
        $totalObtained   = 0;
        $totalObtainable = 0;

        if ($scores && $scores->isNotEmpty()) {
            foreach ($scores as $score) {
                if ($score->total !== null && is_numeric($score->total)) {
                    $totalObtained += (float) $score->total;
                }
                $totalObtainable += 100;
            }
        }

        $totalPercentage = $totalObtainable > 0
            ? round(($totalObtained / $totalObtainable) * 100, 1)
            : 0;

        $totalsSummary = [
            'obtained'   => round($totalObtained, 1),
            'obtainable' => $totalObtainable,
            'percentage' => $totalPercentage,
        ];

        // GPA / CGPA
        $gpaData = [];
        if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            try {
                $gpaData = $this->computeOverallGPAAndCGPAForStudent($id, $schoolclass, $termid, $sessionid);
            } catch (\Exception $e) {
                Log::error('Error calculating GPA/CGPA', ['student_id' => $id, 'error' => $e->getMessage()]);
                $gpaData = [
                    'gpa'                => 0.0,
                    'cgpa'               => 0.0,
                    'gpa_grade'          => 'F9',
                    'num_subjects'       => 0,
                    'total_grade_points' => 0,
                    'calculated_gpa'     => 0.0,
                ];
            }
        }

        // ==================== STUDENT PERSONALITY PROFILE + PRINCIPAL COMMENT FALLBACK ====================
        try {
            $studentpp = Studentpersonalityprofile::where('studentpersonalityprofiles.studentid', $id)
                ->where('studentpersonalityprofiles.termid', $termid)
                ->where('studentpersonalityprofiles.sessionid', $sessionid)
                ->where('studentpersonalityprofiles.schoolclassid', $schoolclassid)
                ->join('schoolsession', 'schoolsession.id', '=', 'studentpersonalityprofiles.sessionid')
                ->join('schoolterm', 'schoolterm.id', '=', 'studentpersonalityprofiles.termid')
                ->join('schoolclass', 'schoolclass.id', '=', 'studentpersonalityprofiles.schoolclassid')
                ->select(
                    'studentpersonalityprofiles.*',
                    'schoolsession.session as session',
                    'schoolterm.term as term',
                    'schoolclass.schoolclass as schoolclass'
                )
                ->get();

            // PRINCIPAL COMMENT FALLBACK
            if ($studentpp->isEmpty() || empty(trim($studentpp->first()->principalscomment ?? ''))) {
                Log::info('Principal comment missing - applying fallback', [
                    'student_id' => $id,
                    'schoolclassid' => $schoolclassid,
                    'sessionid' => $sessionid,
                    'termid' => $termid,
                ]);

                $classPrincipal = \App\Models\Principalscomment::with('staff')
                    ->where('schoolclassid', $schoolclassid)
                    ->where('sessionid', $sessionid)
                    ->where('termid', $termid)
                    ->first();

                if ($classPrincipal && $classPrincipal->staff) {
                    $principalName = $classPrincipal->staff->name ?? 'Principal';

                    $profile = Studentpersonalityprofile::firstOrCreate(
                        [
                            'studentid'     => $id,
                            'schoolclassid' => $schoolclassid,
                            'sessionid'     => $sessionid,
                            'termid'        => $termid,
                        ],
                        [
                            'staffid' => $classPrincipal->staffId,
                        ]
                    );

                    if (empty(trim($profile->principalscomment ?? ''))) {
                        $profile->update([
                            'principalscomment' => "Principal: {$principalName}"
                        ]);
                    }

                    // Refresh profile data
                    $studentpp = Studentpersonalityprofile::where('studentid', $id)
                        ->where('schoolclassid', $schoolclassid)
                        ->where('sessionid', $sessionid)
                        ->where('termid', $termid)
                        ->join('schoolsession', 'schoolsession.id', '=', 'studentpersonalityprofiles.sessionid')
                        ->join('schoolterm', 'schoolterm.id', '=', 'studentpersonalityprofiles.termid')
                        ->join('schoolclass', 'schoolclass.id', '=', 'studentpersonalityprofiles.schoolclassid')
                        ->select(
                            'studentpersonalityprofiles.*',
                            'schoolsession.session as session',
                            'schoolterm.term as term',
                            'schoolclass.schoolclass as schoolclass'
                        )
                        ->get();

                    Log::info('Principal comment fallback successfully applied', [
                        'student_id' => $id,
                        'principal'  => $principalName
                    ]);
                }
            }

            if ($studentpp->isEmpty()) {
                $studentpp = collect();
            }
        } catch (\Exception $e) {
            Log::error('Error fetching student personality profile', [
                'student_id' => $id,
                'error'      => $e->getMessage()
            ]);
            $studentpp = collect();
        }

        // Remaining data
        $schoolsession    = Schoolsession::where('id', $sessionid)->first();
        $schoolterm       = Schoolterm::where('id', $termid)->first();
        $numberOfStudents = Studentclass::where('schoolclassid', $schoolclassid)->where('sessionid', $sessionid)->count();

        $schoolInfo = SchoolInformation::first();
        if (!$schoolInfo) {
            $schoolInfo                        = new \stdClass();
            $schoolInfo->id                    = 0;
            $schoolInfo->school_name           = 'School Name Not Found';
            $schoolInfo->school_logo           = null;
            $schoolInfo->school_stamp          = null;
            $schoolInfo->school_motto          = 'Motto Not Found';
            $schoolInfo->school_address        = 'Address Not Found';
            $schoolInfo->school_phone          = 'Phone Not Found';
            $schoolInfo->date_school_opened    = null;
            $schoolInfo->date_next_term_begins = null;
        } else {
            $schoolInfo->school_stamp = $schoolInfo->school_stamp ?? null;
        }

        // Promotion status
        $promotionStatusValue = null;
        try {
            $promotionStatus = PromotionStatus::where('student_id', $id)
                ->where('session_id', $sessionid)
                ->where('term_id', $termid)
                ->first();
            if ($promotionStatus) {
                $promotionStatusValue = $promotionStatus->status;
            }
        } catch (\Exception $e) {
            Log::error('Error fetching promotion status', ['student_id' => $id, 'error' => $e->getMessage()]);
        }

        // Compulsory subjects
        $compulsorySubjects = [];
        try {
            $compulsorySubjects = CompulsorySubjectClass::where('class_id', $schoolclassid)
                ->pluck('subject_id')
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error fetching compulsory subjects', ['class_id' => $schoolclassid, 'error' => $e->getMessage()]);
        }

        if ($scores) {
            foreach ($scores as $score) {
                $score->is_compulsory = in_array($score->subject_id, $compulsorySubjects);
            }
        }

        // Attendance
        $attendanceSummary = $this->getAttendanceSummary($id, $schoolclassid, $termid, $sessionid);

        $result = [
            'students'             => $students,
            'studentpp'            => $studentpp,
            'scores'               => $scores,
            'studentid'            => $id,
            'schoolclassid'        => $schoolclassid,
            'sessionid'            => $sessionid,
            'termid'               => $termid,
            'schoolclass'          => $schoolclass,
            'schoolterm'           => $schoolterm,
            'schoolsession'        => $schoolsession,
            'numberOfStudents'     => $numberOfStudents,
            'schoolInfo'           => $schoolInfo,
            'promotionStatusValue' => $promotionStatusValue,
            'assessments'          => $assessments,
            'compulsorySubjects'   => $compulsorySubjects,
            'gpa_data'             => $gpaData,
            'totals_summary'       => $totalsSummary,
            'attendance_summary'   => $attendanceSummary,
        ];

        Log::channel('pdf')->info('========== END getStudentResultData ==========', [
            'student_id'     => $id,
            'students_count' => $students->count() ?? 0,
            'scores_count'   => $scores ? $scores->count() : 0,
            'has_profile'    => $studentpp->isNotEmpty(),
            'has_principal_comment' => $studentpp->isNotEmpty() ? !empty($studentpp->first()->principalscomment) : false,
        ]);

        return $result;

    } catch (Exception $e) {
        Log::channel('pdf')->error('========== ERROR in getStudentResultData ==========', [
            'student_id'    => $id,
            'error_message' => $e->getMessage(),
            'error_file'    => $e->getFile(),
            'error_line'    => $e->getLine(),
        ]);
        return [];
    }
}

    /**
     * Get column options for PDF generation — includes all 4 position columns + attendance.
     */
    public function getColumnOptions(Request $request)
    {
        $schoolclassid = $request->input('schoolclassid');
        $sessionid     = $request->input('sessionid');
        $termid        = $request->input('termid');

        if (!$schoolclassid || !$sessionid || !$termid) {
            return response()->json(['success' => false, 'message' => 'Missing parameters'], 400);
        }

        $schoolclass = Schoolclass::with('classcategories')->find($schoolclassid);
        $assessments = collect();

        if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            $categoryIds = $schoolclass->classcategories->pluck('id');
            try {
                if (class_exists(\App\Models\Assessment::class)) {
                    $assessments = \App\Models\Assessment::whereIn('classcategory_id', $categoryIds)
                        ->with('subAssessments')
                        ->orderBy('id')
                        ->get();
                }
            } catch (\Exception $e) {
                Log::error('Error loading assessments for column options', ['error' => $e->getMessage()]);
            }
        }

        $columns = [
            'student_info' => [
                'sn'           => ['label' => 'SN',            'default' => true],
                'admission_no' => ['label' => 'Admission No',  'default' => true],
                'name'         => ['label' => 'Name',          'default' => true],
                'picture'      => ['label' => 'Picture',       'default' => true],
                'gender'       => ['label' => 'Gender',        'default' => false],
                'dob'          => ['label' => 'Date of Birth', 'default' => false],
            ],
            'assessments' => [],
            'scores' => [
                'total'            => ['label' => 'Total',                       'default' => true],
                'bf'               => ['label' => 'BF',                          'default' => true],
                'cum'              => ['label' => 'Cum',                         'default' => true],
                'grade'            => ['label' => 'Grade',                       'default' => true],
                'position'         => ['label' => 'Class Pos (Cum) — All Arms',  'default' => true],
                'position_total'   => ['label' => 'Class Pos (Total) — All Arms','default' => true],
                'arm_position'     => ['label' => 'Arm Pos (Total) — This Arm',  'default' => true],
                'arm_position_cum' => ['label' => 'Arm Pos (Cum) — This Arm',    'default' => true],
                'class_average'    => ['label' => 'Class Avg',                   'default' => true],
            ],
            'gpa_metrics' => [
                'num_subjects'       => ['label' => 'Num Subjects', 'default' => true],
                'total_grade_points' => ['label' => 'Total GP',     'default' => true],
                'gpa'                => ['label' => 'GPA',           'default' => true],
                'calculated_gpa'     => ['label' => 'Calc GPA',      'default' => true],
                'gpa_grade'          => ['label' => 'GPA Grade',     'default' => true],
                'cgpa'               => ['label' => 'CGPA',          'default' => true],
            ],
            // ── Attendance columns ──────────────────────────────────────
            'attendance' => [
                'attendance_days_present'    => ['label' => 'Days Present',    'default' => true],
                'attendance_days_absent'     => ['label' => 'Days Absent',     'default' => true],
                'attendance_days_late'       => ['label' => 'Days Late',       'default' => false],
                'attendance_sick_leave'      => ['label' => 'Sick Leave',      'default' => false],
                'attendance_excused'         => ['label' => 'Excused',         'default' => false],
                'attendance_total_days'      => ['label' => 'Total School Days','default' => true],
                'attendance_percentage'      => ['label' => 'Attendance %',    'default' => true],
            ],
            'other' => [
                'compulsory_flag' => ['label' => 'Compulsory',    'default' => false],
                'vetted_status'   => ['label' => 'Vetted Status', 'default' => true],
            ],
        ];

        foreach ($assessments as $assessment) {
            $columns['assessments'][$assessment->id] = [
                'label'               => $assessment->name . ' (' . $assessment->max_score . ')',
                'default'             => true,
                'is_assessment'       => true,
                'max_score'           => $assessment->max_score,
                'has_sub_assessments' => $assessment->subAssessments->isNotEmpty(),
            ];
        }

        return response()->json([
            'success'           => true,
            'columns'           => $columns,
            'assessments_count' => $assessments->count(),
            'is_senior'         => $schoolclass && $schoolclass->classcategories->isNotEmpty()
                ? ($schoolclass->classcategories->first()->is_senior ?? false)
                : false,
        ]);
    }

    /**
     * Calculate grade preview for AJAX requests
     */
    public function calculateGradePreview(Request $request)
    {
        $request->validate([
            'schoolclass_id' => 'required|exists:schoolclass,id',
            'total'          => 'required|numeric|min:0|max:100',
        ]);

        $grade = $this->calculateGrade($request->total);

        return response()->json(['grade' => $grade]);
    }

    /**
     * Display student result view
     */
    public function studentresult($id, $schoolclassid, $sessionid, $termid)
    {
        $pagetitle         = "Student Personality Profile";
        $metricsCalculated = $this->calculateClassPositionsAndAverages($schoolclassid, $sessionid, $termid);

        if (!$metricsCalculated) {
            return back()->with('error', 'Failed to calculate class metrics. Please try again.');
        }

        $data = $this->getStudentResultData($id, $schoolclassid, $sessionid, $termid);

        return view('studentreports.studentresult')->with($data)->with('pagetitle', $pagetitle);
    }

    /**
     * Display student mock result
     */
    public function studentmockresult($id, $schoolclassid, $sessionid, $termid)
    {
        $pagetitle         = "Student Mock Result";
        $metricsCalculated = $this->calculateClassPositionsAndAverages($schoolclassid, $sessionid, $termid);

        if (!$metricsCalculated) {
            return back()->with('error', 'Failed to calculate class metrics. Please try again.');
        }

        $data = $this->getStudentResultData($id, $schoolclassid, $sessionid, $termid);

        return view('studentreports.studentmockresult')->with($data)->with('pagetitle', $pagetitle);
    }

    /**
     * Display class broadsheet
     */
    public function classBroadsheet($schoolclassid, $sessionid, $termid): View
    {
        $class     = Schoolclass::findOrFail($schoolclassid);
        $session   = Schoolsession::findOrFail($sessionid);
        $term      = $termid;
        $pagetitle = "Broadsheet for {$class->schoolclass} - {$session->session} - Term {$term}";

        return view('studentreports.broadsheet', [
            'class'     => $class,
            'session'   => $session,
            'term'      => $term,
            'pagetitle' => $pagetitle,
        ]);
    }

    /**
     * Get registered classes
     */
    public function registeredClasses(Request $request)
    {
        $classId   = $request->query('class_id');
        $sessionId = $request->query('session_id');

        if (!$classId || !$sessionId || $classId === 'ALL' || $sessionId === 'ALL') {
            return response()->json(['success' => false, 'message' => 'Please select a valid class and session.'], 400);
        }

        $classes = Studentclass::query()
            ->join('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->join('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
            ->where('schoolclass.id', $classId)
            ->where('schoolsession.id', $sessionId)
            ->where('schoolsession.status', 'Current')
            ->groupBy('schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm', 'schoolsession.session')
            ->selectRaw('
                schoolclass.schoolclass as class_name,
                schoolarm.arm as name_arm,
                schoolsession.session as session_name,
                COUNT(DISTINCT studentclass.studentId) as student_count
            ')
            ->get();

        return response()->json(['success' => true, 'data' => $classes]);
    }

    /**
     * Export single student result as PDF
     */
    public function exportStudentResultPdf($id, $schoolclassid, $sessionid, $termid)
    {
        try {
            Log::channel('pdf')->info('========== START SINGLE STUDENT PDF EXPORT ==========', [
                'student_id'    => $id,
                'schoolclassid' => $schoolclassid,
                'sessionid'     => $sessionid,
                'termid'        => $termid,
            ]);

            ini_set('max_execution_time', 600);
            ini_set('memory_limit', '1024M');

            $metricsCalculated = $this->calculateClassPositionsAndAverages($schoolclassid, $sessionid, $termid);
            if (!$metricsCalculated) {
                return back()->with('error', 'Failed to calculate class metrics. Please try again.');
            }

            $data = $this->getStudentResultData($id, $schoolclassid, $sessionid, $termid);

            if (empty($data) || empty($data['students']) || $data['students']->isEmpty()) {
                return back()->with('error', 'No student data found for the provided parameters.');
            }

            $this->fixImagePaths([$data]);

            $student     = $data['students']->first();
            $studentName = $student ? $student->fname . '_' . $student->lastname : 'Student';
            $filename    = 'Terminal_Report_' . $studentName . '_' . $data['schoolsession']->session . '_Term_' . $data['termid'] . '.pdf';

            $pdf = Pdf::loadView('studentreports.studentresult_pdf', ['data' => $data])
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'dpi'                     => 150,
                    'defaultFont'             => 'DejaVu Sans',
                    'isRemoteEnabled'         => true,
                    'isHtml5ParserEnabled'    => true,
                    'isFontSubsettingEnabled' => true,
                    'isPhpEnabled'            => false,
                    'chroot'                  => [public_path(), storage_path()],
                    'fontCache'               => storage_path('fonts/'),
                    'logOutputFile'           => storage_path('logs/dompdf.log'),
                ]);

            return $pdf->download($filename);

        } catch (Exception $e) {
            Log::channel('pdf')->error('========== ERROR SINGLE STUDENT PDF EXPORT ==========', [
                'student_id'    => $id,
                'error_message' => $e->getMessage(),
                'error_file'    => $e->getFile(),
                'error_line'    => $e->getLine(),
            ]);

            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export class results as PDF
     */
    public function exportClassResultsPdf(Request $request)
    {
        try {
            Log::info('========== START CLASS PDF EXPORT ==========', [
                'request_data' => $request->all(),
                'timestamp'    => now()->toDateTimeString(),
            ]);

            $this->checkServerRequirements();
            $this->debugStorageStructure();

            ini_set('max_execution_time', 300);
            ini_set('memory_limit', '512M');

            $schoolclassid   = $request->input('schoolclassid');
            $sessionid       = $request->input('sessionid');
            $termid          = $request->input('termid', 3);
            $studentIds      = $request->input('studentIds', []);
            $selectedColumns = $request->input('selectedColumns', []);

            if (!$schoolclassid || !$sessionid || !$termid) {
                return response()->json(['success' => false, 'message' => 'Missing required parameters'], 400);
            }

            $metricsCalculated = $this->calculateClassPositionsAndAverages($schoolclassid, $sessionid, $termid);
            if (!$metricsCalculated) {
                return response()->json(['success' => false, 'message' => 'Failed to calculate class metrics.'], 500);
            }

            $allStudentData = [];
            $processedCount = 0;
            $failedCount    = 0;

            foreach ($studentIds as $index => $studentId) {
                $studentData = $this->getStudentResultData($studentId, $schoolclassid, $sessionid, $termid);

                if (
                    !empty($studentData) &&
                    !empty($studentData['students']) &&
                    $studentData['students']->isNotEmpty()
                ) {
                    $studentData['selected_columns'] = $selectedColumns;
                    $allStudentData[]                = $studentData;
                    $processedCount++;
                } else {
                    $failedCount++;
                    Log::warning('Skipped student due to empty data', ['student_id' => $studentId]);
                }
            }

            if (empty($allStudentData)) {
                $this->debugStudentQuery($studentIds, $schoolclassid, $sessionid, $termid);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process student data.',
                ], 500);
            }

            $this->fixImagePaths($allStudentData);

            $schoolclass   = Schoolclass::where('id', $schoolclassid)->with(['arms', 'classcategories'])->first(['id', 'schoolclass', 'arm']);
            $schoolsession = Schoolsession::where('id', $sessionid)->value('session') ?? 'N/A';
            $term          = $this->getTermName($termid);
            $className     = $schoolclass
                ? ($schoolclass->schoolclass . ($schoolclass->arms ? $schoolclass->arms->arm : ''))
                : 'Class';

            $filename = 'Class_Results_'
                . preg_replace('/[^A-Za-z0-9_-]/', '_', $className) . '_'
                . preg_replace('/[^A-Za-z0-9_-]/', '_', $schoolsession) . '_'
                . $term . '.pdf';

            $viewName = 'studentreports.class_results_pdf';
            if (!view()->exists($viewName)) {
                return response()->json(['success' => false, 'message' => 'PDF template view not found: ' . $viewName], 500);
            }

            $viewData = [
                'allStudentData' => $allStudentData,
                'metadata'       => [
                    'class_name'       => $className,
                    'session'          => $schoolsession,
                    'term'             => $term,
                    'generation_date'  => now()->format('Y-m-d H:i:s'),
                    'student_count'    => count($allStudentData),
                    'selected_columns' => $selectedColumns,
                ],
            ];

            $pdf = Pdf::loadView($viewName, $viewData)
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'dpi'                     => 96,
                    'defaultFont'             => 'DejaVu Sans',
                    'isRemoteEnabled'         => true,
                    'isHtml5ParserEnabled'    => true,
                    'isFontSubsettingEnabled' => true,
                    'isPhpEnabled'            => false,
                    'chroot'                  => [public_path(), storage_path()],
                    'tempDir'                 => storage_path('app/temp/'),
                    'fontCache'               => storage_path('fonts/'),
                    'logOutputFile'           => storage_path('logs/dompdf.log'),
                    'isJavascriptEnabled'     => false,
                    'enable_css_float'        => true,
                ]);

            $pdfContent = $pdf->output();

            if (empty($pdfContent)) {
                return response()->json(['success' => false, 'message' => 'Generated PDF content is empty'], 500);
            }

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
                ->header('Content-Length', strlen($pdfContent));

        } catch (Exception $e) {
            Log::error('========== ERROR CLASS PDF EXPORT ==========', [
                'error_message' => $e->getMessage(),
                'error_file'    => $e->getFile(),
                'error_line'    => $e->getLine(),
            ]);

            return response()->json([
                'success'    => false,
                'message'    => 'Failed to generate PDF: ' . $e->getMessage(),
                'error_type' => get_class($e),
            ], 500);
        }
    }

    // =========================================================================
    // IMAGE HELPERS
    // =========================================================================

    private function checkServerRequirements()
    {
        $checks = [
            'storage_writable'       => is_writable(storage_path()),
            'temp_dir_writable'      => is_writable(storage_path('app/temp')),
            'dompdf_installed'       => class_exists('Barryvdh\DomPDF\PDF'),
            'php_memory_limit'       => ini_get('memory_limit'),
            'php_max_execution_time' => ini_get('max_execution_time'),
        ];

        Log::info('Server requirements check', $checks);

        if (!$checks['temp_dir_writable']) {
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
        }

        return $checks;
    }

    private function getAbsoluteImagePath($path, $isStudent = false)
    {
        if (empty($path)) return null;

        if (str_starts_with($path, public_path()) || str_starts_with($path, storage_path())) {
            return file_exists($path) ? $path : null;
        }

        if (str_starts_with($path, 'data:image')) return null;

        $path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
        $path = preg_replace('/^(http:\/\/|https:\/\/|\/\/)[^\/]+/', '', $path);
        $path = ltrim($path, DIRECTORY_SEPARATOR);

        $possiblePaths = $isStudent ? [
            public_path('storage/student_avatars/' . $path),
            storage_path('app/public/student_avatars/' . $path),
            public_path('storage/' . $path),
            storage_path('app/public/' . $path),
            public_path($path),
            storage_path($path),
        ] : [
            storage_path('app/public/' . $path),
            public_path('storage/' . $path),
            storage_path('app/public/school_logos/' . basename($path)),
            public_path('storage/school_logos/' . basename($path)),
            public_path($path),
            storage_path($path),
        ];

        foreach (array_unique($possiblePaths) as $fullPath) {
            if (file_exists($fullPath)) return $fullPath;
        }

        return null;
    }

    private function imageToBase64($imagePath)
    {
        if (str_starts_with((string) $imagePath, 'data:image')) return $imagePath;

        if (!$imagePath || !file_exists($imagePath)) {
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
                    <rect width="100" height="100" fill="#f0f0f0"/>
                    <circle cx="50" cy="40" r="15" fill="#ddd"/>
                    <rect x="35" y="60" width="30" height="25" fill="#ddd" rx="2"/>
                </svg>';
            return 'data:image/svg+xml;base64,' . base64_encode($svg);
        }

        try {
            $imageData = file_get_contents($imagePath);
            if (empty($imageData)) throw new \Exception('Image file is empty');

            $mimeType = mime_content_type($imagePath);
            if (!$mimeType) {
                $ext      = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
                $mimeType = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
                             'gif' => 'image/gif', 'svg' => 'image/svg+xml', 'webp' => 'image/webp'][$ext] ?? 'image/jpeg';
            }

            return "data:{$mimeType};base64," . base64_encode($imageData);
        } catch (\Exception $e) {
            Log::error('Failed to convert image to base64', ['path' => $imagePath, 'error' => $e->getMessage()]);
            return 'data:image/svg+xml;base64,' . base64_encode(
                '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><rect width="100" height="100" fill="#f8f9fa"/></svg>'
            );
        }
    }

    private function fixImagePaths(&$studentData)
    {
        $defaultStudentImage = public_path('storage/student_avatars/unnamed.jpg');
        $defaultSchoolLogo   = public_path('storage/school_logos/default.jpg');

        if (!file_exists($defaultStudentImage)) {
            $dir = dirname($defaultStudentImage);
            if (!file_exists($dir)) mkdir($dir, 0755, true);
            $this->createPlaceholderImage($defaultStudentImage, 'Student');
        }

        if (!file_exists($defaultSchoolLogo)) {
            $dir = dirname($defaultSchoolLogo);
            if (!file_exists($dir)) mkdir($dir, 0755, true);
            $this->createPlaceholderImage($defaultSchoolLogo, 'School');
        }

        foreach ($studentData as &$student) {
            if (isset($student['students']) && $student['students']->isNotEmpty() && $student['students']->first()->picture) {
                $absolutePath                    = $this->getAbsoluteImagePath($student['students']->first()->picture, true);
                $student['student_image_base64'] = $absolutePath && file_exists($absolutePath)
                    ? $this->imageToBase64($absolutePath)
                    : $this->imageToBase64($defaultStudentImage);
            } else {
                $student['student_image_base64'] = $this->imageToBase64($defaultStudentImage);
            }

            if (isset($student['schoolInfo']) && !empty($student['schoolInfo']->school_logo)) {
                $absolutePath                  = $this->getAbsoluteImagePath($student['schoolInfo']->school_logo, false);
                $student['school_logo_base64'] = ($absolutePath && file_exists($absolutePath) && filesize($absolutePath) > 100)
                    ? $this->imageToBase64($absolutePath)
                    : $this->imageToBase64($defaultSchoolLogo);
            } else {
                $student['school_logo_base64'] = $this->imageToBase64($defaultSchoolLogo);
            }

            if (isset($student['schoolInfo']) && !empty($student['schoolInfo']->school_stamp)) {
                $absolutePath                   = $this->getAbsoluteImagePath($student['schoolInfo']->school_stamp, false);
                $student['school_stamp_base64'] = ($absolutePath && file_exists($absolutePath) && filesize($absolutePath) > 100)
                    ? $this->imageToBase64($absolutePath)
                    : null;
            } else {
                $student['school_stamp_base64'] = null;
            }
        }
    }

    private function createPlaceholderImage($path, $text)
    {
        try {
            $width  = 300;
            $height = 200;
            $image  = imagecreatetruecolor($width, $height);
            $bg     = imagecolorallocate($image, 240, 240, 240);
            $tc     = imagecolorallocate($image, 153, 153, 153);
            imagefill($image, 0, 0, $bg);
            $font = 5;
            $x    = ($width - imagefontwidth($font) * strlen($text)) / 2;
            $y    = ($height - imagefontheight($font)) / 2;
            imagestring($image, $font, $x, $y, $text, $tc);
            imagejpeg($image, $path, 80);
            imagedestroy($image);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to create placeholder image', ['path' => $path, 'error' => $e->getMessage()]);
            return false;
        }
    }

    private function debugStorageStructure()
    {
        $paths = [
            'storage/app/public'             => storage_path('app/public'),
            'public/storage'                 => public_path('storage'),
            'public/storage/school_logos'    => public_path('storage/school_logos'),
            'public/storage/student_avatars' => public_path('storage/student_avatars'),
        ];

        foreach ($paths as $name => $path) {
            Log::info("Storage path check: {$name}", [
                'path'   => $path,
                'exists' => file_exists($path),
                'is_dir' => is_dir($path),
            ]);
        }
    }

    private function getTermName($termid)
    {
        return [1 => 'First Term', 2 => 'Second Term', 3 => 'Third Term'][$termid] ?? 'Unknown Term';
    }

    private function debugStudentQuery($studentIds, $schoolclassid, $sessionid, $termid)
    {
        $studentsExist = Student::whereIn('id', $studentIds)->count();
        $broadsheets   = DB::table('broadsheets')
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->whereIn('broadsheet_records.student_id', $studentIds)
            ->where('broadsheet_records.schoolclass_id', $schoolclassid)
            ->where('broadsheet_records.session_id', $sessionid)
            ->where('broadsheets.term_id', $termid)
            ->count();

        Log::info('DEBUG: Student data checks', [
            'students_found' => $studentsExist,
            'broadsheets'    => $broadsheets,
        ]);
    }

    // =========================================================================
    // INDEX
    // =========================================================================

    public function index(Request $request): View|JsonResponse
    {
        $pagetitle   = "Student Terminal Report Management";
        $current     = "Current";
        $allstudents = new LengthAwarePaginator([], 0, 10);

        if (
            $request->filled('schoolclassid') && $request->filled('sessionid') &&
            $request->input('schoolclassid') !== 'ALL' && $request->input('sessionid') !== 'ALL'
        ) {
            $query = Studentclass::query()
                ->where('schoolclassid', $request->input('schoolclassid'))
                ->where('sessionid', $request->input('sessionid'))
                ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'studentclass.studentId')
                ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
                ->leftJoin('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
                ->where('schoolsession.status', '=', $current);

            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('studentRegistration.admissionNo', 'like', "%{$search}%")
                      ->orWhere('studentRegistration.firstname', 'like', "%{$search}%")
                      ->orWhere('studentRegistration.lastname', 'like', "%{$search}%")
                      ->orWhere('studentRegistration.othername', 'like', "%{$search}%");
                });
            }

            $allstudents = $query->select([
                'studentRegistration.admissionNo as admissionno',
                'studentRegistration.firstname as firstname',
                'studentRegistration.lastname as lastname',
                'studentRegistration.othername as othername',
                'studentRegistration.gender as gender',
                'studentRegistration.id as stid',
                'studentpicture.picture as picture',
                'studentclass.schoolclassid as schoolclassID',
                'studentclass.sessionid as sessionid',
                'schoolclass.schoolclass as schoolclass',
                'schoolarm.arm as schoolarm',
                'schoolsession.session as session',
            ])->latest('studentclass.created_at')->paginate(100);
        }

        $schoolsessions = Schoolsession::where('status', 'Current')->get();
        $schoolclasses  = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm']);

        if ($request->ajax()) {
            return response()->json([
                'tableBody'    => view('studentreports.partials.student_rows', compact('allstudents'))->render(),
                'pagination'   => $allstudents->links('pagination::bootstrap-5')->render(),
                'studentCount' => $allstudents->total(),
            ]);
        }

        return view('studentreports.index', compact('allstudents', 'schoolsessions', 'schoolclasses', 'pagetitle'));
    }

    /**
     * Fetch mock scores for the drawer — mirrors the query in
     * ViewStudentMockReportController::getStudentMockResultData() but returns
     * a plain serialisable array ready for JSON.
     */
    private function fetchMockScoresForDrawer($studentId, $schoolclassId, $sessionId, $termId): array
    {
        try {
            $rows = BroadsheetsMock::where('broadsheet_records_mock.student_id', $studentId)
                ->where('broadsheetmock.term_id', $termId)
                ->where('broadsheet_records_mock.session_id', $sessionId)
                ->where('broadsheet_records_mock.schoolclass_id', $schoolclassId)
                ->join('broadsheet_records_mock', 'broadsheet_records_mock.id', '=', 'broadsheetmock.broadsheet_records_mock_id')
                ->join('subject', 'subject.id', '=', 'broadsheet_records_mock.subject_id')
                ->orderBy('subject.subject')
                ->select([
                    'subject.subject as subject_name',
                    'subject.subject_code',
                    'broadsheetmock.exam',
                    'broadsheetmock.total',
                    'broadsheetmock.grade',
                    'broadsheetmock.remark',
                    'broadsheetmock.subject_position_class as position',
                    'broadsheetmock.avg as class_average',
                    'broadsheetmock.cmin',
                    'broadsheetmock.cmax',
                ])
                ->get();

            return $rows->map(function ($r) {
                return [
                    'subject_name'  => $r->subject_name,
                    'subject_code'  => $r->subject_code,
                    'exam'          => $r->exam  !== null ? (float) $r->exam  : null,
                    'total'         => $r->total !== null ? (float) $r->total : null,
                    'grade'         => $r->grade,
                    'remark'        => $r->remark,
                    'position'      => $r->position,
                    'class_average' => $r->class_average !== null ? (float) $r->class_average : null,
                    'cmin'          => $r->cmin !== null ? (float) $r->cmin : null,
                    'cmax'          => $r->cmax !== null ? (float) $r->cmax : null,
                ];
            })->values()->toArray();

        } catch (\Exception $e) {
            Log::error('fetchMockScoresForDrawer error', [
                'student_id' => $studentId,
                'error'      => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Drawer data endpoint — returns full student result data (including dynamic
     * assessments) plus personality profile, attendance, picture URL.
     * Called by the profile drawer via AJAX.
     */
    public function drawerData($studentId, $schoolclassId, $sessionId, $termId)
    {
        try {
            if (
                !is_numeric($studentId) || !is_numeric($schoolclassId) ||
                !is_numeric($sessionId) || !is_numeric($termId)
            ) {
                return response()->json(['success' => false, 'message' => 'Invalid parameters'], 400);
            }

            // ── 1. Full result data (scores + dynamic assessments + attendance) ──
            $resultData = $this->getStudentResultData($studentId, $schoolclassId, $sessionId, $termId);

            if (empty($resultData)) {
                return response()->json(['success' => false, 'message' => 'No data found'], 404);
            }

            // ── 2. Personality profile ──────────────────────────────────────────
            $profile = null;
            try {
                $profile = Studentpersonalityprofile::where('studentid', $studentId)
                    ->where('termid', $termId)
                    ->where('sessionid', $sessionId)
                    ->where('schoolclassid', $schoolclassId)
                    ->first();
            } catch (\Exception $e) {
                Log::error('drawerData: error fetching personality profile', ['error' => $e->getMessage()]);
            }

            // ── 3. Student basics ───────────────────────────────────────────────
            $student   = $resultData['students']->first();
            $schoolclass = $resultData['schoolclass'];
            $schoolterm  = $resultData['schoolterm'];
            $schoolsession = $resultData['schoolsession'];

            $fullName = trim(
                strtoupper($student->lastname ?? '') . ' ' .
                ($student->fname ?? '') . ' ' .
                ($student->othername ?? '')
            );

            // ── 4. Serialise assessments metadata ───────────────────────────────
            $assessmentsMeta = ($resultData['assessments'] ?? collect())->map(function ($a) {
                return [
                    'id'        => $a->id,
                    'name'      => $a->name,
                    'max_score' => (float) $a->max_score,
                ];
            })->values()->toArray();

            // ── 5. Serialise scores with dynamic assessment_scores ───────────────
            $scores = ($resultData['scores'] ?? collect())->map(function ($score) {
                // assessment_scores is already a collection on the score object
                $assessmentScores = [];
                if (isset($score->assessment_scores)) {
                    foreach ($score->assessment_scores as $as) {
                        $assessmentScores[] = [
                            'assessment_id' => $as->assessment_id,
                            'score'         => $as->score !== null ? (float) $as->score : null,
                        ];
                    }
                }

                return [
                    'subject_name'      => $score->subject_name,
                    'subject_code'      => $score->subject_code,
                    'assessment_scores' => $assessmentScores,
                    'total'             => $score->total !== null ? (float) $score->total : null,
                    'bf'                => $score->bf    !== null ? (float) $score->bf    : null,
                    'cum'               => $score->cum   !== null ? (float) $score->cum   : null,
                    'grade'             => $score->grade,
                    'remark'            => $score->remark,
                    'position'          => $score->position_formatted     ?? ($score->position     ? $this->formatOrdinal($score->position)     : '-'),
                    'position_total'    => $score->position_total_formatted ?? ($score->position_total ? $this->formatOrdinal($score->position_total) : '-'),
                    'arm_position'      => $score->arm_position_formatted  ?? ($score->arm_position  ? $this->formatOrdinal($score->arm_position)  : '-'),
                    'arm_position_cum'  => $score->arm_position_cum_formatted ?? ($score->arm_position_cum ? $this->formatOrdinal($score->arm_position_cum) : '-'),
                    'class_average'     => $score->class_average !== null ? (float) $score->class_average : null,
                    'is_compulsory'     => $score->is_compulsory ?? false,
                    'vettedstatus'      => $score->vettedstatus,
                ];
            })->values()->toArray();

            // ── 6. Picture URL ──────────────────────────────────────────────────
            $pictureUrl = null;
            if ($student && $student->picture) {
                $pictureUrl = asset('storage/student_avatars/' . basename($student->picture));
            }

            // ── 7. Attendance ───────────────────────────────────────────────────
            $attendance = $resultData['attendance_summary'] ?? [];
            if ($schoolterm) {
                $attendance['term_name'] = $schoolterm->term ?? null;
            }

            // ── 8. Build response ───────────────────────────────────────────────
            return response()->json([
                'success'       => true,
                'student_name'  => $fullName,
                'admissionno'   => $student->admissionNo ?? '—',
                'gender'        => $student->gender      ?? '—',
                'schoolclass'   => trim(($schoolclass->schoolclass ?? '') . ' ' . ($schoolclass->arms->arm ?? '')),
                'term'          => $schoolterm->term      ?? '—',
                'session'       => $schoolsession->session ?? '—',
                'studentid'     => $studentId,
                'schoolclassid' => $schoolclassId,
                'termid'        => $termId,
                'sessionid'     => $sessionId,
                'picture_url'   => $pictureUrl,

                // Dynamic assessments metadata (name + max_score per column)
                'assessments'   => $assessmentsMeta,

                // Scores with per-row assessment_scores keyed by assessment_id
                'scores'        => $scores,

                // Mock scores — queried from BroadsheetsMock
                'mock_scores'   => $this->fetchMockScoresForDrawer($studentId, $schoolclassId, $sessionId, $termId),

                // Personality profile
                'profile'       => $profile ? $profile->toArray() : null,

                // Attendance
                'attendance'    => $attendance,

                // GPA / totals
                'gpa_data'      => $resultData['gpa_data']      ?? [],
                'totals_summary'=> $resultData['totals_summary'] ?? [],
            ]);

        } catch (\Exception $e) {
            Log::error('drawerData error', [
                'student_id' => $studentId,
                'error'      => $e->getMessage(),
                'line'       => $e->getLine(),
            ]);

            return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Test PDF generation endpoint
     */
    public function testPdfGeneration(Request $request)
    {
        try {
            $testStudentId = Student::first()->id ?? null;
            $testClassId   = Schoolclass::first()->id ?? null;
            $testSessionId = Schoolsession::first()->id ?? null;

            if (!$testStudentId || !$testClassId || !$testSessionId) {
                return response()->json(['success' => false, 'message' => 'Test data not available in database']);
            }

            $studentData = $this->getStudentResultData($testStudentId, $testClassId, $testSessionId, 3);

            return response()->json([
                'success'        => !empty($studentData),
                'has_students'   => isset($studentData['students']) && !$studentData['students']->isEmpty(),
                'has_scores'     => isset($studentData['scores'])   && !$studentData['scores']->isEmpty(),
                'scores_count'   => $studentData['scores']->count() ?? 0,
                'totals_summary' => $studentData['totals_summary'] ?? [],
                'attendance'     => $studentData['attendance_summary'] ?? [],
                'position_columns_check' => $studentData['scores'] && $studentData['scores']->isNotEmpty()
                    ? [
                        'position'         => $studentData['scores']->first()->position ?? 'NOT FOUND',
                        'position_total'   => $studentData['scores']->first()->position_total ?? 'NOT FOUND',
                        'arm_position'     => $studentData['scores']->first()->arm_position ?? 'NOT FOUND',
                        'arm_position_cum' => $studentData['scores']->first()->arm_position_cum ?? 'NOT FOUND',
                    ]
                    : 'No scores',
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
