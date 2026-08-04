<?php

namespace Tests\Unit\Models;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_belongs_to_invoice(): void
    {
        $payment = Payment::factory()->create();

        $this->assertInstanceOf(Invoice::class, $payment->invoice);
        $this->assertEquals($payment->invoice_id, $payment->invoice->invoice_id);
    }

    public function test_payment_belongs_to_received_by_user(): void
    {
        $payment = Payment::factory()->create();

        $this->assertInstanceOf(User::class, $payment->receivedBy);
        $this->assertEquals($payment->received_by, $payment->receivedBy->user_id);
    }

    public function test_payment_status_values(): void
    {
        $statuses = ['completed', 'pending', 'failed', 'refunded'];

        foreach ($statuses as $status) {
            $payment = Payment::factory()->create(['payment_status' => $status]);
            $this->assertEquals($status, $payment->payment_status);
        }
    }

    public function test_payment_amount_stored_as_decimal(): void
    {
        $payment = Payment::factory()->create(['amount' => 15000.75]);

        $this->assertEquals(15000.75, $payment->amount);
    }

    public function test_payment_method_values(): void
    {
        $methods = ['cash', 'card', 'mpesa', 'insurance', 'bank_transfer'];

        foreach ($methods as $method) {
            $payment = Payment::factory()->create(['payment_method' => $method]);
            $this->assertEquals($method, $payment->payment_method);
        }
    }
}
