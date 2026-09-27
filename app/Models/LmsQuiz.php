<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LmsQuiz extends Model
{
    protected $table = 'lms_quizzes';

    protected $fillable = [
        'course_id', 'lesson_id', 'title', 'description', 'pass_mark',
        'max_attempts', 'time_limit_minutes', 'shuffle', 'is_published',
    ];

    protected $casts = [
        'shuffle'      => 'boolean',
        'is_published' => 'boolean',
    ];

    public function course()   { return $this->belongsTo(LmsCourse::class, 'course_id'); }
    public function lesson()   { return $this->belongsTo(LmsLesson::class, 'lesson_id'); }
    public function questions(){ return $this->hasMany(LmsQuizQuestion::class, 'quiz_id')->orderBy('position'); }
    public function attempts() { return $this->hasMany(LmsQuizAttempt::class, 'quiz_id'); }

    public function totalPoints(): int
    {
        return (int) $this->questions()->sum('points');
    }

    public function attemptsUsed(?int $studentId): int
    {
        if (!$studentId) return 0;
        return (int) $this->attempts()->where('student_id', $studentId)->count();
    }

    public function attemptsLeft(?int $studentId): ?int
    {
        if ($this->max_attempts <= 0) return null; // unlimited
        return max(0, $this->max_attempts - $this->attemptsUsed($studentId));
    }

    public function bestAttempt(?int $studentId): ?LmsQuizAttempt
    {
        if (!$studentId) return null;
        return $this->attempts()->where('student_id', $studentId)->orderByDesc('percent')->first();
    }
}
