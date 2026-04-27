<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Patient extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $primaryKey = 'patient_id';

    protected $fillable = [
        'user_id',
        'patient_number',
        'blood_group',
        'height',
        'weight',
        'allergies',
        'chronic_diseases',
        'emergency_name',
        'emergency_contact',
        'gender',
        'date_of_birth',
        'address',
        'marital_status',
        'occupation',
        'insurance_provider',
        'insurance_id',
    ];
    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function getAgeAttribute()
    {
        $dob = $this->date_of_birth ?? $this->user?->date_of_birth;
        if (!$dob) return null;
        
        try {
            return \Carbon\Carbon::parse($dob)->age;
        } catch (\Exception $e) {
            return null;
        }
    }

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
