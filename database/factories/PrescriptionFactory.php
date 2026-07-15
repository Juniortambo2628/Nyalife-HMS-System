<?php

namespace Database\Factories;

use App\Models\Prescription;
use App\Models\Patient;
use App\Models\User;
use App\Models\Consultation;
use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrescriptionFactory extends Factory
{
    protected $model = Prescription::class;

    public function definition(): array
    {
        $dispensedBy = $this->faker->boolean(50) ? User::factory() : null;
        $dispensedAt = $this->faker->boolean(50)
            ? $this->faker->dateTimeBetween('-10 days', 'now')->format('Y-m-d H:i:s')
            : null;

        return [
            'patient_id' => Patient::factory(),
            'prescribed_by' => User::factory(),
            'appointment_id' => Appointment::factory(),
            'consultation_id' => Consultation::factory(),
            'prescription_date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'prescription_number' => 'RX-' . strtoupper(uniqid()),
            'status' => $this->faker->randomElement(['pending', 'dispensed', 'partially_dispensed', 'cancelled']),
            'is_voided' => false,
            'void_reason' => null,
            'voided_by' => null,
            'voided_at' => null,
            'notes' => $this->faker->boolean(50) ? $this->faker->paragraph() : null,
            'dispensed_by' => $dispensedBy,
            'dispensed_at' => $dispensedAt,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'pending']);
    }

    public function dispensed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'dispensed',
            'dispensed_by' => User::factory(),
            'dispensed_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    public function voided(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_voided' => true,
            'status' => 'cancelled',
            'void_reason' => $this->faker->sentence(),
            'voided_by' => User::factory(),
            'voided_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }
}