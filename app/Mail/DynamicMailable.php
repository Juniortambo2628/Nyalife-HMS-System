<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Spatie\DatabaseMailTemplates\HasDatabaseMailTemplate;

class DynamicMailable extends Mailable
{
    use Queueable, SerializesModels, HasDatabaseMailTemplate;

    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
