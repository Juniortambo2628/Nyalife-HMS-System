<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => 'required|exists:patients,patient_id',
            'doctor_id' => 'required',
            'consultation_date' => 'required|date',
            'chief_complaint' => 'required|string',
            'status' => 'required|string',
            'history_present_illness' => 'nullable|string',
            'vital_signs' => 'nullable|array',
            'menstrual_history' => 'nullable|array',
            'past_obstetric' => 'nullable|array',
            'cervical_screening' => 'nullable|string',
            'gynecological_history' => 'nullable|string',
            'obstetric_history' => 'nullable|string',
            'social_history' => 'nullable|string',
            'family_history' => 'nullable|string',
            'review_of_systems' => 'nullable|string',
            'general_examination' => 'nullable|string',
            'systems_examination' => 'nullable|string',
        ];
    }
}
