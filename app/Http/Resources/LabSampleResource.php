<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabSampleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sample_id' => $this->sample_id,
            'patient_id' => $this->patient_id,
            'test_type_id' => $this->test_type_id,
            'sample_type' => $this->sample_type,
            'sample_type_label' => $this->sample_type_label,
            'collected_date' => $this->collected_date instanceof \DateTimeInterface
                ? $this->collected_date->format('Y-m-d')
                : $this->collected_date,
            'collected_at' => $this->collected_at instanceof \DateTimeInterface
                ? $this->collected_at->format('Y-m-d H:i')
                : $this->collected_at,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'notes' => $this->notes,
            'urgent' => (bool) $this->urgent,
            'completed_at' => $this->completed_at instanceof \DateTimeInterface
                ? $this->completed_at->format('Y-m-d H:i')
                : $this->completed_at,
            'patient' => $this->whenLoaded('patient', fn () => new PatientResource($this->patient)),
            'test_type' => $this->whenLoaded('testType', fn () => $this->testType),
            'collected_by_user' => $this->whenLoaded('collectedByUser', fn () => new UserResource($this->collectedByUser)),
        ];
    }
}
