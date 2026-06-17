<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use App\Services\ActivityLogger;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    private function authorizeAdmin(): void
    {
        $this->requirePermission(Permissions::MANAGE_DEPARTMENTS);
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $departments = Department::withCount('staffMembers')
            ->filteredQuery($request)
            ->orderBy('department_name')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Department::count(),
            'active' => Department::where('is_active', true)->count(),
            'clinical' => Department::where('type', 'clinical')->count(),
        ];

        return Inertia::render('Departments/Index', [
            'departments' => DepartmentResource::collection($departments),
            'filters' => $request->only(['search', 'type', 'status']),
            'stats' => $stats,
            'departmentTypes' => Department::TYPES,
        ]);
    }

    public function create()
    {
        $this->authorizeAdmin();

        $staffUsers = \App\Models\User::whereHas('roleRelation', function ($q) {
            $q->where('role_name', '!=', 'patient');
        })->with('staff')->get()->map(function ($user) {
            return [
                'user_id' => $user->user_id,
                'name' => trim($user->first_name . ' ' . $user->last_name),
                'role' => $user->role,
                'department_id' => $user->staff?->department_id,
            ];
        });

        return Inertia::render('Departments/Form', [
            'department' => null,
            'departmentTypes' => Department::TYPES,
            'staffUsers' => $staffUsers,
        ]);
    }

    public function store(StoreDepartmentRequest $request)
    {
        $validated = $request->validated();

        $department = Department::create([
            ...$validated,
            'type' => $validated['type'] ?? 'clinical',
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $assignedUserIds = $request->input('assigned_user_ids', []);
        foreach ($assignedUserIds as $userId) {
            $user = \App\Models\User::find($userId);
            if ($user) {
                \App\Models\Staff::updateOrCreate(
                    ['user_id' => $userId],
                    [
                        'department_id' => $department->department_id,
                        'department' => $department->department_name,
                        'employee_id' => $user->staff?->employee_id ?? (strtoupper($user->username) . '-001'),
                        'join_date' => $user->staff?->join_date ?? now()->toDateString(),
                    ]
                );
            }
        }

        ActivityLogger::log(
            'admin',
            "Department \"{$department->department_name}\" created",
            ['department_id' => $department->department_id],
            Auth::user(),
            $department
        );

        return redirect()->route('departments.index')->with('success', 'Department created successfully.');
    }

    public function show($id)
    {
        $this->authorizeAdmin();

        $department = Department::with(['staffMembers.user'])
            ->withCount('staffMembers')
            ->findOrFail($id);

        return Inertia::render('Departments/Show', [
            'department' => DepartmentResource::make($department),
        ]);
    }

    public function edit($id)
    {
        $this->authorizeAdmin();

        $department = Department::with('staffMembers.user')->findOrFail($id);

        $staffUsers = \App\Models\User::whereHas('roleRelation', function ($q) {
            $q->where('role_name', '!=', 'patient');
        })->with('staff')->get()->map(function ($user) {
            return [
                'user_id' => $user->user_id,
                'name' => trim($user->first_name . ' ' . $user->last_name),
                'role' => $user->role,
                'department_id' => $user->staff?->department_id,
            ];
        });

        return Inertia::render('Departments/Form', [
            'department' => DepartmentResource::make($department),
            'departmentTypes' => Department::TYPES,
            'staffUsers' => $staffUsers,
        ]);
    }

    public function update(UpdateDepartmentRequest $request, $id)
    {
        $department = Department::findOrFail($id);
        $validated = $request->validated();

        $department->update([
            ...$validated,
            'is_active' => $validated['is_active'] ?? $department->is_active,
        ]);

        // Keep legacy text field in sync for assigned staff
        if ($department->wasChanged('department_name')) {
            $department->staffMembers()->update(['department' => $department->department_name]);
        }

        $assignedUserIds = $request->input('assigned_user_ids', []);
        
        // Dissociate staff no longer assigned to this department
        \App\Models\Staff::where('department_id', $department->department_id)
            ->whereNotIn('user_id', $assignedUserIds)
            ->update([
                'department_id' => null,
                'department' => null,
            ]);

        // Associate assigned staff
        foreach ($assignedUserIds as $userId) {
            $user = \App\Models\User::find($userId);
            if ($user) {
                \App\Models\Staff::updateOrCreate(
                    ['user_id' => $userId],
                    [
                        'department_id' => $department->department_id,
                        'department' => $department->department_name,
                        'employee_id' => $user->staff?->employee_id ?? (strtoupper($user->username) . '-001'),
                        'join_date' => $user->staff?->join_date ?? now()->toDateString(),
                    ]
                );
            }
        }

        ActivityLogger::log(
            'admin',
            "Department \"{$department->department_name}\" updated",
            ['department_id' => $department->department_id],
            Auth::user(),
            $department
        );

        return redirect()->route('departments.show', $department->department_id)
            ->with('success', 'Department updated successfully.');
    }

    public function destroy($id)
    {
        $this->authorizeAdmin();

        $department = Department::withCount('staffMembers')->findOrFail($id);

        if ($department->staff_members_count > 0) {
            return back()->with('error', 'Cannot delete a department that has assigned staff. Reassign staff first or deactivate the department.');
        }

        ActivityLogger::log(
            'admin',
            "Department \"{$department->department_name}\" deleted",
            ['department_id' => $department->department_id],
            Auth::user()
        );

        $department->delete();

        return redirect()->route('departments.index')->with('success', 'Department deleted.');
    }

    public function toggle($id)
    {
        $this->authorizeAdmin();

        $department = Department::findOrFail($id);
        $department->update(['is_active' => ! $department->is_active]);

        $state = $department->is_active ? 'activated' : 'deactivated';

        ActivityLogger::log(
            'admin',
            "Department \"{$department->department_name}\" {$state}",
            ['department_id' => $department->department_id],
            Auth::user(),
            $department
        );

        return back()->with('success', "Department {$state}.");
    }
}
