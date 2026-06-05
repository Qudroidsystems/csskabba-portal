<?php

namespace App\Http\Controllers;

use App\Models\CompulsorySubjectClass;
use App\Models\Schoolclass;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Subject;
use App\Models\Subjectclass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompulsorySubjectClassController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View compulsory-subject|Create compulsory-subject|Update compulsory-subject|Delete compulsory-subject', ['only' => ['index']]);
        $this->middleware('permission:Create compulsory-subject', ['only' => ['store']]);
        $this->middleware('permission:Update compulsory-subject', ['only' => ['update']]);
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

        return view('compulsorysubjectclass.index', compact(
            'compulsorysubjectclasses',
            'schoolclasses',
            'terms',
            'sessions',
            'pagetitle'
        ));
    }

    /**
     * AJAX: Return subjects assigned to a class (via subjectclass table)
     * filtered by term+session, with teacher name and grade scale.
     */
    public function subjectsByClass(Request $request)
    {
        $classId   = $request->query('classid');
        $termId    = $request->query('termid');    // nullable
        $sessionId = $request->query('sessionid'); // nullable

        if (!$classId) {
            return response()->json(['success' => false, 'message' => 'Class is required.'], 422);
        }

        // Load the class with its category (for grade scale)
        $schoolclass = Schoolclass::with('classcategories')->find($classId);

        if (!$schoolclass) {
            return response()->json(['success' => false, 'message' => 'Class not found.'], 404);
        }

        // Determine grade scale from the first linked classcategory
        $gradeScale = [];
        $category   = $schoolclass->classcategories->first();

        if ($category) {
            if ($category->is_senior) {
                $gradeScale = ['A1', 'B2', 'B3', 'C4', 'C5', 'C6', 'D7', 'E8', 'F9'];
            } else {
                $gradeScale = ['A', 'B', 'C', 'D', 'F'];
            }
        }

        // Query subjectclass for this class, filtered by term/session if provided
        $query = Subjectclass::with(['subject', 'subjectTeacher.staff'])
            ->where('schoolclassid', $classId);

        if ($termId) {
            $query->where('termid', $termId);
        }
        if ($sessionId) {
            $query->where('sessionid', $sessionId);
        }

        $subjectclasses = $query->get();

        // Also fetch already-assigned compulsory subject IDs for this class/term/session
        // so the front end can pre-check them
        $alreadyAssigned = CompulsorySubjectClass::where('schoolclassid', $classId)
            ->when($termId,    fn($q) => $q->where('termid', $termId))
            ->when($sessionId, fn($q) => $q->where('sessionid', $sessionId))
            ->get(['subjectId as subjectid', 'min_grade']);

        $assignedMap = $alreadyAssigned->keyBy('subjectid');

        $subjects = $subjectclasses->map(function ($sc) use ($assignedMap) {
            $teacherName = $sc->subjectTeacher?->staff?->name ?? 'N/A';
            $subjectId   = $sc->subject?->id;
            $assigned    = $assignedMap->has($subjectId);
            $minGrade    = $assigned ? ($assignedMap[$subjectId]->min_grade ?? null) : null;

            return [
                'id'           => $subjectId,
                'subject'      => $sc->subject?->subject,
                'subject_code' => $sc->subject?->subject_code,
                'teacher'      => $teacherName,
                'assigned'     => $assigned,
                'min_grade'    => $minGrade,
            ];
        })->unique('id')->values();

        return response()->json([
            'success'     => true,
            'subjects'    => $subjects,
            'grade_scale' => $gradeScale,
            'category'    => $category ? ['name' => $category->category, 'is_senior' => $category->is_senior] : null,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schoolclassid'   => 'required|exists:schoolclass,id',
            'subjectId'       => 'required|array|min:1',
            'subjectId.*'     => 'exists:subject,id',
            'termid'          => 'nullable|exists:schoolterm,id',
            'sessionid'       => 'nullable|exists:schoolsession,id',
            'min_grades'      => 'nullable|array',
            'min_grades.*'    => 'nullable|string|max:10',
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
        $minGrades     = $request->input('min_grades', []);  // keyed by subjectId

        $created  = [];
        $skipped  = [];

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

            $record = CompulsorySubjectClass::create([
                'schoolclassid' => $schoolClassId,
                'subjectId'     => $subjectId,
                'termid'        => $termId,
                'sessionid'     => $sessionId,
                'min_grade'     => $minGrades[$subjectId] ?? null,
            ]);

            $created[] = $record;
        }

        if (empty($created)) {
            return response()->json([
                'success' => false,
                'message' => 'All selected subjects are already assigned to this class for the selected term/session.',
            ], 422);
        }

        $msg = count($created) . ' compulsory subject(s) added successfully.';
        if (!empty($skipped)) {
            $msg .= ' ' . count($skipped) . ' duplicate(s) were skipped.';
        }

        return response()->json(['success' => true, 'message' => $msg, 'data' => $created], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'schoolclassid' => 'required|exists:schoolclass,id',
            'subjectId'     => 'required|exists:subject,id',
            'termid'        => 'nullable|exists:schoolterm,id',
            'sessionid'     => 'nullable|exists:schoolsession,id',
            'min_grade'     => 'nullable|string|max:10',
        ], [
            'schoolclassid.required' => 'Please select a class.',
            'subjectId.required'     => 'Please select a subject.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $schoolClassId = $request->input('schoolclassid');
        $subjectId     = $request->input('subjectId');
        $termId        = $request->input('termid') ?: null;
        $sessionId     = $request->input('sessionid') ?: null;
        $minGrade      = $request->input('min_grade') ?: null;

        // Check for duplicate (excluding current record)
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

        $record->update([
            'schoolclassid' => $schoolClassId,
            'subjectId'     => $subjectId,
            'termid'        => $termId,
            'sessionid'     => $sessionId,
            'min_grade'     => $minGrade,
        ]);

        return response()->json(['success' => true, 'message' => 'Updated successfully.', 'data' => $record]);
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

    /**
     * Bulk delete selected records.
     */
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
}
