<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Medication;
use App\Models\PharmacyPurchaseOrder;
use App\Models\Role;
use App\Models\User;
use App\Support\Permissions;
use Database\Seeders\SyncSpatieRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PharmacyControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $pharmacist;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'doctor', 'nurse', 'pharmacist'] as $name) {
            Role::firstOrCreate(['role_name' => $name]);
        }
        $this->seed(SyncSpatieRolesSeeder::class);

        $this->pharmacist = User::factory()->create([
            'role_id' => Role::where('role_name', 'pharmacist')->first()->role_id,
            'is_active' => true,
        ]);
        $this->pharmacist->assignRole('pharmacist');
        $this->pharmacist->givePermissionTo(Permissions::MANAGE_PHARMACY);
    }

    public function test_store_medicine_creates_medication(): void
    {
        $this->actingAs($this->pharmacist)
            ->post(route('pharmacy.medicines.store'), [
                'medication_name' => 'Ibuprofen 400mg',
                'medication_type' => 'tablet',
                'strength' => '400mg',
                'unit' => 'tablet',
                'price_per_unit' => 25.00,
                'description' => 'NSAID pain reliever',
                'expiry_date' => now()->addYear()->format('Y-m-d'),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('medications', [
            'medication_name' => 'Ibuprofen 400mg',
            'medication_type' => 'tablet',
            'stock_quantity' => 0, // Store always starts at 0
        ]);
    }

    public function test_store_medicine_initializes_stock_at_zero(): void
    {
        $this->actingAs($this->pharmacist)
            ->post(route('pharmacy.medicines.store'), [
                'medication_name' => 'Paracetamol',
                'medication_type' => 'tablet',
                'strength' => '500mg',
                'unit' => 'tablet',
                'price_per_unit' => 10.00,
            ])
            ->assertRedirect();

        $med = Medication::where('medication_name', 'Paracetamol')->first();
        $this->assertNotNull($med);
        $this->assertSame(0, $med->stock_quantity);
    }

    public function test_store_medicine_validation_fails_without_required_fields(): void
    {
        $this->actingAs($this->pharmacist)
            ->post(route('pharmacy.medicines.store'), [
                'medication_name' => '',
                'medication_type' => '',
                'strength' => '',
                'unit' => '',
                'price_per_unit' => '',
            ])
            ->assertSessionHasErrors(['medication_name', 'medication_type', 'strength', 'unit', 'price_per_unit']);
    }

    public function test_update_stock_adds_quantity(): void
    {
        $medication = Medication::factory()->create(['stock_quantity' => 10]);

        $this->actingAs($this->pharmacist)
            ->post(route('pharmacy.inventory.update-stock'), [
                'medication_id' => $medication->medication_id,
                'type' => 'add',
                'quantity' => 50,
            ])
            ->assertRedirect();

        $medication->refresh();
        $this->assertSame(60, $medication->stock_quantity);
    }

    public function test_update_stock_sets_exact_quantity(): void
    {
        $medication = Medication::factory()->create(['stock_quantity' => 100]);

        $this->actingAs($this->pharmacist)
            ->post(route('pharmacy.inventory.update-stock'), [
                'medication_id' => $medication->medication_id,
                'type' => 'set',
                'quantity' => 25,
            ])
            ->assertRedirect();

        $medication->refresh();
        $this->assertSame(25, $medication->stock_quantity);
    }

    public function test_update_stock_updates_expiry_date(): void
    {
        $medication = Medication::factory()->create([
            'expiry_date' => now()->addYear()->format('Y-m-d'),
        ]);

        $newExpiry = now()->addYears(2)->format('Y-m-d');
        $this->actingAs($this->pharmacist)
            ->post(route('pharmacy.inventory.update-stock'), [
                'medication_id' => $medication->medication_id,
                'type' => 'set',
                'quantity' => 50,
                'expiry_date' => $newExpiry,
            ])
            ->assertRedirect();

        $medication->refresh();
        $this->assertSame($newExpiry, $medication->expiry_date);
    }

    public function test_update_stock_fails_for_missing_medication(): void
    {
        $this->actingAs($this->pharmacist)
            ->post(route('pharmacy.inventory.update-stock'), [
                'medication_id' => 99999,
                'type' => 'add',
                'quantity' => 10,
            ])
            ->assertSessionHasErrors('medication_id');
    }

    public function test_store_purchase_order_creates_po(): void
    {
        $medication = Medication::factory()->create();

        $this->actingAs($this->pharmacist)
            ->post(route('pharmacy.po.store'), [
                'medication_id' => $medication->medication_id,
                'quantity' => 100,
                'supplier_name' => 'PharmaCorp Ltd',
                'estimated_cost' => 15000,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('pharmacy_purchase_orders', [
            'medication_id' => $medication->medication_id,
            'quantity' => 100,
            'supplier_name' => 'PharmaCorp Ltd',
            'status' => 'pending',
        ]);
    }

    public function test_store_purchase_order_generates_order_number(): void
    {
        $medication = Medication::factory()->create();

        $this->actingAs($this->pharmacist)
            ->post(route('pharmacy.po.store'), [
                'medication_id' => $medication->medication_id,
                'quantity' => 50,
                'supplier_name' => 'MedSupply Inc',
                'estimated_cost' => 8000,
            ])
            ->assertRedirect();

        $po = PharmacyPurchaseOrder::where('medication_id', $medication->medication_id)->first();
        $this->assertNotNull($po);
        $this->assertStringStartsWith('PO-', $po->order_number);
        $this->assertSame('pending', $po->status);
    }

    public function test_store_purchase_order_validation_fails_without_required_fields(): void
    {
        $this->actingAs($this->pharmacist)
            ->post(route('pharmacy.po.store'), [
                'medication_id' => '',
                'quantity' => '',
                'supplier_name' => '',
                'estimated_cost' => '',
            ])
            ->assertSessionHasErrors(['medication_id', 'quantity', 'supplier_name', 'estimated_cost']);
    }

    public function test_update_po_status_to_received_increments_stock(): void
    {
        $medication = Medication::factory()->create(['stock_quantity' => 20]);
        $po = PharmacyPurchaseOrder::factory()->create([
            'medication_id' => $medication->medication_id,
            'quantity' => 100,
            'status' => 'pending',
        ]);

        $this->actingAs($this->pharmacist)
            ->put(route('pharmacy.po.update-status', $po->id), [
                'status' => 'received',
            ])
            ->assertRedirect();

        $po->refresh();
        $medication->refresh();

        $this->assertSame('received', $po->status);
        $this->assertSame(120, $medication->stock_quantity);
    }

    public function test_update_po_status_to_ordered_does_not_affect_stock(): void
    {
        $medication = Medication::factory()->create(['stock_quantity' => 20]);
        $po = PharmacyPurchaseOrder::factory()->create([
            'medication_id' => $medication->medication_id,
            'quantity' => 100,
            'status' => 'pending',
        ]);

        $this->actingAs($this->pharmacist)
            ->put(route('pharmacy.po.update-status', $po->id), [
                'status' => 'ordered',
            ])
            ->assertRedirect();

        $po->refresh();
        $medication->refresh();

        $this->assertSame('ordered', $po->status);
        $this->assertSame(20, $medication->stock_quantity);
    }

    public function test_update_po_status_to_cancelled_does_not_affect_stock(): void
    {
        $medication = Medication::factory()->create(['stock_quantity' => 20]);
        $po = PharmacyPurchaseOrder::factory()->create([
            'medication_id' => $medication->medication_id,
            'quantity' => 100,
            'status' => 'ordered',
        ]);

        $this->actingAs($this->pharmacist)
            ->put(route('pharmacy.po.update-status', $po->id), [
                'status' => 'cancelled',
            ])
            ->assertRedirect();

        $medication->refresh();
        $this->assertSame(20, $medication->stock_quantity);
    }

    public function test_update_po_status_rejects_invalid_status(): void
    {
        $po = PharmacyPurchaseOrder::factory()->create(['status' => 'pending']);

        $this->actingAs($this->pharmacist)
            ->put(route('pharmacy.po.update-status', $po->id), [
                'status' => 'invalid_status',
            ])
            ->assertSessionHasErrors('status');
    }

    public function test_destroy_medicine_removes_medication(): void
    {
        $medication = Medication::factory()->create();

        $this->actingAs($this->pharmacist)
            ->delete(route('pharmacy.medicines.destroy', $medication->medication_id))
            ->assertRedirect();

        $this->assertDatabaseMissing('medications', [
            'medication_id' => $medication->medication_id,
        ]);
    }

    public function test_update_medicine_modifies_fields(): void
    {
        $medication = Medication::factory()->create([
            'medication_name' => 'Old Name',
            'price_per_unit' => 100,
        ]);

        $this->actingAs($this->pharmacist)
            ->put(route('pharmacy.medicines.update', $medication->medication_id), [
                'medication_name' => 'New Name',
                'medication_type' => 'tablet',
                'strength' => '500mg',
                'unit' => 'tablet',
                'price_per_unit' => 150,
            ])
            ->assertRedirect();

        $medication->refresh();
        $this->assertSame('New Name', $medication->medication_name);
        $this->assertSame(150.0, (float) $medication->price_per_unit);
    }

    public function test_pharmacist_without_permission_cannot_access(): void
    {
        $noPerm = User::factory()->create([
            'role_id' => Role::where('role_name', 'pharmacist')->first()->role_id,
            'is_active' => true,
        ]);
        $noPerm->syncRoles([]);

        $this->actingAs($noPerm)
            ->get(route('pharmacy.inventory'))
            ->assertForbidden();
    }
}
