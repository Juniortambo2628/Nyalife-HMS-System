<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class TelehealthConsent extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $primaryKey = 'id';

    protected $fillable = [
        'patient_id',
        'appointment_id',
        'patient_name',
        'patient_email',
        'patient_phone',
        'doctor_name',
        'patient_signature_path',
        'verbal_consent_obtained',
        'doctor_signature_path',
        'signed_at',
        'ip_address'
    ];

    protected $casts = [
        'verbal_consent_obtained' => 'boolean',
        'signed_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }
}
