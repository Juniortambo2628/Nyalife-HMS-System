<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTestType extends Model
{
    use HasFactory;
    
    protected $table = 'lab_test_types';
    protected $primaryKey = 'test_type_id';

    protected $fillable = [
        'test_name',
        'description',
        'category',
        'price',
        'normal_range',
        'units',
        'is_active',
        'template'
    ];

    protected $casts = [
        'template' => 'array',
        'is_active' => 'boolean'
    ];
}
