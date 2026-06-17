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
        'employee_id',
        'specialization',
        'department',
        'department_id',
        'position',
        'license_number',
        'qualification',
        'join_date',
        'emergency_contact',
        'emergency_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function departmentRelation()
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }
    
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id', 'staff_id');
    }
}
