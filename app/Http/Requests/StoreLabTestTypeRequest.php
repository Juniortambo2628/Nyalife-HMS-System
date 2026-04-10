<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLabTestTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'test_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'category' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'normal_range' => 'nullable|string|max:100',
            'units' => 'nullable|string|max:20',
        ];
    }
}
