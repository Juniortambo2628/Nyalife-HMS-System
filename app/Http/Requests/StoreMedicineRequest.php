<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'medication_name' => 'required|string|max:255',
            'medication_type' => 'required|string|max:100',
            'strength' => 'required|string|max:100',
            'unit' => 'required|string|max:50',
            'price_per_unit' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ];
    }
}
