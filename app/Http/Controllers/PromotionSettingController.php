<?php
// app/Http/Controllers/PromotionSettingController.php

namespace App\Http\Controllers;

use App\Models\PromotionSetting;
use App\Models\Schoolclass;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Subjectclass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PromotionSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View promotion|Update promotion');
    }

    public function index()
    {
        $pagetitle = "Promotion Settings Management";

        $settings = PromotionSetting::with(['schoolclass', 'session', 'term'])
            ->orderBy('schoolclass_id')
            ->get();

        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm']);

        $sessions = Schoolsession::orderBy('session', 'desc')->get();
        $terms    = Schoolterm::orderBy('term')->get();

        return view('promotions.settings', compact(
            'settings', 'schoolclasses', 'sessions', 'terms', 'pagetitle'
        ));
    }

    /**
     * Return ALL subjects for a class (used by the rule builder).
     * Term / session scoping is optional.
     */
    public function subjectsByClass(Request $request)
    {
        $classId   = $request->query('classid');
        $termId    = $request->query('termid');
        $sessionId = $request->query('sessionid');

        if (!$classId) {
            return response()->json(['success' => false, 'message' => 'Class required'], 422);
        }

        $query = Subjectclass::with(['subject'])
            ->join('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->where('subjectclass.schoolclassid', $classId)
            ->select('subjectclass.subjectid');

        if ($termId)    $query->where('subjectteacher.termid', $termId);
        if ($sessionId) $query->where('subjectteacher.sessionid', $sessionId);

        $subjects = $query->get()
            ->unique('subjectid')
            ->map(fn($sc) => [
                'id'           => $sc->subjectid,
                'subject'      => $sc->subject?->subject,
                'subject_code' => $sc->subject?->subject_code,
            ])
            ->values();

        return response()->json(['success' => true, 'subjects' => $subjects]);
    }

    /**
     * Return compulsory subjects for a class scoped to term/session.
     */
    public function compulsoryByClass(Request $request)
    {
        $classId   = $request->query('classid');
        $termId    = $request->query('termid');
        $sessionId = $request->query('sessionid');

        if (!$classId) {
            return response()->json(['success' => false, 'message' => 'Class required'], 422);
        }

        $query = \App\Models\CompulsorySubjectClass::where('schoolclassid', $classId)
            ->with('subject');

        if ($termId || $sessionId) {
            $query->where(function ($q) use ($termId, $sessionId) {
                // exact match
                $q->where(function ($q2) use ($termId, $sessionId) {
                    if ($termId)    $q2->where('termid', $termId);
                    if ($sessionId) $q2->where('sessionid', $sessionId);
                })
                // or global (no term / no session)
                ->orWhere(function ($q2) {
                    $q2->whereNull('termid')->whereNull('sessionid');
                });
            });
        }

        $subjects = $query->get()->map(fn($cs) => [
            'id'           => $cs->subjectId,
            'subject'      => $cs->subject?->subject,
            'subject_code' => $cs->subject?->subject_code,
            'min_grade'    => $cs->min_grade,
        ]);

        return response()->json(['success' => true, 'subjects' => $subjects]);
    }

    /**
     * Store a new promotion setting.
     *
     * Expected fields:
     *   schoolclass_id, session_id?, term_id?,
     *   promoted_label, trial_label, see_principal_label, repeat_label,
     *   promotion_rules (JSON string)
     *
     * promotion_rules JSON shape:
     * [
     *   {
     *     "rule_name": "All A's — Top",
     *     "status_label": "promoted",          // promoted|trial|see_principal|repeat
     *     "subject_conditions": [
     *       { "subject_id": 3, "subject_name": "Mathematics",
     *         "is_compulsory": true, "min_grade": "A1" },
     *       { "subject_id": 7, "subject_name": "English",
     *         "is_compulsory": true, "min_grade": "" },   // "" = Any
     *       ...
     *     ]
     *   },
     *   ...
     * ]
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schoolclass_id'      => 'required|exists:schoolclass,id',
            'session_id'          => 'nullable|exists:schoolsession,id',
            'term_id'             => 'nullable|exists:schoolterm,id',
            'promoted_label'      => 'nullable|string|max:100',
            'trial_label'         => 'nullable|string|max:100',
            'see_principal_label' => 'nullable|string|max:100',
            'repeat_label'        => 'nullable|string|max:100',
            'promotion_rules'     => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $promotionRules = $request->filled('promotion_rules')
                ? json_decode($request->promotion_rules, true)
                : [];

            $setting = PromotionSetting::updateOrCreate(
                [
                    'schoolclass_id' => $request->schoolclass_id,
                    'session_id'     => $request->session_id ?: null,
                    'term_id'        => $request->term_id    ?: null,
                ],
                [
                    'promoted_label'      => $request->promoted_label      ?? 'Promoted',
                    'trial_label'         => $request->trial_label         ?? 'Promoted on Trial',
                    'see_principal_label' => $request->see_principal_label ?? 'Advised to See Principal',
                    'repeat_label'        => $request->repeat_label        ?? 'Advice to Repeat',
                    'promotion_rules'     => $promotionRules,
                    // Keep legacy columns nullable / default so existing migrations don't break
                    'rule_type'           => 'compulsory_only',
                    'is_active'           => true,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Promotion settings saved successfully.',
                'data'    => $setting,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing promotion setting.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'schoolclass_id'      => 'required|exists:schoolclass,id',
            'session_id'          => 'nullable|exists:schoolsession,id',
            'term_id'             => 'nullable|exists:schoolterm,id',
            'promoted_label'      => 'nullable|string|max:100',
            'trial_label'         => 'nullable|string|max:100',
            'see_principal_label' => 'nullable|string|max:100',
            'repeat_label'        => 'nullable|string|max:100',
            'promotion_rules'     => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $setting = PromotionSetting::findOrFail($id);

            $promotionRules = $request->filled('promotion_rules')
                ? json_decode($request->promotion_rules, true)
                : [];

            $setting->update([
                'schoolclass_id'      => $request->schoolclass_id,
                'session_id'          => $request->session_id ?: null,
                'term_id'             => $request->term_id    ?: null,
                'promoted_label'      => $request->promoted_label      ?? 'Promoted',
                'trial_label'         => $request->trial_label         ?? 'Promoted on Trial',
                'see_principal_label' => $request->see_principal_label ?? 'Advised to See Principal',
                'repeat_label'        => $request->repeat_label        ?? 'Advice to Repeat',
                'promotion_rules'     => $promotionRules,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Promotion settings updated successfully.',
                'data'    => $setting,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            PromotionSetting::findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => 'Promotion settings deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete.'], 500);
        }
    }
}
