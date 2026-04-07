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
    protected $startRow = 1;
    protected $headerRowFound = false;
    protected $admissionColumnIndex = -1;
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
     * Start from row 1, we'll dynamically find where data starts
     */
    public function startRow(): int
    {
        return $this->startRow;
    }

    /**
     * Process the Excel collection
     */
    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            $this->failures[] = [
                'row' => 0,
                'errors' => ['The Excel file is empty.']
            ];
            return;
        }

        Log::info('Total rows in file: ' . $rows->count());

        // Find the header row (contains "Admission No" in any column)
        $headerRowIndex = -1;
        $headerRowValues = null;

        foreach ($rows as $index => $row) {
            $rowArray = is_array($row) ? $row : $row->toArray();
            $rowValues = array_values($rowArray);

            // Look for "Admission No" in any column of this row
            foreach ($rowValues as $colIndex => $value) {
                if (is_string($value) && preg_match('/admission|adm\s*no|student\s*id/i', $value)) {
                    $headerRowIndex = $index;
                    $headerRowValues = $rowValues;
                    $this->admissionColumnIndex = $colIndex;
                    $this->headerRowFound = true;
                    Log::info("Found header row at index: {$index} (Row " . ($index + 1) . ")");
                    Log::info("Admission column found at index: {$colIndex}");
                    Log::info("Header row values: " . json_encode($rowValues));
                    break 2;
                }
            }
        }

        if (!$this->headerRowFound) {
            $this->failures[] = [
                'row' => 0,
                'errors' => ['Could not find header row with "Admission No" in any column. Please ensure your file has a column named "Admission No".']
            ];
            return;
        }

        // Build assessment column mapping from header row
        $this->buildAssessmentColumnMap($headerRowValues);

        // Set start row to the row after header
        $this->startRow = $headerRowIndex + 2;

        // Get only data rows (starting from after header)
        $dataRows = $rows->slice($headerRowIndex + 1);

        Log::info('Data rows to process: ' . $dataRows->count());
        Log::info('Start row set to: ' . $this->startRow);

        DB::beginTransaction();

        try {
            foreach ($dataRows as $index => $row) {
                $rowArray = is_array($row) ? $row : $row->toArray();
                $rowValues = array_values($rowArray);

                // Skip completely empty rows
                $isEmpty = true;
                foreach ($rowValues as $value) {
                    if (!empty($value) && $value !== null && $value !== '') {
                        $isEmpty = false;
                        break;
                    }
                }

                if ($isEmpty) {
                    continue;
                }

                // Check if admission number is empty
                if ($this->admissionColumnIndex === -1 ||
                    !isset($rowValues[$this->admissionColumnIndex]) ||
                    empty($rowValues[$this->admissionColumnIndex])) {
                    Log::warning("Skipping row with no admission number at row " . ($this->startRow + $index));
                    continue;
                }

                try {
                    $this->processRow($rowValues, $this->startRow + $index);
                } catch (\Exception $e) {
                    $this->failures[] = [
                        'row' => $this->startRow + $index,
                        'errors' => [$e->getMessage()]
                    ];
                    Log::error("Import row error at row " . ($this->startRow + $index) . ": " . $e->getMessage());
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
     * Build the assessment column mapping from the header row
     */
    protected function buildAssessmentColumnMap($headerValues)
    {
        foreach ($headerValues as $colIndex => $headerText) {
            // Skip the admission column
            if ($colIndex == $this->admissionColumnIndex) {
                continue;
            }

            if (empty($headerText) || !is_string($headerText)) {
                continue;
            }

            // Extract assessment name (remove max score in parentheses if present)
            // Example: "CA 1 (20.00)" -> "CA 1"
            $assessmentName = trim($headerText);
            $assessmentName = preg_replace('/\s*\([^)]*\)/', '', $assessmentName);
            $assessmentName = trim($assessmentName);

            // Find matching assessment in the system
            foreach ($this->assessments as $assessment) {
                if (strcasecmp($assessment->name, $assessmentName) === 0) {
                    $this->assessmentColumnMap[$assessment->id] = $colIndex;
                    Log::info("Mapped assessment '{$assessment->name}' (ID: {$assessment->id}) to column index {$colIndex} (Header: '{$headerText}')");
                    break;
                }
            }
        }

        Log::info('Final assessment column mapping: ' . json_encode($this->assessmentColumnMap));
    }

    /**
     * Process a single row
     */
    protected function processRow($rowValues, $rowNumber)
    {
        // Get admission number
        $admissionNo = isset($rowValues[$this->admissionColumnIndex])
            ? trim($rowValues[$this->admissionColumnIndex])
            : null;

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

        // Process assessment scores using the dynamic column mapping
        $totalScore = 0;

        foreach ($this->assessments as $assessment) {
            $score = 0;
            $columnIndex = $this->assessmentColumnMap[$assessment->id] ?? null;

            if ($columnIndex !== null && isset($rowValues[$columnIndex])) {
                $scoreValue = $rowValues[$columnIndex];
                if ($scoreValue !== null && $scoreValue !== '') {
                    $score = floatval($scoreValue);
                }
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

            // Look for header row with "Admission No" in any column
            $foundHeader = false;
            foreach ($rows as $index => $row) {
                $rowArray = is_array($row) ? $row : $row->toArray();
                $rowValues = array_values($rowArray);

                foreach ($rowValues as $value) {
                    if (is_string($value) && preg_match('/admission|adm\s*no|student\s*id/i', $value)) {
                        $foundHeader = true;
                        Log::info("Found header row at line: " . ($index + 1));
                        break 2;
                    }
                }
            }

            if (!$foundHeader) {
                throw new \Exception('Could not find header row with "Admission No" in any column. Please ensure your file has a column named "Admission No".');
            }

            Log::info('Excel validation passed.');
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
