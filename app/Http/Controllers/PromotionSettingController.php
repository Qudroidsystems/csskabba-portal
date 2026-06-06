<?php
// app/Http/Controllers/PromotionSettingController.php

namespace App\Http\Controllers;

use App\Models\PromotionSetting;
use App\Models\Schoolclass;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
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
        $terms = Schoolterm::orderBy('term')->get();

        return view('promotions.settings', compact('settings', 'schoolclasses', 'sessions', 'terms', 'pagetitle'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schoolclass_id' => 'required|exists:schoolclass,id',
            'session_id' => 'nullable|exists:schoolsession,id',
            'term_id' => 'nullable|exists:schoolterm,id',
            'rule_type' => 'required|in:compulsory_only,average_only,both',
            'min_compulsory_pass' => 'nullable|integer|min:0',
            'compulsory_fail_action' => 'required_if:rule_type,compulsory_only,both|in:repeat,see_principal,trial',
            'promotion_pass_average' => 'nullable|numeric|min:0|max:100',
            'trial_pass_average' => 'nullable|numeric|min:0|max:100',
            'see_principal_average' => 'nullable|numeric|min:0|max:100',
            'combined_logic' => 'required_if:rule_type,both|in:and,or',
            'promoted_label' => 'nullable|string|max:100',
            'trial_label' => 'nullable|string|max:100',
            'see_principal_label' => 'nullable|string|max:100',
            'repeat_label' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $setting = PromotionSetting::updateOrCreate(
                [
                    'schoolclass_id' => $request->schoolclass_id,
                    'session_id' => $request->session_id ?: null,
                    'term_id' => $request->term_id ?: null,
                ],
                [
                    'rule_type' => $request->rule_type,
                    'min_compulsory_pass' => $request->min_compulsory_pass,
                    'compulsory_fail_action' => $request->compulsory_fail_action,
                    'promotion_pass_average' => $request->promotion_pass_average,
                    'trial_pass_average' => $request->trial_pass_average,
                    'see_principal_average' => $request->see_principal_average,
                    'combined_logic' => $request->combined_logic,
                    'promoted_label' => $request->promoted_label ?? 'Promoted',
                    'trial_label' => $request->trial_label ?? 'Promoted on Trial',
                    'see_principal_label' => $request->see_principal_label ?? 'Advised to See Principal',
                    'repeat_label' => $request->repeat_label ?? 'Advice to Repeat',
                    'is_active' => true,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Promotion settings saved successfully.',
                'data' => $setting
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save settings: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'schoolclass_id' => 'required|exists:schoolclass,id',
            'session_id' => 'nullable|exists:schoolsession,id',
            'term_id' => 'nullable|exists:schoolterm,id',
            'rule_type' => 'required|in:compulsory_only,average_only,both',
            'min_compulsory_pass' => 'nullable|integer|min:0',
            'compulsory_fail_action' => 'required_if:rule_type,compulsory_only,both|in:repeat,see_principal,trial',
            'promotion_pass_average' => 'nullable|numeric|min:0|max:100',
            'trial_pass_average' => 'nullable|numeric|min:0|max:100',
            'see_principal_average' => 'nullable|numeric|min:0|max:100',
            'combined_logic' => 'required_if:rule_type,both|in:and,or',
            'promoted_label' => 'nullable|string|max:100',
            'trial_label' => 'nullable|string|max:100',
            'see_principal_label' => 'nullable|string|max:100',
            'repeat_label' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $setting = PromotionSetting::findOrFail($id);
            $setting->update([
                'schoolclass_id' => $request->schoolclass_id,
                'session_id' => $request->session_id ?: null,
                'term_id' => $request->term_id ?: null,
                'rule_type' => $request->rule_type,
                'min_compulsory_pass' => $request->min_compulsory_pass,
                'compulsory_fail_action' => $request->compulsory_fail_action,
                'promotion_pass_average' => $request->promotion_pass_average,
                'trial_pass_average' => $request->trial_pass_average,
                'see_principal_average' => $request->see_principal_average,
                'combined_logic' => $request->combined_logic,
                'promoted_label' => $request->promoted_label ?? 'Promoted',
                'trial_label' => $request->trial_label ?? 'Promoted on Trial',
                'see_principal_label' => $request->see_principal_label ?? 'Advised to See Principal',
                'repeat_label' => $request->repeat_label ?? 'Advice to Repeat',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Promotion settings updated successfully.',
                'data' => $setting
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $setting = PromotionSetting::findOrFail($id);
            $setting->delete();

            return response()->json([
                'success' => true,
                'message' => 'Promotion settings deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete settings.'
            ], 500);
        }
    }

    public function getSettings($schoolclassId, $sessionId = null, $termId = null)
    {
        $query = PromotionSetting::where('schoolclass_id', $schoolclassId);

        if ($sessionId) {
            $query->where(function($q) use ($sessionId, $termId) {
                $q->where('session_id', $sessionId);
                if ($termId) {
                    $q->where('term_id', $termId);
                }
            });
        }

        $setting = $query->first();

        return response()->json([
            'success' => true,
            'data' => $setting
        ]);
    }
}
