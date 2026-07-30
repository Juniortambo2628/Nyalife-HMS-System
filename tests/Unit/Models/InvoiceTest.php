<?php

namespace Tests\Unit\Models;

use App\Models\Consultation;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

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
            ->has(InvoiceItem::factory()->count(4), 'items')
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
            'voided_by' => User::factory()->create()->user_id,
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

        $allForThisTest = Invoice::withVoided()->whereIn('invoice_number', ['INV-TEST-VOID-1', 'INV-TEST-VOID-2'])->get();
        $this->assertCount(2, $allForThisTest, 'Both invoices should exist');

        $nonVoidedForThisTest = Invoice::whereIn('invoice_number', ['INV-TEST-VOID-1', 'INV-TEST-VOID-2'])->get();
        $this->assertCount(1, $nonVoidedForThisTest, 'Only non-voided invoice should be visible without withVoided');
        $this->assertEquals($activeInvoice->invoice_id, $nonVoidedForThisTest->first()->invoice_id);

        $this->assertTrue($allForThisTest->contains('invoice_id', $voidedInvoice->invoice_id));
        $this->assertTrue($allForThisTest->contains('invoice_id', $activeInvoice->invoice_id));
    }

    public function test_invoice_void_and_unvoid_methods(): void
    {
        $user = User::factory()->create();
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

        $pendingResult = Invoice::status('pending')->where('invoice_number', 'INV-TEST-SCOPE-1')->first();
        $this->assertNotNull($pendingResult);
        $this->assertEquals($pendingInvoice->invoice_id, $pendingResult->invoice_id);

        $paidResult = Invoice::status('paid')->where('invoice_number', 'INV-TEST-SCOPE-2')->first();
        $this->assertNotNull($paidResult);
        $this->assertEquals($paidInvoice->invoice_id, $paidResult->invoice_id);

        $partiallyPaidResult = Invoice::status('partially_paid')->where('invoice_number', 'INV-TEST-SCOPE-3')->first();
        $this->assertNotNull($partiallyPaidResult);
        $this->assertEquals($partiallyPaidInvoice->invoice_id, $partiallyPaidResult->invoice_id);
    }

    public function test_invoice_search_by_patient_or_number(): void
    {
        $patient = Patient::factory()->create([
            'user_id' => User::factory()->create(['first_name' => 'SearchTest', 'last_name' => 'InvoiceUser', 'email' => 'searchtest.invoice.'.uniqid().'@example.com'])->user_id,
        ]);
        $invoice = Invoice::factory()->create([
            'patient_id' => $patient->patient_id,
            'invoice_number' => 'INV-SEARCH-'.uniqid(),
        ]);

        $results = Invoice::searchByPatientOrNumber('SearchTest')->get();
        $this->assertGreaterThan(0, $results->count());
        $this->assertTrue($results->contains('invoice_id', $invoice->invoice_id));

        $results = Invoice::searchByPatientOrNumber($invoice->invoice_number)->get();
        $this->assertGreaterThan(0, $results->count());
        $this->assertTrue($results->contains('invoice_id', $invoice->invoice_id));
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
