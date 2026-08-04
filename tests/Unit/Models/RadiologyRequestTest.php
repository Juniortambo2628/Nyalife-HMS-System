<?php

namespace Tests\Unit\Models;

use App\Models\Patient;
use App\Models\RadiologyRequest;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\SyncSpatieRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RadiologyRequestTest extends TestCase
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

    public function test_radiology_request_belongs_to_patient(): void
    {
        $patient = Patient::factory()->create();
        $doctor = User::factory()->create([
            'role_id' => Role::where('role_name', 'doctor')->first()->role_id,
        ]);

        $rr = RadiologyRequest::create([
            'patient_id' => $patient->patient_id,
            'requested_by' => $doctor->user_id,
            'scan_type' => 'Chest X-Ray',
            'priority' => 'normal',
            'status' => 'pending',
            'request_number' => 'RAD-TEST-001',
        ]);

        $this->assertNotNull($rr->patient);
        $this->assertSame($patient->patient_id, $rr->patient->patient_id);
    }

    public function test_radiology_request_status_values(): void
    {
        $statuses = ['pending', 'processing', 'pending_verification', 'verified', 'completed', 'cancelled'];

        foreach ($statuses as $status) {
            $patient = Patient::factory()->create();
            $rr = RadiologyRequest::create([
                'patient_id' => $patient->patient_id,
                'requested_by' => User::factory()->create([
                    'role_id' => Role::where('role_name', 'doctor')->first()->role_id,
                ])->user_id,
                'scan_type' => 'Test',
                'priority' => 'normal',
                'status' => $status,
                'request_number' => 'RAD-'.uniqid(),
            ]);

            $this->assertSame($status, $rr->status);
        }
    }

    public function test_radiology_request_priority_values(): void
    {
        $patient = Patient::factory()->create();
        $doctor = User::factory()->create([
            'role_id' => Role::where('role_name', 'doctor')->first()->role_id,
        ]);

        $rr = RadiologyRequest::create([
            'patient_id' => $patient->patient_id,
            'requested_by' => $doctor->user_id,
            'scan_type' => 'Test',
            'priority' => 'normal',
            'status' => 'pending',
            'request_number' => 'RAD-TEST-P1',
        ]);
        $this->assertSame('normal', $rr->priority);

        $rr2 = RadiologyRequest::create([
            'patient_id' => $patient->patient_id,
            'requested_by' => $doctor->user_id,
            'scan_type' => 'Test',
            'priority' => 'urgent',
            'status' => 'pending',
            'request_number' => 'RAD-TEST-P2',
        ]);
        $this->assertSame('urgent', $rr2->priority);
    }

    public function test_radiology_request_request_number_format(): void
    {
        $patient = Patient::factory()->create();
        $doctor = User::factory()->create([
            'role_id' => Role::where('role_name', 'doctor')->first()->role_id,
        ]);

        $rr = RadiologyRequest::create([
            'patient_id' => $patient->patient_id,
            'requested_by' => $doctor->user_id,
            'scan_type' => 'Test',
            'priority' => 'normal',
            'status' => 'pending',
            'request_number' => 'RAD-FMT-001',
        ]);

        $this->assertNotEmpty($rr->request_number);
        $this->assertStringStartsWith('RAD-', $rr->request_number);
    }
}
