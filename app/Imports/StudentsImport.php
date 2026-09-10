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
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Validators\Failure;

class StudentsImport implements
    ToModel,
    WithStartRow,
    WithValidation,
    SkipsOnFailure,
    SkipsOnError,
    WithUpserts,
    WithUpsertColumns,
    WithMultipleSheets
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

        Log::info('StudentsImport started', [
            'batch_id'         => $this->batchid,
            'expected_class'   => $this->sclassid,
            'expected_term'    => $this->termid,
            'expected_session' => $this->sessionid,
            'user_id'          => $this->userId,
        ]);
    }

    /**
     * ONLY import the "Student Data" sheet.
     * This is the critical fix.
     */
    public function sheets(): array
    {
        return [
            'Student Data' => $this,
        ];
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

        $clean = fn ($v) => (is_null($v) || trim((string) $v) === '') ? null : trim((string) $v);

        // Cast admission number to string (Excel often returns it as int/float)
        $admissionNo  = $clean($row[0] ?? null);
        if ($admissionNo !== null) {
            $admissionNo = (string) $admissionNo;
        }

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

        // Skip completely empty rows (only locked IDs present)
        if (!$admissionNo && !$lastname && !$firstname) {
            return null;
        }

        if (!$admissionNo || !$lastname || !$firstname) {
            $msg = "Row {$rowNumber}: Admission No, Surname and First Name are required.";
            Log::warning($msg);
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
           $student = Student::updateOrCreate(
                ['admissionNo' => $admissionNo],
                [
                    // NOT NULL, no default — must always have a value
                    'title'            => $title            ?? 'N/A',
                    'firstname'        => $firstname,                 // required input
                    'lastname'         => $lastname,                  // required input
                    'othername'        => $othername        ?? 'N/A',
                    'gender'           => $gender           ?? 'N/A',
                    'future_ambition'  => $futureAmbition   ?? 'N/A', // <-- WAS MISSING
                    'home_address2'    => $homeAddress      ?? 'N/A',
                    'dateofbirth'      => $dob              ?? 'N/A', // varchar — safe
                    'age'              => $age              ?? 'N/A', // varchar — safe, NO is_numeric cast
                    'placeofbirth'     => $placeOfBirth     ?? 'N/A',
                    'religion'         => $religion         ?? 'N/A',
                    'nationality'      => $nationality      ?? 'N/A',
                    'state'            => $state            ?? 'N/A',
                    'local'            => $local            ?? 'N/A',
                    'last_school'      => $lastSchool       ?? 'N/A',
                    'last_class'       => $lastClass        ?? 'N/A',
                    'registeredBy'     => $this->userId     ?? '0',   // NOT NULL — cannot be null

                    // Nullable in DB — null is fine, but 'N/A' is also valid
                    'blood_group'      => $bloodGroup,                // nullable, null OK
                    'genotype'         => $genotype,                  // nullable, null OK
                    'emergency_contact_name'       => $emergencyContactName,       // nullable
                    'emergency_contact_phone'      => $emergencyContactPhone,      // nullable
                    'allergies_medical_conditions' => $allergiesMedical,           // nullable

                    // Defaults
                    'batchid'          => $this->batchid,
                    'statusId'         => 1,
                    'student_status'   => 'Active',
                    'student_category' => 'Day',
                ]
            );
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

            Studentpicture::firstOrCreate(
                ['studentid' => $student->id],
                ['picture' => 'unnamed.jpg']
            );

            Studentclass::updateOrCreate(
                [
                    'studentId' => $student->id,
                    'termid'    => $this->termid,
                    'sessionid' => $this->sessionid,
                ],
                ['schoolclassid' => $this->sclassid]
            );

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

            Studenthouse::updateOrCreate(
                [
                    'studentid' => $student->id,
                    'termid'    => $this->termid,
                    'sessionid' => $this->sessionid,
                ],
                ['schoolhouse' => null]
            );

            Studentpersonalityprofile::firstOrCreate([
                'studentid'     => $student->id,
                'schoolclassid' => $this->sclassid,
                'termid'        => $this->termid,
                'sessionid'     => $this->sessionid,
            ]);

            StudentCurrentTerm::registerTerm(
                $student->id,
                $this->sclassid,
                $this->termid,
                $this->sessionid,
                true
            );

            if ($clubName) {
                $club = Club::whereRaw('LOWER(club) = ?', [strtolower($clubName)])->first();
                if ($club) {
                    StudentClub::updateOrCreate(
                        ['studentid' => $student->id],
                        ['clubid' => $club->id, 'termid' => $this->termid, 'sessionid' => $this->sessionid]
                    );
                }
            }

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

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            Log::warning('Import validation failure', [
                'row'       => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors'    => $failure->errors(),
                'values'    => $failure->values(),
            ]);
        }

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
            // Allow both string and numeric admission numbers
            '0'  => 'required|max:50',
            '1'  => 'required|string|max:100',
            '2'  => 'required|string|max:100',
            '4'  => 'nullable|in:Male,Female',
            '7'  => 'nullable|numeric|min:1|max:100',

            '15' => function ($attribute, $value, $fail) {
                if ((int) $value !== $this->sclassid) {
                    $fail("Class ID does not match. Expected {$this->sclassid}, got {$value}");
                }
            },
            '16' => function ($attribute, $value, $fail) {
                if ((int) $value !== $this->termid) {
                    $fail("Term ID does not match. Expected {$this->termid}, got {$value}");
                }
            },
            '17' => function ($attribute, $value, $fail) {
                if ((int) $value !== $this->sessionid) {
                    $fail("Session ID does not match. Expected {$this->sessionid}, got {$value}");
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