<?php

namespace Database\Factories;

use App\Models\LabTestRequest;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\LabTestType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LabTestRequestFactory extends Factory
{
    protected $model = LabTestRequest::class;

    public function definition(): array
    {
        $completedAt = $this->faker->boolean(50)
            ? $this->faker->dateTimeBetween('-10 days', 'now')->format('Y-m-d H:i:s')
            : null;

        $verifiedAt = $this->faker->boolean(50)
            ? $this->faker->dateTimeBetween('-5 days', 'now')->format('Y-m-d H:i:s')
            : null;

        return [
            'request_number' => 'LAB-' . strtoupper(uniqid()),
            'patient_id' => Patient::factory(),
            'doctor_id' => Staff::factory(),
            'test_type_id' => LabTestType::factory(),
            'priority' => $this->faker->randomElement(['normal', 'urgent', 'stat']),
            'requested_by' => \App\Models\User::factory(),
            'status' => $this->faker->randomElement(['pending', 'sample_collected', 'processing', 'pending_verification', 'verified', 'completed', 'cancelled']),
            'request_date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d H:i:s'),
            'completed_at' => $completedAt,
            'assigned_to' => \App\Models\User::factory(),
            'sample_collected_by' => \App\Models\User::factory(),
            'verified_by' => \App\Models\User::factory(),
            'verified_at' => $verifiedAt,
            'notes' => $this->faker->optional(0.5)->sentence(),
            'consultation_id' => \App\Models\Consultation::factory(),
            'appointment_id' => \App\Models\Appointment::factory(),
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
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'verified',
            'verified_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    public function withResults(): static
    {
        return $this->state(fn (array $attributes) => [
            'results' => json_encode([
                'test_name' => 'Sample Test',
                'result' => $this->faker->randomFloat(2, 10, 200),
                'unit' => 'mg/dL',
                'reference_range' => '10-100',
                'flag' => $this->faker->randomElement(['normal', 'high', 'low']),
            ]),
        ]);
    }
}