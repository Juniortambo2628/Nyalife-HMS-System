<?php

namespace App\Http\Requests;

use App\Models\LabSample;
use App\Support\Permissions;
use Illuminate\Foundation\Http\FormRequest;

class StoreLabSampleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasAnyPermission([
            Permissions::MANAGE_LAB,
            Permissions::MANAGE_VITALS,
            Permissions::MANAGE_CONSULTATIONS,
        ]);
    }

    public function rules(): array
    {
        return [
            'lab_request_id' => 'nullable|exists:lab_test_requests,request_id',
            'patient_id' => 'required|exists:patients,patient_id',
            'test_type_id' => 'required|exists:lab_test_types,test_type_id',
            'sample_type' => 'required|in:'.implode(',', array_keys(LabSample::SAMPLE_TYPES)),
            'collected_date' => 'required|date',
            'collected_at' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'urgent' => 'nullable|boolean',
        ];
    }
}
