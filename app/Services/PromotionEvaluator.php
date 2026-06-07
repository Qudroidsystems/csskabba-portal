<?php
// app/Services/PromotionEvaluator.php

namespace App\Services;

use App\Models\CompulsorySubjectClass;
use App\Models\PromotionSetting;
use App\Models\Schoolterm;
use Illuminate\Support\Facades\DB;

class PromotionEvaluator
{
    const STATUS_PROMOTED      = 'promoted';
    const STATUS_TRIAL         = 'trial';
    const STATUS_SEE_PRINCIPAL = 'see_principal';
    const STATUS_REPEATED      = 'repeated';
    const STATUS_AWAITING      = 'awaiting';

    private static array $gradeOrder = [
        'F9' => 0, 'E8' => 1, 'D7' => 2,
        'C6' => 3, 'C5' => 4, 'C4' => 5,
        'B3' => 6, 'B2' => 7, 'A1' => 8,
        'F'  => 0, 'D'  => 2, 'C'  => 5,
        'B'  => 7, 'A'  => 8,
    ];

    public function evaluate(
        int    $studentId,
        int    $schoolclassid,
        int    $termid,
        int    $sessionid,
               $scores,
        ?float $overallAverage = null
    ): array {
        $term = Schoolterm::find($termid);

        if (!$term || !$term->is_promotional) {
            return $this->result(
                self::STATUS_AWAITING, false, [], false,
                null, $overallAverage, 0, 0, null,
                'Awaiting Decision', []
            );
        }

        $settings = PromotionSetting::where('schoolclass_id', $schoolclassid)
            ->where(function ($q) use ($sessionid, $termid) {
                $q->where(function ($q2) use ($sessionid, $termid) {
                    $q2->where('session_id', $sessionid)->where('term_id', $termid);
                })->orWhere(function ($q2) use ($sessionid) {
                    $q2->where('session_id', $sessionid)->whereNull('term_id');
                })->orWhere(function ($q2) {
                    $q2->whereNull('session_id')->whereNull('term_id');
                });
            })
            ->where('is_active', true)
            ->first();

        if (!$settings) {
            return $this->legacyEvaluate(
                $studentId, $schoolclassid, $termid, $sessionid, $scores, $overallAverage
            );
        }

        // ── Fetch compulsory subject rules scoped to this term/session ──────────
        $compulsoryRules = CompulsorySubjectClass::where('schoolclassid', $schoolclassid)
            ->where(function ($q) use ($termid, $sessionid) {
                $q->where(function ($q2) use ($termid, $sessionid) {
                    $q2->where('termid', $termid)->where('sessionid', $sessionid);
                })->orWhere(function ($q2) use ($sessionid) {
                    $q2->whereNull('termid')->where('sessionid', $sessionid);
                })->orWhere(function ($q2) {
                    $q2->whereNull('termid')->whereNull('sessionid');
                });
            })
            ->get(['subjectId', 'min_grade']);

        $scoreMap = collect($scores)->keyBy(
            fn($s) => is_object($s) ? ($s->subject_id ?? null) : ($s['subject_id'] ?? null)
        );

        // ── Evaluate each compulsory subject ─────────────────────────────────────
        $passedCount            = 0;
        $totalCount             = $compulsoryRules->count();
        $failedCompulsoryList   = [];
        $compulsorySubjectDetail = [];

        foreach ($compulsoryRules as $rule) {
            $subjectId   = $rule->subjectId;
            $minGrade    = $rule->min_grade;
            $scoreEntry  = $scoreMap->get($subjectId);

            $studentGrade = is_object($scoreEntry) ? ($scoreEntry->grade ?? null)
                                                    : ($scoreEntry['grade'] ?? null);
            $subjectName  = is_object($scoreEntry) ? ($scoreEntry->subject_name ?? null) : null;

            $passed = $scoreEntry && !$this->gradeFails($studentGrade, $minGrade);

            if ($passed) {
                $passedCount++;
            } else {
                $failedCompulsoryList[] = [
                    'subject_id'   => $subjectId,
                    'subject'      => $subjectName,
                    'grade'        => $studentGrade,
                    'min_grade'    => $minGrade,
                    'not_sat'      => !$scoreEntry,
                ];
            }

            $compulsorySubjectDetail[] = [
                'subject_id'   => $subjectId,
                'subject'      => $subjectName,
                'grade'        => $studentGrade,
                'min_grade'    => $minGrade,
                'passed'       => $passed,
                'not_sat'      => !$scoreEntry,
            ];
        }

        // ── Evaluate conditional label rules ─────────────────────────────────────
        $matchedLabels = $this->evaluateConditionalRules(
            $settings, $scoreMap, $failedCompulsoryList
        );

        // ── Average evaluation ────────────────────────────────────────────────────
        $averageConditionMet = true;
        $determinedStatus    = null;

        if (in_array($settings->rule_type, ['average_only', 'both'])) {
            if ($overallAverage !== null) {
                if ($settings->promotion_pass_average !== null
                    && $overallAverage >= $settings->promotion_pass_average) {
                    $determinedStatus    = self::STATUS_PROMOTED;
                    $averageConditionMet = true;
                } elseif ($settings->trial_pass_average !== null
                    && $overallAverage >= $settings->trial_pass_average) {
                    $determinedStatus    = self::STATUS_TRIAL;
                    $averageConditionMet = true;
                } elseif ($settings->see_principal_average !== null
                    && $overallAverage >= $settings->see_principal_average) {
                    $determinedStatus    = self::STATUS_SEE_PRINCIPAL;
                    $averageConditionMet = false;
                } else {
                    $determinedStatus    = self::STATUS_REPEATED;
                    $averageConditionMet = false;
                }
            }
        }

        // ── All compulsory subjects must be passed (no partial threshold) ─────────
        $compulsoryConditionMet = $totalCount === 0 || $passedCount === $totalCount;

        // ── Determine final status ────────────────────────────────────────────────
        $finalStatus = match ($settings->rule_type) {
            'compulsory_only' => $compulsoryConditionMet
                ? self::STATUS_PROMOTED
                : ($settings->compulsory_fail_action ?? self::STATUS_REPEATED),

            'average_only'    => $determinedStatus ?? self::STATUS_REPEATED,

            default           => $this->resolveCombinedStatus(
                $settings, $compulsoryConditionMet, $averageConditionMet, $determinedStatus
            ),
        };

        $statusLabel = match ($finalStatus) {
            self::STATUS_PROMOTED      => $settings->promoted_label      ?? 'Promoted',
            self::STATUS_TRIAL         => $settings->trial_label         ?? 'Promoted on Trial',
            self::STATUS_SEE_PRINCIPAL => $settings->see_principal_label ?? 'Advised to See Principal',
            self::STATUS_REPEATED      => $settings->repeat_label        ?? 'Advice to Repeat',
            default                    => 'Awaiting Decision',
        };

        return $this->result(
            $finalStatus,
            true,
            $failedCompulsoryList,
            !$averageConditionMet,
            $settings->promotion_pass_average,
            $overallAverage,
            $totalCount,
            $passedCount,
            $settings,
            $statusLabel,
            $matchedLabels,
            $compulsorySubjectDetail
        );
    }

