<?php

namespace Database\Factories;

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\User;
use App\Models\Vital;
use Illuminate\Database\Eloquent\Factories\Factory;

class VitalFactory extends Factory
{
    protected $model = Vital::class;

    public function definition(): array
    {
        $systolic = $this->faker->numberBetween(90, 160);
        $diastolic = $this->faker->numberBetween(60, 100);
        $height = $this->faker->randomFloat(2, 1.5, 1.95);
        $weight = $this->faker->randomFloat(2, 45, 120);
        $bmi = $weight / ($height * $height);

        return [
            'patient_id' => Patient::factory(),
            'consultation_id' => Consultation::factory(),
            'blood_pressure' => "{$systolic}/{$diastolic}",
            'heart_rate' => $this->faker->numberBetween(50, 120),
            'respiratory_rate' => $this->faker->numberBetween(12, 30),
            'temperature' => $this->faker->randomFloat(1, 35.5, 39.5),
            'weight' => $weight,
            'height' => $height,
            'bmi' => round($bmi, 2),
            'pain_level' => $this->faker->numberBetween(0, 10),
            'oxygen_saturation' => $this->faker->numberBetween(90, 100),
            'priority' => $this->faker->randomElement(['normal', 'urgent', 'emergency']),
            'notes' => $this->faker->optional()->sentence(),
            'measured_at' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d H:i:s'),
            'recorded_by' => User::factory(),
            'is_voided' => false,
            'void_reason' => null,
            'voided_by' => null,
            'voided_at' => null,
        ];
    }

    public function normal(): static
    {
        return $this->state(fn (array $attributes) => [
            'blood_pressure' => '120/80',
            'heart_rate' => 72,
            'temperature' => 37.0,
            'oxygen_saturation' => 99,
            'priority' => 'normal',
        ]);
    }

    public function abnormal(): static
    {
        return $this->state(fn (array $attributes) => [
            'blood_pressure' => '150/95',
            'heart_rate' => 110,
            'temperature' => 38.5,
            'oxygen_saturation' => 92,
            'priority' => 'urgent',
        ]);
    }

    public function voided(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_voided' => true,
            'void_reason' => $this->faker->sentence(),
            'voided_by' => User::factory(),
            'voided_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }
}
