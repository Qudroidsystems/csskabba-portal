<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schoolarm;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class SchoolArmController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View school-arm|Create school-arm|Update school-arm|Delete school-arm', ['only' => ['index']]);
        $this->middleware('permission:Create school-arm', ['only' => ['store']]);
        $this->middleware('permission:Update school-arm', ['only' => ['update', 'updatearm']]);
        $this->middleware('permission:Delete school-arm', ['only' => ['destroy', 'deletearm']]);
    }

    public function index(Request $request)
    {
        $pagetitle = "School Arm Management";

        if ($request->ajax()) {
            $query = Schoolarm::query()->orderBy('arm');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<div class="form-check">
                                <input class="form-check-input chk_child" type="checkbox" value="'.$row->id.'">
                            </div>';
                })
                ->editColumn('description', function ($row) {
                    return $row->description ?: '—';
                })
                ->editColumn('updated_at', function ($row) {
                    return $row->updated_at ? $row->updated_at->format('d M Y') : '—';
                })
                ->addColumn('actions', function ($row) {
                    $btn = '<div class="d-flex gap-2">';

                    if (auth()->user()->can('Update school-arm')) {
                        $btn .= '<button type="button"
                                    class="btn btn-subtle-secondary btn-icon edit-arm-btn"
                                    data-id="'.$row->id.'"
                                    data-arm="'.e($row->arm).'"
                                    data-description="'.e($row->description).'">
                                    <i class="ri-pencil-line"></i>
                                </button>';
                    }

                    if (auth()->user()->can('Delete school-arm')) {
                        $btn .= '<button type="button"
                                    class="btn btn-subtle-danger btn-icon delete-arm-btn"
                                    data-id="'.$row->id.'"
                                    data-name="'.e($row->arm).'">
                                    <i class="ri-delete-bin-line"></i>
                                </button>';
                    }

                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['checkbox', 'actions'])
                ->make(true);
        }

        $all_arms = Schoolarm::count(); // for stats if needed

        return view('arm.index', compact('pagetitle', 'all_arms'));
    }

    public function store(Request $request)
    {
        Log::info('Store School Arm Request:', $request->all());

        $request->validate([
            'arm'         => 'required|string|max:255|unique:schoolarm,arm',
            'description' => 'nullable|string',
        ]);

        $arm = Schoolarm::create([
            'arm'         => $request->input('arm'),
            'description' => $request->input('description') ?? '',
        ]);

        Log::info('School Arm Created:', $arm->toArray());

        return response()->json([
            'success' => true,
            'message' => 'School arm has been created successfully'
        ]);
    }

    public function update(Request $request, $id)
    {
        // kept for resource compatibility
        return $this->updatearm($request);
    }

    public function destroy($id)
    {
        // kept for resource compatibility
        $arm = Schoolarm::findOrFail($id);
        $arm->delete();

        return response()->json([
            'success' => true,
            'message' => 'School arm has been deleted successfully'
        ]);
    }

    public function deletearm(Request $request)
    {
        Log::info('Delete School Arm AJAX Request:', $request->all());

        $request->validate([
            'armid' => 'required|exists:schoolarm,id'
        ]);

        $arm = Schoolarm::findOrFail($request->armid);
        $arm->delete();

        Log::info('School Arm Deleted via AJAX:', ['id' => $request->armid]);

        return response()->json([
            'success' => true,
            'message' => 'School arm has been deleted successfully'
        ]);
    }

    public function updatearm(Request $request)
    {
        Log::info('Update School Arm AJAX Request:', $request->all());

        $request->validate([
            'id'          => 'required|exists:schoolarm,id',
            'arm'         => "required|string|max:255|unique:schoolarm,arm,{$request->id}",
            'description' => 'nullable|string',
        ]);

        $arm = Schoolarm::findOrFail($request->id);
        $arm->update([
            'arm'         => $request->input('arm'),
            'description' => $request->input('description') ?? '',
        ]);

        Log::info('School Arm Updated via AJAX:', $arm->toArray());

        return response()->json([
            'success' => true,
            'message' => 'School arm has been updated successfully'
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:schoolarm,id',
        ]);

        $deleted = Schoolarm::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => $deleted . ' arm(s) deleted successfully'
        ]);
    }
}