    // ── Resolve combined (AND/OR) logic ─────────────────────────────────────────
    private function resolveCombinedStatus($settings, bool $compOk, bool $avgOk, ?string $avgStatus): string
    {
        if ($settings->combined_logic === 'and') {
            if ($compOk && $avgOk)    return self::STATUS_PROMOTED;
            if (!$compOk && !$avgOk)  return self::STATUS_REPEATED;
            if (!$compOk)             return $settings->compulsory_fail_action ?? self::STATUS_REPEATED;
            return $avgStatus ?? self::STATUS_REPEATED;
        }
        // OR logic
        return ($compOk || $avgOk) ? self::STATUS_PROMOTED : self::STATUS_REPEATED;
    }

    // ── Evaluate admin-defined conditional label rules ───────────────────────────
    private function evaluateConditionalRules($settings, $scoreMap, array $failedCompulsory): array
    {
        $promotionRules = $settings->promotion_rules ?? [];
        if (empty($promotionRules) || !is_array($promotionRules)) {
            return [];
        }

        $failedSubjectIds = array_column($failedCompulsory, 'subject_id');
        $matchedLabels    = [];

        foreach ($promotionRules as $rule) {
            if (empty($rule['label'])) continue;

            $condType = $rule['condition_type'] ?? 'compulsory_fail';

            // Condition 1: student must have failed ALL listed compulsory subjects
            $failedSubjectCondition = empty($rule['failed_subject_ids'])
                || count(array_intersect($rule['failed_subject_ids'], $failedSubjectIds))
                    === count($rule['failed_subject_ids']);

            if (!$failedSubjectCondition) continue;

            // Condition 2: other subjects must meet their grade conditions
            $otherConditionMet = true;
            if (!empty($rule['other_subject_conditions'])) {
                foreach ($rule['other_subject_conditions'] as $cond) {
                    $sid        = $cond['subject_id'] ?? null;
                    $minGrade   = $cond['min_grade']  ?? null;
                    $scoreEntry = $sid ? $scoreMap->get($sid) : null;

                    if (!$scoreEntry) {
                        $otherConditionMet = false;
                        break;
                    }

                    $studentGrade = is_object($scoreEntry)
                        ? ($scoreEntry->grade ?? null)
                        : ($scoreEntry['grade'] ?? null);

                    if ($this->gradeFails($studentGrade, $minGrade)) {
                        $otherConditionMet = false;
                        break;
                    }
                }
            }

            if ($otherConditionMet) {
                $matchedLabels[] = [
                    'label'       => $rule['label'],
                    'description' => $rule['description'] ?? null,
                    'color'       => $rule['color']       ?? 'warning',
                ];
            }
        }

        return $matchedLabels;
    }

