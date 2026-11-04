<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BroadsheetAssessmentScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'broadsheet_id',
        'assessment_id',
        'score',
        'is_absent',   // student was absent for this assessment (score kept at 0)
    ];

    protected $casts = [
        'is_absent' => 'boolean',
    ];

    // =========================================================================
    // ABSENCE ("ABS") HELPERS
    // Staff type ABS (or A / ABSENT) instead of a score when a student missed
    // an assessment. It counts as 0 in every calculation, but is remembered so
    // scoresheets and reports show ABS instead of a plain 0.
    // =========================================================================

    protected static ?bool $absentColumnExists = null;

    /** True when a typed score value means "absent" (ABS / ABSENT / A). */
    public static function isAbsentMark($value): bool
    {
        return is_string($value)
            && in_array(strtoupper(trim($value)), ['ABS', 'ABSENT', 'A'], true);
    }

    /**
     * Absent only while the stored score is still 0, so a real score entered
     * later (e.g. by an import) always wins over a stale ABS flag.
     */
    public function isAbsent(): bool
    {
        return (bool) ($this->is_absent ?? false) && (float) ($this->score ?? 0) == 0.0;
    }

    /** Validation rule for a score: a number 0 or more, or ABS. */
    public static function scoreRule(): array
    {
        return ['required', function ($attribute, $value, $fail) {
            if (self::isAbsentMark($value)) return;
            if (!is_numeric($value) || $value < 0) {
                $fail('Score must be a number, or ABS if the student was absent.');
            }
        }];
    }

    /** Whether the is_absent column exists yet (so saving keeps working before migrating). */
    public static function hasAbsentColumn(): bool
    {
        if (self::$absentColumnExists === null) {
            try {
                self::$absentColumnExists = \Illuminate\Support\Facades\Schema::hasColumn((new static)->getTable(), 'is_absent');
            } catch (\Throwable $e) {
                self::$absentColumnExists = false;
            }
        }
        return self::$absentColumnExists;
    }

    /** Attributes to save for a score, including the absent flag when supported. */
    public static function scoreAttributes($score, bool $absent = false): array
    {
        $attrs = ['score' => $score];
        if (self::hasAbsentColumn()) {
            $attrs['is_absent'] = $absent;
        }
        return $attrs;
    }

    /** SQL condition for "this score has been dealt with" (scored, or marked absent). */
    public static function enteredSql(string $table = 'broadsheet_assessment_scores'): string
    {
        return self::hasAbsentColumn()
            ? "({$table}.score > 0 OR {$table}.is_absent = 1)"
            : "{$table}.score > 0";
    }

    public function broadsheet()
    {
        return $this->belongsTo(Broadsheet::class);
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }
}