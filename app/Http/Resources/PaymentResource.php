<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'payment_id' => $this->payment_id,
            'invoice_id' => $this->invoice_id,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'payment_method_label' => \App\Models\Payment::METHODS[$this->payment_method] ?? $this->payment_method,
            'payment_date' => $this->payment_date instanceof \DateTimeInterface
                ? $this->payment_date->format('Y-m-d H:i')
                : $this->payment_date,
            'transaction_reference' => $this->transaction_reference,
            'payment_status' => $this->payment_status,
            'notes' => $this->notes,
            'received_by' => $this->received_by,
            'created_at' => $this->created_at instanceof \DateTimeInterface
                ? $this->created_at->format(\DateTimeInterface::ATOM)
                : $this->created_at,
            'invoice' => $this->whenLoaded('invoice', fn () => InvoiceResource::make($this->invoice)),
            'received_by_user' => $this->whenLoaded('receivedBy', fn () => new UserResource($this->receivedBy)),
        ];
    }
}
