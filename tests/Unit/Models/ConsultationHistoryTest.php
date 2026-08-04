<?php

namespace Tests\Unit\Models;

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Database\Seeders\SyncSpatieRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultationHistoryTest extends TestCase
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

    public function test_latest_history_for_patient_returns_completed(): void
    {
        $patient = Patient::factory()->create();
        $doctor = User::factory()->create([
            'role_id' => Role::where('role_name', 'doctor')->first()->role_id,
        ]);
        $staff = Staff::factory()->create(['user_id' => $doctor->user_id]);

        Consultation::create([
            'patient_id' => $patient->patient_id,
            'doctor_id' => $staff->staff_id,
            'consultation_date' => now()->subDays(5),
            'consultation_status' => 'completed',
            'past_medical_history' => 'Old history',
            'created_by' => $doctor->user_id,
        ]);

        Consultation::create([
            'patient_id' => $patient->patient_id,
            'doctor_id' => $staff->staff_id,
            'consultation_date' => now()->subDay(),
            'consultation_status' => 'completed',
            'past_medical_history' => 'New history',
            'created_by' => $doctor->user_id,
        ]);

        $latest = Consultation::latestHistoryForPatient($patient->patient_id);
        $this->assertNotNull($latest);
        $this->assertSame('New history', $latest->past_medical_history);
    }

    public function test_latest_history_skips_in_progress(): void
    {
        $patient = Patient::factory()->create();
        $doctor = User::factory()->create([
            'role_id' => Role::where('role_name', 'doctor')->first()->role_id,
        ]);
        $staff = Staff::factory()->create(['user_id' => $doctor->user_id]);

        Consultation::create([
            'patient_id' => $patient->patient_id,
            'doctor_id' => $staff->staff_id,
            'consultation_date' => now()->subDays(10),
            'consultation_status' => 'completed',
            'past_medical_history' => 'Completed history',
            'created_by' => $doctor->user_id,
        ]);

        Consultation::create([
            'patient_id' => $patient->patient_id,
            'doctor_id' => $staff->staff_id,
            'consultation_date' => now(),
            'consultation_status' => 'in_progress',
            'past_medical_history' => 'In-progress history',
            'created_by' => $doctor->user_id,
        ]);

        $latest = Consultation::latestHistoryForPatient($patient->patient_id);
        $this->assertNotNull($latest);
        $this->assertSame('Completed history', $latest->past_medical_history);
    }

    public function test_latest_history_returns_null_for_no_completed(): void
    {
        $patient = Patient::factory()->create();

        Consultation::create([
            'patient_id' => $patient->patient_id,
            'doctor_id' => Staff::factory()->create()->staff_id,
            'consultation_date' => now(),
            'consultation_status' => 'in_progress',
            'created_by' => User::factory()->create()->user_id,
        ]);

        $latest = Consultation::latestHistoryForPatient($patient->patient_id);
        $this->assertNull($latest);
    }

    public function test_to_clinical_summary_includes_fields(): void
    {
        $patient = Patient::factory()->create();
        $doctor = User::factory()->create([
            'role_id' => Role::where('role_name', 'doctor')->first()->role_id,
        ]);
        $staff = Staff::factory()->create(['user_id' => $doctor->user_id]);

        $consultation = Consultation::create([
            'patient_id' => $patient->patient_id,
            'doctor_id' => $staff->staff_id,
            'consultation_date' => now(),
            'consultation_status' => 'completed',
            'past_medical_history' => 'Asthma',
            'family_history' => 'Diabetes',
            'social_history' => 'Non-smoker',
            'created_by' => $doctor->user_id,
        ]);

        $summary = $consultation->toClinicalSummary();

        $this->assertIsArray($summary);
        $this->assertSame('Asthma', $summary['past_medical_history']);
        $this->assertSame('Diabetes', $summary['family_history']);
        $this->assertSame('Non-smoker', $summary['social_history']);
        $this->assertArrayHasKey('consultation_id', $summary);
    }

    public function test_to_history_prefill_excludes_empty_fields(): void
    {
        $patient = Patient::factory()->create();
        $doctor = User::factory()->create([
            'role_id' => Role::where('role_name', 'doctor')->first()->role_id,
        ]);
        $staff = Staff::factory()->create(['user_id' => $doctor->user_id]);

        $consultation = Consultation::create([
            'patient_id' => $patient->patient_id,
            'doctor_id' => $staff->staff_id,
            'consultation_date' => now(),
            'consultation_status' => 'completed',
            'past_medical_history' => 'Asthma',
            'family_history' => null,
            'social_history' => '',
            'created_by' => $doctor->user_id,
        ]);

        $prefill = $consultation->toHistoryPrefill();

        $this->assertArrayHasKey('past_medical_history', $prefill);
        $this->assertArrayNotHasKey('family_history', $prefill);
        $this->assertArrayNotHasKey('social_history', $prefill);
    }
}
