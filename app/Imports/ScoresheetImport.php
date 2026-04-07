<?php

namespace App\Imports;

use App\Models\Broadsheets;
use App\Models\BroadsheetRecord;
use App\Models\BroadsheetAssessmentScore;
use App\Models\StudentRegistration;
use App\Models\Assessment;
use App\Models\Schoolclass;
use App\Models\Subjectclass;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ScoresheetImport implements ToCollection, SkipsOnFailure, WithStartRow
{
    use Importable, SkipsFailures;

    protected $importData;
    protected $successCount = 0;
    protected $failures = [];
    protected $assessments = [];
    protected $schoolclass = null;
    protected $subjectId = null;
    protected $assessmentColumnMap = [];

    public function __construct(array $importData)
    {
        $this->importData = $importData;

        try {
            // Load assessments for this class
            if (!empty($importData['schoolclass_id'])) {
                $this->schoolclass = Schoolclass::with('classcategories')->find($importData['schoolclass_id']);
                if ($this->schoolclass && $this->schoolclass->classcategories->isNotEmpty()) {
                    $categoryIds = $this->schoolclass->classcategories->pluck('id');
                    $this->assessments = Assessment::whereIn('classcategory_id', $categoryIds)
                        ->orderBy('id')
                        ->get();

                    Log::info('Loaded assessments: ' . json_encode($this->assessments->pluck('name')->toArray()));
                }
            }

            // Get subject ID
            if (!empty($importData['subjectclass_id'])) {
                $subjectClass = Subjectclass::find($this->importData['subjectclass_id']);
                $this->subjectId = $subjectClass ? $subjectClass->subjectid : null;
            }
        } catch (\Exception $e) {
            Log::error('ScoresheetImport constructor error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Start from row 7 (data starts after header row at row 6)
     */
    public function startRow(): int
    {
        return 7;
    }

    /**
     * Process the Excel collection
     */
    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            $this->failures[] = [
                'row' => 0,
                'errors' => ['The Excel file has no data rows.']
            ];
            return;
        }

        Log::info('Starting import from row 7, total rows to process: ' . $rows->count());

        // Build assessment column mapping from the first data row's structure
        // We know the structure from your Excel:
        // Index 0: #
        // Index 1: Admission No
        // Index 2: Student Name
        // Index 3: CA 1
        // Index 4: CA 2
        // Index 5: EXAM
        // Index 6: Total
        // Index 7: BF
        // Index 8: Cum
        // Index 9: Grade
        // Index 10: Position
        // Index 11: Remark

        // Build assessment column mapping based on known structure
        $assessmentIndexes = [
            'CA 1' => 3,
            'CA 2' => 4,
            'EXAM' => 5,
        ];

        foreach ($this->assessments as $assessment) {
            if (isset($assessmentIndexes[$assessment->name])) {
                $this->assessmentColumnMap[$assessment->id] = $assessmentIndexes[$assessment->name];
                Log::info("Mapped assessment '{$assessment->name}' (ID: {$assessment->id}) to column index {$assessmentIndexes[$assessment->name]}");
            }
        }

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                $rowArray = is_array($row) ? $row : $row->toArray();
                $rowValues = array_values($rowArray);

                // Skip completely empty rows
                $isEmpty = true;
                for ($i = 0; $i < min(3, count($rowValues)); $i++) {
                    if (!empty($rowValues[$i]) && $rowValues[$i] !== null && $rowValues[$i] !== '') {
                        $isEmpty = false;
                        break;
                    }
                }

                if ($isEmpty) {
                    Log::info("Skipping empty row at index: " . ($index + 7));
                    continue;
                }

                // Check if admission number is empty
                if (!isset($rowValues[1]) || empty($rowValues[1])) {
                    Log::warning("Skipping row with no admission number at row " . ($index + 7));
                    continue;
                }

                try {
                    $this->processRow($rowValues, $index + 7);
                } catch (\Exception $e) {
                    $this->failures[] = [
                        'row' => $index + 7,
                        'errors' => [$e->getMessage()]
                    ];
                    Log::error("Import row error at row " . ($index + 7) . ": " . $e->getMessage());
                }
            }

            DB::commit();

            Log::info("Import completed. Success: {$this->successCount}, Failures: " . count($this->failures));

            // Update progress
            session(['import_progress' => 100, 'import_status' => 'completed', 'import_message' => 'Import completed!']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import transaction failed: ' . $e->getMessage());
            $this->failures[] = [
                'row' => 0,
                'errors' => ['Transaction failed: ' . $e->getMessage()]
            ];
            session(['import_progress' => 0, 'import_status' => 'error', 'import_message' => $e->getMessage()]);
        }
    }

    /**
     * Process a single row
     * Based on the Excel structure:
     * Index 0: S/N
     * Index 1: Admission No
     * Index 2: Student Name
     * Index 3: CA 1
     * Index 4: CA 2
     * Index 5: EXAM
     * Index 6: Total
     * Index 7: BF
     * Index 8: Cum
     * Index 9: Grade
     * Index 10: Position
     * Index 11: Remark
     */
    protected function processRow($rowValues, $rowNumber)
    {
        // Get admission number from column index 1
        $admissionNo = trim($rowValues[1]);

        if (!$admissionNo) {
            throw new \Exception("Admission number is required.");
        }

        Log::info("Processing admission number: {$admissionNo} at row {$rowNumber}");

        // Find student by admission number
        $student = StudentRegistration::where('admissionNO', $admissionNo)
            ->orWhere('admissionno', $admissionNo)
            ->first();

        if (!$student) {
            throw new \Exception("Student with admission number '{$admissionNo}' not found in the system.");
        }

        // Find or create broadsheet record
        $broadsheetRecord = BroadsheetRecord::firstOrCreate(
            [
                'student_id' => $student->id,
                'subject_id' => $this->subjectId,
                'schoolclass_id' => $this->importData['schoolclass_id'],
                'session_id' => $this->importData['session_id'],
            ],
            [
                'term_id' => $this->importData['term_id'],
                'teacher_id' => $this->importData['staff_id'],
            ]
        );

        // Find or create broadsheet
        $broadsheet = Broadsheets::firstOrNew([
            'broadSheet_record_id' => $broadsheetRecord->id,
            'subjectclass_id' => $this->importData['subjectclass_id'],
            'staff_id' => $this->importData['staff_id'],
            'term_id' => $this->importData['term_id'],
        ]);

        // Get scores from columns
        $ca1Score = isset($rowValues[3]) && $rowValues[3] !== '' ? floatval($rowValues[3]) : 0;
        $ca2Score = isset($rowValues[4]) && $rowValues[4] !== '' ? floatval($rowValues[4]) : 0;
        $examScore = isset($rowValues[5]) && $rowValues[5] !== '' ? floatval($rowValues[5]) : 0;

        Log::info("Scores for {$admissionNo}: CA1={$ca1Score}, CA2={$ca2Score}, EXAM={$examScore}");

        // Process assessment scores
        $totalScore = 0;

        foreach ($this->assessments as $assessment) {
            $score = 0;

            if (strcasecmp($assessment->name, 'CA 1') === 0) {
                $score = $ca1Score;
            } elseif (strcasecmp($assessment->name, 'CA 2') === 0) {
                $score = $ca2Score;
            } elseif (strcasecmp($assessment->name, 'EXAM') === 0) {
                $score = $examScore;
            }

            $maxScore = floatval($assessment->max_score);

            if ($score > $maxScore) {
                throw new \Exception("{$assessment->name} score ({$score}) exceeds maximum ({$maxScore}) for student {$admissionNo}");
            }

            if ($score < 0) {
                throw new \Exception("{$assessment->name} score ({$score}) cannot be negative for student {$admissionNo}");
            }

            // Save assessment score
            BroadsheetAssessmentScore::updateOrCreate(
                [
                    'broadsheet_id' => $broadsheet->id ?? 0,
                    'assessment_id' => $assessment->id,
                ],
                ['score' => $score]
            );

            $totalScore += $score;
        }

        // Calculate totals
        $bf = $this->getPreviousTermCum($student->id, $this->subjectId, $this->importData['term_id'], $this->importData['session_id']);
        $cum = $this->importData['term_id'] == 1 ? round($totalScore, 2) : round(($totalScore + $bf) / 2, 2);
        $grade = $this->calculateGrade($cum);
        $remark = $this->getRemark($grade);

        Log::info("Calculated for {$admissionNo}: Total={$totalScore}, BF={$bf}, Cum={$cum}, Grade={$grade}");

        // Save broadsheet
        $broadsheet->fill([
            'total' => round($totalScore, 2),
            'bf' => round($bf, 2),
            'cum' => $cum,
            'grade' => $grade,
            'remark' => $remark,
            'vettedstatus' => 0,
        ]);

        $broadsheet->save();

        $this->successCount++;

        // Update progress periodically
        if ($this->successCount % 10 == 0) {
            session(['import_progress' => min(60 + ($this->successCount / 100) * 30, 95)]);
        }
    }

    /**
     * Get previous term cumulative score
     */
    protected function getPreviousTermCum($studentId, $subjectId, $termId, $sessionId)
    {
        if ($termId == 1) return 0;

        try {
            $prev = Broadsheets::where('broadsheet_records.student_id', $studentId)
                ->where('broadsheet_records.subject_id', $subjectId)
                ->where('broadsheets.term_id', $termId - 1)
                ->where('broadsheet_records.session_id', $sessionId)
                ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
                ->value('broadsheets.cum');

            return $prev ? round(floatval($prev), 2) : 0;
        } catch (\Exception $e) {
            Log::warning('Error getting previous term cum: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calculate grade based on score
     */
    protected function calculateGrade($score)
    {
        try {
            if ($this->schoolclass && $this->schoolclass->classcategories->isNotEmpty()) {
                $isSenior = $this->schoolclass->classcategories->first()->is_senior ?? false;

                if ($isSenior) {
                    if ($score >= 75) return 'A1';
                    if ($score >= 70) return 'B2';
                    if ($score >= 65) return 'B3';
                    if ($score >= 60) return 'C4';
                    if ($score >= 55) return 'C5';
                    if ($score >= 50) return 'C6';
                    if ($score >= 45) return 'D7';
                    if ($score >= 40) return 'E8';
                    return 'F9';
                } else {
                    if ($score >= 70) return 'A';
                    if ($score >= 60) return 'B';
                    if ($score >= 50) return 'C';
                    if ($score >= 40) return 'D';
                    return 'F';
                }
            }
        } catch (\Exception $e) {
            Log::warning('Error calculating grade: ' . $e->getMessage());
        }

        // Default grading
        if ($score >= 70) return 'A';
        if ($score >= 60) return 'B';
        if ($score >= 50) return 'C';
        if ($score >= 40) return 'D';
        return 'F';
    }

    /**
     * Get remark based on grade
     */
    protected function getRemark($grade)
    {
        $remarks = [
            'A' => 'Excellent',
            'A1' => 'Excellent',
            'B' => 'Very Good',
            'B2' => 'Very Good',
            'B3' => 'Very Good',
            'C' => 'Good',
            'C4' => 'Good',
            'C5' => 'Good',
            'C6' => 'Good',
            'D' => 'Pass',
            'D7' => 'Pass',
            'E8' => 'Pass',
            'F' => 'Fail',
            'F9' => 'Fail',
        ];

        return $remarks[$grade] ?? 'Pass';
    }

    /**
     * Get number of successfully imported records
     */
    public function getSuccessCount()
    {
        return $this->successCount;
    }

    /**
     * Get all failures
     */
    public function getFailures()
    {
        return $this->failures;
    }

    /**
     * Validate Excel file structure
     */
    public function validateExcelFile($file)
    {
        try {
            // Load the file
            $rows = $this->toCollection($file);

            if ($rows->isEmpty()) {
                throw new \Exception('The Excel file is empty.');
            }

            // Check if we have at least 7 rows
            if ($rows->count() < 7) {
                throw new \Exception('The Excel file does not have enough rows. Expected at least 7 rows.');
            }

            // Get row 6 (index 5) which should be the header row
            $headerRow = $rows[5] ?? null;
            if (!$headerRow) {
                throw new \Exception('Could not find header row at row 6.');
            }

            $headerValues = array_values(is_array($headerRow) ? $headerRow : $headerRow->toArray());

            // Check if column 2 (index 1) contains "Admission No"
            if (!isset($headerValues[1]) || stripos($headerValues[1], 'Admission') === false) {
                throw new \Exception('Header row at row 6 must have "Admission No" in column B.');
            }

            Log::info('Excel validation passed. Found header row at row 6 with Admission No column.');

            return true;

        } catch (\Exception $e) {
            Log::error('Excel validation failed: ' . $e->getMessage());
            throw new \Exception('Excel validation failed: ' . $e->getMessage());
        }
    }

    /**
     * Validate Excel file metadata/structure (alias for backward compatibility)
     */
    public function validateExcelMetadata($filePath)
    {
        return $this->validateExcelFile($filePath);
    }
}
