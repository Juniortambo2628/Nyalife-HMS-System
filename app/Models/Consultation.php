<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Consultation extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $table = 'consultations';
    protected $primaryKey = 'consultation_id';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_id',
        'consultation_date',
        'consultation_status',
        'is_walk_in',
        'priority',
        'chief_complaint',
        'history_present_illness',
        'past_medical_history',
        'family_history',
        'social_history',
        'obstetric_history',
        'gynecological_history',
        'menstrual_history',
        'contraceptive_history',
        'sexual_history',
        'review_of_systems',
        'vital_signs', // JSON
        'physical_examination',
        'general_examination',
        'systems_examination',
        'diagnosis',
        'diagnosis_confidence',
        'differential_diagnosis',
        'diagnostic_plan',
        'treatment_plan',
        'follow_up_instructions',
        'notes',
        'clinical_summary',
        'parity',
        'current_pregnancy',
        'past_obstetric',
        'surgical_history',
        'cervical_screening',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'vital_signs' => 'array',
        'menstrual_history' => 'array',
        'past_obstetric' => 'array',
        'is_walk_in' => 'boolean',
        'consultation_date' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function doctor() // Refers to Staff (doctor)
    {
        return $this->belongsTo(Staff::class, 'doctor_id', 'staff_id'); // Legacy uses staff_id for doctor_id in consultations
    }
    
    // Sometimes we need the User model for the doctor directly if staff link is complex, but Staff is safer
    public function doctorUser()
    {
        return $this->hasOneThrough(User::class, Staff::class, 'staff_id', 'user_id', 'doctor_id', 'user_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'consultation_id', 'consultation_id');
    }

    public function labTestRequests()
    {
        return $this->hasMany(LabTestRequest::class, 'consultation_id', 'consultation_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'consultation_id', 'consultation_id');
    }

    public function scopeSearchByPatientOrDiagnosis($query, $search)
    {
        if (empty($search)) {
            return $query;
        }
        return $query->where(function ($q) use ($search) {
            $q->whereHas('patient.user', function ($uq) use ($search) {
                $uq->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            })->orWhere('diagnosis', 'like', "%{$search}%");
        });
    }

    public function scopeForDoctor($query, $doctorId)
    {
        if (empty($doctorId)) {
            return $query;
        }
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeForPatient($query, $patientId)
    {
        if (empty($patientId)) {
            return $query;
        }
        return $query->where('patient_id', $patientId);
    }
}
