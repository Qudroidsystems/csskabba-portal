<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Student;
use App\Models\Assessment;
use App\Models\Schoolterm;
use App\Models\Broadsheets;
use App\Models\Schoolclass;
use App\Models\Studentclass;
use Illuminate\Http\Request;
use App\Models\Classcategory;
use App\Models\Schoolsession;
use App\Models\BroadsheetRecord;
use App\Models\AttendanceSummary;
use App\Models\Studentpersonalityprofile;
use App\Models\CompulsorySubjectClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\SchoolInformation;
use App\Services\PromotionEvaluator;
use App\Services\ClassPositionService;
use App\Models\BroadsheetsMock;
use Carbon\Carbon;

class StudentAssessmentController extends Controller
{
    protected ClassPositionService $positionService;
    protected const CACHE_DURATION = 3600; // 1 hour

    public function __construct(ClassPositionService $positionService)
    {
        $this->positionService = $positionService;

        $this->middleware('permission:View student assessments', ['only' => ['index', 'printResult', 'printMockResult']]);
    }

    // =========================================================================
    // FORMAT ORDINAL
    // =========================================================================

    protected function formatOrdinal($number): string
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

    // =========================================================================
    // GRADE HELPERS
    // =========================================================================

    protected function calculateSeniorGrade(?float $score): string
    {
        if (!is_numeric($score) || $score < 0) {
            return '-';
        }
        
        if ($score >= 75) return 'A1';
        if ($score >= 70) return 'B2';
        if ($score >= 65) return 'B3';
        if ($score >= 60) return 'C4';
        if ($score >= 55) return 'C5';
        if ($score >= 50) return 'C6';
        if ($score >= 45) return 'D7';
        if ($score >= 40) return 'E8';
        return 'F9';
    }

    protected function calculateJuniorGrade(?float $score): string
    {
        if (!is_numeric($score) || $score < 0) {
            return '-';
        }
        
        if ($score >= 70) return 'A';
        if ($score >= 60) return 'B';
        if ($score >= 50) return 'C';
        if ($score >= 40) return 'D';
        return 'F';
    }

    protected function getGradePoint(?float $score, bool $isSenior = false): float
    {
        if (!is_numeric($score) || $score < 0) {
            return 0.0;
        }

        if (!$isSenior) {
            if ($score >= 70) return 5.0;
            if ($score >= 60) return 4.0;
            if ($score >= 50) return 3.0;
            if ($score >= 40) return 2.0;
            return 0.0;
        } else {
            if ($score >= 75) return 5.0;
            if ($score >= 65) return 4.0;
            if ($score >= 50) return 3.0;
            if ($score >= 45) return 2.0;
            if ($score >= 40) return 1.0;
            return 0.0;
        }
    }

    protected function getGpaGrade(float $gpa): string
    {
        if ($gpa >= 4.5) return 'A1';
        if ($gpa >= 4.0) return 'B2';
        if ($gpa >= 3.5) return 'B3';
        if ($gpa >= 3.0) return 'C4';
        if ($gpa >= 2.5) return 'C5';
        if ($gpa >= 2.0) return 'C6';
        if ($gpa >= 1.5) return 'D7';
        if ($gpa >= 1.0) return 'E8';
        return 'F9';
    }

    protected function getRemark(string $grade): string
    {
        return match ($grade) {
            'A1', 'A' => 'Excellent',
            'B2', 'B3', 'B' => 'Very Good',
            'C4', 'C5', 'C6', 'C' => 'Good',
            'D7', 'D' => 'Pass',
            'E8' => 'Pass',
            'F9', 'F' => 'Fail',
            default => 'Unknown',
        };
    }

    // =========================================================================
    // MOCK DATA HELPER - FIXED
    // =========================================================================

    private function getMockData(int $studentId, int $schoolclassId, int $sessionId, ?int $termId): \Illuminate\Support\Collection
    {
        if (!$termId) {
            return collect();
        }

        try {
            // Fixed query - removed incorrect join
            $rows = BroadsheetsMock::where('student_id', $studentId)
                ->where('session_id', $sessionId)
                ->where('schoolclass_id', $schoolclassId)
                ->where('term_id', $termId)
                ->join('subject', 'subject.id', '=', 'broadsheets_mock.subject_id')
                ->orderBy('subject.subject')
                ->select([
                    'subject.subject as subject_name',
                    'subject.subject_code',
                    'broadsheets_mock.exam',
                    'broadsheets_mock.total',
                    'broadsheets_mock.grade',
                    'broadsheets_mock.remark',
                    'broadsheets_mock.subject_position_class as position',
                    'broadsheets_mock.avg as class_average',
                    'broadsheets_mock.cmin',
                    'broadsheets_mock.cmax',
                    'broadsheets_mock.id as record_id',
                ])
                ->get();

            // If positions are not stored, calculate them dynamically
            if ($rows->isNotEmpty() && $rows->every(fn($row) => empty($row->position) || $row->position == 0)) {
                Log::info('Calculating mock positions dynamically for student ' . $studentId);

                $allMockRecords = BroadsheetsMock::where('term_id', $termId)
                    ->where('schoolclass_id', $schoolclassId)
                    ->where('session_id', $sessionId)
                    ->get();

                $subjectGroups = $allMockRecords->groupBy('subject_id');

                foreach ($rows as $row) {
                    $subjectRecords = $subjectGroups->get($row->record_id ?? $row->subject_id ?? null, collect());

                    if ($subjectRecords->isNotEmpty()) {
                        $sorted = $subjectRecords->sortByDesc('total')->values();
                        $position = $sorted->search(function($record) use ($row) {
                            return $record->total == $row->total && $record->id == $row->record_id;
                        });
                        $row->position = $position !== false ? $position + 1 : null;
                    }
                }
            }

            return $rows;
        } catch (\Exception $e) {
            Log::error('getMockData error', [
                'student_id'    => $studentId,
                'schoolclassid' => $schoolclassId,
                'sessionid'     => $sessionId,
                'termid'        => $termId,
                'error'         => $e->getMessage(),
            ]);
            return collect();
        }
    }

    // =========================================================================
    // COMPUTE GPA/CGPA
    // =========================================================================

