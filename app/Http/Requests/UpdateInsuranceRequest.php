<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateInsuranceRequest extends StoreInsuranceRequest
{
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'name' => ['required', 'string', 'max:255', Rule::unique('insurances', 'name')->ignore($this->route('insurance'))],
                'logo' => 'nullable|image|max:2048',
            ]
        );
    }
}
