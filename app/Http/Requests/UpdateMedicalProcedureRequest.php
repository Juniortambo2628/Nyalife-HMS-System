<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateMedicalProcedureRequest extends StoreMedicalProcedureRequest
{
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            ['name' => ['required', 'string', 'max:255', Rule::unique('medical_procedures', 'name')->ignore($this->route('medical_procedure'))]]
        );
    }
}
