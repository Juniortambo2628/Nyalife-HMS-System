<?php

namespace Tests\Feature\Consultations;

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use App\Support\ParityValue;
use App\Support\Permissions;
use Database\Seeders\SyncSpatieRolesSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * End-to-end tests proving the controller store succeeds even when the
 * consultations.parity column is a legacy INT.
 *
 * Uses DatabaseMigrations because tests ALTER TABLE (DDL), which auto-commits
 * in MySQL and breaks RefreshDatabase's transaction wrapping.
 */
class ParityIntColumnStoreTest extends TestCase
{
    use DatabaseMigrations;

    protected User $doctor;

    protected Patient $patient;

    private int $staffId;

    protected function setUp(): void
    {
        parent::setUp();
        ParityValue::flushCache();

        foreach (['admin', 'doctor', 'nurse', 'receptionist'] as $name) {
            Role::firstOrCreate(['role_name' => $name]);
        }
        $this->seed(SyncSpatieRolesSeeder::class);

        $this->doctor = User::factory()->create([
            'role_id' => Role::where('role_name', 'doctor')->first()->role_id,
            'is_active' => true,
        ]);
        $this->doctor->assignRole('doctor');
        $this->doctor->givePermissionTo(Permissions::MANAGE_CONSULTATIONS);

        $staff = Staff::factory()->create([
            'user_id' => $this->doctor->user_id,
        ]);
        $this->staffId = $staff->staff_id;

        $this->patient = Patient::factory()->create();
    }

    protected function tearDown(): void
    {
        DB::statement('ALTER TABLE consultations MODIFY parity VARCHAR(50) NULL');
        ParityValue::flushCache();
        parent::tearDown();
    }

    /**
     * The production incident: doctor enters "Para 0+0" into a legacy INT
     * parity column. Without ParityValue::normaliseForColumn the store
     * would throw 1366 and the consultation is never saved.
     */
    public function test_store_succeeds_with_para_notation_on_int_column(): void
    {
        DB::statement('ALTER TABLE consultations MODIFY parity INT NULL');

        $payload = $this->basePayload(['parity' => 'Para 0+0']);
        $this->actingAs($this->doctor)
            ->post(route('consultations.store'), $payload)
            ->assertRedirect(route('dashboard'));

        $row = Consultation::where('patient_id', $this->patient->patient_id)->first();
        $this->assertNotNull($row);
        $this->assertSame('completed', $row->consultation_status);
        // ParityValue extracts leading digits: "Para 0+0" → 0
        $this->assertSame(0, $row->parity);
    }

    public function test_store_succeeds_with_gp_notation_on_int_column(): void
    {
        DB::statement('ALTER TABLE consultations MODIFY parity INT NULL');

        $payload = $this->basePayload(['parity' => 'G1P0']);
        $this->actingAs($this->doctor)
            ->post(route('consultations.store'), $payload)
            ->assertRedirect(route('dashboard'));

        $row = Consultation::where('patient_id', $this->patient->patient_id)->first();
        $this->assertSame(1, $row->parity);
    }

    public function test_store_succeeds_with_plus_notation_on_int_column(): void
    {
        DB::statement('ALTER TABLE consultations MODIFY parity INT NULL');

        $payload = $this->basePayload(['parity' => '2+0']);
        $this->actingAs($this->doctor)
            ->post(route('consultations.store'), $payload)
            ->assertRedirect(route('dashboard'));

        $row = Consultation::where('patient_id', $this->patient->patient_id)->first();
        $this->assertSame(2, $row->parity);
    }

    public function test_store_succeeds_with_null_parity_on_int_column(): void
    {
        DB::statement('ALTER TABLE consultations MODIFY parity INT NULL');

        $payload = $this->basePayload(['parity' => null]);
        $this->actingAs($this->doctor)
            ->post(route('consultations.store'), $payload)
            ->assertRedirect(route('dashboard'));

        $row = Consultation::where('patient_id', $this->patient->patient_id)->first();
        $this->assertNotNull($row);
        $this->assertNull($row->parity);
    }

    public function test_store_succeeds_with_parity_on_varchar_column(): void
    {
        // Default varchar(50) schema — parity is stored as-is.
        $payload = $this->basePayload(['parity' => 'G2P1+0']);
        $this->actingAs($this->doctor)
            ->post(route('consultations.store'), $payload)
            ->assertRedirect(route('dashboard'));

        $row = Consultation::where('patient_id', $this->patient->patient_id)->first();
        $this->assertSame('G2P1+0', $row->parity);
    }

    private function basePayload(array $overrides = []): array
    {
        return array_merge([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->staffId,
            'appointment_id' => null,
            'consultation_date' => now()->format('Y-m-d H:i:s'),
            'chief_complaint' => 'Routine visit',
            'priority' => 'normal',
            'is_walk_in' => true,
            'status' => 'completed',
            'vital_signs' => [
                'blood_pressure' => '120/80',
                'heart_rate' => 72,
            ],
            'requested_procedures' => [],
            'requested_labs' => [],
            'requested_service_items' => [],
        ], $overrides);
    }
}
