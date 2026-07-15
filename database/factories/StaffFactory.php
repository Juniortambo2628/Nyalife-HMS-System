<?php

namespace Database\Factories;

use App\Models\Staff;
use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffFactory extends Factory
{
    protected $model = Staff::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'employee_id' => 'EMP-' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'specialization' => $this->faker->randomElement([
                'Obstetrics & Gynecology',
                'Internal Medicine',
                'Pediatrics',
                'General Surgery',
                'Cardiology',
                'Neurology',
                'Dermatology',
                'Orthopedics',
                'Radiology',
                'Anesthesiology',
                'Emergency Medicine',
                'Family Medicine',
            ]),
            'department' => $this->faker->randomElement([
                'Obstetrics & Gynecology',
                'Internal Medicine',
                'Pediatrics',
                'Surgery',
                'Emergency',
                'Laboratory',
                'Pharmacy',
                'Radiology',
            ]),
            'department_id' => Department::factory(),
            'position' => $this->faker->randomElement([
                'Consultant',
                'Senior Registrar',
                'Registrar',
                'Medical Officer',
                'Intern',
                'Nurse',
                'Senior Nurse',
                'Nurse Manager',
                'Lab Technician',
                'Senior Lab Technician',
                'Pharmacist',
                'Senior Pharmacist',
            ]),
            'license_number' => $this->faker->optional()->numerify('LIC-#######'),
            'qualification' => $this->faker->optional()->sentence(3),
            'join_date' => $this->faker->dateTimeBetween('-10 years', 'now')->format('Y-m-d'),
            'emergency_contact' => $this->faker->numerify('07########'),
            'emergency_name' => $this->faker->name(),
        ];
    }
}