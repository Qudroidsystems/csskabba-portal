<?php

namespace App\Http\Controllers;

use App\Models\Schoolsession;
use App\Models\Schoolterm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

/**
 * Clone academic setup from one session/term into another.
 *
 * Supported modes:
 *   - Term → Term     (e.g. 1st Term 2025/2026 → 2nd Term 2025/2026)
 *   - Session → Session, one term  (e.g. 3rd Term 2024/2025 → 1st Term 2025/2026)
 *   - Session → Session, all terms (each term copied onto the same term in the new session)
 *
 * Cloning is strictly ADDITIVE: nothing in the target is updated or deleted.
 * Rows that already exist in the target are skipped, so the operation can be re-run safely.
 */
class SessionCloneController extends Controller
{
    /**
     * Module registry. `requires` lists modules that must also be selected.
     */
    private const MODULES = [
        'subject_teachers' => [
            'label'       => 'Subject Teachers',
            'description' => 'Teacher ↔ subject assignments',
            'icon'        => 'ri-user-star-line',
            'table'       => 'subjectteacher',
            'requires'    => [],
        ],
        'subject_classes' => [
            'label'       => 'Subject Classes',
            'description' => 'Which subject (and teacher) is offered in which class',
            'icon'        => 'ri-book-open-line',
            'table'       => 'subjectclass',
            'requires'    => ['subject_teachers'],
        ],
        'class_teachers' => [
            'label'       => 'Class Teachers',
            'description' => 'Form/class teacher for each class',
            'icon'        => 'ri-team-line',
            'table'       => 'classteacher',
            'requires'    => [],
        ],
        'compulsory_subjects' => [
            'label'       => 'Compulsory Subjects',
            'description' => 'Compulsory subjects per class with minimum grades',
            'icon'        => 'ri-shield-check-line',
            'table'       => 'compulsory_subject_classes',
            'requires'    => [],
        ],
        'school_bills' => [
            'label'       => 'School Bills per Class',
            'description' => 'Bills attached to each class for the term',
            'icon'        => 'ri-bill-line',
            'table'       => 'school_bill_class_term_session',
            'requires'    => [],
        ],
        'promotion_settings' => [
            'label'       => 'Promotion Rules',
            'description' => 'Promotion rule sets per class',
            'icon'        => 'ri-medal-line',
            'table'       => 'promotion_settings',
            'requires'    => [],
        ],
    ];

    /** @var array<string, array<int,string>> */
    private array $columnCache = [];

    /* ------------------------------------------------------------------ */
    /*  Pages                                                             */
    /* ------------------------------------------------------------------ */

    public function index()
    {
        $pagetitle = 'Clone Session / Term Setup';

        $sessions = Schoolsession::orderByDesc('id')->get(['id', 'session', 'status']);
        $terms    = Schoolterm::orderBy('id')->get(['id', 'term', 'status']);

        $modules = collect(self::MODULES)
            ->filter(fn ($m) => Schema::hasTable($m['table']))
            ->map(fn ($m, $key) => array_merge($m, ['key' => $key]))
            ->values();

        $currentSession = $sessions->firstWhere('status', 'Current');
        $currentTerm    = $terms->first(fn ($t) => (bool) $t->status);

        return view('sessionclone.index', compact(
            'pagetitle', 'sessions', 'terms', 'modules', 'currentSession', 'currentTerm'
        ));
    }

    /* ------------------------------------------------------------------ */
    /*  AJAX: preview                                                     */
    /* ------------------------------------------------------------------ */

