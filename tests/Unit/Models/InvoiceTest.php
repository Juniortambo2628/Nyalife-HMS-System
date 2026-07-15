<?php

namespace Tests\Unit\Models;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Consultation;
use App\Models\InvoiceItem;
use App\Models\Payment;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class InvoiceTest extends TestCase
{
    use DatabaseMigrations;

    public function test_invoice_belongs_to_patient(): void
    {
        $invoice = Invoice::factory()->create();

        $this->assertInstanceOf(Patient::class, $invoice->patient);
        $this->assertEquals($invoice->patient_id, $invoice->patient->patient_id);
    }

    public function test_invoice_belongs_to_consultation(): void
    {
        $invoice = Invoice::factory()->create();

        $this->assertInstanceOf(Consultation::class, $invoice->consultation);
        $this->assertEquals($invoice->consultation_id, $invoice->consultation->consultation_id);
    }

    public function test_invoice_has_many_items(): void
    {
        $invoice = Invoice::factory()
            ->has(\App\Models\InvoiceItem::factory()->count(4), 'items')
            ->create();

        $this->assertCount(4, $invoice->items);
    }

    public function test_invoice_has_many_payments(): void
    {
        $invoice = Invoice::factory()->has(Payment::factory()->count(3))->create();

        $this->assertCount(3, $invoice->payments);
    }

    public function test_invoice_void_fields_work_correctly(): void
    {
        $invoice = Invoice::factory()->create([
            'is_voided' => true,
            'void_reason' => 'Duplicate invoice',
            'voided_by' => \App\Models\User::factory()->create()->user_id,
            'voided_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $this->assertTrue($invoice->is_voided);
        $this->assertEquals('Duplicate invoice', $invoice->void_reason);
        $this->assertNotNull($invoice->voided_by);
        $this->assertNotNull($invoice->voided_at);
    }

    public function test_invoice_global_scope_excludes_voided_by_default(): void
    {
        $voidedInvoice = Invoice::factory()->create(['is_voided' => true, 'invoice_number' => 'INV-TEST-VOID-1']);
        $activeInvoice = Invoice::factory()->create(['is_voided' => false, 'invoice_number' => 'INV-TEST-VOID-2']);

        $results = Invoice::all();
        $this->assertCount(1, $results);
        $this->assertEquals($activeInvoice->invoice_id, $results->first()->invoice_id);

        $voidedResults = Invoice::withVoided()->get();
        $this->assertCount(2, $voidedResults);
        $this->assertTrue($voidedResults->contains('invoice_id', $voidedInvoice->invoice_id));
        $this->assertTrue($voidedResults->contains('invoice_id', $activeInvoice->invoice_id));
    }

    public function test_invoice_void_and_unvoid_methods(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $invoice = Invoice::factory()->create(['is_voided' => false, 'invoice_number' => 'INV-TEST-UNVOID-1']);

        $invoice->void('Test void reason');
        $invoice->refresh();

        $this->assertTrue($invoice->isVoided());
        $this->assertEquals('Test void reason', $invoice->void_reason);
        $this->assertEquals($user->user_id, $invoice->voided_by);

        $invoice->unvoid();
        $invoice->refresh();

        $this->assertFalse($invoice->isVoided());
        $this->assertNull($invoice->void_reason);
        $this->assertNull($invoice->voided_by);
    }

    public function test_invoice_status_scope_works(): void
    {
        $pendingInvoice = Invoice::factory()->create(['status' => 'pending', 'invoice_number' => 'INV-TEST-SCOPE-1']);
        $paidInvoice = Invoice::factory()->create(['status' => 'paid', 'invoice_number' => 'INV-TEST-SCOPE-2']);
        $partiallyPaidInvoice = Invoice::factory()->create(['status' => 'partially_paid', 'invoice_number' => 'INV-TEST-SCOPE-3']);

        $pendingResults = Invoice::status('pending')->get();
        $this->assertCount(1, $pendingResults);
        $this->assertEquals($pendingInvoice->invoice_id, $pendingResults->first()->invoice_id);

        $paidResults = Invoice::status('paid')->get();
        $this->assertCount(1, $paidResults);
        $this->assertEquals($paidInvoice->invoice_id, $paidResults->first()->invoice_id);
    }

    public function test_invoice_search_by_patient_or_number(): void
    {
        $patient = Patient::factory()->create([
            'user_id' => \App\Models\User::factory()->create(['first_name' => 'Jane', 'last_name' => 'Smith'])->user_id,
        ]);
        $invoice = Invoice::factory()->create([
            'patient_id' => $patient->patient_id,
            'invoice_number' => 'INV-TEST-001',
        ]);

        $results = Invoice::searchByPatientOrNumber('Jane')->get();
        $this->assertCount(1, $results);

        $results = Invoice::searchByPatientOrNumber('INV-TEST-001')->get();
        $this->assertCount(1, $results);
    }

    public function test_invoice_decimal_casts_work_correctly(): void
    {
        $invoice = Invoice::factory()->create([
            'total_amount' => 12500.50,
            'discount' => 500.00,
            'tax' => 2000.00,
        ]);

        $this->assertEquals(12500.50, $invoice->total_amount);
        $this->assertEquals(500.00, $invoice->discount);
        $this->assertEquals(2000.00, $invoice->tax);
    }
}