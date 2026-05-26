<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Models\Schoolclass;
use App\Models\Subject;
use App\Models\Schoolterm;
use App\Models\Schoolsession;
use App\Models\Staff;
use App\Models\Payment;
use App\Models\Exam;
use App\Models\AttendanceSummary;
use App\Models\SchoolBill;
use App\Models\SchoolPayment;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

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

        // Students by class
        $students_by_class = Student::with('schoolclass')
            ->select('schoolclassid', \DB::raw('count(*) as total'))
            ->groupBy('schoolclassid')
            ->get()
            ->map(function($item) {
                return [
                    'class_name' => $item->schoolclass?->class_name ?? 'N/A',
                    'total' => $item->total
                ];
            })
            ->sortByDesc('total')
            ->take(5)
            ->values()
            ->toArray();

        // Students by term/session
        $students_in_current_term = Student::whereHas('studentTerms', function($q) use ($currentTerm, $currentSession) {
            if ($currentTerm) $q->where('term_id', $currentTerm->id);
            if ($currentSession) $q->where('session_id', $currentSession->id);
        })->count();

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
        $class_capacity_data = Schoolclass::withCount('students')
            ->get()
            ->map(function($class) {
                $capacity = $class->capacity ?? 50;
                $utilization = $capacity > 0 ? ($class->students_count / $capacity) * 100 : 0;
                return [
                    'class_name' => $class->class_name,
                    'students' => $class->students_count,
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

        // Total payments (if Payment model exists)
        $total_payments = 0;
        $payment_percentage = 0;
        $pending_payments = 0;
        $completed_payments = 0;

        if (class_exists(\App\Models\SchoolPayment::class)) {
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
        if (class_exists(\App\Models\SchoolBill::class)) {
            $total_bills = SchoolBill::sum('amount') ?? 0;
        }

        // Collection rate
        $collection_rate = $total_bills > 0 ? round(($total_payments / $total_bills) * 100, 1) : 0;

        // ============================================================
        // EXAM & ASSESSMENT STATISTICS
        // ============================================================

        // Total exams
        $total_exams = 0;
        $upcoming_exams = 0;
        $completed_exams = 0;

        if (class_exists(\App\Models\Exam::class)) {
            $total_exams = Exam::count();
            $upcoming_exams = Exam::where('start_time', '>', Carbon::now())->count();
            $completed_exams = Exam::where('end_time', '<', Carbon::now())->count();
        }

        // ============================================================
        // ATTENDANCE STATISTICS
        // ============================================================

        // Overall attendance rate
        $overall_attendance_rate = 0;
        if (class_exists(\App\Models\AttendanceSummary::class) && $currentTerm && $currentSession) {
            $attendance_summary = AttendanceSummary::where('term_id', $currentTerm->id)
                ->where('session_id', $currentSession->id)
                ->selectRaw('SUM(days_present) as total_present, SUM(total_school_days) as total_days')
                ->first();

            if ($attendance_summary && $attendance_summary->total_days > 0) {
                $overall_attendance_rate = round(($attendance_summary->total_present / $attendance_summary->total_days) * 100, 1);
            }
        }

        // ============================================================
        // RECENT ACTIVITIES (Simulated for dashboard)
        // ============================================================

        $recent_activities = $this->getRecentActivities();

        // ============================================================
        // YEARLY TRENDS (Last 12 months)
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
        // TOP PERFORMING CLASSES (Based on student count)
        // ============================================================

        $top_classes = Schoolclass::withCount('students')
            ->orderBy('students_count', 'desc')
            ->take(5)
            ->get()
            ->map(function($class) {
                return [
                    'name' => $class->class_name,
                    'student_count' => $class->students_count
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
                'description' => $student->first_name . ' ' . $student->last_name . ' joined the school',
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

    /**
     * Get chart data for the dashboard (AJAX endpoint)
     */
    public function getChartData(Request $request)
    {
        $type = $request->get('type', 'students');

        switch ($type) {
            case 'students':
                $data = Student::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->where('created_at', '>=', Carbon::now()->subDays(30))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                break;

            case 'payments':
                $data = SchoolPayment::selectRaw('DATE(created_at) as date, SUM(amount_paid) as total')
                    ->where('created_at', '>=', Carbon::now()->subDays(30))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                break;

            case 'attendance':
                $data = AttendanceSummary::selectRaw('date, AVG(attendance_percentage) as rate')
                    ->where('created_at', '>=', Carbon::now()->subDays(30))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                break;

            default:
                $data = collect();
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get quick stats for dashboard widgets (AJAX endpoint)
     */
    public function getQuickStats()
    {
        $currentTerm = Schoolterm::where('status', true)->first();
        $currentSession = Schoolsession::where('status', 'Current')->first();

        return response()->json([
            'success' => true,
            'data' => [
                'total_students' => Student::count(),
                'total_staff' => User::whereHas('roles', function($q) {
                    $q->whereIn('name', ['staff', 'teacher']);
                })->count(),
                'total_classes' => Schoolclass::count(),
                'total_subjects' => Subject::count(),
                'attendance_rate' => $this->calculateAttendanceRate($currentTerm, $currentSession),
                'collection_rate' => $this->calculateCollectionRate(),
            ]
        ]);
    }

    /**
     * Calculate overall attendance rate
     */
    private function calculateAttendanceRate($term, $session)
    {
        if (!$term || !$session) return 0;

        $summary = AttendanceSummary::where('term_id', $term->id)
            ->where('session_id', $session->id)
            ->selectRaw('SUM(days_present) as total_present, SUM(total_school_days) as total_days')
            ->first();

        if ($summary && $summary->total_days > 0) {
            return round(($summary->total_present / $summary->total_days) * 100, 1);
        }

        return 0;
    }

    /**
     * Calculate collection rate
     */
    private function calculateCollectionRate()
    {
        $total_bills = SchoolBill::sum('amount') ?? 0;
        $total_payments = SchoolPayment::sum('amount_paid') ?? 0;

        if ($total_bills > 0) {
            return round(($total_payments / $total_bills) * 100, 1);
        }

        return 0;
    }
}
