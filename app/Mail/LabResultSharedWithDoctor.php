<?php

namespace App\Mail;

use Spatie\MailTemplates\TemplateMailable;

class LabResultSharedWithDoctor extends TemplateMailable
{
    public $doctor_name;

    public $patient_name;

    public $test_name;

    public $request_number;

    public $result_url;

    public function __construct(array $data)
    {
        $this->doctor_name = $data['doctor_name'] ?? '';
        $this->patient_name = $data['patient_name'] ?? '';
        $this->test_name = $data['test_name'] ?? '';
        $this->request_number = $data['request_number'] ?? '';
        $this->result_url = $data['result_url'] ?? '';
    }
}
