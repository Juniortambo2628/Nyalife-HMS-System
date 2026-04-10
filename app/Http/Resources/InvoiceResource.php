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
            'invoice_number' => $this->invoice_number,
            'invoice_date' => $this->invoice_date instanceof \DateTimeInterface ? $this->invoice_date->format('Y-m-d') : $this->invoice_date,
            'due_date' => $this->due_date instanceof \DateTimeInterface ? $this->due_date->format('Y-m-d') : $this->due_date,
            'total_amount' => $this->total_amount,
            'discount' => $this->discount,
            'tax' => $this->tax,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at instanceof \DateTimeInterface ? $this->created_at->format(\DateTimeInterface::ATOM) : $this->created_at,
            'updated_at' => $this->updated_at instanceof \DateTimeInterface ? $this->updated_at->format(\DateTimeInterface::ATOM) : $this->updated_at,
            'patient' => $this->whenLoaded('patient', fn () => new PatientResource($this->patient)),
            'items' => $this->whenLoaded('items', fn () => $this->items->toArray()),
            'consultation' => $this->whenLoaded('consultation', fn () => new ConsultationResource($this->consultation)),
        ];
    }
}
