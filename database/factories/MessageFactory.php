<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'sender_id' => User::factory(),
            'receiver_id' => User::factory(),
            'content' => $this->faker->paragraph(),
            'metadata' => $this->faker->optional()->randomElement([
                null,
                json_encode(['type' => 'text', 'priority' => 'normal']),
                json_encode(['type' => 'system', 'action' => 'notification']),
                json_encode(['type' => 'file', 'filename' => $this->faker->word() . '.pdf']),
            ]),
            'read_at' => $this->faker->optional()->dateTimeBetween('-5 days', 'now')->format('Y-m-d H:i:s'),
            'sender_archived_at' => $this->faker->optional()->dateTimeBetween('-10 days', 'now')->format('Y-m-d H:i:s'),
            'receiver_archived_at' => $this->faker->optional()->dateTimeBetween('-10 days', 'now')->format('Y-m-d H:i:s'),
        ];
    }

    public function unread(): static
    {
        return $this->state(fn (array $attributes) => ['read_at' => null]);
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }
}