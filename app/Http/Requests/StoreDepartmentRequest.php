<?php

namespace App\Http\Requests;

use App\Models\Department;
use App\Support\Permissions;
use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(Permissions::MANAGE_DEPARTMENTS);
    }

    public function rules(): array
    {
        return [
            'department_name' => 'required|string|max:100|unique:departments,department_name',
            'description' => 'nullable|string|max:2000',
            'code' => 'nullable|string|max:10',
            'type' => 'nullable|in:'.implode(',', array_keys(Department::TYPES)),
            'head_name' => 'nullable|string|max:100',
            'head_position' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
            'assigned_user_ids' => 'nullable|array',
            'assigned_user_ids.*' => 'integer|exists:users,user_id',
        ];
    }
}
