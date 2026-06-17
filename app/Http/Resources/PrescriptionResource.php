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
            'prescription_number' => $this->prescription_number
                ?? ('RX-' . str_pad((string) $this->prescription_id, 6, '0', STR_PAD_LEFT)),
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
            'doctor' => $this->whenLoaded('doctor', fn () => new UserResource($this->doctor)),
            'voided_by_user' => $this->whenLoaded('voidedBy', fn () => new UserResource($this->voidedBy)),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'item_id' => $item->item_id,
                'medication_id' => $item->medication_id,
                'dosage' => $item->dosage,
                'frequency' => $item->frequency,
                'duration' => $item->duration,
                'quantity' => $item->quantity,
                'status' => $item->status,
                'instructions' => $item->instructions,
                'medicine_name' => $item->medication?->medication_name,
                'medication' => $item->relationLoaded('medication') && $item->medication ? [
                    'medication_id' => $item->medication->medication_id,
                    'medication_name' => $item->medication->medication_name,
                    'strength' => $item->medication->strength ?? null,
                    'unit' => $item->medication->unit ?? null,
                ] : null,
            ])),
            'consultation' => $this->whenLoaded('consultation', fn () => new ConsultationResource($this->consultation)),
        ];
    }
}
