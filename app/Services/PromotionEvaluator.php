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

    // Grade grouping maps: grouped grade letter → exact grade codes it covers
    private static array $groupedSenior = [
        'A' => ['A1'],
        'B' => ['B2', 'B3'],
        'C' => ['C4', 'C5', 'C6'],
        'D' => ['D7'],
        'E' => ['E8'],
        'F' => ['F9'],
    ];

    private static array $groupedJunior = [
        'A' => ['A'],
        'B' => ['B'],
        'C' => ['C'],
        'D' => ['D'],
        'F' => ['F'],
    ];

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
        // ── Active settings lookup FIRST (before term gate) ──────────────────
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

        // ── No active setting → gate on is_promotional ────────────────────────
        if (!$settings) {
            $term = Schoolterm::find($termid);
            if (!$term || !$term->is_promotional) {
                return $this->awaitingResult($overallAverage);
            }
            return $this->legacyEvaluate($studentId, $schoolclassid, $termid, $sessionid, $scores, $overallAverage);
        }

        $ruleLogic = $settings->rule_logic ?? 'grade_count';

        // ── Determine if class is senior (for grade grouping) ─────────────────
        $isSenior = $this->classIsSenior($schoolclassid);

        // ── Build score lookup ────────────────────────────────────────────────
        $scoreMap = collect($scores)->keyBy(
            fn($s) => is_object($s) ? ($s->subject_id ?? null) : ($s['subject_id'] ?? null)
        );

        // ── Get compulsory subject IDs for scope filtering ────────────────────
        $compulsoryIds = $this->getCompulsoryIds($schoolclassid, $termid, $sessionid);

        // ── Evaluate each promotion rule (grade_count logic) ──────────────────
        $matchedRule   = null;
        $matchedStatus = null;
        $matchedLabel  = null;

        if (in_array($ruleLogic, ['grade_count', 'both']) && !empty($settings->promotion_rules)) {
            foreach ($settings->promotion_rules as $rule) {
                if (empty($rule['rule_name'])) continue;

                $scope         = $rule['subject_scope']  ?? 'all';
                $gradeGrouping = $rule['grade_grouping'] ?? 'grouped';

                // Filter scores by scope
                $scopedScores = $this->filterScoresByScope($scoreMap, $scope, $compulsoryIds);

                // Count grades in scoped scores
                $gradeCounts = $this->countGrades($scopedScores, $gradeGrouping, $isSenior);

                // Check all grade conditions
                $allMet = true;
                foreach ($rule['grade_conditions'] ?? [] as $cond) {
                    $grade    = strtoupper(trim($cond['grade']));
                    $required = (int) ($cond['count'] ?? 0);
                    $operator = $cond['operator'] ?? '>=';
                    $actual   = $gradeCounts[$grade] ?? 0;

                    if (!$this->compareCount($actual, $operator, $required)) {
                        $allMet = false;
                        break;
                    }
                }

                if ($allMet) {
                    $matchedRule   = $rule;
                    $matchedStatus = $rule['status_label'];
                    $matchedLabel  = $rule['rule_name'];
                    break; // first matching rule wins
                }
            }
        }

        // ── Average condition ─────────────────────────────────────────────────
        $requiredAverage     = $settings->promotion_pass_average
            ?? DB::table('schoolclass_classcategory')->where('schoolclass_id', $schoolclassid)->value('promotion_pass_average');
        $averageConditionMet = true;
        $averageStatus       = null;

        if (in_array($ruleLogic, ['average_only', 'both']) && $requiredAverage !== null && $overallAverage !== null) {
            $averageConditionMet = $overallAverage >= (float) $requiredAverage;
            $averageStatus       = $averageConditionMet ? self::STATUS_PROMOTED : self::STATUS_REPEATED;
        }

        // ── Resolve final status ──────────────────────────────────────────────
        $finalStatus = match ($ruleLogic) {
            'average_only' => $averageStatus ?? self::STATUS_AWAITING,

            'grade_count'  => $matchedStatus
                ?? self::STATUS_REPEATED, // no rule matched = repeat

            'both' => $this->resolveBothLogic(
                $matchedStatus, $averageConditionMet, $matchedRule
            ),

            default => $matchedStatus ?? self::STATUS_REPEATED,
        };

        // If still awaiting (average_only with no average data)
        if ($finalStatus === self::STATUS_AWAITING) {
            return $this->awaitingResult($overallAverage);
        }

        $statusLabel = $this->mapStatusLabel($finalStatus, $settings);

        // ── Legacy compulsory detail (for display in modal) ───────────────────
        [$failedCompulsory, $compulsoryDetail, $passedCount, $totalCount]
            = $this->buildCompulsoryDetail($schoolclassid, $termid, $sessionid, $scoreMap);

        return [
            'status'                    => $finalStatus,
            'status_label'              => $statusLabel,
            'is_promotional_term'       => true,
            'failed_compulsory'         => $failedCompulsory,
            'compulsory_subject_detail' => $compulsoryDetail,
            'average_failed'            => !$averageConditionMet,
            'required_average'          => $requiredAverage,
            'actual_average'            => $overallAverage,
            'compulsory_count'          => $totalCount,
            'passed_compulsory'         => $passedCount,
            'matched_labels'            => [],
            'applied_rule'              => $matchedRule ? [
                'name'        => $matchedLabel,
                'description' => $this->describeRule($matchedRule, $isSenior),
            ] : null,
            'settings_id'               => $settings->id,
            'rule_logic'                => $ruleLogic,
            'settings'                  => [
                'rule_logic'             => $ruleLogic,
                'promotion_pass_average' => $requiredAverage,
            ],
        ];
    }

    // ── Grade counting ────────────────────────────────────────────────────────

    private function filterScoresByScope($scoreMap, string $scope, array $compulsoryIds): \Illuminate\Support\Collection
    {
        return $scoreMap->filter(function ($score, $subjectId) use ($scope, $compulsoryIds) {
            $isComp = in_array((string) $subjectId, array_map('strval', $compulsoryIds));
            return match ($scope) {
                'compulsory_only' => $isComp,
                'other_only'      => !$isComp,
                default           => true,
            };
        });
    }

    private function countGrades($scopedScores, string $grouping, bool $isSenior): array
    {
        $counts  = [];
        $mapping = $isSenior ? self::$groupedSenior : self::$groupedJunior;

        foreach ($scopedScores as $score) {
            $grade = is_object($score) ? ($score->grade ?? null) : ($score['grade'] ?? null);
            if (!$grade) continue;
            $grade = strtoupper(trim($grade));

            if ($grouping === 'grouped') {
                // Map exact grade → group letter
                $groupLetter = $this->exactToGroup($grade, $mapping);
                if ($groupLetter) {
                    $counts[$groupLetter] = ($counts[$groupLetter] ?? 0) + 1;
                }
            } else {
                // exact: count each grade code individually
                $counts[$grade] = ($counts[$grade] ?? 0) + 1;
            }
        }

        return $counts;
    }

    private function exactToGroup(string $exactGrade, array $mapping): ?string
    {
        foreach ($mapping as $letter => $codes) {
            if (in_array($exactGrade, $codes, true)) return $letter;
        }
        return null;
    }

    private function compareCount(int $actual, string $operator, int $required): bool
    {
        return match ($operator) {
            '>=' => $actual >= $required,
            '<=' => $actual <= $required,
            '='  => $actual === $required,
            '>'  => $actual > $required,
            '<'  => $actual < $required,
            default => false,
        };
    }

    // ── Both logic (grade count AND average) ─────────────────────────────────
    private function resolveBothLogic(?string $gradeStatus, bool $avgOk, ?array $matchedRule): string
    {
        $avgLogic = $matchedRule['average_condition']['logic'] ?? 'AND';

        if ($avgLogic === 'OR') {
            return ($gradeStatus !== null || $avgOk)
                ? ($gradeStatus ?? self::STATUS_PROMOTED)
                : self::STATUS_REPEATED;
        }

        // AND: both must pass
        if ($gradeStatus !== null && $avgOk) return $gradeStatus;
        if ($gradeStatus !== null && !$avgOk) return self::STATUS_TRIAL;
        if ($gradeStatus === null && $avgOk)  return self::STATUS_SEE_PRINCIPAL;
        return self::STATUS_REPEATED;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function classIsSenior(int $schoolclassid): bool
    {
        $row = DB::table('schoolclass_classcategory')
            ->join('classcategories', 'classcategories.id', '=', 'schoolclass_classcategory.classcategory_id')
            ->where('schoolclass_classcategory.schoolclass_id', $schoolclassid)
            ->select('classcategories.is_senior')
            ->first();
        return $row ? (bool) $row->is_senior : true;
    }

    private function getCompulsoryIds(int $schoolclassid, int $termid, int $sessionid): array
    {
        return CompulsorySubjectClass::where('schoolclassid', $schoolclassid)
            ->where(function ($q) use ($termid, $sessionid) {
                $q->where(function ($q2) use ($termid, $sessionid) {
                    $q2->where('termid', $termid)->where('sessionid', $sessionid);
                })->orWhere(function ($q2) use ($sessionid) {
                    $q2->whereNull('termid')->where('sessionid', $sessionid);
                })->orWhere(function ($q2) {
                    $q2->whereNull('termid')->whereNull('sessionid');
                });
            })
            ->pluck('subjectId')
            ->map(fn($id) => (string) $id)
            ->toArray();
    }

    private function buildCompulsoryDetail(int $schoolclassid, int $termid, int $sessionid, $scoreMap): array
    {
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

        $failed  = [];
        $detail  = [];
        $passed  = 0;
        $total   = $compulsoryRules->count();

        foreach ($compulsoryRules as $rule) {
            $subjectId    = $rule->subjectId;
            $scoreEntry   = $scoreMap->get($subjectId);
            $studentGrade = is_object($scoreEntry) ? ($scoreEntry->grade ?? null) : ($scoreEntry['grade'] ?? null);
            $subjectName  = is_object($scoreEntry) ? ($scoreEntry->subject_name ?? null) : null;
            $minGrade     = $rule->min_grade;
            $didPass      = $scoreEntry && !$this->gradeFails($studentGrade, $minGrade);

            if (!$didPass) {
                $failed[] = ['subject_id' => $subjectId, 'subject' => $subjectName,
                             'grade' => $studentGrade, 'min_grade' => $minGrade, 'not_sat' => !$scoreEntry];
            } else {
                $passed++;
            }

            $detail[] = ['subject_id' => $subjectId, 'subject' => $subjectName,
                         'grade' => $studentGrade, 'min_grade' => $minGrade,
                         'passed' => $didPass, 'not_sat' => !$scoreEntry];
        }

        return [$failed, $detail, $passed, $total];
    }

    private function mapStatusLabel(string $status, $settings): string
    {
        return match ($status) {
            self::STATUS_PROMOTED      => $settings->promoted_label      ?? 'Promoted',
            self::STATUS_TRIAL         => $settings->trial_label         ?? 'Promoted on Trial',
            self::STATUS_SEE_PRINCIPAL => $settings->see_principal_label ?? 'Advised to See Principal',
            self::STATUS_REPEATED      => $settings->repeat_label        ?? 'Advice to Repeat',
            default                    => 'Awaiting Decision',
        };
    }

    private function describeRule(array $rule, bool $isSenior): string
    {
        $scope   = match ($rule['subject_scope'] ?? 'all') {
            'compulsory_only' => 'Compulsory subjects',
            'other_only'      => 'Other subjects',
            default           => 'All subjects',
        };
        $grouping = ($rule['grade_grouping'] ?? 'grouped') === 'grouped' ? 'grouped grades' : 'exact grades';
        $conds    = [];
        foreach ($rule['grade_conditions'] ?? [] as $cond) {
            $conds[] = "{$cond['operator']} {$cond['count']} {$cond['grade']}";
        }
        $condStr = implode(', ', $conds);
        $avgStr  = '';
        if (!empty($rule['average_condition']['enabled']) && $rule['average_condition']['enabled']) {
            $avgStr = " | Avg {$rule['average_condition']['logic']}: ≥{$rule['average_condition']['min_average']}%";
        }
        return "{$scope} ({$grouping}): {$condStr}{$avgStr}";
    }

    private function awaitingResult(?float $overallAverage): array
    {
        return [
            'status'                    => self::STATUS_AWAITING,
            'status_label'              => 'Awaiting Decision',
            'is_promotional_term'       => false,
            'failed_compulsory'         => [],
            'compulsory_subject_detail' => [],
            'average_failed'            => false,
            'required_average'          => null,
            'actual_average'            => $overallAverage,
            'compulsory_count'          => 0,
            'passed_compulsory'         => 0,
            'matched_labels'            => [],
            'applied_rule'              => null,
            'settings_id'               => null,
            'rule_logic'                => null,
            'settings'                  => null,
        ];
    }

    // ── Legacy evaluation (no PromotionSetting) ───────────────────────────────
    private function legacyEvaluate(int $studentId, int $schoolclassid, int $termid, int $sessionid, $scores, ?float $overallAverage): array
    {
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

        $passAverage  = DB::table('schoolclass_classcategory')->where('schoolclass_id', $schoolclassid)->value('promotion_pass_average');
        $reqAvg       = $passAverage !== null ? (float) $passAverage : null;
        $avgFailed    = $reqAvg !== null && $overallAverage !== null && $overallAverage < $reqAvg;

        $scoreMap = collect($scores)->keyBy(fn($s) => is_object($s) ? ($s->subject_id ?? null) : ($s['subject_id'] ?? null));
        $failed   = [];
        $detail   = [];
        $passed   = 0;
        $total    = $compulsoryRules->count();

        foreach ($compulsoryRules as $rule) {
            $subjectId    = $rule->subjectId;
            $scoreEntry   = $scoreMap->get($subjectId);
            $studentGrade = is_object($scoreEntry) ? ($scoreEntry->grade ?? null) : ($scoreEntry['grade'] ?? null);
            $subjectName  = is_object($scoreEntry) ? ($scoreEntry->subject_name ?? null) : null;
            $didPass      = $scoreEntry && !$this->gradeFails($studentGrade, $rule->min_grade);

            if (!$didPass) {
                $failed[] = ['subject_id' => $subjectId, 'subject' => $subjectName,
                             'grade' => $studentGrade, 'min_grade' => $rule->min_grade, 'not_sat' => !$scoreEntry];
            } else {
                $passed++;
            }
            $detail[] = ['subject_id' => $subjectId, 'subject' => $subjectName,
                         'grade' => $studentGrade, 'min_grade' => $rule->min_grade,
                         'passed' => $didPass, 'not_sat' => !$scoreEntry];
        }

        $status = (!empty($failed) || $avgFailed) ? self::STATUS_REPEATED : self::STATUS_PROMOTED;

        return [
            'status'                    => $status,
            'status_label'              => $status === self::STATUS_PROMOTED ? 'Promoted' : 'Advice to Repeat',
            'is_promotional_term'       => true,
            'failed_compulsory'         => $failed,
            'compulsory_subject_detail' => $detail,
            'average_failed'            => $avgFailed,
            'required_average'          => $reqAvg,
            'actual_average'            => $overallAverage,
            'compulsory_count'          => $total,
            'passed_compulsory'         => $passed,
            'matched_labels'            => [],
            'applied_rule'              => null,
            'settings_id'               => null,
            'rule_logic'                => 'legacy',
            'settings'                  => null,
        ];
    }

    private function gradeFails(?string $studentGrade, ?string $minGrade): bool
    {
        if ($studentGrade === null) return true;
        $sg = strtoupper(trim($studentGrade));
        if ($minGrade) {
            $mg = strtoupper(trim($minGrade));
            return (self::$gradeOrder[$sg] ?? -1) < (self::$gradeOrder[$mg] ?? 0);
        }
        return in_array($sg, ['F', 'F9'], true);
    }

    public function getStatusBadgeClass(string $status): string
    {
        return match ($status) {
            self::STATUS_PROMOTED      => 'bg-success',
            self::STATUS_TRIAL         => 'bg-warning',
            self::STATUS_SEE_PRINCIPAL => 'bg-info',
            self::STATUS_REPEATED      => 'bg-danger',
            default                    => 'bg-secondary',
        };
    }

    public function getStatusIcon(string $status): string
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
