<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalProcedure extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $primaryKey = 'procedure_id';

    protected $fillable = [
        'name',
        'description',
        'category',
        'standard_fee',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'standard_fee' => 'decimal:2',
    ];
}
