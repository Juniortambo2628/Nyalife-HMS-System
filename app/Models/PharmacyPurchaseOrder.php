<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyPurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'medication_id',
        'medication_name',
        'quantity',
        'supplier_name',
        'estimated_cost',
        'status',
    ];

    public function medication()
    {
        return $this->belongsTo(Medication::class, 'medication_id', 'medication_id');
    }
}
