<?php

namespace Database\Factories;

use App\Models\Medication;
use App\Models\MedicationBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicationBatchFactory extends Factory
{
    protected $model = MedicationBatch::class;

    public function definition(): array
    {
        return [
            'medication_id' => Medication::factory(),
            'batch_number' => 'BATCH-'.strtoupper($this->faker->bothify('???####')),
            'quantity' => $this->faker->numberBetween(50, 500),
            'expiry_date' => $this->faker->dateTimeBetween('+6 months', '+2 years')->format('Y-m-d'),
            'manufacturing_date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function expiringSoon(): static
    {
        return $this->state(fn (array $attributes) => ['expiry_date' => $this->faker->dateTimeBetween('now', '+3 months')->format('Y-m-d')]);
    }
}
