<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabTestRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'request_id' => $this->request_id,
            'request_number' => $this->request_number,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'test_type_id' => $this->test_type_id,
            'priority' => $this->priority,
            'requested_by' => $this->requested_by,
            'status' => $this->status,
            'request_date' => $this->request_date instanceof \DateTimeInterface ? $this->request_date->format('Y-m-d') : $this->request_date,
            'processed_by' => $this->processed_by,
            'processed_at' => $this->processed_at instanceof \DateTimeInterface ? $this->processed_at->format(\DateTimeInterface::ATOM) : $this->processed_at,
            'created_at' => $this->created_at instanceof \DateTimeInterface ? $this->created_at->format(\DateTimeInterface::ATOM) : $this->created_at,
            'updated_at' => $this->updated_at instanceof \DateTimeInterface ? $this->updated_at->format(\DateTimeInterface::ATOM) : $this->updated_at,
            'patient' => $this->whenLoaded('patient', fn () => new PatientResource($this->patient)),
            'doctor' => $this->whenLoaded('doctor', fn () => new StaffResource($this->doctor)),
            'requestedBy' => $this->whenLoaded('requestedBy', fn () => new UserResource($this->requestedBy)),
            'testType' => $this->whenLoaded('testType', fn () => $this->testType),
        ];
    }
}
