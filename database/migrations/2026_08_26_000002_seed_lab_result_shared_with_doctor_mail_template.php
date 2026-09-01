<?php

use App\Mail\LabResultSharedWithDoctor;
use App\Models\MailTemplate;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        MailTemplate::firstOrCreate(
            ['mailable' => LabResultSharedWithDoctor::class],
            [
                'subject' => 'Lab result ready: {{ test_name }} ({{ request_number }})',
                'html_template' => '<p>Dear Dr. {{ doctor_name }},</p><p>A verified laboratory result is ready for your review.</p><p><strong>Patient:</strong> {{ patient_name }}<br><strong>Test:</strong> {{ test_name }}<br><strong>Request:</strong> {{ request_number }}</p><p><a href="{{ result_url }}" style="background-color:#ec4899;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;">View secure result</a></p><p>Please sign in to Nyalife HMS to view the report.</p>',
                'text_template' => 'Dear Dr. {{ doctor_name }}, a verified laboratory result is ready for your review. Patient: {{ patient_name }}. Test: {{ test_name }}. Request: {{ request_number }}. Sign in to view it securely: {{ result_url }}',
            ]
        );
    }

    public function down(): void
    {
        MailTemplate::where('mailable', LabResultSharedWithDoctor::class)->delete();
    }
};
