<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $primaryKey = 'contact_message_id';

    protected $fillable = [
        'name',
        'email',
        'message',
        'status',
        'read_at',
    ];
}
