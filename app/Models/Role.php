<?php

namespace App\Models;

use App\Support\Permissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $primaryKey = 'role_id';

    protected $fillable = [
        'role_name',
    ];

    protected static function booted(): void
    {
        static::created(function (self $role): void {
            $role->syncSpatieRole();
        });

        static::updated(function (self $role): void {
            if ($role->isDirty('role_name')) {
                $role->syncSpatieRole();
            }
        });
    }

    /**
     * Get the users for this role.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'role_id');
    }

    public function syncSpatieRole(): void
    {
        $spatieRole = SpatieRole::firstOrCreate([
            'name' => $this->role_name,
            'guard_name' => 'web',
        ]);

        $permissionNames = Permissions::roleMap()[$this->role_name] ?? [];
        foreach ($permissionNames as $permissionName) {
            SpatiePermission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $spatieRole->syncPermissions($permissionNames);
    }

    /**
     * Get the role_id for a given role name.
     */
    public static function idFromName(string $name): int
    {
        $role = static::where('role_name', $name)->first();

        if (! $role) {
            throw new \RuntimeException("Role '{$name}' not found in database.");
        }

        return $role->role_id;
    }
}
