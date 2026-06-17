<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        if (! $request->has('active_only') && ! $request->has('status')) {
            $request->merge(['active_only' => true]);
        }

        $departments = Department::withCount('staffMembers')
            ->filteredQuery($request)
            ->orderBy('department_name')
            ->paginate($request->integer('per_page', 15));

        return DepartmentResource::collection($departments);
    }

    public function show($id)
    {
        $department = Department::withCount('staffMembers')->findOrFail($id);

        return DepartmentResource::make($department);
    }
}
