<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Broadsheets;
use App\Models\Schoolclass;
use App\Models\SubjectVetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * "My Subject Vetting Assignments" -- the subject-class broadsheets an admin
 * has asked this user to vet (check and sign off) each term.
 *
 * Vetting is READ-ONLY on scores: this controller never recalculates totals,
 * cums, grades or positions. Those belong to score entry (MyScoreSheet /
 * AdminScoreEntry) and ClassPositionService. The previous version rewrote
 * every row with an outdated cum formula each time the broadsheet was
 * opened, silently undoing score entry's figures.
 *
 * Status of an assignment:
 *   pending   -- still being vetted (default)
 *   completed -- every student row vetted (set automatically, or manually)
 *   rejected  -- sent back to the subject teacher for corrections
 */
class MySubjectVettingsController extends Controller
{
    private const STATUSES = ['pending', 'completed', 'rejected'];

    public function __construct()
    {
        $this->middleware('permission:View my-subject-vettings', ['only' => ['index', 'classBroadsheet']]);
        $this->middleware('permission:Update my-subject-vettings', ['only' => ['updateVettedStatus', 'bulkVet', 'updateStatus']]);
    }

    // =========================================================================
    // INDEX -- the vetter's assignments with live vetting progress
    // =========================================================================

    public function index(Request $request): View|JsonResponse
    {
        $pagetitle   = 'My Subject Vetting Assignments';
        $assignments = $this->attachProgress($this->assignmentsQuery((int) Auth::id())->get());

        $stats = [
            'total'     => $assignments->count(),
            'pending'   => $assignments->where('status', 'pending')->count(),
            'completed' => $assignments->where('status', 'completed')->count(),
            'rejected'  => $assignments->where('status', 'rejected')->count(),
            'rows'      => (int) $assignments->sum('students'),
            'vetted'    => (int) $assignments->sum('vetted'),
        ];
        $stats['percent'] = $stats['rows'] ? (int) round($stats['vetted'] / $stats['rows'] * 100) : 0;

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'assignments' => $assignments->values(), 'stats' => $stats]);
        }

        $termOptions = $assignments->filter(fn ($a) => $a->termid)->unique('termid')->sortBy('termid')
            ->mapWithKeys(fn ($a) => [$a->termid => $a->termname])->all();
        $sessionOptions = $assignments->filter(fn ($a) => $a->sessionid)->unique('sessionid')->sortByDesc('sessionid')
            ->mapWithKeys(fn ($a) => [$a->sessionid => $a->sessionname])->all();
        $currentSessionId = DB::table('schoolsession')->where('status', 'Current')->value('id');

        return view('mysubjectvettings.index', compact(
            'pagetitle', 'assignments', 'stats', 'termOptions', 'sessionOptions', 'currentSessionId'
        ));
    }

    // =========================================================================
    // CLASS BROADSHEET -- read-only scores + per-student vet toggles
    // =========================================================================

    public function classBroadsheet($schoolclassid, $subjectclassid, $staffid, $termid, $sessionid): View
    {
        $assignment = $this->findAssignment((int) $subjectclassid, (int) $termid, (int) $sessionid);
        abort_unless($assignment || $this->canManageAllVettings(), 403, 'This broadsheet is not in your vetting assignments.');

        $schoolclass = Schoolclass::with('classcategories')->find($schoolclassid);
        $armName     = $schoolclass ? DB::table('schoolarm')->where('id', $schoolclass->arm)->value('arm') : null;

        $assessments = collect();
        if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            $assessments = Assessment::whereIn('classcategory_id', $schoolclass->classcategories->pluck('id'))
                ->orderBy('id')->get();
        }

        $broadsheets = $this->getBroadsheets((int) $subjectclassid, (int) $termid, (int) $sessionid);

        $meta = DB::table('subjectclass as sjc')
            ->join('subjectteacher as st', 'st.id', '=', 'sjc.subjectteacherid')
            ->join('subject as s', 's.id', '=', 'st.subjectid')
            ->leftJoin('users as u', 'u.id', '=', 'st.staffid')
            ->where('sjc.id', $subjectclassid)
            ->first(['s.subject', 's.subject_code', 'u.name as teacher']);

        $summary = [
            'students' => $broadsheets->count(),
            'vetted'   => $broadsheets->filter(fn ($b) => (int) $b->vettedstatus === 1)->count(),
            'entered'  => $broadsheets->filter(fn ($b) => (float) $b->total > 0)->count(),
            'average'  => $broadsheets->count() ? round($broadsheets->avg(fn ($b) => (float) $b->total), 1) : 0,
        ];

        $className = trim(($schoolclass->schoolclass ?? '') . ' ' . ($armName ?? ''));
        $pagetitle = 'Vetting: ' . ($meta->subject ?? 'Subject') . ' — ' . $className;

        return view('mysubjectvettings.classbroadsheet', [
            'pagetitle'      => $pagetitle,
            'broadsheets'    => $broadsheets,
            'assessments'    => $assessments,
            'className'      => $className ?: '—',
            'subjectName'    => $meta->subject ?? '—',
            'subjectCode'    => $meta->subject_code ?? '',
            'teacherName'    => $meta->teacher ?? '—',
            'schoolterm'     => DB::table('schoolterm')->where('id', $termid)->value('term') ?? '—',
            'schoolsession'  => DB::table('schoolsession')->where('id', $sessionid)->value('session') ?? '—',
            'assignment'     => $assignment,
            'summary'        => $summary,
            'subjectclassid' => (int) $subjectclassid,
            'termid'         => (int) $termid,
            'sessionid'      => (int) $sessionid,
            'canUpdate'      => Auth::user()->can('Update my-subject-vettings') && (bool) $assignment,
        ]);
    }

    // =========================================================================
    // VET / UN-VET ONE ROW
    // =========================================================================

    public function updateVettedStatus(Request $request): JsonResponse
    {
        $request->validate([
            'broadsheet_id' => 'required|exists:broadsheets,id',
            'vettedstatus'  => 'required|in:0,1',
        ]);

        $row = $this->broadsheetContext((int) $request->broadsheet_id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Score row not found.'], 404);
        }

        $assignment = $this->findAssignment((int) $row->subjectclass_id, (int) $row->term_id, (int) $row->session_id);
        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'This score row is not in your vetting assignments.'], 403);
        }

        try {
            Broadsheets::where('id', $row->id)->update([
                'vettedstatus' => (int) $request->vettedstatus,
                'vettedby'     => Auth::id(),
            ]);

            return response()->json(['success' => true] + $this->syncAssignmentStatus($assignment));
        } catch (\Throwable $e) {
            Log::error('Failed to update vetted status', ['broadsheet_id' => $request->broadsheet_id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to update vetted status.'], 500);
        }
    }

    // =========================================================================
    // BULK VET -- vet or un-vet many rows of one assignment at once
    // =========================================================================

    public function bulkVet(Request $request): JsonResponse
    {
        $request->validate([
            'subjectclass_id'  => 'required|integer',
            'term_id'          => 'required|integer',
            'session_id'       => 'required|integer',
            'vettedstatus'     => 'required|in:0,1',
            'broadsheet_ids'   => 'nullable|array',
            'broadsheet_ids.*' => 'integer',
        ]);

        $assignment = $this->findAssignment((int) $request->subjectclass_id, (int) $request->term_id, (int) $request->session_id);
        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'Not in your vetting assignments.'], 403);
        }

        // Only rows that genuinely belong to this assignment can be touched.
        $ids = $this->assignmentRowsQuery((int) $request->subjectclass_id, (int) $request->term_id, (int) $request->session_id)
            ->when($request->filled('broadsheet_ids'), fn ($q) => $q->whereIn('b.id', $request->broadsheet_ids))
            ->pluck('b.id');

        try {
            Broadsheets::whereIn('id', $ids)->update([
                'vettedstatus' => (int) $request->vettedstatus,
                'vettedby'     => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Bulk vet failed', ['subjectclass_id' => $request->subjectclass_id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to update vetted status.'], 500);
        }

        return response()->json([
            'success' => true,
            'updated' => $ids->count(),
            'ids'     => $ids->values(),
        ] + $this->syncAssignmentStatus($assignment));
    }

    // =========================================================================
    // ASSIGNMENT STATUS -- mark complete, reopen, or send back to teacher
    // =========================================================================

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate(['status' => 'required|in:' . implode(',', self::STATUSES)]);

        $assignment = SubjectVetting::where('id', $id)->where('userid', Auth::id())->first();
        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'Assignment not found.'], 404);
        }

        if ($request->status === 'completed') {
            $p = $this->progressFor($assignment);
            if ($p['students'] > 0 && $p['vetted'] < $p['students']) {
                return response()->json([
                    'success' => false,
                    'message' => "Only {$p['vetted']} of {$p['students']} students are vetted. Vet every student before marking this complete.",
                ], 422);
            }
        }

        $assignment->status = $request->status;
        $assignment->save();

        return response()->json([
            'success' => true,
            'status'  => $assignment->status,
            'message' => match ($assignment->status) {
                'completed' => 'Marked as completed.',
                'rejected'  => 'Sent back to the subject teacher for corrections.',
                default     => 'Reopened for vetting.',
            },
        ]);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function assignmentsQuery(int $userId)
    {
        return DB::table('subject_vettings as sv')
            ->leftJoin('subjectclass as sjc',  'sjc.id', '=', 'sv.subjectclassid')
            ->leftJoin('schoolclass as sc',    'sc.id',  '=', 'sjc.schoolclassid')
            ->leftJoin('schoolarm as arm',     'arm.id', '=', 'sc.arm')
            ->leftJoin('subjectteacher as st', 'st.id',  '=', 'sjc.subjectteacherid')
            ->leftJoin('subject as s',         's.id',   '=', 'st.subjectid')
            ->leftJoin('users as teacher',     'teacher.id', '=', 'st.staffid')
            ->leftJoin('schoolterm as t',      't.id',   '=', 'sv.termid')
            ->leftJoin('schoolsession as ss',  'ss.id',  '=', 'sv.sessionid')
            ->where('sv.userid', $userId)
            ->orderByDesc('sv.sessionid')->orderBy('sv.termid')->orderBy('s.subject')->orderBy('sc.schoolclass')
            ->select([
                'sv.id          as svid',
                'sjc.id         as subjectclassid',
                'sc.id          as schoolclassid',
                'sc.schoolclass as sclass',
                'arm.arm        as schoolarm',
                'st.staffid     as staffid',
                's.id           as subjectid',
                's.subject      as subjectname',
                's.subject_code as subjectcode',
                'teacher.name   as teachername',
                't.id           as termid',
                't.term         as termname',
                'ss.id          as sessionid',
                'ss.session     as sessionname',
                'sv.status      as status',
                'sv.updated_at  as updated_at',
            ]);
    }

    /** Broadsheet rows for one subject-class / term / session. */
    private function assignmentRowsQuery(int $subjectclassId, int $termId, int $sessionId)
    {
        return DB::table('broadsheets as b')
            ->join('broadsheet_records as br', 'br.id', '=', 'b.broadsheet_record_id')
            ->where('b.subjectclass_id', $subjectclassId)
            ->where('b.term_id', $termId)
            ->where('br.session_id', $sessionId);
    }

    /** Adds students / vetted / entered / percent to every assignment, in one grouped query. */
    private function attachProgress(Collection $rows): Collection
    {
        $ids = $rows->pluck('subjectclassid')->filter()->unique()->values();

        $progress = $ids->isEmpty() ? collect() : DB::table('broadsheets as b')
            ->join('broadsheet_records as br', 'br.id', '=', 'b.broadsheet_record_id')
            ->whereIn('b.subjectclass_id', $ids)
            ->groupBy('b.subjectclass_id', 'b.term_id', 'br.session_id')
            ->select([
                'b.subjectclass_id', 'b.term_id', 'br.session_id',
                DB::raw('COUNT(*) as students'),
                DB::raw('SUM(CASE WHEN b.vettedstatus = 1 THEN 1 ELSE 0 END) as vetted'),
                DB::raw('SUM(CASE WHEN b.total > 0 THEN 1 ELSE 0 END) as entered'),
            ])
            ->get()
            ->keyBy(fn ($p) => "{$p->subjectclass_id}_{$p->term_id}_{$p->session_id}");

        return $rows->map(function ($r) use ($progress) {
            $p = $progress["{$r->subjectclassid}_{$r->termid}_{$r->sessionid}"] ?? null;
            $r->students = (int) ($p->students ?? 0);
            $r->vetted   = (int) ($p->vetted ?? 0);
            $r->entered  = (int) ($p->entered ?? 0);
            $r->percent  = $r->students ? (int) round($r->vetted / $r->students * 100) : 0;
            $r->status   = in_array($r->status, self::STATUSES, true) ? $r->status : 'pending';
            return $r;
        });
    }

    /**
     * The column is `subjectclassId` in the migration; Eloquent attribute
     * names are case-sensitive, so read it whichever way the DB returns it.
     */
    private function subjectclassIdOf(SubjectVetting $a): int
    {
        $attrs = array_change_key_case($a->getAttributes(), CASE_LOWER);
        return (int) ($attrs['subjectclassid'] ?? 0);
    }

    private function progressFor(SubjectVetting $a): array
    {
        $q = $this->assignmentRowsQuery($this->subjectclassIdOf($a), (int) $a->termid, (int) $a->sessionid);
        return [
            'students' => (clone $q)->count(),
            'vetted'   => (clone $q)->where('b.vettedstatus', 1)->count(),
        ];
    }

    /**
     * Keep the assignment status in step with its rows: all vetted ->
     * completed; otherwise a completed assignment drops back to pending.
     * A 'rejected' assignment stays rejected until the vetter changes it.
     */
    private function syncAssignmentStatus(SubjectVetting $a): array
    {
        $p = $this->progressFor($a);
        $allVetted = $p['students'] > 0 && $p['vetted'] === $p['students'];

        if ($allVetted && !in_array($a->status, ['completed', 'rejected'], true)) {
            $a->status = 'completed';
            $a->save();
        } elseif (!$allVetted && $a->status === 'completed') {
            $a->status = 'pending';
            $a->save();
        }

        return [
            'status'   => $a->status,
            'students' => $p['students'],
            'vetted'   => $p['vetted'],
            'percent'  => $p['students'] ? (int) round($p['vetted'] / $p['students'] * 100) : 0,
        ];
    }

    private function findAssignment(int $subjectclassId, int $termId, int $sessionId): ?SubjectVetting
    {
        return SubjectVetting::where('userid', Auth::id())
            ->where('subjectclassid', $subjectclassId)
            ->where('termid', $termId)
            ->where('sessionid', $sessionId)
            ->first();
    }

    /** Admins who manage vetting assignments may open any broadsheet (read-only). */
    private function canManageAllVettings(): bool
    {
        $user = Auth::user();
        return $user && ($user->can('Update subject-vettings') || $user->can('View subject-vettings'));
    }

    private function broadsheetContext(int $broadsheetId): ?object
    {
        return DB::table('broadsheets as b')
            ->join('broadsheet_records as br', 'br.id', '=', 'b.broadsheet_record_id')
            ->where('b.id', $broadsheetId)
            ->first(['b.id', 'b.subjectclass_id', 'b.term_id', 'br.session_id']);
    }

    /**
     * Rows for the broadsheet page, exactly as stored by score entry. Keyed
     * on the subject-class (not the teacher's staff_id), so a change of
     * teacher mid-term doesn't hide students.
     */
    private function getBroadsheets(int $subjectclassId, int $termId, int $sessionId): Collection
    {
        return Broadsheets::query()
            ->with('assessmentScores')
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
            ->where('broadsheets.subjectclass_id', $subjectclassId)
            ->where('broadsheets.term_id', $termId)
            ->where('broadsheet_records.session_id', $sessionId)
            ->orderBy('studentRegistration.lastname')
            ->orderBy('studentRegistration.firstname')
            ->get([
                'broadsheets.id',
                'broadsheet_records.student_id',
                'studentRegistration.admissionNo as admissionno',
                'studentRegistration.firstname   as fname',
                'studentRegistration.lastname    as lname',
                'studentRegistration.othername   as mname',
                DB::raw('(SELECT sp.picture FROM studentpicture sp WHERE sp.studentid = studentRegistration.id ORDER BY sp.id DESC LIMIT 1) as picture'),
                'broadsheets.total',
                'broadsheets.bf',
                'broadsheets.cum',
                'broadsheets.cum_ave',
                'broadsheets.grade',
                'broadsheets.remark',
                'broadsheets.subject_position_class as position',
                'broadsheets.vettedstatus',
            ]);
    }
}
