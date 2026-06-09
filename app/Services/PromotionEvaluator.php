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

    // Senior grade scale (exact notation)
    private static array $seniorGrades = ['A1', 'B2', 'B3', 'C4', 'C5', 'C6', 'D7', 'E8', 'F9'];

    // Junior grade scale (grouped letters)
    private static array $juniorGrades = ['A', 'B', 'C', 'D', 'F'];

    // Senior grade order for comparison (higher = better)
    private static array $seniorGradeOrder = [
        'F9' => 0, 'E8' => 1, 'D7' => 2,
        'C6' => 3, 'C5' => 4, 'C4' => 5,
        'B3' => 6, 'B2' => 7, 'A1' => 8,
    ];

    // Junior grade order for comparison (higher = better)
    private static array $juniorGradeOrder = [
        'F' => 0, 'D' => 1, 'C' => 2, 'B' => 3, 'A' => 4,
    ];

    /**
     * Conversion map: senior-format grade → equivalent junior letter grade.
     * Used when a junior class has senior-style grades stored in the broadsheet
     * (e.g. historical data entered before the grading scale was corrected).
     */
    private static array $gradeConversionMap = [
        'A1' => 'A',
        'B2' => 'B', 'B3' => 'B',
        'C4' => 'C', 'C5' => 'C', 'C6' => 'C',
        'D7' => 'D',
        'E8' => 'F', 'F9' => 'F',
    ];

    // ── Pattern to detect senior-format grades ────────────────────────────────
    // Matches A1, B2, B3, C4, C5, C6, D7, E8, F9
    private const SENIOR_GRADE_PATTERN = '/^[ABCDE][1-9]$|^F9$/';

    // =========================================================================
    // PUBLIC API
    // =========================================================================

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

        $classCategory    = $this->getClassCategory($schoolclassid);
        $dbIsSenior       = $classCategory ? (bool) $classCategory->is_senior : false;

        // Detect actual grading type from both the DB flag AND the stored grades.
        // A junior class (is_senior=false) may have senior-format grades in the
        // broadsheet due to historical data entry.  detectGradingType() resolves
        // this by inspecting a sample of real grades.
        $usesSeniorGrading = $this->detectGradingType($schoolclassid, $scores, $dbIsSenior);

        Log::info('Class category info', [
            'class_id'                => $schoolclassid,
            'db_is_senior'            => $dbIsSenior,
            'detected_grading_type'   => $usesSeniorGrading ? 'senior' : 'junior',
            'grades_format_in_db'     => $usesSeniorGrading ? 'senior (A1-F9)' : 'junior (A-F)',
            'category'                => $classCategory->category ?? 'unknown',
            'promotion_pass_average'  => $classCategory->promotion_pass_average ?? null,
        ]);

        $settings = $this->findBestSettings($schoolclassid, $sessionid, $termid);

        Log::info('Settings lookup result', [
            'found'       => $settings ? 'yes' : 'no',
            'settings_id' => $settings?->id,
            'is_active'   => $settings?->is_active,
            'rule_logic'  => $settings?->rule_logic,
            'rules_count' => $settings && $settings->promotion_rules ? count($settings->promotion_rules) : 0,
        ]);

        if (! $settings) {
            Log::warning('No active promotion setting found, using legacy evaluation');
            $term = Schoolterm::find($termid);
            if (! $term || ! $term->is_promotional) {
                return $this->awaitingResult($overallAverage);
            }
            return $this->legacyEvaluate(
                $studentId, $schoolclassid, $termid, $sessionid,
                $scores, $overallAverage, $usesSeniorGrading
            );
        }

        $scoreMap      = $this->buildScoreMap($scores);
        $compulsoryIds = $this->getCompulsoryIds($schoolclassid, $termid, $sessionid);
        $ruleLogic     = $settings->rule_logic ?? 'grade_count';

        Log::info('Class info', [
            'uses_senior_grading'      => $usesSeniorGrading,
            'compulsory_subjects_count' => count($compulsoryIds),
            'compulsory_ids'           => $compulsoryIds,
            'scores_in_map'            => $scoreMap->count(),
        ]);

        $matchedRule   = null;
        $matchedStatus = null;
        $matchedIndex  = null;

        $rules = $settings->promotion_rules ?? [];

        Log::info('Evaluating rules', [
            'total_rules' => count($rules),
            'rule_logic'  => $ruleLogic,
        ]);

        if (in_array($ruleLogic, ['grade_count', 'both']) && ! empty($rules)) {
            foreach ($rules as $idx => $rule) {
                $ruleName = $rule['rule_name'] ?? 'Unnamed';
                Log::info('Checking rule ' . ($idx + 1), [
                    'rule_name'     => $ruleName,
                    'status_label'  => $rule['status_label'] ?? 'unknown',
                    'grade_grouping'=> $rule['grade_grouping'] ?? 'grouped',
                ]);

                if (empty($rule['rule_name'])) {
                    Log::warning('Rule ' . ($idx + 1) . ' has no name, skipping');
                    continue;
                }

                $matches = $this->ruleMatches($rule, $scoreMap, $compulsoryIds, $usesSeniorGrading);

                Log::info('Rule evaluation result', [
                    'rule_name' => $ruleName,
                    'matches'   => $matches,
                ]);

                if ($matches) {
                    $matchedRule   = $rule;
                    $matchedStatus = $rule['status_label'] ?? self::STATUS_PROMOTED;
                    $matchedIndex  = $idx;
                    Log::info('Rule MATCHED!', ['rule_name' => $ruleName, 'status' => $matchedStatus]);
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
            $ruleLogic, $matchedStatus, $matchedRule, $averageConditionMet, $averageStatus
        );

        Log::info('Final status resolved', [
            'final_status'     => $finalStatus,
            'matched_rule_name'=> $matchedRule['rule_name'] ?? null,
        ]);

        [$failedCompulsory, $compulsoryDetail, $passedCount, $totalCount]
            = $this->buildCompulsoryDetail($schoolclassid, $termid, $sessionid, $scoreMap, $usesSeniorGrading);

        $appliedRuleSummary = null;
        if ($matchedRule !== null) {
            $appliedRuleSummary = [
                'name'        => $matchedRule['rule_name'],
                'description' => $this->describeRule($matchedRule, $usesSeniorGrading),
                'index'       => $matchedIndex + 1,
            ];
        }

        Log::info('========== PROMOTION EVALUATION END ==========', ['final_status' => $finalStatus]);

        return [
            'status'                    => $finalStatus,
            'status_label'              => $this->mapStatusLabel($finalStatus, $settings),
            'is_promotional_term'       => true,
            'failed_compulsory'         => $failedCompulsory,
            'compulsory_subject_detail' => $compulsoryDetail,
            'average_failed'            => ! $averageConditionMet,
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
    // GRADE NORMALIZATION
    // =========================================================================

    /**
     * Detect whether a class uses senior or junior grading.
     *
     * Strategy (in priority order):
     *  1. DB flag ($dbIsSenior) is the authoritative source when grades in the
     *     broadsheet actually match it.
     *  2. If the DB says "junior" but the sampled grades look senior-format
     *     (A1, B2, E8…), the broadsheet was written with the senior scale —
     *     we treat the class as senior-graded FOR EVALUATION PURPOSES so that
     *     comparisons use the correct order table.  We do NOT change the DB.
     *  3. If there are no scores, fall back to the DB flag.
     *
     * This prevents mismatches like E8 being looked up in $juniorGradeOrder
     * (where it doesn't exist) and silently returning rank -1.
     */
    private function detectGradingType(int $schoolclassid, $scores, bool $dbIsSenior): bool
    {
        // No scores → trust the DB flag
        if (! $scores || (is_countable($scores) && count($scores) === 0)) {
            return $dbIsSenior;
        }

        // Sample up to 10 grades from the actual scores
        $sampleGrades  = [];
        $seniorCount   = 0;
        $juniorCount   = 0;
        $sampleLimit   = 10;

        foreach ($scores as $score) {
            $grade = is_object($score) ? ($score->grade ?? null) : ($score['grade'] ?? null);
            if (! $grade) continue;
            $g = strtoupper(trim($grade));
            $sampleGrades[] = $g;

            if (preg_match(self::SENIOR_GRADE_PATTERN, $g)) {
                $seniorCount++;
            } elseif (isset(self::$juniorGradeOrder[$g])) {
                $juniorCount++;
            }

            if (count($sampleGrades) >= $sampleLimit) break;
        }

        $total = $seniorCount + $juniorCount;

        Log::info('Grade format detection', [
            'db_is_senior'  => $dbIsSenior,
            'sample_grades' => $sampleGrades,
            'senior_count'  => $seniorCount,
            'junior_count'  => $juniorCount,
        ]);

        if ($total === 0) {
            // No recognisable grades in sample → trust DB
            return $dbIsSenior;
        }

        $detectedAsSenior = ($seniorCount / $total) > 0.5;

        if ($detectedAsSenior !== $dbIsSenior) {
            Log::warning('Grade format mismatch between DB flag and actual broadsheet grades', [
                'db_is_senior'        => $dbIsSenior,
                'detected_as_senior'  => $detectedAsSenior,
                'action'              => 'using detected format for evaluation; broadsheet data unchanged',
            ]);
        }

        return $detectedAsSenior;
    }

    /**
     * Normalize a grade to match the grading scale in use.
     *
     * - If the class is junior but the grade is senior-format (e.g. C4 stored
     *   in broadsheet), convert it to the junior letter equivalent.
     * - If the class is senior or the grade already matches the expected
     *   format, return it unchanged (uppercased).
     */
    private function normalizeGrade(?string $grade, bool $isSenior): ?string
    {
        if ($grade === null) return null;

        $grade = strtoupper(trim($grade));

        if (! $isSenior && isset(self::$gradeConversionMap[$grade])) {
            $normalized = self::$gradeConversionMap[$grade];
            Log::debug('Grade normalized (senior→junior)', [
                'original'   => $grade,
                'normalized' => $normalized,
            ]);
            return $normalized;
        }

        return $grade;
    }

    // =========================================================================
    // SETTINGS RESOLUTION
    // =========================================================================

    private function getClassCategory(int $schoolclassid): ?object
    {
        return DB::table('schoolclass_classcategory')
            ->join('classcategories', 'classcategories.id', '=', 'schoolclass_classcategory.classcategory_id')
            ->where('schoolclass_classcategory.schoolclass_id', $schoolclassid)
            ->select(
                'classcategories.id',
                'classcategories.category',
                'classcategories.is_senior',
                'classcategories.promotion_pass_average'
            )
            ->first();
    }

    private function findBestSettings(int $schoolclassid, int $sessionid, int $termid): ?PromotionSetting
    {
        Log::info('Finding best settings', [
            'class_id'   => $schoolclassid,
            'session_id' => $sessionid,
            'term_id'    => $termid,
        ]);

        $allSettings = PromotionSetting::where('schoolclass_id', $schoolclassid)
            ->where('is_active', true)
            ->get();

        Log::info('All active settings for class', [
            'count'    => $allSettings->count(),
            'settings' => $allSettings->map(fn ($s) => [
                'id'          => $s->id,
                'session_id'  => $s->session_id,
                'term_id'     => $s->term_id,
                'priority'    => $s->priority,
                'rules_count' => count($s->promotion_rules ?? []),
            ])->toArray(),
        ]);

        if ($allSettings->isEmpty()) {
            return null;
        }

        $scored = [];

        foreach ($allSettings as $setting) {
            [$score, $matchType] = $this->scoreSettings($setting, $sessionid, $termid);
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
            if ($a['score'] !== $b['score']) return $b['score'] - $a['score'];
            return $a['priority'] - $b['priority'];
        });

        $best = $scored[0]['setting'];

        Log::info('Selected best setting', [
            'setting_id' => $best->id,
            'match_type' => $scored[0]['match_type'],
            'score'      => $scored[0]['score'],
            'session_id' => $best->session_id,
            'term_id'    => $best->term_id,
        ]);

        return $best;
    }

    /** Assign a match-quality score to a PromotionSetting for a given session+term. */
    private function scoreSettings(PromotionSetting $setting, int $sessionid, int $termid): array
    {
        if ($setting->session_id == $sessionid && $setting->term_id == $termid) {
            return [100, 'exact_session_term'];
        }
        if ($setting->session_id == $sessionid && $setting->term_id === null) {
            return [90, 'session_only_null_term'];
        }
        if ($setting->session_id == $sessionid && $setting->term_id !== null && $setting->term_id != $termid) {
            return [0, 'wrong_term'];
        }
        if ($setting->session_id === null && $setting->term_id == $termid) {
            return [80, 'term_only'];
        }
        if ($setting->session_id === null && $setting->term_id === null) {
            return [70, 'global'];
        }
        if ($setting->session_id !== null && $setting->term_id === null) {
            return [60, 'session_only_different'];
        }
        if ($setting->session_id === null && $setting->term_id !== null) {
            return [50, 'term_only_different'];
        }
        return [0, 'no_match'];
    }

    // =========================================================================
    // RULE MATCHING
    // =========================================================================

    private function ruleMatches(
        array      $rule,
        Collection $scoreMap,
        array      $compulsoryIds,
        bool       $isSenior
    ): bool {
        $grouping = $rule['grade_grouping'] ?? 'grouped';

        // Senior classes always use exact grade notation — force exact grouping
        // so that a condition "≥ 5 B2" isn't conflated with "≥ 5 B"
        if ($isSenior && $grouping === 'grouped') {
            $grouping = 'exact';
            Log::debug('Senior class detected — forcing exact grade grouping');
        }

        Log::debug('Checking rule conditions', [
            'rule_name'            => $rule['rule_name'] ?? 'Unnamed',
            'grouping'             => $grouping,
            'is_senior'            => $isSenior,
            'comp_subjects_count'  => count($rule['compulsory_section']['subjects'] ?? []),
            'comp_cond_count'      => count($rule['compulsory_section']['count_conditions'] ?? []),
            'other_cond_count'     => count($rule['other_section']['count_conditions'] ?? []),
        ]);

        // ── Section 1: Per-subject minimum grades (compulsory subjects) ──────
        foreach ($rule['compulsory_section']['subjects'] ?? [] as $subjectRule) {
            $minGrade = $subjectRule['min_grade'] ?? null;
            if (! $minGrade) continue;  // "— Any" selected → skip this subject

            $subjectId    = $subjectRule['subject_id'] ?? null;
            $scoreEntry   = $subjectId ? $scoreMap->get($subjectId) : null;
            $studentGrade = $scoreEntry
                ? (is_object($scoreEntry) ? ($scoreEntry->grade ?? null) : ($scoreEntry['grade'] ?? null))
                : null;

            $normalizedStudent = $this->normalizeGrade($studentGrade, $isSenior);
            $normalizedMin     = $this->normalizeGrade($minGrade, $isSenior);

            if ($this->gradeFails($normalizedStudent, $normalizedMin, $isSenior, $grouping)) {
                Log::debug('Rule failed: compulsory subject min grade not met', [
                    'subject_id'              => $subjectId,
                    'raw_student_grade'       => $studentGrade,
                    'normalized_student_grade'=> $normalizedStudent,
                    'required_min_grade'      => $minGrade,
                    'normalized_min_grade'    => $normalizedMin,
                ]);
                return false;
            }
        }

        // ── Section 2: Count conditions on compulsory subjects ───────────────
        if (! $this->evaluateCountConditions(
            $rule['compulsory_section']['count_conditions'] ?? [],
            $scoreMap, $compulsoryIds, $grouping, $isSenior
        )) {
            Log::debug('Rule failed: compulsory count conditions not met');
            return false;
        }

        // ── Section 3: Count conditions on other / all subjects ──────────────
        if (! $this->evaluateCountConditions(
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

            if (! $grade) continue;

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

            if (! $result) {
                return false;
            }
        }

        return true;
    }

    /**
     * Count how many scores in $scopedScores meet or exceed the required $grade.
     *
     * Key behaviour for junior classes with mixed-format broadsheets:
     *  - normalizeGrade() is always called first, converting any senior-format
     *    grade (e.g. B3 → B, C4 → C) to the junior letter before comparison.
     *  - For junior classes the comparison is ">= required rank" so that a
     *    student with B (rank 3) satisfies a condition of "≥ C" (rank 2).
     *  - For senior classes the condition grade is always an exact match because
     *    the rule builder stores exact grades (A1, B2, …) and the evaluator
     *    forces exact grouping for senior classes in ruleMatches().
     */
    private function countMatchingGrade(
        Collection $scopedScores,
        string     $grade,
        string     $grouping,
        bool       $isSenior
    ): int {
        $count         = 0;
        $requiredGrade = strtoupper(trim($grade));

        // For junior classes, normalize the required grade too (handles the
        // edge case where a rule was saved with a senior-format grade label).
        $normalizedRequired = $this->normalizeGrade($requiredGrade, $isSenior);
        if (! $normalizedRequired) return 0;

        foreach ($scopedScores as $score) {
            $rawGrade = is_object($score) ? ($score->grade ?? null) : ($score['grade'] ?? null);
            if (! $rawGrade) continue;

            // Always normalize — converts senior-format grades stored in the
            // broadsheet to the appropriate scale for this class.
            $normalizedGrade = $this->normalizeGrade($rawGrade, $isSenior);
            if (! $normalizedGrade) continue;

            $normalizedGrade = strtoupper(trim($normalizedGrade));

            if ($isSenior) {
                // Senior: compare using exact senior grade order
                $studentRank  = self::$seniorGradeOrder[$normalizedGrade]   ?? -1;
                $requiredRank = self::$seniorGradeOrder[$normalizedRequired] ?? 0;
                if ($studentRank >= $requiredRank) {
                    $count++;
                }
            } else {
                // Junior: compare using junior letter grade order
                // normalizeGrade() has already converted any B3/C4/E8 etc. → B/C/F
                $studentRank  = self::$juniorGradeOrder[$normalizedGrade]   ?? -1;
                $requiredRank = self::$juniorGradeOrder[$normalizedRequired] ?? 0;
                if ($studentRank >= $requiredRank) {
                    $count++;
                }
            }
        }

        Log::debug('countMatchingGrade result', [
            'required_raw'        => $grade,
            'required_normalized' => $normalizedRequired,
            'is_senior'           => $isSenior,
            'grouping'            => $grouping,
            'count'               => $count,
            'total_scoped'        => $scopedScores->count(),
        ]);

        return $count;
    }

    /**
     * Determine whether a student grade FAILS to meet the minimum requirement.
     * Both $studentGrade and $minGrade must already be normalized before calling.
     */
    private function gradeFails(?string $studentGrade, ?string $minGrade, bool $isSenior, string $grouping = 'exact'): bool
    {
        if ($studentGrade === null) return true;

        $sg = strtoupper(trim($studentGrade));

        if (empty($minGrade)) {
            // No specific minimum — only explicit fail grades count as failure
            $failGrades = $isSenior ? ['F9'] : ['F'];
            return in_array($sg, $failGrades, true);
        }

        $mg = strtoupper(trim($minGrade));

        if ($isSenior) {
            $studentRank = self::$seniorGradeOrder[$sg] ?? -1;
            $minRank     = self::$seniorGradeOrder[$mg] ?? 0;
        } else {
            $studentRank = self::$juniorGradeOrder[$sg] ?? -1;
            $minRank     = self::$juniorGradeOrder[$mg] ?? 0;
        }

        $fails = $studentRank < $minRank;

        Log::debug('gradeFails check', [
            'student_grade' => $sg,
            'min_grade'     => $mg,
            'student_rank'  => $studentRank,
            'min_rank'      => $minRank,
            'fails'         => $fails,
            'is_senior'     => $isSenior,
        ]);

        return $fails;
    }

    // =========================================================================
    // HELPER METHODS
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
                'other_only'      => ! $isComp,
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

    private function getCompulsoryIds(int $schoolclassid, int $termid, int $sessionid): array
    {
        $ids = CompulsorySubjectClass::where('schoolclassid', $schoolclassid)
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
            ->map(fn ($id) => (string) $id)
            ->toArray();

        Log::debug('Compulsory subject IDs', ['ids' => $ids]);
        return $ids;
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
            $studentGrade = is_object($scoreEntry) ? ($scoreEntry->grade ?? null) : null;
            $subjectName  = is_object($scoreEntry) ? ($scoreEntry->subject_name ?? null) : null;
            $minGrade     = $rule->min_grade;

            $normalizedStudent = $this->normalizeGrade($studentGrade, $isSenior);
            $normalizedMin     = $this->normalizeGrade($minGrade, $isSenior);

            $didPass = false;
            if ($scoreEntry && $normalizedStudent) {
                $order       = $isSenior ? self::$seniorGradeOrder : self::$juniorGradeOrder;
                $studentRank = $order[$normalizedStudent] ?? -1;
                $minRank     = $order[$normalizedMin]     ?? 0;
                $didPass     = $studentRank >= $minRank;
            }

            if (! $didPass) {
                $failed[] = [
                    'subject_id'            => $subjectId,
                    'subject'               => $subjectName,
                    'grade'                 => $studentGrade,
                    'normalized_grade'      => $normalizedStudent,
                    'min_grade'             => $minGrade,
                    'normalized_min_grade'  => $normalizedMin,
                    'not_sat'               => ! $scoreEntry,
                ];
            } else {
                $passed++;
            }

            $detail[] = [
                'subject_id'            => $subjectId,
                'subject'               => $subjectName,
                'grade'                 => $studentGrade,
                'normalized_grade'      => $normalizedStudent,
                'min_grade'             => $minGrade,
                'normalized_min_grade'  => $normalizedMin,
                'passed'                => $didPass,
                'not_sat'               => ! $scoreEntry,
            ];
        }

        return [$failed, $detail, $passed, $total];
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

    private function evaluateAverage(
        string $ruleLogic,
        ?float $requiredAverage,
        ?float $overallAverage
    ): array {
        if (! in_array($ruleLogic, ['average_only', 'both'])) {
            return [true, null];
        }
        if ($requiredAverage === null || $overallAverage === null) {
            Log::debug('Cannot evaluate average — missing data', [
                'required' => $requiredAverage,
                'actual'   => $overallAverage,
            ]);
            return [true, null];
        }
        $met = $overallAverage >= $requiredAverage;
        Log::debug('Average evaluation', [
            'required' => $requiredAverage,
            'actual'   => $overallAverage,
            'met'      => $met,
        ]);
        return [$met, $met ? self::STATUS_PROMOTED : self::STATUS_REPEATED];
    }

    private function resolveFinalStatus(
        string  $ruleLogic,
        ?string $matchedStatus,
        ?array  $matchedRule,
        bool    $averageConditionMet,
        ?string $averageStatus
    ): string {
        Log::debug('Resolving final status', [
            'rule_logic'            => $ruleLogic,
            'matched_status'        => $matchedStatus,
            'average_condition_met' => $averageConditionMet,
            'average_status'        => $averageStatus,
        ]);

        return match ($ruleLogic) {
            'average_only' => $averageStatus ?? self::STATUS_AWAITING,
            'grade_count'  => $matchedStatus ?? self::STATUS_REPEATED,
            'both' => match (true) {
                $matchedStatus !== null && $averageConditionMet  => $matchedStatus,
                $matchedStatus !== null && ! $averageConditionMet => self::STATUS_TRIAL,
                $matchedStatus === null && $averageConditionMet  => self::STATUS_PROMOTED,
                default                                           => self::STATUS_REPEATED,
            },
            default => $matchedStatus ?? self::STATUS_REPEATED,
        };
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
        $withMin  = array_filter($subjects, fn ($s) => ! empty($s['min_grade']));
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
        if (! empty($avgCond['enabled'])) {
            $logic   = $avgCond['logic']       ?? 'AND';
            $minAvg  = $avgCond['min_average'] ?? '?';
            $parts[] = "avg {$logic} ≥{$minAvg}%";
        }

        return implode('; ', $parts) ?: 'No conditions';
    }

    // =========================================================================
    // LEGACY EVALUATION (fallback when no PromotionSetting exists)
    // =========================================================================

    private function legacyEvaluate(
        int    $studentId,
        int    $schoolclassid,
        int    $termid,
        int    $sessionid,
               $scores,
        ?float $overallAverage,
        bool   $isSenior
    ): array {
        Log::info('Using legacy evaluation');

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

        $compulsorySubjectIds   = $compulsoryRules->pluck('subjectId')->toArray();
        $compulsoryCreditCount  = 0;
        $creditCount            = 0;
        $failCount              = 0;
        $missingCompulsory      = [];
        $failedComp             = [];
        $detail                 = [];
        $passedComp             = 0;

        foreach ($compulsoryRules as $rule) {
            $subjectId       = $rule->subjectId;
            $scoreEntry      = $scoreMap->get($subjectId);
            $grade           = $scoreEntry
                ? (is_object($scoreEntry) ? $scoreEntry->grade : $scoreEntry['grade'])
                : null;
            $subjectName     = $scoreEntry
                ? (is_object($scoreEntry) ? ($scoreEntry->subject_name ?? null) : null)
                : null;
            $normalizedGrade = $this->normalizeGrade($grade, $isSenior);

            if (! $scoreEntry) {
                $missingCompulsory[] = $subjectName ?? "Subject #{$subjectId}";
            } elseif (in_array($normalizedGrade, $creditGrades)) {
                $compulsoryCreditCount++;
            }

            $didPass = $scoreEntry && in_array($normalizedGrade, $creditGrades);
            if (! $didPass) {
                $failedComp[] = [
                    'subject_id'       => $subjectId,
                    'subject'          => $subjectName,
                    'grade'            => $grade,
                    'normalized_grade' => $normalizedGrade,
                    'min_grade'        => $isSenior ? 'C6' : 'C',
                    'not_sat'          => ! $scoreEntry,
                ];
            } else {
                $passedComp++;
            }

            $detail[] = [
                'subject_id'       => $subjectId,
                'subject'          => $subjectName,
                'grade'            => $grade,
                'normalized_grade' => $normalizedGrade,
                'min_grade'        => $isSenior ? 'C6' : 'C',
                'passed'           => $didPass,
                'not_sat'          => ! $scoreEntry,
            ];
        }

        foreach ($scoreCol as $score) {
            $grade           = is_object($score) ? $score->grade : $score['grade'];
            $normalizedGrade = $this->normalizeGrade($grade, $isSenior);
            if (in_array($normalizedGrade, $creditGrades))   $creditCount++;
            elseif (in_array($normalizedGrade, $failGrades)) $failCount++;
        }

        $allDs = $scoreCol->isNotEmpty() && $scoreCol->every(
            fn ($s) => $this->normalizeGrade(is_object($s) ? $s->grade : $s['grade'], $isSenior) === $dGrade
        );

        $mixOfDsAndFs = $scoreCol->isNotEmpty() && $scoreCol->every(function ($s) use ($dGrade, $failGrades, $isSenior) {
            $g = is_object($s) ? $s->grade : $s['grade'];
            $n = $this->normalizeGrade($g, $isSenior);
            return $n === $dGrade || in_array($n, $failGrades);
        });

        $failedNonCompulsory = $scoreCol->filter(function ($s) use ($compulsorySubjectIds, $failGrades, $isSenior) {
            $sid = is_object($s) ? $s->subject_id : $s['subject_id'];
            $g   = is_object($s) ? $s->grade : $s['grade'];
            $n   = $this->normalizeGrade($g, $isSenior);
            return ! in_array($sid, $compulsorySubjectIds) && in_array($n, $failGrades);
        })->count() === max(0, $scoreCol->count() - count($compulsorySubjectIds));

        $compTotal = $compulsoryRules->count();

        if (! empty($missingCompulsory)) {
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

        $reqAvg  = $this->getClassCategory($schoolclassid)?->promotion_pass_average;
        $avgFail = $reqAvg !== null && $overallAverage !== null && $overallAverage < $reqAvg;

        Log::info('Legacy evaluation result', [
            'status'                   => $status,
            'credit_count'             => $creditCount,
            'compulsory_credit_count'  => $compulsoryCreditCount,
            'fail_count'               => $failCount,
            'comp_total'               => $compTotal,
        ]);

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
    // FALLBACK / UTILITY
    // =========================================================================

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
