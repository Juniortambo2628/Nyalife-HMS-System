<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Department;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use App\Traits\HasBulkActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class UserController extends Controller
{
    use HasBulkActions;

    public function index(Request $request)
    {
        $sort = $request->sort ?? 'created_at';
        $direction = $request->direction ?? 'desc';

        $query = User::whereHas('roleRelation', fn ($r) => $r->where('role_name', '!=', 'patient'))
            ->with(['roleRelation', 'staff.departmentRelation'])
            ->search($request->search)
            ->when($request->role, fn ($q) => $q->whereHas('roleRelation', fn ($r) => $r->where('role_name', $request->role)))
            ->when($request->quick_filter, function ($q, $filter) {
                switch ($filter) {
                    case 'active':
                        return $q->where('is_active', true);
                    case 'inactive':
                        return $q->where('is_active', false);
                    case 'admin':
                        return $q->whereHas('roleRelation', fn ($r) => $r->where('role_name', 'admin'));
                    case 'doctor':
                        return $q->whereHas('roleRelation', fn ($r) => $r->where('role_name', 'doctor'));
                    case 'nurse':
                        return $q->whereHas('roleRelation', fn ($r) => $r->where('role_name', 'nurse'));
                }
            })
            ->orderBy($sort, $direction);

        $users = $query->paginate(12)->withQueryString();
        $users->through(fn ($user) => (new UserResource($user))->resolve());

        $stats = [
            'total' => User::whereHas('roleRelation', fn ($r) => $r->where('role_name', '!=', 'patient'))->count(),
            'active' => User::whereHas('roleRelation', fn ($r) => $r->where('role_name', '!=', 'patient'))->where('is_active', true)->count(),
            'inactive' => User::whereHas('roleRelation', fn ($r) => $r->where('role_name', '!=', 'patient'))->where('is_active', false)->count(),
        ];

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => (object) $request->only(['search', 'role', 'sort', 'direction', 'quick_filter']),
            'roles' => Role::where('role_name', '!=', 'patient')->get(),
            'stats' => $stats,
        ]);
    }

    public function create()
    {
        return Inertia::render('Users/Create', [
            'roles' => Role::all(),
            'departments' => Department::all(),
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $username = $validated['username'] ?? strtolower(
            Str::slug($validated['first_name'].'.'.$validated['last_name'])
            .'.'.substr(uniqid(), -4)
        );

        $roleId = $validated['role_id']
            ?? (isset($validated['role']) ? Role::where('role_name', $validated['role'])->first()?->role_id : null)
            ?? Role::where('role_name', 'patient')->first()?->role_id;

        $password = ! empty($validated['password'])
            ? Hash::make($validated['password'])
            : Hash::make(Str::random(12));

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'username' => $username,
            'password' => $password,
            'role_id' => $roleId,
            'is_active' => true,
        ]);

        $roleName = $validated['role'] ?? Role::find($roleId)?->role_name ?? 'patient';
        if (\Spatie\Permission\Models\Role::where('name', $roleName)->where('guard_name', 'web')->exists()) {
            $user->assignRole($roleName);
        }

        if ($roleName !== 'patient') {
            $deptId = $request->input('department_id');
            $departmentName = null;
            if ($deptId) {
                $departmentName = Department::where('department_id', $deptId)->value('department_name');
            }
            Staff::create([
                'user_id' => $user->user_id,
                'department_id' => $deptId,
                'department' => $departmentName,
                'employee_id' => strtoupper($user->username).'-001',
                'join_date' => now()->toDateString(),
            ]);
        }

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function show($id)
    {
        return Inertia::render('Users/Show', [
            'user' => UserResource::make(User::with('roleRelation')->findOrFail($id)),
        ]);
    }

    public function edit($id)
    {
        $user = User::with('staff')->findOrFail($id);

        return Inertia::render('Users/Edit', [
            'user' => UserResource::make($user),
            'roles' => Role::all(),
            'departments' => Department::all(),
        ]);
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validated();

        if (isset($validated['role'])) {
            $role = Role::where('role_name', $validated['role'])->first();
            if ($role) {
                $validated['role_id'] = $role->role_id;

                // Sync Spatie roles if applicable
                if (\Spatie\Permission\Models\Role::where('name', $role->role_name)->where('guard_name', 'web')->exists()) {
                    $user->syncRoles([$role->role_name]);
                }
            }
        }

        $user->update($validated);

        $roleName = $user->role;
        if ($roleName === 'patient') {
            Staff::where('user_id', $user->user_id)->delete();
        } else {
            $deptId = $request->input('department_id');
            $departmentName = null;
            if ($deptId) {
                $departmentName = Department::where('department_id', $deptId)->value('department_name');
            }
            Staff::updateOrCreate(
                ['user_id' => $user->user_id],
                [
                    'department_id' => $deptId,
                    'department' => $departmentName,
                    'employee_id' => $user->staff?->employee_id ?? (strtoupper($user->username).'-001'),
                    'join_date' => $user->staff?->join_date ?? now()->toDateString(),
                ]
            );
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Handle bulk actions on users.
     */
    protected function bulkActionMap(): array
    {
        return [
            'activate' => function (array $ids, int $count) {
                User::whereIn('user_id', $ids)->update(['is_active' => true]);

                return redirect()->back()->with('success', "{$count} user(s) activated.");
            },
            'deactivate' => function (array $ids, int $count) {
                $ids = array_diff($ids, [Auth::id()]);
                $count = count($ids);
                if ($count > 0) {
                    User::whereIn('user_id', $ids)->update(['is_active' => false]);
                }

                return redirect()->back()->with('success', "{$count} user(s) deactivated.");
            },
            'delete' => function (array $ids, int $count) {
                $ids = array_diff($ids, [Auth::id()]);
                $count = count($ids);
                if ($count > 0) {
                    User::whereIn('user_id', $ids)->delete();
                }

                return redirect()->back()->with('success', "{$count} user(s) deleted.");
            },
        ];
    }
}
