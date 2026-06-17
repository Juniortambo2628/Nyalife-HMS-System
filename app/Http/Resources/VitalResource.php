<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VitalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'vital_id' => $this->vital_id,
            'patient_id' => $this->patient_id,
            'consultation_id' => $this->consultation_id,
            'blood_pressure' => $this->blood_pressure,
            'heart_rate' => $this->heart_rate,
            'respiratory_rate' => $this->respiratory_rate,
            'temperature' => $this->temperature,
            'weight' => $this->weight,
            'height' => $this->height,
            'bmi' => $this->bmi,
            'pain_level' => $this->pain_level,
            'oxygen_saturation' => $this->oxygen_saturation,
            'priority' => $this->priority,
            'notes' => $this->notes,
            'measured_at' => $this->measured_at instanceof \DateTimeInterface
                ? $this->measured_at->format(\DateTimeInterface::ATOM)
                : $this->measured_at,
            'recorded_by' => $this->recorded_by,
            'is_voided' => (bool) $this->is_voided,
            'void_reason' => $this->void_reason,
            'voided_by' => $this->voided_by,
            'voided_at' => $this->voided_at instanceof \DateTimeInterface
                ? $this->voided_at->format(\DateTimeInterface::ATOM)
                : $this->voided_at,
            'created_at' => $this->created_at instanceof \DateTimeInterface
                ? $this->created_at->format(\DateTimeInterface::ATOM)
                : $this->created_at,
            'updated_at' => $this->updated_at instanceof \DateTimeInterface
                ? $this->updated_at->format(\DateTimeInterface::ATOM)
                : $this->updated_at,
            'patient' => $this->whenLoaded('patient', fn () => new PatientResource($this->patient)),
            'recorded_by_user' => $this->whenLoaded('recordedBy', fn () => new UserResource($this->recordedBy)),
            'voided_by_user' => $this->whenLoaded('voidedBy', fn () => new UserResource($this->voidedBy)),
            'consultation' => $this->whenLoaded('consultation', fn () => new ConsultationResource($this->consultation)),
        ];
    }
}
