<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PatientQueue extends Model
{
    use HasFactory;

    protected $table = 'patient_queues';

    protected $fillable = ['patient_id', 'appointment_id', 'queue_date', 'queue_number', 'visit_type', 'status', 'checked_in_by', 'called_at', 'completed_at'];

    protected $casts = ['queue_date' => 'date', 'called_at' => 'datetime', 'completed_at' => 'datetime'];

    public function patient() { return $this->belongsTo(Patient::class, 'patient_id', 'patient_id'); }
    public function appointment() { return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id'); }

    public static function clinicQueueDate(): string
    {
        return now()->subHours(8)->toDateString();
    }

    public static function enqueue(int $patientId, ?int $appointmentId, int $checkedInBy): self
    {
        $date = self::clinicQueueDate();
        return DB::transaction(function () use ($patientId, $appointmentId, $checkedInBy, $date) {
            $existing = static::where('queue_date', $date)->where('patient_id', $patientId)->whereNotIn('status', ['completed', 'cancelled'])->lockForUpdate()->first();
            if ($existing) return $existing;
            $visitType = Consultation::where('patient_id', $patientId)->where('consultation_status', 'completed')->exists() ? 'revisit' : 'new';
            $number = (int) static::where('queue_date', $date)->lockForUpdate()->max('queue_number') + 1;
            return static::create(['patient_id' => $patientId, 'appointment_id' => $appointmentId, 'queue_date' => $date, 'queue_number' => $number, 'visit_type' => $visitType, 'status' => 'waiting', 'checked_in_by' => $checkedInBy]);
        });
    }
}
