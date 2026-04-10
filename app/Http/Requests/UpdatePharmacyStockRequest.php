<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePharmacyStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'medication_id' => 'required|exists:medications,medication_id',
            'quantity' => 'required|integer',
            'type' => 'required|in:add,set',
            'notes' => 'nullable|string',
        ];
    }
}
