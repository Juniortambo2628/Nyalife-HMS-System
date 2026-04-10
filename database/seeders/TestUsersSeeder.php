<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates test users for each role in the system.
     */
    public function run(): void
    {
        // Define roles - these should match the roles table in the database
        $roles = [
            'admin' => 'Admin',
            'doctor' => 'Doctor',
            'nurse' => 'Nurse',
            'lab_technician' => 'Lab Technician',
            'pharmacist' => 'Pharmacist',
            'receptionist' => 'Receptionist',
            'patient' => 'Patient',
        ];

        // Ensure all roles exist and get their IDs
        $roleIds = [];
        foreach ($roles as $roleName => $roleDisplay) {
            $role = DB::table('roles')->where('role_name', $roleName)->first();
            if ($role) {
                $roleIds[$roleName] = $role->role_id;
            } else {
                // Create the role if it doesn't exist
                $roleIds[$roleName] = DB::table('roles')->insertGetId([
                    'role_name' => $roleName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->command->info("Created role: {$roleName}");
            }
        }

        // Define test users for each role
        $testUsers = [
            [
                'username' => 'admin',
                'first_name' => 'System',
                'last_name' => 'Admin',
                'email' => 'admin@nyalife.com',
                'role' => 'admin',
            ],
            [
                'username' => 'doctor',
                'first_name' => 'John',
                'last_name' => 'Mwangi',
                'email' => 'doctor@nyalife.com',
                'role' => 'doctor',
            ],
            [
                'username' => 'nurse',
                'first_name' => 'Mary',
                'last_name' => 'Wanjiku',
                'email' => 'nurse@nyalife.com',
                'role' => 'nurse',
            ],
            [
                'username' => 'labtech',
                'first_name' => 'Peter',
                'last_name' => 'Omondi',
                'email' => 'labtech@nyalife.com',
                'role' => 'lab_technician',
            ],
            [
                'username' => 'pharmacist',
                'first_name' => 'Grace',
                'last_name' => 'Achieng',
                'email' => 'pharmacist@nyalife.com',
                'role' => 'pharmacist',
            ],
            [
                'username' => 'receptionist',
                'first_name' => 'Sarah',
                'last_name' => 'Njeri',
                'email' => 'receptionist@nyalife.com',
                'role' => 'receptionist',
            ],
            [
                'username' => 'patient',
                'first_name' => 'James',
                'last_name' => 'Kamau',
                'email' => 'patient@nyalife.com',
                'role' => 'patient',
            ],
        ];

        // Create or update each test user
        foreach ($testUsers as $userData) {
            $exists = DB::table('users')->where('username', $userData['username'])->exists();
            
            if (!$exists) {
                DB::table('users')->insert([
                    'username' => $userData['username'],
                    'first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name'],
                    'email' => $userData['email'],
                    'password' => Hash::make('password'),
                    'role_id' => $roleIds[$userData['role']],
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->command->info("Created user: {$userData['username']} ({$userData['role']})");
            } else {
                DB::table('users')->where('username', $userData['username'])->update([
                    'password' => Hash::make('password'),
                    'role_id' => $roleIds[$userData['role']],
                    'is_active' => 1,
                    'updated_at' => now(),
                ]);
                $this->command->info("Updated user: {$userData['username']} ({$userData['role']})");
            }
        }

        $this->command->newLine();
        $this->command->info('==============================================');
        $this->command->info('Test Users Created/Updated:');
        $this->command->info('==============================================');
        $this->command->info('All users have password: password');
        $this->command->newLine();
        
        $this->command->table(
            ['Username', 'Email', 'Role'],
            array_map(fn($u) => [$u['username'], $u['email'], $u['role']], $testUsers)
        );
    }
}
