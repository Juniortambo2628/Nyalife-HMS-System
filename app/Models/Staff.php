<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;
    
    protected $table = 'staff';
    protected $primaryKey = 'staff_id';

    protected $fillable = [
        'user_id',
        'specialization',
        'department',
        'license_number'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
    
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id', 'staff_id');
    }
}
