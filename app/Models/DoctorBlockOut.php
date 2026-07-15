<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorBlockOut extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'block_date',
        'start_time',
        'end_time',
        'reason',
    ];

    protected $casts = [
        'block_date' => 'date',
    ];

    public function doctor()
    {
        return $this->belongsTo(Staff::class, 'doctor_id', 'staff_id');
    }
}
