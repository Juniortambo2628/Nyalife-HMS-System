<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailTemplate extends Model
{
    protected $fillable = [
        'mailable',
        'subject',
        'html_template',
        'text_template',
    ];
}
