<?php

namespace App\Http\Requests;

class UpdateConsultationRequest extends StoreConsultationRequest
{
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            ['requested_prescriptions' => 'nullable|array']
        );
    }
}
