<?php
// app/Services/PromotionEvaluator.php

namespace App\Services;

use App\Models\CompulsorySubjectClass;
use App\Models\Schoolclass;
use App\Models\Schoolterm;

class PromotionEvaluator
{
    /**
     * Promotion result statuses.
     */
    const STATUS_PROMOTED = 'promoted';
    const STATUS_REPEATED = 'repeated';
    const STATUS_AWAITING = 'awaiting';

    /**
     * Grade ordering — higher index = better grade.
     * Covers both junior (A–F) and senior (A1–F9) scales.
     */
    private static array $gradeOrder = [
        // Senior scale (worst → best)
        'F9' => 0, 'E8' => 1, 'D7' => 2,
        'C6' => 3, 'C5' => 4, 'C4' => 5,
        'B3' => 6, 'B2' => 7, 'A1' => 8,
        // Junior scale
        'F'  => 0, 'D'  => 2, 'C'  => 5, 'B'  => 7, 'A'  => 8,
    ];

    /**
     * Evaluate whether a student should be promoted.
     *
     * @param  int         $studentId
     * @param  int         $schoolclassid
     * @param  int         $termid
     * @param  int         $sessionid
     * @param  \Illuminate\Support\Collection  $scores   — the student's broadsheet scores for this term
     * @param  float|null  $overallAverage              — pre-computed overall percentage
     * @return array {
     *   status: 'promoted'|'repeated'|'awaiting',
     *   is_promotional_term: bool,
     *   failed_compulsory: array,   // subjects that caused failure
     *   average_failed: bool,
     *   required_average: float|null,
     *   actual_average: float|null,
     *   compulsory_count: int,
     *   passed_compulsory: int,
     * }
     */
    public function evaluate(
        int    $studentId,
        int    $schoolclassid,
        int    $termid,
        int    $sessionid,
               $scores,
        ?float $overallAverage = null
    ): array {
        // ── 1. Is this the promotional term? ─────────────────────────────────
        $term = Schoolterm::find($termid);

        if (!$term || !$term->is_promotional) {
            return $this->result(self::STATUS_AWAITING, false, [], false, null, $overallAverage, 0, 0);
        }

        // ── 2. Load compulsory subjects for this class ────────────────────────
        // Priority: rules scoped to this exact term+session,
        // then rules scoped to this session only (no term),
        // then global rules (no term, no session).
        $compulsoryRules = CompulsorySubjectClass::where('schoolclassid', $schoolclassid)
            ->where(function ($q) use ($termid, $sessionid) {
                $q->where(function ($q2) use ($termid, $sessionid) {
                    // Exact match
                    $q2->where('termid', $termid)->where('sessionid', $sessionid);
                })->orWhere(function ($q2) use ($sessionid) {
                    // Session-scoped, all terms
                    $q2->whereNull('termid')->where('sessionid', $sessionid);
                })->orWhere(function ($q2) {
                    // Fully global
                    $q2->whereNull('termid')->whereNull('sessionid');
                });
            })
            ->get(['subjectId', 'min_grade']);

        if ($compulsoryRules->isEmpty()) {
            // No compulsory rules defined → promote if average passes (or always if no threshold)
            $averageFailed   = false;
            $requiredAverage = null;

            $schoolclass = Schoolclass::with('classcategories')->find($schoolclassid);
            $category    = $schoolclass?->classcategories->first();

            if ($category && $category->promotion_pass_average !== null && $overallAverage !== null) {
                $requiredAverage = (float) $category->promotion_pass_average;
                $averageFailed   = $overallAverage < $requiredAverage;
            }

            $status = $averageFailed ? self::STATUS_REPEATED : self::STATUS_PROMOTED;
            return $this->result($status, true, [], $averageFailed, $requiredAverage, $overallAverage, 0, 0);
        }

        // ── 3. Build a subject-id → score/grade map from student's scores ─────
        // $scores can be a Collection of objects with subject_id, total, grade
        $scoreMap = collect($scores)->keyBy(fn($s) => is_object($s) ? ($s->subject_id ?? null) : ($s['subject_id'] ?? null));

        // ── 4. Check each compulsory subject ─────────────────────────────────
        $failedCompulsory = [];
        $passedCount      = 0;
        $totalCount       = $compulsoryRules->count();

        foreach ($compulsoryRules as $rule) {
            $subjectId  = $rule->subjectId;
            $minGrade   = $rule->min_grade;
            $scoreEntry = $scoreMap->get($subjectId);

            if (!$scoreEntry) {
                // Subject not sat — automatic fail
                $failedCompulsory[] = [
                    'subject_id'  => $subjectId,
                    'reason'      => 'not_sat',
                    'grade'       => null,
                    'min_grade'   => $minGrade,
                    'total'       => null,
                ];
                continue;
            }

            $studentGrade = is_object($scoreEntry) ? $scoreEntry->grade : ($scoreEntry['grade'] ?? null);
            $studentTotal = is_object($scoreEntry) ? ($scoreEntry->total ?? null) : ($scoreEntry['total'] ?? null);

            if ($this->gradeFails($studentGrade, $minGrade)) {
                $failedCompulsory[] = [
                    'subject_id'  => $subjectId,
                    'subject'     => is_object($scoreEntry) ? ($scoreEntry->subject_name ?? null) : null,
                    'reason'      => 'below_min_grade',
                    'grade'       => $studentGrade,
                    'min_grade'   => $minGrade,
                    'total'       => $studentTotal,
                ];
            } else {
                $passedCount++;
            }
        }

        // ── 5. Check overall average threshold ───────────────────────────────
        $averageFailed   = false;
        $requiredAverage = null;

        $schoolclass = Schoolclass::with('classcategories')->find($schoolclassid);
        $category    = $schoolclass?->classcategories->first();

        if ($category && $category->promotion_pass_average !== null && $overallAverage !== null) {
            $requiredAverage = (float) $category->promotion_pass_average;
            $averageFailed   = $overallAverage < $requiredAverage;
        }

        // ── 6. Determine status ───────────────────────────────────────────────
        $compulsoryFailed = !empty($failedCompulsory);
        $status = ($compulsoryFailed || $averageFailed) ? self::STATUS_REPEATED : self::STATUS_PROMOTED;

        return $this->result($status, true, $failedCompulsory, $averageFailed, $requiredAverage, $overallAverage, $totalCount, $passedCount);
    }

