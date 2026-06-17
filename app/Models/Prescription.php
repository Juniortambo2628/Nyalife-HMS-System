<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Prescription extends Model
{
    use HasFactory, LogsActivity;

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
        'voided_at' => 'datetime',
        'dispensed_at' => 'datetime',
        'is_voided' => 'boolean',
    ];

    protected static function booted()
    {
        static::addGlobalScope('not_voided', function (Builder $builder) {
            $builder->where('is_voided', false);
        });
    }

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

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by', 'user_id');
    }

    public function scopeSearchByPatientName($query, $search)
    {
        if (empty($search)) {
            return $query;
        }
        return $query->whereHas('patient.user', function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%");
        });
    }

    public function scopeStatus($query, $status)
    {
        if (empty($status)) {
            return $query;
        }
        return $query->where('status', $status);
    }
}
