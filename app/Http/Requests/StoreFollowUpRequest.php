<?php

namespace App\Http\Requests;

use App\Models\FollowUp;
use App\Support\Permissions;
use Illuminate\Foundation\Http\FormRequest;

class StoreFollowUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(Permissions::MANAGE_FOLLOW_UPS);
    }

    public function rules(): array
    {
        return [
            'patient_id' => 'required|exists:patients,patient_id',
            'consultation_id' => 'required|exists:consultations,consultation_id',
            'follow_up_date' => 'required|date',
            'follow_up_type' => 'nullable|string|max:50',
            'reason' => 'required|string|max:2000',
            'status' => 'nullable|in:'.implode(',', array_keys(FollowUp::STATUSES)),
            'notes' => 'nullable|string|max:2000',
        ];
    }
}
