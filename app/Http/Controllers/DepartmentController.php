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

        return Inertia::render('Departments/Form', [
            'department' => null,
            'departmentTypes' => Department::TYPES,
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

        $department = Department::findOrFail($id);

        return Inertia::render('Departments/Form', [
            'department' => DepartmentResource::make($department),
            'departmentTypes' => Department::TYPES,
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
