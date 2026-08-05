<?php

namespace App\Mail;

use Spatie\MailTemplates\TemplateMailable;

class WelcomeEmail extends TemplateMailable
{
    public $user_name;

    public $clinic_name;

    public function __construct(array $data)
    {
        $this->user_name = $data['user_name'] ?? '';
        $this->clinic_name = $data['clinic_name'] ?? config('app.name', 'Nyalife Women\'s Clinic');
    }
}
