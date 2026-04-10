<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    protected $primaryKey = 'prescription_id';

    protected $fillable = [
        'prescription_number',
        'patient_id',
        'prescribed_by', // User ID of doctor
        'appointment_id',
        'consultation_id',
        'prescription_date',
        'status', // pending, dispensed, cancelled
        'notes',
        'dispensed_by',
        'dispensed_at'
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
