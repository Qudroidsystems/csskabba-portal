<?php
// app/Http/Controllers/PromotionSettingController.php

namespace App\Http\Controllers;

use App\Models\PromotionSetting;
use App\Models\Schoolclass;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Subject;
use App\Models\Subjectclass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        return view('promotions.settings', compact('settings', 'schoolclasses', 'sessions', 'terms', 'pagetitle'));
    }

    /**
     * Return subjects available for a class (for the conditional rules builder)
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
            ->select([
                'subjectclass.subjectid',
            ]);

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
     * Return compulsory subjects for a class scoped to term/session
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
                $q->where(function ($q2) use ($termId, $sessionId) {
                    if ($termId)    $q2->where('termid', $termId);
                    if ($sessionId) $q2->where('sessionid', $sessionId);
                })->orWhere(function ($q2) {
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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schoolclass_id'          => 'required|exists:schoolclass,id',
            'session_id'              => 'nullable|exists:schoolsession,id',
            'term_id'                 => 'nullable|exists:schoolterm,id',
            'rule_type'               => 'required|in:compulsory_only,average_only,both',
            'compulsory_fail_action'  => 'nullable|in:repeat,see_principal,trial',
            'promotion_pass_average'  => 'nullable|numeric|min:0|max:100',
            'trial_pass_average'      => 'nullable|numeric|min:0|max:100',
            'see_principal_average'   => 'nullable|numeric|min:0|max:100',
            'combined_logic'          => 'nullable|in:and,or',
            'promoted_label'          => 'nullable|string|max:100',
            'trial_label'             => 'nullable|string|max:100',
            'see_principal_label'     => 'nullable|string|max:100',
            'repeat_label'            => 'nullable|string|max:100',
            'promotion_rules'         => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $promotionRules = null;
            if ($request->filled('promotion_rules')) {
                $promotionRules = json_decode($request->promotion_rules, true);
            }

            $setting = PromotionSetting::updateOrCreate(
                [
                    'schoolclass_id' => $request->schoolclass_id,
                    'session_id'     => $request->session_id ?: null,
                    'term_id'        => $request->term_id    ?: null,
                ],
                [
                    'rule_type'               => $request->rule_type,
                    'compulsory_fail_action'  => $request->compulsory_fail_action,
                    'promotion_pass_average'  => $request->promotion_pass_average,
                    'trial_pass_average'      => $request->trial_pass_average,
                    'see_principal_average'   => $request->see_principal_average,
                    'combined_logic'          => $request->combined_logic,
                    'promoted_label'          => $request->promoted_label          ?? 'Promoted',
                    'trial_label'             => $request->trial_label             ?? 'Promoted on Trial',
                    'see_principal_label'     => $request->see_principal_label     ?? 'Advised to See Principal',
                    'repeat_label'            => $request->repeat_label            ?? 'Advice to Repeat',
                    'promotion_rules'         => $promotionRules,
                    'is_active'               => true,
                ]
            );

            return response()->json(['success' => true, 'message' => 'Promotion settings saved successfully.', 'data' => $setting]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to save settings: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'schoolclass_id'          => 'required|exists:schoolclass,id',
            'session_id'              => 'nullable|exists:schoolsession,id',
            'term_id'                 => 'nullable|exists:schoolterm,id',
            'rule_type'               => 'required|in:compulsory_only,average_only,both',
            'compulsory_fail_action'  => 'nullable|in:repeat,see_principal,trial',
            'promotion_pass_average'  => 'nullable|numeric|min:0|max:100',
            'trial_pass_average'      => 'nullable|numeric|min:0|max:100',
            'see_principal_average'   => 'nullable|numeric|min:0|max:100',
            'combined_logic'          => 'nullable|in:and,or',
            'promoted_label'          => 'nullable|string|max:100',
            'trial_label'             => 'nullable|string|max:100',
            'see_principal_label'     => 'nullable|string|max:100',
            'repeat_label'            => 'nullable|string|max:100',
            'promotion_rules'         => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $setting = PromotionSetting::findOrFail($id);

            $promotionRules = null;
            if ($request->filled('promotion_rules')) {
                $promotionRules = json_decode($request->promotion_rules, true);
            }

            $setting->update([
                'schoolclass_id'          => $request->schoolclass_id,
                'session_id'              => $request->session_id     ?: null,
                'term_id'                 => $request->term_id        ?: null,
                'rule_type'               => $request->rule_type,
                'compulsory_fail_action'  => $request->compulsory_fail_action,
                'promotion_pass_average'  => $request->promotion_pass_average,
                'trial_pass_average'      => $request->trial_pass_average,
                'see_principal_average'   => $request->see_principal_average,
                'combined_logic'          => $request->combined_logic,
                'promoted_label'          => $request->promoted_label          ?? 'Promoted',
                'trial_label'             => $request->trial_label             ?? 'Promoted on Trial',
                'see_principal_label'     => $request->see_principal_label     ?? 'Advised to See Principal',
                'repeat_label'            => $request->repeat_label            ?? 'Advice to Repeat',
                'promotion_rules'         => $promotionRules,
            ]);

            return response()->json(['success' => true, 'message' => 'Promotion settings updated successfully.', 'data' => $setting]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update settings: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            PromotionSetting::findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => 'Promotion settings deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete settings.'], 500);
        }
    }

    public function getSettings($schoolclassId, $sessionId = null, $termId = null)
    {
        $query = PromotionSetting::where('schoolclass_id', $schoolclassId);

        if ($sessionId) {
            $query->where(function ($q) use ($sessionId, $termId) {
                $q->where('session_id', $sessionId);
                if ($termId) $q->where('term_id', $termId);
            });
        }

        return response()->json(['success' => true, 'data' => $query->first()]);
    }
}
