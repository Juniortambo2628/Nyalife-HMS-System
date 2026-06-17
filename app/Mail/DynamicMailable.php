<?php

namespace App\Mail;

use Spatie\MailTemplates\TemplateMailable;

class DynamicMailable extends TemplateMailable
{
    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
