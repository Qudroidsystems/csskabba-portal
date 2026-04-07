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
    protected $headerMapping = [];

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
     * Start from row 1, we'll detect headers dynamically
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

        // Find the header row (contains "Admission No" or similar)
        $headerRowIndex = -1;
        $headerRow = null;

        foreach ($rows as $index => $row) {
            // Convert row to array if it's not already
            $rowArray = is_array($row) ? $row : $row->toArray();
            $rowValues = array_values($rowArray);

            // Look for admission number column header
            foreach ($rowValues as $value) {
                if (is_string($value) && preg_match('/admission|adm\s*no|student\s*id/i', $value)) {
                    $headerRowIndex = $index;
                    $headerRow = $rowValues;
                    break 2;
                }
            }
        }

        if ($headerRowIndex === -1) {
            $this->failures[] = [
                'row' => 0,
                'errors' => ['Could not find header row with admission number column.']
            ];
            return;
        }

        // Map column indexes
        foreach ($headerRow as $idx => $header) {
            if (empty($header)) continue;

            $headerLower = strtolower(trim($header));

            // Map admission number column
            if (preg_match('/admission|adm\s*no|student\s*id/', $headerLower)) {
                $this->headerMapping['admission_no'] = $idx;
            }
            // Map student name column
            elseif (preg_match('/student\s*name|name/', $headerLower)) {
                $this->headerMapping['student_name'] = $idx;
            }
            // Map assessment columns
            elseif (preg_match('/ca\s*1|ca1/i', $headerLower)) {
                $this->headerMapping['ca1'] = $idx;
            }
            elseif (preg_match('/ca\s*2|ca2/i', $headerLower)) {
                $this->headerMapping['ca2'] = $idx;
            }
            elseif (preg_match('/exam|examination/i', $headerLower)) {
                $this->headerMapping['exam'] = $idx;
            }
            // Map total, bf, cum, grade, position, remark
            elseif (preg_match('/total/i', $headerLower)) {
                $this->headerMapping['total'] = $idx;
            }
            elseif (preg_match('/^bf$|bring\s*forward/i', $headerLower)) {
                $this->headerMapping['bf'] = $idx;
            }
            elseif (preg_match('/^cum$|cumulative/i', $headerLower)) {
                $this->headerMapping['cum'] = $idx;
            }
            elseif (preg_match('/grade/i', $headerLower)) {
                $this->headerMapping['grade'] = $idx;
            }
            elseif (preg_match('/position|pos/i', $headerLower)) {
                $this->headerMapping['position'] = $idx;
            }
            elseif (preg_match('/remark/i', $headerLower)) {
                $this->headerMapping['remark'] = $idx;
            }
        }

        // Check if we found required columns
        if (!isset($this->headerMapping['admission_no'])) {
            $this->failures[] = [
                'row' => $headerRowIndex + 1,
                'errors' => ['Header row must contain an admission number column.']
            ];
            return;
        }

        Log::info('Header mapping found: ' . json_encode($this->headerMapping));

        // Start processing from the row after header
        $dataStartRow = $headerRowIndex + 1;
        $this->startRow = $dataStartRow + 1; // +1 because startRow is 1-indexed

        DB::beginTransaction();

        try {
            $dataRows = $rows->slice($dataStartRow);
            $processedCount = 0;

            foreach ($dataRows as $index => $row) {
                $rowArray = is_array($row) ? $row : $row->toArray();
                $rowValues = array_values($rowArray);

                // Skip empty rows
                $isEmpty = true;
                foreach ($rowValues as $value) {
                    if (!empty($value) && $value !== null) {
                        $isEmpty = false;
                        break;
                    }
                }

                if ($isEmpty) {
                    continue;
                }

                try {
                    $this->processRow($rowValues, $headerRowIndex + $index + 2);
                    $processedCount++;
                } catch (\Exception $e) {
                    $this->failures[] = [
                        'row' => $headerRowIndex + $index + 2,
                        'errors' => [$e->getMessage()]
                    ];
                    Log::error("Import row error: " . $e->getMessage(), ['row' => $headerRowIndex + $index + 2]);
                }
            }

            DB::commit();

            Log::info("Import completed. Processed: {$processedCount} rows, Success: {$this->successCount}, Failures: " . count($this->failures));

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
    protected function processRow($rowValues, $rowNumber)
    {
        // Get admission number
        $admissionNo = null;
        if (isset($this->headerMapping['admission_no']) && isset($rowValues[$this->headerMapping['admission_no']])) {
            $admissionNo = trim($rowValues[$this->headerMapping['admission_no']]);
        }

        if (!$admissionNo) {
            throw new \Exception("Admission number is required.");
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

        // Process assessment scores
        $totalScore = 0;

        // Get scores from columns
        $ca1Score = isset($this->headerMapping['ca1']) && isset($rowValues[$this->headerMapping['ca1']])
            ? floatval($rowValues[$this->headerMapping['ca1']]) : 0;
        $ca2Score = isset($this->headerMapping['ca2']) && isset($rowValues[$this->headerMapping['ca2']])
            ? floatval($rowValues[$this->headerMapping['ca2']]) : 0;
        $examScore = isset($this->headerMapping['exam']) && isset($rowValues[$this->headerMapping['exam']])
            ? floatval($rowValues[$this->headerMapping['exam']]) : 0;

        // Validate scores against max scores
        foreach ($this->assessments as $assessment) {
            $score = 0;
            if (strcasecmp($assessment->name, 'CA 1') === 0) {
                $score = $ca1Score;
                if ($score > $assessment->max_score) {
                    throw new \Exception("CA 1 score ({$score}) exceeds maximum ({$assessment->max_score}) for student {$admissionNo}");
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
            elseif (strcasecmp($assessment->name, 'CA 2') === 0) {
                $score = $ca2Score;
                if ($score > $assessment->max_score) {
                    throw new \Exception("CA 2 score ({$score}) exceeds maximum ({$assessment->max_score}) for student {$admissionNo}");
                }
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
            elseif (strcasecmp($assessment->name, 'EXAM') === 0) {
                $score = $examScore;
                if ($score > $assessment->max_score) {
                    throw new \Exception("EXAM score ({$score}) exceeds maximum ({$assessment->max_score}) for student {$admissionNo}");
                }
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
            // Load the file
            $rows = $this->toCollection($file);

            if ($rows->isEmpty()) {
                throw new \Exception('The Excel file is empty.');
            }

            // Try to find header row
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
                throw new \Exception('Could not find header row with admission number column.');
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
