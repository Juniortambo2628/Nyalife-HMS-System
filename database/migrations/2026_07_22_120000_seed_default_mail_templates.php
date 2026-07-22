<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Seed the default Spatie mail templates that the application expects.
     *
     * This migration is idempotent: it only inserts templates that are missing,
     * so it is safe to run on environments that already have some or all of them.
     */
    public function up(): void
    {
        if (!Schema::hasTable('mail_templates')) {
            return;
        }

        $templates = [
            'App\Mail\WelcomeEmail' => [
                'subject' => 'Welcome to {{ clinic_name }}!',
                'html_template' => '<h1>Welcome, {{ user_name }}!</h1><p>Thank you for registering at {{ clinic_name }}. You can now book appointments, view your medical records, prescriptions, and lab results from your patient portal.</p><p>If you have any questions, don\'t hesitate to contact us.</p>',
                'text_template' => 'Welcome, {{ user_name }}! Thank you for registering at {{ clinic_name }}. You can now book appointments, view your medical records, prescriptions, and lab results from your patient portal.',
            ],
            'App\Mail\AppointmentReminder' => [
                'subject' => 'Appointment Reminder: {{ appointment_date }}',
                'html_template' => '<p>Dear {{ patient_name }},</p><p>This is a friendly reminder that you have an appointment scheduled:</p><ul><li><strong>Date:</strong> {{ appointment_date }}</li><li><strong>Time:</strong> {{ appointment_time }}</li><li><strong>Doctor:</strong> {{ doctor_name }}</li></ul><p>Please arrive 10 minutes early. If you need to reschedule, please contact us as soon as possible.</p>',
                'text_template' => 'Dear {{ patient_name }}, This is a reminder for your appointment on {{ appointment_date }} at {{ appointment_time }} with {{ doctor_name }}. Please arrive 10 minutes early.',
            ],
            'App\Mail\TelehealthPaymentNotification' => [
                'subject' => 'Telehealth Appointment - Payment & Consent Required',
                'html_template' => '<p>Dear {{ patient_name }},</p><p>Thank you for booking a telehealth consultation with Nyalife Women\'s Health Clinic. To confirm your appointment, please complete the following steps:</p><h3>Payment Details</h3><p><strong>Amount:</strong> KES {{ payment_amount }}</p><p><strong>Till Number:</strong> {{ till_number }} (Clinic Till)</p><p><strong>Appointment:</strong> {{ appointment_date }} at {{ appointment_time }}</p><p><strong>Doctor:</strong> {{ doctor_name }}</p><h3>Step 1: Make Payment</h3><p>Please pay via M-Pesa to Till Number <strong>{{ till_number }}</strong> for the amount of <strong>KES {{ payment_amount }}</strong>.</p><h3>Step 2: Sign Consent Form</h3><p>After making payment, please sign the telehealth consent form:</p><p><a href="{{ consent_form_url }}" style="background-color:#ec4899;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;">Sign Consent Form</a></p><h3>What Happens Next?</h3><p>Once we confirm your payment and receive your signed consent, we will send you a meeting link and appointment confirmation.</p><p>If you have any questions, please contact us.</p><p>Best regards,<br>Nyalife Women\'s Health Clinic</p>',
                'text_template' => 'Dear {{ patient_name }}, Thank you for booking a telehealth consultation. Payment: KES {{ payment_amount }} via Till {{ till_number }}. Appointment: {{ appointment_date }} at {{ appointment_time }}. After payment, sign consent at: {{ consent_form_url }}. We will send your meeting link after confirmation.',
            ],
            'App\Mail\TelehealthInvitation' => [
                'subject' => 'Your Telehealth Appointment is Confirmed!',
                'html_template' => '<p>Dear {{ patient_name }},</p><p>Great news! Your telehealth appointment has been confirmed. Here are the details:</p><ul><li><strong>Date:</strong> {{ appointment_date }}</li><li><strong>Time:</strong> {{ appointment_time }}</li><li><strong>Doctor:</strong> {{ doctor_name }}</li></ul><h3>Join Your Consultation</h3><p>Click the link below to join your meeting:</p><p><a href="{{ meeting_link }}" style="background-color:#ec4899;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;">Join Meeting</a></p><p><strong>Meeting Link:</strong> <a href="{{ meeting_link }}">{{ meeting_link }}</a></p><h3>Before Your Consultation</h3><ul><li>Ensure you have a stable internet connection</li><li>Have a working camera and microphone</li><li>Find a quiet, private location</li><li>Have your medical information ready</li></ul><p>If you need to reschedule or cancel, please contact us as soon as possible.</p><p>Best regards,<br>Nyalife Women\'s Health Clinic</p>',
                'text_template' => 'Dear {{ patient_name }}, Your telehealth appointment is confirmed! Date: {{ appointment_date }}, Time: {{ appointment_time }}, Doctor: {{ doctor_name }}. Join here: {{ meeting_link }}. Please ensure stable internet, working camera/mic, and a private location.',
            ],
            'App\Mail\GuestCredentialsEmail' => [
                'subject' => 'Your Nyalife Patient Portal Account',
                'html_template' => '<p>Dear {{ patient_name }},</p><p>An account has been created for you on the Nyalife Patient Portal as part of your appointment booking. You can use these credentials to log in, view your appointments, medical records, and more.</p><ul><li><strong>Email:</strong> {{ email }}</li><li><strong>Temporary Password:</strong> {{ password }}</li></ul><p><a href="{{ login_url }}">Log in to your portal</a></p><p><strong>Important:</strong> Please change your password after your first login for security.</p>',
                'text_template' => 'Dear {{ patient_name }}, Your Nyalife account: Email: {{ email }}, Password: {{ password }}. Log in at: {{ login_url }}. Please change your password after first login.',
            ],
        ];

        foreach ($templates as $mailable => $template) {
            \App\Models\MailTemplate::firstOrCreate(
                ['mailable' => $mailable],
                [
                    'subject' => $template['subject'],
                    'html_template' => $template['html_template'],
                    'text_template' => $template['text_template'],
                ]
            );
        }
    }

    public function down(): void
    {
        // No-op: we don't want to delete user-edited templates on rollback.
    }
};