    /**
     * Returns true if the student's grade is below the required minimum.
     * Falls back to checking whether the grade is a fail grade when no min_grade is set.
     */
    private function gradeFails(?string $studentGrade, ?string $minGrade): bool
    {
        if ($studentGrade === null) return true;

        $studentGradeUpper = strtoupper(trim($studentGrade));

        if ($minGrade) {
            $minGradeUpper = strtoupper(trim($minGrade));
            $studentRank   = self::$gradeOrder[$studentGradeUpper] ?? -1;
            $minRank       = self::$gradeOrder[$minGradeUpper]     ?? 0;
            return $studentRank < $minRank;
        }

        // No min_grade set → fail only on F / F9
        return in_array($studentGradeUpper, ['F', 'F9'], true);
    }

    private function result(
        string  $status,
        bool    $isPromotionalTerm,
        array   $failedCompulsory,
        bool    $averageFailed,
        ?float  $requiredAverage,
        ?float  $actualAverage,
        int     $compulsoryCount,
        int     $passedCompulsory
    ): array {
        return [
            'status'              => $status,
            'is_promotional_term' => $isPromotionalTerm,
            'failed_compulsory'   => $failedCompulsory,
            'average_failed'      => $averageFailed,
            'required_average'    => $requiredAverage,
            'actual_average'      => $actualAverage,
            'compulsory_count'    => $compulsoryCount,
            'passed_compulsory'   => $passedCompulsory,
        ];
    }
}
