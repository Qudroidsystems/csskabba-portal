<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Lms\Concerns\InteractsWithLms;
use App\Models\LmsCourse;
use App\Models\LmsQuiz;
use App\Models\LmsQuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Teacher-side lesson quiz builder and results overview. Student take/submit
 * lives in LearnController (enrolment-gated, not permission-gated).
 */
class QuizController extends Controller
{
    use InteractsWithLms;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:Manage courses|Grade coursework');
    }

    public function store(Request $request, LmsCourse $course)
    {
        $this->authorizeManage($course);
        $data = $this->quizRules($request);
        $data['course_id'] = $course->id;
        $quiz = LmsQuiz::create($data);
        return redirect()->route('lms.quizzes.edit', [$course, $quiz])->with('success', 'Quiz created. Add questions below.');
    }

    public function edit(LmsCourse $course, LmsQuiz $quiz)
    {
        $this->authorizeManage($course);
        abort_unless($quiz->course_id === $course->id, 404);
        $quiz->load('questions');
        return view('lms.quizzes.edit', [
            'pagetitle' => 'Quiz — ' . $quiz->title,
            'course'    => $course,
            'quiz'      => $quiz,
        ]);
    }

    public function update(Request $request, LmsCourse $course, LmsQuiz $quiz)
    {
        $this->authorizeManage($course);
        abort_unless($quiz->course_id === $course->id, 404);
        $quiz->update($this->quizRules($request));
        return back()->with('success', 'Quiz updated.');
    }

    public function destroy(LmsCourse $course, LmsQuiz $quiz)
    {
        $this->authorizeManage($course);
        abort_unless($quiz->course_id === $course->id, 404);
        DB::table('lms_quiz_attempts')->where('quiz_id', $quiz->id)->delete();
        $quiz->questions()->delete();
        $quiz->delete();
        return redirect()->route('lms.courses.show', $course)->with('success', 'Quiz deleted.');
    }

    public function storeQuestion(Request $request, LmsCourse $course, LmsQuiz $quiz)
    {
        $this->authorizeManage($course);
        abort_unless($quiz->course_id === $course->id, 404);
        $data = $this->questionRules($request);
        $data['quiz_id']  = $quiz->id;
        $data['position'] = (int) $quiz->questions()->max('position') + 1;
        LmsQuizQuestion::create($data);
        return back()->with('success', 'Question added.');
    }

    public function updateQuestion(Request $request, LmsCourse $course, LmsQuiz $quiz, LmsQuizQuestion $question)
    {
        $this->authorizeManage($course);
        abort_unless($quiz->course_id === $course->id && $question->quiz_id === $quiz->id, 404);
        $question->update($this->questionRules($request));
        return back()->with('success', 'Question updated.');
    }

    public function destroyQuestion(LmsCourse $course, LmsQuiz $quiz, LmsQuizQuestion $question)
    {
        $this->authorizeManage($course);
        abort_unless($quiz->course_id === $course->id && $question->quiz_id === $quiz->id, 404);
        $question->delete();
        return back()->with('success', 'Question removed.');
    }

    /** Attempts overview for a quiz (best attempt per student). */
    public function results(LmsCourse $course, LmsQuiz $quiz)
    {
        $this->authorizeGrade($course);
        abort_unless($quiz->course_id === $course->id, 404);

        $rows = DB::table('lms_quiz_attempts as a')
            ->leftJoin('studentRegistration as s', 's.id', '=', 'a.student_id')
            ->where('a.quiz_id', $quiz->id)
            ->orderByDesc('a.percent')->orderBy('a.attempt_no')
            ->select('a.*', 's.firstname', 's.lastname', 's.admissionNo')
            ->paginate(40);

        return view('lms.quizzes.results', [
            'pagetitle' => 'Quiz results — ' . $quiz->title,
            'course'    => $course,
            'quiz'      => $quiz,
            'rows'      => $rows,
        ]);
    }

    // ── validation ────────────────────────────────────────────────────────
    protected function quizRules(Request $request): array
    {
        $v = $request->validate([
            'title'              => 'required|string|max:200',
            'description'        => 'nullable|string',
            'lesson_id'          => 'nullable|integer',
            'pass_mark'          => 'required|integer|min:0|max:100',
            'max_attempts'       => 'required|integer|min:0|max:100',
            'time_limit_minutes' => 'nullable|integer|min:0|max:100000',
            'shuffle'            => 'nullable|boolean',
            'is_published'       => 'nullable|boolean',
        ]);
        $v['shuffle']      = $request->boolean('shuffle');
        $v['is_published'] = $request->boolean('is_published', true);
        return $v;
    }

    protected function questionRules(Request $request): array
    {
        $data = $request->validate([
            'question' => 'required|string',
            'type'     => 'required|in:single,multiple,boolean',
            'points'   => 'required|integer|min:1|max:100',
            'options'  => 'nullable|array',
            'options.*'=> 'nullable|string|max:500',
            'correct'  => 'required|array|min:1',
            'correct.*'=> 'integer|min:0',
        ]);

        if ($data['type'] === 'boolean') {
            $data['options'] = ['True', 'False'];
        } else {
            $data['options'] = array_values(array_filter(
                array_map(fn ($o) => trim((string) $o), $data['options'] ?? []),
                fn ($o) => $o !== ''
            ));
        }

        // keep only correct indexes that point at a real option; single → one
        $max = count($data['options']);
        $data['correct'] = array_values(array_filter(
            array_unique(array_map('intval', $data['correct'])),
            fn ($i) => $i >= 0 && $i < $max
        ));
        if ($data['type'] !== 'multiple') {
            $data['correct'] = array_slice($data['correct'], 0, 1);
        }
        if (empty($data['correct'])) {
            abort(422, 'Select at least one valid correct answer.');
        }
        return $data;
    }
}
