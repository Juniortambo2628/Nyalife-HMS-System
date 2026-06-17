<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'patient_id' => $this->patient_id,
            'user_id' => $this->user_id,
            'patient_number' => $this->patient_number,
            'gender' => $this->gender ?? ($this->user?->gender),
            'date_of_birth' => $this->date_of_birth ?? ($this->user?->date_of_birth),
            'address' => $this->address ?? ($this->user?->address),
            'blood_group' => $this->blood_group,
            'height' => $this->height,
            'weight' => $this->weight,
            'allergies' => $this->allergies,
            'chronic_diseases' => $this->chronic_diseases,
            'marital_status' => $this->marital_status,
            'occupation' => $this->occupation,
            'insurance_provider' => $this->insurance_provider,
            'insurance_id' => $this->insurance_id,
            'emergency_contact' => $this->emergency_contact,
            'age' => $this->age,
            'emergency_name' => $this->emergency_name ?? null,
            'created_at' => $this->created_at instanceof \DateTimeInterface ? $this->created_at->format(\DateTimeInterface::ATOM) : $this->created_at,
            'updated_at' => $this->updated_at instanceof \DateTimeInterface ? $this->updated_at->format(\DateTimeInterface::ATOM) : $this->updated_at,
            'user' => $this->whenLoaded('user', fn () => new UserResource($this->user)),
            'appointments' => $this->whenLoaded('appointments', fn () => AppointmentResource::collection($this->appointments)),
            'consultations' => $this->whenLoaded('consultations', fn () => ConsultationResource::collection($this->consultations)),
            'prescriptions' => $this->whenLoaded('prescriptions', fn () => PrescriptionResource::collection($this->prescriptions)),
            'vitals' => $this->whenLoaded('vitals', fn () => VitalResource::collection($this->vitals)),
        ];
    }
}
