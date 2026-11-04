#!/usr/bin/env python3
"""Port the ABS (absent) score feature into csskabba-portal.

csskabba stores scores per assessment in broadsheet_assessment_scores, so the
absent flag lives there (is_absent), instead of Topclass's broadsheets.absent_fields.
Every replacement must match exactly once, or the script stops before writing anything.
"""
import os, re, sys, shutil

ROOT = sys.argv[1]
files = {}

def load(rel):
    if rel not in files:
        files[rel] = open(os.path.join(ROOT, rel), encoding='utf-8').read()
    return files[rel]

def rep(rel, old, new, count=1, first_only=False):
    s = load(rel)
    n = s.count(old)
    if n != count:
        sys.exit(f'ABORT {rel}: expected {count} match(es), found {n} for:\n{old[:200]}')
    files[rel] = s.replace(old, new, 1) if first_only else s.replace(old, new)

def resub(rel, pattern, repl, min_count=1, start_marker=None):
    s = load(rel)
    head, body = ('', s)
    if start_marker:
        i = s.index(start_marker)
        head, body = s[:i], s[i:]
    body, n = re.subn(pattern, repl, body)
    if n < min_count:
        sys.exit(f'ABORT {rel}: pattern {pattern} matched {n}')
    files[rel] = head + body
    return n

# ─────────────────────────────────────────────────────────────── model
M = 'app/Models/BroadsheetAssessmentScore.php'
rep(M, """        'assessment_id',
        'score',
    ];
""", """        'assessment_id',
        'score',
        'is_absent',   // student was absent for this assessment (score kept at 0)
    ];

    protected $casts = [
        'is_absent' => 'boolean',
    ];

    // =========================================================================
    // ABSENCE ("ABS") HELPERS
    // Staff type ABS (or A / ABSENT) instead of a score when a student missed
    // an assessment. It counts as 0 in every calculation, but is remembered so
    // scoresheets and reports show ABS instead of a plain 0.
    // =========================================================================

    protected static ?bool $absentColumnExists = null;

    /** True when a typed score value means "absent" (ABS / ABSENT / A). */
    public static function isAbsentMark($value): bool
    {
        return is_string($value)
            && in_array(strtoupper(trim($value)), ['ABS', 'ABSENT', 'A'], true);
    }

    /**
     * Absent only while the stored score is still 0, so a real score entered
     * later (e.g. by an import) always wins over a stale ABS flag.
     */
    public function isAbsent(): bool
    {
        return (bool) ($this->is_absent ?? false) && (float) ($this->score ?? 0) == 0.0;
    }

    /** Validation rule for a score: a number 0 or more, or ABS. */
    public static function scoreRule(): array
    {
        return ['required', function ($attribute, $value, $fail) {
            if (self::isAbsentMark($value)) return;
            if (!is_numeric($value) || $value < 0) {
                $fail('Score must be a number, or ABS if the student was absent.');
            }
        }];
    }

    /** Whether the is_absent column exists yet (so saving keeps working before migrating). */
    public static function hasAbsentColumn(): bool
    {
        if (self::$absentColumnExists === null) {
            try {
                self::$absentColumnExists = \\Illuminate\\Support\\Facades\\Schema::hasColumn((new static)->getTable(), 'is_absent');
            } catch (\\Throwable $e) {
                self::$absentColumnExists = false;
            }
        }
        return self::$absentColumnExists;
    }

    /** Attributes to save for a score, including the absent flag when supported. */
    public static function scoreAttributes($score, bool $absent = false): array
    {
        $attrs = ['score' => $score];
        if (self::hasAbsentColumn()) {
            $attrs['is_absent'] = $absent;
        }
        return $attrs;
    }

    /** SQL condition for "this score has been dealt with" (scored, or marked absent). */
    public static function enteredSql(string $table = 'broadsheet_assessment_scores'): string
    {
        return self::hasAbsentColumn()
            ? "({$table}.score > 0 OR {$table}.is_absent = 1)"
            : "{$table}.score > 0";
    }
""")

# ───────────────────────────────────────────────────── teacher controller
T = 'app/Http/Controllers/MyScoreSheetController.php'
rep(T, """                'assessment_id' => 'required|exists:assessments,id',
                'score' => 'required|numeric|min:0',""",
"""                'assessment_id' => 'required|exists:assessments,id',
                // a number, or "ABS" when the student was absent
                'score' => BroadsheetAssessmentScore::scoreRule(),""")
