<?php

namespace Database\Factories;

use App\Models\LabSample;
use App\Models\LabTestType;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LabSampleFactory extends Factory
{
    protected $model = LabSample::class;

    public function definition(): array
    {
        return [
            'sample_id' => 'SMP-'.date('Ymd').'-'.str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'patient_id' => Patient::factory(),
            'test_type_id' => LabTestType::factory(),
            'sample_type' => $this->faker->randomElement(['blood', 'urine', 'stool', 'sputum', 'csf', 'swab', 'tissue', 'other']),
            'collected_date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'collected_by' => User::factory(),
            'collected_at' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d H:i:s'),
            'status' => $this->faker->randomElement(['registered', 'collected', 'processing', 'completed', 'rejected']),
            'completed_by' => User::factory(),
            'completed_at' => $this->faker->optional()->dateTimeBetween('-10 days', 'now')->format('Y-m-d H:i:s'),
            'notes' => $this->faker->optional()->sentence(),
            'urgent' => $this->faker->boolean(20),
        ];
    }

    public function collected(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'collected']);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }
}
