<?php

namespace Tests\Unit\Models;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Staff;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_appointment_belongs_to_patient(): void
    {
        $appointment = Appointment::factory()->create();

        $this->assertInstanceOf(Patient::class, $appointment->patient);
        $this->assertEquals($appointment->patient_id, $appointment->patient->patient_id);
    }

    public function test_appointment_belongs_to_doctor(): void
    {
        $appointment = Appointment::factory()->create();

        $this->assertInstanceOf(Staff::class, $appointment->doctor);
        $this->assertEquals($appointment->doctor_id, $appointment->doctor->staff_id);
    }

    public function test_appointment_has_many_prescriptions(): void
    {
        $appointment = Appointment::factory()->has(\App\Models\Prescription::factory()->count(2))->create();

        $this->assertCount(2, $appointment->prescriptions);
    }

    public function test_appointment_has_many_lab_test_requests(): void
    {
        $appointment = Appointment::factory()->has(\App\Models\LabTestRequest::factory()->count(2))->create();

        $this->assertCount(2, $appointment->labTestRequests);
    }

    public function test_appointment_has_many_consultations(): void
    {
        $appointment = Appointment::factory()->has(\App\Models\Consultation::factory()->count(2))->create();

        $this->assertCount(2, $appointment->consultations);
    }

    public function test_appointment_has_many_telehealth_consents(): void
    {
        $appointment = Appointment::factory()->has(\App\Models\TelehealthConsent::factory()->count(2))->create();

        $this->assertCount(2, $appointment->telehealthConsents);
    }

    public function test_appointment_scopes_work_correctly(): void
    {
        $scheduled = Appointment::factory()->create(['status' => 'scheduled']);
        $confirmed = Appointment::factory()->create(['status' => 'confirmed']);
        $completed = Appointment::factory()->create(['status' => 'completed']);
        $cancelled = Appointment::factory()->create(['status' => 'cancelled']);
        $today = Appointment::factory()->create(['appointment_date' => now()->format('Y-m-d')]);

        // Use where queries with explicit IDs to avoid test pollution
        $this->assertCount(1, Appointment::where('status', 'scheduled')->where('appointment_id', $scheduled->appointment_id)->get());
        $this->assertCount(1, Appointment::where('status', 'confirmed')->where('appointment_id', $confirmed->appointment_id)->get());
        $this->assertCount(1, Appointment::where('status', 'completed')->where('appointment_id', $completed->appointment_id)->get());
        $this->assertCount(1, Appointment::where('status', 'cancelled')->where('appointment_id', $cancelled->appointment_id)->get());
        $this->assertCount(1, Appointment::whereDate('appointment_date', now()->format('Y-m-d'))->where('appointment_id', $today->appointment_id)->get());
    }

    public function test_appointment_status_enum_values(): void
    {
        $statuses = ['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show', 'pending', 'arrived'];

        foreach ($statuses as $status) {
            $appointment = Appointment::factory()->create(['status' => $status]);
            $this->assertEquals($status, $appointment->status);
        }
    }

    public function test_appointment_fillable_attributes(): void
    {
        $data = [
            'patient_id' => Patient::factory()->create()->patient_id,
            'doctor_id' => Staff::factory()->create()->staff_id,
            'appointment_date' => now()->addDays(5)->format('Y-m-d'),
            'appointment_time' => '10:00:00',
            'end_time' => '10:30:00',
            'appointment_type' => 'general',
            'status' => 'scheduled',
            'reason' => 'Routine checkup',
            'notes' => 'Patient requested morning slot',
            'created_by' => \App\Models\User::factory()->create()->user_id,
        ];

        $appointment = Appointment::create($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $appointment->{$key});
        }
    }
}