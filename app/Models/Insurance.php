<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Insurance extends Model
{
    protected $table = 'insurances';
    protected $primaryKey = 'insurance_id';

    protected $fillable = [
        'name',
        'logo_path',
        'link',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the full URL for the logo.
     */
    public function getLogoUrlAttribute()
    {
        if (!$this->logo_path) {
            return null;
        }

        if (str_starts_with($this->logo_path, 'http')) {
            return $this->logo_path;
        }

        return asset('storage/' . $this->logo_path);
    }

    protected $appends = ['logo_url'];
}
