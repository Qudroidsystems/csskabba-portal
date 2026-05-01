<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BioModel;
use App\Models\Student;
use App\Models\Studentpicture;
use App\Models\User;
use App\Models\PasswordResetHistory; // You'll need to create this model
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View user|Create user|Update user|Delete user', ['only' => ['index', 'store']]);
        $this->middleware('permission:Create user', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update user', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete user', ['only' => ['destroy']]);
    }

    // ─────────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────────
    public function index(Request $request): View
    {
        $pagetitle = "User Management";
        $data = User::latest()->get();
        $roles = Role::pluck('name', 'name')->toArray();
        $role_permissions = Role::all();

        $role_counts = [];
        foreach ($roles as $role) {
            $role_counts[$role] = User::role($role)->count();
        }
        $role_counts['No Role'] = User::doesntHave('roles')->count();

        return view('users.index', compact('data', 'roles', 'role_permissions', 'pagetitle', 'role_counts'));
    }

    // ─────────────────────────────────────────────────────────────────
    // CREATE
    // ─────────────────────────────────────────────────────────────────
    public function create(): View
    {
        $title = "Create User";
        $roles = Role::pluck('name', 'name')->all();
        return view('users.create', compact('roles', 'title'));
    }

    // ─────────────────────────────────────────────────────────────────
    // STORE (generic user)
    // ─────────────────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        Log::debug("Creating user", $request->all());

        try {
            if (!auth()->user()->hasPermissionTo('Create user')) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have the right permissions',
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8|confirmed',
                'roles' => 'required|array',
                'roles.*' => 'exists:roles,name',
                'phone_number' => 'nullable|string|regex:/^\+[1-9]\d{1,14}$/',
            ]);

            $input = $request->all();
            $plainPassword = $input['password'];
            $input['password'] = Hash::make($input['password']);

            $user = User::create($input);
            $user->syncRoles($request->input('roles'));

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name')->toArray(),
                    'phone_number' => $user->phone_number,
                    'password' => $plainPassword,
                ],
            ], 201);

        } catch (ValidationException $e) {
            Log::error("Validation error creating user: " . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error("Create user error: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────────────────────────────
    public function show($id): View
    {
        $pagetitle = "User Overview";

        $user = User::with([
            'roles',
            'bio',
            'student',
            'staffemploymentDetails',
            'staffPicture',
        ])->findOrFail($id);

        $userbio = $user->bio;
        $staffInfo = $user->staffemploymentDetails;
        $staffPicture = $user->staffPicture;
        $studentPicture = null;

        if ($user->isStudent() && $user->student_id) {
            $studentPicture = Studentpicture::where('studentid', $user->student_id)->first();
        }

        $isStudentUser = $user->hasRole('student');
        $studentData = $user->student;
        $currentClass = null;
        $parentData = null;

        if ($isStudentUser && $studentData) {
            $currentClass = $studentData->currentClass;
            $parentData = $studentData->parent;
        }

        return view('users.overview', compact(
            'user', 'userbio', 'staffInfo', 'staffPicture', 'studentPicture',
            'pagetitle', 'isStudentUser', 'studentData', 'currentClass', 'parentData'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────────────────────────────
    public function edit($id): View
    {
        if (auth()->user()->hasRole('student')) {
            abort(403, 'Students are not allowed to edit profiles.');
        }

        $user = User::findOrFail($id);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name', 'name')->all();

        return view('users.edit', compact('user', 'roles', 'userRole'));
    }

    // ─────────────────────────────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────────────────────────────
    public function update(Request $request, $id): JsonResponse
    {
        if (auth()->user()->hasRole('student')) {
            return response()->json([
                'success' => false,
                'message' => 'Students are not allowed to edit profiles.',
            ], 403);
        }

        Log::debug("Updating user ID: {$id}", $request->all());

        try {
            if (!auth()->user()->hasPermissionTo('Update user')) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have the right permissions',
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id,
                'password' => 'nullable|min:8|confirmed',
                'roles' => 'required|array',
                'roles.*' => 'exists:roles,name',
                'phone_number' => 'nullable|string|regex:/^\+[1-9]\d{1,14}$/',
            ]);

            $input = $request->all();
            $plainPassword = !empty($input['password']) ? $input['password'] : null;

            if (!empty($input['password'])) {
                $input['password'] = Hash::make($input['password']);
            } else {
                $input = Arr::except($input, ['password']);
            }

            $user = User::findOrFail($id);
            $user->update($input);
            $user->syncRoles($request->input('roles'));

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name')->toArray(),
                    'phone_number' => $user->phone_number,
                    'password' => $plainPassword,
                ],
            ], 200);

        } catch (ValidationException $e) {
            Log::error("Validation error updating user: " . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error("Update user error: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // DESTROY
    // ─────────────────────────────────────────────────────────────────
    public function destroy($id): JsonResponse
    {
        Log::debug("Attempting to delete user ID: {$id}");

        try {
            $user = User::findOrFail($id);
            $isStudent = $user->hasRole('student');

            BioModel::where('user_id', $id)->delete();

            // Store student_id before detaching if needed for password reset history
            $studentId = $user->student_id;

            $user->roles()->detach();
            $user->delete();

            // Optional: Log this deletion in password reset history
            if ($studentId) {
                PasswordResetHistory::create([
                    'student_id' => $studentId,
                    'action' => 'account_removed',
                    'performed_by' => auth()->id(),
                    'old_password_hash' => null,
                    'new_password_hash' => null,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => $isStudent
                    ? 'User account removed. Student record remains in Student Management.'
                    : 'User deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            Log::error("Delete user error: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // ROLES LIST
    // ─────────────────────────────────────────────────────────────────
    public function roles(): JsonResponse
    {
        $roles = Role::pluck('name')->all();
        return response()->json(['roles' => $roles]);
    }

    // ─────────────────────────────────────────────────────────────────
    // SINGLE student user creation
    // ─────────────────────────────────────────────────────────────────
    public function storeStudent(Request $request): JsonResponse
    {
        Log::debug("Creating student user", $request->all());

        try {
            if (!auth()->user()->hasPermissionTo('Create user')) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have the right permissions',
                ], 403);
            }

            $validated = $request->validate([
                'student_id' => 'required|exists:studentRegistration,id',
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'username' => 'required|string|unique:users,username|max:255',
                'password' => 'required|min:8|confirmed',
                'roles' => 'required|array|min:1',
                'roles.*' => 'exists:roles,name',
            ]);

            $input = $request->all();
            $input['username'] = str_replace('/', '_', $input['username']);
            $plainPassword = $input['password'];
            $input['password'] = Hash::make($input['password']);

            $user = User::create($input);
            $user->syncRoles($request->input('roles'));

            $student = Student::findOrFail($validated['student_id']);

            BioModel::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'firstname' => $student->firstname,
                    'lastname' => $student->lastname,
                    'othernames' => $student->othername ?? '',
                    'phone' => $student->phone_number ?? '',
                    'address' => $student->home_address2 ?? '',
                    'gender' => $student->gender ?? '',
                    'maritalstatus' => '',
                    'nationality' => $student->nationality ?? '',
                    'dob' => $student->dateofbirth ?? '',
                ]
            );

            // Log password creation
            PasswordResetHistory::create([
                'student_id' => $student->id,
                'action' => 'created',
                'performed_by' => auth()->id(),
                'new_password_hash' => Hash::make($plainPassword),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Student user created successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'roles' => $user->roles->pluck('name')->toArray(),
                    'password' => $plainPassword,
                ],
            ], 201);

        } catch (ValidationException $e) {
            Log::error("Validation error creating student user: " . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error("Create student user error: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to create student user',
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // MASS student user creation (UPDATED with better UX)
    // ─────────────────────────────────────────────────────────────────
    public function massCreateStudents(Request $request): JsonResponse
    {
        Log::debug("Mass creating/updating student users", ['count' => count($request->input('students', []))]);

        try {
            if (!auth()->user()->hasPermissionTo('Create user')) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have the right permissions',
                ], 403);
            }

            $validated = $request->validate([
                'students' => 'required|array|min:1',
                'students.*.student_id' => 'required|exists:studentRegistration,id',
                'students.*.action' => 'nullable|in:create,reset,reprint', // NEW: action per student
                'roles' => 'sometimes|array',
                'roles.*' => 'exists:roles,name',
                'password_type' => 'required_if:action_mode,create|in:same,individual',
                'shared_password' => 'required_if:password_type,same|nullable|string|min:6',
                'action_mode' => 'required|in:create,reset,reprint', // NEW: mode selection
                'reprint_only' => 'boolean', // NEW: reprint only mode
            ]);

            $created = [];
            $reset = [];
            $reprinted = [];
            $skipped = [];
            $errors = [];

            DB::beginTransaction();

            foreach ($validated['students'] as $entry) {
                $studentId = $entry['student_id'];
                $action = $entry['action'] ?? $validated['action_mode'];

                $student = DB::table('studentRegistration')->where('id', $studentId)->first();

                if (!$student) {
                    $errors[] = "Student ID {$studentId} not found.";
                    continue;
                }

                $existingUser = User::where('student_id', $studentId)->first();

                // Handle based on action
                switch ($action) {
                    case 'create':
                        if ($existingUser) {
                            $skipped[] = trim("{$student->firstname} {$student->lastname}") . " (already has account)";
                            continue 2;
                        }

                        $result = $this->createUserForStudent($student, $validated);
                        if ($result['success']) {
                            $created[] = $result['data'];
                        } else {
                            $errors[] = $result['error'];
                        }
                        break;

                    case 'reset':
                        if (!$existingUser) {
                            $skipped[] = trim("{$student->firstname} {$student->lastname}") . " (no account to reset)";
                            continue 2;
                        }

                        $result = $this->resetUserPassword($existingUser, $validated);
                        if ($result['success']) {
                            $reset[] = $result['data'];
                        } else {
                            $errors[] = $result['error'];
                        }
                        break;

                    case 'reprint':
                        if (!$existingUser) {
                            $skipped[] = trim("{$student->firstname} {$student->lastname}") . " (no account to reprint)";
                            continue 2;
                        }

                        $reprinted[] = $this->getUserCredentials($existingUser, $student);
                        break;
                }
            }

            DB::commit();

            // Sort alphabetically by name
            usort($created, fn($a, $b) => strcmp($a['name'], $b['name']));
            usort($reset, fn($a, $b) => strcmp($a['name'], $b['name']));
            usort($reprinted, fn($a, $b) => strcmp($a['name'], $b['name']));

            $allPrintable = array_merge($created, $reset, $reprinted);

            $message = [];
            if (count($created)) $message[] = count($created) . " account(s) created";
            if (count($reset)) $message[] = count($reset) . " password(s) reset";
            if (count($reprinted)) $message[] = count($reprinted) . " credential(s) reprinted";
            if (count($skipped)) $message[] = count($skipped) . " skipped";

            return response()->json([
                'success' => true,
                'message' => implode(', ', $message) . '.',
                'created' => $created,
                'reset' => $reset,
                'reprinted' => $reprinted,
                'all_printable' => $allPrintable, // All credentials for printing
                'skipped' => $skipped,
                'errors' => $errors,
                'created_count' => count($created),
                'reset_count' => count($reset),
                'reprinted_count' => count($reprinted),
                'skipped_count' => count($skipped),
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error("Validation error mass operation: " . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Mass operation error: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to process: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Helper method to create user for a student
    private function createUserForStudent($student, $validated)
    {
        try {
            // Build email
            $email = !empty($student->email) ? trim($student->email) : null;

            if (!$email || User::where('email', $email)->exists()) {
                $base = strtolower(
                    Str::ascii(trim($student->firstname)) . '.' .
                    Str::ascii(trim($student->lastname))
                );
                $email = $base . '@student.school';

                if (User::where('email', $email)->exists()) {
                    $email = $base . '.' . $student->id . '@student.school';
                }
            }

            // Build username from admission number
            $baseUsername = str_replace(['/', '\\', ' '], '_', $student->admissionNo ?? "stu_{$student->id}");
            $username = $baseUsername;
            $suffix = 1;
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . '_' . $suffix++;
            }

            // Password
            $plainPassword = $validated['password_type'] === 'same'
                ? $validated['shared_password']
                : strtoupper(Str::random(4)) . rand(100, 999) . strtolower(Str::random(3));

            // Create user
            $user = User::create([
                'name' => trim("{$student->firstname} {$student->lastname}"),
                'email' => $email,
                'username' => $username,
                'student_id' => $student->id,
                'password' => Hash::make($plainPassword),
            ]);

            $user->syncRoles(['student']);

            // Sync bio record
            BioModel::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'firstname' => $student->firstname,
                    'lastname' => $student->lastname,
                    'othernames' => $student->othername ?? '',
                    'phone' => $student->phone_number ?? '',
                    'address' => $student->home_address2 ?? '',
                    'gender' => $student->gender ?? '',
                    'maritalstatus' => '',
                    'nationality' => $student->nationality ?? '',
                    'dob' => $student->dateofbirth ?? '',
                ]
            );

            // Log creation
            PasswordResetHistory::create([
                'student_id' => $student->id,
                'action' => 'created',
                'performed_by' => auth()->id(),
                'new_password_hash' => Hash::make($plainPassword),
            ]);

            return [
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $email,
                    'username' => $username,
                    'password' => $plainPassword,
                    'admissionNo' => $student->admissionNo ?? '',
                    'class_name' => $student->class_name ?? '',
                    'action' => 'created',
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => "Failed to create user for {$student->firstname} {$student->lastname}: {$e->getMessage()}"
            ];
        }
    }

    // Helper method to reset user password
    private function resetUserPassword($user, $validated)
    {
        try {
            $student = DB::table('studentRegistration')->where('id', $user->student_id)->first();

            $plainPassword = $validated['password_type'] === 'same'
                ? $validated['shared_password']
                : strtoupper(Str::random(4)) . rand(100, 999) . strtolower(Str::random(3));

            $oldHash = $user->password;
            $user->password = Hash::make($plainPassword);
            $user->save();

            // Log password reset
            PasswordResetHistory::create([
                'student_id' => $user->student_id,
                'action' => 'reset',
                'performed_by' => auth()->id(),
                'old_password_hash' => $oldHash,
                'new_password_hash' => $user->password,
            ]);

            return [
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'password' => $plainPassword,
                    'admissionNo' => $student->admissionNo ?? '',
                    'class_name' => $student->class_name ?? '',
                    'action' => 'reset',
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => "Failed to reset password for {$user->name}: {$e->getMessage()}"
            ];
        }
    }

    // Helper method to get user credentials for reprint
    private function getUserCredentials($user, $student)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'password' => '********', // Don't show actual password for reprint
            'admissionNo' => $student->admissionNo ?? '',
            'class_name' => $student->class_name ?? '',
            'action' => 'reprint',
            'has_account' => true,
        ];
    }

    // ─────────────────────────────────────────────────────────────────
    // REVOKE student password (single or mass) - UPDATED to actually remove access
    // ─────────────────────────────────────────────────────────────────
    public function revokeStudentPassword(Request $request): JsonResponse
    {
        Log::debug("Revoking student password(s)", $request->all());

        try {
            if (!auth()->user()->hasPermissionTo('Update user')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient permissions.',
                ], 403);
            }

            $userIds = [];

            // Accept user_ids directly (single revoke from user list button)
            if ($request->has('user_ids')) {
                $request->validate([
                    'user_ids' => 'required|array|min:1',
                    'user_ids.*' => 'exists:users,id',
                ]);
                $userIds = $request->input('user_ids');
            }
            // Accept student_ids (mass revoke from mass modal)
            elseif ($request->has('student_ids')) {
                $request->validate([
                    'student_ids' => 'required|array|min:1',
                    'student_ids.*' => 'integer',
                ]);
                $userIds = User::whereIn('student_id', $request->input('student_ids'))
                    ->pluck('id')
                    ->toArray();
            }

            if (empty($userIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid users found to revoke.',
                ], 422);
            }

            $count = 0;
            $skipped = 0;
            $revoked = [];

            foreach ($userIds as $uid) {
                $user = User::with(['roles', 'student'])->find($uid);
                if (!$user) {
                    continue;
                }

                // Safety guard — only revoke for student-role users
                if (!$user->hasRole('student')) {
                    $skipped++;
                    Log::warning("revokeStudentPassword: skipped user {$uid} — not a student role");
                    continue;
                }

                // Instead of just changing password, we COMPLETELY REMOVE the user account
                // But keep the student record intact
                $studentId = $user->student_id;
                $userData = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'admissionNo' => $user->student?->admissionNo ?? '',
                ];

                // Delete the user account
                BioModel::where('user_id', $user->id)->delete();
                $user->roles()->detach();
                $user->delete();

                // Log the revocation
                PasswordResetHistory::create([
                    'student_id' => $studentId,
                    'action' => 'revoked',
                    'performed_by' => auth()->id(),
                    'old_password_hash' => $user->password ?? null,
                    'new_password_hash' => null,
                ]);

                $revoked[] = $userData;
                $count++;
            }

            $msg = "{$count} student account(s) revoked successfully. These students no longer have portal access.";
            if ($skipped) {
                $msg .= " ({$skipped} non-student user(s) skipped.)";
            }

            return response()->json([
                'success' => true,
                'message' => $msg,
                'count' => $count,
                'skipped' => $skipped,
                'revoked' => $revoked,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error("revokeStudentPassword error: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke accounts.',
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // SINGLE PRINT CREDENTIALS (for existing student users)
    // ─────────────────────────────────────────────────────────────────
    public function printCredentials($id): JsonResponse
    {
        try {
            $user = User::with(['student'])->findOrFail($id);

            if (!$user->hasRole('student')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only student accounts can print credentials.',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'credentials' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'admissionNo' => $user->student->admissionNo ?? '',
                    'class_name' => $user->student->class_name ?? '',
                    'password' => '********', // Don't reveal password on reprint
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve credentials.',
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // BULK PRINT ALL STUDENT CREDENTIALS
    // ─────────────────────────────────────────────────────────────────
    public function bulkPrintCredentials(Request $request): JsonResponse
    {
        try {
            $students = User::role('student')
                ->with(['student'])
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'username' => $user->username,
                        'admissionNo' => $user->student->admissionNo ?? '',
                        'class_name' => $user->student->class_name ?? '',
                        'password' => '********',
                    ];
                });

            return response()->json([
                'success' => true,
                'students' => $students,
                'count' => $students->count(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve credentials.',
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // GET students list for modals (UPDATED with has_account flag)
    // ─────────────────────────────────────────────────────────────────
    public function getStudents(Request $request): JsonResponse
    {
        try {
            $search = trim($request->get('search', ''));
            $limit = min((int) $request->get('limit', 300), 2000);
            $classId = $request->get('class_id');
            $armId = $request->get('arm_id');

            $query = DB::table('studentRegistration as sr')
                ->leftJoin('studentclass as sc', function ($join) {
                    $join->on('sc.studentId', '=', 'sr.id')
                        ->whereRaw('sc.id = (
                            SELECT id FROM studentclass
                            WHERE studentId = sr.id
                            ORDER BY id DESC LIMIT 1
                        )');
                })
                ->leftJoin('schoolclass as cls', 'cls.id', '=', 'sc.schoolclassid')
                ->leftJoin('schoolarm as arm', 'arm.id', '=', 'cls.arm')
                ->leftJoin('users as u', 'u.student_id', '=', 'sr.id')
                ->whereNotNull('sr.admissionNo')
                ->select(
                    'sr.id',
                    'sr.admissionNo',
                    'sr.firstname',
                    'sr.lastname',
                    'sr.email',
                    'cls.id as class_id',
                    'cls.schoolclass as class_name',
                    'arm.id as arm_id',
                    'arm.arm as arm_name',
                    DB::raw('CASE WHEN u.id IS NOT NULL THEN 1 ELSE 0 END as has_account'),
                    DB::raw('u.id as user_id') // Include user_id if exists
                );

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('sr.admissionNo', 'like', "%{$search}%")
                        ->orWhere('sr.firstname', 'like', "%{$search}%")
                        ->orWhere('sr.lastname', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(sr.firstname, ' ', sr.lastname) LIKE ?", ["%{$search}%"]);
                });
            }

            if ($classId) {
                $query->where('cls.id', $classId);
            }

            if ($armId) {
                $query->where('arm.id', $armId);
            }

            $students = $query
                ->orderBy('sr.lastname')
                ->orderBy('sr.firstname')
                ->limit($limit)
                ->get();

            // Dropdown data for filter selects
            $classes = DB::table('schoolclass as cls')
                ->leftJoin('schoolarm as arm', 'arm.id', '=', 'cls.arm')
                ->select('cls.id', 'cls.schoolclass as name', 'arm.id as arm_id', 'arm.arm as arm_name')
                ->orderBy('cls.schoolclass')
                ->get();

            $arms = DB::table('schoolarm')
                ->select('id', 'arm as name')
                ->orderBy('arm')
                ->get();

            return response()->json([
                'success' => true,
                'students' => $students->map(fn($s) => [
                    'id' => $s->id,
                    'admissionNo' => $s->admissionNo,
                    'name' => trim("{$s->firstname} {$s->lastname}"),
                    'email' => $s->email ?? '',
                    'class_id' => $s->class_id,
                    'class_name' => $s->class_name ?? '',
                    'arm_id' => $s->arm_id,
                    'arm_name' => $s->arm_name ?? '',
                    'has_account' => (bool) $s->has_account,
                    'user_id' => $s->user_id,
                ])->values()->toArray(),
                'classes' => $classes,
                'arms' => $arms,
            ]);

        } catch (\Exception $e) {
            Log::error("getStudents error: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to load students',
            ], 500);
        }
    }
}
