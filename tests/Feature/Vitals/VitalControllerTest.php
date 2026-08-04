<?php

namespace Tests\Feature\Vitals;

use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use App\Models\Vital;
use App\Support\Permissions;
use Database\Seeders\SyncSpatieRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VitalControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $nurse;

    protected Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'doctor', 'nurse', 'patient'] as $name) {
            Role::firstOrCreate(['role_name' => $name]);
        }
        $this->seed(SyncSpatieRolesSeeder::class);

        $this->nurse = User::factory()->create([
            'role_id' => Role::where('role_name', 'nurse')->first()->role_id,
            'is_active' => true,
        ]);
        $this->nurse->assignRole('nurse');
        $this->nurse->givePermissionTo(Permissions::MANAGE_VITALS);

        $this->patient = Patient::factory()->create();
    }

    public function test_store_creates_vital_with_bmi(): void
    {
        $this->actingAs($this->nurse)
            ->post(route('vitals.store'), [
                'patient_id' => $this->patient->patient_id,
                'temperature' => 36.5,
                'blood_pressure' => '120/80',
                'heart_rate' => 72,
                'respiratory_rate' => 16,
                'weight' => 65.0,
                'height' => 165.0,
                'oxygen_saturation' => 98,
            ])
            ->assertRedirect();

        $vital = Vital::where('patient_id', $this->patient->patient_id)->first();
        $this->assertNotNull($vital);
        $this->assertSame(36.5, (float) $vital->temperature);
        $this->assertSame('120/80', $vital->blood_pressure);
        $this->assertSame(72, $vital->heart_rate);

        // BMI = 65 / (165/100)^2 = 65 / 2.7225 ≈ 23.87
        $expectedBmi = round(65.0 / pow(1.65, 2), 2);
        $this->assertSame($expectedBmi, (float) $vital->bmi);
    }

    public function test_store_creates_vital_without_bmi_when_height_missing(): void
    {
        $this->actingAs($this->nurse)
            ->post(route('vitals.store'), [
                'patient_id' => $this->patient->patient_id,
                'temperature' => 37.0,
                'heart_rate' => 80,
                'weight' => 70.0,
            ])
            ->assertRedirect();

        $vital = Vital::where('patient_id', $this->patient->patient_id)->first();
        $this->assertNotNull($vital);
        $this->assertNull($vital->bmi);
    }

    public function test_store_creates_vital_without_bmi_when_weight_missing(): void
    {
        $this->actingAs($this->nurse)
            ->post(route('vitals.store'), [
                'patient_id' => $this->patient->patient_id,
                'temperature' => 37.0,
                'height' => 170.0,
            ])
            ->assertRedirect();

        $vital = Vital::where('patient_id', $this->patient->patient_id)->first();
        $this->assertNull($vital->bmi);
    }

    public function test_store_sets_recorded_by_and_measured_at(): void
    {
        $this->actingAs($this->nurse)
            ->post(route('vitals.store'), [
                'patient_id' => $this->patient->patient_id,
                'temperature' => 36.8,
            ])
            ->assertRedirect();

        $vital = Vital::where('patient_id', $this->patient->patient_id)->first();
        $this->assertSame($this->nurse->user_id, $vital->recorded_by);
        $this->assertNotNull($vital->measured_at);
    }

    public function test_store_validation_rejects_out_of_range_values(): void
    {
        $this->actingAs($this->nurse)
            ->post(route('vitals.store'), [
                'patient_id' => $this->patient->patient_id,
                'temperature' => 50, // Max is 45
                'heart_rate' => 400, // Max is 300
                'weight' => 600, // Max is 500
                'oxygen_saturation' => 120, // Max is 100
            ])
            ->assertSessionHasErrors(['temperature', 'heart_rate', 'weight', 'oxygen_saturation']);
    }

    public function test_store_validation_requires_patient_id(): void
    {
        $this->actingAs($this->nurse)
            ->post(route('vitals.store'), [
                'patient_id' => '',
                'temperature' => 36.5,
            ])
            ->assertSessionHasErrors('patient_id');
    }

    public function test_destroy_soft_voids_vital(): void
    {
        $vital = Vital::create([
            'patient_id' => $this->patient->patient_id,
            'temperature' => 36.5,
            'recorded_by' => $this->nurse->user_id,
            'measured_at' => now(),
        ]);

        $this->actingAs($this->nurse)
            ->delete(route('vitals.destroy', $vital), [
                'void_reason' => 'Incorrect measurement',
            ])
            ->assertRedirect();

        $vital->refresh();
        $this->assertTrue((bool) $vital->is_voided);
        $this->assertSame('Incorrect measurement', $vital->void_reason);
        $this->assertSame($this->nurse->user_id, $vital->voided_by);
        $this->assertNotNull($vital->voided_at);

        // Should still exist in DB (soft void, not hard delete)
        $this->assertDatabaseHas('vital_signs', ['vital_id' => $vital->vital_id]);
    }

    public function test_destroy_requires_void_reason(): void
    {
        $vital = Vital::create([
            'patient_id' => $this->patient->patient_id,
            'temperature' => 36.5,
            'recorded_by' => $this->nurse->user_id,
            'measured_at' => now(),
        ]);

        $this->actingAs($this->nurse)
            ->delete(route('vitals.destroy', $vital), [])
            ->assertSessionHasErrors('void_reason');
    }

    public function test_latest_returns_most_recent_vital(): void
    {
        Vital::create([
            'patient_id' => $this->patient->patient_id,
            'temperature' => 36.5,
            'recorded_by' => $this->nurse->user_id,
            'measured_at' => now()->subDay(),
        ]);
        $latest = Vital::create([
            'patient_id' => $this->patient->patient_id,
            'temperature' => 37.2,
            'recorded_by' => $this->nurse->user_id,
            'measured_at' => now(),
        ]);

        $this->actingAs($this->nurse)
            ->get(route('patients.latest-vitals', $this->patient->patient_id))
            ->assertOk()
            ->assertJsonFragment(['vital_id' => $latest->vital_id]);
    }
}
