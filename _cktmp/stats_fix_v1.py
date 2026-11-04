#!/usr/bin/env python3
"""Fix Admin Score Entry statistics (csskabba-portal).

Problem: student counts came from `studentclass` filtered by term AND session, but a
studentclass row exists only for the term a student was registered in, so most
classes showed 0 students and every percentage collapsed to 0.

New source of truth, per subject sheet:
  students  = the students actually on that subject's scoresheet (broadsheets for the
              subjectclass + term + session) — exactly what the teacher fills in.
              If the sheet hasn't been generated yet, the class enrolment is used.
  class size = student_current_term for class/term/session, then studentclass for
              class/term/session, then studentclass for class/session (any term).
  entered   = an assessment counts once it has a score > 0 or is marked ABS.
  complete  = every student on the sheet has every assessment entered.
All figures come from a handful of batched queries instead of ~7 queries per sheet.
"""
import os, sys, shutil

ROOT = sys.argv[1]
REL = 'app/Http/Controllers/Admin/AdminScoreEntryController.php'
P = os.path.join(ROOT, REL)
s = open(P, encoding='utf-8').read()

def rep(old, new):
    global s
    n = s.count(old)
    if n != 1:
        sys.exit(f'ABORT: expected 1 match, found {n}: {old[:150]!r}')
    s = s.replace(old, new)

