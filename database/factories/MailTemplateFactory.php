<?php

namespace Database\Factories;

use App\Models\MailTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class MailTemplateFactory extends Factory
{
    protected $model = MailTemplate::class;

    public function definition(): array
    {
        $templates = [
            'App\\Mail\\WelcomeEmail' => [
                'subject' => 'Welcome to {{ $siteName }}',
                'html' => '<h1>Welcome {{ $user->first_name }}!</h1><p>Thank you for registering.</p>',
                'text' => 'Welcome {{ $user->first_name }}! Thank you for registering.',
            ],
            'App\\Mail\\AppointmentReminder' => [
                'subject' => 'Appointment Reminder - {{ $appointment->appointment_date }}',
                'html' => '<h1>Appointment Reminder</h1><p>Dear {{ $patient->user->first_name }},</p><p>Your appointment is scheduled for {{ $appointment->appointment_date }} at {{ $appointment->appointment_time }}.</p>',
                'text' => 'Appointment Reminder. Your appointment is scheduled for {{ $appointment->appointment_date }}.',
            ],
            'App\\Mail\\TelehealthPaymentNotification' => [
                'subject' => 'Telehealth Payment Confirmation',
                'html' => '<h1>Payment Confirmed</h1><p>Your telehealth appointment payment has been received.</p>',
                'text' => 'Your telehealth appointment payment has been received.',
            ],
            'App\\Mail\\TelehealthInvitation' => [
                'subject' => 'Telehealth Meeting Invitation',
                'html' => '<h1>Telehealth Meeting</h1><p>Join your consultation: {{ $meetingLink }}</p>',
                'text' => 'Join your telehealth consultation: {{ $meetingLink }}',
            ],
            'App\\Mail\\GuestCredentialsEmail' => [
                'subject' => 'Your Guest Account Credentials',
                'html' => '<h1>Welcome!</h1><p>Your guest account has been created.</p>',
                'text' => 'Your guest account has been created.',
            ],
        ];

        $class = $this->faker->randomElement(array_keys($templates));
        $template = $templates[$class];

        return [
            'mailable' => $class,
            'subject' => $template['subject'],
            'html_template' => $template['html'],
            'text_template' => $template['text'],
        ];
    }
}
