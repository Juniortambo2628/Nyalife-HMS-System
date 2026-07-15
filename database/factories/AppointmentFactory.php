<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => Staff::factory(),
            'appointment_date' => $this->faker->dateTimeBetween('-30 days', '+30 days')->format('Y-m-d'),
            'appointment_time' => $this->faker->time('H:i'),
            'end_time' => $this->faker->optional()->time('H:i'),
            'appointment_type' => $this->faker->randomElement(['general', 'follow_up', 'telehealth', 'emergency']),
            'status' => $this->faker->randomElement(['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show', 'pending', 'arrived']),
            'reason' => $this->faker->sentence(),
            'notes' => $this->faker->optional()->paragraph(),
            'created_by' => \App\Models\User::factory(),
        ];
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'scheduled']);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'confirmed']);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'completed']);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'cancelled']);
    }

    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'appointment_date' => now()->format('Y-m-d'),
        ]);
    }

    public function telehealth(): static
    {
        return $this->state(fn (array $attributes) => ['appointment_type' => 'telehealth']);
    }
}