# ── 1. per-subject computation in getTeacherSubjects ─────────────────────────
start = s.index("        return $subjects->map(function ($item) use ($termId, $sessionId, $assessmentCountsByCategory, $assessmentIdsByCategory, $assessmentDetailsByCategory) {")
end = s.index("    /**\n     * Get detailed progress for each assessment", start)
s = s[:start] + r"""        if ($subjects->isEmpty()) {
            return $subjects;
        }

        $subjectclassIds = $subjects->pluck('subjectclass_id')->unique()->values()->all();
        $schoolclassIds  = $subjects->pluck('schoolclass_id')->unique()->values()->all();

        // ── Students on each subject's scoresheet (same rows the teacher sees) ──
        $sheetRows = DB::table('broadsheets')
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
            ->join('subjectclass', function ($join) {
                $join->on('subjectclass.id', '=', 'broadsheets.subjectclass_id')
                     ->on('broadsheet_records.subject_id', '=', 'subjectclass.subjectid')
                     ->on('broadsheet_records.schoolclass_id', '=', 'subjectclass.schoolclassid');
            })
            ->whereIn('broadsheets.subjectclass_id', $subjectclassIds)
            ->where('broadsheets.term_id', $termId)
            ->where('broadsheet_records.session_id', $sessionId)
            ->get([
                'broadsheets.id as broadsheet_id',
                'broadsheets.subjectclass_id',
                'broadsheets.staff_id',
                'broadsheets.total',
                'broadsheet_records.student_id',
            ]);
        $sheetBySubjectclass = $sheetRows->groupBy('subjectclass_id');

        // ── Which assessments are entered per broadsheet (score > 0 or ABS) ──
        $enteredByBroadsheet = [];   // broadsheet_id => [assessment_id => true]
        foreach ($sheetRows->pluck('broadsheet_id')->chunk(2000) as $chunk) {
            DB::table('broadsheet_assessment_scores')
                ->whereIn('broadsheet_id', $chunk->all())
                ->whereRaw(BroadsheetAssessmentScore::enteredSql())
                ->get(['broadsheet_id', 'assessment_id'])
                ->each(function ($r) use (&$enteredByBroadsheet) {
                    $enteredByBroadsheet[$r->broadsheet_id][$r->assessment_id] = true;
                });
        }

        // ── Class enrolment for the selected term/session ──
        $classSizes = $this->classEnrolmentCounts($schoolclassIds, $termId, $sessionId);

        // ── Mock sheets: rows and entered rows per subjectclass + teacher ──
        $mockStats = DB::table('broadsheetmock')
            ->join('broadsheet_records_mock', 'broadsheet_records_mock.id', '=', 'broadsheetmock.broadsheet_records_mock_id')
            ->whereIn('broadsheetmock.subjectclass_id', $subjectclassIds)
            ->where('broadsheetmock.term_id', $termId)
            ->where('broadsheet_records_mock.session_id', $sessionId)
            ->groupBy('broadsheetmock.subjectclass_id', 'broadsheetmock.staff_id')
            ->get([
                'broadsheetmock.subjectclass_id',
                'broadsheetmock.staff_id',
                DB::raw('COUNT(DISTINCT broadsheet_records_mock.student_id) as rows_count'),
                DB::raw('COUNT(DISTINCT CASE WHEN broadsheetmock.exam > 0 THEN broadsheet_records_mock.student_id END) as entered_count'),
            ])
            ->keyBy(fn ($r) => $r->subjectclass_id . ':' . $r->staff_id);

        return $subjects->map(function ($item) use (
            $assessmentCountsByCategory, $assessmentIdsByCategory, $assessmentDetailsByCategory,
            $sheetBySubjectclass, $enteredByBroadsheet, $classSizes, $mockStats
        ) {
            $assessmentIds       = $assessmentIdsByCategory[$item->classcategoryid] ?? [];
            $requiredAssessments = count($assessmentIds);

            $item->required_assessment_count = $requiredAssessments;
            $item->assessment_ids            = $assessmentIds;
            $item->assessment_details        = $assessmentDetailsByCategory[$item->classcategoryid] ?? [];

            // One row per student on this sheet (a student can't count twice).
            $rows = ($sheetBySubjectclass[$item->subjectclass_id] ?? collect())->unique('student_id')->values();

            $classSize               = (int) ($classSizes[$item->schoolclass_id] ?? 0);
            $item->class_student_count = $classSize;
            $item->sheet_generated     = $rows->isNotEmpty();
            $studentCount              = $rows->isNotEmpty() ? $rows->count() : $classSize;
            $item->student_count       = $studentCount;

            // Per-student / per-assessment entry counts
            $fully = 0; $partial = 0;
            $perAssessment = array_fill_keys($assessmentIds, 0);
            foreach ($rows as $row) {
                $entered = $enteredByBroadsheet[$row->broadsheet_id] ?? [];
                if ($requiredAssessments > 0) {
                    $done = 0;
                    foreach ($assessmentIds as $aid) {
                        if (isset($entered[$aid])) { $done++; $perAssessment[$aid]++; }
                    }
                    if ($done >= $requiredAssessments) $fully++;
                    elseif ($done > 0) $partial++;
                } elseif ((float) $row->total > 0) {
                    $fully++;   // legacy classes with no assessments configured
                }
            }

            $item->assessment_progress = collect($item->assessment_details)->map(function ($a) use ($perAssessment, $studentCount) {
                $scored = (int) ($perAssessment[$a['id']] ?? 0);
                return [
                    'assessment_id'   => $a['id'],
                    'assessment_name' => $a['name'],
                    'max_score'       => $a['max_score'],
                    'scored_count'    => $scored,
                    'total_students'  => $studentCount,
                    'percentage'      => $studentCount > 0 ? round(($scored / $studentCount) * 100, 1) : 0,
                    'status'          => ($studentCount > 0 && $scored >= $studentCount) ? 'complete' : ($scored > 0 ? 'partial' : 'not_started'),
                ];
            })->values()->all();

            $item->terminal_entries_count = $fully;
            $item->terminal_partial_count = $partial;
            $item->terminal_started_count = $fully + $partial;
            $item->has_terminal_scores    = ($fully + $partial) > 0;
            $item->entry_percentage       = $studentCount > 0 ? round(($fully / $studentCount) * 100, 1) : 0;
            $item->entry_status           = $this->resolveEntryStatus($fully, $fully + $partial, $studentCount);

            // Mock
            $mock         = $mockStats[$item->subjectclass_id . ':' . $item->teacher_id] ?? null;
            $mockRows     = $mock ? (int) $mock->rows_count : 0;
            $mockEntered  = $mock ? (int) $mock->entered_count : 0;
            $mockExpected = $mockRows > 0 ? $mockRows : $studentCount;
            $item->mock_entries_count = $mockEntered;
            $item->mock_percentage    = $mockExpected > 0 ? round(($mockEntered / $mockExpected) * 100, 1) : 0;
            $item->mock_status        = $this->resolveEntryStatus($mockEntered, $mockEntered, $mockExpected);
            $item->has_mock_scores    = $item->mock_status === 'complete';

            return $item;
        });
    }

    /**
     * Number of students enrolled in each class for a term/session.
     * student_current_term is the per-term enrolment record; studentclass only has
     * a row for the term a student was registered in, so it is used as a fallback.
     */
    protected function classEnrolmentCounts(array $schoolclassIds, $termId, $sessionId): array
    {
        if (empty($schoolclassIds)) return [];

        $counts = DB::table('student_current_term')
            ->whereIn('schoolclassId', $schoolclassIds)
            ->where('termId', $termId)
            ->where('sessionId', $sessionId)
            ->groupBy('schoolclassId')
            ->select('schoolclassId', DB::raw('COUNT(DISTINCT studentId) as c'))
            ->pluck('c', 'schoolclassId')
            ->map(fn ($c) => (int) $c)
            ->all();

        $missing = array_values(array_filter($schoolclassIds, fn ($id) => empty($counts[$id])));
        if ($missing) {
            $byTerm = DB::table('studentclass')
                ->whereIn('schoolclassid', $missing)
                ->where('termid', $termId)
                ->where('sessionid', $sessionId)
                ->groupBy('schoolclassid')
                ->select('schoolclassid', DB::raw('COUNT(DISTINCT studentId) as c'))
                ->pluck('c', 'schoolclassid');
            foreach ($byTerm as $cid => $c) $counts[$cid] = (int) $c;
        }

        $missing = array_values(array_filter($schoolclassIds, fn ($id) => empty($counts[$id])));
        if ($missing) {
            $bySession = DB::table('studentclass')
                ->whereIn('schoolclassid', $missing)
                ->where('sessionid', $sessionId)
                ->groupBy('schoolclassid')
                ->select('schoolclassid', DB::raw('COUNT(DISTINCT studentId) as c'))
                ->pluck('c', 'schoolclassid');
            foreach ($bySession as $cid => $c) $counts[$cid] = (int) $c;
        }

        return $counts;
    }

""" + s[end:]

