<?php

namespace App\Models;

use App\Traits\HasStatusScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class FollowUp extends Model
{
    use HasFactory, HasStatusScope;

    protected $table = 'follow_ups';

    protected $primaryKey = 'follow_up_id';

    protected $fillable = [
        'patient_id',
        'consultation_id',
        'follow_up_date',
        'follow_up_type',
        'reason',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
    ];

    public const TYPES = [
        'general' => 'General Follow-up',
        'post_surgery' => 'Post-Surgery Follow-up',
        'medication_review' => 'Medication Review',
        'lab_results' => 'Lab Results Review',
        'treatment_progress' => 'Treatment Progress',
        'specialist_referral' => 'Specialist Referral',
        'emergency' => 'Emergency Follow-up',
    ];

    public const STATUSES = [
        'scheduled' => 'Scheduled',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'no_show' => 'No Show',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class, 'consultation_id', 'consultation_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function scopeFilterSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('reason', 'like', "%{$search}%")
                ->orWhereHas('patient.user', function ($uq) use ($search) {
                    $uq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
        });
    }

    public function scopeType(Builder $query, ?string $type): Builder
    {
        if (empty($type)) {
            return $query;
        }

        return $query->where('follow_up_type', $type);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('status', 'scheduled')
            ->whereDate('follow_up_date', '>=', Carbon::today());
    }

    public function scopeFilteredQuery(Builder $query, Request $request): Builder
    {
        return $query
            ->filterSearch($request->search)
            ->status($request->status)
            ->type($request->type)
            ->when($request->patient_id, fn ($q) => $q->where('patient_id', $request->patient_id))
            ->when($request->date_from, fn ($q) => $q->whereDate('follow_up_date', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('follow_up_date', '<=', $request->date_to));
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->follow_up_type] ?? ($this->follow_up_type ?: 'General');
    }
}
