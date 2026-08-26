<?php
// app/Services/PromotionEvaluator.php

namespace App\Services;

use App\Models\CompulsorySubjectClass;
use App\Models\PromotionSetting;
use App\Models\Schoolterm;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PromotionEvaluator
{
    const STATUS_PROMOTED      = 'promoted';
    const STATUS_TRIAL         = 'trial';
    const STATUS_SEE_PRINCIPAL = 'see_principal';
    const STATUS_REPEATED      = 'repeated';
    const STATUS_AWAITING      = 'awaiting';

    private static array $seniorGradeOrder = [
        'F9' => 0, 'E8' => 1, 'D7' => 2,
        'C6' => 3, 'C5' => 4, 'C4' => 5,
        'B3' => 6, 'B2' => 7, 'A1' => 8,
    ];

    private static array $juniorGradeOrder = [
        'F' => 0, 'D' => 1, 'C' => 2, 'B' => 3, 'A' => 4,
    ];

    private static array $gradeConversionMap = [
        'A1' => 'A',
        'B2' => 'B', 'B3' => 'B',
        'C4' => 'C', 'C5' => 'C', 'C6' => 'C',
        'D7' => 'D',
        'E8' => 'F', 'F9' => 'F',
    ];

    public function evaluate(
        int    $studentId,
        int    $schoolclassid,
        int    $termid,
        int    $sessionid,
               $scores,
        ?float $overallAverage = null
    ): array {
        Log::info('========== PROMOTION EVALUATION START ==========', [
            'student_id'      => $studentId,
            'class_id'        => $schoolclassid,
            'session_id'      => $sessionid,
            'term_id'         => $termid,
            'overall_average' => $overallAverage,
            'scores_count'    => is_countable($scores) ? count($scores) : 0,
        ]);

        // ── Step 1: Is this a promotional term at all? ────────────────────────
        $term          = Schoolterm::find($termid);
        $isPromotional = $term && $term->is_promotional;

        Log::info('Term promotional check', [
            'term_id'        => $termid,
            'term_found'     => (bool) $term,
            'is_promotional' => $isPromotional,
        ]);

        if (!$isPromotional) {
            Log::info('Non-promotional term — returning awaiting');
            return $this->awaitingResult($overallAverage);
        }

        // ── Step 2: Class category ────────────────────────────────────────────
        $classCategory     = $this->getClassCategory($schoolclassid);
        $usesSeniorGrading = $this->detectGradingType($schoolclassid, $scores);

        Log::info('Class category info', [
            'class_id'               => $schoolclassid,
            'detected_grading_type'  => $usesSeniorGrading ? 'senior' : 'junior',
            'category'               => $classCategory ? $classCategory->category : 'unknown',
            'promotion_pass_average' => $classCategory ? $classCategory->promotion_pass_average : null,
        ]);

        // ── Step 3: Does ANY active setting exist for this class? ─────────────
        $anySettingsExist = PromotionSetting::where('schoolclass_id', $schoolclassid)
            ->where('is_active', true)
            ->exists();

        if (!$anySettingsExist) {
            Log::warning('No active promotion settings exist for this class — returning awaiting', [
                'class_id' => $schoolclassid,
            ]);
            return $this->awaitingResult($overallAverage);
        }

        // ── Step 4: Find the best setting that matches this session + term ─────
        $settings = $this->findBestSettings($schoolclassid, $sessionid, $termid);

        Log::info('Settings lookup result', [
            'found'       => $settings ? 'yes' : 'no',
            'settings_id' => $settings ? $settings->id : null,
            'rule_logic'  => $settings ? $settings->rule_logic : null,
            'rules_count' => $settings ? count($settings->promotion_rules ?? []) : 0,
        ]);

        if (!$settings) {
            Log::warning('Settings exist for class but none match this session/term — returning awaiting', [
                'class_id'   => $schoolclassid,
                'session_id' => $sessionid,
                'term_id'    => $termid,
            ]);
            return $this->awaitingResult($overallAverage);
        }

        // ── Step 5: Settings found — check they actually have rules ───────────
        $rules = $settings->promotion_rules ?? [];

        if (empty($rules)) {
            Log::warning('Matched settings contain no rules — returning awaiting', [
                'settings_id' => $settings->id,
            ]);
            return $this->awaitingResult($overallAverage);
        }

        // ── Step 6: Run rule evaluation ───────────────────────────────────────
        $scoreMap      = $this->buildScoreMap($scores);
        $compulsoryIds = $this->getCompulsoryIds($schoolclassid, $termid, $sessionid);
        $ruleLogic     = $settings->rule_logic ?? 'grade_count';

        Log::info('Starting rule evaluation', [
            'uses_senior_grading'       => $usesSeniorGrading,
            'compulsory_subjects_count' => count($compulsoryIds),
            'scores_in_map'             => $scoreMap->count(),
            'total_rules'               => count($rules),
            'rule_logic'                => $ruleLogic,
        ]);

        $matchedRule   = null;
        $matchedStatus = null;
        $matchedIndex  = null;
        $matchedRuleName = null;

        if (in_array($ruleLogic, ['grade_count', 'both'])) {
            foreach ($rules as $idx => $rule) {
                // Get rule name - try multiple possible keys
                $ruleName = $rule['rule_name'] ?? $rule['name'] ?? $rule['label'] ?? 'Unnamed Rule';

                if (empty($rule['rule_name']) && empty($rule['name']) && empty($rule['label'])) {
                    Log::warning('Rule ' . ($idx + 1) . ' has no name, using fallback', [
                        'rule_keys' => array_keys($rule)
                    ]);
                }

                Log::info('Checking rule ' . ($idx + 1), [
                    'rule_name'      => $ruleName,
                    'status_label'   => $rule['status_label'] ?? 'unknown',
                    'grade_grouping' => $rule['grade_grouping'] ?? 'grouped',
                ]);

                $matches = $this->ruleMatches($rule, $scoreMap, $compulsoryIds, $usesSeniorGrading);

                Log::info('Rule evaluation result', [
                    'rule_name' => $ruleName,
                    'matches'   => $matches,
                ]);

                if ($matches) {
                    $matchedRule = $rule;
                    $matchedStatus = $rule['status_label'] ?? self::STATUS_PROMOTED;
                    $matchedIndex = $idx;
                    $matchedRuleName = $ruleName; // Store the actual rule name
                    
                    Log::info('Rule MATCHED!', [
                        'rule_name' => $matchedRuleName,
                        'status'    => $matchedStatus,
                    ]);
                    break;
                }
            }
        }

        $requiredAverage = $this->resolveRequiredAverage($settings, $schoolclassid);

        Log::info('Average condition', [
            'required_average' => $requiredAverage,
            'actual_average'   => $overallAverage,
            'rule_logic'       => $ruleLogic,
        ]);

        [$averageConditionMet, $averageStatus] = $this->evaluateAverage(
            $ruleLogic, $requiredAverage, $overallAverage
        );

        $finalStatus = $this->resolveFinalStatus(
            $ruleLogic,
            $matchedStatus,
            $matchedRule,
            $averageConditionMet,
            $averageStatus,
            $isPromotional
        );

        Log::info('Final status resolved', [
            'final_status'      => $finalStatus,
            'matched_rule_name' => $matchedRuleName,
            'matched_status'    => $matchedStatus,
        ]);

        [$failedCompulsory, $compulsoryDetail, $passedCount, $totalCount]
            = $this->buildCompulsoryDetail(
                $schoolclassid, $termid, $sessionid, $scoreMap, $usesSeniorGrading
            );

        // ── Build applied rule summary with the actual rule name ──
        $appliedRuleSummary = null;
        if ($matchedRule !== null && $matchedRuleName !== null) {
            $appliedRuleSummary = [
                'name'        => $matchedRuleName,
                'description' => $this->describeRule($matchedRule, $usesSeniorGrading),
                'index'       => $matchedIndex + 1,
            ];
        } elseif ($matchedRule !== null) {
            // Fallback: try to extract name from the rule array
            $fallbackName = $matchedRule['rule_name'] ?? 
                           $matchedRule['name'] ?? 
                           $matchedRule['label'] ?? 
                           'Unknown Rule';
            $appliedRuleSummary = [
                'name'        => $fallbackName,
                'description' => $this->describeRule($matchedRule, $usesSeniorGrading),
                'index'       => $matchedIndex + 1,
            ];
        }

        Log::info('========== PROMOTION EVALUATION END ==========', [
            'final_status' => $finalStatus,
            'applied_rule_name' => $appliedRuleSummary['name'] ?? null,
        ]);

        return [
            'status'                    => $finalStatus,
            'status_label'              => $this->mapStatusLabel($finalStatus, $settings),
            'is_promotional_term'       => $isPromotional,
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

    // ── Public so PromotionController can call directly ───────────────────────
    public function awaitingResult(?float $overallAverage): array
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
    // PRIVATE METHODS
    // =========================================================================

    private function getClassCategory(int $schoolclassid): ?object
    {
        return DB::table('schoolclass_classcategory')
            ->join('classcategories', 'classcategories.id', '=',
                'schoolclass_classcategory.classcategory_id')
            ->where('schoolclass_classcategory.schoolclass_id', $schoolclassid)
            ->select(
                'classcategories.id',
                'classcategories.category',
                'classcategories.is_senior',
                'classcategories.promotion_pass_average'
            )
            ->first();
    }

    private function detectGradingType(int $schoolclassid, $scores): bool
    {
        $classCategory = $this->getClassCategory($schoolclassid);
        if ($classCategory && isset($classCategory->is_senior)) {
            return (bool) $classCategory->is_senior;
        }

        if (!$scores || (is_countable($scores) && count($scores) === 0)) {
            return false;
        }

        $sampleGrades = [];
        $sampleCount  = 0;

        foreach ($scores as $score) {
            $grade = is_object($score) ? ($score->grade ?? null) : ($score['grade'] ?? null);
            if ($grade && $sampleCount < 10) {
                $sampleGrades[] = strtoupper(trim($grade));
                $sampleCount++;
            }
        }

        $seniorCount = 0;
        foreach ($sampleGrades as $grade) {
            if (preg_match('/^[A-E][1-9]$|^F9$/', $grade)) {
                $seniorCount++;
            }
        }

        $isSenior = $sampleCount > 0 && $seniorCount > ($sampleCount / 2);

        Log::info('Grade type detection', [
            'sample_grades'      => $sampleGrades,
            'senior_count'       => $seniorCount,
            'total_sample'       => $sampleCount,
            'detected_as_senior' => $isSenior,
        ]);

        return $isSenior;
    }

    private function normalizeGrade(?string $grade, bool $isSenior): ?string
    {
        if ($grade === null) return null;
        $grade = strtoupper(trim($grade));
        if (!$isSenior && isset(self::$gradeConversionMap[$grade])) {
            return self::$gradeConversionMap[$grade];
        }
        return $grade;
    }

    private function findBestSettings(int $schoolclassid, int $sessionid, int $termid): ?PromotionSetting
    {
        $allSettings = PromotionSetting::where('schoolclass_id', $schoolclassid)
            ->where('is_active', true)
            ->get();

        Log::info('All active settings for class', [
            'count'    => $allSettings->count(),
            'settings' => $allSettings->map(fn($s) => [
                'id'          => $s->id,
                'session_id'  => $s->session_id,
                'term_id'     => $s->term_id,
                'priority'    => $s->priority,
                'rule_set_name' => $s->rule_set_name,
                'rules_count' => count($s->promotion_rules ?? []),
            ])->toArray(),
        ]);

        if ($allSettings->isEmpty()) {
            return null;
        }

        $scored = [];

        foreach ($allSettings as $setting) {
            $score     = 0;
            $matchType = '';

            if ($setting->session_id == $sessionid && $setting->term_id == $termid) {
                $score = 100; $matchType = 'exact_session_term';
            } elseif ($setting->session_id == $sessionid && $setting->term_id === null) {
                $score = 90; $matchType = 'session_only_null_term';
            } elseif ($setting->session_id == $sessionid && $setting->term_id !== null
                      && $setting->term_id != $termid) {
                $score = 0; $matchType = 'wrong_term';
            } elseif ($setting->session_id === null && $setting->term_id == $termid) {
                $score = 80; $matchType = 'term_only';
            } elseif ($setting->session_id === null && $setting->term_id === null) {
                $score = 70; $matchType = 'global';
            } elseif ($setting->session_id !== null
                      && $setting->session_id != $sessionid
                      && $setting->term_id === null) {
                $score = 60; $matchType = 'different_session_null_term';
            } elseif ($setting->session_id === null
                      && $setting->term_id !== null
                      && $setting->term_id != $termid) {
                $score = 0; $matchType = 'wrong_term_null_session';
            }

            if ($score > 0) {
                $scored[] = [
                    'setting'    => $setting,
                    'score'      => $score,
                    'match_type' => $matchType,
                    'priority'   => $setting->priority ?? 999,
                ];
            }
        }

        if (empty($scored)) {
            Log::warning('No matching settings found with positive score');
            return null;
        }

        usort($scored, function ($a, $b) {
            if ($a['score'] !== $b['score']) {
                return $b['score'] - $a['score'];
            }
            return $a['priority'] - $b['priority'];
        });

        $best = $scored[0]['setting'];

        Log::info('Selected best setting', [
            'setting_id' => $best->id,
            'rule_set_name' => $best->rule_set_name,
            'match_type' => $scored[0]['match_type'],
            'score'      => $scored[0]['score'],
            'session_id' => $best->session_id,
            'term_id'    => $best->term_id,
        ]);

        return $best;
    }

    private function ruleMatches(
        array      $rule,
        Collection $scoreMap,
        array      $compulsoryIds,
        bool       $isSenior
    ): bool {
        $grouping = $rule['grade_grouping'] ?? 'grouped';

        if ($isSenior && $grouping === 'grouped') {
            $grouping = 'exact';
        }

        // Section 1: Per-subject minimum grade
        foreach ($rule['compulsory_section']['subjects'] ?? [] as $subjectRule) {
            $minGrade = $subjectRule['min_grade'] ?? null;
            if (!$minGrade) continue;

            $subjectId    = $subjectRule['subject_id'] ?? null;
            $scoreEntry   = $subjectId ? $scoreMap->get($subjectId) : null;
            $studentGrade = $scoreEntry
                ? (is_object($scoreEntry) ? ($scoreEntry->grade ?? null) : ($scoreEntry['grade'] ?? null))
                : null;

            $normalizedStudent = $this->normalizeGrade($studentGrade, $isSenior);
            $normalizedMin     = $this->normalizeGrade($minGrade, $isSenior);

            if ($this->gradeFails($normalizedStudent, $normalizedMin, $isSenior, $grouping)) {
                Log::debug('Rule failed: compulsory subject min grade not met', [
                    'subject_id'     => $subjectId,
                    'student_grade'  => $studentGrade,
                    'required_grade' => $minGrade,
                ]);
                return false;
            }
        }

        // Section 2: Count conditions on compulsory subjects
        if (!$this->evaluateCountConditions(
            $rule['compulsory_section']['count_conditions'] ?? [],
            $scoreMap, $compulsoryIds, $grouping, $isSenior
        )) {
            Log::debug('Rule failed: compulsory count conditions not met');
            return false;
        }

        // Section 3: Count conditions on other/all subjects
        if (!$this->evaluateCountConditions(
            $rule['other_section']['count_conditions'] ?? [],
            $scoreMap, $compulsoryIds, $grouping, $isSenior
        )) {
            Log::debug('Rule failed: other count conditions not met');
            return false;
        }

        Log::debug('Rule passed all conditions');
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
            $actual       = $this->countMatchingGrade($scopedScores, $grade, $grouping, $isSenior);
            $result       = $this->compareCount($actual, $operator, $required);

            Log::debug('Count condition evaluation', [
                'grade'    => $grade,
                'operator' => $operator,
                'required' => $required,
                'actual'   => $actual,
                'scope'    => $scope,
                'result'   => $result,
            ]);

            if (!$result) return false;
        }

        return true;
    }

    private function gradeFails(
        ?string $studentGrade,
        ?string $minGrade,
        bool    $isSenior,
        string  $grouping = 'exact'
    ): bool {
        if ($studentGrade === null) return true;

        $sg = strtoupper(trim($studentGrade));

        if (empty($minGrade)) {
            return in_array($sg, $isSenior ? ['F9'] : ['F'], true);
        }

        $mg = strtoupper(trim($minGrade));

        if ($isSenior) {
            return (self::$seniorGradeOrder[$sg] ?? -1) < (self::$seniorGradeOrder[$mg] ?? 0);
        }

        return (self::$juniorGradeOrder[$sg] ?? -1) < (self::$juniorGradeOrder[$mg] ?? 0);
    }

    private function countMatchingGrade(
        Collection $scopedScores,
        string     $grade,
        string     $grouping,
        bool       $isSenior
    ): int {
        $count         = 0;
        $requiredGrade = strtoupper(trim($grade));

        foreach ($scopedScores as $score) {
            $studentGrade = is_object($score)
                ? ($score->grade ?? null)
                : ($score['grade'] ?? null);
            if (!$studentGrade) continue;

            $normalized = strtoupper(trim(
                $this->normalizeGrade($studentGrade, $isSenior) ?? ''
            ));
            if (!$normalized) continue;

            if ($isSenior) {
                if ($normalized === $requiredGrade) $count++;
            } else {
                $studentRank  = self::$juniorGradeOrder[$normalized]   ?? -1;
                $requiredRank = self::$juniorGradeOrder[$requiredGrade] ?? 0;
                if ($studentRank >= $requiredRank) $count++;
            }
        }

        return $count;
    }

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

    private function compareCount(int $actual, string $operator, int $required): bool
    {
        return match ($operator) {
            '>=' => $actual >= $required,
            '<=' => $actual <= $required,
            '='  => $actual === $required,
            '>'  => $actual >  $required,
            '<'  => $actual <  $required,
            default => false,
        };
    }

    private function buildCompulsoryDetail(
        int        $schoolclassid,
        int        $termid,
        int        $sessionid,
        Collection $scoreMap,
        bool       $isSenior
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

        foreach ($compulsoryRules as $rule) {
            $subjectId    = $rule->subjectId;
            $scoreEntry   = $scoreMap->get($subjectId);
            $studentGrade = is_object($scoreEntry)
                ? ($scoreEntry->grade       ?? null)
                : ($scoreEntry['grade']     ?? null);
            $subjectName  = is_object($scoreEntry)
                ? ($scoreEntry->subject_name ?? null)
                : null;
            $minGrade     = $rule->min_grade;

            $normalizedStudent = $this->normalizeGrade($studentGrade, $isSenior);
            $normalizedMin     = $this->normalizeGrade($minGrade, $isSenior);

            $didPass = false;
            if ($scoreEntry && $normalizedStudent) {
                if ($isSenior) {
                    $didPass = (self::$seniorGradeOrder[$normalizedStudent] ?? -1)
                             >= (self::$seniorGradeOrder[$normalizedMin]    ?? 0);
                } else {
                    $didPass = (self::$juniorGradeOrder[$normalizedStudent] ?? -1)
                             >= (self::$juniorGradeOrder[$normalizedMin]    ?? 0);
                }
            }

            $entry = [
                'subject_id'           => $subjectId,
                'subject'              => $subjectName,
                'grade'                => $studentGrade,
                'normalized_grade'     => $normalizedStudent,
                'min_grade'            => $minGrade,
                'normalized_min_grade' => $normalizedMin,
                'passed'               => $didPass,
                'not_sat'              => !$scoreEntry,
            ];

            $detail[] = $entry;

            if (!$didPass) {
                $failed[] = $entry;
            } else {
                $passed++;
            }
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

    private function resolveRequiredAverage(PromotionSetting $settings, int $schoolclassid): ?float
    {
        if ($settings->promotion_pass_average !== null && $settings->promotion_pass_average !== '') {
            return (float) $settings->promotion_pass_average;
        }

        $val = DB::table('schoolclass_classcategory')
            ->join('classcategories', 'classcategories.id', '=',
                'schoolclass_classcategory.classcategory_id')
            ->where('schoolclass_classcategory.schoolclass_id', $schoolclassid)
            ->value('classcategories.promotion_pass_average');

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

    private function resolveFinalStatus(
        string  $ruleLogic,
        ?string $matchedStatus,
        ?array  $matchedRule,
        bool    $averageConditionMet,
        ?string $averageStatus,
        bool    $isPromotional = true
    ): string {
        Log::debug('Resolving final status', [
            'rule_logic'            => $ruleLogic,
            'matched_status'        => $matchedStatus,
            'average_condition_met' => $averageConditionMet,
            'average_status'        => $averageStatus,
            'is_promotional'        => $isPromotional,
        ]);

        if (!$isPromotional) {
            return self::STATUS_AWAITING;
        }

        switch ($ruleLogic) {
            case 'average_only':
                return $averageStatus ?? self::STATUS_AWAITING;

            case 'grade_count':
                if ($matchedStatus === null) {
                    Log::debug('No rule matched on promotional term → REPEATED');
                    return self::STATUS_REPEATED;
                }
                return $matchedStatus;

            case 'both':
                if ($matchedStatus !== null && $averageConditionMet)  return $matchedStatus;
                if ($matchedStatus !== null && !$averageConditionMet) return self::STATUS_TRIAL;
                if ($matchedStatus === null && $averageConditionMet)  return self::STATUS_PROMOTED;
                return self::STATUS_REPEATED;

            default:
                return $matchedStatus ?? self::STATUS_REPEATED;
        }
    }

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
        $parts   = [];
        $withMin = array_filter(
            $rule['compulsory_section']['subjects'] ?? [],
            fn($s) => !empty($s['min_grade'])
        );
        if ($withMin) {
            $parts[] = count($withMin) . ' compulsory subject min-grade requirement(s)';
        }

        foreach ($rule['compulsory_section']['count_conditions'] ?? [] as $c) {
            $scope   = match ($c['scope'] ?? 'all') {
                'compulsory_only' => 'compulsory subj',
                'other_only'      => 'other subj',
                default           => 'all subj',
            };
            $parts[] = "{$c['operator']} {$c['count']} {$c['grade']} in {$scope}";
        }

        foreach ($rule['other_section']['count_conditions'] ?? [] as $c) {
            $scope   = match ($c['scope'] ?? 'all') {
                'compulsory_only' => 'compulsory subj',
                'other_only'      => 'other subj',
                default           => 'all subj',
            };
            $parts[] = "{$c['operator']} {$c['count']} {$c['grade']} in {$scope}";
        }

        $avgCond = $rule['average_condition'] ?? [];
        if (!empty($avgCond['enabled'])) {
            $parts[] = 'avg ' . ($avgCond['logic'] ?? 'AND') . ' ≥' . ($avgCond['min_average'] ?? '?') . '%';
        }

        return implode('; ', $parts) ?: 'No conditions';
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