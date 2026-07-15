<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasStatusScope
{
    /**
     * Scope to filter by the given status value.
     */
    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        if (empty($status)) {
            return $query;
        }

        $column = $this->statusColumn ?? 'status';

        return $query->where($column, $status);
    }
}
