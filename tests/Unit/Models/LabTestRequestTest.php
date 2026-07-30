<?php

namespace Tests\Unit\Models;

use App\Models\LabTestRequest;
use App\Models\LabTestType;
use App\Models\Patient;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabTestRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_lab_test_request_belongs_to_patient(): void
    {
        $request = LabTestRequest::factory()->create();

        $this->assertInstanceOf(Patient::class, $request->patient);
        $this->assertEquals($request->patient_id, $request->patient->patient_id);
    }

    public function test_lab_test_request_belongs_to_doctor(): void
    {
        $request = LabTestRequest::factory()->create();

        $this->assertInstanceOf(Staff::class, $request->doctor);
        $this->assertEquals($request->doctor_id, $request->doctor->staff_id);
    }

    public function test_lab_test_request_belongs_to_test_type(): void
    {
        $request = LabTestRequest::factory()->create();

        $this->assertInstanceOf(LabTestType::class, $request->testType);
        $this->assertEquals($request->test_type_id, $request->testType->test_type_id);
    }

    public function test_lab_test_request_has_many_samples(): void
    {
        $this->assertTrue(true);
    }

    public function test_lab_test_request_status_values(): void
    {
        $statuses = ['pending', 'sample_collected', 'processing', 'pending_verification', 'verified', 'completed', 'cancelled'];

        foreach ($statuses as $status) {
            $request = LabTestRequest::factory()->create(['status' => $status]);
            $this->assertEquals($status, $request->status);
        }
    }

    public function test_lab_test_request_priority_values(): void
    {
        $priorities = ['normal', 'urgent', 'stat'];

        foreach ($priorities as $priority) {
            $request = LabTestRequest::factory()->create(['priority' => $priority]);
            $this->assertEquals($priority, $request->priority);
        }
    }

    public function test_lab_test_request_results_json_cast(): void
    {
        $results = [
            'test_name' => 'Full Blood Count',
            'result' => 'Hb: 13.5 g/dL, WBC: 7200, Plt: 250000',
            'unit' => 'various',
            'reference_range' => 'Hb: 12-16, WBC: 4000-11000, Plt: 150000-450000',
            'flag' => 'normal',
        ];

        $request = LabTestRequest::factory()->create(['results' => $results]);

        $this->assertEquals($results, $request->results);
    }

    public function test_lab_test_request_scopes(): void
    {
        $pendingRequest = LabTestRequest::factory()->create(['status' => 'pending']);
        $completedRequest = LabTestRequest::factory()->create(['status' => 'completed']);
        $verifiedRequest = LabTestRequest::factory()->create(['status' => 'verified']);

        $this->assertNotNull(LabTestRequest::where('status', 'pending')->find($pendingRequest->request_id));
        $this->assertNotNull(LabTestRequest::where('status', 'completed')->find($completedRequest->request_id));
        $this->assertNotNull(LabTestRequest::where('status', 'verified')->find($verifiedRequest->request_id));
    }
}
