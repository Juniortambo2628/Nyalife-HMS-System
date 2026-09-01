<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'appointment_id' => $this->appointment_id,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'appointment_date' => $this->appointment_date instanceof \DateTimeInterface ? $this->appointment_date->format('Y-m-d') : $this->appointment_date,
            'appointment_time' => $this->appointment_time instanceof \DateTimeInterface
                ? $this->appointment_time->format('H:i')
                : (is_string($this->appointment_time) ? substr($this->appointment_time, 0, 5) : $this->appointment_time),
            'end_time' => $this->end_time instanceof \DateTimeInterface
                ? $this->end_time->format('H:i')
                : (is_string($this->end_time) ? substr($this->end_time, 0, 5) : $this->end_time),
            'appointment_type' => $this->appointment_type,
            'status' => $this->status,
            'reason' => $this->reason,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'telehealth_payment_amount' => $this->telehealth_payment_amount,
            'telehealth_payment_reference' => $this->telehealth_payment_reference,
            'telehealth_payment_submitted_at' => $this->telehealth_payment_submitted_at?->format(\DateTimeInterface::ATOM),
            'telehealth_payment_expires_at' => $this->telehealth_payment_expires_at?->format(\DateTimeInterface::ATOM),
            'telehealth_payment_approved_at' => $this->telehealth_payment_approved_at?->format(\DateTimeInterface::ATOM),
            'created_at' => $this->created_at instanceof \DateTimeInterface ? $this->created_at->format(\DateTimeInterface::ATOM) : $this->created_at,
            'updated_at' => $this->updated_at instanceof \DateTimeInterface ? $this->updated_at->format(\DateTimeInterface::ATOM) : $this->updated_at,
            'patient' => $this->whenLoaded('patient', fn () => new PatientResource($this->patient)),
            'doctor' => $this->whenLoaded('doctor', fn () => new StaffResource($this->doctor)),
            'prescriptions' => $this->whenLoaded('prescriptions', fn () => PrescriptionResource::collection($this->prescriptions)),
            'lab_test_requests' => $this->whenLoaded('labTestRequests', fn () => LabTestRequestResource::collection($this->labTestRequests)),
            'consultations' => $this->whenLoaded('consultations', fn () => ConsultationResource::collection($this->consultations)),
        ];
    }
}
