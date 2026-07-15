<?php

namespace Tests\Unit\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Patient;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_completed_total_for_invoice(): void
    {
        $invoice = Invoice::factory()->create(['total_amount' => 10000]);

        Payment::factory()->create([
            'invoice_id' => $invoice->invoice_id,
            'amount' => 3000,
            'payment_status' => 'completed',
        ]);
        Payment::factory()->create([
            'invoice_id' => $invoice->invoice_id,
            'amount' => 2000,
            'payment_status' => 'completed',
        ]);
        Payment::factory()->create([
            'invoice_id' => $invoice->invoice_id,
            'amount' => 1000,
            'payment_status' => 'pending',
        ]);

        $total = \App\Services\PaymentService::completedTotalForInvoice($invoice->invoice_id);
        $this->assertEquals(5000, $total);
    }

    public function test_remaining_balance(): void
    {
        $invoice = Invoice::factory()->create(['total_amount' => 10000]);

        Payment::factory()->create([
            'invoice_id' => $invoice->invoice_id,
            'amount' => 4000,
            'payment_status' => 'completed',
        ]);

        $balance = \App\Services\PaymentService::remainingBalance($invoice);
        $this->assertEquals(6000, $balance);
    }

    public function test_remaining_balance_never_negative(): void
    {
        $invoice = Invoice::factory()->create(['total_amount' => 5000]);

        Payment::factory()->create([
            'invoice_id' => $invoice->invoice_id,
            'amount' => 6000,
            'payment_status' => 'completed',
        ]);

        $balance = \App\Services\PaymentService::remainingBalance($invoice);
        $this->assertEquals(0, $balance);
    }

    public function test_sync_invoice_status(): void
    {
        $invoice = Invoice::factory()->create(['total_amount' => 10000, 'status' => 'pending']);

        // No payments
        \App\Services\PaymentService::syncInvoiceStatus($invoice);
        $invoice->refresh();
        $this->assertEquals('pending', $invoice->status);

        // Partial payment
        Payment::factory()->create([
            'invoice_id' => $invoice->invoice_id,
            'amount' => 3000,
            'payment_status' => 'completed',
            'payment_method' => 'cash',
            'payment_date' => now()->format('Y-m-d H:i:s'),
        ]);
        \App\Services\PaymentService::syncInvoiceStatus($invoice);
        $invoice->refresh();
        $this->assertEquals('partially_paid', $invoice->status);
        $this->assertEquals('cash', $invoice->payment_method);

        // Full payment
        Payment::factory()->create([
            'invoice_id' => $invoice->invoice_id,
            'amount' => 7000,
            'payment_status' => 'completed',
            'payment_method' => 'mpesa',
            'payment_date' => now()->addMinutes(1)->format('Y-m-d H:i:s'),
        ]);
        \App\Services\PaymentService::syncInvoiceStatus($invoice);
        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
        $this->assertEquals('mpesa', $invoice->payment_method);
    }
}