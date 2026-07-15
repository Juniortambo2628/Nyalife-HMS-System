<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Consultation;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $totalAmount = $this->faker->randomFloat(2, 1000, 500000);
        $discount = $this->faker->randomFloat(2, 0, $totalAmount * 0.1);
        $tax = $this->faker->randomFloat(2, 0, $totalAmount * 0.16);

        return [
            'patient_id' => Patient::factory(),
            'consultation_id' => Consultation::factory(),
            'doctor_id' => Staff::factory(),
            'invoice_number' => 'INV-' . strtoupper(uniqid()),
            'invoice_date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'total_amount' => $totalAmount,
            'discount' => $discount,
            'tax' => $tax,
            'status' => $this->faker->randomElement(['pending', 'paid', 'partially_paid', 'overdue', 'cancelled']),
            'payment_method' => $this->faker->optional()->randomElement(['cash', 'card', 'mpesa', 'insurance', 'bank_transfer']),
            'notes' => $this->faker->optional()->paragraph(),
            'created_by' => User::factory(),
            'insurance_claim_id' => $this->faker->optional()->numerify('CLM-#######'),
            'insurance_coverage' => $this->faker->optional()->randomFloat(2, 0, $totalAmount),
            'patient_responsibility' => $this->faker->optional()->randomFloat(2, 0, $totalAmount),
            'is_voided' => false,
            'void_reason' => null,
            'voided_by' => null,
            'voided_at' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'pending']);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'paid']);
    }

    public function partiallyPaid(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'partially_paid']);
    }

    public function voided(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_voided' => true,
            'status' => 'cancelled',
            'void_reason' => $this->faker->sentence(),
            'voided_by' => User::factory(),
            'voided_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }
}