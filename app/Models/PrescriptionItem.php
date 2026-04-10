<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model
{
    use HasFactory;
    
    protected $table = 'prescription_items';
    protected $primaryKey = 'item_id';

    protected $fillable = [
        'prescription_id',
        'medication_id',
        'dosage',
        'frequency',
        'duration',
        'instructions'
    ];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class, 'prescription_id', 'prescription_id');
    }

    public function medication()
    {
        return $this->belongsTo(Medication::class, 'medication_id', 'medication_id');
    }
}
