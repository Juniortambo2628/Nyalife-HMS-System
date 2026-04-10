<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $primaryKey = 'patient_id';

    protected $fillable = [
        'user_id',
        'patient_number',
        'blood_type',
        'emergency_name',
        'emergency_contact',
        'chronic_diseases', // Mapped from medical_conditions in legacy logic, ensure column exists
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id', 'patient_id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'patient_id', 'patient_id');
    }
    
    public function labTestRequests()
    {
        return $this->hasMany(LabTestRequest::class, 'patient_id', 'patient_id');
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'patient_id', 'patient_id');
    }

    public function vitals()
    {
        return $this->hasMany(Vital::class, 'patient_id', 'patient_id');
    }

    public function scopeSearchByUserName($query, $search)
    {
        if (empty($search)) {
            return $query;
        }
        return $query->whereHas('user', function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }
}
