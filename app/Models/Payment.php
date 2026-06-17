<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class Payment extends Model
{
    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'invoice_id',
        'amount',
        'payment_method',
        'payment_date',
        'transaction_reference',
        'payment_status',
        'status',
        'notes',
        'received_by',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public const METHODS = [
        'cash' => 'Cash',
        'credit_card' => 'Credit Card',
        'debit_card' => 'Debit Card',
        'bank_transfer' => 'Bank Transfer',
        'check' => 'Check',
        'insurance' => 'Insurance',
        'mobile_payment' => 'Mobile Payment',
        'other' => 'Other',
    ];

    public const STATUSES = [
        'pending' => 'Pending',
        'completed' => 'Completed',
        'failed' => 'Failed',
        'refunded' => 'Refunded',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'invoice_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by', 'user_id');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('payment_status', 'completed');
    }

    public function scopeFilterSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('transaction_reference', 'like', "%{$search}%")
                ->orWhereHas('invoice', fn ($iq) => $iq->where('invoice_number', 'like', "%{$search}%"))
                ->orWhereHas('invoice.patient.user', function ($uq) use ($search) {
                    $uq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
        });
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        if (empty($status)) {
            return $query;
        }

        return $query->where('payment_status', $status);
    }

    public function scopeMethod(Builder $query, ?string $method): Builder
    {
        if (empty($method)) {
            return $query;
        }

        return $query->where('payment_method', $method);
    }

    public function scopeFilteredQuery(Builder $query, Request $request): Builder
    {
        return $query
            ->filterSearch($request->search)
            ->status($request->status)
            ->method($request->method)
            ->when($request->invoice_id, fn ($q) => $q->where('invoice_id', $request->invoice_id))
            ->when($request->date_from, fn ($q) => $q->whereDate('payment_date', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('payment_date', '<=', $request->date_to));
    }
}
