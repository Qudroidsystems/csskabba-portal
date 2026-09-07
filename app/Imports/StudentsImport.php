<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Studentclass;
use App\Models\Studenthouse;
use App\Models\Studentpicture;
use App\Models\PromotionStatus;
use App\Models\ParentRegistration;
use App\Models\Studentpersonalityprofile;
use App\Models\StudentCurrentTerm;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithUpsertColumns;

class StudentsImport implements
    ToModel,
    WithStartRow,
    WithValidation,
    SkipsOnFailure,
    SkipsOnError,
    WithUpserts,
    WithUpsertColumns
{
    use Importable, SkipsFailures, SkipsErrors;

    protected int $sclassid;
    protected int $termid;
    protected int $sessionid;
    protected int $batchid;
    protected ?int $userId;

    protected int $rowCounter = 0;
    protected ?string $progressKey = null;
    protected int $totalRows = 0;

    public function __construct(
        int $schoolclassid,
        int $termid,
        int $sessionid,
        int $batchid,
        ?int $userId = null
    ) {
        $this->sclassid  = $schoolclassid;
        $this->termid    = $termid;
        $this->sessionid = $sessionid;
        $this->batchid   = $batchid;
        $this->userId    = $userId;
    }

    public function setProgressTracking(string $progressKey, int $totalRows): void
    {
        $this->progressKey = $progressKey;
        $this->totalRows   = $totalRows;
    }

    public function model(array $row)
    {
        $this->rowCounter++;
        $this->reportProgress();

        $clean = fn ($v) => (is_null($v) || trim((string) $v) === '') ? null : trim((string) $v);

        $admissionNo  = $clean($row[0] ?? null);
        $lastname     = $clean($row[1] ?? null);
        $firstname    = $clean($row[2] ?? null);
        $othername    = $clean($row[3] ?? null);
        $gender       = $clean($row[4] ?? null);
        $homeAddress  = $clean($row[5] ?? null);
        $dob          = $clean($row[6] ?? null);
        $age          = $clean($row[7] ?? null);
        $placeOfBirth = $clean($row[8] ?? null);
        $nationality  = $clean($row[9] ?? null);
        $state        = $clean($row[10] ?? null);
        $local        = $clean($row[11] ?? null);
        $religion     = $clean($row[12] ?? null);
        $lastSchool   = $clean($row[13] ?? null);
        $lastClass    = $clean($row[14] ?? null);

        // Parent fields
        $fatherTitle      = $clean($row[18] ?? null);
        $fatherName       = $clean($row[19] ?? null);
        $fatherPhone      = $clean($row[20] ?? null);
        $officeAddress    = $clean($row[21] ?? null);
        $fatherOccupation = $clean($row[22] ?? null);
        $motherTitle      = $clean($row[23] ?? null);
        $motherName       = $clean($row[24] ?? null);
        $motherPhone      = $clean($row[25] ?? null);
        $motherOccupation = $clean($row[26] ?? null);
        $motherOfficeAddr = $clean($row[27] ?? null);
        $parentAddress    = $clean($row[28] ?? null);
        $parentReligion   = $clean($row[29] ?? null);

        $rowNumber = $this->startRow() + $this->rowCounter - 1;

        if (!$admissionNo || !$lastname || !$firstname) {
            throw new \Exception("Row {$rowNumber}: Admission No, Surname and First Name are required.");
        }

        return DB::transaction(function () use (
            $admissionNo, $lastname, $firstname, $othername, $gender, $homeAddress,
            $dob, $age, $placeOfBirth, $nationality, $state, $local, $religion,
            $lastSchool, $lastClass,
            $fatherTitle, $fatherName, $fatherPhone, $officeAddress, $fatherOccupation,
            $motherTitle, $motherName, $motherPhone, $motherOccupation, $motherOfficeAddr,
            $parentAddress, $parentReligion
        ) {
            // 1. Student (upsert by admissionNo)
            $student = Student::updateOrCreate(
                ['admissionNo' => $admissionNo],
                [
                    'title'            => 'N/A',
                    'firstname'        => $firstname,
                    'lastname'         => $lastname,
                    'othername'        => $othername,
                    'gender'           => $gender,
                    'home_address'     => $homeAddress,
                    'home_address2'    => $homeAddress ?? 'N/A',
                    'dateofbirth'      => $dob,
                    'age'              => is_numeric($age) ? (int) $age : null,
                    'placeofbirth'     => $placeOfBirth,
                    'religion'         => $religion,
                    'nationality'      => $nationality,
                    'state'            => $state,
                    'local'            => $local,
                    'last_school'      => $lastSchool,
                    'last_class'       => $lastClass,
                    'registeredBy'     => $this->userId,
                    'batchid'          => $this->batchid,
                    'statusId'         => 1,          // Old student for batch uploads
                    'student_status'   => 'Active',
                    'student_category' => 'Day',
                ]
            );

            // 2. Parent
            ParentRegistration::updateOrCreate(
                ['studentId' => $student->id],
                [
                    'father_title'          => $fatherTitle,
                    'father'                => $fatherName,
                    'father_phone'          => $fatherPhone,
                    'office_address'        => $officeAddress,
                    'father_occupation'     => $fatherOccupation,
                    'mother_title'          => $motherTitle,
                    'mother'                => $motherName,
                    'mother_phone'          => $motherPhone,
                    'mother_occupation'     => $motherOccupation,
                    'mother_office_address' => $motherOfficeAddr,
                    'parent_address'        => $parentAddress,
                    'religion'              => $parentReligion,
                ]
            );

            // 3. Picture
            Studentpicture::firstOrCreate(
                ['studentid' => $student->id],
                ['picture' => 'unnamed.jpg']
            );

            // 4. Studentclass
            Studentclass::updateOrCreate(
                [
                    'studentId' => $student->id,
                    'termid'    => $this->termid,
                    'sessionid' => $this->sessionid,
                ],
                ['schoolclassid' => $this->sclassid]
            );

            // 5. PromotionStatus
            PromotionStatus::updateOrCreate(
                [
                    'studentId'     => $student->id,
                    'schoolclassid' => $this->sclassid,
                    'termid'        => $this->termid,
                    'sessionid'     => $this->sessionid,
                ],
                [
                    'promotionStatus' => 'PROMOTED',
                    'classstatus'     => 'CURRENT',
                ]
            );

            // 6. Student house
            Studenthouse::updateOrCreate(
                [
                    'studentid' => $student->id,
                    'termid'    => $this->termid,
                    'sessionid' => $this->sessionid,
                ],
                ['schoolhouse' => null]
            );

            // 7. Personality profile
            Studentpersonalityprofile::firstOrCreate([
                'studentid'     => $student->id,
                'schoolclassid' => $this->sclassid,
                'termid'        => $this->termid,
                'sessionid'     => $this->sessionid,
            ]);

            // 8. Current Term – use the official model method
            StudentCurrentTerm::registerTerm(
                $student->id,
                $this->sclassid,
                $this->termid,
                $this->sessionid,
                true   // mark as current
            );

            return $student;
        });
    }

    protected function reportProgress(): void
    {
        if (!$this->progressKey || $this->totalRows <= 0) {
            return;
        }

        // Update every 5 rows or on the last row
        if ($this->rowCounter % 5 !== 0 && $this->rowCounter < $this->totalRows) {
            return;
        }

        Cache::put($this->progressKey, [
            'status'   => 'processing',
            'progress' => min($this->rowCounter, $this->totalRows),
            'total'    => $this->totalRows,
            'message'  => "Processed {$this->rowCounter} of {$this->totalRows} rows",
        ], now()->addMinutes(45));
    }

    public function rules(): array
    {
        return [
            '0'  => 'required|string|max:50',
            '1'  => 'required|string|max:100',
            '2'  => 'required|string|max:100',
            '4'  => 'nullable|in:Male,Female',
            '7'  => 'nullable|numeric|min:1|max:100',
            '15' => function ($attribute, $value, $fail) {
                if ((int) $value !== $this->sclassid) {
                    $fail('Class ID does not match the selected class for this batch.');
                }
            },
            '16' => function ($attribute, $value, $fail) {
                if ((int) $value !== $this->termid) {
                    $fail('Term ID does not match the selected term for this batch.');
                }
            },
            '17' => function ($attribute, $value, $fail) {
                if ((int) $value !== $this->sessionid) {
                    $fail('Session ID does not match the selected session for this batch.');
                }
            },
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.required' => 'Admission number is required.',
            '1.required' => 'Surname is required.',
            '2.required' => 'First name is required.',
            '4.in'       => 'Gender must be Male or Female.',
        ];
    }

    public function startRow(): int
    {
        return 2;
    }

    public function uniqueBy()
    {
        return 'admissionNo';
    }

    public function upsertColumns()
    {
        return [
            'title', 'firstname', 'lastname', 'othername', 'gender',
            'home_address', 'home_address2', 'dateofbirth', 'age', 'placeofbirth',
            'religion', 'nationality', 'state', 'local', 'last_school', 'last_class',
            'registeredBy', 'batchid', 'statusId', 'student_status',
        ];
    }
}