rep(T, """            $score = $validated['score'];
            $isSub = $validated['is_sub'] ?? false;""",
"""            $isAbsent = BroadsheetAssessmentScore::isAbsentMark($validated['score']);
            $score = $isAbsent ? 0.0 : (float) $validated['score'];
            $isSub = $validated['is_sub'] ?? false;""")
rep(T, """                $assessmentId,
                $score,
                $broadsheet,""", """                $assessmentId,
                $score,
                $isAbsent,
                $broadsheet,""")
rep(T, """                        ['broadsheet_id' => $broadsheetId, 'assessment_id' => $assessmentId],
                        ['score' => $score]""", """                        ['broadsheet_id' => $broadsheetId, 'assessment_id' => $assessmentId],
                        BroadsheetAssessmentScore::scoreAttributes($score, $isAbsent)""")
rep(T, """                            ['score' => max(0, min($normalized, $assessment->max_score))]""",
"""                            BroadsheetAssessmentScore::scoreAttributes(max(0, min($normalized, $assessment->max_score)), false)""")
rep(T, """                            ['score' => max(0, min($normalized, $parentAssessment->max_score))]""",
"""                            BroadsheetAssessmentScore::scoreAttributes(max(0, min($normalized, $parentAssessment->max_score)), false)""")
rep(T, """                        $componentId = (int) $componentId;
                        $inputScore = max(0, (float) $inputScore);
""", """                        $componentId = (int) $componentId;
                        $isAbsent = BroadsheetAssessmentScore::isAbsentMark($inputScore);
                        $inputScore = $isAbsent ? 0.0 : max(0, (float) $inputScore);
""")
rep(T, """                            ['score' => min($inputScore, $model->max_score)]""",
"""                            BroadsheetAssessmentScore::scoreAttributes(min($inputScore, $model->max_score), $isAbsent)""")
rep(T, """                'data' => [
                    'total' => $broadsheet->total,""", """                'data' => [
                    'is_absent' => $isAbsent,
                    'total' => $broadsheet->total,""", count=2, first_only=True)
rep(T, """                    'score' => $s ? floatval($s->score) : 0,
                ];""", """                    'score' => $s ? floatval($s->score) : 0,
                    'is_absent' => $s ? $s->isAbsent() : false,
                ];""")

# ─────────────────────────────────────────────────────── admin controller
A = 'app/Http/Controllers/Admin/AdminScoreEntryController.php'
rep(A, """                'score'             => 'required|numeric|min:0',""",
"""                // a number, or "ABS" when the student was absent
                'score'             => BroadsheetAssessmentScore::scoreRule(),""")
rep(A, """            $score           = $validated['score'];""",
"""            $isAbsent        = BroadsheetAssessmentScore::isAbsentMark($validated['score']);
            $score           = $isAbsent ? 0.0 : (float) $validated['score'];""")
rep(A, """                $broadsheetId, $assessmentId, $score, $broadsheet, $isSub,""",
"""                $broadsheetId, $assessmentId, $score, $isAbsent, $broadsheet, $isSub,""")
rep(A, """                        ['broadsheet_id' => $broadsheetId, 'assessment_id' => $assessmentId],
                        ['score' => $score]""", """                        ['broadsheet_id' => $broadsheetId, 'assessment_id' => $assessmentId],
                        BroadsheetAssessmentScore::scoreAttributes($score, $isAbsent)""")
rep(A, """                            ['score' => max(0, min($normalized, $assessment->max_score))]""",
"""                            BroadsheetAssessmentScore::scoreAttributes(max(0, min($normalized, $assessment->max_score)), false)""")
rep(A, """                            ['score' => max(0, min($normalized, $parentAssessment->max_score))]""",
"""                            BroadsheetAssessmentScore::scoreAttributes(max(0, min($normalized, $parentAssessment->max_score)), false)""")
rep(A, """                        $componentId = (int) $componentId;
                        $inputScore  = max(0, (float) $inputScore);
""", """                        $componentId = (int) $componentId;
                        $isAbsent    = BroadsheetAssessmentScore::isAbsentMark($inputScore);
                        $inputScore  = $isAbsent ? 0.0 : max(0, (float) $inputScore);
""")
rep(A, """                            ['score' => min($inputScore, $model->max_score)]""",
"""                            BroadsheetAssessmentScore::scoreAttributes(min($inputScore, $model->max_score), $isAbsent)""")
rep(A, """                'data'    => [
                    'total'                        => $broadsheet->total,""", """                'data'    => [
                    'is_absent'                    => $isAbsent,
                    'total'                        => $broadsheet->total,""")
