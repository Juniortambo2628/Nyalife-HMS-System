<?php

namespace Tests\Feature;

use App\Mail\LabResultSharedWithDoctor;
use App\Models\LabTestRequest;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Database\Seeders\RolePermissionsSeeder;
use Database\Seeders\SyncSpatieRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LabResultSharingTest extends TestCase
{
    use RefreshDatabase;

    public function test_lab_technician_can_share_a_verified_result_with_the_requesting_doctor(): void
    {
        $this->seedRolesAndPermissions();
        Mail::fake();

        $doctor = $this->userWithRole('doctor', fake()->unique()->safeEmail());
        $labTechnician = $this->userWithRole('lab_technician', fake()->unique()->safeEmail());
        $doctorStaff = Staff::factory()->create(['user_id' => $doctor->user_id]);
        $request = LabTestRequest::factory()->verified()->create([
            'doctor_id' => $doctorStaff->staff_id,
            'requested_by' => $doctor->user_id,
        ]);

        $this->actingAs($labTechnician)
            ->post(route('lab.results.share-with-requesting-doctor', $request->request_id))
            ->assertRedirect();

        Mail::assertSent(LabResultSharedWithDoctor::class, function (LabResultSharedWithDoctor $mail) use ($doctor) {
            return $mail->hasTo($doctor->email)
                && $mail->request_number !== ''
                && str_contains($mail->result_url, '/lab-results/');
        });
    }

    public function test_unverified_results_cannot_be_shared(): void
    {
        $this->seedRolesAndPermissions();
        Mail::fake();

        $doctor = $this->userWithRole('doctor', fake()->unique()->safeEmail());
        $labTechnician = $this->userWithRole('lab_technician', fake()->unique()->safeEmail());
        $doctorStaff = Staff::factory()->create(['user_id' => $doctor->user_id]);
        $request = LabTestRequest::factory()->pending()->create([
            'doctor_id' => $doctorStaff->staff_id,
            'requested_by' => $doctor->user_id,
        ]);

        $this->actingAs($labTechnician)
            ->post(route('lab.results.share-with-requesting-doctor', $request->request_id))
            ->assertStatus(422);

        Mail::assertNothingSent();
    }

    private function seedRolesAndPermissions(): void
    {
        foreach (['admin', 'doctor', 'nurse', 'receptionist', 'lab_technician', 'pharmacist', 'patient'] as $roleName) {
            Role::firstOrCreate(['role_name' => $roleName]);
        }

        $this->seed(SyncSpatieRolesSeeder::class);
        $this->seed(RolePermissionsSeeder::class);
    }

    private function userWithRole(string $roleName, string $email): User
    {
        $user = User::factory()->create([
            'role_id' => Role::where('role_name', $roleName)->value('role_id'),
            'email' => $email,
        ]);
        $user->assignRole($roleName);

        return $user;
    }
}
