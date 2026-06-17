<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'email' => 'sometimes|email|max:255',
            'address' => 'nullable|string',
            'gender' => 'nullable|string|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'blood_group' => 'nullable|string',
            'height' => 'nullable|numeric|min:0|max:300',
            'weight' => 'nullable|numeric|min:0|max:500',
            'allergies' => 'nullable|string|max:1000',
            'chronic_diseases' => 'nullable|string|max:1000',
            'marital_status' => 'nullable|string|in:single,married,divorced,widowed,other',
            'occupation' => 'nullable|string|max:255',
            'insurance_provider' => 'nullable|string|max:255',
            'insurance_id' => 'nullable|string|max:255',
            'emergency_name' => 'nullable|string|max:255',
            'emergency_contact' => 'nullable|string',
        ];
    }
}
