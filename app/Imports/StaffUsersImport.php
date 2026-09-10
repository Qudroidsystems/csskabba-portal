<?php

namespace App\Imports;

use App\Models\User;
use App\Models\BioModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Validators\Failure;

class StaffUsersImport implements
    ToModel,
    WithStartRow,
    WithValidation,
    SkipsOnFailure,
    SkipsOnError,
    WithMultipleSheets
{
    use Importable, SkipsFailures, SkipsErrors;

    protected int $rowCounter = 0;
    protected array $created = [];
    protected array $skipped = [];

    public function sheets(): array
    {
        return [
            'Staff Users' => $this,
        ];
    }

    public function model(array $row)
    {
        $this->rowCounter++;

        $name     = trim((string) ($row[0] ?? ''));
        $email    = strtolower(trim((string) ($row[1] ?? '')));
        $role     = trim((string) ($row[2] ?? ''));
        $password = trim((string) ($row[3] ?? ''));

        // Skip completely empty rows
        if ($name === '' && $email === '' && $password === '') {
            return null;
        }

        if ($name === '' || $email === '' || $password === '') {
            $this->skipped[] = "Row " . ($this->rowCounter + 1) . ": Name, Email and Password are required.";
            return null;
        }

        // Force Staff role only
        if (strtolower($role) !== 'staff' && $role !== '') {
            $this->skipped[] = "Row " . ($this->rowCounter + 1) . ": Role must be 'Staff' (got '{$role}').";
            return null;
        }

        if (User::where('email', $email)->exists()) {
            $this->skipped[] = "Row " . ($this->rowCounter + 1) . ": Email {$email} already exists.";
            return null;
        }

        $user = User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make($password),
        ]);

        $user->syncRoles(['Staff']);

        // Optional basic bio record
        BioModel::updateOrCreate(
            ['user_id' => $user->id],
            [
                'firstname' => explode(' ', $name)[0] ?? $name,
                'lastname'  => explode(' ', $name)[1] ?? '',
            ]
        );

        $this->created[] = [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
        ];

        Log::info("Staff user imported", ['email' => $email, 'user_id' => $user->id]);

        return $user;
    }

    public function rules(): array
    {
        return [
            '0' => 'required|string|max:255',          // Name
            '1' => 'required|email|max:255',           // Email
            '3' => 'required|string|min:6|max:100',    // Password
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.required' => 'Full Name is required.',
            '1.required' => 'Email is required.',
            '1.email'    => 'Email must be a valid email address.',
            '3.required' => 'Password is required.',
            '3.min'      => 'Password must be at least 6 characters.',
        ];
    }

    public function startRow(): int
    {
        return 2;
    }

    public function getCreated(): array
    {
        return $this->created;
    }

    public function getSkipped(): array
    {
        return $this->skipped;
    }
}