rep(A, """'score' => $s ? floatval($s->score) : 0];""",
"""'score' => $s ? floatval($s->score) : 0, 'is_absent' => $s ? $s->isAbsent() : false];""")
# progress counts: a student marked ABS has been dealt with by the teacher
rep(A, """                ->where('broadsheet_assessment_scores.assessment_id', $assessmentId)
                ->where('broadsheet_assessment_scores.score', '>', 0)""",
"""                ->where('broadsheet_assessment_scores.assessment_id', $assessmentId)
                ->whereRaw(BroadsheetAssessmentScore::enteredSql())""")
rep(A, """                     ->whereIn('broadsheet_assessment_scores.assessment_id', $assessmentIds)
                     ->where('broadsheet_assessment_scores.score', '>', 0);""",
"""                     ->whereIn('broadsheet_assessment_scores.assessment_id', $assessmentIds)
                     ->whereRaw(BroadsheetAssessmentScore::enteredSql());""")

# ───────────────────────────────────────────── report controller (drawer JSON)
R = 'app/Http/Controllers/ViewStudentReportController.php'
rep(R, """                            'assessment_id' => $as->assessment_id,
                            'score'         => $as->score !== null ? (float) $as->score : null,""",
"""                            'assessment_id' => $as->assessment_id,
                            'score'         => $as->score !== null ? (float) $as->score : null,
                            'is_absent'     => method_exists($as, 'isAbsent') ? $as->isAbsent() : false,""")

# ──────────────────────────────────────────────────────────── scoresheet views
CSS_ANCHOR = ".score-input:disabled   { background: #f3f4f6; cursor: not-allowed; opacity: 0.7; }\n"
CSS_ADD = CSS_ANCHOR + """.score-input.is-absent, .score-input-large.is-absent { color: #b91c1c; font-weight: 700; letter-spacing: .5px; background: #fff7ed; border-color: #fdba74; }
.score-input.is-absent.is-saved { border-color: var(--ss-success) !important; }
.abs-hint kbd { background: #fff7ed; color: #b91c1c; border: 1px solid #fdba74; font-weight: 700; padding: 0 4px; }
"""
HINT_ANCHOR = """<small class="text-muted"><i class="ri-keyboard-line me-1"></i>Ctrl+S to save</small>"""
HINT_ADD = """<small class="text-muted abs-hint"><i class="ri-user-unfollow-line me-1"></i>Student absent? Type <kbd>ABS</kbd> (or <kbd>A</kbd>) instead of 0</small>
                        """ + HINT_ANCHOR
JS_HELPERS = """/* ── Absent ("ABS") handling ─────────────────────────────────────────
   Staff type ABS (or A / ABSENT) in a score cell when the student was
   absent. It counts as 0 in every calculation, but is saved and shown as
   ABS so it is not confused with a real score of 0.                       */
const isAbs        = v => /^\\s*(abs|absent|a)\\s*$/i.test(String(v ?? ''));
const scoreNum     = v => isAbs(v) ? 0 : (parseFloat(v) || 0);
const scoreKey     = v => isAbs(v) ? 'ABS' : String(scoreNum(v));
const scorePayload = v => isAbs(v) ? 'ABS' : scoreNum(v);

function validateInput(inp, normalize = false) {
    const raw = String(inp.value ?? '').trim();
    const abs = isAbs(raw);
    if (abs && normalize) inp.value = 'ABS';
    inp.classList.toggle('is-absent', abs);
    if (abs || raw === '') { inp.classList.remove('is-invalid'); return true; }

    const max = parseFloat(inp.dataset.max) || 0;
    const ok  = /^\\d*\\.?\\d+$|^\\d+\\.$/.test(raw) && parseFloat(raw) >= 0 && parseFloat(raw) <= max;
    inp.classList.toggle('is-invalid', !ok);
    return ok;
}
"""
INVALID_MSG = "${invalid} score(s) are not valid. Enter a number up to the maximum, or ABS if the student was absent."

