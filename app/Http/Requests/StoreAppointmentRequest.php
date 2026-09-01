<?php

namespace App\Http\Requests;

use App\Models\Appointment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'patient_id' => 'required|exists:patients,patient_id',
            'doctor_id' => 'required|exists:staff,staff_id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'appointment_type' => ['nullable', 'string', Rule::in(Appointment::APPOINTMENT_TYPES)],
            'reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
        ];
    }
}
