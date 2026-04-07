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
    protected $headerRow = 6; // Headers start at row 6

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
     * Start reading from row 7 (after headers)
     */
    public function startRow(): int
    {
        return 7; // Data starts from row 7
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

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                try {
                    $this->processRow($row, $index + 7); // Row number in original file
                } catch (\Exception $e) {
                    $this->failures[] = [
                        'row' => $index + 7,
                        'errors' => [$e->getMessage()]
                    ];
                    Log::error("Import row error: " . $e->getMessage(), ['row' => $index + 7]);
                }
            }

            DB::commit();

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
     */
    protected function processRow($row, $rowNumber)
    {
        // The row is indexed from 0, map to the correct columns based on your Excel structure
        // Based on your log, the structure is:
        // Column 0: S/N
        // Column 1: Admission No
        // Column 2: Student Name
        // Column 3: CA 1
        // Column 4: CA 2
        // Column 5: EXAM
        // Column 6: Total
        // Column 7: BF
        // Column 8: Cum
        // Column 9: Grade
        // Column 10: Position
        // Column 11: Remark

        // Get admission number from column index 1
        $admissionNo = null;
        if (isset($row[1]) && !empty($row[1])) {
            $admissionNo = trim($row[1]);
        }

        if (!$admissionNo) {
            throw new \Exception("Admission number is required. Column 2 (Admission No) is empty.");
        }

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

        // Process assessment scores from column indexes
        $totalScore = 0;

        // Map assessments to column indexes
        $assessmentColumns = [
            'CA 1' => 3,
            'CA 2' => 4,
            'EXAM' => 5,
        ];

        foreach ($this->assessments as $assessment) {
            $columnIndex = $assessmentColumns[$assessment->name] ?? null;

            if ($columnIndex !== null && isset($row[$columnIndex])) {
                $scoreValue = $row[$columnIndex];

                if ($scoreValue !== null && $scoreValue !== '') {
                    $score = floatval($scoreValue);
                    $maxScore = floatval($assessment->max_score);

                    if ($score > $maxScore) {
                        throw new \Exception("Score for {$assessment->name} ({$score}) exceeds maximum ({$maxScore}) for student {$admissionNo}");
                    }

                    if ($score < 0) {
                        throw new \Exception("Score for {$assessment->name} ({$score}) cannot be negative for student {$admissionNo}");
                    }

                    // Save assessment score
                    if ($broadsheet->id) {
                        BroadsheetAssessmentScore::updateOrCreate(
                            [
                                'broadsheet_id' => $broadsheet->id,
                                'assessment_id' => $assessment->id,
                            ],
                            ['score' => $score]
                        );
                    }

                    $totalScore += $score;
                }
            }
        }

        // Calculate totals
        $bf = $this->getPreviousTermCum($student->id, $this->subjectId, $this->importData['term_id'], $this->importData['session_id']);
        $cum = $this->importData['term_id'] == 1 ? round($totalScore, 2) : round(($totalScore + $bf) / 2, 2);
        $grade = $this->calculateGrade($cum);
        $remark = $this->getRemark($grade);

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
            // Load the entire file to check structure
            $rows = $this->toCollection($file);

            if ($rows->isEmpty()) {
                throw new \Exception('The Excel file is empty.');
            }

            // Check if we have at least 7 rows (header rows + data)
            if ($rows->count() < 7) {
                throw new \Exception('The Excel file does not have enough rows. Expected at least 7 rows (header rows + data).');
            }

            // Get the header row (row 6 in original, index 5 in collection)
            $headerRow = $rows[5] ?? null;
            if (!$headerRow) {
                throw new \Exception('Could not find header row at row 6.');
            }

            // Check required columns
            $requiredColumns = [
                1 => 'Admission No',  // Column index 1
                3 => 'CA 1',          // Column index 3
                4 => 'CA 2',          // Column index 4
                5 => 'EXAM',          // Column index 5
            ];

            $missingColumns = [];
            foreach ($requiredColumns as $index => $columnName) {
                if (!isset($headerRow[$index]) || empty($headerRow[$index])) {
                    $missingColumns[] = $columnName . " (column " . ($index + 1) . ")";
                }
            }

            if (!empty($missingColumns)) {
                throw new \Exception('Missing required columns: ' . implode(', ', $missingColumns));
            }

            Log::info('Excel validation passed. Found all required columns.');

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
