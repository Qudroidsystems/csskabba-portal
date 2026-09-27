<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LmsQuizQuestion extends Model
{
    protected $table = 'lms_quiz_questions';

    protected $fillable = [
        'quiz_id', 'question', 'type', 'options', 'correct', 'points', 'position',
    ];

    protected $casts = [
        'options' => 'array',
        'correct' => 'array',
    ];

    public function quiz() { return $this->belongsTo(LmsQuiz::class, 'quiz_id'); }

    /** True when the given selected option indexes exactly match the key. */
    public function isCorrect(array $selected): bool
    {
        $key = array_map('intval', (array) ($this->correct ?? []));
        $sel = array_values(array_unique(array_map('intval', $selected)));
        sort($key);
        sort($sel);
        return $key === $sel;
    }
}
