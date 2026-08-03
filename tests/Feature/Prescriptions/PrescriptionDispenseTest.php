<?php

namespace Tests\Feature\Prescriptions;

use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use App\Support\Permissions;
use Database\Seeders\SyncSpatieRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrescriptionDispenseTest extends TestCase
{
    use RefreshDatabase;

    protected User $pharmacist;

    protected Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'doctor', 'nurse', 'pharmacist', 'patient'] as $name) {
            Role::firstOrCreate(['role_name' => $name]);
        }
        $this->seed(SyncSpatieRolesSeeder::class);

        $this->pharmacist = User::factory()->create([
            'role_id' => Role::where('role_name', 'pharmacist')->first()->role_id,
            'is_active' => true,
        ]);
        $this->pharmacist->assignRole('pharmacist');
        $this->pharmacist->givePermissionTo(Permissions::MANAGE_PRESCRIPTIONS);

        $this->patient = Patient::factory()->create();
    }

    private function createPrescription(string $status = 'pending'): Prescription
    {
        $doctor = User::factory()->create([
            'role_id' => Role::where('role_name', 'doctor')->first()->role_id,
            'is_active' => true,
        ]);
        $doctor->assignRole('doctor');

        $staff = Staff::factory()->create(['user_id' => $doctor->user_id]);

        return Prescription::create([
            'patient_id' => $this->patient->patient_id,
            'prescribed_by' => $doctor->user_id,
            'prescription_date' => now()->format('Y-m-d'),
            'status' => $status,
            'prescription_number' => 'RX-TEST-'.uniqid(),
        ]);
    }

    public function test_dispense_marks_pending_prescription_as_dispensed(): void
    {
        $rx = $this->createPrescription('pending');

        $this->actingAs($this->pharmacist)
            ->post(route('prescriptions.dispense', $rx->prescription_id))
            ->assertRedirect();

        $rx->refresh();
        $this->assertSame('dispensed', $rx->status);
        $this->assertNotNull($rx->dispensed_at);
        $this->assertSame($this->pharmacist->user_id, $rx->dispensed_by);
    }

    public function test_dispense_rejects_already_dispensed_prescription(): void
    {
        $rx = $this->createPrescription('dispensed');

        $this->actingAs($this->pharmacist)
            ->post(route('prescriptions.dispense', $rx->prescription_id))
            ->assertRedirect()
            ->assertSessionHasErrors('error');

        $rx->refresh();
        $this->assertSame('dispensed', $rx->status);
    }

    public function test_dispense_rejects_cancelled_prescription(): void
    {
        $rx = $this->createPrescription('cancelled');

        $this->actingAs($this->pharmacist)
            ->post(route('prescriptions.dispense', $rx->prescription_id))
            ->assertRedirect()
            ->assertSessionHasErrors('error');
    }

    public function test_void_prescription_sets_void_fields(): void
    {
        $rx = $this->createPrescription('pending');

        $this->actingAs($this->pharmacist)
            ->delete(route('prescriptions.destroy', $rx->prescription_id), [
                'void_reason' => 'Patient no longer needs this medication',
            ])
            ->assertRedirect();

        $rx->refresh();
        $this->assertTrue((bool) $rx->is_voided);
        $this->assertSame('Patient no longer needs this medication', $rx->void_reason);
        $this->assertSame($this->pharmacist->user_id, $rx->voided_by);
        $this->assertNotNull($rx->voided_at);
    }

    public function test_void_prescription_requires_void_reason(): void
    {
        $rx = $this->createPrescription('pending');

        $this->actingAs($this->pharmacist)
            ->delete(route('prescriptions.destroy', $rx->prescription_id), [])
            ->assertSessionHasErrors('void_reason');
    }
}
