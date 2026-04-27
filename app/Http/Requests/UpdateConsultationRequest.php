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
            'patient_id' => 'required|exists:patients,patient_id',
            'doctor_id' => 'required',
            'consultation_date' => 'required|date',
            'chief_complaint' => 'nullable|string',
            'status' => 'required|string',
            'priority' => 'nullable|string|in:normal,emergency',
            'is_walk_in' => 'nullable|boolean',
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
            'diagnosis' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
            'follow_up_instructions' => 'nullable|string',
            'notes' => 'nullable|string',
            'requested_procedures' => 'nullable|array',
            'requested_labs' => 'nullable|array',
            'requested_service_items' => 'nullable|array',
            'requested_prescriptions' => 'nullable|array',
        ];
    }
}
