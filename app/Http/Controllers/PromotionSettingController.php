<?php
// app/Http/Controllers/PromotionSettingController.php

namespace App\Http\Controllers;

use App\Models\PromotionSetting;
use App\Models\Schoolclass;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\CompulsorySubjectClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PromotionSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View promotion|Update promotion');
    }

    public function index()
    {
        $pagetitle = "Promotion Settings Management";

        $settings = PromotionSetting::with(['schoolclass.arm', 'session', 'term'])
            ->orderBy('schoolclass_id')
            ->get();

        $schoolclasses = DB::table('schoolclass')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select('schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm as arm_name')
            ->get();

        $sessions = Schoolsession::orderBy('session', 'desc')->get();
        $terms    = Schoolterm::orderBy('term')->get();

        return view('promotions.settings', compact(
            'settings', 'schoolclasses', 'sessions', 'terms', 'pagetitle'
        ));
    }

    public function subjectsByClass(Request $request)
    {
        try {
            $classId   = $request->query('classid');
            $termId    = $request->query('termid');
            $sessionId = $request->query('sessionid');

            if (!$classId) {
                return response()->json(['success' => false, 'message' => 'Class required'], 422);
            }

            $sql    = "SELECT DISTINCT s.id, s.subject, s.subject_code
                       FROM subjectclass sc
                       INNER JOIN subjectteacher st ON st.id = sc.subjectteacherid
                       INNER JOIN subject s ON s.id = sc.subjectid
                       WHERE sc.schoolclassid = ?";
            $params = [$classId];

            if ($termId && $termId !== 'null' && $termId !== '') {
                $sql .= " AND st.termid = ?";
                $params[] = $termId;
            }
            if ($sessionId && $sessionId !== 'null' && $sessionId !== '') {
                $sql .= " AND st.sessionid = ?";
                $params[] = $sessionId;
            }

            $results  = DB::select($sql, $params);
            $subjects = array_map(fn($r) => [
                'id'           => (string) $r->id,
                'subject'      => $r->subject,
                'subject_code' => $r->subject_code,
            ], $results);

            return response()->json(['success' => true, 'subjects' => $subjects, 'total' => count($subjects)]);

        } catch (\Exception $e) {
            Log::error('Subjects by Class Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getClassPromotionData(Request $request)
    {
        try {
            $classId = $request->query('classid');
            if (!$classId) {
                return response()->json(['success' => false, 'message' => 'Class required'], 422);
            }

            $passAverage = DB::table('schoolclass_classcategory')
                ->where('schoolclass_id', $classId)
                ->value('promotion_pass_average');

            $gradeScale   = ['A1', 'B2', 'B3', 'C4', 'C5', 'C6', 'D7', 'E8', 'F9'];
            $categoryData = DB::table('schoolclass_classcategory')
                ->join('classcategories', 'classcategories.id', '=', 'schoolclass_classcategory.classcategory_id')
                ->where('schoolclass_classcategory.schoolclass_id', $classId)
                ->select('classcategories.is_senior', 'classcategories.category')
                ->first();

            $isSenior = true;
            if ($categoryData && isset($categoryData->is_senior) && !$categoryData->is_senior) {
                $gradeScale = ['A', 'B', 'C', 'D', 'F'];
                $isSenior   = false;
            }

            $compulsoryCount = DB::table('compulsory_subject_classes')
                ->where('schoolclassid', $classId)
                ->whereNull('termid')
                ->whereNull('sessionid')
                ->count();

            $totalSubjectCount = count(DB::select(
                "SELECT DISTINCT sc.subjectid FROM subjectclass sc
                 INNER JOIN subjectteacher st ON st.id = sc.subjectteacherid
                 WHERE sc.schoolclassid = ?", [$classId]
            ));

            return response()->json([
                'success'          => true,
                'pass_average'     => $passAverage,
                'grade_scale'      => $gradeScale,
                'is_senior'        => $isSenior,
                'compulsory_count' => $compulsoryCount,
                'total_subjects'   => $totalSubjectCount,
            ]);

        } catch (\Exception $e) {
            Log::error('Get Class Promotion Data Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schoolclass_id'         => 'required|exists:schoolclass,id',
            'session_id'             => 'nullable|exists:schoolsession,id',
            'term_id'                => 'nullable|exists:schoolterm,id',
            'promoted_label'         => 'nullable|string|max:100',
            'trial_label'            => 'nullable|string|max:100',
            'see_principal_label'    => 'nullable|string|max:100',
            'repeat_label'           => 'nullable|string|max:100',
            'rule_logic'             => 'nullable|in:grade_count,average_only,both',
            'promotion_pass_average' => 'nullable|numeric|min:0|max:100',
            'is_active'              => 'nullable',
            'promotion_rules'        => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            [$sessionId, $termId] = $this->cleanIds($request);
            $promotionRules = $this->parseAndValidateRules($request);
            if ($promotionRules instanceof \Illuminate\Http\JsonResponse) return $promotionRules;

            $isActive = filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN);

            $setting = PromotionSetting::updateOrCreate(
                ['schoolclass_id' => $request->schoolclass_id, 'session_id' => $sessionId, 'term_id' => $termId],
                [
                    'promoted_label'         => $request->promoted_label      ?? 'Promoted',
                    'trial_label'            => $request->trial_label         ?? 'Promoted on Trial',
                    'see_principal_label'    => $request->see_principal_label ?? 'Advised to See Principal',
                    'repeat_label'           => $request->repeat_label        ?? 'Advice to Repeat',
                    'rule_logic'             => $request->rule_logic          ?? 'grade_count',
                    'promotion_pass_average' => $request->promotion_pass_average ?: null,
                    'is_active'              => $isActive,
                ]
            );
            $setting->promotion_rules = $promotionRules;
            $setting->save();

            Log::info('Promotion setting saved', ['id' => $setting->id]);
            return response()->json(['success' => true, 'message' => 'Promotion settings saved successfully.', 'data' => $setting]);

        } catch (\Exception $e) {
            Log::error('Store Promotion Setting Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to save: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'schoolclass_id'         => 'required|exists:schoolclass,id',
            'session_id'             => 'nullable|exists:schoolsession,id',
            'term_id'                => 'nullable|exists:schoolterm,id',
            'promoted_label'         => 'nullable|string|max:100',
            'trial_label'            => 'nullable|string|max:100',
            'see_principal_label'    => 'nullable|string|max:100',
            'repeat_label'           => 'nullable|string|max:100',
            'rule_logic'             => 'nullable|in:grade_count,average_only,both',
            'promotion_pass_average' => 'nullable|numeric|min:0|max:100',
            'is_active'              => 'nullable',
            'promotion_rules'        => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $setting = PromotionSetting::findOrFail($id);
            [$sessionId, $termId] = $this->cleanIds($request);
            $promotionRules = $this->parseAndValidateRules($request);
            if ($promotionRules instanceof \Illuminate\Http\JsonResponse) return $promotionRules;

            $isActive = filter_var($request->input('is_active', $setting->is_active), FILTER_VALIDATE_BOOLEAN);

            $setting->update([
                'schoolclass_id'         => $request->schoolclass_id,
                'session_id'             => $sessionId,
                'term_id'                => $termId,
                'promoted_label'         => $request->promoted_label      ?? 'Promoted',
                'trial_label'            => $request->trial_label         ?? 'Promoted on Trial',
                'see_principal_label'    => $request->see_principal_label ?? 'Advised to See Principal',
                'repeat_label'           => $request->repeat_label        ?? 'Advice to Repeat',
                'rule_logic'             => $request->rule_logic          ?? 'grade_count',
                'promotion_pass_average' => $request->promotion_pass_average ?: null,
                'is_active'              => $isActive,
            ]);
            $setting->promotion_rules = $promotionRules;
            $setting->save();

            return response()->json(['success' => true, 'message' => 'Promotion settings updated successfully.', 'data' => $setting]);

        } catch (\Exception $e) {
            Log::error('Update Promotion Setting Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update: ' . $e->getMessage()], 500);
        }
    }

    public function toggleActive(Request $request, $id)
    {
        try {
            $setting            = PromotionSetting::findOrFail($id);
            $setting->is_active = (bool) $request->input('is_active', false);
            $setting->save();
            return response()->json(['success' => true, 'message' => 'Status updated.', 'is_active' => $setting->is_active]);
        } catch (\Exception $e) {
            Log::error('Toggle Active Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update status.'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $setting = PromotionSetting::findOrFail($id);
            $setting->delete();
            Log::info('Promotion setting deleted', ['id' => $id, 'schoolclass_id' => $setting->schoolclass_id]);
            return response()->json(['success' => true, 'message' => 'Promotion settings deleted successfully.']);
        } catch (\Exception $e) {
            Log::error('Delete Promotion Setting Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete: ' . $e->getMessage()], 500);
        }
    }

    private function cleanIds(Request $request): array
    {
        $sessionId = $request->session_id ?: null;
        $termId    = $request->term_id    ?: null;
        if (in_array($sessionId, ['null', ''])) $sessionId = null;
        if (in_array($termId,    ['null', ''])) $termId    = null;
        return [$sessionId, $termId];
    }

    private function parseAndValidateRules(Request $request)
    {
        if (!$request->filled('promotion_rules')) return [];

        $rules = json_decode($request->promotion_rules, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['success' => false, 'message' => 'Invalid JSON in promotion rules.'], 422);
        }

        $validStatuses = ['promoted', 'trial', 'see_principal', 'repeat'];
        $validScopes   = ['all', 'compulsory_only', 'other_only'];
        $validGrouping = ['grouped', 'exact'];
        $validOps      = ['>=', '<=', '=', '>', '<'];

        foreach ($rules as $i => $rule) {
            $n = $i + 1;
            if (empty($rule['rule_name'])) {
                return response()->json(['success' => false, 'message' => "Rule {$n}: name is required."], 422);
            }
            if (!in_array($rule['status_label'] ?? '', $validStatuses)) {
                return response()->json(['success' => false, 'message' => "Rule {$n}: invalid status label."], 422);
            }
            if (!in_array($rule['subject_scope'] ?? 'all', $validScopes)) {
                return response()->json(['success' => false, 'message' => "Rule {$n}: invalid subject scope."], 422);
            }
            if (!in_array($rule['grade_grouping'] ?? 'grouped', $validGrouping)) {
                return response()->json(['success' => false, 'message' => "Rule {$n}: invalid grade grouping."], 422);
            }
            foreach ($rule['grade_conditions'] ?? [] as $j => $cond) {
                $c = $j + 1;
                if (empty($cond['grade'])) {
                    return response()->json(['success' => false, 'message' => "Rule {$n}, condition {$c}: grade required."], 422);
                }
                if (!is_numeric($cond['count'] ?? null)) {
                    return response()->json(['success' => false, 'message' => "Rule {$n}, condition {$c}: count must be numeric."], 422);
                }
                if (!in_array($cond['operator'] ?? '>=', $validOps)) {
                    return response()->json(['success' => false, 'message' => "Rule {$n}, condition {$c}: invalid operator."], 422);
                }
            }
        }

        return $rules;
    }
}
