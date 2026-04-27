<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVitalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => 'required|exists:patients,patient_id',
            'temperature' => 'nullable|numeric|between:30,45',
            'blood_pressure' => 'nullable|string|max:20',
            'heart_rate' => 'nullable|integer|between:20,300',
            'respiratory_rate' => 'nullable|integer|between:5,100',
            'weight' => 'nullable|numeric|between:0,500',
            'height' => 'nullable|numeric|between:0,300',
            'oxygen_saturation' => 'nullable|integer|between:0,100',
            'priority' => 'nullable|string|in:normal,emergency',
            'notes' => 'nullable|string',
        ];
    }
}
