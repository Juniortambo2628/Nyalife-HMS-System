<?php

namespace Tests\Feature\Lab;

use App\Models\LabTestRequest;
use App\Models\LabTestType;
use App\Models\Patient;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use App\Support\Permissions;
use Database\Seeders\SyncSpatieRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'doctor', 'nurse', 'lab_technician'] as $name) {
            Role::firstOrCreate(['role_name' => $name]);
        }
        $this->seed(SyncSpatieRolesSeeder::class);

        $this->admin = User::factory()->create([
            'role_id' => Role::where('role_name', 'admin')->first()->role_id,
            'is_active' => true,
        ]);
        $this->admin->assignRole('admin');
        $this->admin->givePermissionTo(Permissions::MANAGE_LAB);

        $this->patient = Patient::factory()->create();
    }

    private function createLabRequest(string $status = 'pending'): LabTestRequest
    {
        $doctor = User::factory()->create([
            'role_id' => Role::where('role_name', 'doctor')->first()->role_id,
            'is_active' => true,
        ]);
        $doctor->assignRole('doctor');
        $staff = Staff::factory()->create(['user_id' => $doctor->user_id]);

        $testType = LabTestType::factory()->create(['is_active' => true]);

        return LabTestRequest::create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $staff->staff_id,
            'requested_by' => $doctor->user_id,
            'test_type_id' => $testType->test_type_id,
            'status' => $status,
            'request_number' => 'LAB-TEST-'.uniqid(),
            'clinical_indication' => 'Routine check',
        ]);
    }

    public function test_update_status_to_processing(): void
    {
        $lab = $this->createLabRequest('pending');

        $this->actingAs($this->admin)
            ->post(route('lab.update-status', $lab->request_id), [
                'status' => 'processing',
            ])
            ->assertRedirect();

        $lab->refresh();
        $this->assertSame('processing', $lab->status);
        $this->assertSame($this->admin->user_id, $lab->assigned_to);
    }

    public function test_update_status_to_verified(): void
    {
        $lab = $this->createLabRequest('pending_verification');

        $this->actingAs($this->admin)
            ->post(route('lab.update-status', $lab->request_id), [
                'status' => 'verified',
                'results' => ['hb' => '14.5', 'wbc' => '6000'],
            ])
            ->assertRedirect();

        $lab->refresh();
        $this->assertSame('verified', $lab->status);
        $this->assertNotNull($lab->verified_at);
        $this->assertNotNull($lab->completed_at);
        $this->assertSame($this->admin->user_id, $lab->verified_by);
    }

    public function test_update_status_to_completed(): void
    {
        $lab = $this->createLabRequest('pending');

        $this->actingAs($this->admin)
            ->post(route('lab.update-status', $lab->request_id), [
                'status' => 'completed',
            ])
            ->assertRedirect();

        $lab->refresh();
        $this->assertSame('completed', $lab->status);
        $this->assertNotNull($lab->completed_at);
    }

    public function test_update_status_rejects_invalid_status(): void
    {
        $lab = $this->createLabRequest('pending');

        $this->actingAs($this->admin)
            ->post(route('lab.update-status', $lab->request_id), [
                'status' => 'invalid_status',
            ])
            ->assertSessionHasErrors('status');
    }

    public function test_update_status_sets_assigned_to_on_pending_verification(): void
    {
        $lab = $this->createLabRequest('processing');

        $this->actingAs($this->admin)
            ->post(route('lab.update-status', $lab->request_id), [
                'status' => 'pending_verification',
            ])
            ->assertRedirect();

        $lab->refresh();
        $this->assertSame('pending_verification', $lab->status);
        $this->assertSame($this->admin->user_id, $lab->assigned_to);
    }

    public function test_lab_technician_can_access_lab(): void
    {
        $tech = User::factory()->create([
            'role_id' => Role::where('role_name', 'lab_technician')->first()->role_id,
            'is_active' => true,
        ]);
        $tech->assignRole('lab_technician');
        $tech->givePermissionTo(Permissions::MANAGE_LAB);

        $this->actingAs($tech)
            ->get(route('lab.index'))
            ->assertOk();
    }

    public function test_unauthorized_user_cannot_access_lab(): void
    {
        $nurse = User::factory()->create([
            'role_id' => Role::where('role_name', 'nurse')->first()->role_id,
            'is_active' => true,
        ]);
        $nurse->assignRole('nurse');

        $this->actingAs($nurse)
            ->get(route('lab.index'))
            ->assertForbidden();
    }
}