def input_markup(rel, lock_var):
    rep(rel, """                                    $scoreValue = $scoreObj ? $scoreObj->score : 0;
                                @endphp""", """                                    $scoreValue = $scoreObj ? $scoreObj->score : 0;
                                    $isAbsent   = $scoreObj ? $scoreObj->isAbsent() : false;
                                    $shownValue = $isAbsent ? 'ABS' : $scoreValue;
                                @endphp""")
    rep(rel, """                                    <input type="number"
                                           class="score-input"
                                           data-field="{{ $assessment->id }}"
                                           data-max="{{ $assessment->max_score }}"
                                           data-id="{{ $broadsheet->id }}"
                                           data-original="{{ $scoreValue }}"
                                           data-assessment-name="{{ $assessment->name }}"
                                           value="{{ $scoreValue }}"
                                           min="0" max="{{ $assessment->max_score }}" step="0.1"
                                           {{ %s ? 'disabled' : '' }}>""" % lock_var,
"""                                    <input type="text"
                                           class="score-input {{ $isAbsent ? 'is-absent' : '' }}"
                                           data-field="{{ $assessment->id }}"
                                           data-max="{{ $assessment->max_score }}"
                                           data-id="{{ $broadsheet->id }}"
                                           data-original="{{ $shownValue }}"
                                           data-assessment-name="{{ $assessment->name }}"
                                           value="{{ $shownValue }}"
                                           maxlength="6" autocomplete="off"
                                           title="Enter a score (max {{ $assessment->max_score }}), or ABS if the student was absent"
                                           {{ %s ? 'disabled' : '' }}>""" % lock_var)
    rep(rel, CSS_ANCHOR, CSS_ADD)
    rep(rel, HINT_ANCHOR, HINT_ADD)

# Teacher scoresheet
V = 'resources/views/subjectscoresheet/index.blade.php'
input_markup(V, '$isRowLocked')
rep(V, """function validateInput(inp) {
    const max = parseFloat(inp.dataset.max) || 0;
    const val = parseFloat(inp.value)       || 0;
    inp.classList.toggle('is-invalid', val > max);
    return val <= max;
}
""", JS_HELPERS)
rep(V, """            score           : parseFloat(input.value) || 0,""", """            score           : scorePayload(input.value),""")
rep(V, """    const invalid = document.querySelectorAll('.score-input.is-invalid').length;
    if (invalid) { Swal.fire({ icon:'warning', title:'Invalid Scores', text:`${invalid} score(s) exceed their maximum.` }); return; }""",
"""    document.querySelectorAll('.score-input:not(:disabled)').forEach(i => validateInput(i, true));
    const invalid = document.querySelectorAll('.score-input.is-invalid').length;
    if (invalid) { Swal.fire({ icon:'warning', title:'Invalid Scores', text:`%s` }); return; }""" % INVALID_MSG)
rep(V, """row.querySelectorAll('.score-input').forEach(inp => { assessments[inp.dataset.field] = parseFloat(inp.value) || 0; });""",
"""row.querySelectorAll('.score-input').forEach(inp => { assessments[inp.dataset.field] = scorePayload(inp.value); });""")
rep(V, """            if (!validateInput(this)) return;
            if (window.isEditingDisabled) return;
            const orig = parseFloat(this.dataset.original) || 0;
            const curr = parseFloat(this.value)            || 0;
            if (Math.abs(curr - orig) > 0.001) {""", """            if (!validateInput(this, true)) return;
            if (window.isEditingDisabled) return;
            if (scoreKey(this.dataset.original) !== scoreKey(this.value)) {""")
rep(V, """            if (validateInput(this)) saveIndividualScore(this);""",
"""            if (validateInput(this, true)) {
                this.dataset.original = this.value;
                saveIndividualScore(this);
            }""")

