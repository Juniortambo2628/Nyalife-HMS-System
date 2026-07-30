<?php

namespace App\Models;

use App\Traits\DescribesActivity;
use App\Traits\HasStatusScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Appointment extends Model
{
    use DescribesActivity, HasFactory, HasStatusScope, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getActivityLabel(): string
    {
        return 'Appointment';
    }

    protected $primaryKey = 'appointment_id';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_date',
        'appointment_time',
        'end_time',
        'appointment_type',
        'status', // scheduled, confirmed, completed, cancelled, no_show, pending
        'reason',
        'notes',
        'created_by',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    // Determine if doctor_id refers to Staff or User. Legacy hints it might be staff_id or user_id depending on context.
    // Legacy AppointmentModel: JOIN staff s ON a.doctor_id = s.staff_id
    public function doctor()
    {
        return $this->belongsTo(Staff::class, 'doctor_id', 'staff_id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'appointment_id', 'appointment_id');
    }

    public function labTestRequests()
    {
        return $this->hasMany(LabTestRequest::class, 'appointment_id', 'appointment_id');
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'appointment_id', 'appointment_id');
    }

    public function telehealthConsents()
    {
        return $this->hasMany(TelehealthConsent::class, 'appointment_id', 'appointment_id');
    }

    public function scopeForDoctor($query, $staffId)
    {
        if (empty($staffId)) {
            return $query;
        }

        return $query->where('doctor_id', $staffId);
    }

    public function scopeForPatient($query, $patientId)
    {
        if (empty($patientId)) {
            return $query;
        }

        return $query->where('patient_id', $patientId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
