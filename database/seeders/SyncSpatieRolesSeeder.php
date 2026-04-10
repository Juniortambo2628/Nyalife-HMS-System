<?php

namespace Database\Seeders;

use App\Models\Role as AppRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class SyncSpatieRolesSeeder extends Seeder
{
    /**
     * Sync existing app roles into Spatie's spatie_roles table and assign users.
     * Run after migration: php artisan db:seed --class=SyncSpatieRolesSeeder
     */
    public function run(): void
    {
        $guard = 'web';

        AppRole::all()->each(function (AppRole $appRole) use ($guard) {
            Role::firstOrCreate(
                ['name' => $appRole->role_name, 'guard_name' => $guard]
            );
        });

        User::with('roleRelation')->get()->each(function (User $user) use ($guard) {
            $roleName = $user->roleRelation?->role_name ?? 'patient';
            $role = Role::where('name', $roleName)->where('guard_name', $guard)->first();
            if ($role && ! $user->hasRole($roleName)) {
                $user->assignRole($role);
            }
        });
    }
}
