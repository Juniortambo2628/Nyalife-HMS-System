<?php

namespace Tests\Unit\Models;

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\Appointment;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ConsultationTest extends TestCase
{
    use RefreshDatabase;

    public function test_consultation_belongs_to_patient(): void
    {
        $consultation = Consultation::factory()->create();

        $this->assertInstanceOf(Patient::class, $consultation->patient);
        $this->assertEquals($consultation->patient_id, $consultation->patient->patient_id);
    }

    public function test_consultation_belongs_to_doctor(): void
    {
        $consultation = Consultation::factory()->create();

        $this->assertInstanceOf(Staff::class, $consultation->doctor);
        $this->assertEquals($consultation->doctor_id, $consultation->doctor->staff_id);
    }

    public function test_consultation_belongs_to_appointment(): void
    {
        $consultation = Consultation::factory()->create();

        $this->assertInstanceOf(Appointment::class, $consultation->appointment);
        $this->assertEquals($consultation->appointment_id, $consultation->appointment->appointment_id);
    }

    public function test_consultation_has_many_prescriptions(): void
    {
        $consultation = Consultation::factory()->has(\App\Models\Prescription::factory()->count(3))->create();

        $this->assertCount(3, $consultation->prescriptions);
    }

    public function test_consultation_has_many_lab_test_requests(): void
    {
        $consultation = Consultation::factory()->has(\App\Models\LabTestRequest::factory()->count(2))->create();

        $this->assertCount(2, $consultation->labTestRequests);
    }

    public function test_consultation_has_many_invoices(): void
    {
        $consultation = Consultation::factory()->has(\App\Models\Invoice::factory()->count(2))->create();

        $this->assertCount(2, $consultation->invoices);
    }

    public function test_consultation_has_many_follow_ups(): void
    {
        $consultation = Consultation::factory()->has(\App\Models\FollowUp::factory()->count(2))->create();

        $this->assertCount(2, $consultation->followUps);
    }

    public function test_consultation_scopes_work_correctly(): void
    {
        $doctor = \App\Models\Staff::factory()->create();
        $patient = \App\Models\Patient::factory()->create();

        Consultation::factory()->create(['doctor_id' => $doctor->staff_id]);
        Consultation::factory()->create(['patient_id' => $patient->patient_id]);

        $this->assertCount(1, Consultation::forDoctor($doctor->staff_id)->get());
        $this->assertCount(1, Consultation::forPatient($patient->patient_id)->get());
    }

    public function test_consultation_search_by_patient_or_diagnosis(): void
    {
        $patient = Patient::factory()->create([
            'user_id' => \App\Models\User::factory()->create(['first_name' => 'John', 'last_name' => 'Doe'])->user_id,
        ]);
        $consultation = Consultation::factory()->create([
            'patient_id' => $patient->patient_id,
            'diagnosis' => 'Hypertension',
        ]);

        $results = Consultation::searchByPatientOrDiagnosis('John')->get();
        $this->assertCount(1, $results);

        $results = Consultation::searchByPatientOrDiagnosis('Hypertension')->get();
        $this->assertCount(1, $results);
    }

    public function test_consultation_json_casts_work_correctly(): void
    {
        $vitalSigns = ['bp' => '120/80', 'hr' => 72, 'temp' => 37.0];
        $menstrualHistory = ['last_period' => '2024-01-15', 'cycle_length' => 28];
        $pastObstetric = ['previous_cs' => 1, 'vaginal_deliveries' => 2];

        $consultation = Consultation::factory()->create([
            'vital_signs' => $vitalSigns,
            'menstrual_history' => $menstrualHistory,
            'past_obstetric' => $pastObstetric,
        ]);

        $this->assertEquals($vitalSigns, $consultation->vital_signs);
        $this->assertEquals($menstrualHistory, $consultation->menstrual_history);
        $this->assertEquals($pastObstetric, $consultation->past_obstetric);
    }

    public function test_consultation_to_history_prefill_returns_relevant_fields(): void
    {
        $consultation = Consultation::factory()->create([
            'past_medical_history' => 'Diabetes',
            'surgical_history' => 'Appendectomy',
            'family_history' => 'Mother had hypertension',
        ]);

        $prefill = $consultation->toHistoryPrefill();

        $this->assertArrayHasKey('past_medical_history', $prefill);
        $this->assertArrayHasKey('surgical_history', $prefill);
        $this->assertArrayHasKey('family_history', $prefill);
        $this->assertEquals('Diabetes', $prefill['past_medical_history']);
    }

    public function test_consultation_to_clinical_summary_returns_summary(): void
    {
        $consultation = Consultation::factory()->create([
            'past_medical_history' => 'Hypertension',
            'consultation_date' => now()->format('Y-m-d H:i:s'),
        ]);

        $summary = $consultation->toClinicalSummary();

        $this->assertArrayHasKey('consultation_id', $summary);
        $this->assertArrayHasKey('consultation_date', $summary);
        $this->assertArrayHasKey('past_medical_history', $summary);
        $this->assertEquals('Hypertension', $summary['past_medical_history']);
    }
}