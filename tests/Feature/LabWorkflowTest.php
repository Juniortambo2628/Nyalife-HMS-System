<?php

namespace Tests\Feature;

use App\Models\Consultation;
use App\Models\LabTestRequest;
use App\Models\LabTestType;
use App\Models\Patient;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LabWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_lab_technician_can_process_and_complete_lab_request()
    {
        // Setup Users
        $doctorUser = User::factory()->create([
            'role_id' => Role::query()->firstOrCreate(['role_name' => 'doctor'])->role_id,
        ]);
        $doctorStaff = Staff::factory()->create(['user_id' => $doctorUser->user_id]);

        $labTechUser = User::factory()->create([
            'role_id' => Role::query()->firstOrCreate(['role_name' => 'lab_technician'])->role_id,
        ]);
        $labTechStaff = Staff::factory()->create(['user_id' => $labTechUser->user_id]);

        $patient = Patient::factory()->create();

        // Setup Test Type
        $testType = LabTestType::factory()->create([
            'test_name' => 'Complete Blood Count',
            'category' => 'Hematology',
            'template' => [
                ['label' => 'Hemoglobin', 'unit' => 'g/dL', 'normalRange' => '13.8 - 17.2'],
                ['label' => 'WBC', 'unit' => 'x10^9/L', 'normalRange' => '4.5 - 11.0'],
            ]
        ]);

        // Doctor requests a lab during a consultation
        $this->actingAs($doctorUser);
        $consultationData = [
            'patient_id' => $patient->patient_id,
            'doctor_id' => $doctorStaff->staff_id,
            'consultation_date' => now()->format('Y-m-d\TH:i'),
            'status' => 'in_progress',
            'is_walk_in' => true,
            'requested_labs' => [
                ['test_type_id' => $testType->test_type_id]
            ]
        ];

        $this->post(route('consultations.store'), $consultationData);

        // Verify Lab Request was created
        $labRequest = LabTestRequest::where('patient_id', $patient->patient_id)->first();
        $this->assertNotNull($labRequest);
        $this->assertEquals('pending', $labRequest->status);

        // Lab Tech assigns request to themselves
        $this->actingAs($labTechUser);
        $response = $this->put(route('lab.update', $labRequest->request_id), [
            'status' => 'in_progress',
            'assigned_to' => $labTechUser->user_id, // Assigned to user
        ]);
        $response->assertRedirect();
        
        $labRequest->refresh();
        $this->assertEquals('in_progress', $labRequest->status);
        $this->assertEquals($labTechUser->user_id, $labRequest->assigned_to);

        // Lab Tech completes the request with results
        $resultsData = [
            'status' => 'completed',
            'results' => [
                'lab_results' => [
                    'Hemoglobin' => '15.0',
                    'WBC' => '8.0'
                ],
                'observations' => 'Normal blood count',
                'conclusions' => 'No abnormalities detected.'
            ]
        ];

        $response = $this->put(route('lab.update', $labRequest->request_id), $resultsData);
        $response->assertRedirect(route('lab.index'));

        $labRequest->refresh();
        $this->assertEquals('completed', $labRequest->status);
        $this->assertEquals('15.0', $labRequest->results['lab_results']['Hemoglobin']);
        $this->assertEquals('Normal blood count', $labRequest->results['observations']);
    }
}
