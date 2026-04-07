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
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ScoresheetImport implements ToCollection, WithHeadingRow, SkipsOnFailure, WithStartRow
{
    use Importable, SkipsFailures;

    protected $importData;
    protected $successCount = 0;
    protected $failures = [];
    protected $assessments = [];
    protected $schoolclass = null;
    protected $subjectId = null;
    protected $headerRow = null;

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
                $subjectClass = Subjectclass::find($importData['subjectclass_id']);
                $this->subjectId = $subjectClass ? $subjectClass->subjectid : null;
            }
        } catch (\Exception $e) {
            Log::error('ScoresheetImport constructor error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Start from row 1 (header row)
     */
    public function startRow(): int
    {
        return 1;
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

        // Get the first row as headers
        $firstRow = $rows->first();
        $headers = [];

        // Check if the first row contains actual headers (strings) or numeric indexes
        foreach ($firstRow as $key => $value) {
            // If the key is numeric, we need to use the cell values as headers
            if (is_numeric($key)) {
                $headers[] = trim($value);
            } else {
                $headers[] = $key;
            }
        }

        // If headers are numeric (0,1,2...), use the first row values as headers
        $hasNumericHeaders = true;
        foreach ($headers as $header) {
            if (!is_numeric($header) && $header !== null && $header !== '') {
                $hasNumericHeaders = false;
                break;
            }
        }

        if ($hasNumericHeaders) {
            // The first row contains the actual headers as values
            $actualHeaders = [];
            foreach ($firstRow as $value) {
                $actualHeaders[] = trim($value);
            }

            // Remove the header row from data rows
            $dataRows = $rows->slice(1);

            // Process each data row
            foreach ($dataRows as $index => $row) {
                try {
                    // Convert indexed row to associative array using headers
                    $associativeRow = [];
                    foreach ($actualHeaders as $idx => $header) {
                        if ($header && $header !== '') {
                            $associativeRow[$header] = $row[$idx] ?? null;
                        }
                    }
                    $this->processRow($associativeRow, $index + 3); // +3 because row 1 is headers, row 2 is start of data
                } catch (\Exception $e) {
                    $this->failures[] = [
                        'row' => $index + 3,
                        'errors' => [$e->getMessage()]
                    ];
                    Log::error("Import row error: " . $e->getMessage(), ['row' => $index + 3]);
                }
            }
        } else {
            // Headers are already in the keys, process all rows
            DB::beginTransaction();

            try {
                foreach ($rows as $index => $row) {
                    try {
                        $this->processRow($row, $index + 2);
                    } catch (\Exception $e) {
                        $this->failures[] = [
                            'row' => $index + 2,
                            'errors' => [$e->getMessage()]
                        ];
                        Log::error("Import row error: " . $e->getMessage(), ['row' => $index + 2]);
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
    }

    /**
     * Process a single row
     */
    protected function processRow($row, $rowNumber)
    {
        // Get admission number - try different possible column names
        $admissionNo = null;
        $possibleColumns = ['admission_no', 'admissionno', 'admission', 'adm_no', 'student_id', 'Admission No', 'AdmissionNO', 'ADMISSION NO', 'Adm No', 'Adm. No'];

        foreach ($possibleColumns as $col) {
            if (isset($row[$col]) && !empty($row[$col])) {
                $admissionNo = trim($row[$col]);
                break;
            }
        }

        // Also check case-insensitive
        if (!$admissionNo) {
            foreach ($row as $key => $value) {
                foreach ($possibleColumns as $col) {
                    if (strcasecmp(trim($key), $col) === 0 && !empty($value)) {
                        $admissionNo = trim($value);
                        break 2;
                    }
                }
            }
        }

        if (!$admissionNo) {
            throw new \Exception("Admission number is required. Please ensure your Excel has a column named 'admission_no' or 'admissionno'.");
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

        foreach ($this->assessments as $assessment) {
            // Look for column with assessment name (case insensitive)
            $scoreValue = null;
            foreach ($row as $key => $value) {
                if (strcasecmp(trim($key), $assessment->name) === 0) {
                    $scoreValue = $value;
                    break;
                }
            }

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
            // Read the file directly
            $rows = $this->toCollection($file);

            if ($rows->isEmpty()) {
                throw new \Exception('The Excel file is empty.');
            }

            $firstRow = $rows->first();

            // Get headers from the first row values
            $headers = [];
            foreach ($firstRow as $value) {
                if ($value !== null && trim($value) !== '') {
                    $headers[] = trim($value);
                }
            }

            Log::info('Excel headers found: ' . json_encode($headers));
            Log::info('Expected assessments: ' . json_encode($this->assessments->pluck('name')->toArray()));

            // Check if there's at least one assessment column
            $hasAssessmentColumn = false;
            $foundAssessments = [];

            foreach ($this->assessments as $assessment) {
                foreach ($headers as $header) {
                    if (strcasecmp(trim($header), $assessment->name) === 0) {
                        $hasAssessmentColumn = true;
                        $foundAssessments[] = $assessment->name;
                        break;
                    }
                }
            }

            if (!$hasAssessmentColumn && $this->assessments->isNotEmpty()) {
                $assessmentNames = $this->assessments->pluck('name')->implode(', ');
                throw new \Exception("Excel file must contain at least one assessment column. Expected columns: {$assessmentNames}. Found headers: " . implode(', ', $headers));
            }

            // Check for admission number column
            $hasAdmissionColumn = false;
            $admissionColumns = ['admission_no', 'admissionno', 'admission', 'adm_no', 'student_id', 'Admission No', 'AdmissionNO', 'ADMISSION NO', 'Adm No', 'Adm. No'];
            $foundAdmissionColumn = null;

            foreach ($headers as $header) {
                foreach ($admissionColumns as $admissionCol) {
                    if (strcasecmp(trim($header), $admissionCol) === 0) {
                        $hasAdmissionColumn = true;
                        $foundAdmissionColumn = $header;
                        break 2;
                    }
                }
            }

            if (!$hasAdmissionColumn) {
                throw new \Exception('Excel file must contain an admission number column. Expected column names: ' . implode(', ', $admissionColumns) . '. Found headers: ' . implode(', ', $headers));
            }

            Log::info('Excel validation passed. Found admission column: ' . $foundAdmissionColumn . ', Assessments: ' . implode(', ', $foundAssessments));

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
