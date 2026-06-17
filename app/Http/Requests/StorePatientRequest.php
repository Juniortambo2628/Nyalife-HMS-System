<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'address' => 'nullable|string',
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
            'emergency_contact' => 'nullable|string|max:20',
        ];
    }
}
