<?php
// app/Services/PromotionEvaluator.php

namespace App\Services;

use App\Models\CompulsorySubjectClass;
use App\Models\PromotionSetting;
use App\Models\Schoolclass;
use App\Models\Schoolterm;
use Illuminate\Support\Facades\DB;

class PromotionEvaluator
{
    const STATUS_PROMOTED = 'promoted';
    const STATUS_TRIAL = 'trial';
    const STATUS_SEE_PRINCIPAL = 'see_principal';
    const STATUS_REPEATED = 'repeated';
    const STATUS_AWAITING = 'awaiting';

    private static array $gradeOrder = [
        'F9' => 0, 'E8' => 1, 'D7' => 2,
        'C6' => 3, 'C5' => 4, 'C4' => 5,
        'B3' => 6, 'B2' => 7, 'A1' => 8,
        'F'  => 0, 'D'  => 2, 'C'  => 5, 'B'  => 7, 'A'  => 8,
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
            return $this->result(self::STATUS_AWAITING, false, [], false, null, $overallAverage, 0, 0, null, 'Awaiting Decision');
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
            return $this->legacyEvaluate($studentId, $schoolclassid, $termid, $sessionid, $scores, $overallAverage);
        }

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

        $scoreMap = collect($scores)->keyBy(fn($s) => is_object($s) ? ($s->subject_id ?? null) : ($s['subject_id'] ?? null));

        $passedCount = 0;
        $totalCount = $compulsoryRules->count();
        $failedCompulsoryList = [];

        foreach ($compulsoryRules as $rule) {
            $subjectId = $rule->subjectId;
            $minGrade = $rule->min_grade;
            $scoreEntry = $scoreMap->get($subjectId);

            $passed = false;
            if ($scoreEntry) {
                $studentGrade = is_object($scoreEntry) ? $scoreEntry->grade : ($scoreEntry['grade'] ?? null);
                if (!$this->gradeFails($studentGrade, $minGrade)) {
                    $passed = true;
                    $passedCount++;
                }
            }

            if (!$passed) {
                $failedCompulsoryList[] = [
                    'subject_id' => $subjectId,
                    'subject' => is_object($scoreEntry) ? ($scoreEntry->subject_name ?? null) : null,
                    'grade' => is_object($scoreEntry) ? ($scoreEntry->grade ?? null) : null,
                    'min_grade' => $minGrade,
                ];
            }
        }

        $compulsoryConditionMet = ($settings->min_compulsory_pass === null) || ($passedCount >= $settings->min_compulsory_pass);
        $averageConditionMet = true;
        $determinedStatus = null;

        if ($settings->rule_type === 'average_only' || $settings->rule_type === 'both') {
            if ($overallAverage !== null) {
                if ($settings->promotion_pass_average !== null && $overallAverage >= $settings->promotion_pass_average) {
                    $determinedStatus = self::STATUS_PROMOTED;
                    $averageConditionMet = true;
                } elseif ($settings->trial_pass_average !== null && $overallAverage >= $settings->trial_pass_average) {
                    $determinedStatus = self::STATUS_TRIAL;
                    $averageConditionMet = true;
                } elseif ($settings->see_principal_average !== null && $overallAverage >= $settings->see_principal_average) {
                    $determinedStatus = self::STATUS_SEE_PRINCIPAL;
                    $averageConditionMet = false;
                } else {
                    $determinedStatus = self::STATUS_REPEATED;
                    $averageConditionMet = false;
                }
            }
        }

        $finalStatus = null;

        if ($settings->rule_type === 'compulsory_only') {
            $finalStatus = $compulsoryConditionMet ? self::STATUS_PROMOTED : $settings->compulsory_fail_action;
        } elseif ($settings->rule_type === 'average_only') {
            $finalStatus = $determinedStatus ?? self::STATUS_REPEATED;
        } else {
            if ($settings->combined_logic === 'and') {
                if ($compulsoryConditionMet && $averageConditionMet) {
                    $finalStatus = self::STATUS_PROMOTED;
                } elseif (!$compulsoryConditionMet && !$averageConditionMet) {
                    $finalStatus = self::STATUS_REPEATED;
                } elseif (!$compulsoryConditionMet) {
                    $finalStatus = $settings->compulsory_fail_action;
                } else {
                    $finalStatus = $determinedStatus ?? self::STATUS_REPEATED;
                }
            } else {
                if ($compulsoryConditionMet || $averageConditionMet) {
                    $finalStatus = self::STATUS_PROMOTED;
                } else {
                    $finalStatus = self::STATUS_REPEATED;
                }
            }
        }

