<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BioModel;
use App\Models\Student;
use App\Models\Studentpicture;
use App\Models\User;
use App\Models\PasswordResetLog; // You'll need to create this model
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
            $user->roles()->detach();
            $user->delete();

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

            // Log password reset
            $this->logPasswordReset($user->id, $plainPassword, 'created');

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
    // MASS student user creation (ENHANCED - allows reprint, reset, revoke)
    // ─────────────────────────────────────────────────────────────────
    public function massCreateStudents(Request $request): JsonResponse
    {
        Log::debug("Mass creating/resetting student users", ['count' => count($request->input('students', []))]);

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
                'students.*.action' => 'sometimes|in:create,reset,revoke,reprint',
                'roles' => 'required_if:action_type,create,reset|array|min:1',
                'roles.*' => 'exists:roles,name',
                'password_type' => 'required_if:action_type,create,reset|in:same,individual',
                'shared_password' => 'required_if:password_type,same|nullable|string|min:6',
                'action_type' => 'required|in:create,reset,revoke,reprint',
                'reprint_only' => 'sometimes|boolean',
            ]);

            DB::beginTransaction();

            $created = [];
            $reset = [];
            $revoked = [];
            $reprinted = [];
            $skipped = [];
            $errors = [];

            foreach ($validated['students'] as $entry) {
                $studentId = $entry['student_id'];
                $action = $entry['action'] ?? $validated['action_type'];

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
                            $skipped[] = trim("{$student->firstname} {$student->lastname} (already has account)");
                            continue 2;
                        }
                        $result = $this->createStudentUser($student, $validated);
                        if ($result['success']) {
                            $created[] = $result['data'];
                        } else {
                            $errors[] = $result['error'];
                        }
                        break;

                    case 'reset':
                        if (!$existingUser) {
                            $skipped[] = trim("{$student->firstname} {$student->lastname} (no account to reset)");
                            continue 2;
                        }
                        $result = $this->resetStudentPassword($existingUser, $student, $validated);
                        if ($result['success']) {
                            $reset[] = $result['data'];
                        } else {
                            $errors[] = $result['error'];
                        }
                        break;

                    case 'revoke':
                        if (!$existingUser) {
                            $skipped[] = trim("{$student->firstname} {$student->lastname} (no account to revoke)");
                            continue 2;
                        }
                        $result = $this->revokeStudentAccount($existingUser);
                        if ($result['success']) {
                            $revoked[] = $result['data'];
                        } else {
                            $errors[] = $result['error'];
                        }
                        break;

                    case 'reprint':
                        if (!$existingUser) {
                            $skipped[] = trim("{$student->firstname} {$student->lastname} (no account to reprint)");
                            continue 2;
                        }
                        $result = $this->getStudentCredentials($existingUser, $student);
                        if ($result['success']) {
                            $reprinted[] = $result['data'];
                        } else {
                            $errors[] = $result['error'];
                        }
                        break;
                }
            }

            DB::commit();

            // Prepare response based on action type
            $responseData = [
                'success' => true,
                'action_type' => $validated['action_type'],
                'created' => $created,
                'reset' => $reset,
                'revoked' => $revoked,
                'reprinted' => $reprinted,
                'skipped' => $skipped,
                'errors' => $errors,
                'created_count' => count($created),
                'reset_count' => count($reset),
                'revoked_count' => count($revoked),
                'reprinted_count' => count($reprinted),
            ];

            $message = $this->buildActionMessage($responseData);
            $responseData['message'] = $message;

            return response()->json($responseData, 201);

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

    // ─────────────────────────────────────────────────────────────────
    // Helper: Create new student user
    // ─────────────────────────────────────────────────────────────────
    private function createStudentUser($student, $validated)
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

            // Build username
            $baseUsername = str_replace(['/', '\\', ' '], '_', $student->admissionNo ?? "stu_{$student->id}");
            $username = $baseUsername;
            $suffix = 1;
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . '_' . $suffix++;
            }

            // Password
            $plainPassword = $validated['password_type'] === 'same'
                ? $validated['shared_password']
                : $this->generateRandomPassword();

            // Create user
            $user = User::create([
                'name' => trim("{$student->firstname} {$student->lastname}"),
                'email' => $email,
                'username' => $username,
                'student_id' => $student->id,
                'password' => Hash::make($plainPassword),
            ]);

            $user->syncRoles($validated['roles']);

            // Sync bio
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
            $this->logPasswordReset($user->id, $plainPassword, 'created');

            return [
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'student_id' => $student->id,
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
                'error' => "Failed to create account for {$student->firstname} {$student->lastname}: " . $e->getMessage()
            ];
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Helper: Reset student password
    // ─────────────────────────────────────────────────────────────────
    private function resetStudentPassword($user, $student, $validated)
    {
        try {
            $plainPassword = $validated['password_type'] === 'same'
                ? $validated['shared_password']
                : $this->generateRandomPassword();

            $user->update(['password' => Hash::make($plainPassword)]);

            // Sync roles if provided
            if (isset($validated['roles'])) {
                $user->syncRoles($validated['roles']);
            }

            // Log reset
            $this->logPasswordReset($user->id, $plainPassword, 'reset');

            return [
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'student_id' => $student->id,
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
                'error' => "Failed to reset password for {$student->firstname} {$student->lastname}: " . $e->getMessage()
            ];
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Helper: Revoke student account (remove user, keep student record)
    // ─────────────────────────────────────────────────────────────────
    private function revokeStudentAccount($user)
    {
        try {
            $studentId = $user->student_id;
            $student = DB::table('studentRegistration')->where('id', $studentId)->first();
            $name = $user->name;

            // Log before deletion
            Log::info("Revoking student account: {$name} (ID: {$user->id})");

            // Detach roles and delete user
            $user->roles()->detach();
            $user->delete();

            return [
                'success' => true,
                'data' => [
                    'student_id' => $studentId,
                    'name' => $name,
                    'admissionNo' => $student->admissionNo ?? '',
                    'class_name' => $student->class_name ?? '',
                    'action' => 'revoked',
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => "Failed to revoke account: " . $e->getMessage()
            ];
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Helper: Get student credentials for reprint
    // ─────────────────────────────────────────────────────────────────
    private function getStudentCredentials($user, $student)
    {
        try {
            // Note: We can't retrieve the original password, so we indicate this
            return [
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'student_id' => $student->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'password' => '********', // Password not retrievable for security
                    'admissionNo' => $student->admissionNo ?? '',
                    'class_name' => $student->class_name ?? '',
                    'action' => 'reprinted',
                    'note' => 'Password not shown for security. Use Reset action to set new password.',
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => "Failed to retrieve credentials for {$student->firstname} {$student->lastname}"
            ];
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Helper: Generate random password
    // ─────────────────────────────────────────────────────────────────
    private function generateRandomPassword()
    {
        return strtoupper(Str::random(4)) . rand(100, 999) . strtolower(Str::random(3));
    }

    // ─────────────────────────────────────────────────────────────────
    // Helper: Log password reset/creation
    // ─────────────────────────────────────────────────────────────────
    private function logPasswordReset($userId, $password, $action)
    {
        // Optional: Create password_reset_logs table to track changes
        // For now, just log to Laravel log
        Log::info("Password {$action} for user ID {$userId}", [
            'action' => $action,
            'user_id' => $userId,
            'timestamp' => now(),
            'reset_by' => auth()->id(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // Helper: Build action message
    // ─────────────────────────────────────────────────────────────────
    private function buildActionMessage($data)
    {
        $parts = [];
        if ($data['created_count'] > 0) {
            $parts[] = $data['created_count'] . ' account(s) created';
        }
        if ($data['reset_count'] > 0) {
            $parts[] = $data['reset_count'] . ' password(s) reset';
        }
        if ($data['revoked_count'] > 0) {
            $parts[] = $data['revoked_count'] . ' account(s) revoked';
        }
        if ($data['reprinted_count'] > 0) {
            $parts[] = $data['reprinted_count'] . ' credential(s) reprinted';
        }

        $message = implode(', ', $parts) . ' successfully.';

        if (count($data['skipped']) > 0) {
            $message .= ' ' . count($data['skipped']) . ' skipped.';
        }

        return $message;
    }

    // ─────────────────────────────────────────────────────────────────
    // REVOKE student password (simplified - now integrated into mass operation)
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

            if ($request->has('user_ids')) {
                $request->validate([
                    'user_ids' => 'required|array|min:1',
                    'user_ids.*' => 'exists:users,id',
                ]);
                $userIds = $request->input('user_ids');
            } elseif ($request->has('student_ids')) {
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

            $plainPassword = 'ChangeMe@123';
            $newPassword = Hash::make($plainPassword);
            $count = 0;
            $skipped = 0;
            $revoked = [];

            foreach ($userIds as $uid) {
                $user = User::with(['roles', 'student'])->find($uid);
                if (!$user) {
                    continue;
                }

                if (!$user->hasRole('student')) {
                    $skipped++;
                    continue;
                }

                $user->update(['password' => $newPassword]);
                $this->logPasswordReset($user->id, $plainPassword, 'revoked');
                $count++;

                $revoked[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'admissionNo' => $user->student?->admissionNo ?? '',
                    'password' => $plainPassword,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => "{$count} student password(s) reset. New password: {$plainPassword}",
                'count' => $count,
                'skipped' => $skipped,
                'revoked' => $revoked,
            ]);

        } catch (\Exception $e) {
            Log::error("revokeStudentPassword error: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke passwords.',
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Single student password reset
    // ─────────────────────────────────────────────────────────────────
    public function resetSingleStudentPassword(Request $request, $id): JsonResponse
    {
        try {
            if (!auth()->user()->hasPermissionTo('Update user')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient permissions.',
                ], 403);
            }

            $user = User::findOrFail($id);

            if (!$user->hasRole('student')) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not a student.',
                ], 422);
            }

            $plainPassword = $this->generateRandomPassword();
            $user->update(['password' => Hash::make($plainPassword)]);
            $this->logPasswordReset($user->id, $plainPassword, 'reset');

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully',
                'password' => $plainPassword,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error("Reset password error: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset password.',
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // GET students list (ENHANCED - includes has_account flag)
    // ─────────────────────────────────────────────────────────────────
    public function getStudents(Request $request): JsonResponse
    {
        try {
            $search = trim($request->get('search', ''));
            $limit = min((int) $request->get('limit', 2000), 5000);
            $classId = $request->get('class_id');
            $armId = $request->get('arm_id');
            $hasAccount = $request->get('has_account'); // 'yes', 'no', or 'all'

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
                    'sr.phone_number',
                    'cls.id as class_id',
                    'cls.schoolclass as class_name',
                    'arm.id as arm_id',
                    'arm.arm as arm_name',
                    DB::raw('CASE WHEN u.id IS NOT NULL THEN 1 ELSE 0 END as has_account'),
                    DB::raw('u.id as user_id'),
                    DB::raw('u.username as username'),
                    DB::raw('u.created_at as account_created_at')
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

            if ($hasAccount === 'yes') {
                $query->whereNotNull('u.id');
            } elseif ($hasAccount === 'no') {
                $query->whereNull('u.id');
            }

            $students = $query
                ->orderBy('sr.lastname')
                ->orderBy('sr.firstname')
                ->limit($limit)
                ->get();

            // Get filter data
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
                'students' => $students->map(fn ($s) => [
                    'id' => $s->id,
                    'admissionNo' => $s->admissionNo,
                    'name' => trim("{$s->firstname} {$s->lastname}"),
                    'firstname' => $s->firstname,
                    'lastname' => $s->lastname,
                    'email' => $s->email ?? '',
                    'phone_number' => $s->phone_number ?? '',
                    'class_id' => $s->class_id,
                    'class_name' => $s->class_name ?? '',
                    'arm_id' => $s->arm_id,
                    'arm_name' => $s->arm_name ?? '',
                    'has_account' => (bool) $s->has_account,
                    'user_id' => $s->user_id,
                    'username' => $s->username,
                    'account_created_at' => $s->account_created_at,
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

    // ─────────────────────────────────────────────────────────────────
    // Bulk reprint credentials for selected students
    // ─────────────────────────────────────────────────────────────────
    public function bulkReprintCredentials(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'student_ids' => 'required|array|min:1',
                'student_ids.*' => 'exists:studentRegistration,id',
            ]);

            $students = DB::table('studentRegistration')
                ->whereIn('id', $request->input('student_ids'))
                ->get();

            $credentials = [];
            $skipped = [];

            foreach ($students as $student) {
                $user = User::where('student_id', $student->id)->first();

                if (!$user) {
                    $skipped[] = trim("{$student->firstname} {$student->lastname}");
                    continue;
                }

                $credentials[] = [
                    'student_id' => $student->id,
                    'name' => trim("{$student->firstname} {$student->lastname}"),
                    'email' => $user->email,
                    'username' => $user->username,
                    'admissionNo' => $student->admissionNo ?? '',
                    'class_name' => $student->class_name ?? '',
                    'note' => 'Password not shown - use Reset Password to set new one',
                ];
            }

            return response()->json([
                'success' => true,
                'credentials' => $credentials,
                'skipped' => $skipped,
                'count' => count($credentials),
            ]);

        } catch (\Exception $e) {
            Log::error("Bulk reprint error: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve credentials',
            ], 500);
        }
    }
}
