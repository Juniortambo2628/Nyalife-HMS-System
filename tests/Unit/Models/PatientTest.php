<?php

namespace Tests\Unit\Models;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\LabTestRequest;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use App\Models\Vital;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PatientTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_belongs_to_user(): void
    {
        $patient = Patient::factory()->create();

        $this->assertInstanceOf(User::class, $patient->user);
        $this->assertEquals($patient->user_id, $patient->user->user_id);
    }

    public function test_patient_has_many_appointments(): void
    {
        $patient = Patient::factory()->has(Appointment::factory()->count(3))->create();

        $this->assertCount(3, $patient->appointments);
        $patient->appointments->each(fn ($appointment) => $this->assertEquals($patient->patient_id, $appointment->patient_id));
    }

    public function test_patient_has_many_prescriptions(): void
    {
        $patient = Patient::factory()->has(Prescription::factory()->count(2))->create();

        $this->assertCount(2, $patient->prescriptions);
    }

    public function test_patient_has_many_lab_test_requests(): void
    {
        $patient = Patient::factory()->has(LabTestRequest::factory()->count(2))->create();

        $this->assertCount(2, $patient->labTestRequests);
    }

    public function test_patient_has_many_consultations(): void
    {
        $patient = Patient::factory()->has(Consultation::factory()->count(2))->create();

        $this->assertCount(2, $patient->consultations);
    }

    public function test_patient_has_many_vitals(): void
    {
        $patient = Patient::factory()->has(Vital::factory()->count(3))->create();

        $this->assertCount(3, $patient->vitals);
    }

    public function test_patient_generates_unique_patient_number(): void
    {
        $user = User::factory()->create();
        $number = Patient::generateNumber($user->user_id);

        $this->assertStringStartsWith('PAT-', $number);
        // PAT-YYYYMMDD-NNNN — the suffix is zero-padded to at least 4 digits
        // and can grow as the users table accumulates rows.
        $this->assertMatchesRegularExpression('/PAT-\d{8}-\d{4,}/', $number);
    }

    public function test_patient_scope_search_by_user_name(): void
    {
        $patient1 = Patient::factory()->create([
            'user_id' => User::factory()->create(['first_name' => 'JohnUnique', 'last_name' => 'Doe'])->user_id,
        ]);
        $patient2 = Patient::factory()->create([
            'user_id' => User::factory()->create(['first_name' => 'JaneUnique', 'last_name' => 'Smith'])->user_id,
        ]);

        $results = Patient::searchByUserName('JohnUnique')->get();

        $this->assertCount(1, $results);
        $this->assertEquals($patient1->patient_id, $results->first()->patient_id);
    }

    public function test_patient_age_accessor_calculates_correctly(): void
    {
        $dob = now()->subYears(25)->format('Y-m-d');
        $patient = Patient::factory()->create(['date_of_birth' => $dob]);

        $this->assertEquals(25, $patient->age);
    }

    public function test_patient_fillable_attributes_are_mass_assignable(): void
    {
        $data = [
            'user_id' => User::factory()->create()->user_id,
            'patient_number' => 'PAT-TEST-0001',
            'date_of_birth' => '1990-01-01',
            'gender' => 'female',
            'blood_group' => 'O+',
            'height' => 165.5,
            'weight' => 60.0,
            'allergies' => 'Penicillin',
            'chronic_diseases' => 'None',
            'emergency_name' => 'Jane Doe',
            'emergency_contact' => '0712345678',
            'marital_status' => 'married',
            'occupation' => 'Teacher',
            'insurance_provider' => 'NHIF',
            'insurance_id' => 'NHIF-12345',
            'insurance_number' => 'POL-67890',
            'insurance_expiry' => '2025-12-31',
        ];

        $patient = Patient::create($data);

        foreach ($data as $key => $value) {
            if ($key !== 'user_id') {
                $actual = $patient->{$key};
                // Handle date comparison (Carbon vs string)
                if ($actual instanceof Carbon) {
                    $this->assertEquals($value, $actual->format('Y-m-d'));
                } else {
                    $this->assertEquals($value, $actual);
                }
            }
        }
    }
}
