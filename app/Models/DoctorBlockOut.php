<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorBlockOut extends Model
{
    use HasFactory;

    public const MODE_ALL = 'all';

    public const MODE_IN_PERSON = 'in_person';

    public const MODE_TELEHEALTH = 'telehealth';

    protected $fillable = [
        'doctor_id',
        'block_date',
        'start_time',
        'end_time',
        'appointment_mode',
        'reason',
    ];

    protected $casts = [
        'block_date' => 'date',
    ];

    public function doctor()
    {
        return $this->belongsTo(Staff::class, 'doctor_id', 'staff_id');
    }

    public function scopeForAppointmentType($query, ?string $appointmentType)
    {
        $mode = $appointmentType === self::MODE_TELEHEALTH
            ? self::MODE_TELEHEALTH
            : self::MODE_IN_PERSON;

        return $query->whereIn('appointment_mode', [self::MODE_ALL, $mode]);
    }
}
