<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'void_reason' => 'required|string|max:255',
        ];
    }
}
