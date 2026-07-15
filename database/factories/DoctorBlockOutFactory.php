<?php

namespace Database\Factories;

use App\Models\DoctorBlockOut;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorBlockOutFactory extends Factory
{
    protected $model = DoctorBlockOut::class;

    public function definition(): array
    {
        return [
            'doctor_id' => Staff::factory(),
            'block_date' => $this->faker->dateTimeBetween('-30 days', '+60 days')->format('Y-m-d'),
            'start_time' => $this->faker->time('H:i'),
            'end_time' => $this->faker->time('H:i'),
            'reason' => $this->faker->optional()->sentence(),
        ];
    }

    public function fullDay(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => '08:00',
            'end_time' => '17:00',
        ]);
    }

    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'block_date' => $this->faker->dateTimeBetween('-60 days', '-1 day')->format('Y-m-d'),
        ]);
    }

    public function future(): static
    {
        return $this->state(fn (array $attributes) => [
            'block_date' => $this->faker->dateTimeBetween('now', '+60 days')->format('Y-m-d'),
        ]);
    }
}