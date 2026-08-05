<?php

namespace App\Console\Commands;

use App\Mail\AppointmentReminder;
use App\Models\Appointment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:send-appointment-reminders';

    /**
     * The console command description.
     */
    protected $description = 'Send email reminders for appointments scheduled tomorrow';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tomorrow = now()->addDay()->toDateString();

        $appointments = Appointment::with(['patient.user', 'doctor.user'])
            ->whereDate('appointment_date', $tomorrow)
            ->whereIn('status', ['scheduled', 'confirmed', 'pending'])
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($appointments as $appointment) {
            $patientEmail = $appointment->patient->user->email ?? null;
            $patientName = trim(
                ($appointment->patient->user->first_name ?? '').' '.
                ($appointment->patient->user->last_name ?? '')
            );
            $doctorName = trim(
                'Dr. '.($appointment->doctor->user->first_name ?? '').' '.
                ($appointment->doctor->user->last_name ?? '')
            );

            if (! $patientEmail) {
                $this->warn("Skipping appointment #{$appointment->appointment_id} — no patient email.");

                continue;
            }

            try {
                Mail::to($patientEmail)->send(new AppointmentReminder([
                    'patient_name' => $patientName,
                    'appointment_date' => $appointment->appointment_date,
                    'appointment_time' => $appointment->appointment_time,
                    'doctor_name' => $doctorName,
                ]));
                $sent++;
            } catch (\Exception $e) {
                Log::warning("Appointment reminder failed for #{$appointment->appointment_id}: ".$e->getMessage());
                $failed++;
            }
        }

        $this->info("Appointment reminders sent: {$sent} | Failed: {$failed} | Total: {$appointments->count()}");

        return self::SUCCESS;
    }
}
