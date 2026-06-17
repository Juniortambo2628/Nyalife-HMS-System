<?php

namespace Database\Seeders;

use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionsSeeder extends Seeder
{
    /**
     * Create module permissions and assign them to Spatie roles.
     * Run after SyncSpatieRolesSeeder: php artisan db:seed --class=RolePermissionsSeeder
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'web';

        foreach (Permissions::all() as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => $guard]);
        }

        foreach (Permissions::roleMap() as $roleName => $permissionNames) {
            $role = Role::where('name', $roleName)->where('guard_name', $guard)->first();
            if (! $role) {
                continue;
            }

            $role->syncPermissions($permissionNames);
        }
    }
}
