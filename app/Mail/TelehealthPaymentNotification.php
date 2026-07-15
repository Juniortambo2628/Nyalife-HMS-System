<?php

namespace App\Mail;

use Spatie\MailTemplates\TemplateMailable;

class TelehealthPaymentNotification extends TemplateMailable
{
    public $patient_name;
    public $appointment_date;
    public $appointment_time;
    public $doctor_name;
    public $payment_amount;
    public $till_number;
    public $consent_form_url;
    public $appointment_id;

    public function __construct(array $data)
    {
        $this->patient_name = $data['patient_name'] ?? '';
        $this->appointment_date = $data['appointment_date'] ?? '';
        $this->appointment_time = $data['appointment_time'] ?? '';
        $this->doctor_name = $data['doctor_name'] ?? '';
        $this->payment_amount = $data['payment_amount'] ?? '4,000';
        $this->till_number = $data['till_number'] ?? '9344367';
        $this->consent_form_url = $data['consent_form_url'] ?? '';
        $this->appointment_id = $data['appointment_id'] ?? '';
    }
}
