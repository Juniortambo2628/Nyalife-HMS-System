<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait SearchByPatientName
{
    /**
     * Scope to filter by patient's first or last name.
     *
     * Requires the model to have a `patient` relationship with a `user` relation.
     */
    public function scopeSearchByPatientName(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->whereHas('patient.user', function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%");
        });
    }
}
