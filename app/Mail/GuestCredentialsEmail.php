<?php

namespace App\Mail;

use Spatie\MailTemplates\TemplateMailable;

class GuestCredentialsEmail extends TemplateMailable
{
    public $patient_name;

    public $email;

    public $password;

    public $login_url;

    public function __construct(array $data)
    {
        $this->patient_name = $data['patient_name'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->login_url = $data['login_url'] ?? '';
    }
}
