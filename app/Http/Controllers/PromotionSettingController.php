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
        try {
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

            // Log for debugging
            Log::info('Subjects by Class Query', [
                'class_id' => $classId,
                'term_id' => $termId,
                'session_id' => $sessionId,
                'count' => $subjectClasses->count()
            ]);

            // Get unique subjects
            $subjects = $subjectClasses
                ->filter(function($sc) {
                    return $sc->subject !== null;
                })
                ->unique('subjectid')
                ->map(function($sc) {
                    return [
                        'id'           => (string)$sc->subjectid,
                        'subject'      => $sc->subject?->subject ?? 'Unknown',
                        'subject_code' => $sc->subject?->subject_code ?? '',
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'subjects' => $subjects,
                'total' => $subjects->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Subjects by Class Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading subjects: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Return compulsory subjects for a class scoped to term/session.
     */
    public function compulsoryByClass(Request $request)
    {
        try {
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

            $subjects = $query->get()->map(function($cs) {
                return [
                    'id'           => (string)$cs->subjectId,
                    'subject'      => $cs->subject?->subject ?? 'Unknown',
                    'subject_code' => $cs->subject?->subject_code ?? '',
                    'min_grade'    => $cs->min_grade ?? '',
                ];
            });

            Log::info('Compulsory Subjects Response', [
                'class_id' => $classId,
                'count' => $subjects->count()
            ]);

            return response()->json([
                'success' => true,
                'subjects' => $subjects,
                'total' => $subjects->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Compulsory Subjects Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading compulsory subjects: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new promotion setting.
     *
     * Expected fields:
     *   schoolclass_id, session_id?, term_id?,
     *   promoted_label, trial_label, see_principal_label, repeat_label,
     *   promotion_rules (JSON string)
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

            // Validate rule structure
            foreach ($promotionRules as $index => $rule) {
                if (empty($rule['rule_name'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Rule ' . ($index + 1) . ' must have a name.'
                    ], 422);
                }
                if (!in_array($rule['status_label'], ['promoted', 'trial', 'see_principal', 'repeat'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Rule ' . ($index + 1) . ' has an invalid status label.'
                    ], 422);
                }
            }

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
            Log::error('Store Promotion Setting Error: ' . $e->getMessage());
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

            // Validate rule structure
            foreach ($promotionRules as $index => $rule) {
                if (empty($rule['rule_name'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Rule ' . ($index + 1) . ' must have a name.'
                    ], 422);
                }
                if (!in_array($rule['status_label'], ['promoted', 'trial', 'see_principal', 'repeat'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Rule ' . ($index + 1) . ' has an invalid status label.'
                    ], 422);
                }
            }

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
            Log::error('Update Promotion Setting Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a promotion setting.
     */
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
            Log::error('Delete Promotion Setting Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get grade scale for a class based on its category
     */
    public function getGradeScale(Request $request)
    {
        try {
            $classId = $request->query('classid');

            if (!$classId) {
                return response()->json(['success' => false, 'message' => 'Class required'], 422);
            }

            $schoolclass = Schoolclass::with('classcategories')->find($classId);

            if (!$schoolclass) {
                return response()->json(['success' => false, 'message' => 'Class not found'], 404);
            }

            $category = $schoolclass->classcategories->first();
            $gradeScale = ['A1', 'B2', 'B3', 'C4', 'C5', 'C6', 'D7', 'E8', 'F9']; // Default senior scale

            if ($category && !$category->is_senior) {
                $gradeScale = ['A', 'B', 'C', 'D', 'F'];
            }

            return response()->json([
                'success' => true,
                'grade_scale' => $gradeScale,
                'is_senior' => $category ? $category->is_senior : true
            ]);

        } catch (\Exception $e) {
            Log::error('Get Grade Scale Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading grade scale: ' . $e->getMessage()
            ], 500);
        }
    }
}
