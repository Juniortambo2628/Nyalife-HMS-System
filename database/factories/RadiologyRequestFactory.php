<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\RadiologyRequest;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RadiologyRequestFactory extends Factory
{
    protected $model = RadiologyRequest::class;

    public function definition(): array
    {
        return [
            'request_number' => 'RAD-'.strtoupper(uniqid()),
            'patient_id' => Patient::factory(),
            'doctor_id' => Staff::factory(),
            'scan_type' => $this->faker->randomElement([
                'Pelvic Ultrasound',
                'Abdominal Ultrasound',
                'Obstetric Ultrasound (1st Trimester)',
                'Obstetric Ultrasound (2nd/3rd Trimester)',
                'Transvaginal Ultrasound',
                'Breast Ultrasound',
                'Thyroid Ultrasound',
                'Renal Ultrasound',
                'Scrotal Ultrasound',
                'Doppler Ultrasound',
                'X-Ray Chest',
                'X-Ray Abdomen',
                'X-Ray Pelvis',
                'X-Ray Extremities',
                'Mammography',
                'CT Scan',
                'MRI',
            ]),
            'clinical_indication' => $this->faker->paragraph(),
            'scan_details' => $this->faker->optional()->paragraph(),
            'findings' => $this->faker->optional()->paragraph(),
            'impression' => $this->faker->optional()->paragraph(),
            'priority' => $this->faker->randomElement(['routine', 'urgent', 'stat']),
            'status' => $this->faker->randomElement(['pending', 'processing', 'pending_verification', 'verified', 'completed', 'cancelled']),
            'requested_by' => User::factory(),
            'assigned_to' => User::factory(),
            'verified_by' => User::factory(),
            'verified_at' => $this->faker->optional()->dateTimeBetween('-5 days', 'now')->format('Y-m-d H:i:s'),
            'completed_at' => $this->faker->optional()->dateTimeBetween('-10 days', 'now')->format('Y-m-d H:i:s'),
            'consultation_id' => Consultation::factory(),
            'appointment_id' => Appointment::factory(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'pending']);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => now()->format('Y-m-d H:i:s'),
            'findings' => $this->faker->paragraph(),
            'impression' => $this->faker->paragraph(),
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'verified',
            'verified_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }
}
