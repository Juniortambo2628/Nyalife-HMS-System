<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicationCategory extends Model
{
    use HasFactory;

    protected $primaryKey = 'category_id';

    protected $fillable = [
        'category_name',
        'description',
        'is_active'
    ];

    public function medications()
    {
        return $this->hasMany(Medication::class, 'category_id', 'category_id');
    }
}
