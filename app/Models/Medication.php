<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medication extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'medication_id';

    protected $fillable = [
        'medication_name',
        'medication_type',
        'description',
        'strength',
        'unit',
        'stock_quantity',
        'price_per_unit'
    ];

    public function scopeSearchByNameOrType($query, $search)
    {
        if (empty($search)) {
            return $query;
        }
        return $query->where(function ($q) use ($search) {
            $q->where('medication_name', 'like', "%{$search}%")
                ->orWhere('medication_type', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('strength', 'like', "%{$search}%");
        });
    }
}
