<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsultationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'consultation_id' => $this->consultation_id,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'appointment_id' => $this->appointment_id,
            'consultation_date' => $this->consultation_date instanceof \DateTimeInterface ? $this->consultation_date->format(\DateTimeInterface::ATOM) : $this->consultation_date,
            'consultation_status' => $this->consultation_status,
            'is_walk_in' => $this->is_walk_in,
            'chief_complaint' => $this->chief_complaint,
            'history_present_illness' => $this->history_present_illness,
            'past_medical_history' => $this->past_medical_history,
            'family_history' => $this->family_history,
            'social_history' => $this->social_history,
            'obstetric_history' => $this->obstetric_history,
            'gynecological_history' => $this->gynecological_history,
            'menstrual_history' => $this->menstrual_history,
            'contraceptive_history' => $this->contraceptive_history,
            'sexual_history' => $this->sexual_history,
            'review_of_systems' => $this->review_of_systems,
            'vital_signs' => $this->vital_signs,
            'physical_examination' => $this->physical_examination,
            'general_examination' => $this->general_examination,
            'systems_examination' => $this->systems_examination,
            'diagnosis' => $this->diagnosis,
            'diagnosis_confidence' => $this->diagnosis_confidence,
            'differential_diagnosis' => $this->differential_diagnosis,
            'diagnostic_plan' => $this->diagnostic_plan,
            'treatment_plan' => $this->treatment_plan,
            'follow_up_instructions' => $this->follow_up_instructions,
            'notes' => $this->notes,
            'clinical_summary' => $this->clinical_summary,
            'parity' => $this->parity,
            'current_pregnancy' => $this->current_pregnancy,
            'past_obstetric' => $this->past_obstetric,
            'surgical_history' => $this->surgical_history,
            'cervical_screening' => $this->cervical_screening,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at instanceof \DateTimeInterface ? $this->created_at->format(\DateTimeInterface::ATOM) : $this->created_at,
            'updated_at' => $this->updated_at instanceof \DateTimeInterface ? $this->updated_at->format(\DateTimeInterface::ATOM) : $this->updated_at,
            'patient' => $this->whenLoaded('patient', fn () => new PatientResource($this->patient)),
            'doctor' => $this->whenLoaded('doctor', fn () => new StaffResource($this->doctor)),
            'appointment' => $this->whenLoaded('appointment', fn () => new AppointmentResource($this->appointment)),
            'prescriptions' => $this->whenLoaded('prescriptions', fn () => PrescriptionResource::collection($this->prescriptions)),
            'lab_test_requests' => $this->whenLoaded('labTestRequests', fn () => LabTestRequestResource::collection($this->labTestRequests)),
            'invoices' => $this->whenLoaded('invoices', fn () => $this->invoices), // Using array/object directly is fine for this context
        ];
    }
}
