<?php

use App\Models\Role as AppRole;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'web';

        foreach (Permissions::all() as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => $guard]);
        }

        AppRole::query()->each(function (AppRole $appRole) use ($guard) {
            Role::firstOrCreate(
                ['name' => $appRole->role_name, 'guard_name' => $guard]
            );
        });

        foreach (Permissions::roleMap() as $roleName => $permissionNames) {
            $role = Role::where('name', $roleName)->where('guard_name', $guard)->first();
            if ($role) {
                $role->syncPermissions($permissionNames);
            }
        }

        User::with('roleRelation')->get()->each(function (User $user) use ($guard) {
            $roleName = $user->roleRelation?->role_name ?? 'patient';
            $role = Role::where('name', $roleName)->where('guard_name', $guard)->first();
            if ($role && ! $user->hasRole($roleName)) {
                $user->assignRole($role);
            }
        });

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Permissions are shared application config; leave data in place on rollback.
    }
};
