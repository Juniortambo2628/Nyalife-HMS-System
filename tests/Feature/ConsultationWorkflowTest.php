<?php

namespace Tests\Feature;

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ConsultationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_nurse_can_create_vitals_draft_and_doctor_can_conclude()
    {
        // 1. Setup Nurse
        $nurseUser = User::factory()->create(['role' => 'nurse']);
        $nurseStaff = Staff::factory()->create(['user_id' => $nurseUser->user_id]);

        // 2. Setup Doctor
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorStaff = Staff::factory()->create(['user_id' => $doctorUser->user_id]);

        // 3. Setup Patient
        $patient = Patient::factory()->create();

        // 4. Nurse creates a draft (SAVE VITALS)
        $this->actingAs($nurseUser);
        
        $createData = [
            'patient_id' => $patient->patient_id,
            'doctor_id' => $doctorStaff->staff_id,
            'consultation_date' => now()->format('Y-m-d\TH:i'),
            'status' => 'in_progress', // SAVE VITALS sets this
            'is_walk_in' => true,
            'vital_signs' => [
                'blood_pressure' => '120/80',
                'temperature' => '37.0',
                'heart_rate' => '72',
            ],
            'parity' => 'Para 1+0',
        ];

        $response = $this->post(route('consultations.store'), $createData);

        // Should redirect to edit page
        $response->assertRedirect();
        
        $consultation = Consultation::where('patient_id', $patient->patient_id)->first();
        $this->assertNotNull($consultation);
        $this->assertEquals('in_progress', $consultation->consultation_status);
        $this->assertEquals('120/80', $consultation->vital_signs['blood_pressure']);
        // Verify parity was normalised to string "1+0" or int depending on logic, just check it didn't crash

        // 5. Nurse edits the draft (adds more vitals)
        $updateData = array_merge($createData, [
            'vital_signs' => [
                'blood_pressure' => '120/80',
                'temperature' => '37.5',
                'heart_rate' => '75',
            ]
        ]);

        $response = $this->put(route('consultations.update', $consultation->consultation_id), $updateData);
        $response->assertRedirect();
        
        $consultation->refresh();
        $this->assertEquals('37.5', $consultation->vital_signs['temperature']);

        // 6. Doctor concludes the consultation
        $this->actingAs($doctorUser);

        $concludeData = array_merge($updateData, [
            'status' => 'completed',
            'diagnosis' => 'Healthy',
            'treatment_plan' => 'Rest',
            'notes' => 'Patient is doing well.'
        ]);

        $response = $this->put(route('consultations.update', $consultation->consultation_id), $concludeData);
        
        // Doctor completing it redirects to dashboard
        $response->assertRedirect(route('dashboard'));

        $consultation->refresh();
        $this->assertEquals('completed', $consultation->consultation_status);
        $this->assertEquals('Healthy', $consultation->diagnosis);
    }
}
