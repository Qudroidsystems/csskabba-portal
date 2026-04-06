<?php

namespace App\Imports;

use App\Models\Assessment;
use App\Models\Broadsheets;
use App\Models\Schoolclass;
use App\Models\Subjectclass;
use App\Models\BroadsheetRecord;
use App\Models\BroadsheetAssessmentScore;
use App\Http\Controllers\MyScoreSheetController;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Dynamic ScoresheetImport
 *
 * The Excel file exported by RecordsheetExport has this structure:
 *  Row 1-3 : School info
 *  Row 4   : Subject | Class | Term | Session | Teacher
 *  Row 5   : Empty spacer
 *  Row 6   : Headers:  # | Admission No | Student Name | [Assessment 1 (max)] | ... | Total | BF | Cum | Grade | Position | Remark
 *  Row 7+  : Data
 *
 * We parse row 6 to discover the assessment columns dynamically.
 */
class ScoresheetImport implements ToCollection, WithStartRow
{
    use Importable;

    protected array  $data;
    protected array  $updatedBroadsheets = [];
    protected array  $failures           = [];
    protected array  $assessments        = [];   // Assessment models indexed by name (uppercase, no spaces)
    protected array  $assessmentColMap   = [];   // column index → assessment_id

    public function __construct(array $importData)
    {
        $this->data = $importData;

        if (!in_array((int)$this->data['term_id'], [1, 2, 3])) {
            throw new \Exception('Invalid term ID provided. Must be 1, 2, or 3.');
        }

        Session::put('subjectclass_id', $this->data['subjectclass_id']);
        Session::put('staff_id',        $this->data['staff_id']);
        Session::put('term_id',         $this->data['term_id']);
        Session::put('session_id',      $this->data['session_id']);
        Session::put('schoolclass_id',  $this->data['schoolclass_id']);

        // Pre-load assessments for this class
        $schoolclass = Schoolclass::with('classcategories')->find($this->data['schoolclass_id']);
        if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            $categoryIds = $schoolclass->classcategories->pluck('id');
            foreach (Assessment::whereIn('classcategory_id', $categoryIds)->orderBy('id')->get() as $a) {
                // Key: normalised name for header matching
                $this->assessments[$this->normalise($a->name)] = $a;
            }
        }
    }

    // ── Row 6 is the heading row; data starts from row 7 ──────────────────────
    public function startRow(): int { return 6; }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) return;

        // ── Row 0 (Excel row 6) is the header ─────────────────────────────────
        $headerRow = $rows->first()->toArray();
        $this->buildAssessmentColumnMap($headerRow);

        // ── Rows 1+ are data ───────────────────────────────────────────────────
        foreach ($rows->slice(1) as $rowIndex => $row) {
            $this->processRow($row->toArray(), $rowIndex + 7); // +7 = actual Excel row number
        }

        // ── Post-processing: positions + metrics ──────────────────────────────
        if (!empty($this->updatedBroadsheets)) {
            $this->runAfterImport();
        }
    }

    /**
     * Build a map of column_index → Assessment model by matching header strings.
     * Expected header cell format: "Assessment Name (max_score)" e.g. "CA1 (30)"
     */
    private function buildAssessmentColumnMap(array $header): void
    {
        foreach ($header as $colIndex => $cell) {
            $normalised = $this->normalise((string)($cell ?? ''));
            foreach ($this->assessments as $key => $assessment) {
                // Match if header starts with the assessment name (ignores the "(max)" part)
                if (str_starts_with($normalised, $key)) {
                    $this->assessmentColMap[$colIndex] = $assessment;
                    break;
                }
            }
        }
        Log::info('ScoresheetImport: Column map built', ['map' => array_map(fn($a) => $a->name . '(id=' . $a->id . ')', $this->assessmentColMap)]);
    }

    private function processRow(array $row, int $rowNumber): void
    {
        try {
            // Column layout (0-indexed): 0=#, 1=AdmNo, 2=Name, [3..n]=assessments, n+1=Total, n+2=BF ...
            $admissionNo = strtoupper(trim($row[1] ?? ''));
            if (empty($admissionNo)) return; // skip empty rows

            $subjectclassId = $this->data['subjectclass_id'];
            $staffId        = $this->data['staff_id'];
            $termId         = (int)$this->data['term_id'];
            $sessionId      = $this->data['session_id'];

            // Find the broadsheet for this student + subject
            $broadsheetData = DB::table('broadsheets')
                ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
                ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
                ->where('studentRegistration.admissionNO', $admissionNo)
                ->where('broadsheets.subjectclass_id', $subjectclassId)
                ->where('broadsheets.staff_id', $staffId)
                ->where('broadsheets.term_id', $termId)
                ->where('broadsheet_records.session_id', $sessionId)
                ->select('broadsheets.id as broadsheet_id', 'broadsheet_records.student_id', 'broadsheet_records.subject_id')
                ->first();

            if (!$broadsheetData) {
                $this->failures[] = ['row' => $rowNumber, 'error' => "No broadsheet found for admission number: {$admissionNo}"];
                Log::warning('ScoresheetImport: No broadsheet found', ['admissionno' => $admissionNo, 'row' => $rowNumber]);
                return;
            }

            // Update assessment scores from their mapped columns
            DB::transaction(function () use ($row, $broadsheetData, $termId, $sessionId) {
                foreach ($this->assessmentColMap as $colIndex => $assessment) {
                    $rawScore = $row[$colIndex] ?? 0;
                    $score    = is_numeric($rawScore) ? max(0, min((float)$rawScore, $assessment->max_score)) : 0;

                    BroadsheetAssessmentScore::updateOrCreate(
                        ['broadsheet_id' => $broadsheetData->broadsheet_id, 'assessment_id' => $assessment->id],
                        ['score' => $score]
                    );
                }

                // Recompute total, cum, grade, remark
                $broadsheet = Broadsheets::with(['assessmentScores'])->find($broadsheetData->broadsheet_id);
                if ($broadsheet) {
                    $schoolclass = Schoolclass::with('classcategories')->find(
                        BroadsheetRecord::find($broadsheet->broadSheet_record_id)?->schoolclass_id ?? 0
                    );

                    $allAssessments = collect($this->assessments); // values = Assessment models
                    $totalRaw       = 0;
                    foreach ($allAssessments as $a) {
                        $scoreObj  = $broadsheet->assessmentScores->where('assessment_id', $a->id)->first();
                        $totalRaw += $scoreObj ? $scoreObj->score : 0;
                    }

                    $bf     = $this->getPreviousTermCum($broadsheetData->student_id, $broadsheetData->subject_id, $termId, $sessionId);
                    $cum    = $termId == 1 ? round($totalRaw, 2) : round(($totalRaw + $bf) / 2, 2);
                    $grade  = $schoolclass && $schoolclass->classcategories->isNotEmpty()
                        ? $schoolclass->classcategories->first()->calculateGrade($cum)
                        : $this->defaultGrade($cum);
                    $remark = $this->getRemark($grade);

                    DB::table('broadsheets')->where('id', $broadsheet->id)->update([
                        'total' => $totalRaw, 'bf' => $bf, 'cum' => $cum,
                        'grade' => $grade, 'remark' => $remark, 'updated_at' => now(),
                    ]);

                    $this->updatedBroadsheets[] = $broadsheet->id;
                }
            });

        } catch (\Exception $e) {
            $this->failures[] = ['row' => $rowNumber, 'error' => $e->getMessage()];
            Log::error('ScoresheetImport: Row error', ['row' => $rowNumber, 'error' => $e->getMessage()]);
        }
    }

    private function runAfterImport(): void
    {
        try {
            $controller = new MyScoreSheetController();
            DB::transaction(function () use ($controller) {
                $subjectclassId = $this->data['subjectclass_id'];
                $staffId        = $this->data['staff_id'];
                $termId         = $this->data['term_id'];
                $sessionId      = $this->data['session_id'];
                $schoolclassId  = $this->data['schoolclass_id'];

                $controller->updateClassMetrics($subjectclassId, $staffId, $termId, $sessionId);
                $controller->updateSubjectPositions($subjectclassId, $staffId, $termId, $sessionId);
                $controller->updateClassPositions($schoolclassId, $termId, $sessionId);
            });
        } catch (\Exception $e) {
            Log::error('ScoresheetImport afterImport error', ['error' => $e->getMessage()]);
        }
    }

    // ── Metadata validation ────────────────────────────────────────────────────

    public function validateExcelMetadata(string $filePath): void
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \Exception('Excel file is missing or unreadable.');
        }

        $spreadsheet = IOFactory::load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();
        $metaCell    = trim((string)($sheet->getCell('A4')->getValue() ?? ''));

        if (empty($metaCell)) {
            Log::info('ScoresheetImport: No metadata in row 4, skipping validation.');
            return;
        }

        // Parse "Subject: X | Class: Y | Term: Z | Session: W"
        $metadata = [];
        foreach (explode('|', $metaCell) as $item) {
            $parts = array_map('trim', explode(':', $item, 2));
            if (count($parts) === 2) $metadata[strtolower($parts[0])] = $parts[1];
        }

        // Lenient: just log mismatches, don't throw
        $subjectclass = Subjectclass::with(['subjectTeacher.subject'])->find($this->data['subjectclass_id']);
        $term         = DB::table('schoolterm')->where('id', $this->data['term_id'])->value('term');
        $session      = DB::table('schoolsession')->where('id', $this->data['session_id'])->value('session');

        Log::info('ScoresheetImport: Metadata check', compact('metadata', 'term', 'session'));
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function normalise(string $str): string
    {
        return strtolower(preg_replace('/[\s\(\)\.]+/', '', $str));
    }

    private function getPreviousTermCum($studentId, $subjectId, $termId, $sessionId): float
    {
        if ($termId == 1) return 0.0;
        $prev = Broadsheets::where('broadsheet_records.student_id', $studentId)
            ->where('broadsheet_records.subject_id', $subjectId)
            ->where('broadsheets.term_id', $termId - 1)
            ->where('broadsheet_records.session_id', $sessionId)
            ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->value('broadsheets.cum');
        return $prev ? round((float)$prev, 2) : 0.0;
    }

    private function defaultGrade($score): string
    {
        if ($score >= 70) return 'A';
        if ($score >= 60) return 'B';
        if ($score >= 50) return 'C';
        if ($score >= 40) return 'D';
        return 'F';
    }

    private function getRemark($grade): string
    {
        return match($grade) {
            'A', 'A1'              => 'Excellent',
            'B', 'B2', 'B3'       => 'Very Good',
            'C', 'C4', 'C5', 'C6' => 'Good',
            'D', 'D7', 'E8'       => 'Pass',
            default                => 'Fail',
        };
    }

    public function getUpdatedBroadsheets(): array { return $this->updatedBroadsheets; }
    public function getFailures(): array           { return $this->failures; }
}
