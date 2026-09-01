<?php

namespace Tests\Feature\Consultations;

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Database\Seeders\RolePermissionsSeeder;
use Database\Seeders\SyncSpatieRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultationHandoverAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_clinicians_can_see_and_resume_another_doctors_in_progress_consultation(): void
    {
        $this->seedRolesAndPermissions();

        $firstDoctor = $this->userWithRole('doctor');
        $secondDoctor = $this->userWithRole('doctor');
        $firstDoctorStaff = Staff::factory()->create(['user_id' => $firstDoctor->user_id]);
        $secondDoctorStaff = Staff::factory()->create(['user_id' => $secondDoctor->user_id]);
        $patient = Patient::factory()->create();
        $consultation = Consultation::factory()->inProgress()->create([
            'patient_id' => $patient->patient_id,
            'doctor_id' => $firstDoctorStaff->staff_id,
        ]);

        $this->actingAs($secondDoctor)
            ->get(route('consultations.index'), $this->inertiaHeaders())
            ->assertOk()
            ->assertJsonPath('props.consultations.data.0.consultation_id', $consultation->consultation_id)
            ->assertJsonPath('props.drafts.0.consultation_id', $consultation->consultation_id);

        $this->actingAs($secondDoctor)
            ->get(route('consultations.edit', $consultation->consultation_id))
            ->assertOk();

        $this->assertNotSame($firstDoctorStaff->staff_id, $secondDoctorStaff->staff_id);
    }

    public function test_patient_still_sees_only_their_own_consultations(): void
    {
        $this->seedRolesAndPermissions();

        $patientUser = $this->userWithRole('patient');
        $patient = Patient::factory()->create(['user_id' => $patientUser->user_id]);
        $otherPatient = Patient::factory()->create();
        $ownConsultation = Consultation::factory()->inProgress()->create(['patient_id' => $patient->patient_id]);
        Consultation::factory()->inProgress()->create(['patient_id' => $otherPatient->patient_id]);

        $this->actingAs($patientUser)
            ->get(route('consultations.index'), $this->inertiaHeaders())
            ->assertOk()
            ->assertJsonCount(1, 'props.consultations.data')
            ->assertJsonPath('props.consultations.data.0.consultation_id', $ownConsultation->consultation_id)
            ->assertJsonCount(1, 'props.drafts');
    }

    private function seedRolesAndPermissions(): void
    {
        foreach (['admin', 'doctor', 'nurse', 'receptionist', 'lab_technician', 'pharmacist', 'patient'] as $roleName) {
            Role::firstOrCreate(['role_name' => $roleName]);
        }

        $this->seed(SyncSpatieRolesSeeder::class);
        $this->seed(RolePermissionsSeeder::class);
    }

    private function userWithRole(string $roleName): User
    {
        $user = User::factory()->create([
            'role_id' => Role::where('role_name', $roleName)->value('role_id'),
        ]);
        $user->assignRole($roleName);

        return $user;
    }

    private function inertiaHeaders(): array
    {
        return [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => hash_file('xxh128', public_path('build/manifest.json')),
        ];
    }
}