    private function computeOverallForStudent(int $studentId, $schoolclass, int $termId, int $sessionId, bool $isSenior): array
    {
        $currentTermBroadsheets = Broadsheets::where('term_id', $termId)
            ->whereHas('broadsheetRecord', function ($q) use ($studentId, $sessionId) {
                $q->where('student_id', $studentId)->where('session_id', $sessionId);
            })
            ->get(['cum_ave']);

        if ($currentTermBroadsheets->isEmpty()) {
            return [
                'gpa'                => 0.0,
                'cgpa'               => 0.0,
                'gpa_grade'          => '-',
                'num_subjects'       => 0,
                'total_grade_points' => 0.0,
            ];
        }

        $termGradePoints    = $currentTermBroadsheets->map(fn ($b) => $this->getGradePoint(round($b->cum_ave ?? 0), $isSenior));
        $gpa                = $termGradePoints->avg() ?? 0.0;
        $num_subjects       = $currentTermBroadsheets->count();
        $total_grade_points = $termGradePoints->sum();
        $gpaGrade           = $this->getGpaGrade($gpa);

        return [
            'gpa'                => $gpa,
            'cgpa'               => 0.0,
            'gpa_grade'          => $gpaGrade,
            'num_subjects'       => $num_subjects,
            'total_grade_points' => $total_grade_points,
        ];
    }

    // =========================================================================
    // BUILD GPA TREND - OPTIMIZED
    // =========================================================================

    private function buildGpaTrend(int $studentId, ?int $sessionId, bool $isSenior): array
    {
        try {
            // Cache the trend data for better performance
            $cacheKey = "gpa_trend_{$studentId}_{$sessionId}_{$isSenior}";
            
            return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($studentId, $sessionId, $isSenior) {
                $query = Broadsheets::whereHas('broadsheetRecord', function ($q) use ($studentId, $sessionId) {
                    $q->where('student_id', $studentId);
                    if ($sessionId) {
                        $q->where('session_id', $sessionId);
                    }
                })->with(['term', 'broadsheetRecord']);

                $broadsheets = $query->get();

                if ($broadsheets->isEmpty()) {
                    return [];
                }

                $trend = [];
                $terms = Schoolterm::orderBy('id')->get();

                foreach ($terms as $t) {
                    $termBroadsheets = $broadsheets->filter(fn($b) => $b->term_id == $t->id);
                    
                    if ($termBroadsheets->isEmpty()) {
                        continue;
                    }

                    $gp = $termBroadsheets->map(fn($b) => $this->getGradePoint(round($b->cum_ave ?? 0), $isSenior));
                    $gpa = $gp->avg() ?? 0.0;
                    
                    if ($gpa > 0) {
                        $trend[$t->term] = round($gpa, 2);
                    }
                }

                return $trend;
            });
        } catch (\Exception $e) {
            Log::error('Error building GPA trend', [
                'student_id' => $studentId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    // =========================================================================
    // IMAGE HELPERS
    // =========================================================================

    private function logoToBase64($schoolInfo): string
    {
        $placeholder = 'data:image/svg+xml;base64,' . base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80">
                <rect width="80" height="80" rx="40" fill="#1e3a5f"/>
                <text x="40" y="46" text-anchor="middle" fill="white" font-family="Arial" font-size="14" font-weight="bold">SCH</text>
            </svg>'
        );

        if (!$schoolInfo || empty($schoolInfo->school_logo)) {
            return $placeholder;
        }

        $paths = [
            storage_path('app/public/' . $schoolInfo->school_logo),
            public_path('storage/' . $schoolInfo->school_logo),
        ];

        foreach ($paths as $path) {
            if (file_exists($path) && filesize($path) > 100) {
                $mime = mime_content_type($path) ?: 'image/jpeg';
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
            }
        }
        return $placeholder;
    }

    private function imageToBase64ForPdf(?string $path): string
    {
        $placeholder = 'data:image/svg+xml;base64,' . base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="95" viewBox="0 0 80 95">
                <rect width="80" height="95" fill="#e2e8f0"/>
                <circle cx="40" cy="32" r="18" fill="#94a3b8"/>
                <rect x="20" y="56" width="40" height="28" rx="4" fill="#94a3b8"/>
                <text x="40" y="90" text-anchor="middle" fill="#475569" font-family="Arial" font-size="8">PHOTO</text>
            </svg>'
        );

        if (!$path) {
            return $placeholder;
        }

        $possiblePaths = [
            public_path('storage/student_avatars/' . $path),
            storage_path('app/public/student_avatars/' . $path),
        ];

        foreach ($possiblePaths as $fullPath) {
            if (file_exists($fullPath) && filesize($fullPath) > 100) {
                $mime = mime_content_type($fullPath) ?: 'image/jpeg';
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
            }
        }
        return $placeholder;
    }

    private function getSchoolStampBase64($schoolInfo): string
    {
        $placeholder = 'data:image/svg+xml;base64,' . base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="45" fill="#f1f5f9" stroke="#3b82f6" stroke-width="2"/>
                <text x="50" y="55" text-anchor="middle" fill="#1e293b" font-size="12" font-family="Arial">STAMP</text>
            </svg>'
        );

        if (!$schoolInfo || empty($schoolInfo->school_stamp)) {
            return $placeholder;
        }

        $paths = [
            storage_path('app/public/' . $schoolInfo->school_stamp),
            public_path('storage/' . $schoolInfo->school_stamp),
        ];

        foreach ($paths as $path) {
            if (file_exists($path) && filesize($path) > 100) {
                $mime = mime_content_type($path) ?: 'image/png';
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
            }
        }

        return $placeholder;
    }

    // =========================================================================
    // GET STUDENT PROFILE DATA
    // =========================================================================

    private function getStudentProfileData(int $studentId, int $termId, int $sessionId, int $schoolclassId): \Illuminate\Support\Collection
    {
        try {
            $profile = Studentpersonalityprofile::where('studentid', $studentId)
                ->where('termid', $termId)
                ->where('sessionid', $sessionId)
                ->where('schoolclassid', $schoolclassId)
                ->first();

            return $profile ? collect([$profile]) : collect();
        } catch (\Exception $e) {
            Log::error('Error fetching student personality profile', [
                'student_id' => $studentId,
                'error'      => $e->getMessage(),
            ]);
            return collect();
        }
    }

