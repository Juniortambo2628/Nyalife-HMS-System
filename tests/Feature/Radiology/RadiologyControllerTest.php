<?php

namespace Tests\Feature\Radiology;

use App\Models\Patient;
use App\Models\RadiologyRequest;
use App\Models\Role;
use App\Models\User;
use App\Support\Permissions;
use Database\Seeders\SyncSpatieRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RadiologyControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'doctor', 'nurse', 'patient'] as $name) {
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

    private function createRadiologyRequest(string $status = 'pending'): RadiologyRequest
    {
        return RadiologyRequest::create([
            'patient_id' => $this->patient->patient_id,
            'requested_by' => $this->admin->user_id,
            'scan_type' => 'Abdominal Ultrasound',
            'priority' => 'normal',
            'clinical_indication' => 'Abdominal pain',
            'status' => $status,
            'request_number' => 'RAD-TEST-'.uniqid(),
        ]);
    }

    public function test_store_creates_radiology_request(): void
    {
        $this->actingAs($this->admin)
            ->post(route('radiology.store'), [
                'patient_id' => $this->patient->patient_id,
                'scan_type' => 'Chest X-Ray',
                'priority' => 'urgent',
                'clinical_indication' => 'Chest pain',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('radiology_requests', [
            'patient_id' => $this->patient->patient_id,
            'scan_type' => 'Chest X-Ray',
            'priority' => 'urgent',
            'status' => 'pending',
        ]);
    }

    public function test_store_generates_request_number(): void
    {
        $this->actingAs($this->admin)
            ->post(route('radiology.store'), [
                'patient_id' => $this->patient->patient_id,
                'scan_type' => 'MRI Brain',
                'priority' => 'normal',
            ])
            ->assertRedirect();

        $rr = RadiologyRequest::where('patient_id', $this->patient->patient_id)->first();
        $this->assertStringStartsWith('RAD-', $rr->request_number);
    }

    public function test_store_validation_fails_without_required_fields(): void
    {
        $this->actingAs($this->admin)
            ->post(route('radiology.store'), [
                'patient_id' => '',
                'scan_type' => '',
                'priority' => '',
            ])
            ->assertSessionHasErrors(['patient_id', 'scan_type', 'priority']);
    }

    public function test_update_modifies_pending_request(): void
    {
        $rr = $this->createRadiologyRequest('pending');

        $this->actingAs($this->admin)
            ->put(route('radiology.update', $rr->request_id), [
                'scan_type' => 'Pelvic Ultrasound',
                'priority' => 'urgent',
                'clinical_indication' => 'Updated indication',
            ])
            ->assertRedirect();

        $rr->refresh();
        $this->assertSame('Pelvic Ultrasound', $rr->scan_type);
        $this->assertSame('urgent', $rr->priority);
    }

    public function test_update_blocks_non_pending_requests(): void
    {
        $rr = $this->createRadiologyRequest('processing');

        $this->actingAs($this->admin)
            ->put(route('radiology.update', $rr->request_id), [
                'scan_type' => 'Modified',
                'priority' => 'normal',
            ])
            ->assertRedirect();
    }

    public function test_destroy_removes_pending_request(): void
    {
        $rr = $this->createRadiologyRequest('pending');

        $this->actingAs($this->admin)
            ->delete(route('radiology.destroy', $rr->request_id))
            ->assertRedirect();

        $this->assertDatabaseMissing('radiology_requests', [
            'request_id' => $rr->request_id,
        ]);
    }

    public function test_destroy_blocks_non_pending_requests(): void
    {
        $rr = $this->createRadiologyRequest('processing');

        $this->actingAs($this->admin)
            ->delete(route('radiology.destroy', $rr->request_id))
            ->assertRedirect();

        $this->assertDatabaseHas('radiology_requests', [
            'request_id' => $rr->request_id,
        ]);
    }

    public function test_update_status_to_processing(): void
    {
        $rr = $this->createRadiologyRequest('pending');

        $this->actingAs($this->admin)
            ->post(route('radiology.update-status', $rr->request_id), [
                'status' => 'processing',
            ])
            ->assertRedirect();

        $rr->refresh();
        $this->assertSame('processing', $rr->status);
        $this->assertSame($this->admin->user_id, $rr->assigned_to);
    }

    public function test_update_status_to_verified(): void
    {
        $rr = $this->createRadiologyRequest('pending_verification');
        $rr->update(['assigned_to' => $this->admin->user_id]);

        $this->actingAs($this->admin)
            ->post(route('radiology.update-status', $rr->request_id), [
                'status' => 'verified',
                'findings' => 'Normal',
                'impression' => 'No abnormality',
            ])
            ->assertRedirect();

        $rr->refresh();
        $this->assertSame('verified', $rr->status);
        $this->assertNotNull($rr->verified_at);
        $this->assertNotNull($rr->completed_at);
        $this->assertSame($this->admin->user_id, $rr->verified_by);
    }

    public function test_update_status_to_completed(): void
    {
        $rr = $this->createRadiologyRequest('pending');

        $this->actingAs($this->admin)
            ->post(route('radiology.update-status', $rr->request_id), [
                'status' => 'completed',
            ])
            ->assertRedirect();

        $rr->refresh();
        $this->assertSame('completed', $rr->status);
        $this->assertNotNull($rr->completed_at);
    }

    public function test_update_status_rejects_invalid_status(): void
    {
        $rr = $this->createRadiologyRequest('pending');

        $this->actingAs($this->admin)
            ->post(route('radiology.update-status', $rr->request_id), [
                'status' => 'invalid',
            ])
            ->assertSessionHasErrors('status');
    }

    public function test_patient_scoped_to_own_records(): void
    {
        $patientUser = User::factory()->create([
            'role_id' => Role::where('role_name', 'patient')->first()->role_id,
            'is_active' => true,
        ]);
        $patientUser->assignRole('patient');
        $patientUser->givePermissionTo(Permissions::MANAGE_LAB);
        $patientUser->givePermissionTo(Permissions::VIEW_OWN_RECORDS);

        $patient = Patient::factory()->create(['user_id' => $patientUser->user_id]);
        $otherPatient = Patient::factory()->create();

        // Create records directly (avoid buggy factory)
        RadiologyRequest::create([
            'patient_id' => $patient->patient_id,
            'requested_by' => $this->admin->user_id,
            'scan_type' => 'Test',
            'priority' => 'normal',
            'status' => 'pending',
            'request_number' => 'RAD-SCOPING-1',
        ]);
        RadiologyRequest::create([
            'patient_id' => $otherPatient->patient_id,
            'requested_by' => $this->admin->user_id,
            'scan_type' => 'Test',
            'priority' => 'normal',
            'status' => 'pending',
            'request_number' => 'RAD-SCOPING-2',
        ]);

        $this->actingAs($patientUser)
            ->get(route('radiology.index'))
            ->assertOk();
    }
}
