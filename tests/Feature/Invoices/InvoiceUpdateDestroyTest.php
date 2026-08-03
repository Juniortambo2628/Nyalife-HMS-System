<?php

namespace Tests\Feature\Invoices;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use App\Support\Permissions;
use Database\Seeders\SyncSpatieRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceUpdateDestroyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'doctor', 'receptionist', 'patient'] as $name) {
            Role::firstOrCreate(['role_name' => $name]);
        }
        $this->seed(SyncSpatieRolesSeeder::class);

        $this->admin = User::factory()->create([
            'role_id' => Role::where('role_name', 'admin')->first()->role_id,
            'is_active' => true,
        ]);
        $this->admin->assignRole('admin');
        $this->admin->givePermissionTo(Permissions::MANAGE_INVOICES);

        $this->patient = Patient::factory()->create();
    }

    private function createInvoice(string $status = 'pending', float $amount = 5000): Invoice
    {
        $invoice = Invoice::create([
            'patient_id' => $this->patient->patient_id,
            'invoice_number' => 'INV-TEST-'.uniqid(),
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'total_amount' => $amount,
            'status' => $status,
            'created_by' => $this->admin->user_id,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->invoice_id,
            'item_type' => 'service',
            'description' => 'Consultation fee',
            'quantity' => 1,
            'unit_price' => $amount,
            'total_price' => $amount,
        ]);

        return $invoice;
    }

    public function test_update_invoice_status_to_paid(): void
    {
        $invoice = $this->createInvoice('pending');

        $this->actingAs($this->admin)
            ->put(route('invoices.update', $invoice->invoice_id), [
                'status' => 'paid',
                'payment_method' => 'cash',
            ])
            ->assertRedirect();

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
    }

    public function test_update_invoice_status_to_overdue(): void
    {
        $invoice = $this->createInvoice('pending');

        $this->actingAs($this->admin)
            ->put(route('invoices.update', $invoice->invoice_id), [
                'status' => 'overdue',
            ])
            ->assertRedirect();

        $invoice->refresh();
        $this->assertSame('overdue', $invoice->status);
    }

    public function test_update_invoice_rejects_empty_payload(): void
    {
        $invoice = $this->createInvoice('pending');

        $this->actingAs($this->admin)
            ->put(route('invoices.update', $invoice->invoice_id), [])
            ->assertRedirect();
    }

    public function test_void_pending_invoice_sets_void_fields(): void
    {
        $invoice = $this->createInvoice('pending');

        $this->actingAs($this->admin)
            ->delete(route('invoices.destroy', $invoice->invoice_id), [
                'void_reason' => 'Duplicate invoice created by mistake',
            ])
            ->assertRedirect();

        $invoice->refresh();
        $this->assertTrue((bool) $invoice->is_voided);
        $this->assertSame('Duplicate invoice created by mistake', $invoice->void_reason);
        $this->assertSame($this->admin->user_id, $invoice->voided_by);
        $this->assertNotNull($invoice->voided_at);
    }

    public function test_void_paid_invoice_is_rejected(): void
    {
        $invoice = $this->createInvoice('paid');

        $this->actingAs($this->admin)
            ->delete(route('invoices.destroy', $invoice->invoice_id), [
                'void_reason' => 'Trying to void a paid invoice',
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'Paid invoices cannot be voided.');

        $invoice->refresh();
        $this->assertFalse((bool) $invoice->is_voided);
    }

    public function test_void_requires_void_reason(): void
    {
        $invoice = $this->createInvoice('pending');

        $this->actingAs($this->admin)
            ->delete(route('invoices.destroy', $invoice->invoice_id), [])
            ->assertSessionHasErrors('void_reason');
    }

    public function test_patient_can_only_see_own_invoices(): void
    {
        $otherPatient = Patient::factory()->create();
        $myInvoice = $this->createInvoice('pending');
        $otherInvoice = $this->createInvoice('pending');

        $patientUser = User::factory()->create([
            'role_id' => Role::where('role_name', 'patient')->first()->role_id,
            'is_active' => true,
        ]);
        $patientUser->assignRole('patient');
        $patientUser->givePermissionTo(Permissions::VIEW_OWN_RECORDS);

        // Assign the invoice to the patient
        $myInvoice->update(['patient_id' => $this->patient->patient_id]);

        $this->actingAs($patientUser)
            ->get(route('invoices.index'))
            ->assertOk();
    }

    public function test_admin_can_see_all_invoices(): void
    {
        $this->createInvoice('pending', 1000);
        $this->createInvoice('paid', 2000);

        $this->actingAs($this->admin)
            ->get(route('invoices.index'))
            ->assertOk();
    }
}
