<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';
    protected $primaryKey = 'role_id';

    protected $fillable = [
        'role_name',
    ];

    /**
     * Get the users for this role.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'role_id');
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
