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
                'html_template' => '<h1>Welcome, {{ user_name }}!</h1><p>Thank you for registering at {{ clinic_name }}.</p>',
                'text_template' => 'Welcome, {{ user_name }}! Thank you for registering at {{ clinic_name }}.',
            ]
        );

        \App\Models\MailTemplate::firstOrCreate(
            ['mailable' => 'App\Mail\AppointmentReminder'],
            [
                'subject' => 'Appointment Reminder: {{ appointment_date }}',
                'html_template' => '<p>Dear {{ patient_name }},</p><p>This is a reminder for your appointment on {{ appointment_date }} at {{ appointment_time }}.</p>',
                'text_template' => 'Dear {{ patient_name }}, This is a reminder for your appointment on {{ appointment_date }} at {{ appointment_time }}.',
            ]
        );
    }
}
