<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->sort ?? 'created_at';
        $direction = $request->direction ?? 'desc';

        $query = User::with('roleRelation')
            ->search($request->search)
            ->when($request->role, fn ($q) => $q->whereHas('roleRelation', fn ($r) => $r->where('role_name', $request->role)))
            ->orderBy($sort, $direction);

        $users = $query->paginate(12)->withQueryString();
        $users->through(fn ($user) => (new UserResource($user))->resolve());

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => (object) $request->only(['search', 'role', 'sort', 'direction']),
            'roles' => Role::all()
        ]);
    }

    public function create()
    {
        return Inertia::render('Users/Create', [
            'roles' => Role::all()
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $username = $validated['username'] ?? strtolower(
            \Illuminate\Support\Str::slug($validated['first_name'] . '.' . $validated['last_name'])
            . '.' . substr(uniqid(), -4)
        );

        $roleId = $validated['role_id']
            ?? (isset($validated['role']) ? Role::where('role_name', $validated['role'])->first()?->role_id : null)
            ?? Role::where('role_name', 'patient')->first()?->role_id;

        $password = ! empty($validated['password'])
            ? Hash::make($validated['password'])
            : Hash::make(\Illuminate\Support\Str::random(12));

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

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function show($id)
    {
        return Inertia::render('Users/Show', [
            'user' => UserResource::make(User::with('roleRelation')->findOrFail($id))
        ]);
    }

    public function edit($id)
    {
        return Inertia::render('Users/Edit', [
            'user' => UserResource::make(User::findOrFail($id)),
            'roles' => Role::all()
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

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
