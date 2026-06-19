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

    const LAB_CATEGORIES = [
        'Hematology', 'Chemistry', 'Biochemistry', 'Microbiology',
        'Parasitology', 'Pathology', 'Reproductive', 'Serology', 'Laboratory'
    ];

    const SERVICE_CATEGORIES = [
        'Procedure', 'Imaging', 'General Services', 'Delivery',
        'Consultation', 'Antenatal', 'Family Planning', 'Immunization'
    ];

    public function scopeLabTests($query)
    {
        return $query->whereIn('category', static::LAB_CATEGORIES);
    }

    public function scopeServices($query)
    {
        return $query->whereIn('category', static::SERVICE_CATEGORIES);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
