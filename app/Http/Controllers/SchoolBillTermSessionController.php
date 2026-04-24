<?php
// app/Http/Controllers/SchoolBillTermSessionController.php

namespace App\Http\Controllers;

use App\Models\Schoolterm;
use App\Models\Schoolclass;
use Illuminate\Http\Request;
use App\Models\Schoolsession;
use App\Models\SchoolBillModel;
use Illuminate\Support\Facades\DB;
use App\Models\SchoolBillTermSession;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class SchoolBillTermSessionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View school-bill-for-term-session|Create school-bill-for-term-session|Update school-bill-for-term-session|Delete school-bill-for-term-session', ['only' => ['index', 'store']]);
        $this->middleware('permission:Create school-bill-for-term-session', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update school-bill-for-term-session', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete school-bill-for-term-session', ['only' => ['destroy', 'deleteschoolbilltermsession']]);
    }

    /**
     * Display a listing with DataTable AJAX support.
     */
    public function index(Request $request)
    {
        $pagetitle = "School Bill Term Session Management";

        $terms = Schoolterm::all();
        $sessions = Schoolsession::all();
        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.id as id', 'schoolclass.schoolclass as schoolclass', 'schoolarm.arm as arm'])
            ->orderBy('schoolclass')
            ->get();
        $schoolbills = SchoolBillModel::all();

        if ($request->ajax()) {
            $assignments = SchoolBillTermSession::leftJoin('school_bill', 'school_bill.id', '=', 'school_bill_class_term_session.bill_id')
                ->leftJoin('schoolclass', 'schoolclass.id', '=', 'school_bill_class_term_session.class_id')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->leftJoin('schoolterm', 'schoolterm.id', '=', 'school_bill_class_term_session.termid_id')
                ->leftJoin('schoolsession', 'schoolsession.id', '=', 'school_bill_class_term_session.session_id')
                ->leftJoin('users', 'users.id', '=', 'school_bill_class_term_session.created_by')
                ->select([
                    'school_bill_class_term_session.id as id',
                    'schoolclass.schoolclass as schoolclass',
                    'schoolarm.arm as schoolarm',
                    'schoolterm.term as schoolterm',
                    'schoolsession.session as schoolsession',
                    'users.name as createdBy',
                    'school_bill.title as schoolbill',
                    'school_bill_class_term_session.updated_at as updated_at'
                ]);

            return DataTables::of($assignments)
                ->addIndexColumn()
                ->addColumn('class_display', function($row) {
                    return $row->schoolclass . ' ' . ($row->schoolarm ?? '');
                })
                ->addColumn('term_session', function($row) {
                    return $row->schoolterm . ' | ' . $row->schoolsession;
                })
                ->addColumn('action', function($row) {
                    $buttons = '';
                    if(auth()->user()->can('Update school-bill-for-term-session')) {
                        $buttons .= '<button class="btn btn-sm btn-primary edit-assignment me-1" data-id="'.$row->id.'" data-bill_id="'.$row->bill_id.'" data-class_id="'.$row->class_id.'" data-termid_id="'.$row->termid_id.'" data-session_id="'.$row->session_id.'"><i class="ri-pencil-line"></i></button>';
                    }
                    if(auth()->user()->can('Delete school-bill-for-term-session')) {
                        $buttons .= '<button class="btn btn-sm btn-danger delete-assignment" data-id="'.$row->id.'"><i class="ri-delete-bin-line"></i></button>';
                    }
                    return $buttons;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('schoolbilltermsession.index', compact(
            'pagetitle', 'schoolbills', 'schoolclasses', 'terms', 'sessions'
        ));
    }

    /**
     * Store a newly created assignment (AJAX).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bill_id' => 'required|exists:school_bill,id',
            'class_id' => 'required|array|min:1',
            'class_id.*' => 'exists:schoolclass,id',
            'termid_id' => 'required|array|min:1',
            'termid_id.*' => 'exists:schoolterm,id',
            'session_id' => 'required|exists:schoolsession,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $bill_id = $request->input('bill_id');
        $class_ids = $request->input('class_id');
        $term_ids = $request->input('termid_id');
        $session_id = $request->input('session_id');

        // Check for existing combinations
        $existing = SchoolBillTermSession::where('bill_id', $bill_id)
            ->whereIn('class_id', $class_ids)
            ->whereIn('termid_id', $term_ids)
            ->where('session_id', $session_id)
            ->exists();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'One or more combinations already exist!'
            ], 422);
        }

        $createdRecords = [];
        foreach ($term_ids as $term_id) {
            foreach ($class_ids as $class_id) {
                $record = SchoolBillTermSession::create([
                    'bill_id' => $bill_id,
                    'class_id' => $class_id,
                    'termid_id' => $term_id,
                    'session_id' => $session_id,
                    'created_by' => auth()->id()
                ]);
                $createdRecords[] = $record;
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($createdRecords) . ' assignment(s) created successfully!',
            'data' => $createdRecords
        ], 201);
    }

    /**
     * Update assignments (AJAX).
     */
    public function update(Request $request, $id)
    {
        $assignment = SchoolBillTermSession::find($id);
        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Assignment not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'bill_id' => 'required|exists:school_bill,id',
            'class_id' => 'required|exists:schoolclass,id',
            'termid_id' => 'required|exists:schoolterm,id',
            'session_id' => 'required|exists:schoolsession,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $assignment->update([
            'bill_id' => $request->bill_id,
            'class_id' => $request->class_id,
            'termid_id' => $request->termid_id,
            'session_id' => $request->session_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Assignment updated successfully!',
            'data' => $assignment
        ], 200);
    }

    /**
     * Remove assignment (AJAX).
     */
    public function destroy($id)
    {
        $assignment = SchoolBillTermSession::find($id);
        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Assignment not found.'
            ], 404);
        }

        $assignment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Assignment deleted successfully.'
        ], 200);
    }

    /**
     * Get related data for editing (AJAX).
     */
    public function getRelated($id)
    {
        $assignment = SchoolBillTermSession::find($id);
        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Assignment not found.'
            ], 404);
        }

        $relatedRecords = SchoolBillTermSession::where('bill_id', $assignment->bill_id)
            ->where('session_id', $assignment->session_id)
            ->select('class_id', 'termid_id')
            ->get();

        return response()->json([
            'success' => true,
            'bill_id' => $assignment->bill_id,
            'class_ids' => $relatedRecords->pluck('class_id')->unique()->values(),
            'term_ids' => $relatedRecords->pluck('termid_id')->unique()->values(),
            'session_id' => $assignment->session_id
        ], 200);
    }

    /**
     * Bulk delete assignments (AJAX).
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No assignments selected.'
            ], 400);
        }

        $deleted = SchoolBillTermSession::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => $deleted . ' assignment(s) deleted successfully.'
        ], 200);
    }
}
