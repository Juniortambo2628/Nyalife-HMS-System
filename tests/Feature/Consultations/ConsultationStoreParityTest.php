<?php

namespace Tests\Feature\Consultations;

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use App\Support\Permissions;
use Database\Seeders\SyncSpatieRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConsultationStoreParityTest extends TestCase
{
    use RefreshDatabase;

    protected User $doctor;

    protected Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

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

    private int $staffId;

    public function test_consultation_store_accepts_obstetric_parity_notation(): void
    {
        $payload = $this->basePayload([
            'parity' => '2+0',
            'status' => 'completed',
        ]);

        $this->actingAs($this->doctor)
            ->post(route('consultations.store'), $payload)
            ->assertRedirect(route('dashboard'));

        $row = Consultation::where('patient_id', $this->patient->patient_id)->first();
        $this->assertNotNull($row);
        $this->assertSame('2+0', $row->parity);
        $this->assertSame('completed', $row->consultation_status);
    }

    public function test_consultation_store_handles_gp_notation(): void
    {
        $payload = $this->basePayload([
            'parity' => 'G1P0',
            'status' => 'completed',
        ]);

        $this->actingAs($this->doctor)
            ->post(route('consultations.store'), $payload)
            ->assertRedirect(route('dashboard'));

        $row = Consultation::where('patient_id', $this->patient->patient_id)->first();
        $this->assertSame('G1P0', $row->parity);
    }

    public function test_consultation_store_truncates_unreasonably_long_parity(): void
    {
        $longValue = str_repeat('G1P0+', 30); // 150 chars

        $payload = $this->basePayload([
            'parity' => $longValue,
            'status' => 'completed',
        ]);

        $this->actingAs($this->doctor)
            ->post(route('consultations.store'), $payload)
            ->assertRedirect(route('dashboard'));

        $row = Consultation::where('patient_id', $this->patient->patient_id)->first();
        $this->assertLessThanOrEqual(50, mb_strlen((string) $row->parity));
    }

    public function test_consultation_store_survives_narrow_legacy_parity_column(): void
    {
        // Simulate a production DB that still has the legacy narrow parity
        // column (varchar(3)) so we can prove the controller-level guard
        // prevents the silent truncation that breaks the whole transaction.
        // First clear existing rows so the ALTER doesn't itself warn.
        DB::table('consultations')->update(['parity' => null]);
        DB::statement('ALTER TABLE consultations MODIFY parity VARCHAR(3) NULL');

        $payload = $this->basePayload([
            'parity' => '2+0', // 3 chars fits in the narrow column exactly
            'status' => 'completed',
        ]);

        $this->actingAs($this->doctor)
            ->post(route('consultations.store'), $payload)
            ->assertRedirect(route('dashboard'));

        $row = Consultation::where('patient_id', $this->patient->patient_id)->first();
        $this->assertNotNull($row, 'Consultation should be persisted even on a narrow legacy column.');
        $this->assertSame('completed', $row->consultation_status);
        $this->assertSame('2+0', $row->parity);
    }

    public function test_alter_parity_migration_is_idempotent_on_wide_column(): void
    {
        // Already at varchar(50) — running the migration must be a no-op.
        DB::statement('ALTER TABLE consultations MODIFY parity VARCHAR(50) NULL');

        $migration = require __DIR__.'/../../../database/migrations/2026_07_16_173900_alter_parity_column_in_consultations_table.php';
        $migration->up();

        $row = DB::selectOne(
            'SELECT CHARACTER_MAXIMUM_LENGTH AS len
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = "consultations"
               AND COLUMN_NAME = "parity"',
            [DB::connection()->getDatabaseName()]
        );
        $this->assertSame(50, (int) $row->len);
    }

    public function test_alter_parity_migration_widens_narrow_column(): void
    {
        // Start with the legacy narrow column.
        DB::statement('ALTER TABLE consultations MODIFY parity VARCHAR(3) NULL');

        $migration = require __DIR__.'/../../../database/migrations/2026_07_16_173900_alter_parity_column_in_consultations_table.php';
        $migration->up();

        $row = DB::selectOne(
            'SELECT CHARACTER_MAXIMUM_LENGTH AS len
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = "consultations"
               AND COLUMN_NAME = "parity"',
            [DB::connection()->getDatabaseName()]
        );
        $this->assertSame(50, (int) $row->len);
    }

    public function test_patient_history_returns_latest_completed_consultation(): void
    {
        // Two completed + one in_progress. Clinical summary should pull from
        // the most recent completed one.
        $completed1 = Consultation::create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->staffId,
            'consultation_date' => now()->subDays(10),
            'consultation_status' => 'completed',
            'past_medical_history' => 'Asthma',
            'created_by' => $this->doctor->user_id,
        ]);
        Consultation::create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->staffId,
            'consultation_date' => now()->subDays(2),
            'consultation_status' => 'in_progress',
            'past_medical_history' => 'Asthma + new finding',
            'created_by' => $this->doctor->user_id,
        ]);
        $completed2 = Consultation::create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->staffId,
            'consultation_date' => now()->subDay(),
            'consultation_status' => 'completed',
            'past_medical_history' => 'Diabetes',
            'created_by' => $this->doctor->user_id,
        ]);

        $latest = Consultation::latestHistoryForPatient($this->patient->patient_id);
        $this->assertNotNull($latest);
        $this->assertSame($completed2->consultation_id, $latest->consultation_id);
        $this->assertSame('Diabetes', $latest->past_medical_history);
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
