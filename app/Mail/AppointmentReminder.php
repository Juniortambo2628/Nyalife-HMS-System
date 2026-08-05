<?php

namespace App\Mail;

use Spatie\MailTemplates\TemplateMailable;

class AppointmentReminder extends TemplateMailable
{
    public $patient_name;

    public $appointment_date;

    public $appointment_time;

    public $doctor_name;

    public function __construct(array $data)
    {
        $this->patient_name = $data['patient_name'] ?? '';
        $this->appointment_date = $data['appointment_date'] ?? '';
        $this->appointment_time = $data['appointment_time'] ?? '';
        $this->doctor_name = $data['doctor_name'] ?? '';
    }
}
