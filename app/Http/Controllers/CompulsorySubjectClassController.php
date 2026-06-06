<?php

namespace App\Http\Controllers;

use App\Models\CompulsorySubjectClass;
use App\Models\Schoolclass;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Subject;
use App\Models\Subjectclass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class CompulsorySubjectClassController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View compulsory-subject|Create compulsory-subject|Update compulsory-subject|Delete compulsory-subject', ['only' => ['index']]);
        $this->middleware('permission:Create compulsory-subject', ['only' => ['store']]);
        $this->middleware('permission:Update compulsory-subject', ['only' => ['update', 'updatePassAverage']]);
        $this->middleware('permission:Delete compulsory-subject', ['only' => ['destroy', 'bulkDestroy']]);
    }

    public function index(Request $request)
    {
        $pagetitle = "Compulsory Subject Class Management";

        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get(['schoolclass.id as id', 'schoolclass.schoolclass as schoolclass', 'schoolarm.arm as arm'])
            ->sortBy('schoolclass');

        $terms    = Schoolterm::orderBy('term')->get();
        $sessions = Schoolsession::orderBy('session')->get();

        $compulsorysubjectclasses = CompulsorySubjectClass::leftJoin('schoolclass', 'compulsory_subject_classes.schoolclassid', '=', 'schoolclass.id')
            ->leftJoin('subject', 'compulsory_subject_classes.subjectId', '=', 'subject.id')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'compulsory_subject_classes.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'compulsory_subject_classes.sessionid')
            ->select([
                'compulsory_subject_classes.id as cscid',
                'schoolclass.id as schoolclassid',
                'schoolclass.schoolclass as sclass',
                'schoolarm.arm as schoolarm',
                'subject.id as subjectid',
                'subject.subject as subjectname',
                'subject.subject_code as subjectcode',
                'schoolterm.id as termid',
                'schoolterm.term as termname',
                'schoolsession.id as sessionid',
                'schoolsession.session as sessionname',
                'compulsory_subject_classes.min_grade',
                'compulsory_subject_classes.updated_at',
            ])
            ->orderBy('sclass')
            ->get();

        // Load promotion_pass_average from classcategories via the pivot table
        $classPassAverages = DB::table('schoolclass_classcategory')
            ->join('classcategories', 'classcategories.id', '=', 'schoolclass_classcategory.classcategory_id')
            ->select(
                'schoolclass_classcategory.schoolclass_id as classid',
                'classcategories.promotion_pass_average'
            )
            ->get()
            ->keyBy('classid');

        // Track which classes have categories linked
        $classesWithCategories = DB::table('schoolclass_classcategory')
            ->pluck('schoolclass_id')
            ->flip()
            ->toArray();

        return view('compulsorysubjectclass.index', compact(
            'compulsorysubjectclasses',
            'schoolclasses',
            'terms',
            'sessions',
            'classPassAverages',
            'classesWithCategories',
            'pagetitle'
        ));
    }

    public function subjectsByClass(Request $request)
    {
        $classId   = $request->query('classid');
        $termId    = $request->query('termid');
        $sessionId = $request->query('sessionid');

        if (!$classId) {
            return response()->json(['success' => false, 'message' => 'Class is required.'], 422);
        }

        $schoolclass = Schoolclass::with('classcategories')->find($classId);

        if (!$schoolclass) {
            return response()->json(['success' => false, 'message' => 'Class not found.'], 404);
        }

        $gradeScale = [];
        $category   = $schoolclass->classcategories->first();

        if ($category) {
            $gradeScale = $category->is_senior
                ? ['A1', 'B2', 'B3', 'C4', 'C5', 'C6', 'D7', 'E8', 'F9']
                : ['A', 'B', 'C', 'D', 'F'];
        }

        $query = Subjectclass::with(['subject'])
            ->join('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->join('users', 'users.id', '=', 'subjectteacher.staffid')
            ->where('subjectclass.schoolclassid', $classId)
            ->select([
                'subjectclass.id as id',
                'subjectclass.subjectid',
                'subjectteacher.staffid',
                'subjectteacher.termid',
                'subjectteacher.sessionid',
                'users.name as teacher_name',
            ]);

        if ($termId)    $query->where('subjectteacher.termid', $termId);
        if ($sessionId) $query->where('subjectteacher.sessionid', $sessionId);

        $subjectclasses = $query->get();

        $alreadyAssigned = CompulsorySubjectClass::where('schoolclassid', $classId)
            ->when($termId,    fn($q) => $q->where('termid', $termId))
            ->when($sessionId, fn($q) => $q->where('sessionid', $sessionId))
            ->get(['subjectId as subjectid', 'min_grade']);

        $assignedMap = $alreadyAssigned->keyBy('subjectid');

        $subjects = $subjectclasses->map(function ($sc) use ($assignedMap) {
            $subjectId = $sc->subjectid;
            $assigned  = $assignedMap->has($subjectId);
            $minGrade  = $assigned ? ($assignedMap[$subjectId]->min_grade ?? null) : null;

            return [
                'id'           => $subjectId,
                'subject'      => $sc->subject?->subject,
                'subject_code' => $sc->subject?->subject_code,
                'teacher'      => $sc->teacher_name ?? 'N/A',
                'assigned'     => $assigned,
                'min_grade'    => $minGrade,
            ];
        })->unique('id')->values();

        $passAverage = $category?->promotion_pass_average !== null
            ? (float) $category->promotion_pass_average
            : null;

        return response()->json([
            'success'      => true,
            'subjects'     => $subjects,
            'grade_scale'  => $gradeScale,
            'pass_average' => $passAverage,
            'has_category' => $category !== null,
            'category'     => $category
                ? ['name' => $category->category, 'is_senior' => $category->is_senior, 'id' => $category->id]
                : null,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schoolclassid'          => 'required|exists:schoolclass,id',
            'subjectId'              => 'required|array|min:1',
            'subjectId.*'            => 'exists:subject,id',
            'termid'                 => 'nullable|exists:schoolterm,id',
            'sessionid'              => 'nullable|exists:schoolsession,id',
            'min_grades'             => 'nullable|array',
            'min_grades.*'           => 'nullable|string|max:10',
            'promotion_pass_average' => 'nullable|numeric|min:0|max:100',
        ], [
            'schoolclassid.required' => 'Please select a class.',
            'schoolclassid.exists'   => 'Selected class does not exist.',
            'subjectId.required'     => 'Please select at least one subject.',
            'subjectId.min'          => 'Please select at least one subject.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $schoolClassId = $request->input('schoolclassid');
        $subjectIds    = $request->input('subjectId', []);
        $termId        = $request->input('termid') ?: null;
        $sessionId     = $request->input('sessionid') ?: null;
        $minGrades     = $request->input('min_grades', []);
        $passAverage   = $request->input('promotion_pass_average');

        // Check if class has category linked before saving pass average
        $hasCategory = DB::table('schoolclass_classcategory')
            ->where('schoolclass_id', $schoolClassId)
            ->exists();

        if ($passAverage !== null && $passAverage !== '') {
            if (!$hasCategory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot set promotion pass average: This class is not linked to any category. Please contact administrator.',
                ], 422);
            }
            $this->savePassAverage($schoolClassId, (float) $passAverage);
        }

        $created = [];
        $skipped = [];

        foreach ($subjectIds as $subjectId) {
            $exists = CompulsorySubjectClass::where('schoolclassid', $schoolClassId)
                ->where('subjectId', $subjectId)
                ->where('termid', $termId)
                ->where('sessionid', $sessionId)
                ->exists();

            if ($exists) {
                $skipped[] = $subjectId;
                continue;
            }

            $created[] = CompulsorySubjectClass::create([
                'schoolclassid' => $schoolClassId,
                'subjectId'     => $subjectId,
                'termid'        => $termId,
                'sessionid'     => $sessionId,
                'min_grade'     => $minGrades[$subjectId] ?? null,
            ]);
        }

        if (empty($created)) {
            return response()->json([
                'success' => false,
                'message' => 'All selected subjects are already assigned to this class for the selected term/session.',
            ], 422);
        }

        $msg = count($created) . ' compulsory subject(s) added successfully.';
        if (!empty($skipped)) $msg .= ' ' . count($skipped) . ' duplicate(s) skipped.';

        return response()->json(['success' => true, 'message' => $msg, 'data' => $created], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'schoolclassid'          => 'required|exists:schoolclass,id',
            'subjectId'              => 'required|exists:subject,id',
            'termid'                 => 'nullable|exists:schoolterm,id',
            'sessionid'              => 'nullable|exists:schoolsession,id',
            'min_grade'              => 'nullable|string|max:10',
            'promotion_pass_average' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $schoolClassId = $request->input('schoolclassid');
        $subjectId     = $request->input('subjectId');
        $termId        = $request->input('termid') ?: null;
        $sessionId     = $request->input('sessionid') ?: null;
        $minGrade      = $request->input('min_grade') ?: null;
        $passAverage   = $request->input('promotion_pass_average');

        $duplicate = CompulsorySubjectClass::where('schoolclassid', $schoolClassId)
            ->where('subjectId', $subjectId)
            ->where('termid', $termId)
            ->where('sessionid', $sessionId)
            ->where('id', '!=', $id)
            ->exists();

        if ($duplicate) {
            return response()->json([
                'success' => false,
                'message' => 'This subject is already assigned to this class for the selected term/session.',
            ], 422);
        }

        $record = CompulsorySubjectClass::find($id);
        if (!$record) {
            return response()->json(['success' => false, 'message' => 'Record not found.'], 404);
        }

        if ($passAverage !== null && $passAverage !== '') {
            $hasCategory = DB::table('schoolclass_classcategory')
                ->where('schoolclass_id', $schoolClassId)
                ->exists();

            if (!$hasCategory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update promotion pass average: This class is not linked to any category.',
                ], 422);
            }
            $this->savePassAverage($schoolClassId, (float) $passAverage);
        }

        $record->update([
            'schoolclassid' => $schoolClassId,
            'subjectId'     => $subjectId,
            'termid'        => $termId,
            'sessionid'     => $sessionId,
            'min_grade'     => $minGrade,
        ]);

        return response()->json(['success' => true, 'message' => 'Updated successfully.', 'data' => $record]);
    }

    /**
     * Standalone AJAX: update only the promotion pass average for a class.
     */
    public function updatePassAverage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schoolclassid'          => 'required|exists:schoolclass,id',
            'promotion_pass_average' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $schoolClassId = $request->input('schoolclassid');
        $passAverage   = $request->input('promotion_pass_average');

        // Convert empty string to null
        $passAverageValue = ($passAverage !== null && $passAverage !== '') ? (float) $passAverage : null;

        // Check if class has category linked
        $hasCategory = DB::table('schoolclass_classcategory')
            ->where('schoolclass_id', $schoolClassId)
            ->exists();

        if (!$hasCategory) {
            $class = Schoolclass::find($schoolClassId);
            $className = $class ? $class->schoolclass : 'Unknown class';

            return response()->json([
                'success' => false,
                'message' => "Class '{$className}' is not linked to any category. Please contact administrator to link this class to a category first.",
                'needs_category_link' => true,
                'class_id' => $schoolClassId,
                'class_name' => $className
            ], 422);
        }

        $savedValue = $this->savePassAverage($schoolClassId, $passAverageValue);

        $display = ($savedValue !== null)
            ? number_format($savedValue, 1) . '%'
            : 'None (threshold disabled)';

        return response()->json([
            'success' => true,
            'message' => "Promotion pass average updated to {$display}.",
            'saved_value' => $savedValue,
        ]);
    }

    public function destroy($id)
    {
        $record = CompulsorySubjectClass::find($id);
        if (!$record) {
            return response()->json(['success' => false, 'message' => 'Record not found.'], 404);
        }
        $record->delete();
        return response()->json(['success' => true, 'message' => 'Compulsory subject removed successfully.']);
    }

    public function bulkDestroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids'   => 'required|array|min:1',
            'ids.*' => 'exists:compulsory_subject_classes,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $count = CompulsorySubjectClass::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => $count . ' record(s) deleted successfully.',
        ]);
    }

    /**
     * Get classes that don't have categories linked
     */
    public function getUnlinkedClasses()
    {
        $linkedClassIds = DB::table('schoolclass_classcategory')
            ->pluck('schoolclass_id')
            ->toArray();

        $unlinkedClasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->whereNotIn('schoolclass.id', $linkedClassIds)
            ->get(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm'])
            ->map(function($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->schoolclass . ($class->arm ? ' (' . $class->arm . ')' : ''),
                    'arm' => $class->arm ?? 'N/A'
                ];
            });

        return response()->json([
            'success' => true,
            'unlinked_classes' => $unlinkedClasses
        ]);
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Save promotion pass average for a class
     *
     * @param int $schoolClassId
     * @param float|null $passAverage
     * @return float|null Returns the saved value or null if failed
     */
    private function savePassAverage(int $schoolClassId, ?float $passAverage): ?float
    {
        $pivotRow = DB::table('schoolclass_classcategory')
            ->where('schoolclass_id', $schoolClassId)
            ->first();

        if (!$pivotRow) {
            Log::warning('No class category linked to class ID: ' . $schoolClassId);
            return null;
        }

        DB::table('classcategories')
            ->where('id', $pivotRow->classcategory_id)
            ->update(['promotion_pass_average' => $passAverage]);

        return $passAverage;
    }
}