# ── 2. dashboard totals: "completed" means the whole sheet is entered ────────
rep("""        // Get all registered students for this term/session to calculate expected entries
        $totalStudentsInTerm = DB::table('studentclass')
            ->where('sessionid', $sessionId)
            ->where('termid', $termId)
            ->distinct('studentId')
            ->count('studentId');

""", "")
rep("""                if ($subject->has_terminal_scores) {
                    $teacherStats['completed_terminal']++;""", """                if ($subject->entry_status === 'complete') {
                    $teacherStats['completed_terminal']++;""")
rep("""                if ($subject->has_mock_scores) {
                    $teacherStats['completed_mock']++;""", """                if ($subject->mock_status === 'complete') {
                    $teacherStats['completed_mock']++;""")
rep("""            $studentCount = $subjects->first()->student_count;
            $totalSubjects = $subjects->count();
            $completedSubjects = $subjects->where('entry_status', 'complete')->count();
            $classExpectedEntries = $totalSubjects * $studentCount;""",
"""            // Class size = enrolment for the term; expected entries = students on each subject's sheet
            $studentCount = (int) ($subjects->max('class_student_count') ?: $subjects->max('student_count'));
            $totalSubjects = $subjects->count();
            $completedSubjects = $subjects->where('entry_status', 'complete')->count();
            $classExpectedEntries = $subjects->sum('student_count');""")

shutil.copy2(P, P + '.bak_stats')
open(P, 'w', encoding='utf-8').write(s)
print('patched', REL)