    // =========================================================================
    // GET AUTHENTICATED STUDENT
    // =========================================================================

    private function getAuthenticatedStudent(): ?Student
    {
        $studentId = auth()->user()->student_id;

        if (!$studentId) {
            return null;
        }

        return Student::where('id', $studentId)
            ->select('id', 'firstname', 'lastname', 'othername', 'admissionNo', 'gender', 'can_view_assessments')
            ->first();
    }

    // =========================================================================
    // GET SELECTED FILTERS
    // =========================================================================

    private function getSelectedFilters(Request $request): array
    {
        $sessions = Schoolsession::whereIn('status', ['Current', 'Previous'])
            ->orderBy('id', 'desc')
            ->get(['id', 'session']);

        $userSelectedTermId = $request->get('term_id');
        $selectedSessionId = $request->get('session_id', $sessions->first()?->id ?? null);
        $selectedTermId = $userSelectedTermId ?: null;
        $isAllTerms = empty($userSelectedTermId);

        return [
            'sessions' => $sessions,
            'userSelectedTermId' => $userSelectedTermId,
            'selectedSessionId' => $selectedSessionId,
            'selectedTermId' => $selectedTermId,
            'isAllTerms' => $isAllTerms,
        ];
    }

    // =========================================================================
    // GET STUDENT CLASS DATA
    // =========================================================================

    private function getStudentClassData(int $studentId, array $filters): ?\stdClass
    {
        $query = DB::table('studentclass')
            ->where('studentclass.studentId', $studentId)
            ->join('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
            ->join('schoolterm', 'schoolterm.id', '=', 'studentclass.termid')
            ->join('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm');

        if ($filters['selectedSessionId']) {
            $query->where('schoolsession.id', $filters['selectedSessionId']);
        }

        return $query->select(
            'schoolclass.id as class_id',
            'schoolclass.schoolclass as class_name',
            'schoolarm.arm as arm_name',
            'schoolterm.id as term_id',
            'schoolterm.term as term_name',
            'schoolsession.id as session_id',
            'schoolsession.session as session_name'
        )->first();
    }

    // =========================================================================
    // GET REGISTERED SUBJECTS - FIXED
    // =========================================================================

    private function getRegisteredSubjects(int $studentId, int $classId, ?int $sessionId, ?int $termId, bool $isAllTerms): \Illuminate\Support\Collection
    {
        $query = DB::table('student_subject_register_record as ssrr')
            ->where('ssrr.studentId', $studentId)
            ->leftJoin('subjectclass', 'subjectclass.id', '=', 'ssrr.subjectclassid')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'ssrr.session')
            ->when($sessionId, fn($q) => $q->where('schoolsession.id', $sessionId))
            ->when(!$isAllTerms && $termId, fn($q) => $q->where('subjectteacher.termid', $termId))
            ->where('schoolsession.status', '!=', 'Archived')
            ->join('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->select(
                'subject.id as subject_id', 
                'subject.subject as subject_name', 
                'subject.subject_code'
            )
            ->distinct();

        // Group by subject_id to avoid duplicates
        return $query->get()->unique('subject_id')->values();
    }

    // =========================================================================
    // GET COMPULSORY SUBJECTS - FIXED
    // =========================================================================

