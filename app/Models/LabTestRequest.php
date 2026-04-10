<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTestRequest extends Model
{
    use HasFactory;
    
    protected $table = 'lab_test_requests';
    protected $primaryKey = 'request_id';

    protected $fillable = [
        'request_number',
        'patient_id',
        'doctor_id',
        'test_type_id', // Note: Legacy used test_id or test_type_id
        'priority',
        'requested_by',
        'status', // pending, completed
        'request_date',
        'processed_by',
        'processed_at',
        'results'
    ];

    protected $casts = [
        'results' => 'array',
        'request_date' => 'datetime',
        'processed_at' => 'datetime'
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
    
    public function testType()
    {
        return $this->belongsTo(LabTestType::class, 'test_type_id', 'test_type_id');
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
