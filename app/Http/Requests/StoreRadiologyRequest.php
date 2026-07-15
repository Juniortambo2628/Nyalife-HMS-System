<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRadiologyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => 'required|exists:patients,patient_id',
            'consultation_id' => 'nullable|integer|exists:consultations,consultation_id',
            'scan_type' => 'required|string|max:255',
            'priority' => 'required|string|in:routine,urgent,stat',
            'clinical_indication' => 'nullable|string',
            'scan_details' => 'nullable|string',
        ];
    }
}
