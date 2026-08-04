<?php

namespace Tests\Unit\Models;

use App\Models\Consultation;
use App\Models\FollowUp;
use App\Models\Patient;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Database\Seeders\SyncSpatieRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowUpTest extends TestCase
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

    public function test_follow_up_belongs_to_patient(): void
    {
        $patient = Patient::factory()->create();
        $doctor = User::factory()->create([
            'role_id' => Role::where('role_name', 'doctor')->first()->role_id,
        ]);
        $staff = Staff::factory()->create(['user_id' => $doctor->user_id]);
        $consultation = Consultation::factory()->create([
            'patient_id' => $patient->patient_id,
            'doctor_id' => $staff->staff_id,
        ]);

        $followUp = FollowUp::create([
            'patient_id' => $patient->patient_id,
            'consultation_id' => $consultation->consultation_id,
            'follow_up_date' => now()->addDays(7)->format('Y-m-d'),
            'reason' => 'Checkup',
            'status' => 'scheduled',
            'created_by' => $doctor->user_id,
        ]);

        $this->assertNotNull($followUp->patient);
        $this->assertSame($patient->patient_id, $followUp->patient->patient_id);
    }

    public function test_follow_up_belongs_to_consultation(): void
    {
        $patient = Patient::factory()->create();
        $doctor = User::factory()->create([
            'role_id' => Role::where('role_name', 'doctor')->first()->role_id,
        ]);
        $staff = Staff::factory()->create(['user_id' => $doctor->user_id]);
        $consultation = Consultation::factory()->create([
            'patient_id' => $patient->patient_id,
            'doctor_id' => $staff->staff_id,
        ]);

        $followUp = FollowUp::create([
            'patient_id' => $patient->patient_id,
            'consultation_id' => $consultation->consultation_id,
            'follow_up_date' => now()->addDays(7)->format('Y-m-d'),
            'reason' => 'Checkup',
            'status' => 'scheduled',
            'created_by' => $doctor->user_id,
        ]);

        $this->assertNotNull($followUp->consultation);
        $this->assertSame($consultation->consultation_id, $followUp->consultation->consultation_id);
    }

    public function test_follow_up_status_values(): void
    {
        $this->assertContains('scheduled', array_keys(FollowUp::STATUSES));
        $this->assertContains('completed', array_keys(FollowUp::STATUSES));
        $this->assertContains('cancelled', array_keys(FollowUp::STATUSES));
        $this->assertContains('no_show', array_keys(FollowUp::STATUSES));
    }

    public function test_follow_up_type_values(): void
    {
        $this->assertContains('general', array_keys(FollowUp::TYPES));
        $this->assertContains('medication_review', array_keys(FollowUp::TYPES));
        $this->assertContains('post_surgery', array_keys(FollowUp::TYPES));
    }

    public function test_follow_up_is_scheduled_scope(): void
    {
        $patient = Patient::factory()->create();

        FollowUp::factory()->create([
            'patient_id' => $patient->patient_id,
            'follow_up_date' => now()->addDays(3)->format('Y-m-d'),
            'status' => 'scheduled',
        ]);

        FollowUp::factory()->create([
            'patient_id' => $patient->patient_id,
            'follow_up_date' => now()->subDays(3)->format('Y-m-d'),
            'status' => 'completed',
        ]);

        $upcoming = FollowUp::where('status', 'scheduled')
            ->where('follow_up_date', '>=', now()->format('Y-m-d'))
            ->count();

        $this->assertGreaterThanOrEqual(1, $upcoming);
    }
}
