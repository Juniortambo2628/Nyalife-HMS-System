<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FollowUpResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'follow_up_id' => $this->follow_up_id,
            'patient_id' => $this->patient_id,
            'consultation_id' => $this->consultation_id,
            'follow_up_date' => $this->follow_up_date instanceof \DateTimeInterface
                ? $this->follow_up_date->format('Y-m-d')
                : $this->follow_up_date,
            'follow_up_type' => $this->follow_up_type,
            'follow_up_type_label' => $this->type_label,
            'reason' => $this->reason,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at instanceof \DateTimeInterface
                ? $this->created_at->format(\DateTimeInterface::ATOM)
                : $this->created_at,
            'patient' => $this->whenLoaded('patient', fn () => new PatientResource($this->patient)),
            'consultation' => $this->whenLoaded('consultation', fn () => ConsultationResource::make($this->consultation)),
            'created_by_user' => $this->whenLoaded('createdBy', fn () => new UserResource($this->createdBy)),
        ];
    }
}
