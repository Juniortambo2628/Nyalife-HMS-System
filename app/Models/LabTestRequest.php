<?php

namespace App\Models;

use App\Traits\DescribesActivity;
use App\Traits\HasStatusScope;
use App\Traits\SearchByPatientName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LabTestRequest extends Model
{
    use DescribesActivity, HasFactory, HasStatusScope, LogsActivity, SearchByPatientName;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getActivityLabel(): string
    {
        return 'Lab Test Request';
    }

    protected $table = 'lab_test_requests';

    protected $primaryKey = 'request_id';

    protected $fillable = [
        'request_number',
        'patient_id',
        'doctor_id',
        'test_type_id',
        'priority',
        'requested_by',
        'status', // pending, processing, pending_verification, verified, completed, cancelled
        'request_date',
        'completed_at',
        'results',
        'assigned_to',
        'sample_collected_by',
        'notes',
        'consultation_id',
        'appointment_id',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'results' => 'array',
        'request_date' => 'datetime',
        'completed_at' => 'datetime',
        'verified_at' => 'datetime',
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