    private function getCompulsorySubjects(int $classId): array
    {
        try {
            return CompulsorySubjectClass::where('schoolclassid', $classId)
                ->with(['subject:id,subject,subject_code'])
                ->get()
                ->map(function($item) {
                    return [
                        'subject_id' => $item->subjectId,
                        'subject' => $item->subject->subject ?? null,
                        'subject_code' => $item->subject->subject_code ?? null,
                    ];
                })
                ->filter(fn($item) => !empty($item['subject_id']))
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error fetching compulsory subjects', [
                'class_id' => $classId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    // =========================================================================
    // INDEX - Web View (UPDATED)
    // =========================================================================

    public function index(Request $request)
    {
        $pagetitle = 'My Assessments';
        
        // Get authenticated student
        $student = $this->getAuthenticatedStudent();
        
        if (!$student) {
            return redirect()->route('dashboard')->with('error', 'Student profile not found.');
        }

        if (!$student->can_view_assessments) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view assessments.');
        }

        // Get filters
        $filters = $this->getSelectedFilters($request);
        $terms = Schoolterm::orderBy('id', 'desc')->get(['id', 'term']);
        
        // Get student class data
        $studentClassData = $this->getStudentClassData($student->id, $filters);

        if (!$studentClassData) {
            return view('student.assessments.index', compact(
                'pagetitle', 'student', 'terms', 'filters'
            ))->with('error', 'No class registration found for the selected term and session.');
        }

        // Determine selected term
        if ($filters['isAllTerms'] && $filters['selectedSessionId']) {
            $latestTermId = DB::table('studentclass')
                ->where('studentId', $student->id)
                ->where('sessionid', $filters['selectedSessionId'])
                ->join('schoolterm', 'schoolterm.id', '=', 'studentclass.termid')
                ->orderBy('schoolterm.id', 'desc')
                ->value('schoolterm.id');

            if ($latestTermId) {
                $filters['selectedTermId'] = $latestTermId;
            }
        }

        // Get school class with categories
        $schoolclass = Schoolclass::with('classcategories')->find($studentClassData->class_id);

        if (!$schoolclass || $schoolclass->classcategories->isEmpty()) {
            return view('student.assessments.index', compact(
                'pagetitle', 'student', 'terms', 'filters'
            ))->with('error', 'Class category not found.');
        }

        $isSenior = $schoolclass->classcategories->first()->is_senior ?? false;
        $categoryIds = $schoolclass->classcategories->pluck('id');
        $gradeCategory = $schoolclass->classcategories->first();

        // Ensure positions/averages are current
        if ($filters['selectedTermId']) {
            $this->positionService->recalculate(
                $studentClassData->class_id,
                $filters['selectedSessionId'] ?? $studentClassData->session_id,
                $filters['selectedTermId']
            );
        }

        // Get compulsory subjects for this class (FIXED - now returns structured data)
        $compulsorySubjects = $this->getCompulsorySubjects($studentClassData->class_id);
        $compulsorySubjectIds = collect($compulsorySubjects)->pluck('subject_id')->toArray();

        // Get attendance summary
        $attendanceSummary = AttendanceSummary::where('student_id', $student->id)
            ->where('term_id', $filters['selectedTermId'])
            ->where('session_id', $filters['selectedSessionId'] ?? $studentClassData->session_id)
            ->first();

        // Get registered subjects with improved query
        $registeredSubjects = $this->getRegisteredSubjects(
            $student->id,
            $studentClassData->class_id,
            $filters['selectedSessionId'] ?? $studentClassData->session_id,
            $filters['selectedTermId'],
            $filters['isAllTerms']
        );

        // Get all assessments
        $allAssessments = Assessment::whereIn('classcategory_id', $categoryIds)
            ->with('subAssessments')
            ->orderBy('id')
            ->get();

        // Preload BroadsheetRecords with Broadsheets
        $broadsheetRecords = BroadsheetRecord::where('student_id', $student->id)
            ->whereIn('subject_id', $registeredSubjects->pluck('subject_id'))
            ->where('schoolclass_id', $studentClassData->class_id)
            ->where('session_id', $filters['selectedSessionId'] ?? $studentClassData->session_id)
            ->with(['broadsheets' => function($q) use ($filters) {
                if ($filters['selectedTermId']) {
                    $q->where('term_id', $filters['selectedTermId'])
                      ->with(['assessmentScores', 'subAssessmentScores']);
                }
            }])
            ->get()
            ->keyBy('subject_id');

        // Process subjects and build assessment data
        $subjectsWithAssessments = collect();
        $overallProgress = [
            'total_subjects' => 0,
            'completed_subjects' => 0,
            'total_score' => 0,
            'average_cum' => 0,
            'gpa' => '-',
            'cgpa' => '-',
            'gpa_grade' => '-',
            'num_subjects' => 0,
            'total_grade_points' => 0.0,
        ];

        foreach ($registeredSubjects as $regSubject) {
            $broadsheetRecord = $broadsheetRecords->get($regSubject->subject_id);
            if (!$broadsheetRecord) {
                continue;
            }

            // Get the broadsheet for the selected term
            $broadsheet = $broadsheetRecord->broadsheets->filter(function($b) use ($filters) {
                return $b->term_id == $filters['selectedTermId'];
            })->first();

            if (!$broadsheet) {
                continue;
            }

            // Build assessment data
            $assessmentData = $allAssessments->map(function ($assessment) use ($broadsheet) {
                $scoreObj = $broadsheet->assessmentScores->where('assessment_id', $assessment->id)->first();
                $score = $scoreObj ? $scoreObj->score : 0;

                $subScores = collect();
                if ($assessment->subAssessments->isNotEmpty()) {
                    $subScores = $assessment->subAssessments->map(function ($sub) use ($broadsheet) {
                        $subScoreObj = $broadsheet->subAssessmentScores->where('sub_assessment_id', $sub->id)->first();
                        return [
                            'id' => $sub->id,
                            'name' => $sub->name,
                            'max_score' => $sub->max_score,
                            'score' => $subScoreObj ? $subScoreObj->score : 0,
                            'percentage' => $sub->max_score > 0
                                ? round(($subScoreObj ? $subScoreObj->score : 0) / $sub->max_score * 100, 2)
                                : 0,
                        ];
                    });
                }

                return [
                    'id' => $assessment->id,
                    'name' => $assessment->name,
                    'max_score' => $assessment->max_score,
                    'score' => $score,
                    'percentage' => $assessment->max_score > 0 ? round($score / $assessment->max_score * 100, 2) : 0,
                    'sub_assessments' => $subScores,
                ];
            });

            $cumRaw = $broadsheet->cum ?? 0;
            $cumAve = $broadsheet->cum_ave ?? 0;

            $gradeSource = round($cumAve);
            $subjectGPA = $this->getGradePoint($gradeSource, $isSenior);
            $subjectGrade = $gradeCategory ? $gradeCategory->calculateGrade($gradeSource) : ($broadsheet->grade ?? '-');

            $isCompulsory = in_array($regSubject->subject_id, $compulsorySubjectIds);

            $subjectsWithAssessments->push([
                'subject_id' => $regSubject->subject_id,
                'subject_name' => $regSubject->subject_name,
                'subject_code' => $regSubject->subject_code,
                'assessments' => $assessmentData,
                'total' => $broadsheet->total ?? 0,
                'bf' => $broadsheet->bf ?? 0,
                'cum' => $cumRaw,
                'cum_ave' => $cumAve,
                'grade' => $subjectGrade,
                'subject_gpa' => round($subjectGPA, 1),
                'remark' => $broadsheet->remark ?? '-',
                'position' => $broadsheet->subject_position_class ? $this->formatOrdinal($broadsheet->subject_position_class) : '-',
                'position_total' => $broadsheet->subject_position_class_total ? $this->formatOrdinal($broadsheet->subject_position_class_total) : '-',
                'arm_position' => $broadsheet->arm_position ? $this->formatOrdinal($broadsheet->arm_position) : '-',
                'arm_position_cum' => $broadsheet->arm_position_cum ? $this->formatOrdinal($broadsheet->arm_position_cum) : '-',
                'is_compulsory' => $isCompulsory,
                // Add compulsory subject details
                'compulsory_details' => $isCompulsory ? collect($compulsorySubjects)->firstWhere('subject_id', $regSubject->subject_id) : null,
            ]);

            $overallProgress['total_subjects']++;
            if ($cumAve > 0) {
                $overallProgress['completed_subjects']++;
                $overallProgress['total_score'] += $cumAve;
            }
        }

        // Calculate overall progress
        if ($overallProgress['completed_subjects'] > 0) {
            $overallProgress['average_cum'] = round(
                $overallProgress['total_score'] / $overallProgress['completed_subjects'], 2
            );
        }

        if ($subjectsWithAssessments->isNotEmpty() && $schoolclass) {
            $gpaCgpaData = $this->computeOverallForStudent(
                $student->id, 
                $schoolclass, 
                $filters['selectedTermId'] ?? $studentClassData->term_id,
                $filters['selectedSessionId'] ?? $studentClassData->session_id, 
                $isSenior
            );
            $overallProgress['gpa'] = round($gpaCgpaData['gpa'], 2);
            $overallProgress['cgpa'] = round($gpaCgpaData['cgpa'], 2);
            $overallProgress['gpa_grade'] = $gpaCgpaData['gpa_grade'] ?? 'F';
            $overallProgress['num_subjects'] = $gpaCgpaData['num_subjects'];
            $overallProgress['total_grade_points'] = $gpaCgpaData['total_grade_points'];
        }

        // Get mock results
        $mockResults = $this->getMockData(
            $student->id,
            $studentClassData->class_id,
            $filters['selectedSessionId'] ?? $studentClassData->session_id,
            $filters['selectedTermId']
        );

        // Calculate mock summary
        $mockTotalObtained = $mockResults->sum(fn($r) => (float)($r->total ?? 0));
        $mockTotalObtainable = $mockResults->count() * 100;
        $mockPercentage = $mockTotalObtainable > 0
            ? round(($mockTotalObtained / $mockTotalObtainable) * 100, 1)
            : 0;
        
        $mockSummary = [
            'obtained' => round($mockTotalObtained, 1),
            'obtainable' => $mockTotalObtainable,
            'percentage' => $mockPercentage,
            'count' => $mockResults->count(),
        ];

        // Build GPA trend
        $gpaTrend = $this->buildGpaTrend($student->id, $filters['selectedSessionId'] ?? $studentClassData->session_id, $isSenior);

        // Get student picture and school info
        $studentPicture = DB::table('studentpicture')->where('studentid', $student->id)->value('picture');
        $schoolInfo = Cache::remember('school_information', self::CACHE_DURATION, function() {
            return SchoolInformation::first();
        });

        // Get selected term/session names
        $selectedTermModel = $filters['userSelectedTermId'] ? Schoolterm::find($filters['userSelectedTermId']) : null;
        $selectedTermName = $selectedTermModel ? $selectedTermModel->term : ($studentClassData->term_name ?? null);
        $selectedSessionName = $studentClassData->session_name ?? null;

        // Build class object for view
        $class = (object) [
            'id' => $studentClassData->class_id,
            'schoolclass' => $studentClassData->class_name,
            'arm_name' => $studentClassData->arm_name ?? '',
        ];

        $term = (object) ['id' => $studentClassData->term_id, 'term' => $studentClassData->term_name];
        $session = (object) ['id' => $studentClassData->session_id, 'session' => $studentClassData->session_name];

        // Pass filter data to view
        $viewData = compact(
            'pagetitle', 'student', 'class', 'term', 'session',
            'subjectsWithAssessments', 'terms', 'filters',
            'overallProgress', 'gpaTrend', 'studentPicture', 
            'schoolInfo', 'isSenior', 'allAssessments', 
            'attendanceSummary', 'mockResults', 'mockSummary',
            'selectedTermName', 'selectedSessionName',
            'compulsorySubjects', 'compulsorySubjectIds'
        );

        return view('student.assessments.index', $viewData);
    }

    // =========================================================================
    // PRINT TERMINAL RESULT (PDF) - UPDATED
    // =========================================================================

    public function printResult(Request $request)
    {
        ini_set('max_execution_time', 120);
        ini_set('memory_limit', '512M');

        $studentId = auth()->user()->student_id;
        $selectedSessionId = $request->get('session_id');
        $selectedTermId = $request->get('term_id');
        $selectedColumns = $request->get('selected_columns', []);

        if (!$studentId) {
            return back()->with('error', 'Student profile not found.');
        }

        // Verify this student owns the data
        $student = Student::where('id', $studentId)
            ->select('id', 'firstname', 'lastname', 'othername', 'admissionNo', 'gender', 'can_view_assessments')
            ->first();

        if (!$student || !$student->can_view_assessments) {
            return back()->with('error', 'You do not have permission to print assessments.');
        }

        // Get class data
        $studentClassData = DB::table('studentclass')
            ->where('studentclass.studentId', $studentId)
            ->join('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
            ->join('schoolterm', 'schoolterm.id', '=', 'studentclass.termid')
            ->join('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->when($selectedSessionId, fn($q) => $q->where('schoolsession.id', $selectedSessionId))
            ->select(
                'schoolclass.id as class_id',
                'schoolclass.schoolclass as class_name',
                'schoolarm.arm as arm_name',
                'schoolterm.id as term_id',
                'schoolterm.term as term_name',
                'schoolsession.id as session_id',
                'schoolsession.session as session_name'
            )
            ->first();

        if (!$studentClassData) {
            return back()->with('error', 'No class data found.');
        }

        if (!$selectedTermId) {
            $selectedTermId = $studentClassData->term_id;
        }

        $sessionIdForQuery = $selectedSessionId ?? $studentClassData->session_id;
        $schoolclassId = $studentClassData->class_id;

        // Ensure positions/averages are current
        $this->positionService->recalculate($schoolclassId, $sessionIdForQuery, $selectedTermId);

        $schoolclass = Schoolclass::with('classcategories')->find($schoolclassId);
        $isSenior = $schoolclass?->classcategories->first()?->is_senior ?? false;
        $categoryIds = $schoolclass?->classcategories->pluck('id') ?? collect();
        $gradeCategory = $schoolclass?->classcategories->first();

        // Get compulsory subjects
        $compulsorySubjectIds = CompulsorySubjectClass::where('schoolclassid', $schoolclassId)
            ->pluck('subjectId')
            ->toArray();

        // Get registered subjects
        $registeredSubjects = $this->getRegisteredSubjects(
            $studentId,
            $schoolclassId,
            $sessionIdForQuery,
            $selectedTermId,
            false
        );

        // Get all assessments
        $allAssessments = Assessment::whereIn('classcategory_id', $categoryIds)->orderBy('id')->get();

        // Preload BroadsheetRecords
        $broadsheetRecords = BroadsheetRecord::where('student_id', $studentId)
            ->whereIn('subject_id', $registeredSubjects->pluck('subject_id'))
            ->where('schoolclass_id', $schoolclassId)
            ->where('session_id', $sessionIdForQuery)
            ->with(['broadsheets' => function($q) use ($selectedTermId) {
                $q->where('term_id', $selectedTermId)
                  ->with(['assessmentScores', 'subAssessmentScores']);
            }])
            ->get()
            ->keyBy('subject_id');

        // Build scores
        $scores = collect();
        $totalObtained = 0;
        $totalObtainable = 0;

        $previousTermId = Schoolterm::where('id', '<', $selectedTermId)->orderBy('id', 'desc')->first()?->id;

        foreach ($registeredSubjects as $regSubject) {
            $broadsheetRecord = $broadsheetRecords->get($regSubject->subject_id);
            if (!$broadsheetRecord) {
                continue;
            }

            $broadsheet = $broadsheetRecord->broadsheets->filter(function($b) use ($selectedTermId) {
                return $b->term_id == $selectedTermId;
            })->first();

            if (!$broadsheet) {
                continue;
            }

            $scoreData = new \stdClass();
            $scoreData->subject_id = $regSubject->subject_id;
            $scoreData->subject_name = $regSubject->subject_name;
            $scoreData->subject_code = $regSubject->subject_code;
            $scoreData->total = $broadsheet->total ?? 0;

            // Get BF from previous term
            $bfValue = 0;
            if ($previousTermId) {
                $previousBroadsheet = Broadsheets::where('broadSheet_record_id', $broadsheetRecord->id)
                    ->where('term_id', $previousTermId)
                    ->first();
                $bfValue = $previousBroadsheet ? ($previousBroadsheet->cum ?? 0) : 0;
            }
            $scoreData->bf = $bfValue;

            $cumRaw = $broadsheet->cum ?? 0;
            $cumAve = $broadsheet->cum_ave ?? 0;

            $gradeSource = round($cumAve);

            $scoreData->cum = $cumRaw;
            $scoreData->cum_ave = $cumAve;
            $scoreData->grade = $gradeCategory ? $gradeCategory->calculateGrade($gradeSource) : ($broadsheet->grade ?? '-');
            $scoreData->class_average = $broadsheet->avg ?? 0;

            $scoreData->position = $broadsheet->subject_position_class ?? null;
            $scoreData->position_total = $broadsheet->subject_position_class_total ?? null;
            $scoreData->arm_position = $broadsheet->arm_position ?? null;
            $scoreData->arm_position_cum = $broadsheet->arm_position_cum ?? null;
            $scoreData->subject_position_class = $broadsheet->subject_position_class ?? null;
            $scoreData->subject_position_class_total = $broadsheet->subject_position_class_total ?? null;

            $scoreData->position_formatted = $broadsheet->subject_position_class ? $this->formatOrdinal($broadsheet->subject_position_class) : '-';
            $scoreData->position_total_formatted = $broadsheet->subject_position_class_total ? $this->formatOrdinal($broadsheet->subject_position_class_total) : '-';
            $scoreData->arm_position_formatted = $broadsheet->arm_position ? $this->formatOrdinal($broadsheet->arm_position) : '-';
            $scoreData->arm_position_cum_formatted = $broadsheet->arm_position_cum ? $this->formatOrdinal($broadsheet->arm_position_cum) : '-';

            $scoreData->is_compulsory = in_array($regSubject->subject_id, $compulsorySubjectIds);

            $scoreData->assessment_scores = collect();
            foreach ($allAssessments as $assessment) {
                $scoreObj = $broadsheet->assessmentScores->where('assessment_id', $assessment->id)->first();
                $scoreData->assessment_scores->push((object) [
                    'assessment_id' => $assessment->id,
                    'score' => $scoreObj ? $scoreObj->score : 0,
                    'max_score' => $assessment->max_score,
                    'name' => $assessment->name,
                ]);
            }

            $scores->push($scoreData);
            $totalObtained += (float) $scoreData->total;
            $totalObtainable += 100;
        }

        $percentage = $totalObtainable > 0 ? round(($totalObtained / $totalObtainable) * 100, 1) : 0;

        // Get GPA data
        $gpaData = $this->computeOverallForStudent(
            $studentId, 
            $schoolclass, 
            $selectedTermId,
            $sessionIdForQuery, 
            $isSenior
        );

        // Promotion Evaluation - with detailed subject data
        $evaluator = new PromotionEvaluator();
        
        // Prepare detailed subject scores for evaluator
        $subjectScores = [];
        foreach ($scores as $score) {
            $subjectScores[$score->subject_id] = [
                'subject_id' => $score->subject_id,
                'subject_name' => $score->subject_name,
                'score' => round($score->cum_ave ?? 0),
                'is_compulsory' => $score->is_compulsory ?? false,
                'grade' => $score->grade ?? '-',
            ];
        }

        $promotionResult = $evaluator->evaluate(
            studentId: $studentId,
            schoolclassid: $schoolclassId,
            termid: $selectedTermId,
            sessionid: $sessionIdForQuery,
            scores: $scores,
            overallAverage: $percentage
        );

        Log::info('Promotion evaluation result for student PDF', [
            'student_id' => $studentId,
            'status' => $promotionResult['status'] ?? 'unknown',
            'status_label' => $promotionResult['status_label'] ?? 'unknown',
        ]);

        // Get school info
        $schoolInfo = Cache::remember('school_information', self::CACHE_DURATION, function() {
            return SchoolInformation::first();
        });
        
        $logoBase64 = $this->logoToBase64($schoolInfo);
        $pictureBase64 = $this->imageToBase64ForPdf(
            DB::table('studentpicture')->where('studentid', $studentId)->value('picture')
        );
        $stampBase64 = $this->getSchoolStampBase64($schoolInfo);

        // Get number of students
        $numberOfStudents = DB::table('studentclass')
            ->where('schoolclassid', $schoolclassId)
            ->where('sessionid', $sessionIdForQuery)
            ->where('termid', $selectedTermId)
            ->count();

        // Get student profile data
        $studentProfileData = $this->getStudentProfileData($studentId, $selectedTermId, $sessionIdForQuery, $schoolclassId);

        // Get attendance summary
        $attendanceSummary = AttendanceSummary::where('student_id', $studentId)
            ->where('term_id', $selectedTermId)
            ->where('session_id', $sessionIdForQuery)
            ->first();

        $attendanceData = [];
        if ($attendanceSummary) {
            $attendanceData = [
                'found' => true,
                'total_school_days' => $attendanceSummary->total_school_days ?? 0,
                'days_present' => $attendanceSummary->days_present ?? 0,
                'days_absent' => $attendanceSummary->days_absent ?? 0,
                'days_late' => $attendanceSummary->days_late ?? 0,
                'days_sick_leave' => $attendanceSummary->days_sick_leave ?? 0,
                'days_excused' => $attendanceSummary->days_excused ?? 0,
                'attendance_percentage' => $attendanceSummary->attendance_percentage ?? 0,
            ];
        } else {
            $attendanceData = ['found' => false];
        }

        // Get mock results
        $mockRows = $this->getMockData($studentId, $schoolclassId, $sessionIdForQuery, $selectedTermId);
        $mockTotalObtained = $mockRows->sum(fn($r) => (float)($r->total ?? 0));
        $mockTotalObtainable = $mockRows->count() * 100;
        $mockPercentage = $mockTotalObtainable > 0
            ? round(($mockTotalObtained / $mockTotalObtainable) * 100, 1)
            : 0;
        
        $mockSummaryForPdf = [
            'obtained' => round($mockTotalObtained, 1),
            'obtainable' => $mockTotalObtainable,
            'percentage' => $mockPercentage,
        ];

        // Get selected term/session names
        $termModel = Schoolterm::find($selectedTermId);
        $sessionModel = Schoolsession::find($sessionIdForQuery);
        $selectedTermName = $termModel ? $termModel->term : ($studentClassData->term_name ?? 'Term');
        $selectedSessionName = $sessionModel ? $sessionModel->session : ($studentClassData->session_name ?? 'Session');

        $metadata = [
            'term' => $selectedTermName,
            'session' => $selectedSessionName,
            'selected_columns' => $selectedColumns,
            'grade_basis' => 'cum_ave',
        ];

        $schoolclassWithArms = new \stdClass();
        $schoolclassWithArms->schoolclass = $studentClassData->class_name ?? '';
        $schoolclassWithArms->arms = new \stdClass();
        $schoolclassWithArms->arms->arm = $studentClassData->arm_name ?? '';

        $allStudentData = [[
            'students' => collect([$student]),
            'schoolclass' => $schoolclassWithArms,
            'scores' => $scores,
            'assessments' => $allAssessments,
            'gpa_data' => $gpaData,
            'totals_summary' => [
                'obtained' => $totalObtained,
                'obtainable' => $totalObtainable,
                'percentage' => $percentage,
            ],
            'schoolInfo' => $schoolInfo,
            'school_logo_base64' => $logoBase64,
            'school_stamp_base64' => $stampBase64,
            'student_image_base64' => $pictureBase64,
            'numberOfStudents' => $numberOfStudents,
            'studentpp' => $studentProfileData,
            'attendance_summary' => $attendanceData,
            'promotion_result' => $promotionResult,
            'mock_results' => $mockRows,
            'mock_summary' => $mockSummaryForPdf,
            'compulsory_subject_ids' => $compulsorySubjectIds,
            'subject_scores' => $subjectScores, // Added for detailed promotion data
        ]];

        $safeAdmissionNo = preg_replace('/[^A-Za-z0-9\-]/', '_', $student->admissionNo ?? 'student');
        $safeTerm = preg_replace('/[^A-Za-z0-9\-]/', '_', $selectedTermName);
        $filename = 'Terminal_Report_' . $safeAdmissionNo . '_' . $safeTerm . '.pdf';

        $pdf = Pdf::loadView('student.assessments.print-pdf', [
            'allStudentData' => $allStudentData,
            'metadata' => $metadata,
        ])
        ->setPaper('A4', 'portrait')
        ->setOptions([
            'dpi' => 150,
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
        ]);

        return $pdf->stream($filename);
    }

    // =========================================================================
    // PRINT MOCK RESULT (PDF)
    // =========================================================================

    public function printMockResult(Request $request)
    {
        ini_set('max_execution_time', 120);
        ini_set('memory_limit', '512M');

        $studentId = auth()->user()->student_id;
        $selectedSessionId = $request->get('session_id');
        $selectedTermId = $request->get('term_id');

        if (!$studentId) {
            return back()->with('error', 'Student profile not found.');
        }

        $student = Student::where('id', $studentId)
            ->select('id', 'firstname', 'lastname', 'othername', 'admissionNo', 'gender', 'can_view_assessments')
            ->first();

        if (!$student || !$student->can_view_assessments) {
            return back()->with('error', 'You do not have permission to print assessments.');
        }

        $studentClassData = DB::table('studentclass')
            ->where('studentclass.studentId', $studentId)
            ->join('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
            ->join('schoolterm', 'schoolterm.id', '=', 'studentclass.termid')
            ->join('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->when($selectedSessionId, fn($q) => $q->where('schoolsession.id', $selectedSessionId))
            ->select(
                'schoolclass.id as class_id',
                'schoolclass.schoolclass as class_name',
                'schoolarm.arm as arm_name',
                'schoolterm.id as term_id',
                'schoolterm.term as term_name',
                'schoolsession.id as session_id',
                'schoolsession.session as session_name'
            )
            ->first();

        if (!$studentClassData) {
            return back()->with('error', 'No class data found.');
        }

        if (!$selectedTermId) {
            $selectedTermId = $studentClassData->term_id;
        }

        $sessionIdForQuery = $selectedSessionId ?? $studentClassData->session_id;
        $schoolclassId = $studentClassData->class_id;

        $termModel = Schoolterm::find($selectedTermId);
        $sessionModel = Schoolsession::find($sessionIdForQuery);
        $schoolInfo = Cache::remember('school_information', self::CACHE_DURATION, function() {
            return SchoolInformation::first();
        });

        $mockRows = $this->getMockData($studentId, $schoolclassId, $sessionIdForQuery, $selectedTermId);

        $mockTotalObtained = $mockRows->sum(fn($r) => (float)($r->total ?? 0));
        $mockTotalObtainable = $mockRows->count() * 100;
        $mockPercentage = $mockTotalObtainable > 0
            ? round(($mockTotalObtained / $mockTotalObtainable) * 100, 1)
            : 0;

        $logoBase64 = $this->logoToBase64($schoolInfo);
        $pictureBase64 = $this->imageToBase64ForPdf(
            DB::table('studentpicture')->where('studentid', $studentId)->value('picture')
        );
        $stampBase64 = $this->getSchoolStampBase64($schoolInfo);

        $numberOfStudents = DB::table('studentclass')
            ->where('schoolclassid', $schoolclassId)
            ->where('sessionid', $sessionIdForQuery)
            ->where('termid', $selectedTermId)
            ->count();

        $schoolclassWithArms = new \stdClass();
        $schoolclassWithArms->schoolclass = $studentClassData->class_name ?? '';
        $schoolclassWithArms->arms = new \stdClass();
        $schoolclassWithArms->arms->arm = $studentClassData->arm_name ?? '';

        $selectedTermName = $termModel ? $termModel->term : ($studentClassData->term_name ?? 'Term');
        $selectedSessionName = $sessionModel ? $sessionModel->session : ($studentClassData->session_name ?? 'Session');

        $safeAdmissionNo = preg_replace('/[^A-Za-z0-9\-]/', '_', $student->admissionNo ?? 'student');
        $safeTerm = preg_replace('/[^A-Za-z0-9\-]/', '_', $selectedTermName);
        $filename = 'Mock_Report_' . $safeAdmissionNo . '_' . $safeTerm . '.pdf';

        $pdf = Pdf::loadView('student.assessments.print-mock-pdf', [
            'student' => $student,
            'schoolclass' => $schoolclassWithArms,
            'mockRows' => $mockRows,
            'mockSummary' => [
                'obtained' => round($mockTotalObtained, 1),
                'obtainable' => $mockTotalObtainable,
                'percentage' => $mockPercentage,
            ],
            'schoolInfo' => $schoolInfo,
            'logoBase64' => $logoBase64,
            'stampBase64' => $stampBase64,
            'pictureBase64' => $pictureBase64,
            'numberOfStudents' => $numberOfStudents,
            'term' => $selectedTermName,
            'session' => $selectedSessionName,
        ])
        ->setPaper('A4', 'portrait')
        ->setOptions([
            'dpi' => 150,
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
        ]);

        return $pdf->stream($filename);
    }

    // =========================================================================
    // GET COLUMN OPTIONS (for PDF column selection)
    // =========================================================================

    public function getColumnOptions(Request $request)
    {
        $schoolclassid = $request->input('schoolclassid');
        $sessionid = $request->input('sessionid');
        $termid = $request->input('termid');

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
                'sn' => ['label' => 'SN', 'default' => true],
                'admission_no' => ['label' => 'Admission No', 'default' => true],
                'name' => ['label' => 'Name', 'default' => true],
                'picture' => ['label' => 'Picture', 'default' => true],
                'gender' => ['label' => 'Gender', 'default' => false],
                'dob' => ['label' => 'Date of Birth', 'default' => false],
            ],
            'assessments' => [],
            'scores' => [
                'total' => ['label' => 'Total', 'default' => true],
                'bf' => ['label' => 'BF', 'default' => true],
                'cum' => ['label' => 'Cum (raw sum)', 'default' => true],
                'cum_ave' => ['label' => 'Cum Ave', 'default' => true],
                'grade' => ['label' => 'Grade', 'default' => true],
                'arm_position' => ['label' => 'Arm Pos (Total) — This Arm', 'default' => true],
                'arm_position_cum' => ['label' => 'Arm Pos (Cum) — This Arm', 'default' => true],
                'position_total' => ['label' => 'Class Pos (Total) — All Arms', 'default' => true],
                'position' => ['label' => 'Class Pos (Cum) — All Arms', 'default' => true],
                'class_average' => ['label' => 'Class Avg', 'default' => true],
            ],
            'gpa_metrics' => [
                'num_subjects' => ['label' => 'Num Subjects', 'default' => true],
                'total_grade_points' => ['label' => 'Total GP', 'default' => true],
                'gpa' => ['label' => 'GPA', 'default' => true],
                'calculated_gpa' => ['label' => 'Calc GPA', 'default' => true],
                'gpa_grade' => ['label' => 'GPA Grade', 'default' => true],
                'cgpa' => ['label' => 'CGPA', 'default' => true],
            ],
            'attendance' => [
                'attendance_days_present' => ['label' => 'Days Present', 'default' => true],
                'attendance_days_absent' => ['label' => 'Days Absent', 'default' => true],
                'attendance_days_late' => ['label' => 'Days Late', 'default' => false],
                'attendance_sick_leave' => ['label' => 'Sick Leave', 'default' => false],
                'attendance_excused' => ['label' => 'Excused', 'default' => false],
                'attendance_total_days' => ['label' => 'Total School Days', 'default' => true],
                'attendance_percentage' => ['label' => 'Attendance %', 'default' => true],
            ],
            'other' => [
                'compulsory_flag' => ['label' => 'Compulsory', 'default' => false],
                'vetted_status' => ['label' => 'Vetted Status', 'default' => true],
                'promotion_status' => ['label' => 'Promotion', 'default' => true],
            ],
        ];

        foreach ($assessments as $assessment) {
            $columns['assessments'][$assessment->id] = [
                'label' => $assessment->name . ' (' . $assessment->max_score . ')',
                'default' => true,
                'is_assessment' => true,
                'max_score' => $assessment->max_score,
                'has_sub_assessments' => $assessment->subAssessments->isNotEmpty(),
            ];
        }

        return response()->json([
            'success' => true,
            'columns' => $columns,
            'assessments_count' => $assessments->count(),
            'is_senior' => $schoolclass && $schoolclass->classcategories->isNotEmpty()
                ? ($schoolclass->classcategories->first()->is_senior ?? false)
                : false,
        ]);
    }
}