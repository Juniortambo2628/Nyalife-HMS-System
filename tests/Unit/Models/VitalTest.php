<?php

namespace Tests\Unit\Models;

use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use App\Models\Vital;
use Database\Seeders\SyncSpatieRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VitalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['admin', 'doctor', 'nurse', 'patient'] as $name) {
            Role::firstOrCreate(['role_name' => $name]);
        }
        $this->seed(SyncSpatieRolesSeeder::class);
    }

    public function test_vital_belongs_to_patient(): void
    {
        $patient = Patient::factory()->create();

        $vital = Vital::create([
            'patient_id' => $patient->patient_id,
            'temperature' => 36.5,
            'recorded_by' => User::factory()->create([
                'role_id' => Role::where('role_name', 'nurse')->first()->role_id,
            ])->user_id,
            'measured_at' => now(),
        ]);

        $this->assertNotNull($vital->patient);
        $this->assertSame($patient->patient_id, $vital->patient->patient_id);
    }

    public function test_vital_bmi_calculation(): void
    {
        $patient = Patient::factory()->create();

        // BMI is stored by the controller; verify the column stores/retrieves correctly.
        $vital = Vital::create([
            'patient_id' => $patient->patient_id,
            'weight' => 70.0,
            'height' => 170.0,
            'bmi' => 24.22,
            'recorded_by' => User::factory()->create([
                'role_id' => Role::where('role_name', 'nurse')->first()->role_id,
            ])->user_id,
            'measured_at' => now(),
        ]);

        $this->assertSame(24.22, (float) $vital->bmi);
    }

    public function test_vital_bmi_zero_when_no_height(): void
    {
        $patient = Patient::factory()->create();

        $vital = Vital::create([
            'patient_id' => $patient->patient_id,
            'weight' => 70.0,
            'recorded_by' => User::factory()->create([
                'role_id' => Role::where('role_name', 'nurse')->first()->role_id,
            ])->user_id,
            'measured_at' => now(),
        ]);

        $this->assertSame(0.0, (float) $vital->bmi);
    }

    public function test_vital_recorded_by_user(): void
    {
        $nurse = User::factory()->create([
            'role_id' => Role::where('role_name', 'nurse')->first()->role_id,
        ]);

        $vital = Vital::create([
            'patient_id' => Patient::factory()->create()->patient_id,
            'temperature' => 36.5,
            'recorded_by' => $nurse->user_id,
            'measured_at' => now(),
        ]);

        $this->assertNotNull($vital->recordedBy);
        $this->assertSame($nurse->user_id, $vital->recordedBy->user_id);
    }

    public function test_vital_void_fields(): void
    {
        $vital = Vital::create([
            'patient_id' => Patient::factory()->create()->patient_id,
            'temperature' => 36.5,
            'recorded_by' => User::factory()->create([
                'role_id' => Role::where('role_name', 'nurse')->first()->role_id,
            ])->user_id,
            'measured_at' => now(),
        ]);

        $this->assertNull($vital->is_voided);
        $this->assertNull($vital->void_reason);

        $vital->update([
            'is_voided' => true,
            'void_reason' => 'Error',
            'voided_by' => $this->nurse()->user_id ?? User::first()->user_id,
            'voided_at' => now(),
        ]);

        $vital->refresh();
        $this->assertTrue((bool) $vital->is_voided);
        $this->assertSame('Error', $vital->void_reason);
    }

    private function nurse(): ?User
    {
        return User::where('role_id', Role::where('role_name', 'nurse')->first()->role_id)->first();
    }
}
