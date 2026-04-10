<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure 'admin' role exists
        $adminRole = DB::table('roles')->where('role_name', 'admin')->first();

        if ($adminRole) {
            $roleId = $adminRole->role_id;
        } else {
            // Create admin role if it doesn't exist (assuming roles table structure)
            // Note: We should probably check if roles table exists, but let's assume it does from legacy
            $roleId = DB::table('roles')->insertGetId([
                'role_name' => 'admin',
                // Add description if needed? Legacy db might vary.
            ]);
        }

        // 2. Create Admin User
        // Check if user exists
        $exists = DB::table('users')->where('username', 'admin')->exists();

        if (!$exists) {
            DB::table('users')->insert([
                'username' => 'admin',
                'first_name' => 'System',
                'last_name' => 'Admin',
                'email' => 'admin@nyalife.com',
                'password' => Hash::make('password'), // Default password
                'role_id' => $roleId,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info('Admin user created. Username: admin, Password: password');
        } else {
            DB::table('users')->where('username', 'admin')->update([
                'password' => Hash::make('password'),
                'is_active' => 1, // Ensure active
            ]);
            $this->command->info('Admin user already exists. Password reset to: password');
        }
    }
}
