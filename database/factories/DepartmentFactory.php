<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        $types = ['clinical', 'administrative', 'support'];
        $names = [
            'Obstetrics & Gynecology' => 'clinical',
            'Internal Medicine' => 'clinical',
            'Pediatrics' => 'clinical',
            'General Surgery' => 'clinical',
            'Emergency' => 'clinical',
            'Laboratory' => 'support',
            'Pharmacy' => 'support',
            'Radiology' => 'support',
            'Nursing' => 'clinical',
            'Administration' => 'administrative',
            'Finance' => 'administrative',
            'Human Resources' => 'administrative',
            'Records' => 'administrative',
            'Maintenance' => 'support',
            'IT' => 'support',
        ];

        $name = $this->faker->randomElement(array_keys($names));

        return [
            'department_name' => $name,
            'description' => $this->faker->sentence(),
            'is_active' => true,
            'code' => strtoupper(substr($name, 0, 4)).$this->faker->unique()->numberBetween(10, 99),
            'type' => $names[$name],
            'head_name' => $this->faker->optional()->name(),
            'head_position' => $this->faker->optional()->jobTitle(),
            'head_image' => null,
        ];
    }
}
