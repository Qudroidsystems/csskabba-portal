<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Studentclass;
use App\Models\Studenthouse;
use App\Models\StudentStatus;
use App\Models\Studentpicture;
use App\Models\PromotionStatus;
use App\Models\ParentRegistration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\ToModel;
use App\Models\Studentpersonalityprofile;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithUpsertColumns;

/**
 * SkipsOnFailure / SkipsOnError mean a bad row is recorded and skipped
 * instead of aborting the whole import. Call $import->failures() and
 * $import->errors() after Excel::import() to retrieve what went wrong.
 */
class StudentsImport implements ToModel, WithStartRow, WithUpsertColumns, WithUpserts, WithValidation, SkipsOnFailure, SkipsOnError
{
    use Importable, SkipsFailures, SkipsErrors;

    public $id = 0;

    protected int $sclassid;
    protected int $termid;
    protected int $sessionid;
    protected int $batchid;

    protected ?string $progressKey = null;
    protected int $totalRows = 0;

    /**
     * Context is passed explicitly rather than read from the HTTP session,
     * because this class runs inside a queue worker where no session exists.
     * Session::get() is kept only as a fallback for any legacy caller.
     */
    public function __construct(
        ?int $schoolclassid = null,
        ?int $termid = null,
        ?int $sessionid = null,
        ?int $batchid = null
    ) {
        $this->sclassid  = $schoolclassid ?? (int) Session::get('sclassid');
        $this->termid    = $termid ?? (int) Session::get('tid');
        $this->sessionid = $sessionid ?? (int) Session::get('sid');
        $this->batchid   = $batchid ?? (int) Session::get('batchid');
    }

    public function setProgressTracking(string $progressKey, int $totalRows): void
    {
        $this->progressKey = $progressKey;
        $this->totalRows   = $totalRows;
    }

    public function model(array $row)
    {
        // Count this row as "attempted" before any exception can interrupt,
        // so the progress bar stays accurate even on skipped/failed rows.
        $this->id++;
        $this->reportProgress();

        $naIfEmpty = function ($value) {
            return (is_null($value) || trim((string) $value) === '') ? 'N/A' : trim((string) $value);
        };

        $schoolclassid = $this->sclassid ?: 'N/A';
        $termid        = $this->termid ?: 'N/A';
        $sessionid     = $this->sessionid ?: 'N/A';
        $batchid       = $this->batchid ?: 'N/A';

        $admissionno = $naIfEmpty($row[0] ?? null);
        $surname = $naIfEmpty($row[1] ?? null);
        $firstname = $naIfEmpty($row[2] ?? null);
        $othername = $naIfEmpty($row[3] ?? null);
        $gender = $naIfEmpty($row[4] ?? null);
        $homeaddress = $naIfEmpty($row[5] ?? null);
        $dob = $naIfEmpty($row[6] ?? null);
        $age = $naIfEmpty($row[7] ?? null);
        $placeofbirth = $naIfEmpty($row[8] ?? null);
        $nationality = $naIfEmpty($row[9] ?? null);
        $state = $naIfEmpty($row[10] ?? null);
        $local = $naIfEmpty($row[11] ?? null);
        $religion = $naIfEmpty($row[12] ?? null);
        $lastschool = $naIfEmpty($row[13] ?? null);
        $lastclass = $naIfEmpty($row[14] ?? null);

        $father_title          = $naIfEmpty($row[18] ?? null);
        $father                = $naIfEmpty($row[19] ?? null);
        $father_phone          = $naIfEmpty($row[20] ?? null);
        $office_address        = $naIfEmpty($row[21] ?? null);
        $father_occupation     = $naIfEmpty($row[22] ?? null);
        $mother_title          = $naIfEmpty($row[23] ?? null);
        $mother                = $naIfEmpty($row[24] ?? null);
        $mother_phone          = $naIfEmpty($row[25] ?? null);
        $mother_occupation     = $naIfEmpty($row[26] ?? null);
        $mother_office_address = $naIfEmpty($row[27] ?? null);
        $parent_address        = $naIfEmpty($row[28] ?? null);
        $parent_religion       = $naIfEmpty($row[29] ?? null);

        $rowNumber = $this->startRow() + $this->id - 1;

        if (in_array($admissionno, ['N/A', ''], true) || in_array($surname, ['N/A', ''], true) || in_array($firstname, ['N/A', ''], true)) {
            throw new \Exception("Row {$rowNumber}: required fields (admissionno, surname, firstname) cannot be empty or 'N/A'.");
        }

        if (in_array($schoolclassid, ['N/A', ''], true) || in_array($termid, ['N/A', ''], true) || in_array($sessionid, ['N/A', ''], true) || in_array($batchid, ['N/A', ''], true)) {
            throw new \Exception("Row {$rowNumber}: session data (schoolclassid, termid, sessionid, batchid) cannot be empty or 'N/A'.");
        }

        $studentbiodata = new Student();
        $studentclass = new Studentclass();
        $promotion = new PromotionStatus();
        $parent = new ParentRegistration();
        $studenthouse = new Studenthouse();
        $picture = new Studentpicture();
        $studentpersonalityprofile = new Studentpersonalityprofile();
        $studentStatus = StudentStatus::where('status', 'old')->first();

        return \DB::transaction(function () use (
            $studentbiodata, $studentclass, $promotion, $parent, $studenthouse, $picture, $studentpersonalityprofile, $studentStatus,
            $admissionno, $surname, $firstname, $othername, $gender, $homeaddress, $dob, $age, $placeofbirth, $nationality, $state, $local, $religion, $lastschool, $lastclass,
            $father_title, $father, $father_phone, $office_address, $father_occupation,
            $mother_title, $mother, $mother_phone, $mother_occupation, $mother_office_address, $parent_address, $parent_religion,
            $schoolclassid, $termid, $sessionid, $batchid
        ) {
            $studentbiodata->admissionNo = $admissionno;
            $studentbiodata->title = 'N/A';
            $studentbiodata->firstname = $firstname;
            $studentbiodata->lastname = $surname;
            $studentbiodata->othername = $othername;
            $studentbiodata->gender = $gender;
            $studentbiodata->home_address = $homeaddress;
            $studentbiodata->home_address2 = 'N/A';
            $studentbiodata->dateofbirth = $dob;
            $studentbiodata->age = $age;
            $studentbiodata->placeofbirth = $placeofbirth;
            $studentbiodata->religion = $religion;
            $studentbiodata->nationality = $nationality;
            $studentbiodata->state = $state;
            $studentbiodata->local = $local;
            $studentbiodata->last_school = $lastschool;
            $studentbiodata->last_class = $lastclass;
            $studentbiodata->registeredBy = Auth::user()->id ?? 'N/A';
            $studentbiodata->batchid = $batchid;
            $studentbiodata->statusId = $studentStatus ? $studentStatus->id : 'N/A';
            $studentbiodata->save();
            $studentId = $studentbiodata->id;

            $parent->studentId = $studentId;
            $parent->father_title = $father_title;
            $parent->father = $father;
            $parent->father_phone = $father_phone;
            $parent->office_address = $office_address;
            $parent->father_occupation = $father_occupation;
            $parent->mother_title = $mother_title;
            $parent->mother = $mother;
            $parent->mother_phone = $mother_phone;
            $parent->mother_occupation = $mother_occupation;
            $parent->mother_office_address = $mother_office_address;
            $parent->parent_address = $parent_address;
            $parent->religion = $parent_religion;
            $parent->save();

            $picture->studentid = $studentId;
            $picture->picture = 'N/A';
            $picture->save();

            $studentclass->studentId = $studentId;
            $studentclass->schoolclassid = $schoolclassid;
            $studentclass->termid = $termid;
            $studentclass->sessionid = $sessionid;
            $studentclass->save();

            $promotion->studentId = $studentId;
            $promotion->schoolclassid = $schoolclassid;
            $promotion->termid = $termid;
            $promotion->sessionid = $sessionid;
            $promotion->promotionStatus = 'PROMOTED';
            $promotion->classstatus = 'CURRENT';
            $promotion->save();

            $studenthouse->studentid = $studentId;
            $studenthouse->termid = $termid;
            $studenthouse->sessionid = $sessionid;
            $studenthouse->schoolhouse = 'N/A';
            $studenthouse->save();

            $studentpersonalityprofile->studentid = $studentId;
            $studentpersonalityprofile->schoolclassid = $schoolclassid;
            $studentpersonalityprofile->termid = $termid;
            $studentpersonalityprofile->sessionid = $sessionid;
            $studentpersonalityprofile->save();

            return $studentbiodata;
        });
    }

