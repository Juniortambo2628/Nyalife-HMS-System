<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class Department extends Model
{
    use HasFactory;
    protected $primaryKey = 'department_id';

    protected $fillable = [
        'department_name',
        'description',
        'is_active',
        'code',
        'type',
        'head_name',
        'head_position',
        'head_image',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const TYPES = [
        'clinical' => 'Clinical',
        'administrative' => 'Administrative',
        'support' => 'Support',
    ];

    public function staffMembers()
    {
        return $this->hasMany(Staff::class, 'department_id', 'department_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFilterSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('department_name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function scopeType(Builder $query, ?string $type): Builder
    {
        if (empty($type)) {
            return $query;
        }

        return $query->where('type', $type);
    }

    public function scopeFilteredQuery(Builder $query, Request $request): Builder
    {
        $query->filterSearch($request->search)->type($request->type);

        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        } elseif ($request->has('active_only') && $request->boolean('active_only')) {
            $query->where('is_active', true);
        }

        return $query;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst((string) $this->type);
    }
}
