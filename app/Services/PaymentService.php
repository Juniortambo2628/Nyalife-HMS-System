<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;

class PaymentService
{
    public static function completedTotalForInvoice(int $invoiceId): float
    {
        return (float) Payment::query()
            ->where('invoice_id', $invoiceId)
            ->completed()
            ->sum('amount');
    }

    public static function remainingBalance(Invoice $invoice): float
    {
        $paid = self::completedTotalForInvoice($invoice->invoice_id);

        return max(0, round((float) $invoice->total_amount - $paid, 2));
    }

    public static function syncInvoiceStatus(Invoice $invoice): void
    {
        $paid = self::completedTotalForInvoice($invoice->invoice_id);
        $total = (float) $invoice->total_amount;

        if ($paid >= $total && $total > 0) {
            $status = 'paid';
        } elseif ($paid > 0) {
            $status = 'partially_paid';
        } else {
            $status = 'pending';
        }

        $lastPayment = Payment::query()
            ->where('invoice_id', $invoice->invoice_id)
            ->completed()
            ->latest('payment_date')
            ->first();

        $invoice->update([
            'status' => $status,
            'payment_method' => $lastPayment?->payment_method ?? $invoice->payment_method,
        ]);
    }
}
