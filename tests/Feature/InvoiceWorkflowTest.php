<?php

namespace Tests\Feature;

use App\Models\Consultation;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InvoiceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_payment_workflow()
    {
        // Setup User
        $receptionistUser = User::factory()->create([
            'role_id' => Role::query()->firstOrCreate(['role_name' => 'receptionist'])->role_id,
        ]);
        $receptionistStaff = Staff::factory()->create(['user_id' => $receptionistUser->user_id]);

        $patient = Patient::factory()->create();

        // Setup Consultation
        $consultation = Consultation::factory()->create([
            'patient_id' => $patient->patient_id,
        ]);

        // Create Invoice
        $invoice = Invoice::create([
            'patient_id' => $patient->patient_id,
            'consultation_id' => $consultation->consultation_id,
            'total_amount' => 500.00,
            'amount_paid' => 0.00,
            'amount_due' => 500.00,
            'status' => 'unpaid',
            'issued_date' => now(),
        ]);

        // Add Invoice Item
        InvoiceItem::create([
            'invoice_id' => $invoice->invoice_id,
            'item_type' => 'consultation_fee',
            'description' => 'General Consultation',
            'quantity' => 1,
            'unit_price' => 500.00,
            'total_price' => 500.00,
        ]);

        // Partial Payment
        $this->actingAs($receptionistUser);
        
        $paymentData = [
            'amount' => 200.00,
            'payment_method' => 'cash',
            'reference_number' => 'RCPT123',
            'notes' => 'Partial payment',
        ];

        $response = $this->post(route('invoices.payments.store', $invoice->invoice_id), $paymentData);
        $response->assertRedirect();
        
        $invoice->refresh();
        $this->assertEquals(200.00, $invoice->amount_paid);
        $this->assertEquals(300.00, $invoice->amount_due);
        $this->assertEquals('partially_paid', $invoice->status);

        // Full Payment
        $paymentData2 = [
            'amount' => 300.00,
            'payment_method' => 'mpesa',
            'reference_number' => 'MPESA123',
            'notes' => 'Full payment',
        ];

        $response = $this->post(route('invoices.payments.store', $invoice->invoice_id), $paymentData2);
        
        $invoice->refresh();
        $this->assertEquals(500.00, $invoice->amount_paid);
        $this->assertEquals(0.00, $invoice->amount_due);
        $this->assertEquals('paid', $invoice->status);
    }
}
