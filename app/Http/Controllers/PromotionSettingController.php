<?php
// app/Http/Controllers/PromotionSettingController.php

namespace App\Http\Controllers;

use App\Models\PromotionSetting;
use App\Models\Schoolclass;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Subjectclass;
use App\Models\CompulsorySubjectClass;
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

        // Build query for subjects assigned to this class
        $query = Subjectclass::with(['subject'])
            ->where('subjectclass.schoolclassid', $classId);

        // Apply filters if provided
        if ($termId && $termId !== 'null' && $termId !== '') {
            $query->where('subjectclass.termid', $termId);
        }
        if ($sessionId && $sessionId !== 'null' && $sessionId !== '') {
            $query->where('subjectclass.sessionid', $sessionId);
        }

        $subjectClasses = $query->get();

        // Get unique subjects
        $subjects = $subjectClasses
            ->filter(function($sc) {
                return $sc->subject !== null;
            })
            ->unique('subjectid')
            ->map(fn($sc) => [
                'id'           => $sc->subjectid,
                'subject'      => $sc->subject?->subject,
                'subject_code' => $sc->subject?->subject_code,
            ])
            ->values();

        return response()->json([
            'success' => true,
            'subjects' => $subjects,
            'total' => $subjects->count()
        ]);
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

        $query = CompulsorySubjectClass::where('schoolclassid', $classId)
            ->with('subject');

        // Apply term/session filters
        if ($termId && $termId !== 'null' && $termId !== '') {
            $query->where(function ($q) use ($termId, $sessionId) {
                $q->where('termid', $termId);
                if ($sessionId && $sessionId !== 'null' && $sessionId !== '') {
                    $q->where('sessionid', $sessionId);
                }
                // Also include global records (where termid and sessionid are null)
                $q->orWhere(function ($q2) {
                    $q2->whereNull('termid')->whereNull('sessionid');
                });
            });
        } elseif ($sessionId && $sessionId !== 'null' && $sessionId !== '') {
            $query->where(function ($q) use ($sessionId) {
                $q->where('sessionid', $sessionId);
                $q->orWhere(function ($q2) {
                    $q2->whereNull('termid')->whereNull('sessionid');
                });
            });
        } else {
            // Include global records when no filters
            $query->where(function ($q) {
                $q->whereNull('termid')->whereNull('sessionid');
            });
        }

        $subjects = $query->get()->map(fn($cs) => [
            'id'           => $cs->subjectId,
            'subject'      => $cs->subject?->subject,
            'subject_code' => $cs->subject?->subject_code,
            'min_grade'    => $cs->min_grade,
        ]);

        return response()->json([
            'success' => true,
            'subjects' => $subjects,
            'total' => $subjects->count()
        ]);
    }

    /**
     * Store a new promotion setting.
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

            // Clean up null/empty values
            $sessionId = $request->session_id ?: null;
            $termId = $request->term_id ?: null;

            if ($sessionId === 'null' || $sessionId === '') $sessionId = null;
            if ($termId === 'null' || $termId === '') $termId = null;

            $setting = PromotionSetting::updateOrCreate(
                [
                    'schoolclass_id' => $request->schoolclass_id,
                    'session_id'     => $sessionId,
                    'term_id'        => $termId,
                ],
                [
                    'promoted_label'      => $request->promoted_label      ?? 'Promoted',
                    'trial_label'         => $request->trial_label         ?? 'Promoted on Trial',
                    'see_principal_label' => $request->see_principal_label ?? 'Advised to See Principal',
                    'repeat_label'        => $request->repeat_label        ?? 'Advice to Repeat',
                    'promotion_rules'     => $promotionRules,
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

            // Clean up null/empty values
            $sessionId = $request->session_id ?: null;
            $termId = $request->term_id ?: null;

            if ($sessionId === 'null' || $sessionId === '') $sessionId = null;
            if ($termId === 'null' || $termId === '') $termId = null;

            $setting->update([
                'schoolclass_id'      => $request->schoolclass_id,
                'session_id'          => $sessionId,
                'term_id'             => $termId,
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
