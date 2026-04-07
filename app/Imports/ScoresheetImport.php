<?php

namespace App\Imports;

use App\Models\Broadsheets;
use App\Models\BroadsheetRecord;
use App\Models\BroadsheetAssessmentScore;
use App\Models\StudentRegistration;
use App\Models\Assessment;
use App\Models\Schoolclass;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ScoresheetImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;

    protected $importData;
    protected $successCount = 0;
    protected $failures = [];
    protected $assessments = [];
    protected $schoolclass = null;

    public function __construct(array $importData)
    {
        $this->importData = $importData;

        // Load assessments for this class
        if (!empty($importData['schoolclass_id'])) {
            $this->schoolclass = Schoolclass::with('classcategories')->find($importData['schoolclass_id']);
            if ($this->schoolclass && $this->schoolclass->classcategories->isNotEmpty()) {
                $categoryIds = $this->schoolclass->classcategories->pluck('id');
                $this->assessments = Assessment::whereIn('classcategory_id', $categoryIds)
                    ->orderBy('id')
                    ->get()
                    ->keyBy('name'); // Key by assessment name for easy lookup
            }
        }
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

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                try {
                    $this->processRow($row, $index + 2); // +2 because row 1 is header
                } catch (\Exception $e) {
                    $this->failures[] = [
                        'row' => $index + 2,
                        'errors' => [$e->getMessage()]
                    ];
                    Log::error("Import row error: " . $e->getMessage(), ['row' => $index + 2]);
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import transaction failed: ' . $e->getMessage());
            $this->failures[] = [
                'row' => 0,
                'errors' => ['Transaction failed: ' . $e->getMessage()]
            ];
        }
    }

    /**
     * Process a single row
     */
    protected function processRow($row, $rowNumber)
    {
        // Get admission number - try different possible column names
        $admissionNo = $row['admission_no'] ??
                      $row['admissionno'] ??
                      $row['admission'] ??
                      $row['adm_no'] ??
                      $row['student_id'] ??
                      null;

        if (!$admissionNo) {
            throw new \Exception("Admission number is required.");
        }

        // Find student by admission number
        $student = StudentRegistration::where('admissionNO', $admissionNo)
            ->orWhere('admissionno', $admissionNo)
            ->first();

        if (!$student) {
            throw new \Exception("Student with admission number '{$admissionNo}' not found.");
        }

        // Find or create broadsheet record
        $broadsheetRecord = BroadsheetRecord::firstOrCreate(
            [
                'student_id' => $student->id,
                'subject_id' => $this->getSubjectId(),
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
                $maxScore = $assessment->max_score;

                if ($score > $maxScore) {
                    throw new \Exception("Score for {$assessment->name} ({$score}) exceeds maximum ({$maxScore})");
                }

                // Save assessment score
                BroadsheetAssessmentScore::updateOrCreate(
                    [
                        'broadsheet_id' => $broadsheet->id ?? null,
                        'assessment_id' => $assessment->id,
                    ],
                    ['score' => $score]
                );

                $totalScore += $score;
            }
        }

        // Calculate totals
        $bf = $this->getPreviousTermCum($student->id, $this->getSubjectId(), $this->importData['term_id'], $this->importData['session_id']);
        $cum = $this->importData['term_id'] == 1 ? $totalScore : round(($totalScore + $bf) / 2, 2);
        $grade = $this->calculateGrade($cum);
        $remark = $this->getRemark($grade);

        // Save broadsheet
        $broadsheet->fill([
            'total' => $totalScore,
            'bf' => $bf,
            'cum' => $cum,
            'grade' => $grade,
            'remark' => $remark,
            'vettedstatus' => 0,
        ]);

        $broadsheet->save();

        // If this is a new broadsheet, associate it with the record
        if (!$broadsheet->wasRecentlyCreated && $broadsheet->wasChanged()) {
            // Updated existing
        }

        $this->successCount++;
    }

    /**
     * Get subject ID from subjectclass
     */
    protected function getSubjectId()
    {
        static $subjectId = null;

        if ($subjectId === null) {
            $subjectClass = \App\Models\Subjectclass::find($this->importData['subjectclass_id']);
            $subjectId = $subjectClass ? $subjectClass->subjectid : null;
        }

        return $subjectId;
    }

    /**
     * Get previous term cumulative score
     */
    protected function getPreviousTermCum($studentId, $subjectId, $termId, $sessionId)
    {
        if ($termId == 1) return 0;

        $prev = Broadsheets::where('broadsheet_records.student_id', $studentId)
            ->where('broadsheet_records.subject_id', $subjectId)
            ->where('broadsheets.term_id', $termId - 1)
            ->where('broadsheet_records.session_id', $sessionId)
            ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
            ->value('broadsheets.cum');

        return $prev ? round($prev, 2) : 0;
    }

    /**
     * Calculate grade based on score
     */
    protected function calculateGrade($score)
    {
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
        return match($grade) {
            'A', 'A1' => 'Excellent',
            'B', 'B2', 'B3' => 'Very Good',
            'C', 'C4', 'C5', 'C6' => 'Good',
            'D', 'D7', 'E8' => 'Pass',
            default => 'Fail',
        };
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'admission_no' => 'nullable|string',
            'admissionno' => 'nullable|string',
            'admission' => 'nullable|string',
            'adm_no' => 'nullable|string',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'admission_no.required' => 'Admission number is required for each student.',
        ];
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
     * Validate Excel file metadata/structure
     */
    public function validateExcelMetadata($filePath)
    {
        try {
            // Load the Excel file to check structure
            $rows = $this->toCollection($filePath);

            if ($rows->isEmpty()) {
                throw new \Exception('The Excel file is empty.');
            }

            $firstRow = $rows->first();
            $headers = array_keys($firstRow->toArray());

            // Check if there's at least one assessment column
            $hasAssessmentColumn = false;
            foreach ($this->assessments as $assessment) {
                foreach ($headers as $header) {
                    if (strcasecmp(trim($header), $assessment->name) === 0) {
                        $hasAssessmentColumn = true;
                        break 2;
                    }
                }
            }

            if (!$hasAssessmentColumn && $this->assessments->isNotEmpty()) {
                $assessmentNames = $this->assessments->pluck('name')->implode(', ');
                throw new \Exception("Excel file must contain at least one assessment column. Expected columns: {$assessmentNames}");
            }

            // Check for admission number column
            $hasAdmissionColumn = false;
            $admissionColumns = ['admission_no', 'admissionno', 'admission', 'adm_no', 'student_id'];
            foreach ($headers as $header) {
                if (in_array(strtolower(trim($header)), $admissionColumns)) {
                    $hasAdmissionColumn = true;
                    break;
                }
            }

            if (!$hasAdmissionColumn) {
                throw new \Exception('Excel file must contain an admission number column (admission_no, admissionno, admission, or adm_no).');
            }

            return true;

        } catch (\Exception $e) {
            throw new \Exception('Excel validation failed: ' . $e->getMessage());
        }
    }
}
