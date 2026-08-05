<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'amount' => $this->faker->randomFloat(2, 100, 100000),
            'payment_method' => $this->faker->randomElement(['cash', 'card', 'mpesa', 'insurance', 'bank_transfer']),
            'payment_date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d H:i:s'),
            'transaction_reference' => $this->faker->optional()->numerify('TXN-##########'),
            'payment_status' => $this->faker->randomElement(['completed', 'pending', 'failed', 'refunded']),
            'status' => $this->faker->randomElement(['completed', 'pending', 'failed', 'refunded']),
            'notes' => $this->faker->optional()->sentence(),
            'received_by' => User::factory(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'completed',
            'status' => 'completed',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'failed',
            'status' => 'failed',
        ]);
    }
}
