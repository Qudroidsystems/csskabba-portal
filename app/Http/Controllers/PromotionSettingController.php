<?php
// app/Http/Controllers/PromotionSettingController.php

namespace App\Http\Controllers;

use App\Models\PromotionSetting;
use App\Models\Schoolclass;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Subjectclass;
use App\Models\SubjectTeacher;
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

    public function subjectsByClass(Request $request)
    {
        try {
            $classId   = $request->query('classid');
            $termId    = $request->query('termid');
            $sessionId = $request->query('sessionid');

            if (!$classId) {
                return response()->json(['success' => false, 'message' => 'Class required'], 422);
            }

            $sql = "SELECT DISTINCT s.id, s.subject, s.subject_code
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

            $results = DB::select($sql, $params);

            $subjects = array_map(function($row) {
                return [
                    'id' => (string)$row->id,
                    'subject' => $row->subject,
                    'subject_code' => $row->subject_code,
                ];
            }, $results);

            return response()->json([
                'success' => true,
                'subjects' => $subjects,
                'total' => count($subjects)
            ]);

        } catch (\Exception $e) {
            Log::error('Subjects by Class Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading subjects: ' . $e->getMessage()
            ], 500);
        }
    }

    public function compulsoryByClass(Request $request)
    {
        try {
            $classId   = $request->query('classid');
            $termId    = $request->query('termid');
            $sessionId = $request->query('sessionid');

            if (!$classId) {
                return response()->json(['success' => false, 'message' => 'Class required'], 422);
            }

            $sql = "SELECT DISTINCT cs.subjectId, s.subject, s.subject_code, cs.min_grade
                    FROM compulsory_subject_classes cs
                    INNER JOIN subject s ON s.id = cs.subjectId
                    WHERE cs.schoolclassid = ?";

            $params = [$classId];

            if ($termId && $termId !== 'null' && $termId !== '') {
                $sql .= " AND (cs.termid = ? OR (cs.termid IS NULL AND cs.sessionid IS NULL))";
                $params[] = $termId;

                if ($sessionId && $sessionId !== 'null' && $sessionId !== '') {
                    $sql .= " AND (cs.sessionid = ? OR (cs.termid IS NULL AND cs.sessionid IS NULL))";
                    $params[] = $sessionId;
                }
            } elseif ($sessionId && $sessionId !== 'null' && $sessionId !== '') {
                $sql .= " AND (cs.sessionid = ? OR (cs.termid IS NULL AND cs.sessionid IS NULL))";
                $params[] = $sessionId;
            } else {
                $sql .= " AND (cs.termid IS NULL AND cs.sessionid IS NULL)";
            }

            $results = DB::select($sql, $params);

            $subjects = array_map(function($row) {
                return [
                    'id' => (string)$row->subjectId,
                    'subject' => $row->subject,
                    'subject_code' => $row->subject_code,
                    'min_grade' => $row->min_grade ?? '',
                ];
            }, $results);

            return response()->json([
                'success' => true,
                'subjects' => $subjects,
                'total' => count($subjects)
            ]);

        } catch (\Exception $e) {
            Log::error('Compulsory Subjects Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading compulsory subjects: ' . $e->getMessage()
            ], 500);
        }
    }

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
            $promotionRules = [];
            if ($request->filled('promotion_rules')) {
                $promotionRules = json_decode($request->promotion_rules, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid JSON in promotion rules: ' . json_last_error_msg()
                    ], 422);
                }
            }

            $sessionId = $request->session_id ?: null;
            $termId = $request->term_id ?: null;

            if ($sessionId === 'null' || $sessionId === '') $sessionId = null;
            if ($termId === 'null' || $termId === '') $termId = null;

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
                    'is_active'           => true,
                ]
            );

            $setting->promotion_rules = $promotionRules;
            $setting->save();

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

            $promotionRules = [];
            if ($request->filled('promotion_rules')) {
                $promotionRules = json_decode($request->promotion_rules, true);
            }

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
            ]);

            $setting->promotion_rules = $promotionRules;
            $setting->save();

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
                'message' => 'Failed to delete: ' . $e->getMessage()
            ], 500);
        }
    }
}
