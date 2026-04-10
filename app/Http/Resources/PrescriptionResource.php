<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrescriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'prescription_id' => $this->prescription_id,
            'prescription_number' => $this->prescription_number,
            'patient_id' => $this->patient_id,
            'prescribed_by' => $this->prescribed_by,
            'appointment_id' => $this->appointment_id,
            'consultation_id' => $this->consultation_id,
            'prescription_date' => $this->prescription_date instanceof \DateTimeInterface
                ? $this->prescription_date->format('Y-m-d')
                : $this->prescription_date,
            'status' => $this->status,
            'notes' => $this->notes,
            'dispensed_by' => $this->dispensed_by,
            'dispensed_at' => $this->dispensed_at instanceof \DateTimeInterface
                ? $this->dispensed_at->format(\DateTimeInterface::ATOM)
                : $this->dispensed_at,
            'created_at' => $this->created_at instanceof \DateTimeInterface
                ? $this->created_at->format(\DateTimeInterface::ATOM)
                : $this->created_at,
            'updated_at' => $this->updated_at instanceof \DateTimeInterface
                ? $this->updated_at->format(\DateTimeInterface::ATOM)
                : $this->updated_at,
            'patient' => $this->whenLoaded('patient', fn () => new PatientResource($this->patient)),
            'doctor' => $this->whenLoaded('doctor', fn () => new UserResource($this->doctor)),
            'items' => $this->whenLoaded('items', fn () => $this->items->toArray()),
            'consultation' => $this->whenLoaded('consultation', fn () => new ConsultationResource($this->consultation)),
        ];
    }
}
