<?php

namespace App\Models;

use App\Traits\DescribesActivity;
use App\Traits\HasVoidFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Vital extends Model
{
    use DescribesActivity, HasFactory, HasVoidFields, LogsActivity;

    protected $table = 'vital_signs';

    protected $primaryKey = 'vital_id';

    protected $fillable = [
        'patient_id',
        'consultation_id',
        'blood_pressure',
        'heart_rate',
        'respiratory_rate',
        'temperature',
        'weight',
        'height',
        'bmi',
        'pain_level',
        'oxygen_saturation',
        'priority',
        'notes',
        'measured_at',
        'recorded_by',
        'is_voided',
        'void_reason',
        'voided_by',
        'voided_at',
    ];

    protected $casts = [
        'measured_at' => 'datetime',
        'temperature' => 'decimal:1',
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
        'bmi' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getActivityLabel(): string
    {
        return 'Vital Signs';
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class, 'consultation_id', 'consultation_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by', 'user_id');
    }
}
