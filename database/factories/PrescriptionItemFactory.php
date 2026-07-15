<?php

namespace Database\Factories;

use App\Models\PrescriptionItem;
use App\Models\Prescription;
use App\Models\Medication;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrescriptionItemFactory extends Factory
{
    protected $model = PrescriptionItem::class;

    public function definition(): array
    {
        $frequencies = ['once daily', 'twice daily', 'three times daily', 'four times daily', 'every 6 hours', 'every 8 hours', 'at bedtime', 'as needed'];
        $durations = ['3 days', '5 days', '7 days', '10 days', '14 days', '21 days', '1 month', '3 months'];

        $dispensedAt = $this->faker->boolean(50)
            ? $this->faker->dateTimeBetween('-10 days', 'now')->format('Y-m-d H:i:s')
            : null;

        return [
            'prescription_id' => Prescription::factory(),
            'medication_id' => Medication::factory(),
            'dosage' => $this->faker->randomElement(['1 tablet', '2 tablets', '5ml', '10ml', '1 capsule', '1 ampoule', '1 vial']),
            'frequency' => $this->faker->randomElement($frequencies),
            'quantity' => $this->faker->numberBetween(1, 90),
            'duration' => $this->faker->randomElement($durations),
            'instructions' => $this->faker->boolean(50) ? $this->faker->sentence() : null,
            'status' => $this->faker->randomElement(['pending', 'dispensed', 'partial']),
            'dispensed_by' => \App\Models\User::factory(),
            'dispensed_at' => $dispensedAt,
        ];
    }
}