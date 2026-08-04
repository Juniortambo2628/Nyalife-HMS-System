<?php

namespace Tests\Feature\FollowUps;

use App\Models\Consultation;
use App\Models\FollowUp;
use App\Models\Patient;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use App\Support\Permissions;
use Database\Seeders\SyncSpatieRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowUpControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $doctor;

    protected Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'doctor', 'nurse', 'patient'] as $name) {
            Role::firstOrCreate(['role_name' => $name]);
        }
        $this->seed(SyncSpatieRolesSeeder::class);

        $this->doctor = User::factory()->create([
            'role_id' => Role::where('role_name', 'doctor')->first()->role_id,
            'is_active' => true,
        ]);
        $this->doctor->assignRole('doctor');
        $this->doctor->givePermissionTo(Permissions::MANAGE_FOLLOW_UPS);

        $this->patient = Patient::factory()->create();
    }

    private function createFollowUp(string $status = 'scheduled'): FollowUp
    {
        $staff = Staff::factory()->create(['user_id' => $this->doctor->user_id]);
        $consultation = Consultation::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $staff->staff_id,
        ]);

        return FollowUp::create([
            'patient_id' => $this->patient->patient_id,
            'consultation_id' => $consultation->consultation_id,
            'follow_up_date' => now()->addDays(7)->format('Y-m-d'),
            'follow_up_type' => 'general',
            'reason' => 'Post-surgery checkup',
            'status' => $status,
            'created_by' => $this->doctor->user_id,
        ]);
    }

    public function test_store_creates_follow_up(): void
    {
        $staff = Staff::factory()->create(['user_id' => $this->doctor->user_id]);
        $consultation = Consultation::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $staff->staff_id,
        ]);

        $this->actingAs($this->doctor)
            ->post(route('follow-ups.store'), [
                'patient_id' => $this->patient->patient_id,
                'consultation_id' => $consultation->consultation_id,
                'follow_up_date' => now()->addDays(14)->format('Y-m-d'),
                'follow_up_type' => 'review',
                'reason' => 'Medication review',
                'status' => 'scheduled',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('follow_ups', [
            'patient_id' => $this->patient->patient_id,
            'consultation_id' => $consultation->consultation_id,
            'reason' => 'Medication review',
            'status' => 'scheduled',
        ]);
    }

    public function test_store_defaults_to_scheduled_and_general(): void
    {
        $staff = Staff::factory()->create(['user_id' => $this->doctor->user_id]);
        $consultation = Consultation::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $staff->staff_id,
        ]);

        $this->actingAs($this->doctor)
            ->post(route('follow-ups.store'), [
                'patient_id' => $this->patient->patient_id,
                'consultation_id' => $consultation->consultation_id,
                'follow_up_date' => now()->format('Y-m-d'),
                'reason' => 'Routine check',
            ])
            ->assertRedirect();

        $followUp = FollowUp::where('patient_id', $this->patient->patient_id)->first();
        $this->assertSame('scheduled', $followUp->status);
        $this->assertSame('general', $followUp->follow_up_type);
    }

    public function test_store_validation_fails_without_required_fields(): void
    {
        $this->actingAs($this->doctor)
            ->post(route('follow-ups.store'), [
                'patient_id' => '',
                'consultation_id' => '',
                'follow_up_date' => '',
                'reason' => '',
            ])
            ->assertSessionHasErrors(['patient_id', 'consultation_id', 'follow_up_date', 'reason']);
    }

    public function test_update_modifies_follow_up(): void
    {
        $followUp = $this->createFollowUp('scheduled');

        $this->actingAs($this->doctor)
            ->put(route('follow-ups.update', $followUp->follow_up_id), [
                'follow_up_date' => now()->addDays(30)->format('Y-m-d'),
                'reason' => 'Updated reason',
                'status' => 'completed',
            ])
            ->assertRedirect();

        $followUp->refresh();
        $this->assertSame('Updated reason', $followUp->reason);
        $this->assertSame('completed', $followUp->status);
    }

    public function test_update_status_transitions(): void
    {
        $followUp = $this->createFollowUp('scheduled');

        $this->actingAs($this->doctor)
            ->post(route('follow-ups.update-status', $followUp->follow_up_id), [
                'status' => 'completed',
            ])
            ->assertRedirect();

        $followUp->refresh();
        $this->assertSame('completed', $followUp->status);
    }

    public function test_update_status_rejects_invalid_status(): void
    {
        $followUp = $this->createFollowUp('scheduled');

        $this->actingAs($this->doctor)
            ->post(route('follow-ups.update-status', $followUp->follow_up_id), [
                'status' => 'invalid_status',
            ])
            ->assertSessionHasErrors('status');
    }

    public function test_destroy_removes_scheduled_follow_up(): void
    {
        $followUp = $this->createFollowUp('scheduled');

        $this->actingAs($this->doctor)
            ->delete(route('follow-ups.destroy', $followUp->follow_up_id))
            ->assertRedirect();

        $this->assertDatabaseMissing('follow_ups', [
            'follow_up_id' => $followUp->follow_up_id,
        ]);
    }

    public function test_destroy_blocks_deletion_of_completed_follow_up(): void
    {
        $followUp = $this->createFollowUp('completed');

        $this->actingAs($this->doctor)
            ->delete(route('follow-ups.destroy', $followUp->follow_up_id))
            ->assertRedirect()
            ->assertSessionHas('error', 'Completed follow-ups cannot be deleted.');

        $this->assertDatabaseHas('follow_ups', [
            'follow_up_id' => $followUp->follow_up_id,
        ]);
    }

    public function test_unauthorized_user_cannot_access(): void
    {
        $noPerm = User::factory()->create([
            'role_id' => Role::where('role_name', 'nurse')->first()->role_id,
            'is_active' => true,
            'username' => 'nurse_no_followup_perm_'.uniqid(),
        ]);
        $noPerm->assignRole('nurse');

        $this->actingAs($noPerm)
            ->get(route('follow-ups.index'))
            ->assertForbidden();
    }
}
