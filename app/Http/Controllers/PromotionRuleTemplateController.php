<?php
// app/Http/Controllers/PromotionRuleTemplateController.php

namespace App\Http\Controllers;

use App\Models\PromotionRuleTemplate;
use App\Models\CompulsorySubjectClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PromotionRuleTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View promotion|Update promotion');
    }

    public function index()
    {
        $pagetitle = 'Promotion Rule Templates';
        $templates = PromotionRuleTemplate::withCount('settings')
            ->orderByDesc('created_at')
            ->get();
        return view('promotions.templates', compact('templates', 'pagetitle'));
    }

    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'grade_scale' => 'required|in:senior,junior',
            'rules'       => 'nullable|json',
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        try {
            $rules = $request->filled('rules') ? json_decode($request->rules, true) : [];
            $tpl   = PromotionRuleTemplate::create([
                'name'        => $request->name,
                'description' => $request->description,
                'grade_scale' => $request->grade_scale,
                'rules'       => $rules,
                'created_by'  => auth()->id(),
            ]);
            return response()->json(['success' => true, 'message' => 'Template created.', 'data' => $tpl]);
        } catch (\Exception $e) {
            Log::error('Template store error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $v = Validator::make($request->all(), [
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'grade_scale' => 'required|in:senior,junior',
            'rules'       => 'nullable|json',
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        try {
            $tpl = PromotionRuleTemplate::findOrFail($id);
            $tpl->update([
                'name'        => $request->name,
                'description' => $request->description,
                'grade_scale' => $request->grade_scale,
                'rules'       => $request->filled('rules') ? json_decode($request->rules, true) : [],
            ]);
            return response()->json(['success' => true, 'message' => 'Template updated.', 'data' => $tpl]);
        } catch (\Exception $e) {
            Log::error('Template update error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $tpl = PromotionRuleTemplate::findOrFail($id);
            // Detach from settings
            DB::table('promotion_settings')->where('template_id', $id)->update(['template_id' => null]);
            $tpl->delete();
            return response()->json(['success' => true, 'message' => 'Template deleted.']);
        } catch (\Exception $e) {
            Log::error('Template delete error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Load template rules, merging class compulsory subjects ───────────────
    public function loadForClass(Request $request, $templateId)
    {
        try {
            $tpl     = PromotionRuleTemplate::findOrFail($templateId);
            $classId = $request->query('classid');

            if (!$classId) {
                return response()->json(['success' => false, 'message' => 'Class ID required.'], 422);
            }

            // Get class compulsory subjects
            $compSubjects = $this->getClassCompulsorySubjects($classId,
                $request->query('termid'), $request->query('sessionid'));

            $compMap = collect($compSubjects)->keyBy('subject_id');

            // Merge template rules with class compulsory subjects
            $mergedRules = collect($tpl->rules ?? [])->map(function ($rule) use ($compSubjects, $compMap) {
                // Always use class's actual compulsory subjects
                // Pre-fill min_grade from compulsory_subject_classes, overridden by template if present
                $tplSubjects = collect($rule['compulsory_section']['subjects'] ?? [])->keyBy('subject_id');

                $mergedSubjects = collect($compSubjects)->map(function ($cs) use ($tplSubjects) {
                    $tplSubj   = $tplSubjects->get($cs['subject_id']);
                    return [
                        'subject_id'   => $cs['subject_id'],
                        'subject_name' => $cs['subject_name'],
                        'subject_code' => $cs['subject_code'],
                        'min_grade'    => $tplSubj['min_grade'] ?? $cs['default_min_grade'] ?? '',
                        'override'     => isset($tplSubj['min_grade']),
                        'default_min_grade' => $cs['default_min_grade'] ?? '',
                    ];
                })->values()->toArray();

                $rule['compulsory_section']['subjects'] = $mergedSubjects;
                return $rule;
            })->toArray();

            return response()->json([
                'success'          => true,
                'template'         => $tpl,
                'merged_rules'     => $mergedRules,
                'comp_subjects'    => $compSubjects,
                'grade_scale'      => $tpl->grade_scale,
            ]);
        } catch (\Exception $e) {
            Log::error('Template load for class error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── List templates as JSON (for modal dropdown) ──────────────────────────
    public function list(Request $request)
    {
        $gradeScale = $request->query('grade_scale');
        $query      = PromotionRuleTemplate::select('id', 'name', 'description', 'grade_scale')
            ->withCount('settings');
        if ($gradeScale) $query->where('grade_scale', $gradeScale);
        return response()->json(['success' => true, 'templates' => $query->orderBy('name')->get()]);
    }

    // ── Helper: get class compulsory subjects with defaults ──────────────────
    private function getClassCompulsorySubjects($classId, $termId = null, $sessionId = null): array
    {
        $q = CompulsorySubjectClass::where('schoolclassid', $classId)
            ->where(function ($q) use ($termId, $sessionId) {
                $q->where(function ($q2) use ($termId, $sessionId) {
                    if ($termId)    $q2->where('termid', $termId);
                    if ($sessionId) $q2->where('sessionid', $sessionId);
                    if ($termId || $sessionId) return;
                    $q2->whereNull('termid')->whereNull('sessionid');
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
