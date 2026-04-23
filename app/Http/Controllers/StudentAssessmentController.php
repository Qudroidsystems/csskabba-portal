<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Subject;
use App\Models\Assessment;
use App\Models\Schoolterm;
use App\Models\Broadsheets;
use App\Models\Schoolclass;
use App\Models\SchoolInformation;
use Illuminate\Http\Request;
use App\Models\Schoolsession;
use App\Models\SubAssessment;
use App\Models\BroadsheetRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentAssessmentController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('permission:View student assessments', ['only' => ['index', 'printResult']]);
    // }

    // =========================================================================
    // INDEX
    // =========================================================================

    public function index(Request $request)
    {
        $pagetitle = 'My Assessments';
        $studentId = auth()->user()->student_id;

        if (!$studentId) {
            return redirect()->route('dashboard')->with('error', 'Student profile not found.');
        }

        $student = Student::where('id', $studentId)
            ->select('id', 'firstname', 'lastname', 'admissionNo', 'can_view_assessments')
            ->first();

        if (!$student || !$student->can_view_assessments) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view assessments.');
        }

        $terms    = Schoolterm::orderBy('id', 'desc')->get(['id', 'term']);
        $sessions = Schoolsession::where('status', 'Current')
            ->orWhere('status', 'Previous')
            ->orderBy('id', 'desc')
            ->get(['id', 'session']);

        $userSelectedTermId = $request->get('term_id');
        $selectedSessionId  = $request->get('session_id', $sessions->first()?->id ?? null);
        $selectedTermId     = $userSelectedTermId ?: null;
        $isAllTerms         = empty($userSelectedTermId);

        if ($isAllTerms && $selectedSessionId) {
            $latestTermId = DB::table('studentclass')
                ->where('studentId', $studentId)
                ->where('sessionid', $selectedSessionId)
                ->join('schoolterm', 'schoolterm.id', '=', 'studentclass.termid')
                ->orderBy('schoolterm.id', 'desc')
                ->value('schoolterm.id');

            if ($latestTermId) {
                $selectedTermId = $latestTermId;
            }
        }

        $class = null; $term = null; $session = null;

        $studentClassData = DB::table('studentclass')
            ->where('studentId', $studentId)
            ->join('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
            ->join('schoolterm', 'schoolterm.id', '=', 'studentclass.termid')
            ->join('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
            ->when($selectedSessionId, fn ($q) => $q->where('schoolsession.id', $selectedSessionId))
            ->select(
                'schoolclass.id as class_id',
                'schoolclass.schoolclass as class_name',
                'schoolterm.id as term_id',
                'schoolterm.term as term_name',
                'schoolsession.id as session_id',
                'schoolsession.session as session_name'
            )
            ->first();

        if (!$studentClassData) {
            return view('student.assessments.index', compact('pagetitle', 'student', 'terms', 'sessions', 'userSelectedTermId', 'selectedSessionId'))
                ->with('error', 'No class registration found for the selected term and session.');
        }

        $class   = (object) ['id' => $studentClassData->class_id, 'schoolclass' => $studentClassData->class_name];
        $term    = (object) ['id' => $studentClassData->term_id, 'term' => $studentClassData->term_name];
        $session = (object) ['id' => $studentClassData->session_id, 'session' => $studentClassData->session_name];

        $schoolclass = Schoolclass::with('classcategories')->find($studentClassData->class_id);
        if (!$schoolclass || $schoolclass->classcategories->isEmpty()) {
            return view('student.assessments.index', compact('pagetitle', 'student', 'class', 'term', 'session', 'terms', 'sessions', 'userSelectedTermId', 'selectedSessionId'))
                ->with('error', 'Class category not found.');
        }

        $isSenior    = $schoolclass->classcategories->first()->is_senior ?? false;
        $categoryIds = $schoolclass->classcategories->pluck('id');

        $registeredSubjects = DB::table('student_subject_register_record as ssrr')
            ->where('ssrr.studentId', $studentId)
            ->leftJoin('subjectclass', 'subjectclass.id', '=', 'ssrr.subjectclassid')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'ssrr.session')
            ->when($selectedSessionId, fn ($q) => $q->where('schoolsession.id', $selectedSessionId))
            ->when(!$isAllTerms && $selectedTermId, fn ($q) => $q->where('subjectteacher.termid', $selectedTermId))
            ->where('schoolsession.status', '!=', 'Archived')
            ->join('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->select(
                'subject.id as subject_id',
                'subject.subject as subject_name',
                'subject.subject_code',
                'subjectclass.id as subjectclass_id'
            )
            ->distinct()
            ->get();

        $subjectsWithAssessments = collect();
        $overallProgress = [
            'total_subjects'     => 0,
            'completed_subjects' => 0,
            'total_score'        => 0,
            'average_cum'        => 0,
            'gpa'                => '-',
            'cgpa'               => '-',
            'gpa_grade'          => '-',
            'num_subjects'       => 0,
            'total_grade_points' => 0.0,
            'calculated_gpa'     => 0.0,
        ];

        foreach ($registeredSubjects as $regSubject) {
            $assessments = Assessment::whereIn('classcategory_id', $categoryIds)
                ->with('subAssessments')
                ->orderBy('id')
                ->get();

            if ($assessments->isEmpty()) continue;

            $broadsheetRecord = BroadsheetRecord::where('student_id', $studentId)
                ->where('subject_id', $regSubject->subject_id)
                ->where('schoolclass_id', $studentClassData->class_id)
                ->where('session_id', $selectedSessionId ?? $studentClassData->session_id)
                ->first();

            if (!$broadsheetRecord) continue;

            $broadsheet = Broadsheets::where('broadSheet_record_id', $broadsheetRecord->id)
                ->where('term_id', $selectedTermId)
                ->first();

            if (!$broadsheet) continue;

            $broadsheet->load(['assessmentScores', 'subAssessmentScores']);

            $assessmentData = $assessments->map(function ($assessment) use ($broadsheet) {
                $scoreObj = $broadsheet->assessmentScores->where('assessment_id', $assessment->id)->first();
                $score    = $scoreObj ? $scoreObj->score : 0;

                $subScores = collect();
                if ($assessment->subAssessments->isNotEmpty()) {
                    $subScores = $assessment->subAssessments->map(function ($sub) use ($broadsheet) {
                        $subScoreObj = $broadsheet->subAssessmentScores->where('sub_assessment_id', $sub->id)->first();
                        return [
                            'id'         => $sub->id,
                            'name'       => $sub->name,
                            'max_score'  => $sub->max_score,
                            'score'      => $subScoreObj ? $subScoreObj->score : 0,
                            'percentage' => $sub->max_score > 0
                                ? round(($subScoreObj ? $subScoreObj->score : 0) / $sub->max_score * 100, 2)
                                : 0,
                        ];
                    });
                }

                return [
                    'id'               => $assessment->id,
                    'name'             => $assessment->name,
                    'max_score'        => $assessment->max_score,
                    'score'            => $score,
                    'percentage'       => $assessment->max_score > 0
                        ? round($score / $assessment->max_score * 100, 2)
                        : 0,
                    'sub_assessments'  => $subScores,
                ];
            });

            $subjectGPA = $this->getGradePoint($broadsheet->cum ?? 0, $isSenior);

            $subjectsWithAssessments->push([
                'subject_id'   => $regSubject->subject_id,
                'subject_name' => $regSubject->subject_name,
                'subject_code' => $regSubject->subject_code,
                'assessments'  => $assessmentData,
                'total'        => $broadsheet->total ?? 0,
                'bf'           => $broadsheet->bf ?? 0,
                'cum'          => $broadsheet->cum ?? 0,
                'grade'        => $broadsheet->grade ?? '-',
                'subject_gpa'  => round($subjectGPA, 1),
                'remark'       => $broadsheet->remark ?? '-',
                'position'     => $broadsheet->position
                    ? $broadsheet->position . $this->ordinalSuffix($broadsheet->position)
                    : '-',
            ]);

            $overallProgress['total_subjects']++;
            if ($broadsheet->cum > 0) {
                $overallProgress['completed_subjects']++;
                $overallProgress['total_score'] += $broadsheet->cum;
            }
        }

        if ($overallProgress['completed_subjects'] > 0) {
            $overallProgress['average_cum'] = round(
                $overallProgress['total_score'] / $overallProgress['completed_subjects'], 2
            );
        }

        if ($subjectsWithAssessments->isNotEmpty() && $schoolclass) {
            $gpaCgpaData = $this->computeOverallForStudent(
                $studentId,
                $schoolclass,
                $selectedTermId,
                $selectedSessionId ?? $studentClassData->session_id,
                $isSenior
            );
            $overallProgress['gpa']                = round($gpaCgpaData['gpa'], 2);
            $overallProgress['cgpa']               = round($gpaCgpaData['cgpa'], 2);
            $overallProgress['gpa_grade']          = $gpaCgpaData['gpa_grade'] ?? 'F';
            $overallProgress['num_subjects']       = $gpaCgpaData['num_subjects'];
            $overallProgress['total_grade_points'] = $gpaCgpaData['total_grade_points'];
            $overallProgress['calculated_gpa']     = $gpaCgpaData['num_subjects'] > 0
                ? round($gpaCgpaData['total_grade_points'] / $gpaCgpaData['num_subjects'], 2)
                : 0;
        }

        // Build per-term GPA trend for chart
        $gpaTrend = $this->buildGpaTrend($studentId, $selectedSessionId, $isSenior);

        // Gather student picture for print
        $studentPicture = DB::table('studentpicture')->where('studentid', $studentId)->value('picture');
        $schoolInfo     = SchoolInformation::first();

        return view('student.assessments.index', compact(
            'pagetitle', 'student', 'class', 'term', 'session',
            'subjectsWithAssessments', 'terms', 'sessions',
            'userSelectedTermId', 'selectedSessionId', 'overallProgress',
            'gpaTrend', 'studentPicture', 'schoolInfo',
            'selectedTermId', 'isSenior'
        ));
    }

    // =========================================================================
    // PRINT RESULT (PDF) — watermarked "STUDENT COPY - NOT FOR OFFICIAL USE"
    // =========================================================================
    public function printResult(Request $request)
    {
        ini_set('max_execution_time', 120);
        ini_set('memory_limit', '512M');

        $studentId         = auth()->user()->student_id;
        $selectedSessionId = $request->get('session_id');
        $selectedTermId    = $request->get('term_id');

        if (!$studentId) {
            return back()->with('error', 'Student profile not found.');
        }

        $student = Student::where('id', $studentId)
            ->select('id', 'firstname', 'lastname', 'admissionNo', 'can_view_assessments')
            ->first();

        if (!$student || !$student->can_view_assessments) {
            return back()->with('error', 'You do not have permission to print assessments.');
        }

        $studentClassData = DB::table('studentclass')
            ->where('studentId', $studentId)
            ->join('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
            ->join('schoolterm', 'schoolterm.id', '=', 'studentclass.termid')
            ->join('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
            ->when($selectedSessionId, fn ($q) => $q->where('schoolsession.id', $selectedSessionId))
            ->select(
                'schoolclass.id as class_id', 'schoolclass.schoolclass as class_name',
                'schoolterm.id as term_id',   'schoolterm.term as term_name',
                'schoolsession.id as session_id', 'schoolsession.session as session_name'
            )
            ->first();

        if (!$studentClassData) {
            return back()->with('error', 'No class data found.');
        }

        if (!$selectedTermId) {
            $selectedTermId = $studentClassData->term_id;
        }

        $schoolclass = Schoolclass::with('classcategories')->find($studentClassData->class_id);
        $isSenior    = $schoolclass?->classcategories->first()?->is_senior ?? false;
        $categoryIds = $schoolclass?->classcategories->pluck('id') ?? collect();

        $registeredSubjects = DB::table('student_subject_register_record as ssrr')
            ->where('ssrr.studentId', $studentId)
            ->leftJoin('subjectclass', 'subjectclass.id', '=', 'ssrr.subjectclassid')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'ssrr.session')
            ->when($selectedSessionId, fn ($q) => $q->where('schoolsession.id', $selectedSessionId))
            ->when($selectedTermId, fn ($q) => $q->where('subjectteacher.termid', $selectedTermId))
            ->where('schoolsession.status', '!=', 'Archived')
            ->join('subject', 'subject.id', '=', 'subjectteacher.subjectid')
            ->select('subject.id as subject_id', 'subject.subject as subject_name', 'subject.subject_code')
            ->distinct()
            ->get();

        $subjectsWithAssessments = collect();
        $totalObtained = 0;
        $totalObtainable = 0;

        foreach ($registeredSubjects as $regSubject) {
            $assessments = Assessment::whereIn('classcategory_id', $categoryIds)
                ->with('subAssessments')->orderBy('id')->get();
            if ($assessments->isEmpty()) continue;

            $broadsheetRecord = BroadsheetRecord::where('student_id', $studentId)
                ->where('subject_id', $regSubject->subject_id)
                ->where('schoolclass_id', $studentClassData->class_id)
                ->where('session_id', $selectedSessionId ?? $studentClassData->session_id)
                ->first();
            if (!$broadsheetRecord) continue;

            $broadsheet = Broadsheets::where('broadSheet_record_id', $broadsheetRecord->id)
                ->where('term_id', $selectedTermId)->first();
            if (!$broadsheet) continue;

            $broadsheet->load(['assessmentScores', 'subAssessmentScores']);

            $assessmentData = $assessments->map(function ($a) use ($broadsheet) {
                $so = $broadsheet->assessmentScores->where('assessment_id', $a->id)->first();
                return [
                    'name'       => $a->name,
                    'max_score'  => $a->max_score,
                    'score'      => $so ? $so->score : 0,
                    'percentage' => $a->max_score > 0
                        ? round(($so ? $so->score : 0) / $a->max_score * 100, 1)
                        : 0,
                ];
            });

            $subjectsWithAssessments->push([
                'subject_name' => $regSubject->subject_name,
                'subject_code' => $regSubject->subject_code,
                'assessments'  => $assessmentData,
                'total'        => $broadsheet->total ?? 0,
                'bf'           => $broadsheet->bf ?? 0,
                'cum'          => $broadsheet->cum ?? 0,
                'grade'        => $broadsheet->grade ?? '-',
                'remark'       => $broadsheet->remark ?? '-',
                'position'     => $broadsheet->position
                    ? $broadsheet->position . $this->ordinalSuffix($broadsheet->position)
                    : '-',
            ]);

            if (is_numeric($broadsheet->total)) $totalObtained += (float) $broadsheet->total;
            $totalObtainable += 100;
        }

        $overallProgress = ['gpa' => 0, 'cgpa' => 0, 'gpa_grade' => '-', 'average_cum' => 0];
        if ($subjectsWithAssessments->isNotEmpty() && $schoolclass) {
            $gpaCgpaData = $this->computeOverallForStudent(
                $studentId, $schoolclass, $selectedTermId,
                $selectedSessionId ?? $studentClassData->session_id, $isSenior
            );
            $overallProgress['gpa']       = round($gpaCgpaData['gpa'], 2);
            $overallProgress['cgpa']      = round($gpaCgpaData['cgpa'], 2);
            $overallProgress['gpa_grade'] = $gpaCgpaData['gpa_grade'] ?? '-';
            $cumValues = $subjectsWithAssessments->pluck('cum')->filter(fn ($v) => $v > 0);
            $overallProgress['average_cum'] = $cumValues->count() > 0 ? round($cumValues->avg(), 1) : 0;
        }

        $percentage = $totalObtainable > 0 ? round($totalObtained / $totalObtainable * 100, 1) : 0;

        $schoolInfo = SchoolInformation::first();

        // Logo → base64
        $logoBase64 = $this->logoToBase64($schoolInfo);

        // Student picture → base64
        $picturePath = DB::table('studentpicture')->where('studentid', $studentId)->value('picture');
        $pictureBase64 = $this->imageToBase64ForPdf($picturePath);

        $termName = Schoolterm::find($selectedTermId)?->term ?? 'Term';
        $sessionName = $studentClassData->session_name;
        $className   = $studentClassData->class_name;
        $fullName    = strtoupper($student->lastname) . ', ' . $student->firstname;

        // **FIX: Sanitize filename - remove any slashes or problematic characters**
        $safeTermName = preg_replace('/[\/\\\\]/', '-', $termName);
        $safeAdmissionNo = preg_replace('/[\/\\\\]/', '-', $student->admissionNo);
        $filename = 'Assessment_Report_' . $safeAdmissionNo . '_' . $safeTermName . '.pdf';

        $pdf = Pdf::loadView('student.assessments.print-pdf', [  // Note: using a different view name
            'student'                => $student,
            'fullName'               => $fullName,
            'className'              => $className,
            'termName'               => $termName,
            'sessionName'            => $sessionName,
            'subjectsWithAssessments'=> $subjectsWithAssessments,
            'overallProgress'        => $overallProgress,
            'totalObtained'          => round($totalObtained, 1),
            'totalObtainable'        => $totalObtainable,
            'percentage'             => $percentage,
            'schoolInfo'             => $schoolInfo,
            'logoBase64'             => $logoBase64,
            'pictureBase64'          => $pictureBase64,
        ])
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'dpi'                     => 150,
                'defaultFont'             => 'DejaVu Sans',
                'isRemoteEnabled'         => true,
                'isHtml5ParserEnabled'    => true,
                'isFontSubsettingEnabled' => true,
                'isPhpEnabled'           => false,
            ]);

        return $pdf->download($filename);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function buildGpaTrend(int $studentId, ?int $sessionId, bool $isSenior): array
    {
        $trend = [];
        $terms = Schoolterm::orderBy('id')->get();

        foreach ($terms as $t) {
            $broadsheets = Broadsheets::where('term_id', $t->id)
                ->whereHas('broadsheetRecord', function ($q) use ($studentId, $sessionId) {
                    $q->where('student_id', $studentId);
                    if ($sessionId) $q->where('session_id', $sessionId);
                })
                ->get(['total']);

            if ($broadsheets->isEmpty()) continue;

            $gp  = $broadsheets->map(fn ($b) => $this->getGradePoint($b->total, $isSenior));
            $gpa = $gp->avg() ?? 0.0;
            if ($gpa > 0) {
                $trend[$t->term] = round($gpa, 2);
            }
        }

        return $trend;
    }

    private function getGradePoint($score, $isSenior = false): float
    {
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

    private function getGpaGrade(float $gpa): string
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

    private function computeOverallForStudent($studentId, $schoolclass, $termId, $sessionId, $isSenior): array
    {
        $currentTermBroadsheets = Broadsheets::where('term_id', $termId)
            ->whereHas('broadsheetRecord', function ($q) use ($studentId, $sessionId) {
                $q->where('student_id', $studentId)->where('session_id', $sessionId);
            })
            ->get(['total']);

        $termGradePoints    = $currentTermBroadsheets->map(fn ($b) => $this->getGradePoint($b->total, $isSenior));
        $gpa                = $termGradePoints->avg() ?? 0.0;
        $num_subjects       = $currentTermBroadsheets->count();
        $total_grade_points = $termGradePoints->sum();

        $averageTotal = $currentTermBroadsheets->avg('total') ?? 0.0;
        $category     = $schoolclass->classcategories->first();
        $gpaGrade     = $this->getGpaGrade($gpa);

        $annualGPAs = [];
        $studentSessionsInCategory = DB::table('broadsheet_records')
            ->join('schoolclass', 'schoolclass.id', '=', 'broadsheet_records.schoolclass_id')
            ->join('classcategories', 'classcategories.id', '=', 'schoolclass.classcategoryid')
            ->where('broadsheet_records.student_id', $studentId)
            ->where('classcategories.is_senior', $isSenior)
            ->select('broadsheet_records.session_id')
            ->distinct()
            ->orderByDesc('broadsheet_records.session_id')
            ->limit(3)
            ->pluck('session_id');

        foreach ($studentSessionsInCategory as $targetSession) {
            $sessionAnnualGPAs = [];
            for ($t = 1; $t <= 3; $t++) {
                $termBroadsheets = Broadsheets::where('term_id', $t)
                    ->whereHas('broadsheetRecord', function ($q) use ($studentId, $targetSession) {
                        $q->where('student_id', $studentId)->where('session_id', $targetSession);
                    })
                    ->get(['total']);

                $termGradePointsPast = $termBroadsheets->map(fn ($b) => $this->getGradePoint($b->total, $isSenior));
                $sessionAnnualGPAs[] = $termGradePointsPast->avg() ?? 0.0;
            }
            $annualGPAs[] = collect($sessionAnnualGPAs)->avg() ?? 0.0;
        }

        $cgpa = collect($annualGPAs)->avg() ?? 0.0;

        return [
            'gpa'                => $gpa,
            'cgpa'               => $cgpa,
            'gpa_grade'          => $gpaGrade,
            'num_subjects'       => $num_subjects,
            'total_grade_points' => $total_grade_points,
        ];
    }

    private function ordinalSuffix(int $n): string
    {
        $last2 = $n % 100;
        $last1 = $n % 10;
        if ($last2 >= 11 && $last2 <= 13) return 'th';
        return match ($last1) {
            1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th'
        };
    }

    private function logoToBase64($schoolInfo): string
    {
        $placeholder = 'data:image/svg+xml;base64,' . base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80">
                <rect width="80" height="80" rx="40" fill="#1e3a5f"/>
                <text x="40" y="46" text-anchor="middle" fill="white" font-family="Arial" font-size="14" font-weight="bold">SCH</text>
            </svg>'
        );

        if (!$schoolInfo || empty($schoolInfo->school_logo)) return $placeholder;

        $paths = [
            storage_path('app/public/' . $schoolInfo->school_logo),
            public_path('storage/' . $schoolInfo->school_logo),
            public_path($schoolInfo->school_logo),
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

        if (!$path) return $placeholder;

        $possiblePaths = [
            public_path('storage/student_avatars/' . $path),
            storage_path('app/public/student_avatars/' . $path),
            public_path('storage/' . $path),
            storage_path('app/public/' . $path),
            public_path($path),
        ];

        foreach ($possiblePaths as $fullPath) {
            if (file_exists($fullPath) && filesize($fullPath) > 100) {
                $mime = mime_content_type($fullPath) ?: 'image/jpeg';
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
            }
        }

        return $placeholder;
    }

    protected function getDefaultGrade($score): string
    {
        if ($score >= 70) return 'A';
        if ($score >= 60) return 'B';
        if ($score >= 50) return 'C';
        if ($score >= 40) return 'D';
        return 'F';
    }
}
