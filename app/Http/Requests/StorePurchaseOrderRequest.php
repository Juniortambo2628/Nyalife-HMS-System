<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'medication_id' => 'required|exists:medications,medication_id',
            'quantity' => 'required|integer|min:1',
            'supplier_name' => 'required|string|max:255',
            'estimated_cost' => 'required|numeric|min:0',
        ];
    }
}
