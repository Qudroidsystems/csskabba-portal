<?php

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

    // Senior grade mapping (exact)
    private static array $seniorGrades = ['A1', 'B2', 'B3', 'C4', 'C5', 'C6', 'D7', 'E8', 'F9'];

    // Junior grade mapping (grouped)
    private static array $juniorGrades = ['A', 'B', 'C', 'D', 'F'];

    // Senior grade order for comparison
    private static array $seniorGradeOrder = [
        'F9' => 0, 'E8' => 1, 'D7' => 2,
        'C6' => 3, 'C5' => 4, 'C4' => 5,
        'B3' => 6, 'B2' => 7, 'A1' => 8,
    ];

    // Junior grade order for comparison
    private static array $juniorGradeOrder = [
        'F' => 0, 'D' => 1, 'C' => 2, 'B' => 3, 'A' => 4,
    ];

    // Grade conversion map (senior-style to junior letter grades)
    private static array $gradeConversionMap = [
        'A1' => 'A',
        'B2' => 'B', 'B3' => 'B',
        'C4' => 'C', 'C5' => 'C', 'C6' => 'C',
        'D7' => 'D',
        'E8' => 'F', 'F9' => 'F',
    ];

    public function evaluate(
        int     $studentId,
        int     $schoolclassid,
        int     $termid,
        int     $sessionid,
                $scores,
        ?float  $overallAverage = null,
        ?object $classCategory = null
    ): array {
        Log::info('========== PROMOTION EVALUATION START ==========', [
            'student_id' => $studentId,
            'class_id' => $schoolclassid,
            'session_id' => $sessionid,
            'term_id' => $termid,
            'overall_average' => $overallAverage,
            'scores_count' => is_countable($scores) ? count($scores) : 0
        ]);

        // Determine if senior or junior from class category
        $isSenior = false;
        if ($classCategory !== null) {
            $isSenior = (bool) ($classCategory->is_senior ?? false);
            Log::info('Using class category from parameter', [
                'category' => $classCategory->category ?? 'unknown',
                'is_senior' => $isSenior
            ]);
        } else {
            // Fallback: fetch from database
            $dbCategory = $this->getClassCategory($schoolclassid);
            $isSenior = $dbCategory ? (bool)$dbCategory->is_senior : false;
            Log::info('Fetched class category from DB', ['is_senior' => $isSenior]);
        }

        $settings = $this->findBestSettings($schoolclassid, $sessionid, $termid);

        if (!$settings) {
            Log::warning('No active promotion setting found, using legacy evaluation');
            $term = Schoolterm::find($termid);
            if (!$term || !$term->is_promotional) {
                return $this->awaitingResult($overallAverage);
            }
            return $this->legacyEvaluate(
                $studentId, $schoolclassid, $termid, $sessionid, $scores, $overallAverage, $isSenior
            );
        }

        $scoreMap      = $this->buildScoreMap($scores);
        $compulsoryIds = $this->getCompulsoryIds($schoolclassid, $termid, $sessionid);
        $ruleLogic     = $settings->rule_logic ?? 'grade_count';

        $matchedRule   = null;
        $matchedStatus = null;
        $matchedIndex  = null;

        $rules = $settings->promotion_rules ?? [];

        if (in_array($ruleLogic, ['grade_count', 'both']) && !empty($rules)) {
            foreach ($rules as $idx => $rule) {
                $ruleName = $rule['rule_name'] ?? 'Unnamed';
                if (empty($rule['rule_name'])) continue;

                $matches = $this->ruleMatches($rule, $scoreMap, $compulsoryIds, $isSenior);

                if ($matches) {
                    $matchedRule   = $rule;
                    $matchedStatus = $rule['status_label'] ?? self::STATUS_PROMOTED;
                    $matchedIndex  = $idx;
                    break;
                }
            }
        }

        $requiredAverage = $this->resolveRequiredAverage($settings, $schoolclassid);
        [$averageConditionMet, $averageStatus] = $this->evaluateAverage(
            $ruleLogic, $requiredAverage, $overallAverage
        );

        $finalStatus = $this->resolveFinalStatus(
            $ruleLogic, $matchedStatus, $matchedRule, $averageConditionMet, $averageStatus
        );

        [$failedCompulsory, $compulsoryDetail, $passedCount, $totalCount]
            = $this->buildCompulsoryDetail($schoolclassid, $termid, $sessionid, $scoreMap, $isSenior);

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

    private function getClassCategory(int $schoolclassid): ?object
    {
        return DB::table('schoolclass_classcategory')
            ->join('classcategories', 'classcategories.id', '=', 'schoolclass_classcategory.classcategory_id')
            ->where('schoolclass_classcategory.schoolclass_id', $schoolclassid)
            ->select('classcategories.id', 'classcategories.category', 'classcategories.is_senior', 'classcategories.promotion_pass_average')
            ->first();
    }

    private function findBestSettings(int $schoolclassid, int $sessionid, int $termid): ?PromotionSetting
    {
        $allSettings = PromotionSetting::where('schoolclass_id', $schoolclassid)
            ->where('is_active', true)
            ->get();

        if ($allSettings->isEmpty()) return null;

        $scoredSettings = [];
        foreach ($allSettings as $setting) {
            $score = 0;
            if ($setting->session_id == $sessionid && $setting->term_id == $termid) {
                $score = 100;
            } elseif ($setting->session_id == $sessionid && $setting->term_id === null) {
                $score = 90;
            } elseif ($setting->session_id === null && $setting->term_id == $termid) {
                $score = 80;
            } elseif ($setting->session_id === null && $setting->term_id === null) {
                $score = 70;
            } else {
                continue;
            }

            $scoredSettings[] = [
                'setting' => $setting,
                'score' => $score,
                'priority' => $setting->priority ?? 999
            ];
        }

        if (empty($scoredSettings)) return null;

        usort($scoredSettings, function($a, $b) {
            if ($a['score'] != $b['score']) return $b['score'] - $a['score'];
            return $a['priority'] - $b['priority'];
        });

        return $scoredSettings[0]['setting'];
    }

    private function ruleMatches(array $rule, Collection $scoreMap, array $compulsoryIds, bool $isSenior): bool
    {
        // Section 1: Per-subject minimum grade for compulsory subjects
        $compSubjects = $rule['compulsory_section']['subjects'] ?? [];
        foreach ($compSubjects as $subjectRule) {
            $minGrade = $subjectRule['min_grade'] ?? null;
            if (!$minGrade) continue;

            $subjectId    = $subjectRule['subject_id'] ?? null;
            $scoreEntry   = $subjectId ? $scoreMap->get($subjectId) : null;
            $studentGrade = $scoreEntry
                ? (is_object($scoreEntry) ? ($scoreEntry->grade ?? null) : ($scoreEntry['grade'] ?? null))
                : null;

            if ($this->gradeFails($studentGrade, $minGrade, $isSenior)) {
                return false;
            }
        }

        // Section 2: Count conditions on compulsory subjects
        $compCountConditions = $rule['compulsory_section']['count_conditions'] ?? [];
        if (!$this->evaluateCountConditions($compCountConditions, $scoreMap, $compulsoryIds, $isSenior)) {
            return false;
        }

        // Section 3: Count conditions on other/all subjects
        $otherCountConditions = $rule['other_section']['count_conditions'] ?? [];
        if (!$this->evaluateCountConditions($otherCountConditions, $scoreMap, $compulsoryIds, $isSenior)) {
            return false;
        }

        return true;
    }

    private function evaluateCountConditions(array $conditions, Collection $scoreMap, array $compulsoryIds, bool $isSenior): bool
    {
        if (empty($conditions)) return true;

        foreach ($conditions as $cond) {
            $grade    = strtoupper(trim($cond['grade'] ?? ''));
            $operator = $cond['operator'] ?? '>=';
            $required = (int) ($cond['count'] ?? 0);
            $scope    = $cond['scope'] ?? 'all';

            if (!$grade) continue;

            $scopedScores = $this->filterByScope($scoreMap, $scope, $compulsoryIds);
            $actual = $this->countMatchingGrade($scopedScores, $grade, $isSenior);

            if (!$this->compareCount($actual, $operator, $required)) {
                return false;
            }
        }

        return true;
    }

    private function gradeFails(?string $studentGrade, ?string $minGrade, bool $isSenior): bool
    {
        if ($studentGrade === null) return true;

        $sg = strtoupper(trim($studentGrade));

        if (empty($minGrade)) {
            $failGrades = $isSenior ? ['F9'] : ['F'];
            return in_array($sg, $failGrades, true);
        }

        $mg = strtoupper(trim($minGrade));

        if ($isSenior) {
            $studentRank = self::$seniorGradeOrder[$sg] ?? -1;
            $minRank = self::$seniorGradeOrder[$mg] ?? 0;
            return $studentRank < $minRank;
        } else {
            $studentRank = self::$juniorGradeOrder[$sg] ?? -1;
            $minRank = self::$juniorGradeOrder[$mg] ?? 0;
            return $studentRank < $minRank;
        }
    }

    private function countMatchingGrade(Collection $scopedScores, string $grade, bool $isSenior): int
    {
        $count = 0;
        $requiredGrade = strtoupper(trim($grade));

        foreach ($scopedScores as $score) {
            $studentGrade = is_object($score) ? ($score->grade ?? null) : ($score['grade'] ?? null);
            if (!$studentGrade) continue;

            $normalizedGrade = strtoupper(trim($studentGrade));

            if ($isSenior) {
                if ($normalizedGrade === $requiredGrade) {
                    $count++;
                }
            } else {
                $studentRank = self::$juniorGradeOrder[$normalizedGrade] ?? -1;
                $requiredRank = self::$juniorGradeOrder[$requiredGrade] ?? 0;
                if ($studentRank >= $requiredRank) {
                    $count++;
                }
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
            '>'  => $actual > $required,
            '<'  => $actual < $required,
            default => false,
        };
    }

    private function buildCompulsoryDetail(int $schoolclassid, int $termid, int $sessionid, Collection $scoreMap, bool $isSenior): array
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

        $failed = [];
        $detail = [];
        $passed = 0;
        $total  = $compulsoryRules->count();

        foreach ($compulsoryRules as $rule) {
            $subjectId    = $rule->subjectId;
            $scoreEntry   = $scoreMap->get($subjectId);
            $studentGrade = is_object($scoreEntry)
                ? ($scoreEntry->grade ?? null)
                : ($scoreEntry['grade'] ?? null);
            $subjectName  = is_object($scoreEntry)
                ? ($scoreEntry->subject_name ?? null)
                : null;
            $minGrade     = $rule->min_grade;

            $didPass = false;
            if ($scoreEntry && $studentGrade) {
                $sg = strtoupper(trim($studentGrade));
                $mg = strtoupper(trim($minGrade ?? ($isSenior ? 'C6' : 'C')));

                if ($isSenior) {
                    $studentRank = self::$seniorGradeOrder[$sg] ?? -1;
                    $minRank = self::$seniorGradeOrder[$mg] ?? 0;
                    $didPass = $studentRank >= $minRank;
                } else {
                    $studentRank = self::$juniorGradeOrder[$sg] ?? -1;
                    $minRank = self::$juniorGradeOrder[$mg] ?? 0;
                    $didPass = $studentRank >= $minRank;
                }
            }

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

    private function resolveRequiredAverage(PromotionSetting $settings, int $schoolclassid): ?float
    {
        if ($settings->promotion_pass_average !== null && $settings->promotion_pass_average !== '') {
            return (float) $settings->promotion_pass_average;
        }

        $val = DB::table('schoolclass_classcategory')
            ->join('classcategories', 'classcategories.id', '=', 'schoolclass_classcategory.classcategory_id')
            ->where('schoolclass_classcategory.schoolclass_id', $schoolclassid)
            ->value('classcategories.promotion_pass_average');

        return $val !== null ? (float) $val : null;
    }

    private function evaluateAverage(string $ruleLogic, ?float $requiredAverage, ?float $overallAverage): array
    {
        if (!in_array($ruleLogic, ['average_only', 'both'])) {
            return [true, null];
        }
        if ($requiredAverage === null || $overallAverage === null) {
            return [true, null];
        }
        $met = $overallAverage >= $requiredAverage;
        return [$met, $met ? self::STATUS_PROMOTED : self::STATUS_REPEATED];
    }

    private function resolveFinalStatus(string $ruleLogic, ?string $matchedStatus, ?array $matchedRule, bool $averageConditionMet, ?string $averageStatus): string
    {
        switch ($ruleLogic) {
            case 'average_only':
                return $averageStatus ?? self::STATUS_AWAITING;
            case 'grade_count':
                return $matchedStatus ?? self::STATUS_REPEATED;
            case 'both':
                if ($matchedStatus !== null && $averageConditionMet) return $matchedStatus;
                if ($matchedStatus !== null && !$averageConditionMet) return self::STATUS_TRIAL;
                if ($matchedStatus === null && $averageConditionMet) return self::STATUS_PROMOTED;
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
        $parts = [];
        $subjects = $rule['compulsory_section']['subjects'] ?? [];
        $withMin = array_filter($subjects, fn($s) => !empty($s['min_grade']));
        if ($withMin) {
            $parts[] = count($withMin) . ' compulsory subject min-grade requirement(s)';
        }

        foreach ($rule['compulsory_section']['count_conditions'] ?? [] as $c) {
            $scope = match ($c['scope'] ?? 'all') {
                'compulsory_only' => 'compulsory subj',
                'other_only' => 'other subj',
                default => 'all subj',
            };
            $parts[] = "{$c['operator']} {$c['count']} {$c['grade']} in {$scope}";
        }

        foreach ($rule['other_section']['count_conditions'] ?? [] as $c) {
            $scope = match ($c['scope'] ?? 'all') {
                'compulsory_only' => 'compulsory subj',
                'other_only' => 'other subj',
                default => 'all subj',
            };
            $parts[] = "{$c['operator']} {$c['count']} {$c['grade']} in {$scope}";
        }

        $avgCond = $rule['average_condition'] ?? [];
        if (!empty($avgCond['enabled'])) {
            $logic = $avgCond['logic'] ?? 'AND';
            $minAvg = $avgCond['min_average'] ?? '?';
            $parts[] = "avg {$logic} ≥{$minAvg}%";
        }

        return implode('; ', $parts) ?: 'No conditions';
    }

    private function legacyEvaluate(int $studentId, int $schoolclassid, int $termid, int $sessionid, $scores, ?float $overallAverage, bool $isSenior): array
    {
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

        $creditGrades = $isSenior ? ['A1', 'B2', 'B3', 'C4', 'C5', 'C6'] : ['A', 'B', 'C'];
        $failGrades = $isSenior ? ['F9', 'E8'] : ['F'];
        $dGrade = $isSenior ? 'D7' : 'D';

        $compulsorySubjectIds = $compulsoryRules->pluck('subjectId')->toArray();
        $compulsoryCreditCount = 0;
        $creditCount = 0;
        $failCount = 0;
        $missingCompulsorySubjects = [];
        $failedComp = [];
        $detail = [];
        $passedComp = 0;

        foreach ($compulsoryRules as $rule) {
            $subjectId = $rule->subjectId;
            $scoreEntry = $scoreMap->get($subjectId);
            $grade = $scoreEntry
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
                    'subject_id' => $subjectId,
                    'subject' => $subjectName,
                    'grade' => $grade,
                    'min_grade' => $isSenior ? 'C6' : 'C',
                    'not_sat' => !$scoreEntry,
                ];
            } else {
                $passedComp++;
            }
            $detail[] = [
                'subject_id' => $subjectId,
                'subject' => $subjectName,
                'grade' => $grade,
                'min_grade' => $isSenior ? 'C6' : 'C',
                'passed' => $didPass,
                'not_sat' => !$scoreEntry,
            ];
        }

        foreach ($scoreCol as $score) {
            $grade = is_object($score) ? $score->grade : $score['grade'];
            if (in_array($grade, $creditGrades)) $creditCount++;
            elseif (in_array($grade, $failGrades)) $failCount++;
        }

        $allDs = $scoreCol->isNotEmpty() && $scoreCol->every(fn($s) => (is_object($s) ? $s->grade : $s['grade']) === $dGrade);
        $mixOfDsAndFs = $scoreCol->isNotEmpty() && $scoreCol->every(function ($s) use ($dGrade, $failGrades) {
            $g = is_object($s) ? $s->grade : $s['grade'];
            return $g === $dGrade || in_array($g, $failGrades);
        });

        $failedNonCompulsory = $scoreCol->filter(function ($s) use ($compulsorySubjectIds, $failGrades) {
            $sid = is_object($s) ? $s->subject_id : $s['subject_id'];
            $g = is_object($s) ? $s->grade : $s['grade'];
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
            self::STATUS_PROMOTED => 'Promoted',
            self::STATUS_TRIAL => 'Promoted on Trial',
            self::STATUS_SEE_PRINCIPAL => 'Parents to See Principal',
            self::STATUS_REPEATED => 'Advice to Repeat',
        ];

        return [
            'status' => $status,
            'status_label' => $labelMap[$status] ?? 'Awaiting Decision',
            'is_promotional_term' => true,
            'failed_compulsory' => $failedComp,
            'compulsory_subject_detail' => $detail,
            'average_failed' => false,
            'required_average' => null,
            'actual_average' => $overallAverage,
            'compulsory_count' => $compTotal,
            'passed_compulsory' => $passedComp,
            'matched_labels' => [],
            'applied_rule' => null,
            'settings_id' => null,
            'rule_logic' => 'legacy',
            'settings' => null,
        ];
    }

    private function awaitingResult(?float $overallAverage): array
    {
        return [
            'status' => self::STATUS_AWAITING,
            'status_label' => 'Awaiting Decision',
            'is_promotional_term' => false,
            'failed_compulsory' => [],
            'compulsory_subject_detail' => [],
            'average_failed' => false,
            'required_average' => null,
            'actual_average' => $overallAverage,
            'compulsory_count' => 0,
            'passed_compulsory' => 0,
            'matched_labels' => [],
            'applied_rule' => null,
            'settings_id' => null,
            'rule_logic' => null,
            'settings' => null,
        ];
    }

    public function getStatusBadgeClass(string $status): string
    {
        return match ($status) {
            self::STATUS_PROMOTED => 'bg-success',
            self::STATUS_TRIAL => 'bg-warning',
            self::STATUS_SEE_PRINCIPAL => 'bg-info',
            self::STATUS_REPEATED => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function getStatusIcon(string $status): string
    {
        return match ($status) {
            self::STATUS_PROMOTED => 'ri-checkbox-circle-line',
            self::STATUS_TRIAL => 'ri-time-line',
            self::STATUS_SEE_PRINCIPAL => 'ri-eye-line',
            self::STATUS_REPEATED => 'ri-repeat-line',
            default => 'ri-question-line',
        };
    }
}