    protected function reportProgress(): void
    {
        if (!$this->progressKey || $this->totalRows <= 0) {
            return;
        }

        $isLastRow = $this->id >= $this->totalRows;

        if ($this->id % 3 !== 0 && !$isLastRow) {
            return;
        }

        Cache::put($this->progressKey, [
            'status'   => 'processing',
            'progress' => min($this->id, $this->totalRows),
            'total'    => $this->totalRows,
            'message'  => "Processed {$this->id} of {$this->totalRows} rows",
        ], now()->addMinutes(30));
    }

    public function rules(): array
    {
        return [
            '0' => 'required',
            '1' => 'required',
            '2' => 'required',
            '4' => 'nullable|in:Male,Female',
            '7' => 'numeric|nullable',
            '15' => function ($attribute, $value, $onFailure) {
                if ($value != $this->sclassid) {
                    $onFailure('This data does not match the selected School Class');
                }
            },
            '16' => function ($attribute, $value, $onFailure) {
                if ($value != $this->termid) {
                    $onFailure('This data does not match the selected School Term');
                }
            },
            '17' => function ($attribute, $value, $onFailure) {
                if ($value != $this->sessionid) {
                    $onFailure('This data does not match the selected School Session');
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
            '4.in' => 'Gender must be Male or Female.',
            '7.numeric' => 'Age must be a number.',
            '15' => 'School class ID does not match the selected class.',
            '16' => 'Term ID does not match the selected term.',
            '17' => 'Session ID does not match the selected session.',
        ];
    }

    public function customValidationAttributes()
    {
        return [
            '0' => 'admissionno',
            '1' => 'surname',
            '2' => 'firstname',
            '4' => 'gender',
            '7' => 'age',
            '15' => 'schoolclassid',
            '16' => 'termid',
            '17' => 'sessionid',
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
            'registeredBy', 'batchid', 'statusId',
        ];
    }
}