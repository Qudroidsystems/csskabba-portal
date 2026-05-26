<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Models\Schoolclass;
use App\Models\Subject;
use App\Models\Schoolterm;
use App\Models\Schoolsession;
use App\Models\Studentclass;
use App\Models\Exam;
use App\Models\AttendanceSummary;
use App\Models\SchoolBill;
use App\Models\SchoolPayment;
use App\Models\Broadsheets;
use App\Models\Subjectclass;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:dashboard', ['only' => ['index']]);
    }

    public function index()
    {
        // Page title
        $pagetitle = "Dashboard Management";

        // Date ranges for comparisons
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();
        $currentYear = Carbon::now()->year;
        $previousYear = Carbon::now()->subYear()->year;
        $currentTerm = Schoolterm::where('status', true)->first();
        $currentSession = Schoolsession::where('status', 'Current')->first();

        // ============================================================
        // STUDENT STATISTICS
        // ============================================================

        // Total population (all students)
        $total_population = Student::count();
        $previous_population = Student::where('created_at', '<', $currentMonth)
            ->where('created_at', '>=', $previousMonth)
            ->count();
        $population_percentage = $previous_population > 0
            ? number_format((($total_population - $previous_population) / $previous_population) * 100, 2)
            : ($total_population > 0 ? 100.00 : 0.00);

        // Status counts (Old vs New Students)
        $status_counts = Student::groupBy('statusId')
            ->selectRaw("CASE
                WHEN statusId = 1 THEN 'Old Student'
                WHEN statusId = 2 THEN 'New Student'
                ELSE 'Other'
            END as student_status, COUNT(*) as student_count")
            ->pluck('student_count', 'student_status')
            ->toArray();

        $status_counts = [
            'Old Student' => $status_counts['Old Student'] ?? 0,
            'New Student' => $status_counts['New Student'] ?? 0,
            'Other' => $status_counts['Other'] ?? 0
        ];

        // Gender counts
        $gender_counts = Student::groupBy('gender')
            ->selectRaw('gender, COUNT(*) as gender_count')
            ->pluck('gender_count', 'gender')
            ->toArray();
        $gender_counts = [
            'Male' => $gender_counts['Male'] ?? 0,
            'Female' => $gender_counts['Female'] ?? 0
        ];

        // Student percentage changes
        $previous_male = Student::where('gender', 'Male')
            ->where('created_at', '<', $currentMonth)
            ->where('created_at', '>=', $previousMonth)
            ->count();
        $male_percentage = $previous_male > 0
            ? number_format((($gender_counts['Male'] - $previous_male) / $previous_male) * 100, 2)
            : ($gender_counts['Male'] > 0 ? 100.00 : 0.00);

        $previous_female = Student::where('gender', 'Female')
            ->where('created_at', '<', $currentMonth)
            ->where('created_at', '>=', $previousMonth)
            ->count();
        $female_percentage = $previous_female > 0
            ? number_format((($gender_counts['Female'] - $previous_female) / $previous_female) * 100, 2)
            : ($gender_counts['Female'] > 0 ? 100.00 : 0.00);

        // FIXED: Students by class (using studentclass pivot table)
        $students_by_class = Studentclass::with('schoolclass')
            ->where('sessionid', $currentSession?->id ?? 0)
            ->select('schoolclassid', DB::raw('count(*) as total'))
            ->groupBy('schoolclassid')
            ->get()
            ->map(function($item) {
                return [
                    'class_name' => $item->schoolclass?->schoolclass ?? 'N/A',
                    'arm' => $item->schoolclass?->armRelation?->arm ?? '',
                    'total' => $item->total
                ];
            })
            ->sortByDesc('total')
            ->take(5)
            ->values()
            ->toArray();

        // Students by term/session (using studentclass table)
        $students_in_current_term = Studentclass::where('sessionid', $currentSession?->id ?? 0)
            ->where('termid', $currentTerm?->id ?? 0)
            ->distinct('studentId')
            ->count('studentId');

        // ============================================================
        // ACADEMIC STATISTICS
        // ============================================================

        // Student academic performance - average scores by class
        $academic_performance = [];
        if (class_exists(Broadsheets::class) && $currentTerm && $currentSession) {
            $academic_performance = Broadsheets::where('term_id', $currentTerm->id)
                ->whereHas('broadsheetRecord', function($q) use ($currentSession) {
                    $q->where('session_id', $currentSession->id);
                })
                ->select('broadsheet_records.schoolclass_id', DB::raw('AVG(broadsheets.total) as avg_score'))
                ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
                ->groupBy('broadsheet_records.schoolclass_id')
                ->get()
                ->map(function($item) {
                    return [
                        'class_name' => Schoolclass::find($item->schoolclass_id)?->schoolclass ?? 'N/A',
                        'avg_score' => round($item->avg_score, 1)
                    ];
                })
                ->toArray();
        }

        // Top performing students (based on cumulative scores)
        $top_students = [];
        if (class_exists(Broadsheets::class) && $currentTerm && $currentSession) {
            $top_students = Broadsheets::where('term_id', $currentTerm->id)
                ->whereHas('broadsheetRecord', function($q) use ($currentSession) {
                    $q->where('session_id', $currentSession->id);
                })
                ->select(
                    'broadsheet_records.student_id',
                    DB::raw('SUM(broadsheets.cum) as total_cum'),
                    DB::raw('COUNT(DISTINCT broadsheet_records.subject_id) as subject_count')
                )
                ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
                ->groupBy('broadsheet_records.student_id')
                ->orderBy('total_cum', 'desc')
                ->limit(5)
                ->get()
                ->map(function($item) {
                    $student = Student::find($item->student_id);
                    return [
                        'name' => $student ? $student->firstname . ' ' . $student->lastname : 'N/A',
                        'admission_no' => $student?->admissionNo ?? 'N/A',
                        'average' => $item->subject_count > 0 ? round($item->total_cum / $item->subject_count, 1) : 0
                    ];
                })
                ->toArray();
        }

        // Subject performance - best and worst performing subjects
        $subject_performance = [];
        if (class_exists(Subjectclass::class) && class_exists(Broadsheets::class) && $currentTerm && $currentSession) {
            $subject_performance = Broadsheets::where('term_id', $currentTerm->id)
                ->whereHas('broadsheetRecord', function($q) use ($currentSession) {
                    $q->where('session_id', $currentSession->id);
                })
                ->select(
                    'broadsheet_records.subject_id',
                    DB::raw('AVG(broadsheets.total) as avg_score'),
                    DB::raw('MAX(broadsheets.total) as max_score'),
                    DB::raw('MIN(broadsheets.total) as min_score')
                )
                ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
                ->groupBy('broadsheet_records.subject_id')
                ->get()
                ->map(function($item) {
                    $subject = Subject::find($item->subject_id);
                    return [
                        'subject_name' => $subject?->subject ?? 'N/A',
                        'avg_score' => round($item->avg_score, 1),
                        'max_score' => round($item->max_score, 1),
                        'min_score' => round($item->min_score, 1),
                        'pass_rate' => $this->calculatePassRate($item->subject_id)
                    ];
                })
                ->sortByDesc('avg_score')
                ->take(5)
                ->values()
                ->toArray();
        }

        // Grade distribution
        $grade_distribution = $this->getGradeDistribution($currentTerm, $currentSession);

        // ============================================================
        // STAFF STATISTICS
        // ============================================================

        // Staff count (users with roles 'staff' or 'teacher' or NULL student_id)
        $staff_count = User::whereHas('roles', function($query) {
                $query->whereIn('name', ['staff', 'teacher', 'admin']);
            })
            ->orWhereNull('student_id')
            ->count();

        $previous_staff = User::whereHas('roles', function($query) {
                $query->whereIn('name', ['staff', 'teacher', 'admin']);
            })
            ->orWhereNull('student_id')
            ->where('created_at', '<', $currentMonth)
            ->where('created_at', '>=', $previousMonth)
            ->count();
        $staff_percentage = $previous_staff > 0
            ? number_format((($staff_count - $previous_staff) / $previous_staff) * 100, 2)
            : ($staff_count > 0 ? 100.00 : 0.00);

        // Staff by role
        $staff_by_role = User::with('roles')
            ->whereHas('roles', function($query) {
                $query->whereIn('name', ['staff', 'teacher', 'admin']);
            })
            ->get()
            ->groupBy(function($user) {
                return $user->roles->first()->name ?? 'Unknown';
            })
            ->map(function($group) {
                return $group->count();
            })
            ->toArray();

        // ============================================================
        // CLASS & SUBJECT STATISTICS
        // ============================================================

        // Total classes
        $total_classes = Schoolclass::count();
        $previous_classes = Schoolclass::where('created_at', '<', $currentMonth)
            ->where('created_at', '>=', $previousMonth)
            ->count();
        $classes_percentage = $previous_classes > 0
            ? number_format((($total_classes - $previous_classes) / $previous_classes) * 100, 2)
            : ($total_classes > 0 ? 100.00 : 0.00);

        // Total subjects
        $total_subjects = Subject::count();
        $previous_subjects = Subject::where('created_at', '<', $currentMonth)
            ->where('created_at', '>=', $previousMonth)
            ->count();
        $subjects_percentage = $previous_subjects > 0
            ? number_format((($total_subjects - $previous_subjects) / $previous_subjects) * 100, 2)
            : ($total_subjects > 0 ? 100.00 : 0.00);

        // Class capacity and utilization
        $class_capacity_data = Schoolclass::withCount(['studentCurrentTerms' => function($q) use ($currentSession) {
                if ($currentSession) $q->where('sessionId', $currentSession->id);
            }])
            ->get()
            ->map(function($class) {
                $capacity = $class->capacity ?? 50;
                $utilization = $capacity > 0 ? ($class->student_current_terms_count / $capacity) * 100 : 0;
                return [
                    'class_name' => $class->schoolclass,
                    'arm' => $class->armRelation?->arm ?? '',
                    'students' => $class->student_current_terms_count,
                    'capacity' => $capacity,
                    'utilization' => round($utilization, 1)
                ];
            })
            ->sortByDesc('students')
            ->take(5)
            ->values()
            ->toArray();

        // ============================================================
        // PAYMENT & FINANCE STATISTICS
        // ============================================================

        // Total payments
        $total_payments = 0;
        $payment_percentage = 0;
        $pending_payments = 0;
        $completed_payments = 0;

        if (class_exists(SchoolPayment::class)) {
            $total_payments = SchoolPayment::sum('amount_paid') ?? 0;
            $previous_payments = SchoolPayment::where('created_at', '<', $currentMonth)
                ->where('created_at', '>=', $previousMonth)
                ->sum('amount_paid');
            $payment_percentage = $previous_payments > 0
                ? number_format((($total_payments - $previous_payments) / $previous_payments) * 100, 2)
                : ($total_payments > 0 ? 100.00 : 0.00);

            $pending_payments = SchoolPayment::where('status', 'pending')->count();
            $completed_payments = SchoolPayment::where('status', 'completed')->count();
        }

        // Total bills amount
        $total_bills = 0;
        if (class_exists(SchoolBill::class)) {
            $total_bills = SchoolBill::sum('amount') ?? 0;
        }

        // Collection rate
        $collection_rate = $total_bills > 0 ? round(($total_payments / $total_bills) * 100, 1) : 0;

        // ============================================================
        // EXAM STATISTICS
        // ============================================================

        $total_exams = 0;
        $upcoming_exams = 0;
        $completed_exams = 0;

        if (class_exists(Exam::class)) {
            $total_exams = Exam::count();
            $upcoming_exams = Exam::where('start_time', '>', Carbon::now())->count();
            $completed_exams = Exam::where('end_time', '<', Carbon::now())->count();
        }

        // ============================================================
        // ATTENDANCE STATISTICS
        // ============================================================

        $overall_attendance_rate = 0;
        if (class_exists(AttendanceSummary::class) && $currentTerm && $currentSession) {
            $attendance_summary = AttendanceSummary::where('term_id', $currentTerm->id)
                ->where('session_id', $currentSession->id)
                ->selectRaw('SUM(days_present) as total_present, SUM(total_school_days) as total_days')
                ->first();

            if ($attendance_summary && $attendance_summary->total_days > 0) {
                $overall_attendance_rate = round(($attendance_summary->total_present / $attendance_summary->total_days) * 100, 1);
            }
        }

        // ============================================================
        // RECENT ACTIVITIES
        // ============================================================

        $recent_activities = $this->getRecentActivities();

        // ============================================================
        // YEARLY TRENDS
        // ============================================================

        $yearly_trends = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthly_students = Student::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            $yearly_trends[] = [
                'month' => $month->format('M Y'),
                'students' => $monthly_students
            ];
        }

        // ============================================================
        // TOP CLASSES (Based on student count)
        // ============================================================

        $top_classes = Schoolclass::withCount(['studentCurrentTerms' => function($q) use ($currentSession) {
                if ($currentSession) $q->where('sessionId', $currentSession->id);
            }])
            ->orderBy('student_current_terms_count', 'desc')
            ->take(5)
            ->get()
            ->map(function($class) {
                return [
                    'name' => $class->schoolclass,
                    'arm' => $class->armRelation?->arm ?? '',
                    'student_count' => $class->student_current_terms_count
                ];
            })
            ->toArray();

        return view('dashboards.dashboard', compact(
            'pagetitle',
            // Student stats
            'total_population',
            'population_percentage',
            'status_counts',
            'gender_counts',
            'male_percentage',
            'female_percentage',
            'students_by_class',
            'students_in_current_term',
            // Academic stats
            'academic_performance',
            'top_students',
            'subject_performance',
            'grade_distribution',
            // Staff stats
            'staff_count',
            'staff_percentage',
            'staff_by_role',
            // Class & Subject stats
            'total_classes',
            'classes_percentage',
            'total_subjects',
            'subjects_percentage',
            'class_capacity_data',
            // Payment stats
            'total_payments',
            'payment_percentage',
            'pending_payments',
            'completed_payments',
            'total_bills',
            'collection_rate',
            // Exam stats
            'total_exams',
            'upcoming_exams',
            'completed_exams',
            // Attendance stats
            'overall_attendance_rate',
            // Current term/session
            'currentTerm',
            'currentSession',
            // Additional data
            'recent_activities',
            'yearly_trends',
            'top_classes'
        ));
    }

    /**
     * Calculate pass rate for a subject
     */
    private function calculatePassRate($subjectId)
    {
        $totalStudents = Broadsheets::whereHas('broadsheetRecord', function($q) use ($subjectId) {
            $q->where('subject_id', $subjectId);
        })->count();

        $passedStudents = Broadsheets::whereHas('broadsheetRecord', function($q) use ($subjectId) {
            $q->where('subject_id', $subjectId);
        })->where('total', '>=', 40)->count();

        return $totalStudents > 0 ? round(($passedStudents / $totalStudents) * 100, 1) : 0;
    }

    /**
     * Get grade distribution for the current term
     */
    private function getGradeDistribution($term, $session)
    {
        if (!$term || !$session || !class_exists(Broadsheets::class)) {
            return [];
        }

        $grades = Broadsheets::where('term_id', $term->id)
            ->whereHas('broadsheetRecord', function($q) use ($session) {
                $q->where('session_id', $session->id);
            })
            ->select('grade', DB::raw('count(*) as count'))
            ->groupBy('grade')
            ->get()
            ->pluck('count', 'grade')
            ->toArray();

        $gradeOrder = ['A1', 'B2', 'B3', 'C4', 'C5', 'C6', 'D7', 'E8', 'F9'];
        $result = [];

        foreach ($gradeOrder as $grade) {
            $result[$grade] = $grades[$grade] ?? 0;
        }

        return $result;
    }

    /**
     * Get recent activities for the dashboard
     */
    private function getRecentActivities()
    {
        $activities = [];

        // Recent student registrations
        $recent_students = Student::orderBy('created_at', 'desc')->take(3)->get();
        foreach ($recent_students as $student) {
            $activities[] = [
                'type' => 'student',
                'title' => 'New Student Enrolled',
                'description' => $student->firstname . ' ' . $student->lastname . ' joined the school',
                'time' => $student->created_at->diffForHumans(),
                'icon' => 'ph-user-plus',
                'color' => 'primary'
            ];
        }

        // Recent staff registrations
        $recent_staff = User::whereHas('roles', function($q) {
                $q->whereIn('name', ['staff', 'teacher']);
            })
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get();
        foreach ($recent_staff as $staff) {
            $activities[] = [
                'type' => 'staff',
                'title' => 'New Staff Member',
                'description' => $staff->name . ' joined as ' . ($staff->roles->first()->name ?? 'staff'),
                'time' => $staff->created_at->diffForHumans(),
                'icon' => 'ph-chalkboard-teacher',
                'color' => 'success'
            ];
        }

        // Sort activities by time (most recent first)
        usort($activities, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        return array_slice($activities, 0, 5);
    }
}
