<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasVoidFields;
use App\Traits\HasStatusScope;

class Invoice extends Model
{
    use HasFactory, LogsActivity, HasVoidFields, HasStatusScope;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $primaryKey = 'invoice_id';

    protected $fillable = [
        'patient_id',
        'consultation_id',
        'doctor_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'total_amount',
        'discount',
        'tax',
        'status',
        'payment_method',
        'notes',
        'created_by',
        'insurance_claim_id',
        'insurance_coverage',
        'patient_responsibility',
        'is_voided',
        'void_reason',
        'voided_by',
        'voided_at'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'insurance_coverage' => 'decimal:2',
        'patient_responsibility' => 'decimal:2',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id', 'invoice_id');
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class, 'consultation_id', 'consultation_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'invoice_id', 'invoice_id');
    }

    public function scopeSearchByPatientOrNumber(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }
        return $query->where(function ($q) use ($search) {
            $q->whereHas('patient.user', function ($uq) use ($search) {
                $uq->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            })->orWhere('invoice_number', 'like', "%{$search}%");
        });
    }
}
