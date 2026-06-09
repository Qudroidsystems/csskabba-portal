<?php

namespace App\Services;

use App\Models\CompulsorySubjectClass;
use App\Models\PromotionSetting;
use App\Models\Schoolterm;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PromotionEvaluator
{
    const STATUS_PROMOTED      = 'promoted';
    const STATUS_TRIAL         = 'trial';
    const STATUS_SEE_PRINCIPAL = 'see_principal';
    const STATUS_REPEATED      = 'repeated';
    const STATUS_AWAITING      = 'awaiting';

    // Grouped-grade maps: letter → exact codes it covers
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

    // =========================================================================
    // PUBLIC ENTRY POINT
    // =========================================================================

    public function evaluate(
        int    $studentId,
        int    $schoolclassid,
        int    $termid,
        int    $sessionid,
               $scores,
        ?float $overallAverage = null
    ): array {
        // ------------------------------------------------------------------
        // 1. Find the best-matching active PromotionSetting for this class.
        // ------------------------------------------------------------------
        $settings = $this->findBestSettings($schoolclassid, $sessionid, $termid);

        // No active setting → gate on is_promotional term, then legacy path
        if (!$settings) {
            $term = Schoolterm::find($termid);
            if (!$term || !$term->is_promotional) {
                return $this->awaitingResult($overallAverage);
            }
            return $this->legacyEvaluate(
                $studentId, $schoolclassid, $termid, $sessionid, $scores, $overallAverage
            );
        }

        // ------------------------------------------------------------------
        // 2. Build helpers
        // ------------------------------------------------------------------
        $isSenior      = $this->classIsSenior($schoolclassid);
        $scoreMap      = $this->buildScoreMap($scores);
        $compulsoryIds = $this->getCompulsoryIds($schoolclassid, $termid, $sessionid);
        $ruleLogic     = $settings->rule_logic ?? 'grade_count';

        // ------------------------------------------------------------------
        // 3. Walk rules in priority order until one fully matches
        // ------------------------------------------------------------------
        $matchedRule   = null;
        $matchedStatus = null;
        $matchedIndex  = null;

        $rules = $settings->promotion_rules ?? [];

        if (in_array($ruleLogic, ['grade_count', 'both']) && !empty($rules)) {
            foreach ($rules as $idx => $rule) {
                if (empty($rule['rule_name'])) continue;

                if ($this->ruleMatches($rule, $scoreMap, $compulsoryIds, $isSenior, $overallAverage)) {
                    $matchedRule   = $rule;
                    $matchedStatus = $rule['status_label'] ?? self::STATUS_PROMOTED;
                    $matchedIndex  = $idx;
                    break; // first match wins
                }
            }
        }

        // ------------------------------------------------------------------
        // 4. Evaluate global average condition
        // ------------------------------------------------------------------
        $requiredAverage = $this->resolveRequiredAverage($settings, $schoolclassid);
        [$averageConditionMet, $averageStatus] = $this->evaluateAverage(
            $ruleLogic, $requiredAverage, $overallAverage
        );

        // ------------------------------------------------------------------
        // 5. Resolve final status
        // ------------------------------------------------------------------
        $finalStatus = $this->resolveFinalStatus(
            $ruleLogic, $matchedStatus, $matchedRule, $averageConditionMet, $averageStatus
        );

        if ($finalStatus === self::STATUS_AWAITING) {
            return $this->awaitingResult($overallAverage);
        }

        // ------------------------------------------------------------------
        // 6. Build compulsory subject detail
        // ------------------------------------------------------------------
        [$failedCompulsory, $compulsoryDetail, $passedCount, $totalCount]
            = $this->buildCompulsoryDetail($schoolclassid, $termid, $sessionid, $scoreMap);

        // ------------------------------------------------------------------
        // 7. Build applied-rule summary
        // ------------------------------------------------------------------
        $appliedRuleSummary = null;
        if ($matchedRule !== null) {
            $appliedRuleSummary = [
                'name'        => $matchedRule['rule_name'],
                'description' => $this->describeRule($matchedRule, $isSenior),
                'index'       => $matchedIndex + 1,
            ];
        }

        return [
            'status'                    => $finalStatus,
            'status_label'              => $this->mapStatusLabel($finalStatus, $settings),
            'is_promotional_term'       => true,
            'failed_compulsory'         => $failedCompulsory,
            'compulsory_subject_detail' => $compulsoryDetail,
            'average_failed'            => !$averageConditionMet,
            'required_average'          => $requiredAverage,
            'actual_average'            => $overallAverage,
            'compulsory_count'          => $totalCount,
            'passed_compulsory'         => $passedCount,
            'matched_labels'            => [],
            'applied_rule'              => $appliedRuleSummary,
            'settings_id'               => $settings->id,
            'rule_logic'                => $ruleLogic,
            'settings'                  => [
                'rule_logic'             => $ruleLogic,
                'promotion_pass_average' => $requiredAverage,
            ],
        ];
    }

    // =========================================================================
    // SETTINGS LOOKUP
    // =========================================================================

    private function findBestSettings(int $schoolclassid, int $sessionid, int $termid): ?PromotionSetting
    {
        $candidates = PromotionSetting::where('schoolclass_id', $schoolclassid)
            ->where('is_active', true)
            ->where(function ($q) use ($sessionid, $termid) {
                $q->where(function ($q2) use ($sessionid, $termid) {
                    $q2->where('session_id', $sessionid)->where('term_id', $termid);
                })->orWhere(function ($q2) use ($sessionid) {
                    $q2->where('session_id', $sessionid)->whereNull('term_id');
                })->orWhere(function ($q2) {
                    $q2->whereNull('session_id')->whereNull('term_id');
                });
            })
            ->orderBy('priority', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        if ($candidates->isEmpty()) return null;

        $best = null;
        $bestScore = 0;

        foreach ($candidates as $candidate) {
            $score = 1;
            if ($candidate->session_id == $sessionid) $score = 2;
            if ($candidate->session_id == $sessionid && $candidate->term_id == $termid) $score = 3;

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $candidate;
            }
        }

        return $best;
    }

    // =========================================================================
    // RULE MATCHING - FIXED VERSION
    // =========================================================================

    private function ruleMatches(
        array      $rule,
        Collection $scoreMap,
        array      $compulsoryIds,
        bool       $isSenior,
        ?float     $overallAverage = null
    ): bool {
        $grouping = $rule['grade_grouping'] ?? 'grouped';

        // ------------------------------------------------------------------
        // Section 1: Per-subject minimum grade for compulsory subjects
        // ------------------------------------------------------------------
        $compSubjects = $rule['compulsory_section']['subjects'] ?? [];
        foreach ($compSubjects as $subjectRule) {
            $minGrade = $subjectRule['min_grade'] ?? null;

            // Skip if no minimum grade set for this subject
            if (empty($minGrade)) continue;

            $subjectId   = $subjectRule['subject_id'] ?? null;
            $scoreEntry  = $subjectId ? $scoreMap->get($subjectId) : null;
            $studentGrade = $scoreEntry
                ? (is_object($scoreEntry) ? ($scoreEntry->grade ?? null) : ($scoreEntry['grade'] ?? null))
                : null;

            if ($this->gradeFails($studentGrade, $minGrade)) {
                return false;
            }
        }

        // ------------------------------------------------------------------
        // Section 2: Count conditions on compulsory subjects
        // ------------------------------------------------------------------
        $compCountConditions = $rule['compulsory_section']['count_conditions'] ?? [];
        if (!$this->evaluateCountConditions(
            $compCountConditions, $scoreMap, $compulsoryIds, $grouping, $isSenior
        )) {
            return false;
        }

        // ------------------------------------------------------------------
        // Section 3: Count conditions on other/all subjects
        // ------------------------------------------------------------------
        $otherCountConditions = $rule['other_section']['count_conditions'] ?? [];
        if (!$this->evaluateCountConditions(
            $otherCountConditions, $scoreMap, $compulsoryIds, $grouping, $isSenior
        )) {
            return false;
        }

        // ------------------------------------------------------------------
        // Section 4: Per-rule average condition
        // ------------------------------------------------------------------
        $avgCond = $rule['average_condition'] ?? [];
        if (!empty($avgCond['enabled']) && $overallAverage !== null) {
            $minAvg = $avgCond['min_average'] ?? 0;
            $logic = strtoupper($avgCond['logic'] ?? 'AND');

            if ($logic === 'AND') {
                if ($overallAverage < $minAvg) {
                    return false;
                }
            }
            // For OR logic, average alone qualifies, but we don't filter here
        }

        return true;
    }

    private function evaluateCountConditions(
        array      $conditions,
        Collection $scoreMap,
        array      $compulsoryIds,
        string     $grouping,
        bool       $isSenior
    ): bool {
        if (empty($conditions)) return true;

        foreach ($conditions as $cond) {
            $grade    = strtoupper(trim($cond['grade'] ?? ''));
            $operator = $cond['operator'] ?? '>=';
            $required = (int) ($cond['count'] ?? 0);
            $scope    = $cond['scope'] ?? 'all';

            if (!$grade) continue;

            $scopedScores = $this->filterByScope($scoreMap, $scope, $compulsoryIds);
            $actual = $this->countMatchingGrade($scopedScores, $grade, $grouping, $isSenior);

            if (!$this->compareCount($actual, $operator, $required)) {
                return false;
            }
        }

        return true;
    }

    // =========================================================================
    // AVERAGE HANDLING
    // =========================================================================

    private function resolveRequiredAverage(PromotionSetting $settings, int $schoolclassid): ?float
    {
        if ($settings->promotion_pass_average !== null) {
            return (float) $settings->promotion_pass_average;
        }
        $val = DB::table('schoolclass_classcategory')
            ->where('schoolclass_id', $schoolclassid)
            ->value('promotion_pass_average');
        return $val !== null ? (float) $val : null;
    }

    private function evaluateAverage(
        string $ruleLogic,
        ?float $requiredAverage,
        ?float $overallAverage
    ): array {
        if (!in_array($ruleLogic, ['average_only', 'both'])) {
            return [true, null];
        }
        if ($requiredAverage === null || $overallAverage === null) {
            return [true, null];
        }
        $met = $overallAverage >= $requiredAverage;
        return [$met, $met ? self::STATUS_PROMOTED : self::STATUS_REPEATED];
    }

    // =========================================================================
    // FINAL STATUS RESOLUTION
    // =========================================================================

    private function resolveFinalStatus(
        string  $ruleLogic,
        ?string $matchedStatus,
        ?array  $matchedRule,
        bool    $averageConditionMet,
        ?string $averageStatus
    ): string {
        switch ($ruleLogic) {
            case 'average_only':
                return $averageStatus ?? self::STATUS_AWAITING;

            case 'grade_count':
                if ($matchedStatus === null) {
                    return self::STATUS_REPEATED;
                }
                return $this->applyRuleAverageCondition($matchedStatus, $matchedRule, $averageConditionMet);

            case 'both':
                return $this->resolveBothLogic($matchedStatus, $matchedRule, $averageConditionMet);

            default:
                return $matchedStatus ?? self::STATUS_REPEATED;
        }
    }

    private function applyRuleAverageCondition(
        string  $matchedStatus,
        ?array  $rule,
        bool    $avgMet
    ): string {
        if ($rule === null) return $matchedStatus;

        $avgCond = $rule['average_condition'] ?? [];
        if (empty($avgCond['enabled'])) return $matchedStatus;

        $logic = strtoupper($avgCond['logic'] ?? 'AND');

        if ($logic === 'OR') {
            return $matchedStatus;
        }

        if ($avgMet) return $matchedStatus;
        return self::STATUS_TRIAL;
    }

    private function resolveBothLogic(
        ?string $gradeStatus,
        ?array  $matchedRule,
        bool    $avgOk
    ): string {
        $avgLogic = strtoupper($matchedRule['average_condition']['logic'] ?? 'AND');

        if ($avgLogic === 'OR') {
            if ($gradeStatus !== null) return $gradeStatus;
            return $avgOk ? self::STATUS_PROMOTED : self::STATUS_REPEATED;
        }

        if ($gradeStatus !== null && $avgOk)  return $gradeStatus;
        if ($gradeStatus !== null && !$avgOk) return self::STATUS_TRIAL;
        if ($gradeStatus === null  && $avgOk) return self::STATUS_SEE_PRINCIPAL;
        return self::STATUS_REPEATED;
    }

    // =========================================================================
    // GRADE COUNTING UTILITIES
    // =========================================================================

    private function buildScoreMap($scores): Collection
    {
        return collect($scores)->keyBy(function ($s) {
            return is_object($s) ? ($s->subject_id ?? null) : ($s['subject_id'] ?? null);
        });
    }

    private function filterByScope(Collection $scoreMap, string $scope, array $compulsoryIds): Collection
    {
        return $scoreMap->filter(function ($score, $subjectId) use ($scope, $compulsoryIds) {
            $isComp = in_array((string) $subjectId, array_map('strval', $compulsoryIds), true);
            return match ($scope) {
                'compulsory_only' => $isComp,
                'other_only'      => !$isComp,
                default           => true,
            };
        });
    }

    private function countMatchingGrade(
        Collection $scopedScores,
        string     $grade,
        string     $grouping,
        bool       $isSenior
    ): int {
        $mapping = $isSenior ? self::$groupedSenior : self::$groupedJunior;
        $count   = 0;

        foreach ($scopedScores as $score) {
            $studentGrade = is_object($score) ? ($score->grade ?? null) : ($score['grade'] ?? null);
            if (!$studentGrade) continue;
            $studentGrade = strtoupper(trim($studentGrade));

            if ($grouping === 'grouped') {
                $studentGroup = $this->exactToGroup($studentGrade, $mapping);
                if ($studentGroup === $grade) $count++;
            } else {
                if ($studentGrade === $grade) $count++;
            }
        }

        return $count;
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

    // =========================================================================
    // GRADE UTILITIES - FIXED VERSION
    // =========================================================================

    private function gradeFails(?string $studentGrade, ?string $minGrade): bool
    {
        if ($studentGrade === null) return true;

        $sg = strtoupper(trim($studentGrade));

        // If no minimum grade is specified, only F/F9 is failing
        if (empty($minGrade)) {
            return in_array($sg, ['F', 'F9'], true);
        }

        $mg = strtoupper(trim($minGrade));

        // Get order values (higher is better)
        $sgOrder = self::$gradeOrder[$sg] ?? -1;
        $mgOrder = self::$gradeOrder[$mg] ?? 0;

        // Student fails if their grade order is less than required minimum
        return $sgOrder < $mgOrder;
    }

    // =========================================================================
    // COMPULSORY SUBJECT DETAIL
    // =========================================================================

    private function buildCompulsoryDetail(
        int        $schoolclassid,
        int        $termid,
        int        $sessionid,
        Collection $scoreMap
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

        $failed = [];
        $detail = [];
        $passed = 0;
        $total  = $compulsoryRules->count();

        // Determine credit grades based on senior/junior
        $isSenior = $this->classIsSenior($schoolclassid);
        $creditGrades = $isSenior
            ? ['A1', 'B2', 'B3', 'C4', 'C5', 'C6']
            : ['A', 'B', 'C'];

        foreach ($compulsoryRules as $rule) {
            $subjectId    = $rule->subjectId;
            $scoreEntry   = $scoreMap->get($subjectId);
            $studentGrade = is_object($scoreEntry)
                ? ($scoreEntry->grade ?? null)
                : ($scoreEntry['grade'] ?? null);
            $subjectName  = is_object($scoreEntry)
                ? ($scoreEntry->subject_name ?? null)
                : null;
            $minGrade     = $rule->min_grade ?? 'C';

            // Check if passed using grade order
            $didPass = $scoreEntry && !$this->gradeFails($studentGrade, $minGrade);

            if (!$didPass) {
                $failed[] = [
                    'subject_id' => $subjectId,
                    'subject'    => $subjectName,
                    'grade'      => $studentGrade,
                    'min_grade'  => $minGrade,
                    'not_sat'    => !$scoreEntry,
                ];
            } else {
                $passed++;
            }

            $detail[] = [
                'subject_id' => $subjectId,
                'subject'    => $subjectName,
                'grade'      => $studentGrade,
                'min_grade'  => $minGrade,
                'passed'     => $didPass,
                'not_sat'    => !$scoreEntry,
            ];
        }

        return [$failed, $detail, $passed, $total];
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

    private function classIsSenior(int $schoolclassid): bool
    {
        $row = DB::table('schoolclass_classcategory')
            ->join('classcategories', 'classcategories.id', '=', 'schoolclass_classcategory.classcategory_id')
            ->where('schoolclass_classcategory.schoolclass_id', $schoolclassid)
            ->select('classcategories.is_senior')
            ->first();
        return $row ? (bool) $row->is_senior : false;
    }

    // =========================================================================
    // LABEL HELPERS
    // =========================================================================

    private function mapStatusLabel(string $status, PromotionSetting $settings): string
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
        $parts = [];

        $subjects = $rule['compulsory_section']['subjects'] ?? [];
        $withMin  = array_filter($subjects, fn($s) => !empty($s['min_grade']));
        if ($withMin) {
            $parts[] = count($withMin) . ' compulsory subject min-grade requirement(s)';
        }

        foreach ($rule['compulsory_section']['count_conditions'] ?? [] as $c) {
            $scope = match ($c['scope'] ?? 'all') {
                'compulsory_only' => 'compulsory subj',
                'other_only'      => 'other subj',
                default           => 'all subj',
            };
            $parts[] = "{$c['operator']} {$c['count']} {$c['grade']} in {$scope}";
        }

        foreach ($rule['other_section']['count_conditions'] ?? [] as $c) {
            $scope = match ($c['scope'] ?? 'all') {
                'compulsory_only' => 'compulsory subj',
                'other_only'      => 'other subj',
                default           => 'all subj',
            };
            $parts[] = "{$c['operator']} {$c['count']} {$c['grade']} in {$scope}";
        }

        $avgCond = $rule['average_condition'] ?? [];
        if (!empty($avgCond['enabled'])) {
            $logic   = $avgCond['logic'] ?? 'AND';
            $minAvg  = $avgCond['min_average'] ?? '?';
            $parts[] = "avg {$logic} ≥{$minAvg}%";
        }

        return implode('; ', $parts) ?: 'No conditions';
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

    // =========================================================================
    // LEGACY EVALUATION
    // =========================================================================

    private function legacyEvaluate(
        int    $studentId,
        int    $schoolclassid,
        int    $termid,
        int    $sessionid,
               $scores,
        ?float $overallAverage
    ): array {
        $isSenior = $this->classIsSenior($schoolclassid);
        $scoreCol = collect($scores);
        $scoreMap = $this->buildScoreMap($scoreCol);

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

        $creditGrades = $isSenior
            ? ['A1', 'B2', 'B3', 'C4', 'C5', 'C6']
            : ['A', 'B', 'C'];
        $failGrades   = $isSenior ? ['F9', 'E8'] : ['F'];
        $dGrade       = $isSenior ? 'D7' : 'D';

        $compulsorySubjectIds = $compulsoryRules->pluck('subjectId')->toArray();
        $compulsoryCreditCount = 0;
        $creditCount  = 0;
        $failCount    = 0;
        $missingCompulsorySubjects = [];
        $failedComp   = [];
        $detail       = [];
        $passedComp   = 0;

        foreach ($compulsoryRules as $rule) {
            $subjectId   = $rule->subjectId;
            $scoreEntry  = $scoreMap->get($subjectId);
            $grade       = $scoreEntry
                ? (is_object($scoreEntry) ? $scoreEntry->grade : $scoreEntry['grade'])
                : null;
            $subjectName = $scoreEntry
                ? (is_object($scoreEntry) ? ($scoreEntry->subject_name ?? null) : null)
                : null;

            if (!$scoreEntry) {
                $missingCompulsorySubjects[] = $subjectName ?? "Subject #{$subjectId}";
            } elseif (in_array($grade, $creditGrades)) {
                $compulsoryCreditCount++;
            }

            $didPass = $scoreEntry && in_array($grade, $creditGrades);
            if (!$didPass) {
                $failedComp[] = [
                    'subject_id' => $subjectId, 'subject' => $subjectName,
                    'grade' => $grade, 'min_grade' => 'C6', 'not_sat' => !$scoreEntry,
                ];
            } else {
                $passedComp++;
            }
            $detail[] = [
                'subject_id' => $subjectId, 'subject' => $subjectName,
                'grade' => $grade, 'min_grade' => 'C6',
                'passed' => $didPass, 'not_sat' => !$scoreEntry,
            ];
        }

        foreach ($scoreCol as $score) {
            $grade = is_object($score) ? $score->grade : $score['grade'];
            if (in_array($grade, $creditGrades)) $creditCount++;
            elseif (in_array($grade, $failGrades)) $failCount++;
        }

        $allDs       = $scoreCol->isNotEmpty() && $scoreCol->every(fn($s) => (is_object($s) ? $s->grade : $s['grade']) === $dGrade);
        $mixOfDsAndFs = $scoreCol->isNotEmpty() && $scoreCol->every(function ($s) use ($dGrade, $failGrades) {
            $g = is_object($s) ? $s->grade : $s['grade'];
            return $g === $dGrade || in_array($g, $failGrades);
        });
        $failedNonCompulsory = $scoreCol->filter(function ($s) use ($compulsorySubjectIds, $failGrades) {
            $sid = is_object($s) ? $s->subject_id : $s['subject_id'];
            $g   = is_object($s) ? $s->grade : $s['grade'];
            return !in_array($sid, $compulsorySubjectIds) && in_array($g, $failGrades);
        })->count() === max(0, $scoreCol->count() - count($compulsorySubjectIds));

        $compTotal = $compulsoryRules->count();

        if (!empty($missingCompulsorySubjects)) {
            $status = self::STATUS_SEE_PRINCIPAL;
        } elseif ($compTotal > 0 && $compulsoryCreditCount === $compTotal && $creditCount >= 5) {
            $status = self::STATUS_PROMOTED;
        } elseif ($creditCount >= 4 && $compulsoryCreditCount > 0) {
            $status = self::STATUS_TRIAL;
        } elseif ($creditCount >= 4 && $compulsoryCreditCount === 0) {
            $status = self::STATUS_SEE_PRINCIPAL;
        } elseif ($failCount === $scoreCol->count() && $scoreCol->isNotEmpty()) {
            $status = self::STATUS_REPEATED;
        } elseif ($allDs || $mixOfDsAndFs) {
            $status = self::STATUS_REPEATED;
        } elseif ($compulsoryCreditCount === $compTotal && $failedNonCompulsory && $scoreCol->count() > count($compulsorySubjectIds)) {
            $status = self::STATUS_SEE_PRINCIPAL;
        } elseif ($creditCount < 4 && $compulsoryCreditCount < $compTotal) {
            $status = self::STATUS_REPEATED;
        } else {
            $status = self::STATUS_SEE_PRINCIPAL;
        }

        $labelMap = [
            self::STATUS_PROMOTED      => 'Promoted',
            self::STATUS_TRIAL         => 'Promoted on Trial',
            self::STATUS_SEE_PRINCIPAL => 'Parents to See Principal',
            self::STATUS_REPEATED      => 'Advice to Repeat',
        ];

        $passAverage = DB::table('schoolclass_classcategory')
            ->where('schoolclass_id', $schoolclassid)
            ->value('promotion_pass_average');
        $reqAvg  = $passAverage !== null ? (float) $passAverage : null;
        $avgFail = $reqAvg !== null && $overallAverage !== null && $overallAverage < $reqAvg;

        return [
            'status'                    => $status,
            'status_label'              => $labelMap[$status] ?? 'Awaiting Decision',
            'is_promotional_term'       => true,
            'failed_compulsory'         => $failedComp,
            'compulsory_subject_detail' => $detail,
            'average_failed'            => $avgFail,
            'required_average'          => $reqAvg,
            'actual_average'            => $overallAverage,
            'compulsory_count'          => $compTotal,
            'passed_compulsory'         => $passedComp,
            'matched_labels'            => [],
            'applied_rule'              => null,
            'settings_id'               => null,
            'rule_logic'                => 'legacy',
            'settings'                  => null,
        ];
    }

    // =========================================================================
    // PUBLIC HELPERS
    // =========================================================================

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
