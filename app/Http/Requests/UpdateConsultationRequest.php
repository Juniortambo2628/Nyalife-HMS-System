<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => 'required',
            'doctor_id' => 'required',
            'consultation_date' => 'required|date',
            'chief_complaint' => 'required|string',
        ];
    }
}
