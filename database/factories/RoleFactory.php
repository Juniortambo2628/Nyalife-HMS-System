<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        $roles = [
            ['admin', 'System Administrator'],
            ['doctor', 'Doctor'],
            ['nurse', 'Nurse'],
            ['receptionist', 'Receptionist'],
            ['lab_technician', 'Lab Technician'],
            ['pharmacist', 'Pharmacist'],
            ['patient', 'Patient'],
        ];

        $role = $this->faker->unique()->randomElement($roles);

        return [
            'role_name' => $role[0],
        ];
    }

    public static function getDefaultRoles(): array
    {
        return [
            'admin',
            'doctor',
            'nurse',
            'receptionist',
            'lab_technician',
            'pharmacist',
            'patient',
        ];
    }
}
