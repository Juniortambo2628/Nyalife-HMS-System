<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => 'required|exists:patients,patient_id',
            'consultation_id' => 'nullable|exists:consultations,consultation_id',
            'prescription_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.medication_id' => 'nullable|exists:medications,medication_id',
            'items.*.medicine_name' => 'required|string',
            'items.*.dosage' => 'required|string',
            'items.*.frequency' => 'required|string',
            'items.*.duration' => 'required|string',
            'notes' => 'nullable|string',
        ];
    }
}
