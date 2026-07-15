<?php

namespace Database\Factories;

use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactMessageFactory extends Factory
{
    protected $model = ContactMessage::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'message' => $this->faker->paragraphs(3, true),
            'status' => $this->faker->randomElement(['pending', 'read', 'replied']),
            'read_at' => $this->faker->optional()->dateTimeBetween('-10 days', 'now')->format('Y-m-d H:i:s'),
            'reply' => $this->faker->optional()->paragraph(),
            'replied_at' => $this->faker->optional()->dateTimeBetween('-5 days', 'now')->format('Y-m-d H:i:s'),
            'replied_by' => \App\Models\User::factory(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'read_at' => null,
            'reply' => null,
            'replied_at' => null,
            'replied_by' => null,
        ]);
    }

    public function replied(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'replied',
            'reply' => $this->faker->paragraph(),
            'replied_at' => now()->format('Y-m-d H:i:s'),
            'replied_by' => \App\Models\User::factory(),
        ]);
    }
}