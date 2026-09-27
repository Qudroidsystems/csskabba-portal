<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Lms\Concerns\InteractsWithLms;
use App\Models\CertificateTemplate;
use App\Models\LmsCourse;
use App\Models\User;
use App\Services\Lms\EnrollmentService;
use App\Services\Lms\ProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    use InteractsWithLms;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:Manage courses|Grade coursework');
    }

    public function index(Request $request)
    {
        $u = Auth::user();
        $mine = !$u->can('Manage courses'); // graders see their own courses only

        $q = LmsCourse::query()
            ->withCount(['lessons', 'enrollments'])
            ->when($mine, fn ($x) => $x->where('teacher_id', $u->id))
            ->when($request->filled('q'), fn ($x) => $x->where('title', 'like', "%{$request->q}%"))
            ->when($request->filled('class'), fn ($x) => $x->where('schoolclass_id', $request->class))
            ->when($request->status === 'published', fn ($x) => $x->where('is_published', true))
            ->when($request->status === 'draft', fn ($x) => $x->where('is_published', false))
            ->orderByDesc('id');

        return view('lms.courses.index', [
            'pagetitle' => 'Courses',
            'rows'      => $q->paginate(20)->withQueryString(),
            'classes'   => $this->classList(),
            'stats'     => [
                'total'     => LmsCourse::when($mine, fn ($x) => $x->where('teacher_id', $u->id))->count(),
                'published' => LmsCourse::when($mine, fn ($x) => $x->where('teacher_id', $u->id))->where('is_published', true)->count(),
                'learners'  => (int) DB::table('lms_enrollments')->distinct('student_id')->count('student_id'),
            ],
        ]);
    }

    public function create()
    {
        return view('lms.courses.form', $this->formData(new LmsCourse(['enrollment_mode' => 'both'])));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = Auth::id();
        if (!Auth::user()->can('Manage courses')) {
            $data['teacher_id'] = Auth::id(); // graders own their courses
        }
        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('lms/covers', 'public');
        }
        $course = LmsCourse::create($data);

        return redirect()->route('lms.courses.show', $course)->with('success', 'Course created. Add sections and lessons below.');
    }

    /** Course management dashboard (curriculum, learners, coursework, engagement). */
    public function show(LmsCourse $course)
    {
        $this->authorizeGrade($course);

        $course->load(['sections.lessons', 'lessons' => fn ($q) => $q->orderBy('position')]);

        return view('lms.courses.show', [
            'pagetitle'   => $course->title,
            'course'      => $course,
            'assignments' => $course->assignments()->withCount('submissions')->latest()->get(),
            'quizzes'     => $course->quizzes()->withCount('questions')->latest()->get(),
            'liveClasses' => $course->liveClasses()->orderByDesc('scheduled_at')->get(),
            'announcements' => $course->announcements()->limit(10)->get(),
            'enrollCount' => $course->enrollments()->count(),
            'completedCount' => $course->enrollments()->where('status', 'completed')->count(),
            'exams'       => $this->examOptions($course),
            'certTemplates' => $this->certTemplates(),
        ]);
    }

    public function edit(LmsCourse $course)
    {
        $this->authorizeManage($course);
        return view('lms.courses.form', $this->formData($course));
    }

    public function update(Request $request, LmsCourse $course)
    {
        $this->authorizeManage($course);
        $data = $this->validated($request);
        if (!Auth::user()->can('Manage courses')) {
            unset($data['teacher_id']); // graders can't reassign ownership
        }
        if ($request->hasFile('cover')) {
            if ($course->cover_path) Storage::disk('public')->delete($course->cover_path);
            $data['cover_path'] = $request->file('cover')->store('lms/covers', 'public');
        }
        $course->update($data);

        return redirect()->route('lms.courses.show', $course)->with('success', 'Course updated.');
    }

    public function destroy(LmsCourse $course)
    {
        $this->authorizeManage($course);
        DB::transaction(function () use ($course) {
            $ids = ['course_id' => $course->id];
            DB::table('lms_lesson_progress')->where($ids)->delete();
            DB::table('lms_assignment_submissions')->whereIn('assignment_id', $course->assignments()->pluck('id'))->delete();
            DB::table('lms_quiz_attempts')->whereIn('quiz_id', $course->quizzes()->pluck('id'))->delete();
            DB::table('lms_quiz_questions')->whereIn('quiz_id', $course->quizzes()->pluck('id'))->delete();
            $course->assignments()->delete();
            $course->quizzes()->delete();
            $course->discussions()->delete();
            $course->liveClasses()->delete();
            $course->announcements()->delete();
            $course->lessons()->delete();
            $course->sections()->delete();
            $course->enrollments()->delete();
            if ($course->cover_path) Storage::disk('public')->delete($course->cover_path);
            $course->delete();
        });

        return redirect()->route('lms.courses.index')->with('success', 'Course deleted.');
    }

    public function togglePublish(LmsCourse $course)
    {
        $this->authorizeManage($course);
        $course->update(['is_published' => !$course->is_published]);
        return back()->with('success', $course->is_published ? 'Course published.' : 'Course unpublished.');
    }

    // ── helpers ───────────────────────────────────────────────────────────
    protected function validated(Request $request): array
    {
        $v = $request->validate([
            'title'                       => 'required|string|max:200',
            'code'                        => 'nullable|string|max:60',
            'description'                 => 'nullable|string',
            'subject_id'                  => 'nullable|integer',
            'schoolclass_id'              => 'nullable|integer',
            'session_id'                  => 'nullable|integer',
            'term_id'                     => 'nullable|integer',
            'teacher_id'                  => 'nullable|integer',
            'enrollment_mode'             => 'required|in:auto,manual,both',
            'allow_self_enroll'           => 'nullable|boolean',
            'completion_cert_template_id' => 'nullable|integer',
            'is_published'                => 'nullable|boolean',
            'cover'                       => 'nullable|image|max:4096',
        ]);
        $v['allow_self_enroll'] = $request->boolean('allow_self_enroll');
        $v['is_published']      = $request->boolean('is_published');
        unset($v['cover']);
        return $v;
    }

    protected function formData(LmsCourse $course): array
    {
        return [
            'pagetitle'     => $course->exists ? 'Edit Course' : 'New Course',
            'course'        => $course,
            'subjects'      => Schema::hasTable('subject') ? DB::table('subject')->orderBy('subject')->get(['id', 'subject']) : collect(),
            'classes'       => $this->classList(),
            'sessions'      => Schema::hasTable('schoolsession') ? DB::table('schoolsession')->orderByDesc('id')->get(['id', 'session', 'status']) : collect(),
            'terms'         => Schema::hasTable('schoolterm') ? DB::table('schoolterm')->get(['id', 'term', 'status']) : collect(),
            'teachers'      => $this->teacherList(),
            'certTemplates' => $this->certTemplates(),
        ];
    }

    protected function classList()
    {
        if (!Schema::hasTable('schoolclass')) return collect();
        return DB::table('schoolclass')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->selectRaw("schoolclass.id, TRIM(CONCAT(schoolclass.schoolclass,' ',COALESCE(schoolarm.arm,''))) as name")
            ->orderBy('schoolclass.schoolclass')->get();
    }

    protected function teacherList()
    {
        try {
            return User::role('Staff')->orderBy('name')->get(['id', 'name']);
        } catch (\Throwable $e) {
            return User::has('staff')->orderBy('name')->get(['id', 'name']);
        }
    }

    protected function certTemplates()
    {
        if (!Schema::hasTable('certificate_templates')) return collect();
        return CertificateTemplate::orderBy('name')->get(['id', 'name']);
    }

    /** Existing CBT exams that a lesson can point to. */
    protected function examOptions(LmsCourse $course)
    {
        if (!Schema::hasTable('exams')) return collect();
        return DB::table('exams')
            ->when($course->schoolclass_id, fn ($q) => $q->where('schoolclass_id', $course->schoolclass_id))
            ->orderByDesc('id')->limit(200)->get(['id', 'title']);
    }
}
