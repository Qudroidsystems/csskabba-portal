<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Lms\Concerns\InteractsWithLms;
use App\Models\LmsCourse;
use App\Services\Lms\EnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Manage a course's learners: auto-sync a class, manually add/remove students.
 */
class EnrollmentController extends Controller
{
    use InteractsWithLms;

    public function __construct(protected EnrollmentService $enroller)
    {
        $this->middleware('auth');
        $this->middleware('permission:Manage courses|Grade coursework');
    }

    public function index(Request $request, LmsCourse $course)
    {
        $this->authorizeGrade($course);

        $rows = DB::table('lms_enrollments as e')
            ->leftJoin('studentRegistration as s', 's.id', '=', 'e.student_id')
            ->where('e.course_id', $course->id)
            ->when($request->filled('q'), fn ($q) => $q->where(function ($w) use ($request) {
                $w->where('s.firstname', 'like', "%{$request->q}%")
                  ->orWhere('s.lastname', 'like', "%{$request->q}%")
                  ->orWhere('s.admissionNo', 'like', "%{$request->q}%");
            }))
            ->orderBy('s.lastname')->orderBy('s.firstname')
            ->select('e.*', 's.firstname', 's.lastname', 's.admissionNo')
            ->paginate(30)->withQueryString();

        return view('lms.enrollments.index', [
            'pagetitle' => 'Learners — ' . $course->title,
            'course'    => $course,
            'rows'      => $rows,
            'eligible'  => count($this->enroller->eligibleStudentIds($course)),
        ]);
    }

    public function syncAuto(LmsCourse $course)
    {
        $this->authorizeManage($course);
        $n = $this->enroller->syncAuto($course);
        return back()->with('success', $n > 0 ? "Enrolled {$n} student(s) from the class." : 'No new students to enrol.');
    }

    /** AJAX: students of the course class who are not yet enrolled. */
    public function candidates(Request $request, LmsCourse $course)
    {
        $this->authorizeManage($course);

        $enrolled = DB::table('lms_enrollments')->where('course_id', $course->id)->pluck('student_id')->all();

        $q = DB::table('studentclass as sc')
            ->join('studentRegistration as s', 's.id', '=', 'sc.studentId')
            ->when($course->schoolclass_id, fn ($x) => $x->where('sc.schoolclassid', $course->schoolclass_id))
            ->when($request->filled('class_id'), fn ($x) => $x->where('sc.schoolclassid', $request->class_id))
            ->when($request->filled('session_id'), fn ($x) => $x->where('sc.sessionid', $request->session_id))
            ->when($request->filled('q'), fn ($x) => $x->where(function ($w) use ($request) {
                $w->where('s.firstname', 'like', "%{$request->q}%")
                  ->orWhere('s.lastname', 'like', "%{$request->q}%")
                  ->orWhere('s.admissionNo', 'like', "%{$request->q}%");
            }))
            ->when($enrolled, fn ($x) => $x->whereNotIn('s.id', $enrolled))
            ->orderBy('s.lastname')->orderBy('s.firstname')
            ->selectRaw("s.id, TRIM(CONCAT(s.firstname,' ',s.lastname)) as name, s.admissionNo")
            ->limit(500)->get();

        return response()->json(['data' => $q]);
    }

    public function store(Request $request, LmsCourse $course)
    {
        $this->authorizeManage($course);
        $ids = (array) $request->input('student_ids', []);
        $n = $this->enroller->enroll($course, $ids, $this->me()->id);
        return back()->with('success', "Enrolled {$n} student(s).");
    }

    public function destroy(Request $request, LmsCourse $course, int $student)
    {
        $this->authorizeManage($course);
        $this->enroller->unenroll($course, [$student]);
        return back()->with('success', 'Student removed from the course.');
    }
}
