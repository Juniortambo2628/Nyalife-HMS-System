<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicationBatch extends Model
{
    use HasFactory;

    protected $primaryKey = 'batch_id';

    const UPDATED_AT = null;

    protected $fillable = [
        'medication_id',
        'batch_number',
        'quantity',
        'expiry_date',
        'manufacturing_date',
        'supplier_id',
        'notes'
    ];

    public function medication()
    {
        return $this->belongsTo(Medication::class, 'medication_id', 'medication_id');
    }
}
