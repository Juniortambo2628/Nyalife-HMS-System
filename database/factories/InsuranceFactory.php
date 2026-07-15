<?php

namespace Database\Factories;

use App\Models\Insurance;
use Illuminate\Database\Eloquent\Factories\Factory;

class InsuranceFactory extends Factory
{
    protected $model = Insurance::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'NHIF',
                'AAR Insurance',
                'Jubilee Insurance',
                'Britam Insurance',
                'CIC Insurance',
                'UAP Old Mutual',
                'Sanlam Insurance',
                'Madison Insurance',
                'APA Insurance',
                'GA Insurance',
                'Resolution Health',
                'Metropolitan Cannon',
                'Heritage Insurance',
                'First Assurance',
                'Kenindia Assurance',
            ]),
            'logo_path' => $this->faker->optional()->imageUrl(200, 100),
            'link' => $this->faker->optional()->url(),
            'is_active' => $this->faker->boolean(80),
            'sort_order' => $this->faker->numberBetween(1, 50),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}