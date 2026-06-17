<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RadiologyRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $showClinical = true;

        if ($user) {
            if ($user->role === 'receptionist') {
                $showClinical = false;
            } elseif ($user->role === 'patient') {
                if (!in_array($this->status, ['verified', 'completed'])) {
                    $showClinical = false;
                }
            }
        }

        return [
            'request_id' => $this->request_id,
            'request_number' => $this->request_number,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'appointment_id' => $this->appointment_id,
            'consultation_id' => $this->consultation_id,
            'scan_type' => $this->scan_type,
            'priority' => $this->priority,
            'status' => $this->status,
            'requested_by' => $this->requested_by,
            'assigned_to' => $this->assigned_to,
            'verified_by' => $this->verified_by,
            'verified_at' => $this->verified_at instanceof \DateTimeInterface ? $this->verified_at->format(\DateTimeInterface::ATOM) : $this->verified_at,
            'completed_at' => $this->completed_at instanceof \DateTimeInterface ? $this->completed_at->format(\DateTimeInterface::ATOM) : $this->completed_at,
            'created_at' => $this->created_at instanceof \DateTimeInterface ? $this->created_at->format(\DateTimeInterface::ATOM) : $this->created_at,
            'updated_at' => $this->updated_at instanceof \DateTimeInterface ? $this->updated_at->format(\DateTimeInterface::ATOM) : $this->updated_at,
            
            // HIPAA-protected clinical information
            'clinical_indication' => $showClinical ? $this->clinical_indication : null,
            'scan_details' => $showClinical ? $this->scan_details : null,
            'findings' => $showClinical ? $this->findings : null,
            'impression' => $showClinical ? $this->impression : null,
            
            'patient' => $this->whenLoaded('patient', fn () => new PatientResource($this->patient)),
            'doctor' => $this->whenLoaded('doctor', fn () => new StaffResource($this->doctor)),
            'requestedBy' => $this->whenLoaded('requestedBy', fn () => new UserResource($this->requestedBy)),
            'assignedToUser' => $this->whenLoaded('assignedTo', fn () => new UserResource($this->assignedTo)),
            'verifiedByUser' => $this->whenLoaded('verifiedBy', fn () => new UserResource($this->verifiedBy)),
            'consultation' => $this->whenLoaded('consultation', fn () => new ConsultationResource($this->consultation)),
        ];
    }
}
