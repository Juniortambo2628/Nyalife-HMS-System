<?php

namespace App\Http\Requests;

use App\Models\Department;
use App\Support\Permissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(Permissions::MANAGE_DEPARTMENTS);
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'department_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('departments', 'department_name')->ignore($id, 'department_id'),
            ],
            'description' => 'nullable|string|max:2000',
            'code' => 'nullable|string|max:10',
            'type' => 'nullable|in:' . implode(',', array_keys(Department::TYPES)),
            'head_name' => 'nullable|string|max:100',
            'head_position' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ];
    }
}
