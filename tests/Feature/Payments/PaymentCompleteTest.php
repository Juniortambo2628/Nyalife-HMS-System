<?php

namespace Tests\Feature\Payments;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use App\Support\Permissions;
use Database\Seeders\SyncSpatieRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentCompleteTest extends TestCase
{
    use RefreshDatabase;

    protected User $receptionist;

    protected Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'doctor', 'receptionist', 'patient'] as $name) {
            Role::firstOrCreate(['role_name' => $name]);
        }
        $this->seed(SyncSpatieRolesSeeder::class);

        $this->receptionist = User::factory()->create([
            'role_id' => Role::where('role_name', 'receptionist')->first()->role_id,
            'is_active' => true,
        ]);
        $this->receptionist->assignRole('receptionist');
        $this->receptionist->givePermissionTo(Permissions::MANAGE_PAYMENTS);

        $this->patient = Patient::factory()->create();
    }

    private function createInvoice(float $total = 5000): Invoice
    {
        $invoice = Invoice::create([
            'patient_id' => $this->patient->patient_id,
            'invoice_number' => 'INV-TEST-'.uniqid(),
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'total_amount' => $total,
            'status' => 'pending',
            'created_by' => $this->receptionist->user_id,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->invoice_id,
            'item_type' => 'service',
            'description' => 'Consultation',
            'quantity' => 1,
            'unit_price' => $total,
            'total_price' => $total,
        ]);

        return $invoice;
    }

    public function test_complete_pending_payment_marks_as_completed(): void
    {
        $invoice = $this->createInvoice(5000);
        $payment = Payment::create([
            'invoice_id' => $invoice->invoice_id,
            'amount' => 5000,
            'payment_method' => 'cash',
            'payment_date' => now()->format('Y-m-d'),
            'payment_status' => 'pending',
            'status' => 'pending',
            'received_by' => $this->receptionist->user_id,
        ]);

        $this->actingAs($this->receptionist)
            ->post(route('payments.complete', $payment->payment_id))
            ->assertRedirect();

        $payment->refresh();
        $this->assertSame('completed', $payment->payment_status);
        $this->assertSame('completed', $payment->status);
    }

    public function test_complete_already_completed_payment_returns_error(): void
    {
        $invoice = $this->createInvoice(5000);
        $payment = Payment::create([
            'invoice_id' => $invoice->invoice_id,
            'amount' => 5000,
            'payment_method' => 'cash',
            'payment_date' => now()->format('Y-m-d'),
            'payment_status' => 'completed',
            'status' => 'completed',
            'received_by' => $this->receptionist->user_id,
        ]);

        $this->actingAs($this->receptionist)
            ->post(route('payments.complete', $payment->payment_id))
            ->assertRedirect()
            ->assertSessionHas('error', 'Payment is already completed.');
    }

    public function test_complete_payment_exceeding_balance_is_rejected(): void
    {
        $invoice = $this->createInvoice(3000);
        $payment = Payment::create([
            'invoice_id' => $invoice->invoice_id,
            'amount' => 5000, // More than the invoice total
            'payment_method' => 'cash',
            'payment_date' => now()->format('Y-m-d'),
            'payment_status' => 'pending',
            'status' => 'pending',
            'received_by' => $this->receptionist->user_id,
        ]);

        $this->actingAs($this->receptionist)
            ->post(route('payments.complete', $payment->payment_id))
            ->assertRedirect()
            ->assertSessionHas('error', 'Payment amount exceeds remaining invoice balance.');
    }

    public function test_complete_payment_updates_invoice_status(): void
    {
        $invoice = $this->createInvoice(5000);
        $payment = Payment::create([
            'invoice_id' => $invoice->invoice_id,
            'amount' => 5000,
            'payment_method' => 'm-pesa',
            'payment_date' => now()->format('Y-m-d'),
            'payment_status' => 'pending',
            'status' => 'pending',
            'received_by' => $this->receptionist->user_id,
        ]);

        $this->actingAs($this->receptionist)
            ->post(route('payments.complete', $payment->payment_id))
            ->assertRedirect();

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
    }

    public function test_complete_partial_payment_marks_invoice_partially_paid(): void
    {
        $invoice = $this->createInvoice(10000);
        $payment = Payment::create([
            'invoice_id' => $invoice->invoice_id,
            'amount' => 3000,
            'payment_method' => 'cash',
            'payment_date' => now()->format('Y-m-d'),
            'payment_status' => 'pending',
            'status' => 'pending',
            'received_by' => $this->receptionist->user_id,
        ]);

        $this->actingAs($this->receptionist)
            ->post(route('payments.complete', $payment->payment_id))
            ->assertRedirect();

        $invoice->refresh();
        $this->assertSame('partially_paid', $invoice->status);
    }

    public function test_unauthorized_user_cannot_complete_payment(): void
    {
        $patient = User::factory()->create([
            'role_id' => Role::where('role_name', 'patient')->first()->role_id,
            'is_active' => true,
        ]);
        $patient->assignRole('patient');

        $invoice = $this->createInvoice(5000);
        $payment = Payment::create([
            'invoice_id' => $invoice->invoice_id,
            'amount' => 5000,
            'payment_method' => 'cash',
            'payment_date' => now()->format('Y-m-d'),
            'payment_status' => 'pending',
            'status' => 'pending',
            'received_by' => $this->receptionist->user_id,
        ]);

        $this->actingAs($patient)
            ->post(route('payments.complete', $payment->payment_id))
            ->assertForbidden();
    }

    public function test_store_payment_creates_payment_record(): void
    {
        $invoice = $this->createInvoice(5000);

        $this->actingAs($this->receptionist)
            ->post(route('payments.store'), [
                'invoice_id' => $invoice->invoice_id,
                'amount' => 5000,
                'payment_method' => 'cash',
                'payment_date' => now()->format('Y-m-d'),
                'payment_status' => 'completed',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->invoice_id,
            'amount' => 5000,
            'payment_method' => 'cash',
            'payment_status' => 'completed',
        ]);
    }
}
