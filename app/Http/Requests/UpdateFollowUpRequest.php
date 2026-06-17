<?php

namespace App\Http\Requests;

use App\Models\FollowUp;
use App\Support\Permissions;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFollowUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(Permissions::MANAGE_FOLLOW_UPS);
    }

    public function rules(): array
    {
        return [
            'follow_up_date' => 'required|date',
            'follow_up_type' => 'nullable|string|max:50',
            'reason' => 'required|string|max:2000',
            'status' => 'required|in:' . implode(',', array_keys(FollowUp::STATUSES)),
            'notes' => 'nullable|string|max:2000',
        ];
    }
}
