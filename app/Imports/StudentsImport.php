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
use App\Models\Club;
use App\Models\Sport;
use App\Models\StudentClub;
use App\Models\StudentSport;
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
use Maatwebsite\Excel\Validators\Failure;

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

        // Debug: show what IDs the import expects
        Log::info('StudentsImport started', [
            'batch_id'       => $this->batchid,
            'expected_class' => $this->sclassid,
            'expected_term'  => $this->termid,
            'expected_session' => $this->sessionid,
            'user_id'        => $this->userId,
        ]);
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

        $rowNumber = $this->startRow() + $this->rowCounter - 1;

        // ---------- DEBUG: log the raw row data ----------
        Log::debug("Import row {$rowNumber} data", [
            'row_number' => $rowNumber,
            'admissionNo'=> $row[0]  ?? null,
            'surname'    => $row[1]  ?? null,
            'firstname'  => $row[2]  ?? null,
            'gender'     => $row[4]  ?? null,
            'class_id'   => $row[15] ?? null,   // locked column
            'term_id'    => $row[16] ?? null,   // locked column
            'session_id' => $row[17] ?? null,   // locked column
            'full_row'   => $row,               // remove later if too noisy
        ]);

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

        // Extended fields
        $bloodGroup            = $clean($row[30] ?? null);
        $genotype              = $clean($row[31] ?? null);
        $emergencyContactName  = $clean($row[32] ?? null);
        $emergencyContactPhone = $clean($row[33] ?? null);
        $allergiesMedical      = $clean($row[34] ?? null);
        $guardianName          = $clean($row[35] ?? null);
        $guardianRelationship  = $clean($row[36] ?? null);
        $guardianPhone         = $clean($row[37] ?? null);
        $whatsappNumber        = $clean($row[38] ?? null);
        $clubName              = $clean($row[39] ?? null);
        $sportName             = $clean($row[40] ?? null);

        if (!$admissionNo || !$lastname || !$firstname) {
            $msg = "Row {$rowNumber}: Admission No, Surname and First Name are required.";
            Log::warning($msg, compact('admissionNo', 'lastname', 'firstname'));
            throw new \Exception($msg);
        }

        return DB::transaction(function () use (
            $admissionNo, $lastname, $firstname, $othername, $gender, $homeAddress,
            $dob, $age, $placeOfBirth, $nationality, $state, $local, $religion,
            $lastSchool, $lastClass,
            $fatherTitle, $fatherName, $fatherPhone, $officeAddress, $fatherOccupation,
            $motherTitle, $motherName, $motherPhone, $motherOccupation, $motherOfficeAddr,
            $parentAddress, $parentReligion,
            $bloodGroup, $genotype, $emergencyContactName, $emergencyContactPhone,
            $allergiesMedical, $guardianName, $guardianRelationship, $guardianPhone,
            $whatsappNumber, $clubName, $sportName, $rowNumber
        ) {
            // 1. Student
            $student = Student::updateOrCreate(
                ['admissionNo' => $admissionNo],
                [
                    'title'                       => 'N/A',
                    'firstname'                   => $firstname,
                    'lastname'                    => $lastname,
                    'othername'                   => $othername,
                    'gender'                      => $gender,
                    'home_address'                => $homeAddress,
                    'home_address2'               => $homeAddress ?? 'N/A',
                    'dateofbirth'                 => $dob,
                    'age'                         => is_numeric($age) ? (int) $age : null,
                    'placeofbirth'                => $placeOfBirth,
                    'religion'                    => $religion,
                    'nationality'                 => $nationality,
                    'state'                       => $state,
                    'local'                       => $local,
                    'last_school'                 => $lastSchool,
                    'last_class'                  => $lastClass,
                    'blood_group'                 => $bloodGroup,
                    'genotype'                    => $genotype,
                    'emergency_contact_name'      => $emergencyContactName,
                    'emergency_contact_phone'     => $emergencyContactPhone,
                    'allergies_medical_conditions'=> $allergiesMedical,
                    'registeredBy'                => $this->userId,
                    'batchid'                     => $this->batchid,
                    'statusId'                    => 1,
                    'student_status'              => 'Active',
                    'student_category'            => 'Day',
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
                    'guardian_name'         => $guardianName,
                    'guardian_relationship' => $guardianRelationship,
                    'guardian_phone'        => $guardianPhone,
                    'whatsapp_number'       => $whatsappNumber,
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

            // 8. Current Term
            StudentCurrentTerm::registerTerm(
                $student->id,
                $this->sclassid,
                $this->termid,
                $this->sessionid,
                true
            );

            // 9. Club
            if ($clubName) {
                $club = Club::whereRaw('LOWER(club) = ?', [strtolower($clubName)])->first();
                if ($club) {
                    StudentClub::updateOrCreate(
                        ['studentid' => $student->id],
                        ['clubid' => $club->id, 'termid' => $this->termid, 'sessionid' => $this->sessionid]
                    );
                }
            }

            // 10. Sport
            if ($sportName) {
                $sport = Sport::whereRaw('LOWER(sport) = ?', [strtolower($sportName)])->first();
                if ($sport) {
                    StudentSport::updateOrCreate(
                        ['studentid' => $student->id],
                        ['sportid' => $sport->id, 'termid' => $this->termid, 'sessionid' => $this->sessionid]
                    );
                }
            }

            Log::info("Row {$rowNumber} imported successfully", [
                'admissionNo' => $admissionNo,
                'student_id'  => $student->id,
            ]);

            return $student;
        });
    }

    /**
     * Called by Maatwebsite when a row fails validation.
     * We log the exact failures so we can see them in laravel.log
     */
    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            Log::warning('Import validation failure', [
                'row'       => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors'    => $failure->errors(),
                'values'    => $failure->values(),   // the actual cell values that failed
            ]);
        }

        // Still collect them the normal way (SkipsFailures trait)
        $this->failures = array_merge($this->failures ?? [], $failures);
    }

    protected function reportProgress(): void
    {
        if (!$this->progressKey || $this->totalRows <= 0) {
            return;
        }

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

            // These three are the most common reason for "all rows failed"
            '15' => function ($attribute, $value, $fail) {
                $expected = $this->sclassid;
                $actual   = (int) $value;
                if ($actual !== $expected) {
                    Log::warning('Class ID mismatch', [
                        'row_attribute' => $attribute,
                        'expected'      => $expected,
                        'actual'        => $value,
                        'actual_int'    => $actual,
                    ]);
                    $fail("Class ID does not match the selected class for this batch. Expected {$expected}, got {$value}");
                }
            },
            '16' => function ($attribute, $value, $fail) {
                $expected = $this->termid;
                $actual   = (int) $value;
                if ($actual !== $expected) {
                    Log::warning('Term ID mismatch', [
                        'row_attribute' => $attribute,
                        'expected'      => $expected,
                        'actual'        => $value,
                        'actual_int'    => $actual,
                    ]);
                    $fail("Term ID does not match the selected term for this batch. Expected {$expected}, got {$value}");
                }
            },
            '17' => function ($attribute, $value, $fail) {
                $expected = $this->sessionid;
                $actual   = (int) $value;
                if ($actual !== $expected) {
                    Log::warning('Session ID mismatch', [
                        'row_attribute' => $attribute,
                        'expected'      => $expected,
                        'actual'        => $value,
                        'actual_int'    => $actual,
                    ]);
                    $fail("Session ID does not match the selected session for this batch. Expected {$expected}, got {$value}");
                }
            },

            '30' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            '31' => 'nullable|in:AA,AS,SS,AC,SC,CC',
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.required' => 'Admission number is required.',
            '1.required' => 'Surname is required.',
            '2.required' => 'First name is required.',
            '4.in'       => 'Gender must be Male or Female.',
            '30.in'      => 'Blood Group must be one of A+, A-, B+, B-, AB+, AB-, O+, O-.',
            '31.in'      => 'Genotype must be one of AA, AS, SS, AC, SC, CC.',
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
            'blood_group', 'genotype', 'emergency_contact_name', 'emergency_contact_phone',
            'allergies_medical_conditions',
            'registeredBy', 'batchid', 'statusId', 'student_status',
        ];
    }
}