<?php
// app/Http/Controllers/PromotionSettingController.php

namespace App\Http\Controllers;

use App\Models\PromotionSetting;
use App\Models\PromotionRuleTemplate;
use App\Models\CompulsorySubjectClass;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
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

    // ── Index ─────────────────────────────────────────────────────────────────
    public function index()
    {
        $pagetitle = 'Promotion Settings';

        $settings = PromotionSetting::with(['schoolclass.arm', 'session', 'term', 'template'])
            ->orderBy('schoolclass_id')
            ->get();

        $schoolclasses = DB::table('schoolclass')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select('schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm as arm_name')
            ->get();

        $sessions  = Schoolsession::orderBy('session', 'desc')->get();
        $terms     = Schoolterm::orderBy('term')->get();
        $templates = PromotionRuleTemplate::select('id', 'name', 'grade_scale')->orderBy('name')->get();

        return view('promotions.settings', compact(
            'settings', 'schoolclasses', 'sessions', 'terms', 'templates', 'pagetitle'
        ));
    }

    // ── Class meta: grade scale + subject counts ──────────────────────────────
    public function getClassPromotionData(Request $request)
    {
        try {
            $classId   = $request->query('classid');
            $termId    = $request->query('termid');
            $sessionId = $request->query('sessionid');

            if (!$classId) {
                return response()->json(['success' => false, 'message' => 'Class required'], 422);
            }

            // Grade scale from classcategories
            $gradeScale   = ['A1', 'B2', 'B3', 'C4', 'C5', 'C6', 'D7', 'E8', 'F9'];
            $isSenior     = true;
            $categoryData = DB::table('schoolclass_classcategory')
                ->join('classcategories', 'classcategories.id', '=', 'schoolclass_classcategory.classcategory_id')
                ->where('schoolclass_classcategory.schoolclass_id', $classId)
                ->select('classcategories.is_senior', 'classcategories.category')
                ->first();

            if ($categoryData && isset($categoryData->is_senior) && !$categoryData->is_senior) {
                $gradeScale = ['A', 'B', 'C', 'D', 'F'];
                $isSenior   = false;
            }

            $passAverage = DB::table('schoolclass_classcategory')
                ->where('schoolclass_id', $classId)
                ->value('promotion_pass_average');

            // All subjects for this class
            $subjectParams = [$classId];
            $subjectSql    = "SELECT DISTINCT s.id, s.subject, s.subject_code
                              FROM subjectclass sc
                              INNER JOIN subjectteacher st ON st.id = sc.subjectteacherid
                              INNER JOIN subject s ON s.id = sc.subjectid
                              WHERE sc.schoolclassid = ?";
            if ($termId && $termId !== '') { $subjectSql .= " AND st.termid = ?"; $subjectParams[] = $termId; }
            if ($sessionId && $sessionId !== '') { $subjectSql .= " AND st.sessionid = ?"; $subjectParams[] = $sessionId; }
            $allSubjects = DB::select($subjectSql, $subjectParams);

            // Compulsory subjects with their min_grade
            $compulsorySubjects = $this->getCompulsorySubjects($classId, $termId, $sessionId);
            $compIds = array_column($compulsorySubjects, 'subject_id');

            // Other subjects
            $otherSubjects = array_filter($allSubjects, fn($s) => !in_array((string)$s->id, array_map('strval', $compIds)));

            return response()->json([
                'success'             => true,
                'pass_average'        => $passAverage,
                'grade_scale'         => $gradeScale,
                'is_senior'           => $isSenior,
                'compulsory_subjects' => $compulsorySubjects,
                'compulsory_count'    => count($compulsorySubjects),
                'other_count'         => count($otherSubjects),
                'total_subjects'      => count($allSubjects),
            ]);

        } catch (\Exception $e) {
            Log::error('Get Class Promotion Data Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Store ─────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $v = $this->validateRequest($request);
        if ($v->fails()) return response()->json(['success' => false, 'errors' => $v->errors()], 422);

        try {
            [$sessionId, $termId] = $this->cleanIds($request);
            $rules = $this->parseRules($request);
            if ($rules instanceof \Illuminate\Http\JsonResponse) return $rules;

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
                    'template_id'            => $request->template_id ?: null,
                ]
            );
            $setting->promotion_rules = $rules;
            $setting->save();

            Log::info('Promotion setting saved', ['id' => $setting->id]);
            return response()->json(['success' => true, 'message' => 'Promotion settings saved.', 'data' => $setting]);
        } catch (\Exception $e) {
            Log::error('Store Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Update ────────────────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $v = $this->validateRequest($request);
        if ($v->fails()) return response()->json(['success' => false, 'errors' => $v->errors()], 422);

        try {
            $setting = PromotionSetting::findOrFail($id);
            [$sessionId, $termId] = $this->cleanIds($request);
            $rules = $this->parseRules($request);
            if ($rules instanceof \Illuminate\Http\JsonResponse) return $rules;

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
                'template_id'            => $request->template_id ?: null,
            ]);
            $setting->promotion_rules = $rules;
            $setting->save();

            return response()->json(['success' => true, 'message' => 'Settings updated.', 'data' => $setting]);
        } catch (\Exception $e) {
            Log::error('Update Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Toggle active ─────────────────────────────────────────────────────────
    public function toggleActive(Request $request, $id)
    {
        try {
            $setting            = PromotionSetting::findOrFail($id);
            $setting->is_active = (bool) $request->input('is_active', false);
            $setting->save();
            return response()->json(['success' => true, 'is_active' => $setting->is_active]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Delete ────────────────────────────────────────────────────────────────
    public function destroy($id)
    {
        try {
            $setting = PromotionSetting::findOrFail($id);
            $setting->delete();
            Log::info('Promotion setting deleted', ['id' => $id]);
            return response()->json(['success' => true, 'message' => 'Deleted successfully.']);
        } catch (\Exception $e) {
            Log::error('Delete Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    private function validateRequest(Request $request)
    {
        return Validator::make($request->all(), [
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
            'template_id'            => 'nullable|exists:promotion_rule_templates,id',
            'promotion_rules'        => 'nullable|json',
        ]);
    }

    private function cleanIds(Request $request): array
    {
        $s = $request->session_id ?: null;
        $t = $request->term_id    ?: null;
        if (in_array($s, ['null', ''])) $s = null;
        if (in_array($t, ['null', ''])) $t = null;
        return [$s, $t];
    }

    private function parseRules(Request $request)
    {
        if (!$request->filled('promotion_rules')) return [];
        $rules = json_decode($request->promotion_rules, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['success' => false, 'message' => 'Invalid JSON in promotion rules.'], 422);
        }
        $validStatuses = ['promoted', 'trial', 'see_principal', 'repeat'];
        foreach ($rules as $i => $rule) {
            $n = $i + 1;
            if (empty($rule['rule_name'])) {
                return response()->json(['success' => false, 'message' => "Rule {$n}: name required."], 422);
            }
            if (!in_array($rule['status_label'] ?? '', $validStatuses)) {
                return response()->json(['success' => false, 'message' => "Rule {$n}: invalid status."], 422);
            }
        }
        return $rules;
    }

    private function getCompulsorySubjects($classId, $termId = null, $sessionId = null): array
    {
        $q = CompulsorySubjectClass::where('schoolclassid', $classId)
            ->where(function ($q) use ($termId, $sessionId) {
                $q->where(function ($q2) use ($termId, $sessionId) {
                    if ($termId)    $q2->where('termid', $termId);
                    if ($sessionId) $q2->where('sessionid', $sessionId);
                })->orWhere(function ($q2) {
                    $q2->whereNull('termid')->whereNull('sessionid');
                });
            })
            ->with('subject')
            ->get();

        return $q->map(fn($cs) => [
            'subject_id'        => (string) $cs->subjectId,
            'subject_name'      => $cs->subject?->subject ?? 'N/A',
            'subject_code'      => $cs->subject?->subject_code ?? '',
            'default_min_grade' => $cs->min_grade ?? '',
        ])->unique('subject_id')->values()->toArray();
    }
}
