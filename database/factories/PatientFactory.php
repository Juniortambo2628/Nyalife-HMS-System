<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        $insuranceExpiry = $this->faker->boolean(50)
            ? $this->faker->dateTimeBetween('now', '+2 years')->format('Y-m-d')
            : null;

        return [
            'user_id' => User::factory(),
            'patient_number' => 'PAT-'.date('Ymd').'-'.str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'date_of_birth' => $this->faker->dateTimeBetween('-80 years', '-18 years')->format('Y-m-d'),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'address' => $this->faker->address(),
            'blood_group' => $this->faker->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            'height' => $this->faker->randomFloat(2, 150, 200),
            'weight' => $this->faker->randomFloat(2, 45, 120),
            'allergies' => $this->faker->boolean(50) ? $this->faker->sentence() : null,
            'chronic_diseases' => $this->faker->boolean(50) ? $this->faker->sentence() : null,
            'emergency_name' => $this->faker->name(),
            'emergency_contact' => $this->faker->numerify('07########'),
            'marital_status' => $this->faker->randomElement(['single', 'married', 'divorced', 'widowed']),
            'occupation' => $this->faker->jobTitle(),
            'insurance_provider' => $this->faker->boolean(50) ? $this->faker->company() : null,
            'insurance_id' => $this->faker->boolean(50) ? $this->faker->numerify('INS-#######') : null,
            'insurance_number' => $this->faker->boolean(50) ? $this->faker->numerify('POL-#######') : null,
            'insurance_expiry' => $insuranceExpiry,
        ];
    }
}
