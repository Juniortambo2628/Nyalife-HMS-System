<?php

namespace Database\Factories;

use App\Models\Medication;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicationFactory extends Factory
{
    protected $model = Medication::class;

    public function definition(): array
    {
        $types = ['tablet', 'capsule', 'syrup', 'suspension', 'injection', 'cream', 'ointment', 'drops', 'inhaler', 'suppository'];
        $strengths = ['10mg', '20mg', '50mg', '100mg', '250mg', '500mg', '1g', '5ml', '10ml', '100ml'];
        $units = ['tablet', 'capsule', 'ml', 'g', 'mg', 'vial', 'ampoule', 'tube', 'bottle'];

        $names = [
            'Amoxicillin', 'Ciprofloxacin', 'Metronidazole', 'Azithromycin', 'Doxycycline',
            'Paracetamol', 'Ibuprofen', 'Diclofenac', 'Naproxen', 'Celecoxib',
            'Omeprazole', 'Ranitidine', 'Pantoprazole', 'Esomeprazole', 'Lansoprazole',
            'Amlodipine', 'Lisinopril', 'Losartan', 'Hydrochlorothiazide', 'Bisoprolol',
            'Metformin', 'Glibenclamide', 'Insulin', 'Sitagliptin', 'Empagliflozin',
            'Folic Acid', 'Ferrous Sulphate', 'Vitamin D', 'Calcium', 'Multivitamin',
            'Salbutamol', 'Beclomethasone', 'Montelukast', 'Theophylline', 'Prednisolone',
        ];

        return [
            'medication_name' => $this->faker->unique()->randomElement($names),
            'medication_type' => $this->faker->randomElement($types),
            'description' => $this->faker->optional()->sentence(),
            'strength' => $this->faker->randomElement($strengths),
            'unit' => $this->faker->randomElement($units),
            'stock_quantity' => $this->faker->numberBetween(0, 1000),
            'price_per_unit' => $this->faker->randomFloat(2, 5, 500),
            'expiry_date' => $this->faker->dateTimeBetween('+6 months', '+2 years')->format('Y-m-d'),
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => ['stock_quantity' => $this->faker->numberBetween(0, 10)]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => ['expiry_date' => $this->faker->dateTimeBetween('-1 year', '-1 day')->format('Y-m-d')]);
    }
}