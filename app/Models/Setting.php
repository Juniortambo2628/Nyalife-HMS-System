<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
    ];

    public static function clinicContactSettings(): Collection
    {
        return static::whereIn('key', [
            'contact_address', 'contact_email', 'contact_phone',
        ])->pluck('value', 'key');
    }

    public static function clinicInvoiceSettings(): Collection
    {
        return static::whereIn('key', [
            'contact_address', 'contact_email', 'contact_phone', 'tax_rate',
        ])->pluck('value', 'key');
    }
}
