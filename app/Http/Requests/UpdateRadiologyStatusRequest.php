<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRadiologyStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:pending,processing,pending_verification,verified,completed,cancelled',
            'findings' => 'nullable|string',
            'impression' => 'nullable|string',
            'scan_details' => 'nullable|string',
        ];
    }
}
