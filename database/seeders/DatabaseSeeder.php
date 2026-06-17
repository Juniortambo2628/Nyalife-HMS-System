<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            AdminUserSeeder::class,
            SyncSpatieRolesSeeder::class,
            RolePermissionsSeeder::class,
            CMSSettingsSeeder::class,
            ServiceTabSeeder::class,
            BlogSeeder::class,
        ]);

        // For a full demo clinic dataset (appointments, labs, billing, etc.):
        // php artisan db:seed --class=DemoEnvironmentSeeder
    }
}
