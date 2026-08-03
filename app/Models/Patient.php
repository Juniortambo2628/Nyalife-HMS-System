<?php

namespace App\Models;

use App\Traits\DescribesActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Patient extends Model
{
    use DescribesActivity, HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getActivityLabel(): string
    {
        return 'Patient';
    }

    protected $primaryKey = 'patient_id';

    protected $fillable = [
        'user_id',
        'patient_number',
        'blood_group',
        'height',
        'weight',
        'allergies',
        'chronic_diseases',
        'emergency_name',
        'emergency_contact',
        'gender',
        'date_of_birth',
        'address',
        'marital_status',
        'occupation',
        'insurance_provider',
        'insurance_id',
        'insurance_number',
        'insurance_expiry',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
    ];

    public function getAgeAttribute()
    {
        $dob = $this->date_of_birth ?? $this->user?->date_of_birth;
        if (! $dob) {
            return null;
        }

        try {
            return Carbon::parse($dob)->age;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id', 'patient_id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'patient_id', 'patient_id');
    }

    public function labTestRequests()
    {
        return $this->hasMany(LabTestRequest::class, 'patient_id', 'patient_id');
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'patient_id', 'patient_id');
    }

    public function vitals()
    {
        return $this->hasMany(Vital::class, 'patient_id', 'patient_id');
    }

    public function scopeSearchByUserName($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->whereHas('user', function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Generate a unique patient number.
     */
    public static function generateNumber(int $userId): string
    {
        return 'PAT-'.date('Ymd').'-'.str_pad((string) $userId, 4, '0', STR_PAD_LEFT);
    }
}
