<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\SearchByPatientName;
use App\Traits\HasStatusScope;

class RadiologyRequest extends Model
{
    use HasFactory, LogsActivity, SearchByPatientName, HasStatusScope;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $table = 'radiology_requests';
    protected $primaryKey = 'request_id';

    protected $fillable = [
        'request_number',
        'patient_id',
        'doctor_id',
        'scan_type',
        'clinical_indication',
        'scan_details',
        'findings',
        'impression',
        'priority',
        'status', // pending, processing, pending_verification, verified, completed, cancelled
        'requested_by',
        'assigned_to',
        'verified_by',
        'verified_at',
        'completed_at',
        'consultation_id',
        'appointment_id'
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Staff::class, 'doctor_id', 'staff_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by', 'user_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'user_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by', 'user_id');
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class, 'consultation_id', 'consultation_id');
    }
}
