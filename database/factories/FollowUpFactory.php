<?php

namespace Database\Factories;

use App\Models\FollowUp;
use App\Models\Patient;
use App\Models\Consultation;
use Illuminate\Database\Eloquent\Factories\Factory;

class FollowUpFactory extends Factory
{
    protected $model = FollowUp::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'consultation_id' => Consultation::factory(),
            'follow_up_date' => $this->faker->dateTimeBetween('now', '+90 days')->format('Y-m-d'),
            'follow_up_type' => $this->faker->randomElement(['general', 'post_op', 'postnatal', 'lab_results', 'imaging_results', 'medication_review', 'chronic_disease']),
            'reason' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['scheduled', 'completed', 'cancelled', 'no_show', 'rescheduled']),
            'notes' => $this->faker->optional()->paragraph(),
            'created_by' => \App\Models\User::factory(),
        ];
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'scheduled']);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'completed']);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'cancelled']);
    }

    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'follow_up_date' => $this->faker->dateTimeBetween('-60 days', '-1 day')->format('Y-m-d'),
            'status' => $this->faker->randomElement(['completed', 'cancelled', 'no_show']),
        ]);
    }
}