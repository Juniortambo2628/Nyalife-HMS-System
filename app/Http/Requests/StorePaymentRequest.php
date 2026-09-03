<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Support\Permissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePaymentRequest extends FormRequest
{
    public function prepareForValidation(): void
    {
        $invoiceId = $this->input('invoice_id', $this->route('invoice'));
        if ($invoiceId !== null && ! $this->has('invoice_id')) {
            $this->merge(['invoice_id' => $invoiceId]);
        }

        if (! $this->has('payment_date')) {
            $this->merge(['payment_date' => now()->toDateString()]);
        }

        if (! $this->has('transaction_reference') && $this->has('reference_number')) {
            $this->merge(['transaction_reference' => $this->input('reference_number')]);
        }
    }

    public function authorize(): bool
    {
        return (bool) $this->user()?->can(Permissions::MANAGE_PAYMENTS);
    }

    public function rules(): array
    {
        return [
            'invoice_id' => 'required|exists:invoices,invoice_id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:'.implode(',', array_keys(Payment::METHODS)),
            'payment_date' => 'required|date',
            'transaction_reference' => 'nullable|string|max:100',
            'payment_status' => 'nullable|in:'.implode(',', array_keys(Payment::STATUSES)),
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $invoice = Invoice::find($this->input('invoice_id'));
            if (! $invoice) {
                return;
            }

            if ($invoice->status === 'paid') {
                $validator->errors()->add('invoice_id', 'This invoice is already fully paid.');
            }

            $status = $this->input('payment_status', 'completed');
            if ($status === 'completed') {
                $remaining = PaymentService::remainingBalance($invoice);
                if ((float) $this->input('amount') > $remaining) {
                    $validator->errors()->add(
                        'amount',
                        'Payment amount exceeds remaining balance (Ksh '.number_format($remaining, 2).').'
                    );
                }
            }
        });
    }
}
