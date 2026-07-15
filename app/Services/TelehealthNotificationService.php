<?php

namespace App\Services;

use App\Mail\GuestCredentialsEmail;
use App\Mail\TelehealthInvitation;
use App\Mail\TelehealthPaymentNotification;
use App\Models\Appointment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TelehealthNotificationService
{
    public static function sendGuestCredentials(string $email, string $patientName, string $password): void
    {
        try {
            Mail::to($email)->send(new GuestCredentialsEmail([
                'patient_name' => $patientName,
                'email' => $email,
                'password' => $password,
                'login_url' => rtrim(config('app.url'), '/') . '/login',
            ]));
        } catch (\Exception $e) {
            Log::warning('Guest credentials email failed: ' . $e->getMessage());
        }
    }

    public static function sendPaymentNotification(Appointment $appointment, string $patientEmail): void
    {
        try {
            $doctorName = 'Clinic Physician';
            if ($appointment->doctor && $appointment->doctor->user) {
                $doctorName = "Dr. {$appointment->doctor->user->first_name} {$appointment->doctor->user->last_name}";
            }

            Mail::to($patientEmail)->send(new TelehealthPaymentNotification([
                'patient_name' => trim(($appointment->patient->user->first_name ?? '') . ' ' . ($appointment->patient->user->last_name ?? '')),
                'appointment_date' => $appointment->appointment_date,
                'appointment_time' => $appointment->appointment_time,
                'doctor_name' => $doctorName,
                'payment_amount' => '4,000',
                'till_number' => '9344367',
                'consent_form_url' => rtrim(config('app.url'), '/') . '/telehealth',
                'appointment_id' => $appointment->appointment_id,
            ]));
        } catch (\Exception $e) {
            Log::warning('Telehealth payment notification email failed: ' . $e->getMessage());
        }
    }

    public static function confirmPaymentAndSendInvite(Appointment $appointment): string
    {
        $meetingId = 'nyalife-' . strtolower(Str::random(12));
        $appUrl = rtrim(config('app.url'), '/');
        $link = "{$appUrl}/telehealth/meeting/{$meetingId}";

        $appointment->notes = ($appointment->notes ?? '') . "\nMeeting Link: {$link}";
        $appointment->status = 'confirmed';
        $appointment->save();

        $patientEmail = $appointment->patient->user->email ?? null;
        if ($patientEmail) {
            try {
                $doctorName = 'Clinic Physician';
                if ($appointment->doctor && $appointment->doctor->user) {
                    $doctorName = "Dr. {$appointment->doctor->user->first_name} {$appointment->doctor->user->last_name}";
                }

                Mail::to($patientEmail)->send(new TelehealthInvitation([
                    'patient_name' => trim(($appointment->patient->user->first_name ?? '') . ' ' . ($appointment->patient->user->last_name ?? '')),
                    'meeting_link' => $link,
                    'appointment_date' => $appointment->appointment_date,
                    'appointment_time' => $appointment->appointment_time,
                    'doctor_name' => $doctorName,
                    'consent_form_url' => rtrim(config('app.url'), '/') . '/telehealth',
                ]));
            } catch (\Exception $e) {
                Log::warning('Telehealth confirmation email failed: ' . $e->getMessage());
            }
        }

        return $link;
    }
}
