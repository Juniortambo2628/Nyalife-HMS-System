<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasVoidFields;
use App\Traits\SearchByPatientName;
use App\Traits\HasStatusScope;

class Prescription extends Model
{
    use HasFactory, LogsActivity, HasVoidFields, SearchByPatientName, HasStatusScope;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $primaryKey = 'prescription_id';

    protected $fillable = [
        'patient_id',
        'prescribed_by', // User ID of doctor
        'appointment_id',
        'consultation_id',
        'prescription_date',
        'prescription_number',
        'status', // pending, dispensed, cancelled
        'notes',
        'dispensed_by',
        'dispensed_at',
        'is_voided',
        'void_reason',
        'voided_by',
        'voided_at'
    ];

    protected $casts = [
        'prescription_date' => 'date',
        'dispensed_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'prescribed_by', 'user_id');
    }

    public function items()
    {
        return $this->hasMany(PrescriptionItem::class, 'prescription_id', 'prescription_id');
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class, 'consultation_id', 'consultation_id');
    }
}
