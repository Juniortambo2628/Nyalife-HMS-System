<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalProcedureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:medical_procedures,name',
            'description' => 'nullable|string',
            'category' => 'required|string|max:255',
            'standard_fee' => 'required|numeric|min:0',
        ];
    }
}
