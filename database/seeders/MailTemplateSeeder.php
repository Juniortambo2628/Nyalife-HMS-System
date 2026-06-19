<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\MailTemplate::firstOrCreate(
            ['mailable' => 'App\Mail\WelcomeEmail'],
            [
                'subject' => 'Welcome to {{ clinic_name }}!',
                'html_template' => '<h1>Welcome, {{ user_name }}!</h1><p>Thank you for registering at {{ clinic_name }}. You can now book appointments, view your medical records, prescriptions, and lab results from your patient portal.</p><p>If you have any questions, don\'t hesitate to contact us.</p>',
                'text_template' => 'Welcome, {{ user_name }}! Thank you for registering at {{ clinic_name }}. You can now book appointments, view your medical records, prescriptions, and lab results from your patient portal.',
            ]
        );

        \App\Models\MailTemplate::firstOrCreate(
            ['mailable' => 'App\Mail\AppointmentReminder'],
            [
                'subject' => 'Appointment Reminder: {{ appointment_date }}',
                'html_template' => '<p>Dear {{ patient_name }},</p><p>This is a friendly reminder that you have an appointment scheduled:</p><ul><li><strong>Date:</strong> {{ appointment_date }}</li><li><strong>Time:</strong> {{ appointment_time }}</li><li><strong>Doctor:</strong> {{ doctor_name }}</li></ul><p>Please arrive 10 minutes early. If you need to reschedule, please contact us as soon as possible.</p>',
                'text_template' => 'Dear {{ patient_name }}, This is a reminder for your appointment on {{ appointment_date }} at {{ appointment_time }} with {{ doctor_name }}. Please arrive 10 minutes early.',
            ]
        );

        \App\Models\MailTemplate::firstOrCreate(
            ['mailable' => 'App\Mail\TelehealthInvitation'],
            [
                'subject' => 'Your Telehealth Appointment Details',
                'html_template' => '<p>Dear {{ patient_name }},</p><p>Your telehealth appointment has been confirmed. Here are the details:</p><ul><li><strong>Date:</strong> {{ appointment_date }}</li><li><strong>Time:</strong> {{ appointment_time }}</li><li><strong>Doctor:</strong> {{ doctor_name }}</li></ul><p>Join your meeting using this link: <a href="{{ meeting_link }}">{{ meeting_link }}</a></p><p>Please ensure you have a stable internet connection, a working camera, and microphone.</p>',
                'text_template' => 'Dear {{ patient_name }}, Your telehealth appointment is on {{ appointment_date }} at {{ appointment_time }} with {{ doctor_name }}. Join here: {{ meeting_link }}',
            ]
        );

        \App\Models\MailTemplate::firstOrCreate(
            ['mailable' => 'App\Mail\GuestCredentialsEmail'],
            [
                'subject' => 'Your Nyalife Patient Portal Account',
                'html_template' => '<p>Dear {{ patient_name }},</p><p>An account has been created for you on the Nyalife Patient Portal as part of your appointment booking. You can use these credentials to log in, view your appointments, medical records, and more.</p><ul><li><strong>Email:</strong> {{ email }}</li><li><strong>Temporary Password:</strong> {{ password }}</li></ul><p><a href="{{ login_url }}">Log in to your portal</a></p><p><strong>Important:</strong> Please change your password after your first login for security.</p>',
                'text_template' => 'Dear {{ patient_name }}, Your Nyalife account: Email: {{ email }}, Password: {{ password }}. Log in at: {{ login_url }}. Please change your password after first login.',
            ]
        );
    }
}
