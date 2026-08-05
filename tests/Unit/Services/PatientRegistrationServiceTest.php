<?php

namespace Tests\Unit\Services;

use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use App\Services\PatientRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientRegistrationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create legacy roles so Role::idFromName() works
        Role::firstOrCreate(['role_name' => 'admin']);
        Role::firstOrCreate(['role_name' => 'doctor']);
        Role::firstOrCreate(['role_name' => 'nurse']);
        Role::firstOrCreate(['role_name' => 'receptionist']);
        Role::firstOrCreate(['role_name' => 'lab_technician']);
        Role::firstOrCreate(['role_name' => 'pharmacist']);
        Role::firstOrCreate(['role_name' => 'patient']);
    }

    public function test_register_creates_user_and_patient(): void
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '0712345678',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'address' => '123 Main St',
            'blood_group' => 'O+',
            'height' => 175,
            'weight' => 70,
            'allergies' => 'None',
            'chronic_diseases' => 'None',
            'emergency_name' => 'Jane Doe',
            'emergency_contact' => '0787654321',
            'marital_status' => 'single',
            'occupation' => 'Engineer',
            'insurance_provider' => 'NHIF',
            'insurance_id' => 'NHIF-12345',
            'insurance_number' => 'POL-67890',
            'insurance_expiry' => '2025-12-31',
        ];

        $result = PatientRegistrationService::register($data);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('patient', $result);
        $this->assertInstanceOf(User::class, $result['user']);
        $this->assertInstanceOf(Patient::class, $result['patient']);
        $this->assertEquals('john.doe@example.com', $result['user']->email);
        $this->assertEquals('John', $result['user']->first_name);
        $this->assertEquals('Doe', $result['user']->last_name);
        $this->assertEquals($result['user']->user_id, $result['patient']->user_id);
        $this->assertStringStartsWith('PAT-', $result['patient']->patient_number);
    }

    public function test_quick_register_creates_minimal_patient(): void
    {
        $data = [
            'first_name' => 'Quick',
            'last_name' => 'Patient',
            'email' => 'quick@example.com',
            'phone' => '0722334455',
            'gender' => 'female',
            'date_of_birth' => '1995-06-15',
        ];

        $result = PatientRegistrationService::quickRegister($data);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('patient', $result);
        $this->assertStringStartsWith('PAT-', $result['patient']->patient_number);
        $this->assertEquals('quick@example.com', $result['user']->email);
    }
}