    // ── Legacy evaluation (no PromotionSetting record) ───────────────────────────
    private function legacyEvaluate(
        int    $studentId,
        int    $schoolclassid,
        int    $termid,
        int    $sessionid,
               $scores,
        ?float $overallAverage = null
    ): array {
        $compulsoryRules = CompulsorySubjectClass::where('schoolclassid', $schoolclassid)
            ->where(function ($q) use ($termid, $sessionid) {
                $q->where(function ($q2) use ($termid, $sessionid) {
                    $q2->where('termid', $termid)->where('sessionid', $sessionid);
                })->orWhere(function ($q2) use ($sessionid) {
                    $q2->whereNull('termid')->where('sessionid', $sessionid);
                })->orWhere(function ($q2) {
                    $q2->whereNull('termid')->whereNull('sessionid');
                });
            })
            ->get(['subjectId', 'min_grade']);

        $passAverage   = DB::table('schoolclass_classcategory')
            ->where('schoolclass_id', $schoolclassid)
            ->value('promotion_pass_average');

        $requiredAverage = null;
        $averageFailed   = false;

        if ($passAverage !== null && $overallAverage !== null) {
            $requiredAverage = (float) $passAverage;
            $averageFailed   = $overallAverage < $requiredAverage;
        }

        if ($compulsoryRules->isEmpty()) {
            $status = $averageFailed ? self::STATUS_REPEATED : self::STATUS_PROMOTED;
            $label  = $status === self::STATUS_PROMOTED ? 'Promoted' : 'Advice to Repeat';
            return $this->result($status, true, [], $averageFailed, $requiredAverage, $overallAverage, 0, 0, null, $label, []);
        }

        $scoreMap              = collect($scores)->keyBy(
            fn($s) => is_object($s) ? ($s->subject_id ?? null) : ($s['subject_id'] ?? null)
        );
        $failedCompulsory      = [];
        $compulsorySubjectDetail = [];
        $passedCount           = 0;
        $totalCount            = $compulsoryRules->count();

        foreach ($compulsoryRules as $rule) {
            $subjectId   = $rule->subjectId;
            $minGrade    = $rule->min_grade;
            $scoreEntry  = $scoreMap->get($subjectId);
            $studentGrade = is_object($scoreEntry) ? ($scoreEntry->grade ?? null)
                                                    : ($scoreEntry['grade'] ?? null);

            $passed = $scoreEntry && !$this->gradeFails($studentGrade, $minGrade);

            if (!$passed) {
                $failedCompulsory[] = [
                    'subject_id' => $subjectId,
                    'grade'      => $studentGrade,
                    'min_grade'  => $minGrade,
                    'not_sat'    => !$scoreEntry,
                ];
            } else {
                $passedCount++;
            }

            $compulsorySubjectDetail[] = [
                'subject_id' => $subjectId,
                'subject'    => is_object($scoreEntry) ? ($scoreEntry->subject_name ?? null) : null,
                'grade'      => $studentGrade,
                'min_grade'  => $minGrade,
                'passed'     => $passed,
                'not_sat'    => !$scoreEntry,
            ];
        }

        $status = (!empty($failedCompulsory) || $averageFailed)
            ? self::STATUS_REPEATED
            : self::STATUS_PROMOTED;
        $label  = $status === self::STATUS_PROMOTED ? 'Promoted' : 'Advice to Repeat';

        return $this->result(
            $status, true, $failedCompulsory, $averageFailed,
            $requiredAverage, $overallAverage, $totalCount, $passedCount,
            null, $label, [], $compulsorySubjectDetail
        );
    }

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
        int     $passedCompulsory,
        ?object $settings,
        ?string $statusLabel = null,
        array   $matchedLabels = [],
        array   $compulsorySubjectDetail = []
    ): array {
        return [
            'status'                    => $status,
            'status_label'              => $statusLabel ?? ucfirst(str_replace('_', ' ', $status)),
            'is_promotional_term'       => $isPromotionalTerm,
            'failed_compulsory'         => $failedCompulsory,
            'compulsory_subject_detail' => $compulsorySubjectDetail,
            'average_failed'            => $averageFailed,
            'required_average'          => $requiredAverage,
            'actual_average'            => $actualAverage,
            'compulsory_count'          => $compulsoryCount,
            'passed_compulsory'         => $passedCompulsory,
            'matched_labels'            => $matchedLabels,
            'settings'                  => $settings ? [
                'rule_type'               => $settings->rule_type,
                'promotion_pass_average'  => $settings->promotion_pass_average,
                'trial_pass_average'      => $settings->trial_pass_average,
                'combined_logic'          => $settings->combined_logic,
            ] : null,
        ];
    }

    public function getStatusBadgeClass($status): string
    {
        return match ($status) {
            self::STATUS_PROMOTED      => 'bg-success',
            self::STATUS_TRIAL         => 'bg-warning',
            self::STATUS_SEE_PRINCIPAL => 'bg-info',
            self::STATUS_REPEATED      => 'bg-danger',
            default                    => 'bg-secondary',
        };
    }

    public function getStatusIcon($status): string
    {
        return match ($status) {
            self::STATUS_PROMOTED      => 'ri-checkbox-circle-line',
            self::STATUS_TRIAL         => 'ri-time-line',
            self::STATUS_SEE_PRINCIPAL => 'ri-eye-line',
            self::STATUS_REPEATED      => 'ri-repeat-line',
            default                    => 'ri-question-line',
        };
    }
}
