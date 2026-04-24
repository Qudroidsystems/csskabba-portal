<?php
// app/Http/Controllers/SchoolBillController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\SchoolBillModel;
use Yajra\DataTables\Facades\DataTables;

class SchoolBillController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View school-bills|Create school-bills|Update school-bills|Delete school-bills', ['only' => ['index', 'store']]);
        $this->middleware('permission:Create school-bills', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update school-bills', ['only' => ['edit', 'update', 'updatebill']]);
        $this->middleware('permission:Delete school-bills', ['only' => ['destroy', 'deletebill']]);
    }

    /**
     * Display a listing of the resource with AJAX DataTable support.
     */
    public function index(Request $request)
    {
        $pagetitle = "School Bill Management";

        if ($request->ajax()) {
            $schoolbills = SchoolBillModel::leftJoin('student_status', 'student_status.id', '=', 'school_bill.statusId')
                ->whereIn('student_status.id', [1, 2])
                ->select([
                    'school_bill.id as id',
                    'school_bill.title as title',
                    'school_bill.description as description',
                    'school_bill.bill_amount as bill_amount',
                    'student_status.id as statusId',
                    'school_bill.updated_at as updated_at'
                ]);

            return DataTables::of($schoolbills)
                ->addIndexColumn()
                ->addColumn('status_name', function($row) {
                    if($row->statusId == 1) return '<span class="badge bg-info">Old Student Bill</span>';
                    if($row->statusId == 2) return '<span class="badge bg-success">New Student Bill</span>';
                    return '<span class="badge bg-secondary">Unknown</span>';
                })
                ->addColumn('formatted_amount', function($row) {
                    return '₦ ' . number_format($row->bill_amount, 2);
                })
                ->addColumn('formatted_date', function($row) {
                    return $row->updated_at ? $row->updated_at->format('Y-m-d H:i') : 'N/A';
                })
                ->addColumn('action', function($row) {
                    $buttons = '';
                    if(auth()->user()->can('Update school-bills')) {
                        $buttons .= '<button class="btn btn-sm btn-primary edit-bill me-1" data-id="'.$row->id.'" data-title="'.$row->title.'" data-amount="'.$row->bill_amount.'" data-description="'.$row->description.'" data-status="'.$row->statusId.'"><i class="ri-pencil-line"></i></button>';
                    }
                    if(auth()->user()->can('Delete school-bills')) {
                        $buttons .= '<button class="btn btn-sm btn-danger delete-bill" data-id="'.$row->id.'" data-title="'.$row->title.'"><i class="ri-delete-bin-line"></i></button>';
                    }
                    return $buttons;
                })
                ->rawColumns(['status_name', 'action'])
                ->make(true);
        }

        return view('schoolbill.index')
            ->with('pagetitle', $pagetitle);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('schoolbill.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|min:1|unique:school_bill,title',
            'bill_amount' => 'required|numeric|min:1',
            'description' => 'required',
            'statusId' => 'required|in:1,2',
        ], [
            'title.required' => 'Please enter a bill title!',
            'title.unique' => 'This bill title already exists!',
            'bill_amount.required' => 'Please enter a bill amount!',
            'bill_amount.numeric' => 'Bill amount must be a number!',
            'bill_amount.min' => 'Bill amount must be at least 1!',
            'description.required' => 'Please enter a description!',
            'statusId.required' => 'Please select a student status!',
            'statusId.in' => 'Invalid student status selected!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $plainNumberString = str_replace(['₦', ','], '', $request->bill_amount);
        $number = floatval($plainNumberString);

        $sbill = SchoolBillModel::create([
            'title' => $request->title,
            'bill_amount' => $number,
            'description' => $request->description,
            'statusId' => $request->statusId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'School Bill created successfully!',
            'data' => $sbill
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $bill = SchoolBillModel::find($id);
        if (!$bill) {
            return response()->json([
                'success' => false,
                'message' => 'School Bill not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $bill
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $bill = SchoolBillModel::find($id);
        if (!$bill) {
            return response()->json([
                'success' => false,
                'message' => 'School Bill not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $bill
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $sbill = SchoolBillModel::find($id);
        if (!$sbill) {
            return response()->json([
                'success' => false,
                'message' => 'School Bill not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|min:1|unique:school_bill,title,' . $id,
            'bill_amount' => 'required|numeric|min:1',
            'description' => 'required',
            'statusId' => 'required|in:1,2',
        ], [
            'title.required' => 'Please enter a bill title!',
            'title.unique' => 'This bill title already exists!',
            'bill_amount.required' => 'Please enter a bill amount!',
            'bill_amount.numeric' => 'Bill amount must be a number!',
            'bill_amount.min' => 'Bill amount must be at least 1!',
            'description.required' => 'Please enter a description!',
            'statusId.required' => 'Please select a student status!',
            'statusId.in' => 'Invalid student status selected!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $plainNumberString = str_replace(['₦', ','], '', $request->bill_amount);
        $number = floatval($plainNumberString);

        $sbill->update([
            'title' => $request->title,
            'bill_amount' => $number,
            'description' => $request->description,
            'statusId' => $request->statusId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'School Bill updated successfully!',
            'data' => $sbill
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $sbill = SchoolBillModel::find($id);
        if (!$sbill) {
            return response()->json([
                'success' => false,
                'message' => 'School Bill not found.'
            ], 404);
        }

        $sbill->delete();

        return response()->json([
            'success' => true,
            'message' => 'School Bill deleted successfully.'
        ], 200);
    }

    /**
     * Bulk delete school bills.
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No bills selected.'
            ], 400);
        }

        $deleted = SchoolBillModel::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => $deleted . ' bill(s) deleted successfully.',
            'count' => $deleted
        ]);
    }
}