    public function preview(Request $request): JsonResponse
    {
        [$ok, $payload] = $this->validatePayload($request);
        if (!$ok) {
            return $payload;
        }

        try {
            $result = $this->run($payload, dryRun: true);

            return response()->json(['success' => true] + $result);
        } catch (\Throwable $e) {
            Log::error('Session clone preview failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Could not build the preview: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* ------------------------------------------------------------------ */
    /*  AJAX: execute                                                     */
    /* ------------------------------------------------------------------ */

    public function clone(Request $request): JsonResponse
    {
        [$ok, $payload] = $this->validatePayload($request);
        if (!$ok) {
            return $payload;
        }

        DB::beginTransaction();
        try {
            $result = $this->run($payload, dryRun: false);
            DB::commit();

            Log::info('Session/term setup cloned', [
                'by'      => auth()->id(),
                'payload' => $payload,
                'totals'  => $result['totals'],
            ]);

            $created = $result['totals']['create'];

            return response()->json([
                'success' => true,
                'message' => $created > 0
                    ? "{$created} record(s) cloned successfully."
                    : 'Nothing new to clone — the target already has everything.',
            ] + $result);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Session clone failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Cloning failed and no changes were saved: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Validation                                                        */
    /* ------------------------------------------------------------------ */

    /**
     * @return array{0:bool,1:mixed}
     */
    private function validatePayload(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'source_session' => 'required|exists:schoolsession,id',
            'source_term'    => 'required',
            'target_session' => 'required|exists:schoolsession,id',
            'target_term'    => 'nullable',
            'modules'        => 'required|array|min:1',
            'modules.*'      => 'in:' . implode(',', array_keys(self::MODULES)),
        ], [
            'modules.required' => 'Select at least one item to clone.',
            'modules.min'      => 'Select at least one item to clone.',
        ]);

        if ($validator->fails()) {
            return [false, response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422)];
        }

        $allTerms     = $request->input('source_term') === 'all';
        $validTermIds = Schoolterm::pluck('id')->map(fn ($id) => (string) $id)->all();

        if (!$allTerms && !in_array((string) $request->input('source_term'), $validTermIds, true)) {
            return [false, response()->json(['success' => false, 'message' => 'Select a valid source term.'], 422)];
        }

        if (!$allTerms && !in_array((string) $request->input('target_term'), $validTermIds, true)) {
            return [false, response()->json(['success' => false, 'message' => 'Select a valid target term.'], 422)];
        }

        // Build list of [sourceTerm => targetTerm] pairs
        $pairs = $allTerms
            ? array_combine($validTermIds, $validTermIds)
            : [(string) $request->input('source_term') => (string) $request->input('target_term')];

        $sourceSession = (string) $request->input('source_session');
        $targetSession = (string) $request->input('target_session');

        foreach ($pairs as $s => $t) {
            if ($sourceSession === $targetSession && (string) $s === (string) $t) {
                return [false, response()->json([
                    'success' => false,
                    'message' => 'Source and target are the same session and term. Choose a different target.',
                ], 422)];
            }
        }

        // Auto-include dependencies (e.g. subject classes need subject teachers)
        $modules = array_values(array_unique($request->input('modules')));
        foreach ($modules as $m) {
            foreach (self::MODULES[$m]['requires'] as $req) {
                if (!in_array($req, $modules, true)) {
                    $modules[] = $req;
                }
            }
        }
        // Keep registry order so dependencies run first
        $modules = array_values(array_filter(array_keys(self::MODULES), fn ($k) => in_array($k, $modules, true)));
        $modules = array_values(array_filter($modules, fn ($k) => Schema::hasTable(self::MODULES[$k]['table'])));

        return [true, [
            'source_session' => $sourceSession,
            'target_session' => $targetSession,
            'pairs'          => $pairs,
            'modules'        => $modules,
        ]];
    }

    /* ------------------------------------------------------------------ */
    /*  Engine                                                            */
    /* ------------------------------------------------------------------ */

    /**
     * Runs every selected module for every term pair.
     * In dry-run mode nothing is written; counts and sample rows are returned.
     */
    private function run(array $p, bool $dryRun): array
    {
        $summary = [];
        foreach ($p['modules'] as $m) {
            $summary[$m] = [
                'key'     => $m,
                'label'   => self::MODULES[$m]['label'],
                'icon'    => self::MODULES[$m]['icon'],
                'source'  => 0,
                'create'  => 0,
                'skip'    => 0,
                'samples' => [],
                'notes'   => [],
            ];
        }

        foreach ($p['pairs'] as $srcTerm => $tgtTerm) {
            $ctx = [
                'src_session' => $p['source_session'],
                'src_term'    => (string) $srcTerm,
                'tgt_session' => $p['target_session'],
                'tgt_term'    => (string) $tgtTerm,
                'dry'         => $dryRun,
                // old subjectteacher id => new (or already-existing) subjectteacher id
                'st_map'      => [],
                // dry-run placeholders for subjectteacher rows that would be created
                'st_virtual'  => [],
            ];

            foreach ($p['modules'] as $m) {
                $method = 'clone' . str_replace('_', '', ucwords($m, '_'));
                $res    = $this->{$method}($ctx);

                $summary[$m]['source'] += $res['source'];
                $summary[$m]['create'] += $res['create'];
                $summary[$m]['skip']   += $res['skip'];
                $summary[$m]['notes']   = array_values(array_unique(array_merge($summary[$m]['notes'], $res['notes'] ?? [])));

                if (count($summary[$m]['samples']) < 60) {
                    $summary[$m]['samples'] = array_slice(
                        array_merge($summary[$m]['samples'], $res['samples']),
                        0,
                        60
                    );
                }
            }
        }

        $totals = ['source' => 0, 'create' => 0, 'skip' => 0];
        foreach ($summary as $s) {
            $totals['source'] += $s['source'];
            $totals['create'] += $s['create'];
            $totals['skip']   += $s['skip'];
        }

        return [
            'modules' => array_values($summary),
            'totals'  => $totals,
            'dry_run' => $dryRun,
        ];
    }

    /* ---------------------------- Modules ----------------------------- */

    private function cloneSubjectTeachers(array &$ctx): array
    {
        $rows = DB::table('subjectteacher as st')
            ->leftJoin('users as u', 'u.id', '=', 'st.staffid')
            ->leftJoin('subject as s', 's.id', '=', 'st.subjectid')
            ->where('st.sessionid', $ctx['src_session'])
            ->where('st.termid', $ctx['src_term'])
            ->select('st.*', 'u.name as _staff', 's.subject as _subject')
            ->get();

        $existing = DB::table('subjectteacher')
            ->where('sessionid', $ctx['tgt_session'])
            ->where('termid', $ctx['tgt_term'])
            ->get(['id', 'staffid', 'subjectid'])
            ->keyBy(fn ($r) => $r->staffid . '|' . $r->subjectid);

        $out = ['source' => $rows->count(), 'create' => 0, 'skip' => 0, 'samples' => []];

        foreach ($rows as $row) {
            $key   = $row->staffid . '|' . $row->subjectid;
            $label = trim(($row->_staff ?? 'Unknown staff') . ' — ' . ($row->_subject ?? 'Unknown subject'));

            if ($existing->has($key)) {
                $ctx['st_map'][$row->id] = $existing->get($key)->id;
                $out['skip']++;
                $out['samples'][] = ['text' => $label, 'status' => 'exists', 'term' => $ctx['tgt_term']];
                continue;
            }

            if ($ctx['dry']) {
                $ctx['st_map'][$row->id]     = 'virtual:' . $row->id;
                $ctx['st_virtual'][$row->id] = true;
            } else {
                $data = $this->copyRow('subjectteacher', $row, [
                    'sessionid' => $ctx['tgt_session'],
                    'termid'    => $ctx['tgt_term'],
                ]);
                $newId = DB::table('subjectteacher')->insertGetId($data);
                $ctx['st_map'][$row->id] = $newId;
                $existing->put($key, (object) ['id' => $newId, 'staffid' => $row->staffid, 'subjectid' => $row->subjectid]);
            }

            $out['create']++;
            $out['samples'][] = ['text' => $label, 'status' => 'new', 'term' => $ctx['tgt_term']];
        }

        return $out;
    }

    private function cloneSubjectClasses(array &$ctx): array
    {
        $sourceStIds = DB::table('subjectteacher')
            ->where('sessionid', $ctx['src_session'])
            ->where('termid', $ctx['src_term'])
            ->pluck('id');

        $rows = DB::table('subjectclass as sc')
            ->leftJoin('schoolclass as c', 'c.id', '=', 'sc.schoolclassid')
            ->leftJoin('schoolarm as a', 'a.id', '=', 'c.arm')
            ->leftJoin('subject as s', 's.id', '=', 'sc.subjectid')
            ->whereIn('sc.subjectteacherid', $sourceStIds)
            ->select('sc.*', 'c.schoolclass as _class', 'a.arm as _arm', 's.subject as _subject')
            ->get();

        $out = ['source' => $rows->count(), 'create' => 0, 'skip' => 0, 'samples' => [], 'notes' => []];

        // Existing pairs in target, keyed by class|subjectteacher
        $realTargetStIds = array_filter($ctx['st_map'], fn ($v) => is_int($v) || ctype_digit((string) $v));
        $existing = DB::table('subjectclass')
            ->whereIn('subjectteacherid', array_values($realTargetStIds))
            ->get(['schoolclassid', 'subjectteacherid'])
            ->mapWithKeys(fn ($r) => [$r->schoolclassid . '|' . $r->subjectteacherid => true]);

        $hasTerm      = $this->hasColumn('subjectclass', 'termid');
        $hasSessionId = $this->hasColumn('subjectclass', 'sessionid');
        $hasSession   = $this->hasColumn('subjectclass', 'session');

        foreach ($rows as $row) {
            $label = trim(($row->_class ?? 'Class') . ' ' . ($row->_arm ?? '')) . ' — ' . ($row->_subject ?? 'Subject');

            $newStId = $ctx['st_map'][$row->subjectteacherid] ?? null;
            if ($newStId === null) {
                $out['skip']++;
                $out['notes'][] = 'Some subject classes were skipped because their subject teacher could not be mapped.';
                continue;
            }

            $key = $row->schoolclassid . '|' . $newStId;
            if (isset($existing[$key])) {
                $out['skip']++;
                $out['samples'][] = ['text' => $label, 'status' => 'exists', 'term' => $ctx['tgt_term']];
                continue;
            }

            if (!$ctx['dry']) {
                $overrides = ['subjectteacherid' => $newStId];
                if ($hasTerm) {
                    $overrides['termid'] = $ctx['tgt_term'];
                }
                if ($hasSessionId) {
                    $overrides['sessionid'] = $ctx['tgt_session'];
                }
                if ($hasSession) {
                    $overrides['session'] = $ctx['tgt_session'];
                }
                // Fresh assignment: teacher editing re-enabled, lock metadata cleared
                foreach (['teacher_editing_enabled' => 1, 'teacher_editing_disabled_at' => null, 'teacher_editing_disabled_by' => null] as $col => $val) {
                    if ($this->hasColumn('subjectclass', $col)) {
                        $overrides[$col] = $val;
                    }
                }

                DB::table('subjectclass')->insert($this->copyRow('subjectclass', $row, $overrides));
            }

            $existing[$key] = true;
            $out['create']++;
            $out['samples'][] = ['text' => $label, 'status' => 'new', 'term' => $ctx['tgt_term']];
        }

        return $out;
    }

    private function cloneClassTeachers(array &$ctx): array
    {
        $rows = DB::table('classteacher as ct')
            ->leftJoin('users as u', 'u.id', '=', 'ct.staffid')
            ->leftJoin('schoolclass as c', 'c.id', '=', 'ct.schoolclassid')
            ->leftJoin('schoolarm as a', 'a.id', '=', 'c.arm')
            ->where('ct.sessionid', $ctx['src_session'])
            ->where('ct.termid', $ctx['src_term'])
            ->select('ct.*', 'u.name as _staff', 'c.schoolclass as _class', 'a.arm as _arm')
            ->get();

        // A class may only have one class teacher per term/session — skip if already set
        $taken = DB::table('classteacher')
            ->where('sessionid', $ctx['tgt_session'])
            ->where('termid', $ctx['tgt_term'])
            ->pluck('schoolclassid')
            ->map(fn ($v) => (string) $v)
            ->flip();

        $out = ['source' => $rows->count(), 'create' => 0, 'skip' => 0, 'samples' => []];

        foreach ($rows as $row) {
            $label = trim(($row->_class ?? 'Class') . ' ' . ($row->_arm ?? '')) . ' — ' . ($row->_staff ?? 'Unknown staff');

            if (isset($taken[(string) $row->schoolclassid])) {
                $out['skip']++;
                $out['samples'][] = ['text' => $label, 'status' => 'exists', 'term' => $ctx['tgt_term']];
                continue;
            }

            if (!$ctx['dry']) {
                DB::table('classteacher')->insert($this->copyRow('classteacher', $row, [
                    'sessionid' => $ctx['tgt_session'],
                    'termid'    => $ctx['tgt_term'],
                ]));
            }

            $taken[(string) $row->schoolclassid] = true;
            $out['create']++;
            $out['samples'][] = ['text' => $label, 'status' => 'new', 'term' => $ctx['tgt_term']];
        }

        return $out;
    }

    private function cloneCompulsorySubjects(array &$ctx): array
    {
        $rows = DB::table('compulsory_subject_classes as cs')
            ->leftJoin('schoolclass as c', 'c.id', '=', 'cs.schoolclassid')
            ->leftJoin('schoolarm as a', 'a.id', '=', 'c.arm')
            ->leftJoin('subject as s', 's.id', '=', 'cs.subjectId')
            ->where('cs.sessionid', $ctx['src_session'])
            ->where('cs.termid', $ctx['src_term'])
            ->select('cs.*', 'c.schoolclass as _class', 'a.arm as _arm', 's.subject as _subject')
            ->get();

        $existing = DB::table('compulsory_subject_classes')
            ->where('sessionid', $ctx['tgt_session'])
            ->where('termid', $ctx['tgt_term'])
            ->get(['schoolclassid', 'subjectId'])
            ->mapWithKeys(fn ($r) => [$r->schoolclassid . '|' . $r->subjectId => true]);

        return $this->simpleClone(
            $ctx, $rows, 'compulsory_subject_classes',
            fn ($r) => $r->schoolclassid . '|' . $r->subjectId,
            $existing,
            ['sessionid' => $ctx['tgt_session'], 'termid' => $ctx['tgt_term']],
            fn ($r) => trim(($r->_class ?? 'Class') . ' ' . ($r->_arm ?? '')) . ' — ' . ($r->_subject ?? 'Subject')
                . ($r->min_grade ? " (min {$r->min_grade})" : '')
        );
    }

    private function cloneSchoolBills(array &$ctx): array
    {
        $rows = DB::table('school_bill_class_term_session as b')
            ->leftJoin('school_bill as sb', 'sb.id', '=', 'b.bill_id')
            ->leftJoin('schoolclass as c', 'c.id', '=', 'b.class_id')
            ->leftJoin('schoolarm as a', 'a.id', '=', 'c.arm')
            ->where('b.session_id', $ctx['src_session'])
            ->where('b.termid_id', $ctx['src_term'])
            ->select('b.*', 'sb.title as _bill', 'sb.bill_amount as _amount', 'c.schoolclass as _class', 'a.arm as _arm')
            ->get();

        $existing = DB::table('school_bill_class_term_session')
            ->where('session_id', $ctx['tgt_session'])
            ->where('termid_id', $ctx['tgt_term'])
            ->get(['bill_id', 'class_id'])
            ->mapWithKeys(fn ($r) => [$r->bill_id . '|' . $r->class_id => true]);

        $overrides = ['session_id' => $ctx['tgt_session'], 'termid_id' => $ctx['tgt_term']];
        if ($this->hasColumn('school_bill_class_term_session', 'created_by')) {
            $overrides['created_by'] = auth()->id();
        }

        return $this->simpleClone(
            $ctx, $rows, 'school_bill_class_term_session',
            fn ($r) => $r->bill_id . '|' . $r->class_id,
            $existing,
            $overrides,
            fn ($r) => trim(($r->_class ?? 'Class') . ' ' . ($r->_arm ?? '')) . ' — ' . ($r->_bill ?? 'Bill')
                . ($r->_amount !== null ? ' (₦' . number_format((float) $r->_amount, 2) . ')' : '')
        );
    }

    private function clonePromotionSettings(array &$ctx): array
    {
        $rows = DB::table('promotion_settings as p')
            ->leftJoin('schoolclass as c', 'c.id', '=', 'p.schoolclass_id')
            ->leftJoin('schoolarm as a', 'a.id', '=', 'c.arm')
            ->where('p.session_id', $ctx['src_session'])
            ->where('p.term_id', $ctx['src_term'])
            ->select('p.*', 'c.schoolclass as _class', 'a.arm as _arm')
            ->get();

        $existing = DB::table('promotion_settings')
            ->where('session_id', $ctx['tgt_session'])
            ->where('term_id', $ctx['tgt_term'])
            ->get(['schoolclass_id', 'rule_set_name'])
            ->mapWithKeys(fn ($r) => [$r->schoolclass_id . '|' . strtolower((string) $r->rule_set_name) => true]);

        return $this->simpleClone(
            $ctx, $rows, 'promotion_settings',
            fn ($r) => $r->schoolclass_id . '|' . strtolower((string) $r->rule_set_name),
            $existing,
            ['session_id' => $ctx['tgt_session'], 'term_id' => $ctx['tgt_term']],
            fn ($r) => trim(($r->_class ?? 'All classes') . ' ' . ($r->_arm ?? '')) . ' — ' . ($r->rule_set_name ?: 'Default rules')
        );
    }

    /* ---------------------------- Helpers ----------------------------- */

    /**
     * Generic "copy rows that don't exist yet" routine.
     */
    private function simpleClone(
        array $ctx,
        $rows,
        string $table,
        callable $keyFn,
        $existing,
        array $overrides,
        callable $labelFn
    ): array {
        $out  = ['source' => $rows->count(), 'create' => 0, 'skip' => 0, 'samples' => []];
        $seen = $existing->all();

        foreach ($rows as $row) {
            $key   = $keyFn($row);
            $label = $labelFn($row);

            if (isset($seen[$key])) {
                $out['skip']++;
                $out['samples'][] = ['text' => $label, 'status' => 'exists', 'term' => $ctx['tgt_term']];
                continue;
            }

            if (!$ctx['dry']) {
                DB::table($table)->insert($this->copyRow($table, $row, $overrides));
            }

            $seen[$key] = true;
            $out['create']++;
            $out['samples'][] = ['text' => $label, 'status' => 'new', 'term' => $ctx['tgt_term']];
        }

        return $out;
    }

    /**
     * Copy a DB row to an insertable array: only real table columns,
     * drops id/timestamps, applies overrides, stamps fresh timestamps.
     */
    private function copyRow(string $table, object $row, array $overrides): array
    {
        $cols = $this->columns($table);
        $data = [];

        foreach ((array) $row as $col => $val) {
            if (str_starts_with($col, '_') || in_array($col, ['id', 'created_at', 'updated_at'], true)) {
                continue;
            }
            if (in_array($col, $cols, true)) {
                $data[$col] = $val;
            }
        }

        foreach ($overrides as $col => $val) {
            if (in_array($col, $cols, true)) {
                $data[$col] = $val;
            }
        }

        $now = now();
        if (in_array('created_at', $cols, true)) {
            $data['created_at'] = $now;
        }
        if (in_array('updated_at', $cols, true)) {
            $data['updated_at'] = $now;
        }

        return $data;
    }

    private function columns(string $table): array
    {
        return $this->columnCache[$table] ??= Schema::getColumnListing($table);
    }

    private function hasColumn(string $table, string $column): bool
    {
        return in_array($column, $this->columns($table), true);
    }
}
