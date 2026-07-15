<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTelehealthConsentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_name' => 'required|string|max:255',
            'patient_email' => 'required|email|max:255',
            'patient_phone' => 'nullable|string|max:20',
            'doctor_name' => 'required|string|max:255',
            'patient_signature' => 'required|string',
            'verbal_consent_obtained' => 'nullable|boolean',
        ];
    }
}
