<?php

namespace Database\Factories;

use App\Models\InvoiceItem;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 10);
        $unitPrice = $this->faker->randomFloat(2, 50, 10000);

        return [
            'invoice_id' => Invoice::factory(),
            'item_type' => $this->faker->randomElement(['consultation', 'medication', 'lab_test', 'procedure', 'service', 'other']),
            'item_id_ref' => $this->faker->numberBetween(1, 1000),
            'description' => $this->faker->sentence(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $quantity * $unitPrice,
            'discount' => $this->faker->randomFloat(2, 0, $unitPrice * $quantity * 0.1),
            'tax' => $this->faker->randomFloat(2, 0, $unitPrice * $quantity * 0.16),
        ];
    }
}