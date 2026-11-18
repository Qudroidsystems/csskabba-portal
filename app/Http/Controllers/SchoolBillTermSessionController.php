<?php

namespace App\Http\Controllers;

use App\Models\Schoolterm;
use App\Models\Schoolclass;
use Illuminate\Http\Request;
use App\Models\Schoolsession;
use App\Models\SchoolBillModel;
use Illuminate\Support\Facades\DB;
use App\Models\SchoolBillTermSession;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use Yajra\DataTables\Facades\DataTables;

class SchoolBillTermSessionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View school-bill-for-term-session|Create school-bill-for-term-session|Update school-bill-for-term-session|Delete school-bill-for-term-session', ['only' => ['index', 'data', 'stats', 'show', 'getRelated']]);
        $this->middleware('permission:Create school-bill-for-term-session', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update school-bill-for-term-session', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete school-bill-for-term-session', ['only' => ['destroy', 'bulkDestroy']]);
    }

    // =========================================================================
    // INDEX
    // =========================================================================

    public function index()
    {
        $pagetitle      = 'School Bill Term Session Management';
        $terms          = Schoolterm::all();
        $schoolsessions = Schoolsession::all();

        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->get([
                'schoolclass.id as id',
                'schoolclass.schoolclass as schoolclass',
                'schoolarm.arm as arm_name',
            ])
            ->map(function ($c) {
                $c->label = trim($c->schoolclass . ($c->arm_name ? ' ' . $c->arm_name : ''));
                return $c;
            })
            ->sortBy('schoolclass');

        $schoolbills = SchoolBillModel::orderBy('title')->get();

        return view('schoolbilltermsession.index', compact(
            'pagetitle', 'schoolbills', 'schoolclasses', 'terms', 'schoolsessions'
        ));
    }

    // =========================================================================
    // DATATABLE
    // =========================================================================

    public function data(Request $request)
    {
        $assignments = SchoolBillTermSession::query()
            ->leftJoin('school_bill', 'school_bill.id', '=', 'school_bill_class_term_session.bill_id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'school_bill_class_term_session.class_id')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'school_bill_class_term_session.termid_id')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'school_bill_class_term_session.session_id')
            ->leftJoin('users', 'users.id', '=', 'school_bill_class_term_session.created_by')
            ->select([
                'school_bill_class_term_session.id as id',
                'school_bill_class_term_session.bill_id',
                'school_bill_class_term_session.class_id',
                'school_bill_class_term_session.termid_id',
                'school_bill_class_term_session.session_id',
                'schoolclass.schoolclass as schoolclass',
                'schoolarm.arm as arm_name',
                'schoolterm.term as schoolterm',
                'schoolsession.session as schoolsession',
                'users.name as createdBy',
                'school_bill.title as schoolbill',
                'school_bill.bill_amount as bill_amount',
                'school_bill_class_term_session.updated_at as updated_at',
            ]);

        // ── Filter bar ────────────────────────────────────────────────
        $t = 'school_bill_class_term_session';
        if ($request->filled('f_session')) $assignments->where("$t.session_id", (int) $request->f_session);
        if ($request->filled('f_term'))    $assignments->where("$t.termid_id",  (int) $request->f_term);
        if ($request->filled('f_class'))   $assignments->where("$t.class_id",   (int) $request->f_class);
        if ($request->filled('f_bill'))    $assignments->where("$t.bill_id",    (int) $request->f_bill);
        if ($request->filled('f_audience')) {
            $aud = (int) $request->f_audience;
            if ($aud === 0) {
                $assignments->where(fn ($w) => $w->whereNull('school_bill.statusId')->orWhere('school_bill.statusId', 0));
            } else {
                $assignments->where('school_bill.statusId', $aud);
            }
        }
        if ($request->filled('f_search')) {
            foreach (preg_split('/\s+/', mb_strtolower(trim($request->f_search))) as $word) {
                $like = '%' . $word . '%';
                $assignments->where(function ($w) use ($like) {
                    $w->whereRaw('LOWER(school_bill.title) LIKE ?', [$like])
                      ->orWhereRaw("LOWER(CONCAT(schoolclass.schoolclass, ' ', COALESCE(schoolarm.arm, ''))) LIKE ?", [$like])
                      ->orWhereRaw('LOWER(schoolterm.term) LIKE ?', [$like])
                      ->orWhereRaw('LOWER(schoolsession.session) LIKE ?', [$like])
                      ->orWhereRaw('LOWER(users.name) LIKE ?', [$like])
                      ->orWhereRaw('CAST(school_bill.bill_amount AS CHAR) LIKE ?', [$like]);
                });
            }
        }

        return DataTables::of($assignments)
            ->addIndexColumn()
            ->addColumn('formatted_class', function ($row) {
                return trim($row->schoolclass . ($row->arm_name ? ' ' . $row->arm_name : ''));
            })
            ->addColumn('formatted_term_session', function ($row) {
                return '<span class="ts-badge ts-badge-term">'
                    . e($row->schoolterm)
                    . '</span>'
                    . '<span class="ts-badge ts-badge-session ms-1">'
                    . e($row->schoolsession)
                    . '</span>';
            })
            ->addColumn('formatted_bill', function ($row) {
                return '<div class="fw-semibold">' . e($row->schoolbill) . '</div>'
                    . '<div class="text-muted small">&#8358;&nbsp;' . number_format($row->bill_amount, 2) . '</div>';
            })
            ->addColumn('formatted_date', function ($row) {
                return $row->updated_at
                    ? '<span class="text-muted small">'
                        . \Carbon\Carbon::parse($row->updated_at)->format('d M Y')
                        . '<br><span style="font-size:10px">'
                        . \Carbon\Carbon::parse($row->updated_at)->format('H:i')
                        . '</span></span>'
                    : 'N/A';
            })
            ->addColumn('action', function ($row) {
                $classLabel = trim($row->schoolclass . ($row->arm_name ? ' ' . $row->arm_name : ''));
                $buttons = '<div class="btn-group btn-group-sm">';
                if (auth()->user()->can('Update school-bill-for-term-session')) {
                    $buttons .= '<button class="btn btn-primary edit-assignment" title="Edit"
                        data-id="'         . $row->id         . '"
                        data-bill_id="'    . $row->bill_id    . '"
                        data-class_id="'   . $row->class_id   . '"
                        data-termid_id="'  . $row->termid_id  . '"
                        data-session_id="' . $row->session_id . '">
                        <i class="ri-pencil-line"></i>
                    </button>';
                }
                if (auth()->user()->can('Delete school-bill-for-term-session')) {
                    $buttons .= '<button class="btn btn-danger delete-assignment" title="Delete"
                        data-id="'    . $row->id       . '"
                        data-title="' . e($row->schoolbill . ' — ' . $classLabel) . '">
                        <i class="ri-delete-bin-line"></i>
                    </button>';
                }
                $buttons .= '</div>';
                return $buttons;
            })
            ->rawColumns(['formatted_term_session', 'formatted_bill', 'formatted_date', 'action'])
            ->make(true);
    }

    // =========================================================================
    // STATS
    // =========================================================================

    public function stats()
    {
        $assignments = SchoolBillTermSession::query()
            ->leftJoin('school_bill', 'school_bill.id', '=', 'school_bill_class_term_session.bill_id')
            ->select([
                'school_bill_class_term_session.id',
                'school_bill_class_term_session.session_id',
                'school_bill.bill_amount',
            ])
            ->get();

        $uniqueSessions = SchoolBillTermSession::distinct('session_id')->count('session_id');
        $uniqueBills    = SchoolBillTermSession::distinct('bill_id')->count('bill_id');

        return response()->json([
            'stats' => [
                'total'           => $assignments->count(),
                'unique_bills'    => $uniqueBills,
                'unique_sessions' => $uniqueSessions,
                'total_amount'    => $assignments->sum('bill_amount'),
            ]
        ]);
    }

    // =========================================================================
    // STORE — with duplicate pre-check + graceful handling of DB unique violations
    // =========================================================================

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bill_id'      => 'required|exists:school_bill,id',
            'class_id'     => 'required|array|min:1',
            'class_id.*'   => 'exists:schoolclass,id',
            'termid_id'    => 'required|array|min:1',
            'termid_id.*'  => 'exists:schoolterm,id',
            'session_id'   => 'required|exists:schoolsession,id',
        ], [
            'bill_id.required'    => 'Please select a school bill.',
            'bill_id.exists'      => 'Selected bill does not exist.',
            'class_id.required'   => 'Please select at least one class.',
            'class_id.min'        => 'Please select at least one class.',
            'termid_id.required'  => 'Please select at least one term.',
            'termid_id.min'       => 'Please select at least one term.',
            'session_id.required' => 'Please select a session.',
            'session_id.exists'   => 'Selected session does not exist.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $bill_id    = $request->input('bill_id');
        $class_ids  = array_values(array_filter((array) $request->input('class_id', [])));
        $term_ids   = array_values(array_filter((array) $request->input('termid_id', [])));
        $session_id = $request->input('session_id');

        // Existing (live) rows for this bill/session among the chosen classes & terms.
        $existing = SchoolBillTermSession::where('bill_id', $bill_id)
            ->whereIn('class_id', $class_ids)
            ->whereIn('termid_id', $term_ids)
            ->where('session_id', $session_id)
            ->get(['class_id', 'termid_id']);

        $taken = [];
        foreach ($existing as $row) {
            $taken[(int) $row->class_id . '-' . (int) $row->termid_id] = true;
        }

        $created = [];
        $skipped = [];

        DB::beginTransaction();
        try {
            foreach ($term_ids as $term_id) {
                foreach ($class_ids as $class_id) {
                    $key = (int) $class_id . '-' . (int) $term_id;
                    if (isset($taken[$key])) {
                        $skipped[] = $key;
                        continue;
                    }

                    // Old soft-deleted rows for the same combination still occupy the
                    // database unique key and make the insert fail — clear them first.
                    SchoolBillTermSession::onlyTrashed()
                        ->where('bill_id', $bill_id)
                        ->where('class_id', $class_id)
                        ->where('termid_id', $term_id)
                        ->where('session_id', $session_id)
                        ->forceDelete();

                    $data = [
                        'bill_id'    => $bill_id,
                        'class_id'   => $class_id,
                        'termid_id'  => $term_id,
                        'session_id' => $session_id,
                        'created_by' => auth()->id(),
                    ];

                    try {
                        // Savepoint so one clash doesn't abort the whole batch.
                        $created[] = DB::transaction(fn () => SchoolBillTermSession::create($data));
                    } catch (QueryException $e) {
                        if (($e->errorInfo[1] ?? null) === 1062) {
                            $skipped[] = $key;
                            continue;
                        }
                        throw $e;
                    }
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('SchoolBillTermSession store failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Could not save the assignment: ' . $e->getMessage(),
            ], 500);
        }

        $skippedLabel = $this->describeCombos($skipped);

        if (empty($created)) {
            return response()->json([
                'success' => false,
                'message' => 'Nothing new to assign — this bill is already assigned to: ' . $skippedLabel,
            ], 422);
        }

        $msg = count($created) . ' assignment(s) created.';
        if ($skipped) {
            $msg .= ' Skipped ' . count($skipped) . ' that already existed: ' . $skippedLabel . '.';
        }

        return response()->json([
            'success' => true,
            'message' => $msg,
            'created' => count($created),
            'skipped' => count($skipped),
        ], 201);
    }

    /** Turn ["classId-termId", ...] into "JSS 1 A / First Term, ..." */
    private function describeCombos(array $keys): string
    {
        if (!$keys) return '';
        $classIds = $termIds = [];
        foreach ($keys as $k) {
            [$c, $t] = explode('-', $k);
            $classIds[] = (int) $c;
            $termIds[]  = (int) $t;
        }
        $classes = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->whereIn('schoolclass.id', array_unique($classIds))
            ->get(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm as arm_name'])
            ->keyBy('id');
        $terms = Schoolterm::whereIn('id', array_unique($termIds))->pluck('term', 'id');

        $labels = [];
        foreach ($keys as $k) {
            [$c, $t] = explode('-', $k);
            $cl = $classes[(int) $c] ?? null;
            $labels[] = trim(($cl->schoolclass ?? '?') . ' ' . ($cl->arm_name ?? '')) . ' / ' . ($terms[(int) $t] ?? '?');
        }
        $labels = array_values(array_unique($labels));
        $more = count($labels) > 12 ? ' and ' . (count($labels) - 12) . ' more' : '';
        return implode(', ', array_slice($labels, 0, 12)) . $more;
    }

    // =========================================================================
    // SHOW / EDIT
    // =========================================================================

    public function show($id)
    {
        $assignment = SchoolBillTermSession::find($id);
        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'Assignment not found.'], 404);
        }
        return response()->json(['success' => true, 'data' => $assignment]);
    }

    public function edit($id)
    {
        return $this->show($id);
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    public function update(Request $request, $id)
    {
        $assignment = SchoolBillTermSession::find($id);
        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'Assignment not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'bill_id'    => 'required|exists:school_bill,id',
            'class_id'   => 'required|exists:schoolclass,id',
            'termid_id'  => 'required|exists:schoolterm,id',
            'session_id' => 'required|exists:schoolsession,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $duplicate = SchoolBillTermSession::where('bill_id', $request->bill_id)
            ->where('class_id', $request->class_id)
            ->where('termid_id', $request->termid_id)
            ->where('session_id', $request->session_id)
            ->where('id', '!=', $id)
            ->exists();

        if ($duplicate) {
            return response()->json([
                'success' => false,
                'message' => 'This combination already exists.',
            ], 422);
        }

        SchoolBillTermSession::onlyTrashed()
            ->where('bill_id', $request->bill_id)
            ->where('class_id', $request->class_id)
            ->where('termid_id', $request->termid_id)
            ->where('session_id', $request->session_id)
            ->forceDelete();

        try {
            $assignment->update([
                'bill_id'    => $request->bill_id,
                'class_id'   => $request->class_id,
                'termid_id'  => $request->termid_id,
                'session_id' => $request->session_id,
            ]);
        } catch (QueryException $e) {
            if (($e->errorInfo[1] ?? null) === 1062) {
                return response()->json(['success' => false, 'message' => 'This bill is already assigned to that class and term.'], 422);
            }
            throw $e;
        }

        return response()->json([
            'success' => true,
            'message' => 'Assignment updated successfully.',
            'data'    => $assignment,
        ]);
    }

    // =========================================================================
    // DESTROY (single) — hard delete
    // =========================================================================

    public function destroy($id)
    {
        try {
            $assignment = SchoolBillTermSession::find($id);
            if (!$assignment) {
                return response()->json(['success' => false, 'message' => 'Assignment not found.'], 404);
            }

            $assignment->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Assignment deleted successfully.',
            ]);

        } catch (\Throwable $e) {
            Log::error('SchoolBillTermSession destroy failed', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Could not delete: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // BULK DESTROY — hard delete
    // =========================================================================

    public function bulkDestroy(Request $request)
    {
        try {
            $ids = $request->input('ids');

            if (is_string($ids)) {
                $decoded = json_decode($ids, true);
                $ids = is_array($decoded) ? $decoded : array_map('trim', explode(',', $ids));
            }
            if (!is_array($ids)) {
                $ids = [];
            }
            $ids = array_values(array_filter($ids, fn($v) => $v !== null && $v !== ''));

            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No assignments selected.',
                ], 400);
            }

            $existingIds = SchoolBillTermSession::whereIn('id', $ids)->pluck('id')->toArray();
            $invalidIds  = array_diff($ids, $existingIds);
            if (!empty($invalidIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some selected assignments do not exist: ' . implode(', ', $invalidIds),
                ], 400);
            }

            DB::beginTransaction();
            $deleted = SchoolBillTermSession::whereIn('id', $ids)->forceDelete();
            DB::commit();

            return response()->json([
                'success'       => true,
                'message'       => $deleted . ' assignment(s) deleted successfully.',
                'deleted_count' => $deleted,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('SchoolBillTermSession bulkDestroy failed', [
                'ids'   => $request->input('ids'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Could not delete: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // GET RELATED
    // =========================================================================

    public function getRelated($id)
    {
        $assignment = SchoolBillTermSession::find($id);
        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'Assignment not found.'], 404);
        }

        $related = SchoolBillTermSession::where('bill_id', $assignment->bill_id)
            ->where('session_id', $assignment->session_id)
            ->select('class_id', 'termid_id')
            ->get();

        return response()->json([
            'success'    => true,
            'bill_id'    => $assignment->bill_id,
            'class_ids'  => $related->pluck('class_id')->unique()->values(),
            'term_ids'   => $related->pluck('termid_id')->unique()->values(),
            'session_id' => $assignment->session_id,
        ]);
    }
}