        $statusLabel = match($finalStatus) {
            self::STATUS_PROMOTED => $settings->promoted_label,
            self::STATUS_TRIAL => $settings->trial_label,
            self::STATUS_SEE_PRINCIPAL => $settings->see_principal_label,
            self::STATUS_REPEATED => $settings->repeat_label,
            default => 'Awaiting Decision',
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
            $statusLabel
        );
    }

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

        $requiredAverage = null;
        $averageFailed = false;

        $passAverage = DB::table('schoolclass_classcategory')
            ->where('schoolclass_id', $schoolclassid)
            ->value('promotion_pass_average');

        if ($passAverage !== null && $overallAverage !== null) {
            $requiredAverage = (float) $passAverage;
            $averageFailed = $overallAverage < $requiredAverage;
        }

        if ($compulsoryRules->isEmpty()) {
            $status = $averageFailed ? self::STATUS_REPEATED : self::STATUS_PROMOTED;
            $label = $status === self::STATUS_PROMOTED ? 'Promoted' : 'Advice to Repeat';
            return $this->result($status, true, [], $averageFailed, $requiredAverage, $overallAverage, 0, 0, null, $label);
        }

        $scoreMap = collect($scores)->keyBy(fn($s) => is_object($s) ? ($s->subject_id ?? null) : ($s['subject_id'] ?? null));
        $failedCompulsory = [];
        $passedCount = 0;
        $totalCount = $compulsoryRules->count();

        foreach ($compulsoryRules as $rule) {
            $subjectId = $rule->subjectId;
            $minGrade = $rule->min_grade;
            $scoreEntry = $scoreMap->get($subjectId);

            if (!$scoreEntry) {
                $failedCompulsory[] = ['subject_id' => $subjectId, 'reason' => 'not_sat'];
                continue;
            }

            $studentGrade = is_object($scoreEntry) ? $scoreEntry->grade : ($scoreEntry['grade'] ?? null);
            if ($this->gradeFails($studentGrade, $minGrade)) {
                $failedCompulsory[] = ['subject_id' => $subjectId, 'grade' => $studentGrade, 'min_grade' => $minGrade];
            } else {
                $passedCount++;
            }
        }

        $compulsoryFailed = !empty($failedCompulsory);
        $status = ($compulsoryFailed || $averageFailed) ? self::STATUS_REPEATED : self::STATUS_PROMOTED;
        $label = $status === self::STATUS_PROMOTED ? 'Promoted' : 'Advice to Repeat';

        return $this->result($status, true, $failedCompulsory, $averageFailed, $requiredAverage, $overallAverage, $totalCount, $passedCount, null, $label);
    }

    private function gradeFails(?string $studentGrade, ?string $minGrade): bool
    {
        if ($studentGrade === null) return true;
        $studentGradeUpper = strtoupper(trim($studentGrade));

        if ($minGrade) {
            $minGradeUpper = strtoupper(trim($minGrade));
            $studentRank = self::$gradeOrder[$studentGradeUpper] ?? -1;
            $minRank = self::$gradeOrder[$minGradeUpper] ?? 0;
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
        ?string $statusLabel = null
    ): array {
        return [
            'status' => $status,
            'status_label' => $statusLabel ?? ucfirst(str_replace('_', ' ', $status)),
            'is_promotional_term' => $isPromotionalTerm,
            'failed_compulsory' => $failedCompulsory,
            'average_failed' => $averageFailed,
            'required_average' => $requiredAverage,
            'actual_average' => $actualAverage,
            'compulsory_count' => $compulsoryCount,
            'passed_compulsory' => $passedCompulsory,
            'settings' => $settings ? [
                'rule_type' => $settings->rule_type,
                'min_compulsory_pass' => $settings->min_compulsory_pass,
                'promotion_pass_average' => $settings->promotion_pass_average,
                'trial_pass_average' => $settings->trial_pass_average,
                'combined_logic' => $settings->combined_logic,
            ] : null,
        ];
    }

    public function getStatusBadgeClass($status): string
    {
        return match($status) {
            self::STATUS_PROMOTED => 'bg-success',
            self::STATUS_TRIAL => 'bg-warning',
            self::STATUS_SEE_PRINCIPAL => 'bg-info',
            self::STATUS_REPEATED => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function getStatusIcon($status): string
    {
        return match($status) {
            self::STATUS_PROMOTED => 'ri-checkbox-circle-line',
            self::STATUS_TRIAL => 'ri-time-line',
            self::STATUS_SEE_PRINCIPAL => 'ri-eye-line',
            self::STATUS_REPEATED => 'ri-repeat-line',
            default => 'ri-question-line',
        };
    }
}
