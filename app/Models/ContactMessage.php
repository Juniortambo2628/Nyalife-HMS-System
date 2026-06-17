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
        'reply',
        'replied_at',
        'replied_by',
    ];

    public function replier()
    {
        return $this->belongsTo(User::class, 'replied_by', 'user_id');
    }
}
