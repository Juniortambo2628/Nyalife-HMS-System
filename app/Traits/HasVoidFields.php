<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait HasVoidFields
{
    /**
     * Boot the trait — add the global scope to exclude voided records.
     */
    public static function bootHasVoidFields(): void
    {
        static::addGlobalScope('not_voided', function (Builder $builder) {
            $builder->where('is_voided', false);
        });
    }

    /**
     * Include voided records in the query.
     */
    public function scopeWithVoided(Builder $query): Builder
    {
        return $query->withoutGlobalScope('not_voided');
    }

    /**
     * Only include voided records in the query.
     */
    public function scopeOnlyVoided(Builder $query): Builder
    {
        return $query->withoutGlobalScope('not_voided')->where('is_voided', true);
    }

    /**
     * Mark this record as voided.
     */
    public function void(?string $reason = null): bool
    {
        return $this->update([
            'is_voided' => true,
            'void_reason' => $reason,
            'voided_by' => Auth::id(),
            'voided_at' => now(),
        ]);
    }

    /**
     * Restore this record from voided state.
     */
    public function unvoid(): bool
    {
        return $this->update([
            'is_voided' => false,
            'void_reason' => null,
            'voided_by' => null,
            'voided_at' => null,
        ]);
    }

    /**
     * Get the user who voided this record.
     */
    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by', 'user_id');
    }

    /**
     * Check if this record is voided.
     */
    public function isVoided(): bool
    {
        return (bool) $this->is_voided;
    }
}
