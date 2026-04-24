<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BioModel;
use App\Models\Student;
use App\Models\Studentpicture;
use App\Models\User;
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

    public function create(): View
    {
        $title = "Create User";
        $roles = Role::pluck('name', 'name')->all();
        return view('users.create', compact('roles', 'title'));
    }

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
                'name'         => 'required|string|max:255',
                'email'        => 'required|email|unique:users,email',
                'password'     => 'required|min:8|confirmed',
                'roles'        => 'required|array',
                'roles.*'      => 'exists:roles,name',
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
                'user'    => [
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'email'        => $user->email,
                    'roles'        => $user->roles->pluck('name')->toArray(),
                    'phone_number' => $user->phone_number,
                    'password'     => $plainPassword,
                ],
            ], 201);

        } catch (ValidationException $e) {
            Log::error("Validation error creating user: " . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error("Create user error: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
            ], 500);
        }
    }

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

        $userbio        = $user->bio;
        $staffInfo      = $user->staffemploymentDetails;
        $staffPicture   = $user->staffPicture;
        $studentPicture = null;

        if ($user->isStudent() && $user->student_id) {
            $studentPicture = Studentpicture::where('studentid', $user->student_id)->first();
        }

        $isStudentUser = $user->hasRole('student');
        $studentData   = $user->student;
        $currentClass  = null;
        $parentData    = null;

        if ($isStudentUser && $studentData) {
            $currentClass = $studentData->currentClass;
            $parentData   = $studentData->parent;
        }

        return view('users.overview', compact(
            'user', 'userbio', 'staffInfo', 'staffPicture', 'studentPicture',
            'pagetitle', 'isStudentUser', 'studentData', 'currentClass', 'parentData'
        ));
    }

    public function edit($id): View
    {
        if (auth()->user()->hasRole('student')) {
            abort(403, 'Students are not allowed to edit profiles.');
        }

        $user     = User::findOrFail($id);
        $roles    = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name', 'name')->all();

        return view('users.edit', compact('user', 'roles', 'userRole'));
    }

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
                'name'         => 'required|string|max:255',
                'email'        => 'required|email|unique:users,email,' . $id,
                'password'     => 'nullable|min:8|confirmed',
                'roles'        => 'required|array',
                'roles.*'      => 'exists:roles,name',
                'phone_number' => 'nullable|string|regex:/^\+[1-9]\d{1,14}$/',
            ]);

            $input         = $request->all();
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
                'user'    => [
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'email'        => $user->email,
                    'roles'        => $user->roles->pluck('name')->toArray(),
                    'phone_number' => $user->phone_number,
                    'password'     => $plainPassword,
                ],
            ], 200);

        } catch (ValidationException $e) {
            Log::error("Validation error updating user: " . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error("Update user error: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        Log::debug("Attempting to delete user ID: {$id}");

        try {
            $user      = User::findOrFail($id);
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

    public function roles(): JsonResponse
    {
        $roles = Role::pluck('name')->all();
        return response()->json(['roles' => $roles]);
    }

    // ─────────────────────────────────────────────────────────────────
    // SINGLE student user creation (existing)
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
                'name'       => 'required|string|max:255',
                'email'      => 'required|email|unique:users,email',
                'username'   => 'required|string|unique:users,username|max:255',
                'password'   => 'required|min:8|confirmed',
                'roles'      => 'required|array|min:1',
                'roles.*'    => 'exists:roles,name',
            ]);

            $input             = $request->all();
            $input['username'] = str_replace('/', '_', $input['username']);
            $plainPassword     = $input['password'];
            $input['password'] = Hash::make($input['password']);

            $user = User::create($input);
            $user->syncRoles($request->input('roles'));

            $student = Student::findOrFail($validated['student_id']);

            BioModel::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'firstname'     => $student->firstname,
                    'lastname'      => $student->lastname,
                    'othernames'    => $student->othername ?? '',
                    'phone'         => $student->phone_number ?? '',
                    'address'       => $student->home_address2 ?? '',
                    'gender'        => $student->gender ?? '',
                    'maritalstatus' => '',
                    'nationality'   => $student->nationality ?? '',
                    'dob'           => $student->dateofbirth ?? '',
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Student user created successfully',
                'user'    => [
                    'id'       => $user->id,
                    'name'     => $user->name,
                    'email'    => $user->email,
                    'username' => $user->username,
                    'roles'    => $user->roles->pluck('name')->toArray(),
                    'password' => $plainPassword,
                ],
            ], 201);

        } catch (ValidationException $e) {
            Log::error("Validation error creating student user: " . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
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
    // MASS student user creation (NEW)
    // ─────────────────────────────────────────────────────────────────
    public function massCreateStudents(Request $request): JsonResponse
    {
        Log::debug("Mass creating student users", ['count' => count($request->input('students', []))]);

        try {
            if (!auth()->user()->hasPermissionTo('Create user')) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have the right permissions',
                ], 403);
            }

            $validated = $request->validate([
                'students'              => 'required|array|min:1',
                'students.*.student_id' => 'required|exists:studentRegistration,id',
                'roles'                 => 'required|array|min:1',
                'roles.*'               => 'exists:roles,name',
                'password_type'         => 'required|in:same,individual',
                'shared_password'       => 'required_if:password_type,same|nullable|string|min:6',
            ]);

            $created = [];
            $skipped = [];
            $errors  = [];

            $isStudentRole = in_array('student', $validated['roles']);

            DB::beginTransaction();

            foreach ($validated['students'] as $entry) {
                $studentId = $entry['student_id'];

                $student = DB::table('studentRegistration')->where('id', $studentId)->first();

                if (!$student) {
                    $errors[] = "Student ID {$studentId} not found.";
                    continue;
                }

                // Skip if user already linked to this student record
                if (User::where('student_id', $studentId)->exists()) {
                    $skipped[] = trim("{$student->firstname} {$student->lastname}");
                    continue;
                }

                // Build email
                $email = !empty($student->email) ? trim($student->email) : null;

                if (!$email || User::where('email', $email)->exists()) {
                    $base  = strtolower(
                        Str::ascii(trim($student->firstname)) . '.' .
                        Str::ascii(trim($student->lastname))
                    );
                    $email = $base . '@student.school';

                    if (User::where('email', $email)->exists()) {
                        $email = $base . '.' . $studentId . '@student.school';
                    }
                }

                // Build username from admission number
                $baseUsername = str_replace(['/', '\\', ' '], '_', $student->admissionNo ?? "stu_{$studentId}");
                $username     = $baseUsername;
                $suffix       = 1;
                while (User::where('username', $username)->exists()) {
                    $username = $baseUsername . '_' . $suffix++;
                }

                // Password
                $plainPassword = $validated['password_type'] === 'same'
                    ? $validated['shared_password']
                    : strtoupper(Str::random(4)) . rand(100, 999) . strtolower(Str::random(3));

                // Create user
                $user = User::create([
                    'name'       => trim("{$student->firstname} {$student->lastname}"),
                    'email'      => $email,
                    'username'   => $username,
                    'student_id' => $studentId,
                    'password'   => Hash::make($plainPassword),
                ]);

                $user->syncRoles($validated['roles']);

                // Sync bio record
                BioModel::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'firstname'     => $student->firstname,
                        'lastname'      => $student->lastname,
                        'othernames'    => $student->othername ?? '',
                        'phone'         => $student->phone_number ?? '',
                        'address'       => $student->home_address2 ?? '',
                        'gender'        => $student->gender ?? '',
                        'maritalstatus' => '',
                        'nationality'   => $student->nationality ?? '',
                        'dob'           => $student->dateofbirth ?? '',
                    ]
                );

                $created[] = [
                    'id'          => $user->id,
                    'name'        => $user->name,
                    'email'       => $email,
                    'username'    => $username,
                    'password'    => $plainPassword,  // plain — returned once for printing only
                    'admissionNo' => $student->admissionNo ?? '',
                    'class_name'  => $student->class_name ?? '',
                ];
            }

            DB::commit();

            // Sort alphabetically by name for the printout
            usort($created, fn ($a, $b) => strcmp($a['name'], $b['name']));

            return response()->json([
                'success'       => true,
                'message'       => count($created) . ' account(s) created successfully.'
                    . (count($skipped) ? ' ' . count($skipped) . ' skipped (already have accounts).' : ''),
                'created'       => $created,
                'skipped'       => $skipped,
                'errors'        => $errors,
                'created_count' => count($created),
                'skipped_count' => count($skipped),
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error("Validation error mass creating students: " . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Mass create students error: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to create accounts: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Get students list for modals (updated — includes has_account + class_name)
    // ─────────────────────────────────────────────────────────────────
    public function getStudents(Request $request): JsonResponse
    {
        try {
            $search = trim($request->get('search', ''));
            $limit  = min((int) $request->get('limit', 300), 2000);

            $query = DB::table('studentRegistration as sr')
                ->leftJoin('studentclass as sc', function ($join) {
                    // Most recent class record per student
                    $join->on('sc.studentId', '=', 'sr.id')
                         ->whereRaw('sc.id = (
                             SELECT id FROM studentclass
                             WHERE studentId = sr.id
                             ORDER BY id DESC LIMIT 1
                         )');
                })
                ->leftJoin('schoolclass as cls', 'cls.id', '=', 'sc.schoolclassid')
                ->leftJoin('users as u', 'u.student_id', '=', 'sr.id')
                ->whereNotNull('sr.admissionNo')
                ->select(
                    'sr.id',
                    'sr.admissionNo',
                    'sr.firstname',
                    'sr.lastname',
                    'sr.email',
                    'cls.schoolclass as class_name',
                    DB::raw('CASE WHEN u.id IS NOT NULL THEN 1 ELSE 0 END as has_account')
                );

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('sr.admissionNo', 'like', "%{$search}%")
                      ->orWhere('sr.firstname',  'like', "%{$search}%")
                      ->orWhere('sr.lastname',   'like', "%{$search}%")
                      ->orWhereRaw("CONCAT(sr.firstname, ' ', sr.lastname) LIKE ?", ["%{$search}%"]);
                });
            }

            $students = $query
                ->orderBy('sr.lastname')
                ->orderBy('sr.firstname')
                ->limit($limit)
                ->get();

            return response()->json([
                'success'  => true,
                'students' => $students->map(fn ($s) => [
                    'id'          => $s->id,
                    'admissionNo' => $s->admissionNo,
                    'name'        => trim("{$s->firstname} {$s->lastname}"),
                    'email'       => $s->email ?? '',
                    'class_name'  => $s->class_name ?? '',
                    'has_account' => (bool) $s->has_account,
                ])->values()->toArray(),
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
