<?php

namespace Database\Factories;

use App\Models\Medication;
use App\Models\PharmacyPurchaseOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class PharmacyPurchaseOrderFactory extends Factory
{
    protected $model = PharmacyPurchaseOrder::class;

    public function definition(): array
    {
        return [
            'order_number' => 'PO-'.date('Ymd').'-'.str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'medication_id' => Medication::factory(),
            'medication_name' => $this->faker->word().' '.$this->faker->randomElement(['Tablets', 'Capsules', 'Syrup', 'Injection']),
            'quantity' => $this->faker->numberBetween(50, 500),
            'supplier_name' => $this->faker->randomElement([
                'Global Pharma Distributors',
                'MedSupplies Kenya Ltd',
                'PharmaLink East Africa',
                'Healthcare Solutions Ltd',
                'MediCare Distributors',
            ]),
            'estimated_cost' => $this->faker->randomFloat(2, 5000, 200000),
            'status' => $this->faker->randomElement(['pending', 'ordered', 'received', 'cancelled']),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'pending']);
    }

    public function ordered(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'ordered']);
    }

    public function received(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'received']);
    }
}
