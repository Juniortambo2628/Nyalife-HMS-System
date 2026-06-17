<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'invoice_id' => $this->invoice_id,
            'patient_id' => $this->patient_id,
            'consultation_id' => $this->consultation_id,
            'doctor_id' => $this->doctor_id,
            'invoice_number' => $this->invoice_number,
            'invoice_date' => $this->invoice_date instanceof \DateTimeInterface ? $this->invoice_date->format('Y-m-d') : $this->invoice_date,
            'due_date' => $this->due_date instanceof \DateTimeInterface ? $this->due_date->format('Y-m-d') : $this->due_date,
            'total_amount' => $this->total_amount,
            'discount' => $this->discount,
            'tax' => $this->tax,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'insurance_claim_id' => $this->insurance_claim_id,
            'insurance_coverage' => $this->insurance_coverage,
            'patient_responsibility' => $this->patient_responsibility,
            'notes' => $this->notes,
            'is_voided' => (bool) $this->is_voided,
            'void_reason' => $this->void_reason,
            'voided_by' => $this->voided_by,
            'voided_at' => $this->voided_at instanceof \DateTimeInterface
                ? $this->voided_at->format(\DateTimeInterface::ATOM)
                : $this->voided_at,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at instanceof \DateTimeInterface ? $this->created_at->format(\DateTimeInterface::ATOM) : $this->created_at,
            'updated_at' => $this->updated_at instanceof \DateTimeInterface ? $this->updated_at->format(\DateTimeInterface::ATOM) : $this->updated_at,
            'patient' => $this->whenLoaded('patient', fn () => new PatientResource($this->patient)),
            'voided_by_user' => $this->whenLoaded('voidedBy', fn () => new UserResource($this->voidedBy)),
            'items' => $this->whenLoaded('items', fn () => $this->items->toArray()),
            'consultation' => $this->whenLoaded('consultation', fn () => new ConsultationResource($this->consultation)),
            'payments' => $this->whenLoaded('payments', fn () => PaymentResource::collection($this->payments)),
            'amount_paid' => $this->when(
                $this->relationLoaded('payments'),
                fn () => round((float) $this->payments->where('payment_status', 'completed')->sum('amount'), 2)
            ),
            'balance_due' => $this->when(
                $this->relationLoaded('payments'),
                fn () => max(0, round((float) $this->total_amount - (float) $this->payments->where('payment_status', 'completed')->sum('amount'), 2))
            ),
        ];
    }
}