# Admin scoresheet
W = 'resources/views/admin/score-entry/scoresheet.blade.php'
input_markup(W, '$isLocked')
rep(W, """function validateInput(inp) {
    const max = parseFloat(inp.dataset.max) || 0;
    const val = parseFloat(inp.value) || 0;
    inp.classList.toggle('is-invalid', val > max);
    return val <= max;
}
""", JS_HELPERS)
rep(W, """    const originalValue = parseFloat(input.dataset.original) || 0;
    const newValue = parseFloat(input.value) || 0;

    if (Math.abs(newValue - originalValue) < 0.01) return;
""", """    const originalValue = input.dataset.original ?? '';
    if (scoreKey(originalValue) === scoreKey(input.value)) return;
    const newValue = scorePayload(input.value);
""")
rep(W, """input.value = originalValue;""", """input.value = originalValue; validateInput(input);""", count=2)
rep(W, """function bulkSaveScores() {
    const invalid = document.querySelectorAll('.score-input.is-invalid').length;
    if (invalid) {
        Swal.fire({ icon:'warning', title:'Invalid Scores', text:`${invalid} score(s) exceed their maximum.` });""",
"""function bulkSaveScores() {
    document.querySelectorAll('.score-input:not(:disabled)').forEach(i => validateInput(i, true));
    const invalid = document.querySelectorAll('.score-input.is-invalid').length;
    if (invalid) {
        Swal.fire({ icon:'warning', title:'Invalid Scores', text:`%s` });""" % INVALID_MSG)
rep(W, """            assessments[inp.dataset.field] = parseFloat(inp.value) || 0;""",
"""            assessments[inp.dataset.field] = scorePayload(inp.value);""")
rep(W, """                    const newScore = bs.assessment_scores?.find(a => a.assessment_id == assessmentId)?.score;""",
"""                    const rec = bs.assessment_scores?.find(a => a.assessment_id == assessmentId);
                    const newScore = rec ? (rec.is_absent ? 'ABS' : rec.score) : undefined;""")
# edit-scores modal
rep(W, """        const score = currentScores[a.id] || 0;
        scoresMap[a.id] = score;
        currentTotal += score;""", """        const score = currentScores[a.id] ?? 0;
        scoresMap[a.id] = score;
        currentTotal += scoreNum(score);""")
rep(W, """                    <small class="text-muted ms-2">(Max: ${a.max_score})</small>
                </div>
                <input type="number"
                       class="form-control score-input-large"
                       data-assessment-id="${a.id}"
                       data-max="${a.max_score}"
                       value="${scoreValue}"
                       min="0" max="${a.max_score}" step="0.1"
""", """                    <small class="text-muted ms-2">(Max: ${a.max_score}, or ABS if absent)</small>
                </div>
                <input type="text"
                       class="form-control score-input-large ${isAbs(scoreValue) ? 'is-absent' : ''}"
                       data-assessment-id="${a.id}"
                       data-max="${a.max_score}"
                       value="${isAbs(scoreValue) ? 'ABS' : scoreValue}"
                       maxlength="6" autocomplete="off"
                       oninput="this.classList.toggle('is-absent', isAbs(this.value))"
""")
rep(W, """        let value = parseFloat(input.value) || 0;
        if (value > maxScore) {
            Swal.fire({ icon: 'error', title: 'Invalid Score', text: `Score cannot exceed ${maxScore}` });""",
"""        if (isAbs(input.value)) { scores[assessmentId] = 'ABS'; return; }
        const raw = String(input.value ?? '').trim();
        let value = parseFloat(raw) || 0;
        if ((raw !== '' && !/^\\d*\\.?\\d+$|^\\d+\\.$/.test(raw)) || value > maxScore || value < 0) {
            Swal.fire({ icon: 'error', title: 'Invalid Score', text: `Score must be between 0 and ${maxScore}, or ABS if the student was absent.` });""")
rep(W, """                    if (input) { input.value = score; input.dataset.original = score; }""",
"""                    if (input) { input.value = score; input.dataset.original = score; validateInput(input); }""")
rep(W, """                currentScores[input.dataset.field] = parseFloat(input.value) || 0;""",
"""                currentScores[input.dataset.field] = isAbs(input.value) ? 'ABS' : scoreNum(input.value);""")
rep(W, """            if (!validateInput(this)) { this.value = this.dataset.original || 0; return; }""",
"""            if (!validateInput(this, true)) { this.value = this.dataset.original || 0; validateInput(this); return; }""")

# Remaining live-total maths in both views: ABS counts as 0
for rel in (V, W):
    resub(rel, r'parseFloat\((inp|i|input|this)\.value\)\s*\|\|\s*0', r'scoreNum(\1.value)', start_marker='const isAbs')
    resub(rel, r"(document\.getElementById\('stVal'\)\.textContent\s*=\s*)val % 1 === 0",
          r"\1isAbs(inp.value) ? 'ABS (absent)' : val % 1 === 0")

