<?php

namespace Tests\Unit\Services;

use App\Models\Invoice;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Role;
use App\Models\User;
use App\Services\PrescriptionService;
use Database\Seeders\RolePermissionsSeeder;
use Database\Seeders\SyncSpatieRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrescriptionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create legacy roles so SyncSpatieRolesSeeder can sync them
        Role::firstOrCreate(['role_name' => 'admin']);
        Role::firstOrCreate(['role_name' => 'doctor']);
        Role::firstOrCreate(['role_name' => 'nurse']);
        Role::firstOrCreate(['role_name' => 'receptionist']);
        Role::firstOrCreate(['role_name' => 'lab_technician']);
        Role::firstOrCreate(['role_name' => 'pharmacist']);
        Role::firstOrCreate(['role_name' => 'patient']);

        // Seed roles and permissions
        $this->seed(SyncSpatieRolesSeeder::class);
        $this->seed(RolePermissionsSeeder::class);
    }

    public function test_create_creates_prescription_with_items(): void
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');
        $patient = Patient::factory()->create();
        $medication1 = Medication::factory()->create(['stock_quantity' => 100, 'price_per_unit' => 50]);
        $medication2 = Medication::factory()->create(['stock_quantity' => 50, 'price_per_unit' => 100]);

        $data = [
            'patient_id' => $patient->patient_id,
            'consultation_id' => null,
            'prescription_date' => now()->format('Y-m-d'),
            'notes' => 'Test prescription',
            'items' => [
                [
                    'medication_id' => $medication1->medication_id,
                    'dosage' => '1 tablet',
                    'frequency' => 'twice daily',
                    'duration' => '7 days',
                ],
                [
                    'medication_id' => $medication2->medication_id,
                    'dosage' => '5ml',
                    'frequency' => 'three times daily',
                    'duration' => '5 days',
                ],
            ],
        ];

        $this->actingAs($user);
        $prescription = PrescriptionService::create($data);

        $this->assertInstanceOf(Prescription::class, $prescription);
        $this->assertCount(2, $prescription->items);
        $this->assertStringStartsWith('RX-', $prescription->prescription_number);
        $this->assertEquals('pending', $prescription->status);
        $this->assertEquals($user->user_id, $prescription->prescribed_by);

        // Check stock deduction
        $medication1->refresh();
        $medication2->refresh();
        // twice daily * 7 = 14
        $this->assertEquals(86, $medication1->stock_quantity);
        // three times daily * 5 = 15
        $this->assertEquals(35, $medication2->stock_quantity);

        // Check invoice was created
        $invoice = Invoice::where('patient_id', $patient->patient_id)->latest()->first();
        $this->assertNotNull($invoice);
        $this->assertStringStartsWith('INV-', $invoice->invoice_number);
        $this->assertCount(2, $invoice->items);
        $this->assertEquals(
            (50 * 14) + (100 * 15),
            $invoice->total_amount
        );
    }

    public function test_create_handles_items_without_medication(): void
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');
        $patient = Patient::factory()->create();

        $data = [
            'patient_id' => $patient->patient_id,
            'prescription_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'medication_id' => null,
                    'dosage' => 'N/A',
                    'frequency' => 'as needed',
                    'duration' => '1 day',
                ],
            ],
        ];

        $this->actingAs($user);
        $prescription = PrescriptionService::create($data);

        $this->assertCount(1, $prescription->items);
        $this->assertNull($prescription->items->first()->medication_id);

        // No invoice should be created for items without medication
        $this->assertDatabaseMissing('invoices', [
            'notes' => 'Auto-generated from prescription '.$prescription->prescription_number,
        ]);
    }

    public function test_update_updates_prescription_and_items(): void
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');
        $patient = Patient::factory()->create();

        $prescription = Prescription::factory()->create([
            'patient_id' => $patient->patient_id,
            'status' => 'pending',
        ]);
        PrescriptionItem::factory()->count(2)->create(['prescription_id' => $prescription->prescription_id]);

        $newMedication = Medication::factory()->create(['stock_quantity' => 100, 'price_per_unit' => 200]);

        $data = [
            'prescription_date' => now()->format('Y-m-d'),
            'notes' => 'Updated notes',
            'items' => [
                [
                    'medication_id' => $newMedication->medication_id,
                    'dosage' => '2 tablets',
                    'frequency' => 'once daily',
                    'duration' => '10 days',
                ],
            ],
        ];

        $this->actingAs($user);
        $updated = PrescriptionService::update($prescription, $data);

        $this->assertEquals('Updated notes', $updated->notes);
        $this->assertCount(1, $updated->items);
        $this->assertEquals($newMedication->medication_id, $updated->items->first()->medication_id);
    }

    public function test_dispense_marks_as_dispensed(): void
    {
        $user = User::factory()->create();
        $user->assignRole('pharmacist');
        $patient = Patient::factory()->create();

        $prescription = Prescription::factory()->create([
            'patient_id' => $patient->patient_id,
            'status' => 'pending',
        ]);

        $this->actingAs($user);
        $dispensed = PrescriptionService::dispense($prescription);

        $this->assertEquals('dispensed', $dispensed->status);
        $this->assertEquals($user->user_id, $dispensed->dispensed_by);
        $this->assertNotNull($dispensed->dispensed_at);
    }

    public function test_parse_frequency_to_daily(): void
    {
        $this->assertEquals(1, PrescriptionService::parseFrequencyToDaily('once daily'));
        $this->assertEquals(2, PrescriptionService::parseFrequencyToDaily('twice daily'));
        $this->assertEquals(3, PrescriptionService::parseFrequencyToDaily('three times daily'));
        $this->assertEquals(4, PrescriptionService::parseFrequencyToDaily('four times daily'));
        $this->assertEquals(4, PrescriptionService::parseFrequencyToDaily('every 6 hours'));
        $this->assertEquals(3, PrescriptionService::parseFrequencyToDaily('every 8 hours'));
        $this->assertEquals(2, PrescriptionService::parseFrequencyToDaily('every 12 hours'));
        $this->assertEquals(1, PrescriptionService::parseFrequencyToDaily('at bedtime'));
        $this->assertEquals(1, PrescriptionService::parseFrequencyToDaily('as needed'));
        $this->assertEquals(1, PrescriptionService::parseFrequencyToDaily('unknown'));
    }
}
