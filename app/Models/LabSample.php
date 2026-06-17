<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class LabSample extends Model
{
    protected $table = 'lab_samples';

    protected $fillable = [
        'sample_id',
        'patient_id',
        'test_type_id',
        'sample_type',
        'collected_date',
        'collected_by',
        'collected_at',
        'status',
        'completed_by',
        'completed_at',
        'notes',
        'urgent',
    ];

    protected $casts = [
        'collected_date' => 'date',
        'collected_at' => 'datetime',
        'completed_at' => 'datetime',
        'urgent' => 'boolean',
    ];

    public const SAMPLE_TYPES = [
        'blood' => 'Blood',
        'urine' => 'Urine',
        'swab' => 'Swab',
        'tissue' => 'Tissue',
        'stool' => 'Stool',
        'other' => 'Other',
    ];

    public const STATUSES = [
        'registered' => 'Registered',
        'in_progress' => 'In Progress',
        'pending_results' => 'Pending Results',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function testType()
    {
        return $this->belongsTo(LabTestType::class, 'test_type_id', 'test_type_id');
    }

    public function collectedByUser()
    {
        return $this->belongsTo(User::class, 'collected_by', 'user_id');
    }

    public function completedByUser()
    {
        return $this->belongsTo(User::class, 'completed_by', 'user_id');
    }

    public function scopeFilterSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('sample_id', 'like', "%{$search}%")
                ->orWhereHas('patient.user', function ($uq) use ($search) {
                    $uq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
        });
    }

    public function getSampleTypeLabelAttribute(): string
    {
        return self::SAMPLE_TYPES[$this->sample_type] ?? ucfirst((string) $this->sample_type);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }
}
