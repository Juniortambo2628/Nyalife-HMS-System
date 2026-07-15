<?php

namespace App\Mail;

use Spatie\MailTemplates\TemplateMailable;

class TelehealthInvitation extends TemplateMailable
{
    public $patient_name;
    public $meeting_link;
    public $appointment_date;
    public $appointment_time;
    public $doctor_name;
    public $consent_form_url;

    public function __construct(array $data)
    {
        $this->patient_name = $data['patient_name'] ?? '';
        $this->meeting_link = $data['meeting_link'] ?? '';
        $this->appointment_date = $data['appointment_date'] ?? '';
        $this->appointment_time = $data['appointment_time'] ?? '';
        $this->doctor_name = $data['doctor_name'] ?? '';
        $this->consent_form_url = $data['consent_form_url'] ?? '';
    }
}