# ─────────────────────────────────────────────────────────────────── PDFs
P1 = 'resources/views/subjectscoresheet/scores-pdf.blade.php'
rep(P1, """                    <td>{{ $so ? number_format($so->score, 1) : '0.0' }}</td>""",
"""                    @if($so && $so->isAbsent())
                        <td style="color:#b91c1c;font-weight:700;">ABS</td>
                    @else
                        <td>{{ $so ? number_format($so->score, 1) : '0.0' }}</td>
                    @endif""")
ABS_NOTE = """@if(collect($broadsheets)->contains(fn($b) => collect($b->assessmentScores ?? [])->contains(fn($s) => $s->isAbsent())))
    <div style="font-size:8.5px;color:#555;margin-top:3px;"><strong style="color:#b91c1c;">ABS</strong> = student was absent (counted as 0 in the total).</div>
@endif
"""
rep(P1, """</table>

{{-- ── SIGNATURES""", "</table>\n" + ABS_NOTE + """
{{-- ── SIGNATURES""")

P2 = 'resources/views/admin/score-entry/scores-pdf.blade.php'
rep(P2, """                        <td class="score-col">{{ number_format($scoreValue, 1) }}</td>""",
"""                        @if($score && $score->isAbsent())
                            <td class="score-col" style="color:#b91c1c;font-weight:700;">ABS</td>
                        @else
                            <td class="score-col">{{ number_format($scoreValue, 1) }}</td>
                        @endif""")
rep(P2, """    </table>

    <div class="footer">""", "    </table>\n" + ABS_NOTE + """
    <div class="footer">""")

P3 = 'resources/views/admin/score-entry/scoresheet-pdf.blade.php'
rep(P3, """                    $sv = $so ? number_format($so->score, 1) : '0.0';
                @endphp
                <td>{{ $sv }}</td>""", """                    $sv = $so ? ($so->isAbsent() ? 'ABS' : number_format($so->score, 1)) : '0.0';
                @endphp
                <td @if($sv === 'ABS') style="color:#b91c1c;font-weight:700;" @endif>{{ $sv }}</td>""")
rep(P3, """    </table>

    {{-- ══ SIGNATURE BLOCK""", "    </table>\n" + ABS_NOTE + """
    {{-- ══ SIGNATURE BLOCK""")

# Report cards (class results PDF + printed assessment report)
for rel in ('resources/views/studentreports/class_results_pdf.blade.php',
            'resources/views/student/assessments/print-pdf.blade.php'):
    rep(rel, """                                                $assessmentScore = $found ? $found->score : 0;
                                            }""", """                                                $assessmentScore = $found ? $found->score : 0;
                                            }
                                            $isAbsentScore = isset($found) && $found && method_exists($found, 'isAbsent') && $found->isAbsent();""")
    rep(rel, """                                            {{ $assessmentScore !== null && $assessmentScore !== '' ? number_format($assessmentScore, 1) : '-' }}""",
"""                                            {{ $isAbsentScore ? 'ABS' : ($assessmentScore !== null && $assessmentScore !== '' ? number_format($assessmentScore, 1) : '-') }}""")

# ─────────────────────────────────────────────────────────────── migration
MIG = 'database/migrations/2026_11_04_160000_add_is_absent_to_broadsheet_assessment_scores_table.php'
files[MIG] = """<?php
// Records that a student was ABSENT for an assessment, so scoresheets and
// reports can tell "absent" apart from a genuine score of 0. The score itself
// stays 0 and counts as 0 in every total.

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('broadsheet_assessment_scores', 'is_absent')) {
            Schema::table('broadsheet_assessment_scores', function (Blueprint $table) {
                $table->boolean('is_absent')->default(false)->after('score');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('broadsheet_assessment_scores', 'is_absent')) {
            Schema::table('broadsheet_assessment_scores', function (Blueprint $table) {
                $table->dropColumn('is_absent');
            });
        }
    }
};
"""

# ─────────────────────────────────────────────────── write (with backups)
dry = '--dry' in sys.argv
for rel, content in files.items():
    path = os.path.join(ROOT, rel)
    if dry:
        print('would write', rel); continue
    if os.path.exists(path):
        shutil.copy2(path, path + '.bak_absent')
    open(path, 'w', encoding='utf-8').write(content)
    print('wrote', rel)
