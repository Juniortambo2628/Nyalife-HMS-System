<?php

namespace App\Models;

use App\Traits\DescribesActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use DescribesActivity, HasApiTokens, HasFactory, LogsActivity, Notifiable;
    use HasRoles {
        hasPermissionTo as traitHasPermissionTo;
        hasAnyPermission as traitHasAnyPermission;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getActivityLabel(): string
    {
        return 'User';
    }

    protected $table = 'users';

    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'email',
        'password',
        'role_id',
        'is_active',
        'status',
        'phone',
        'gender',
        'date_of_birth',
        'address',
        'profile_image',
        'last_login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the role relationship.
     */
    public function roleRelation()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    /**
     * Get the role name attribute.
     */
    public function getRoleAttribute()
    {
        return $this->roleRelation?->role_name ?? 'patient';
    }

    /**
     * Admins inherit full access (matches sidebar + Permissions::roleMap).
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        return $this->traitHasPermissionTo($permission, $guardName);
    }

    public function hasAnyPermission(...$permissions): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        return $this->traitHasAnyPermission(...$permissions);
    }

    /**
     * Append role to JSON serialization.
     */
    protected $appends = ['role', 'full_name'];

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '').' '.($this->last_name ?? ''));
    }

    /**
     * Get the staff associated with the user.
     */
    public function staff()
    {
        return $this->hasOne(Staff::class, 'user_id', 'user_id');
    }

    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%");
        });
    }
}
